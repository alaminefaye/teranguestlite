import 'dart:async';
import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:provider/provider.dart';
import 'package:video_player/video_player.dart';
import '../models/announcement.dart';
import '../providers/announcements_provider.dart';

/// Overlay (dialog) affichant une annonce (affiche et/ou vidéo).
///
/// Règles conformes à la spec :
/// - Vidéo : audio désactivé par défaut, bouton toggle son, fermeture auto à la fin.
/// - Affiche seule : durée configurée par annonce (displayDurationMinutes), fermeture auto.
/// - Fermeture manuelle : bouton × discret coin haut droit + tap barrière extérieure.
/// - Notification pendant annonce : la vidéo est mise en pause via [pauseVideo].
class AnnouncementPopup extends StatefulWidget {
  final Announcement announcement;

  const AnnouncementPopup({super.key, required this.announcement});

  /// Ouvre le popup et enregistre la vue dans l'API.
  static Future<void> show(
    BuildContext context,
    Announcement announcement,
  ) async {
    // Enregistrement de la vue (fire-and-forget, avant l'affichage)
    context.read<AnnouncementsProvider>().recordView(announcement.id);

    await showDialog<void>(
      context: context,
      barrierDismissible: true,
      barrierColor: Colors.black87,
      builder: (ctx) => AnnouncementPopup(announcement: announcement),
    );
  }

  @override
  State<AnnouncementPopup> createState() => _AnnouncementPopupState();
}

class _AnnouncementPopupState extends State<AnnouncementPopup> {
  VideoPlayerController? _videoController;
  bool _isMuted = true;
  bool _videoInitialized = false;
  bool _videoError = false;
  Timer? _posterTimer;

  Announcement get ann => widget.announcement;

  @override
  void initState() {
    super.initState();

    if (ann.hasVideo) {
      _initVideo();
    } else if (ann.hasPoster) {
      // Affiche seule → fermeture automatique après durée configurée
      _startPosterTimer();
    }
  }

  void _initVideo() {
    _videoController =
        VideoPlayerController.networkUrl(
            Uri.parse(ann.videoUrl!),
            videoPlayerOptions: VideoPlayerOptions(mixWithOthers: true),
          )
          ..initialize()
              .then((_) {
                if (!mounted) return;
                setState(() => _videoInitialized = true);
                // Muet par défaut, lancement auto
                _videoController!.setVolume(0);
                _videoController!.play();
                // Fermeture auto en fin de vidéo
                _videoController!.addListener(_onVideoEnd);
              })
              .catchError((_) {
                if (!mounted) return;
                setState(() => _videoError = true);
                // Fallback : si la vidéo plante, afficher l'affiche avec timer
                if (ann.hasPoster) _startPosterTimer();
              });
  }

  void _onVideoEnd() {
    if (_videoController == null) return;
    final pos = _videoController!.value.position;
    final dur = _videoController!.value.duration;
    if (_videoController!.value.isInitialized &&
        dur > Duration.zero &&
        pos >= dur - const Duration(milliseconds: 300)) {
      _close();
    }
  }

  void _startPosterTimer() {
    final duration =
        ann.displayDuration; // Duration(minutes: displayDurationMinutes)
    _posterTimer = Timer(duration, _close);
  }

  /// Mise en pause de la vidéo (appelée lors d'une notification entrante).
  void pauseVideo() {
    if (_videoController?.value.isPlaying == true) {
      _videoController?.pause();
    }
  }

  /// Reprise de la vidéo après traitement de la notification.
  void resumeVideo() {
    if (_videoController?.value.isInitialized == true &&
        !_videoController!.value.isPlaying) {
      _videoController?.play();
    }
  }

  void _toggleMute() {
    setState(() => _isMuted = !_isMuted);
    _videoController?.setVolume(_isMuted ? 0 : 1);
  }

  void _close() {
    if (mounted) Navigator.of(context, rootNavigator: true).pop();
  }

