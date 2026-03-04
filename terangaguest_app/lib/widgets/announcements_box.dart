import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:provider/provider.dart';
import '../models/announcement.dart';
import '../providers/announcements_provider.dart';
import 'announcement_popup.dart';

/// Encadré « Annonces » à afficher sur l'écran Hotel Infos & Sécurité.
///
/// Affiche la liste des annonces éligibles pour l'utilisateur sous forme
/// de liste horizontale avec miniatures cliquables.
/// Masqué automatiquement si aucune annonce n'est disponible.
class AnnouncementsBox extends StatefulWidget {
  const AnnouncementsBox({super.key});

  @override
  State<AnnouncementsBox> createState() => _AnnouncementsBoxState();
}

class _AnnouncementsBoxState extends State<AnnouncementsBox> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!mounted) return;
      final provider = context.read<AnnouncementsProvider>();
      if (!provider.hasAnnouncements && !provider.isLoading) {
        provider.loadAnnouncements();
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Consumer<AnnouncementsProvider>(
      builder: (context, provider, _) {
        // Chargement initial
        if (provider.isLoading && !provider.hasAnnouncements) {
          return const Padding(
            padding: EdgeInsets.symmetric(vertical: 12),
            child: Center(child: CircularProgressIndicator()),
          );
        }

        // Pas d'annonces → widget masqué
        if (!provider.hasAnnouncements) return const SizedBox.shrink();

        final announcements = provider.announcements;

        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // En-tête section
            Padding(
              padding: const EdgeInsets.only(bottom: 12),
              child: Row(
                children: [
                  const Icon(
                    Icons.campaign_rounded,
                    size: 20,
                    color: Color(0xFFC9A96E),
                  ),
                  const SizedBox(width: 8),
                  Text(
                    'Annonces',
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.w600,
                      letterSpacing: 0.5,
                    ),
                  ),
                ],
              ),
            ),

            // Liste horizontale des annonces
            SizedBox(
              height: 140,
              child: ListView.separated(
                scrollDirection: Axis.horizontal,
                itemCount: announcements.length,
                separatorBuilder: (_, _) => const SizedBox(width: 12),
                itemBuilder: (context, index) {
                  final ann = announcements[index];
                  return _AnnouncementThumbnail(
                    announcement: ann,
                    onTap: () => AnnouncementPopup.show(context, ann),
                  );
                },
              ),
            ),
          ],
        );
      },
    );
  }
}

class _AnnouncementThumbnail extends StatelessWidget {
  final Announcement announcement;
  final VoidCallback onTap;

  const _AnnouncementThumbnail({
    required this.announcement,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final ann = announcement;

    return GestureDetector(
      onTap: onTap,
      child: Container(
        width: 200,
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: const Color(0xFFC9A96E).withValues(alpha: 0.3),
          ),
          color: Colors.grey[900],
        ),
        clipBehavior: Clip.antiAlias,
        child: Stack(
          fit: StackFit.expand,
          children: [
            // Image de fond (affiche ou placeholder)
            if (ann.hasPoster)
              CachedNetworkImage(
                imageUrl: ann.posterUrl!,
                fit: BoxFit.cover,
                placeholder: (_, _) => Container(color: Colors.grey[850]),
                errorWidget: (_, _, _) => _VideoPlaceholder(ann: ann),
              )
            else
              _VideoPlaceholder(ann: ann),

            // Icône play si vidéo
            if (ann.hasVideo)
              Positioned(
                bottom: 8,
                right: 8,
                child: Container(
                  padding: const EdgeInsets.all(5),
                  decoration: BoxDecoration(
                    color: Colors.black54,
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(
                    Icons.play_arrow_rounded,
                    color: Colors.white,
                    size: 18,
                  ),
                ),
              ),

            // Titre en bas si présent
            if (ann.title != null && ann.title!.isNotEmpty)
              Positioned(
                left: 0,
                right: 0,
                bottom: 0,
                child: Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 8,
                    vertical: 6,
                  ),
                  decoration: const BoxDecoration(
                    gradient: LinearGradient(
                      begin: Alignment.bottomCenter,
                      end: Alignment.topCenter,
                      colors: [Colors.black87, Colors.transparent],
                    ),
                  ),
                  child: Text(
                    ann.title!,
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 11,
                      fontWeight: FontWeight.w500,
                    ),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }
}

class _VideoPlaceholder extends StatelessWidget {
  final Announcement ann;
  const _VideoPlaceholder({required this.ann});

  @override
  Widget build(BuildContext context) {
    return Container(
      color: Colors.grey[850],
      child: Center(
        child: Icon(
          ann.hasVideo
              ? Icons.play_circle_outline_rounded
              : Icons.campaign_outlined,
          color: Colors.white38,
          size: 36,
        ),
      ),
    );
  }
}
