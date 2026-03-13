import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../models/guest_session.dart';
import '../../models/user.dart';
import '../../providers/auth_provider.dart';
import '../../providers/tablet_session_provider.dart';
import '../../services/tablet_session_api.dart';
import '../../utils/haptic_helper.dart';
import 'gallery_album_detail_screen.dart';

/// Écran Galerie dédié : image d'établissement + albums (depuis le hub Hotel Infos & Sécurité).
class GalleryScreen extends StatefulWidget {
  const GalleryScreen({super.key});

  @override
  State<GalleryScreen> createState() => _GalleryScreenState();
}

class _GalleryScreenState extends State<GalleryScreen> {
  final TabletSessionApi _tabletApi = TabletSessionApi();
  Gallery? _tabletGallery;
  bool _loadingTablet = false;
  String? _tabletError;
  int? _lastFetchedRoomId;

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    final session = context.read<TabletSessionProvider>().session;
    if (session != null &&
        session.roomId != _lastFetchedRoomId &&
        !_loadingTablet) {
      _loadTabletGallery(session);
    }
  }

  Future<void> _loadTabletGallery(GuestSession session) async {
    if (_loadingTablet) return;
    setState(() {
      _loadingTablet = true;
      _tabletError = null;
    });
    try {
      final infos = await _tabletApi.getHotelInfos(session);
      if (mounted) {
        setState(() {
          _tabletGallery = infos.gallery;
          _lastFetchedRoomId = session.roomId;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _tabletError = e.toString().replaceFirst('Exception: ', '');
        });
      }
    } finally {
      if (mounted) {
        setState(() => _loadingTablet = false);
      }
    }
  }

  Gallery? _getGallery(BuildContext context) {
    final tabletSession = context.watch<TabletSessionProvider>();
    if (tabletSession.hasSession && tabletSession.session != null) {
      if (_loadingTablet) return null;
      if (_tabletError != null) return null;
      return _tabletGallery;
    }
    return context.watch<AuthProvider>().user?.enterprise?.hotelInfos.gallery;
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final gallery = _getGallery(context);

    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(gradient: AppTheme.backgroundGradient),
        child: SafeArea(
          child: Column(
            children: [
              Padding(
                padding: const EdgeInsets.all(20.0),
                child: Row(
                  children: [
                    IconButton(
                      icon: const Icon(
                        Icons.arrow_back,
                        color: AppTheme.accentGold,
                      ),
                      onPressed: () {
                        HapticHelper.lightImpact();
                        Navigator.pop(context);
                      },
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            l10n.gallery,
                            style: TextStyle(
                              fontSize: MediaQuery.of(context).size.width < 600
                                  ? 18
                                  : 28,
                              fontWeight: FontWeight.bold,
                              color: AppTheme.accentGold,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            l10n.galleryDesc,
                            style: const TextStyle(
                              fontSize: 14,
                              color: AppTheme.textGray,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              Expanded(child: _buildContent(context, gallery)),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildContent(BuildContext context, Gallery? gallery) {
    if (_loadingTablet) {
      return const Center(
        child: CircularProgressIndicator(color: AppTheme.accentGold),
      );
    }
    if (_tabletError != null &&
        context.watch<TabletSessionProvider>().hasSession) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(
                _tabletError!,
                style: const TextStyle(color: AppTheme.textGray, fontSize: 14),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 16),
              TextButton(
                onPressed: () {
                  final session = context.read<TabletSessionProvider>().session;
                  if (session != null) _loadTabletGallery(session);
                },
                child: Text(
                  AppLocalizations.of(context).retry,
                  style: const TextStyle(color: AppTheme.accentGold),
                ),
              ),
            ],
          ),
        ),
      );
    }
    if (gallery == null ||
        (gallery.establishmentPhotoUrl == null ||
                gallery.establishmentPhotoUrl!.isEmpty) &&
            gallery.albums.isEmpty) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Text(
            AppLocalizations.of(context).comingSoon,
            style: const TextStyle(color: AppTheme.textGray, fontSize: 16),
          ),
        ),
      );
    }
    return SingleChildScrollView(
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
      child: _buildGalleryContent(context, gallery),
    );
  }

  Widget _buildGalleryContent(BuildContext context, Gallery gallery) {
    final l10n = AppLocalizations.of(context);
    final children = <Widget>[];
    if (gallery.establishmentPhotoUrl != null &&
        gallery.establishmentPhotoUrl!.isNotEmpty) {
      children.add(
        ClipRRect(
          borderRadius: BorderRadius.circular(12),
          child: Image.network(
            gallery.establishmentPhotoUrl!,
            fit: BoxFit.cover,
            width: double.infinity,
            height: 200,
            loadingBuilder: (context, child, progress) => progress == null
                ? child
                : const SizedBox(
                    height: 200,
                    child: Center(
                      child: CircularProgressIndicator(
                        color: AppTheme.accentGold,
                      ),
                    ),
                  ),
            errorBuilder: (_, _, _) => const SizedBox(
              height: 200,
              child: Icon(
                Icons.image_not_supported_outlined,
                color: AppTheme.textGray,
                size: 48,
              ),
            ),
          ),
        ),
      );
      children.add(const SizedBox(height: 20));
    }
    if (gallery.albums.isNotEmpty) {
      children.add(
        Text(
          l10n.albumsTitle,
          style: const TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.w600,
            color: AppTheme.accentGold,
          ),
        ),
      );
      children.add(const SizedBox(height: 12));
      for (final album in gallery.albums) {
        final firstPhotoUrl = album.photos.isNotEmpty
            ? album.photos.first.url
            : null;
        children.add(
          Padding(
            padding: const EdgeInsets.only(bottom: 10),
            child: Material(
              color: Colors.transparent,
              child: InkWell(
                onTap: () {
                  HapticHelper.lightImpact();
                  Navigator.of(context).push(
                    MaterialPageRoute<void>(
                      builder: (context) =>
                          GalleryAlbumDetailScreen(album: album),
                    ),
                  );
                },
                borderRadius: BorderRadius.circular(12),
                child: Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: AppTheme.primaryBlue.withValues(alpha: 0.5),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(
                      color: AppTheme.accentGold.withValues(alpha: 0.4),
                    ),
                  ),
                  child: Row(
                    children: [
                      if (firstPhotoUrl != null)
                        ClipRRect(
                          borderRadius: BorderRadius.circular(8),
                          child: Image.network(
                            firstPhotoUrl,
                            width: 72,
                            height: 72,
                            fit: BoxFit.cover,
                            errorBuilder: (_, _, _) => const SizedBox(
                              width: 72,
                              height: 72,
                              child: Icon(
                                Icons.photo_library,
                                color: AppTheme.textGray,
                              ),
                            ),
                          ),
                        )
                      else
                        const SizedBox(
                          width: 72,
                          height: 72,
                          child: Icon(
                            Icons.photo_library,
                            color: AppTheme.textGray,
                          ),
                        ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              album.name,
                              style: const TextStyle(
                                color: Colors.white,
                                fontWeight: FontWeight.w600,
                                fontSize: 16,
                              ),
                            ),
                            if (album.photos.isNotEmpty)
                              Text(
                                l10n.photoCount(album.photos.length),
                                style: const TextStyle(
                                  color: AppTheme.textGray,
                                  fontSize: 13,
                                ),
                              ),
                          ],
                        ),
                      ),
                      const Icon(
                        Icons.chevron_right,
                        color: AppTheme.accentGold,
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
        );
      }
    }
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: children,
    );
  }
}
