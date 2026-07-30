import 'package:cached_network_image/cached_network_image.dart';
import 'package:cached_network_image_platform_interface/cached_network_image_platform_interface.dart';
import 'package:flutter/material.dart';

import '../../config/theme.dart';
import '../../models/room.dart';
import '../../utils/haptic_helper.dart';
import 'room_price_detail_screen.dart';

class RoomCategoryDetailScreen extends StatefulWidget {
  const RoomCategoryDetailScreen({super.key, required this.category});

  final RoomCategoryViewData category;

  @override
  State<RoomCategoryDetailScreen> createState() =>
      _RoomCategoryDetailScreenState();
}

class _RoomCategoryDetailScreenState extends State<RoomCategoryDetailScreen> {
  late final PageController _pageController;
  int _currentPage = 0;

  @override
  void initState() {
    super.initState();
    _pageController = PageController();
  }

  @override
  void dispose() {
    _pageController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final gallery = widget.category.gallery;

    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(gradient: AppTheme.backgroundGradient),
        child: SafeArea(
          child: Column(
            children: [
              Padding(
                padding: const EdgeInsets.all(16),
                child: Row(
                  children: [
                    IconButton(
                      icon: const Icon(
                        Icons.arrow_back,
                        color: AppTheme.accentGold,
                      ),
                      onPressed: () {
                        HapticHelper.lightImpact();
                        Navigator.of(context).pop();
                      },
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        widget.category.title,
                        style: const TextStyle(
                          fontSize: 22,
                          fontWeight: FontWeight.bold,
                          color: AppTheme.accentGold,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              Expanded(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.fromLTRB(16, 0, 16, 24),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      _buildGallery(gallery),
                      const SizedBox(height: 20),
                      _buildIntroCard(),
                      const SizedBox(height: 20),
                      const Text(
                        'Galerie',
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                          color: AppTheme.accentGold,
                        ),
                      ),
                      const SizedBox(height: 12),
                      _buildThumbnails(gallery),
                      const SizedBox(height: 24),
                      const Text(
                        'Hébergements disponibles',
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                          color: AppTheme.accentGold,
                        ),
                      ),
                      const SizedBox(height: 12),
                      if (widget.category.rooms.isEmpty)
                        _buildEmptyState()
                      else
                        ...widget.category.rooms.map(_buildRoomCard),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildGallery(List<String> gallery) {
    if (gallery.isEmpty) {
      return _buildPlaceholder(height: 230);
    }

    return Column(
      children: [
        SizedBox(
          height: 230,
          child: PageView.builder(
            controller: _pageController,
            itemCount: gallery.length,
            onPageChanged: (index) => setState(() => _currentPage = index),
            itemBuilder: (context, index) {
              final imageUrl = gallery[index];
              return ClipRRect(
                borderRadius: BorderRadius.circular(18),
                child: CachedNetworkImage(
                  imageRenderMethodForWeb: ImageRenderMethodForWeb.HtmlImage,
                  imageUrl: imageUrl,
                  fit: BoxFit.cover,
                  width: double.infinity,
                  placeholder: (context, url) => _buildPlaceholder(height: 230),
                  errorWidget: (context, url, error) =>
                      _buildPlaceholder(height: 230),
                ),
              );
            },
          ),
        ),
        if (gallery.length > 1) ...[
          const SizedBox(height: 10),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: List.generate(gallery.length, (index) {
              final isActive = index == _currentPage;
              return AnimatedContainer(
                duration: const Duration(milliseconds: 180),
                margin: const EdgeInsets.symmetric(horizontal: 3),
                width: isActive ? 18 : 8,
                height: 8,
                decoration: BoxDecoration(
                  color: isActive ? AppTheme.accentGold : Colors.white24,
                  borderRadius: BorderRadius.circular(999),
                ),
              );
            }),
          ),
        ],
      ],
    );
  }

  Widget _buildIntroCard() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [AppTheme.primaryBlue, AppTheme.primaryDark],
        ),
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: AppTheme.accentGold, width: 1.4),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            widget.category.title,
            style: const TextStyle(
              fontSize: 24,
              fontWeight: FontWeight.bold,
              color: AppTheme.accentGold,
            ),
          ),
          const SizedBox(height: 12),
          Text(
            widget.category.description,
            style: const TextStyle(
              fontSize: 15,
              height: 1.45,
              color: Colors.white,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildThumbnails(List<String> gallery) {
    if (gallery.isEmpty) {
      return const SizedBox.shrink();
    }

    return SizedBox(
      height: 84,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        itemCount: gallery.length,
        separatorBuilder: (_, index) => const SizedBox(width: 10),
        itemBuilder: (context, index) {
          final imageUrl = gallery[index];
          return GestureDetector(
            onTap: () {
              _pageController.animateToPage(
                index,
                duration: const Duration(milliseconds: 250),
                curve: Curves.easeInOut,
              );
            },
            child: Container(
              width: 110,
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(14),
                border: Border.all(
                  color: index == _currentPage
                      ? AppTheme.accentGold
                      : Colors.white24,
                  width: 1.4,
                ),
              ),
              child: ClipRRect(
                borderRadius: BorderRadius.circular(12),
                child: CachedNetworkImage(
                  imageRenderMethodForWeb: ImageRenderMethodForWeb.HtmlImage,
                  imageUrl: imageUrl,
                  fit: BoxFit.cover,
                  placeholder: (context, url) => _buildPlaceholder(height: 84),
                  errorWidget: (context, url, error) =>
                      _buildPlaceholder(height: 84),
                ),
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _buildRoomCard(Room room) {
    final title = (room.typeName.trim().isNotEmpty)
        ? room.typeName
        : widget.category.title;
    final description = (room.description?.trim().isNotEmpty ?? false)
        ? room.description!
        : 'Description bientôt disponible.';
    final coverImage = room.image?.trim().isNotEmpty == true
        ? room.image
        : (room.galleryImages.isNotEmpty ? room.galleryImages.first : null);

    return InkWell(
      borderRadius: BorderRadius.circular(16),
      onTap: () {
        HapticHelper.lightImpact();
        Navigator.of(context).push(
          MaterialPageRoute(
            builder: (_) => RoomPriceDetailScreen(
              room: room,
              sectionTitle: widget.category.title,
            ),
          ),
        );
      },
      child: Container(
        margin: const EdgeInsets.only(bottom: 12),
        decoration: BoxDecoration(
          gradient: const LinearGradient(
            colors: [AppTheme.primaryBlue, AppTheme.primaryDark],
          ),
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: AppTheme.accentGold, width: 1.1),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (coverImage != null && coverImage.isNotEmpty)
              ClipRRect(
                borderRadius: const BorderRadius.vertical(
                  top: Radius.circular(15),
                ),
                child: CachedNetworkImage(
                  imageRenderMethodForWeb: ImageRenderMethodForWeb.HtmlImage,
                  imageUrl: coverImage,
                  width: double.infinity,
                  height: 160,
                  fit: BoxFit.cover,
                  placeholder: (context, url) => _buildPlaceholder(height: 160),
                  errorWidget: (context, url, error) =>
                      _buildPlaceholder(height: 160),
                ),
              ),
            Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: const TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: AppTheme.accentGold,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    description,
                    style: const TextStyle(
                      fontSize: 14,
                      height: 1.4,
                      color: Colors.white,
                    ),
                  ),
                  if (room.capacity != null) ...[
                    const SizedBox(height: 10),
                    Text(
                      'Capacité : ${room.capacity} personnes',
                      style: const TextStyle(
                        fontSize: 13,
                        color: AppTheme.textGray,
                      ),
                    ),
                  ],
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildEmptyState() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.05),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.white24),
      ),
      child: const Text(
        'Aucune donnée disponible pour cette catégorie pour le moment.',
        style: TextStyle(fontSize: 14, color: Colors.white),
      ),
    );
  }

  Widget _buildPlaceholder({required double height}) {
    return Container(
      width: double.infinity,
      height: height,
      color: AppTheme.primaryBlue.withValues(alpha: 0.25),
      child: const Center(
        child: Icon(Icons.hotel_outlined, size: 62, color: AppTheme.accentGold),
      ),
    );
  }
}

class RoomCategoryViewData {
  const RoomCategoryViewData({
    required this.title,
    required this.icon,
    required this.imagePath,
    required this.description,
    required this.gallery,
    required this.rooms,
  });

  final String title;
  final IconData icon;
  final String imagePath;
  final String description;
  final List<String> gallery;
  final List<Room> rooms;
}
