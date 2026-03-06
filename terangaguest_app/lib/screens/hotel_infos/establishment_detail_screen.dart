import 'package:flutter/material.dart';
import '../../config/theme.dart';
import '../../models/establishment.dart';
import '../../services/establishments_api.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../utils/haptic_helper.dart';

/// Fiche détail d'un établissement : présentation, adresse, galerie photos.
class EstablishmentDetailScreen extends StatefulWidget {
  const EstablishmentDetailScreen({
    super.key,
    required this.establishmentId,
    required this.name,
  });

  final int establishmentId;
  final String name;

  @override
  State<EstablishmentDetailScreen> createState() =>
      _EstablishmentDetailScreenState();
}

class _EstablishmentDetailScreenState extends State<EstablishmentDetailScreen> {
  final EstablishmentsApi _api = EstablishmentsApi();
  EstablishmentDetail? _detail;
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final detail = await _api.getEstablishmentDetail(widget.establishmentId);
      if (mounted) {
        setState(() {
          _detail = detail;
          _loading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _error = e.toString().replaceFirst('Exception: ', '');
          _loading = false;
        });
      }
    }
  }

  void _showPhotoPreview(BuildContext context, String imageUrl) {
    Navigator.of(context).push(
      PageRouteBuilder(
        opaque: true,
        barrierColor: Colors.black,
        pageBuilder: (context, animation, secondaryAnimation) {
          return _PhotoPreviewPage(imageUrl: imageUrl);
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(
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
                padding: const EdgeInsets.all(12.0),
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
                    const SizedBox(width: 8),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            widget.name,
                            style: const TextStyle(
                              fontSize: 20,
                              fontWeight: FontWeight.bold,
                              color: AppTheme.accentGold,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              Expanded(child: _buildContent(l10n)),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildContent(AppLocalizations l10n) {
    if (_loading) {
      return const Center(
        child: CircularProgressIndicator(color: AppTheme.accentGold),
      );
    }
    if (_error != null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(
                _error!,
                style: const TextStyle(color: AppTheme.textGray, fontSize: 14),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 16),
              TextButton(
                onPressed: _load,
                child: Text(
                  l10n.retry,
                  style: const TextStyle(color: AppTheme.accentGold),
                ),
              ),
            ],
          ),
        ),
      );
    }
    final d = _detail!;
    return SingleChildScrollView(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          if (d.coverPhoto != null && d.coverPhoto!.isNotEmpty)
            ClipRRect(
              borderRadius: BorderRadius.circular(12),
              child: Image.network(
                d.coverPhoto!,
                height: 200,
                fit: BoxFit.cover,
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
                errorBuilder: (context, error, stackTrace) =>
                    const SizedBox.shrink(),
              ),
            ),
          if (d.coverPhoto != null && d.coverPhoto!.isNotEmpty)
            const SizedBox(height: 16),
          if (d.location != null && d.location!.isNotEmpty) ...[
            Text(
              d.location!,
              style: const TextStyle(fontSize: 15, color: AppTheme.textGray),
            ),
            const SizedBox(height: 12),
          ],
          if (d.description != null && d.description!.isNotEmpty) ...[
            Text(
              l10n.presentationTitle,
              style: const TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.w600,
                color: AppTheme.accentGold,
              ),
            ),
            const SizedBox(height: 6),
            Text(
              d.description!,
              style: const TextStyle(
                fontSize: 14,
                color: AppTheme.textGray,
                height: 1.4,
              ),
            ),
            const SizedBox(height: 16),
          ],
          if (d.address != null && d.address!.isNotEmpty) ...[
            Text(
              l10n.addressTitle,
              style: const TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.w600,
                color: AppTheme.accentGold,
              ),
            ),
            const SizedBox(height: 6),
            Text(
              d.address!,
              style: const TextStyle(fontSize: 14, color: AppTheme.textGray),
            ),
            const SizedBox(height: 16),
          ],
          if (d.phone != null && d.phone!.isNotEmpty) ...[
            Text(
              '${l10n.phoneAbbr} ${d.phone!}',
              style: const TextStyle(fontSize: 14, color: AppTheme.textGray),
            ),
            const SizedBox(height: 8),
          ],
          if (d.website != null && d.website!.isNotEmpty) ...[
            InkWell(
              onTap: () {
                // Option: url_launcher
              },
              child: Text(
                d.website!,
                style: const TextStyle(
                  fontSize: 14,
                  color: AppTheme.accentGold,
                  decoration: TextDecoration.underline,
                ),
              ),
            ),
            const SizedBox(height: 20),
          ],
          if (d.photos.isNotEmpty) ...[
            const SizedBox(height: 8),
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: 0.04),
                borderRadius: BorderRadius.circular(16),
                border: Border.all(
                  color: AppTheme.accentGold.withValues(alpha: 0.25),
                  width: 1,
                ),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Icon(
                        Icons.photo_library_rounded,
                        size: 20,
                        color: AppTheme.accentGold.withValues(alpha: 0.9),
                      ),
                      const SizedBox(width: 8),
                      Text(
                        l10n.gallery,
                        style: const TextStyle(
                          fontSize: 15,
                          fontWeight: FontWeight.w600,
                          color: AppTheme.accentGold,
                          letterSpacing: 0.3,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),
                  LayoutBuilder(
                    builder: (context, constraints) {
                      final width = constraints.maxWidth;
                      const maxCellSize = 80.0;
                      const spacing = 8.0;
                      final n = ((width + spacing) / (maxCellSize + spacing))
                          .floor();
                      final crossAxisCount = n.clamp(2, 4);
                      final cellSize =
                          (width - spacing * (crossAxisCount - 1)) /
                          crossAxisCount;
                      return GridView.builder(
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                          crossAxisCount: crossAxisCount,
                          mainAxisSpacing: spacing,
                          crossAxisSpacing: spacing,
                          childAspectRatio: 1.0,
                          mainAxisExtent: cellSize,
                        ),
                        itemCount: d.photos.length,
                        itemBuilder: (context, index) {
                          final photo = d.photos[index];
                          final url = photo.url;
                          if (url == null || url.isEmpty) {
                            return const SizedBox.shrink();
                          }
                          return GestureDetector(
                            onTap: () {
                              HapticHelper.lightImpact();
                              _showPhotoPreview(context, url);
                            },
                            child: Container(
                              width: cellSize,
                              height: cellSize,
                              decoration: BoxDecoration(
                                borderRadius: BorderRadius.circular(8),
                                color: Colors.white.withValues(alpha: 0.06),
                                boxShadow: [
                                  BoxShadow(
                                    color: Colors.black.withValues(alpha: 0.2),
                                    blurRadius: 4,
                                    offset: const Offset(0, 1),
                                  ),
                                ],
                              ),
                              child: ClipRRect(
                                borderRadius: BorderRadius.circular(8),
                                child: Image.network(
                                  url,
                                  fit: BoxFit.cover,
                                  width: cellSize,
                                  height: cellSize,
                                  alignment: Alignment.center,
                                  loadingBuilder: (context, child, progress) =>
                                      progress == null
                                      ? child
                                      : Container(
                                          width: cellSize,
                                          height: cellSize,
                                          color: Colors.white10,
                                          child: const Center(
                                            child: SizedBox(
                                              width: 20,
                                              height: 20,
                                              child: CircularProgressIndicator(
                                                color: AppTheme.accentGold,
                                                strokeWidth: 2,
                                              ),
                                            ),
                                          ),
                                        ),
                                  errorBuilder: (context, error, stackTrace) =>
                                      ColoredBox(
                                        color: Colors.white12,
                                        child: SizedBox(
                                          width: cellSize,
                                          height: cellSize,
                                          child: const Center(
                                            child: Icon(
                                              Icons.broken_image_outlined,
                                              color: AppTheme.textGray,
                                              size: 24,
                                            ),
                                          ),
                                        ),
                                      ),
                                ),
                              ),
                            ),
                          );
                        },
                      );
                    },
                  ),
                ],
              ),
            ),
          ],
          const SizedBox(height: 24),
        ],
      ),
    );
  }
}

