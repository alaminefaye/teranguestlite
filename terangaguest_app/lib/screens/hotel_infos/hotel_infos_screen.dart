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

/// Livret d'accueil : Wi‑Fi (chambre si renseigné), plans, règlement, infos pratiques.
/// En session tablette (code validé), affiche les infos de la chambre concernée.
class HotelInfosScreen extends StatefulWidget {
  const HotelInfosScreen({super.key});

  @override
  State<HotelInfosScreen> createState() => _HotelInfosScreenState();
}

class _HotelInfosScreenState extends State<HotelInfosScreen> {
  final TabletSessionApi _tabletApi = TabletSessionApi();
  HotelInfos? _tabletInfos;
  int? _lastFetchedRoomId;
  bool _loadingTablet = false;
  String? _tabletError;

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    final session = context.read<TabletSessionProvider>().session;
    if (session != null &&
        session.roomId != _lastFetchedRoomId &&
        !_loadingTablet) {
      _loadTabletInfos(session);
    }
  }

  Future<void> _loadTabletInfos(GuestSession session) async {
    if (_loadingTablet) return;
    setState(() {
      _loadingTablet = true;
      _tabletError = null;
    });
    try {
      final infos = await _tabletApi.getHotelInfos(session);
      if (mounted) {
        setState(() {
          _tabletInfos = infos;
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

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final tabletSession = context.watch<TabletSessionProvider>();
    final hasSession =
        tabletSession.hasSession && tabletSession.session != null;

    // En session tablette : utiliser les infos de la chambre (API)
    HotelInfos? infos;
    if (hasSession && tabletSession.session != null) {
      if (_loadingTablet) {
        infos = null; // on affichera le loader
      } else if (_tabletError != null) {
        infos = null; // on affichera l'erreur
      } else {
        infos = _tabletInfos;
      }
    }
    // Sinon : utilisateur connecté (staff / guest avec compte) → infos entreprise (déjà avec Wi‑Fi chambre si user.room_id)
    if (infos == null && !hasSession) {
      infos = context.watch<AuthProvider>().user?.enterprise?.hotelInfos;
    }

    return Scaffold(
      body: Container(
        decoration: BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [AppTheme.primaryDark, AppTheme.primaryBlue],
          ),
        ),
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
                            l10n.hotelInfos,
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
                            l10n.hotelInfosDesc,
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
              Expanded(child: _buildContent(context, l10n, infos)),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildContent(
    BuildContext context,
    AppLocalizations l10n,
    HotelInfos? infos,
  ) {
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
                  if (session != null) _loadTabletInfos(session);
                },
                child: const Text(
                  'Réessayer',
                  style: TextStyle(color: AppTheme.accentGold),
                ),
              ),
            ],
          ),
        ),
      );
    }
    return SingleChildScrollView(
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          if (infos != null) ...[
            if (infos.wifiNetwork.isNotEmpty || infos.wifiPassword.isNotEmpty)
              _section(context, l10n.wifiCode, [
                if (infos.wifiNetwork.isNotEmpty)
                  _row(l10n.wifiCode, infos.wifiNetwork),
                if (infos.wifiPassword.isNotEmpty)
                  _row(l10n.wifiPassword, infos.wifiPassword),
              ]),
            if (infos.houseRules.trim().isNotEmpty)
              _section(context, l10n.houseRules, [
                Padding(
                  padding: const EdgeInsets.only(top: 8),
                  child: Text(
                    infos.houseRules,
                    style: const TextStyle(
                      color: Colors.white70,
                      fontSize: 14,
                      height: 1.4,
                    ),
                  ),
                ),
              ]),
            if (infos.mapUrl != null && infos.mapUrl!.isNotEmpty)
              _section(context, 'Plan', [
                const SizedBox(height: 8),
                ClipRRect(
                  borderRadius: BorderRadius.circular(12),
                  child: Image.network(
                    infos.mapUrl!,
                    fit: BoxFit.contain,
                    width: double.infinity,
                    loadingBuilder: (context, child, progress) =>
                        progress == null
                        ? child
                        : const Center(
                            child: CircularProgressIndicator(
                              color: AppTheme.accentGold,
                            ),
                          ),
                    errorBuilder: (context, error, stackTrace) => const Icon(
                      Icons.map_outlined,
                      color: AppTheme.textGray,
                      size: 48,
                    ),
                  ),
                ),
              ]),
            if (infos.practicalInfo.trim().isNotEmpty)
              _section(context, l10n.practicalInfo, [
                Padding(
                  padding: const EdgeInsets.only(top: 8),
                  child: Text(
                    infos.practicalInfo,
                    style: const TextStyle(
                      color: Colors.white70,
                      fontSize: 14,
                      height: 1.4,
                    ),
                  ),
                ),
              ]),
            if (infos.gallery != null &&
                (infos.gallery!.establishmentPhotoUrl != null ||
                    infos.gallery!.albums.isNotEmpty))
              _buildGallerySection(context, infos.gallery!),
            if (infos.wifiNetwork.isEmpty &&
                infos.wifiPassword.isEmpty &&
                infos.houseRules.trim().isEmpty &&
                (infos.mapUrl == null || infos.mapUrl!.isEmpty) &&
                infos.practicalInfo.trim().isEmpty &&
                (infos.gallery == null ||
                    (infos.gallery!.establishmentPhotoUrl == null &&
                        infos.gallery!.albums.isEmpty)))
              Center(
                child: Padding(
                  padding: const EdgeInsets.all(24),
                  child: Text(
                    l10n.comingSoon,
                    style: const TextStyle(
                      color: AppTheme.textGray,
                      fontSize: 16,
                    ),
                  ),
                ),
              ),
          ] else
            Center(
              child: Padding(
                padding: const EdgeInsets.all(24),
                child: Text(
                  l10n.comingSoon,
                  style: const TextStyle(
                    color: AppTheme.textGray,
                    fontSize: 16,
                  ),
                ),
              ),
            ),
        ],
      ),
    );
  }

  Widget _section(BuildContext context, String title, List<Widget> children) {
    return Container(
      margin: const EdgeInsets.only(bottom: 20),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppTheme.primaryBlue.withValues(alpha: 0.5),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppTheme.accentGold.withValues(alpha: 0.4)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            title,
            style: const TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.w600,
              color: AppTheme.accentGold,
            ),
          ),
          ...children,
        ],
      ),
    );
  }

  Widget _row(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(top: 6),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 170,
            child: Text(
              '$label :',
              style: const TextStyle(color: AppTheme.textGray, fontSize: 14),
              overflow: TextOverflow.ellipsis,
              maxLines: 1,
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: const TextStyle(
                color: Colors.white,
                fontSize: 14,
                fontWeight: FontWeight.w500,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildGallerySection(BuildContext context, Gallery gallery) {
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
            loadingBuilder: (context, child, progress) =>
                progress == null
                    ? child
                    : const SizedBox(
                        height: 200,
                        child: Center(
                          child: CircularProgressIndicator(
                            color: AppTheme.accentGold,
                          ),
                        ),
                      ),
            errorBuilder: (context, error, stackTrace) => const SizedBox(
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
      children.add(const SizedBox(height: 12));
    }
    if (gallery.albums.isNotEmpty) {
      children.add(
        const Text(
          'Albums',
          style: TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.w600,
            color: AppTheme.accentGold,
          ),
        ),
      );
      children.add(const SizedBox(height: 8));
      for (final album in gallery.albums) {
        final firstPhotoUrl = album.photos.isNotEmpty ? album.photos.first.url : null;
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
                      builder: (context) => GalleryAlbumDetailScreen(album: album),
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
                            errorBuilder: (_, __, ___) => const SizedBox(
                              width: 72,
                              height: 72,
                              child: Icon(Icons.photo_library, color: AppTheme.textGray),
                            ),
                          ),
                        )
                      else
                        const SizedBox(
                          width: 72,
                          height: 72,
                          child: Icon(Icons.photo_library, color: AppTheme.textGray),
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
                                '${album.photos.length} photo(s)',
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
    return _section(context, 'Galerie', children);
  }
}
