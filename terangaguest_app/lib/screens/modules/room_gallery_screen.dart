import 'package:cached_network_image/cached_network_image.dart';
import 'package:cached_network_image_platform_interface/cached_network_image_platform_interface.dart';
import 'package:flutter/material.dart';

import '../../config/theme.dart';
import '../../models/room_gallery.dart';
import '../../utils/haptic_helper.dart';

/// Écran galerie hébergements — affiche les photos classées par type (Chambres / Suites)
/// avec onglets, grille de miniatures et visionneuse plein écran.
class RoomGalleryScreen extends StatefulWidget {
  const RoomGalleryScreen({
    super.key,
    required this.categories,
    this.initialTabIndex = 0,
  });

  final List<RoomGalleryCategory> categories;
  final int initialTabIndex;

  @override
  State<RoomGalleryScreen> createState() => _RoomGalleryScreenState();
}

class _RoomGalleryScreenState extends State<RoomGalleryScreen>
    with SingleTickerProviderStateMixin {
  late final TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(
      length: widget.categories.length,
      vsync: this,
      initialIndex: widget.initialTabIndex.clamp(0, widget.categories.length - 1),
    );
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(gradient: AppTheme.backgroundGradient),
        child: SafeArea(
          child: Column(
            children: [
              // AppBar
              Padding(
                padding: const EdgeInsets.fromLTRB(8, 12, 16, 0),
                child: Row(
                  children: [
                    IconButton(
                      icon: const Icon(Icons.arrow_back, color: AppTheme.accentGold),
                      onPressed: () {
                        HapticHelper.lightImpact();
                        Navigator.of(context).pop();
                      },
                    ),
                    const SizedBox(width: 8),
                    const Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            'Chambres et Suites',
                            style: TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                              color: AppTheme.accentGold,
                            ),
                          ),
                          SizedBox(height: 2),
                          Text(
                            'Galerie photo',
                            style: TextStyle(fontSize: 13, color: AppTheme.textGray),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),

              // TabBar
              if (widget.categories.length > 1) ...[
                const SizedBox(height: 12),
                Container(
                  margin: const EdgeInsets.symmetric(horizontal: 16),
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.08),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: TabBar(
                    controller: _tabController,
                    indicatorSize: TabBarIndicatorSize.tab,
                    indicator: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [AppTheme.accentGold, Color(0xFFD4AF37)],
                      ),
                      borderRadius: BorderRadius.circular(10),
                    ),
                    labelColor: AppTheme.primaryDark,
                    unselectedLabelColor: AppTheme.textGray,
                    labelStyle: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w600,
                    ),
                    dividerColor: Colors.transparent,
                    tabs: widget.categories.map((cat) {
                      final icon = cat.type == 'chambre'
                          ? Icons.bed_outlined
                          : Icons.king_bed_outlined;
                      return Tab(
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(icon, size: 16),
                            const SizedBox(width: 6),
                            Text(cat.typeLabel),
                            const SizedBox(width: 4),
                            Container(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 5, vertical: 1),
                              decoration: BoxDecoration(
                                color: Colors.white.withValues(alpha: 0.25),
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: Text(
                                '${cat.total}',
                                style: const TextStyle(fontSize: 11),
                              ),
                            ),
                          ],
                        ),
                      );
                    }).toList(),
                  ),
                ),
              ] else ...[
                const SizedBox(height: 8),
              ],

              const SizedBox(height: 12),

              // Contenu
              Expanded(
                child: widget.categories.length > 1
                    ? TabBarView(
                        controller: _tabController,
                        children: widget.categories
                            .map((cat) => _buildCategoryGrid(cat))
                            .toList(),
                      )
                    : _buildCategoryGrid(widget.categories.first),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildCategoryGrid(RoomGalleryCategory category) {
    if (!category.hasPhotos) {
      return Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.photo_library_outlined,
                size: 64, color: AppTheme.accentGold),
            const SizedBox(height: 16),
            Text(
              'Aucune photo pour les ${category.typeLabel.toLowerCase()}',
              style: const TextStyle(color: AppTheme.textGray, fontSize: 15),
            ),
          ],
        ),
      );
    }

    final screenWidth = MediaQuery.sizeOf(context).width;
    final crossAxisCount = screenWidth < 600 ? 2 : screenWidth < 900 ? 3 : 4;

    return GridView.builder(
      padding: const EdgeInsets.fromLTRB(16, 0, 16, 24),
      gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: crossAxisCount,
        crossAxisSpacing: 10,
        mainAxisSpacing: 10,
        childAspectRatio: 0.85,
      ),
      itemCount: category.photos.length,
      itemBuilder: (context, index) {
        return _buildPhotoCard(category.photos, index);
      },
    );
  }

  Widget _buildPhotoCard(List<RoomGalleryPhoto> photos, int index) {
    final photo = photos[index];
    return GestureDetector(
      onTap: () {
        HapticHelper.lightImpact();
        _openViewer(photos, index);
      },
      child: Hero(
        tag: 'room_gallery_photo_${photo.id}',
        child: Container(
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(14),
            border: Border.all(
                color: AppTheme.accentGold.withValues(alpha: 0.4), width: 1),
            gradient: LinearGradient(
              colors: [
                AppTheme.primaryBlue.withValues(alpha: 0.3),
                AppTheme.primaryDark.withValues(alpha: 0.5),
              ],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
          ),
          child: Stack(
            fit: StackFit.expand,
            children: [
              ClipRRect(
                borderRadius: BorderRadius.circular(13),
                child: CachedNetworkImage(
                  imageRenderMethodForWeb: ImageRenderMethodForWeb.HtmlImage,
                  imageUrl: photo.url,
                  fit: BoxFit.cover,
                  placeholder: (context, url) => Container(
                    color: AppTheme.primaryBlue.withValues(alpha: 0.3),
                    child: const Center(
                      child: CircularProgressIndicator(
                        strokeWidth: 2,
                        valueColor:
                            AlwaysStoppedAnimation<Color>(AppTheme.accentGold),
                      ),
                    ),
                  ),
                  errorWidget: (context, url, error) => Container(
                    color: AppTheme.primaryBlue.withValues(alpha: 0.2),
                    child: const Center(
                      child: Icon(Icons.broken_image_outlined,
                          color: AppTheme.textGray, size: 36),
                    ),
                  ),
                ),
              ),
              // Overlay titre au bas
              if (photo.title?.isNotEmpty == true)
                Positioned(
                  bottom: 0,
                  left: 0,
                  right: 0,
                  child: Container(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 10, vertical: 7),
                    decoration: BoxDecoration(
                      borderRadius: const BorderRadius.vertical(
                          bottom: Radius.circular(13)),
                      gradient: LinearGradient(
                        begin: Alignment.topCenter,
                        end: Alignment.bottomCenter,
                        colors: [
                          Colors.transparent,
                          Colors.black.withValues(alpha: 0.75),
                        ],
                      ),
                    ),
                    child: Text(
                      photo.title!,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w500,
                        color: Colors.white,
                      ),
                    ),
                  ),
                ),
              // Icône zoom
              Positioned(
                top: 8,
                right: 8,
                child: Container(
                  padding: const EdgeInsets.all(4),
                  decoration: BoxDecoration(
                    color: Colors.black.withValues(alpha: 0.45),
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: const Icon(Icons.zoom_in_rounded,
                      color: Colors.white, size: 16),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _openViewer(List<RoomGalleryPhoto> photos, int initialIndex) {
    Navigator.of(context).push(
      PageRouteBuilder(
        opaque: false,
        barrierColor: Colors.black.withValues(alpha: 0.92),
        pageBuilder: (ctx, animation, secondaryAnimation) =>
            _GalleryViewer(photos: photos, initialIndex: initialIndex),
        transitionsBuilder: (ctx, animation, _, child) {
          return FadeTransition(opacity: animation, child: child);
        },
      ),
    );
  }
}

// =============================================================================
// Visionneuse plein écran
// =============================================================================

class _GalleryViewer extends StatefulWidget {
  const _GalleryViewer({required this.photos, required this.initialIndex});
  final List<RoomGalleryPhoto> photos;
  final int initialIndex;

  @override
  State<_GalleryViewer> createState() => _GalleryViewerState();
}

class _GalleryViewerState extends State<_GalleryViewer> {
  late final PageController _ctrl;
  late int _current;

  @override
  void initState() {
    super.initState();
    _current = widget.initialIndex;
    _ctrl = PageController(initialPage: widget.initialIndex);
  }

  @override
  void dispose() {
    _ctrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final photo = widget.photos[_current];
    return Scaffold(
      backgroundColor: Colors.transparent,
      body: Stack(
        children: [
          // PageView des images
          PageView.builder(
            controller: _ctrl,
            itemCount: widget.photos.length,
            onPageChanged: (i) => setState(() => _current = i),
            itemBuilder: (context, index) {
              final p = widget.photos[index];
              return InteractiveViewer(
                minScale: 0.8,
                maxScale: 4.0,
                child: Hero(
                  tag: 'room_gallery_photo_${p.id}',
                  child: CachedNetworkImage(
                    imageRenderMethodForWeb: ImageRenderMethodForWeb.HtmlImage,
                    imageUrl: p.url,
                    fit: BoxFit.contain,
                    placeholder: (context, url) => const Center(
                      child: CircularProgressIndicator(
                        valueColor: AlwaysStoppedAnimation<Color>(
                            AppTheme.accentGold),
                      ),
                    ),
                    errorWidget: (context, url, error) => const Center(
                      child: Icon(Icons.broken_image_outlined,
                          color: Colors.white54, size: 64),
                    ),
                  ),
                ),
              );
            },
          ),

          // Bouton fermer
          SafeArea(
            child: Align(
              alignment: Alignment.topRight,
              child: Padding(
                padding: const EdgeInsets.all(12),
                child: GestureDetector(
                  onTap: () => Navigator.of(context).pop(),
                  child: Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: Colors.black.withValues(alpha: 0.55),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(Icons.close, color: Colors.white, size: 22),
                  ),
                ),
              ),
            ),
          ),

          // Compteur + titre en bas
          Positioned(
            bottom: 0,
            left: 0,
            right: 0,
            child: Container(
              padding: const EdgeInsets.fromLTRB(20, 20, 20, 32),
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                  colors: [
                    Colors.transparent,
                    Colors.black.withValues(alpha: 0.8),
                  ],
                ),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisSize: MainAxisSize.min,
                children: [
                  if (photo.title?.isNotEmpty == true)
                    Text(
                      photo.title!,
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  if (photo.description?.isNotEmpty == true) ...[
                    const SizedBox(height: 4),
                    Text(
                      photo.description!,
                      maxLines: 3,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                          color: Colors.white70, fontSize: 13),
                    ),
                  ],
                  const SizedBox(height: 12),
                  // Indicateurs de pagination
                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: List.generate(widget.photos.length, (index) {
                      final isActive = index == _current;
                      return AnimatedContainer(
                        duration: const Duration(milliseconds: 200),
                        margin: const EdgeInsets.symmetric(horizontal: 3),
                        width: isActive ? 20 : 7,
                        height: 7,
                        decoration: BoxDecoration(
                          color: isActive
                              ? AppTheme.accentGold
                              : Colors.white38,
                          borderRadius: BorderRadius.circular(999),
                        ),
                      );
                    }),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