  @override
  void dispose() {
    _posterTimer?.cancel();
    _videoController?.removeListener(_onVideoEnd);
    _videoController?.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Stack(
      alignment: Alignment.center,
      children: [
        // Contenu du popup
        Dialog(
          backgroundColor: Colors.transparent,
          insetPadding: const EdgeInsets.symmetric(
            horizontal: 16,
            vertical: 32,
          ),
          child: ClipRRect(
            borderRadius: BorderRadius.circular(16),
            child: Container(
              color: Colors.black,
              constraints: BoxConstraints(
                maxHeight: MediaQuery.of(context).size.height * 0.82,
                maxWidth: MediaQuery.of(context).size.width,
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  // Vidéo ou affiche
                  Flexible(child: _buildMedia()),
                  // Barre de contrôle (son) si vidéo présente
                  if (ann.hasVideo && _videoInitialized) _buildControls(),
                ],
              ),
            ),
          ),
        ),

        // Bouton fermeture discret — coin haut droit (en dehors du Dialog pour overlapper)
        Positioned(
          top: MediaQuery.of(context).size.height * 0.04,
          right: 24,
          child: GestureDetector(
            onTap: _close,
            child: Container(
              width: 32,
              height: 32,
              decoration: BoxDecoration(
                color: Colors.black54,
                shape: BoxShape.circle,
                border: Border.all(color: Colors.white30),
              ),
              child: const Icon(Icons.close, color: Colors.white, size: 18),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildMedia() {
    // 1. Vidéo initialisée
    if (ann.hasVideo && _videoInitialized && _videoController != null) {
      return AspectRatio(
        aspectRatio: _videoController!.value.aspectRatio,
        child: VideoPlayer(_videoController!),
      );
    }

    // 2. Vidéo en cours de chargement
    if (ann.hasVideo && !_videoError && !_videoInitialized) {
      return SizedBox(
        height: 240,
        child: _buildPosterBackground(
          child: const Center(
            child: CircularProgressIndicator(color: Colors.white),
          ),
        ),
      );
    }

    // 3. Affiche seule (ou fallback si vidéo en erreur)
    if (ann.hasPoster) {
      return CachedNetworkImage(
        imageUrl: ann.posterUrl!,
        fit: BoxFit.contain,
        placeholder: (_, _) => Container(
          height: 240,
          color: Colors.black,
          child: const Center(
            child: CircularProgressIndicator(color: Colors.white),
          ),
        ),
        errorWidget: (_, _, _) => Container(
          height: 240,
          color: Colors.grey[900],
          child: const Icon(
            Icons.broken_image,
            color: Colors.white54,
            size: 48,
          ),
        ),
      );
    }

    return SizedBox(
      height: 240,
      child: _buildPosterBackground(
        child: const Center(
          child: Icon(Icons.campaign_outlined, color: Colors.white54, size: 64),
        ),
      ),
    );
  }

  Widget _buildPosterBackground({required Widget child}) {
    if (ann.hasPoster) {
      return CachedNetworkImage(
        imageUrl: ann.posterUrl!,
        fit: BoxFit.cover,
        imageBuilder: (_, provider) => Container(
          decoration: BoxDecoration(
            image: DecorationImage(image: provider, fit: BoxFit.cover),
          ),
          child: child,
        ),
        placeholder: (_, _) => Container(color: Colors.black, child: child),
        errorWidget: (_, _, _) =>
            Container(color: Colors.grey[900], child: child),
      );
    }
    return Container(color: Colors.black, child: child);
  }

  Widget _buildControls() {
    return Container(
      color: Colors.black87,
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Row(
        children: [
          // Titre
          Expanded(
            child: Text(
              ann.title ?? '',
              style: const TextStyle(color: Colors.white70, fontSize: 13),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ),
          // Toggle son
          IconButton(
            onPressed: _toggleMute,
            icon: Icon(
              _isMuted ? Icons.volume_off : Icons.volume_up,
              color: Colors.white,
              size: 22,
            ),
            tooltip: _isMuted ? 'Activer le son' : 'Couper le son',
          ),
          // Lecture / Pause manuelle
          IconButton(
            onPressed: () {
              if (_videoController!.value.isPlaying) {
                _videoController!.pause();
              } else {
                _videoController!.play();
              }
              setState(() {});
            },
            icon: Icon(
              _videoController!.value.isPlaying
                  ? Icons.pause
                  : Icons.play_arrow,
              color: Colors.white,
              size: 22,
            ),
          ),
        ],
      ),
    );
  }
}