/// Aperçu plein écran d'une photo avec bouton fermer.
class _PhotoPreviewPage extends StatelessWidget {
  const _PhotoPreviewPage({required this.imageUrl});

  final String imageUrl;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      body: Stack(
        children: [
          Center(
            child: InteractiveViewer(
              minScale: 0.5,
              maxScale: 4.0,
              child: Image.network(
                imageUrl,
                fit: BoxFit.contain,
                loadingBuilder: (context, child, progress) => progress == null
                    ? child
                    : const Center(
                        child: CircularProgressIndicator(
                          color: AppTheme.accentGold,
                        ),
                      ),
                errorBuilder: (context, error, stackTrace) => const Center(
                  child: Icon(
                    Icons.broken_image_outlined,
                    color: AppTheme.textGray,
                    size: 48,
                  ),
                ),
              ),
            ),
          ),
          SafeArea(
            child: Align(
              alignment: Alignment.topRight,
              child: Padding(
                padding: const EdgeInsets.all(12.0),
                child: Material(
                  color: Colors.black54,
                  shape: const CircleBorder(),
                  child: InkWell(
                    onTap: () {
                      HapticHelper.lightImpact();
                      Navigator.of(context).pop();
                    },
                    customBorder: const CircleBorder(),
                    child: const Padding(
                      padding: EdgeInsets.all(12.0),
                      child: Icon(
                        Icons.close,
                        color: AppTheme.accentGold,
                        size: 28,
                      ),
                    ),
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
