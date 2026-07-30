import 'package:cached_network_image/cached_network_image.dart';
import 'package:cached_network_image_platform_interface/cached_network_image_platform_interface.dart';
import 'package:flutter/material.dart';

import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../models/room.dart';
import '../../utils/haptic_helper.dart';

class RoomPriceDetailScreen extends StatefulWidget {
  const RoomPriceDetailScreen({
    super.key,
    required this.room,
    required this.sectionTitle,
  });

  final Room room;
  final String sectionTitle;

  @override
  State<RoomPriceDetailScreen> createState() => _RoomPriceDetailScreenState();
}

class _RoomPriceDetailScreenState extends State<RoomPriceDetailScreen> {
  late final PageController _pageController;
  int _currentImageIndex = 0;

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
    final room = widget.room;
    final sectionTitle = widget.sectionTitle;
    final gallery = room.galleryImages.isNotEmpty
        ? room.galleryImages
        : (room.image != null && room.image!.isNotEmpty
              ? [room.image!]
              : const <String>[]);
    final hasImage = gallery.isNotEmpty;
    final title = room.typeName.trim().isNotEmpty
        ? room.typeName
        : sectionTitle;
    final description = room.description?.trim();
    final l10n = AppLocalizations.of(context);
    final width = MediaQuery.sizeOf(context).width;
    final isMobile = width < 600;
    final heroHeight = isMobile ? 205.0 : 255.0;
    final thumbHeight = isMobile ? 68.0 : 82.0;
    final headerTitleSize = isMobile ? 18.0 : 22.0;
    final sectionLabelSize = isMobile ? 12.0 : 14.0;
    final titleSize = isMobile ? 19.0 : 23.0;
    final priceSize = isMobile ? 16.0 : 19.0;
    final bodySize = isMobile ? 13.0 : 15.0;
    final metaSize = isMobile ? 13.0 : 15.0;
    final amenitiesTitleSize = isMobile ? 14.0 : 16.0;
    final chipTextSize = isMobile ? 11.0 : 12.0;

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
                padding: EdgeInsets.all(isMobile ? 14 : 16),
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
                    const SizedBox(width: 6),
                    Expanded(
                      child: Text(
                        title,
                        style: TextStyle(
                          fontSize: headerTitleSize,
                          fontWeight: FontWeight.bold,
                          color: AppTheme.accentGold,
                        ),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ),
              ),
              Expanded(
                child: SingleChildScrollView(
                  padding: EdgeInsets.fromLTRB(16, isMobile ? 4 : 8, 16, 24),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      ClipRRect(
                        borderRadius: BorderRadius.circular(18),
                        child: hasImage
                            ? SizedBox(
                                height: heroHeight,
                                child: PageView.builder(
                                  controller: _pageController,
                                  itemCount: gallery.length,
                                  onPageChanged: (index) {
                                    setState(() => _currentImageIndex = index);
                                  },
                                  itemBuilder: (context, index) {
                                    return CachedNetworkImage(
                                      imageRenderMethodForWeb:
                                          ImageRenderMethodForWeb.HtmlImage,
                                      imageUrl: gallery[index],
                                      width: double.infinity,
                                      height: heroHeight,
                                      fit: BoxFit.cover,
                                      placeholder: (context, url) =>
                                          _buildPlaceholder(height: heroHeight),
                                      errorWidget: (context, url, error) =>
                                          _buildPlaceholder(height: heroHeight),
                                    );
                                  },
                                ),
                              )
                            : _buildPlaceholder(height: heroHeight),
                      ),
                      if (gallery.length > 1) ...[
                        const SizedBox(height: 10),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: List.generate(gallery.length, (index) {
                            final isActive = index == _currentImageIndex;
                            return AnimatedContainer(
                              duration: const Duration(milliseconds: 180),
                              margin: const EdgeInsets.symmetric(horizontal: 3),
                              width: isActive ? 16 : 7,
                              height: 7,
                              decoration: BoxDecoration(
                                color: isActive
                                    ? AppTheme.accentGold
                                    : Colors.white24,
                                borderRadius: BorderRadius.circular(999),
                              ),
                            );
                          }),
                        ),
                        const SizedBox(height: 10),
                        SizedBox(
                          height: thumbHeight,
                          child: ListView.separated(
                            scrollDirection: Axis.horizontal,
                            itemCount: gallery.length,
                            separatorBuilder: (_, index) =>
                                const SizedBox(width: 10),
                            itemBuilder: (context, index) => GestureDetector(
                              onTap: () {
                                _pageController.animateToPage(
                                  index,
                                  duration: const Duration(milliseconds: 220),
                                  curve: Curves.easeInOut,
                                );
                              },
                              child: Container(
                                width: isMobile ? 92 : 108,
                                decoration: BoxDecoration(
                                  borderRadius: BorderRadius.circular(12),
                                  border: Border.all(
                                    color: index == _currentImageIndex
                                        ? AppTheme.accentGold
                                        : Colors.white24,
                                    width: 1.3,
                                  ),
                                ),
                                child: ClipRRect(
                                  borderRadius: BorderRadius.circular(10),
                                  child: CachedNetworkImage(
                                    imageRenderMethodForWeb:
                                        ImageRenderMethodForWeb.HtmlImage,
                                    imageUrl: gallery[index],
                                    width: isMobile ? 92 : 108,
                                    height: thumbHeight,
                                    fit: BoxFit.cover,
                                    placeholder: (context, url) => Container(
                                      width: isMobile ? 92 : 108,
                                      height: thumbHeight,
                                      color: AppTheme.primaryBlue.withValues(
                                        alpha: 0.3,
                                      ),
                                    ),
                                    errorWidget: (context, url, error) =>
                                        Container(
                                          width: isMobile ? 92 : 108,
                                          height: thumbHeight,
                                          color: AppTheme.primaryBlue
                                              .withValues(alpha: 0.3),
                                        ),
                                  ),
                                ),
                              ),
                            ),
                          ),
                        ),
                      ],
                      SizedBox(height: isMobile ? 14 : 20),
                      Container(
                        width: double.infinity,
                        padding: EdgeInsets.all(isMobile ? 14 : 18),
                        decoration: BoxDecoration(
                          gradient: const LinearGradient(
                            colors: [
                              AppTheme.primaryBlue,
                              AppTheme.primaryDark,
                            ],
                          ),
                          borderRadius: BorderRadius.circular(18),
                          border: Border.all(
                            color: AppTheme.accentGold,
                            width: 1.4,
                          ),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              sectionTitle,
                              style: TextStyle(
                                fontSize: sectionLabelSize,
                                fontWeight: FontWeight.w600,
                                color: AppTheme.textGray,
                              ),
                            ),
                            const SizedBox(height: 6),
                            Text(
                              title,
                              style: TextStyle(
                                fontSize: titleSize,
                                fontWeight: FontWeight.bold,
                                color: Colors.white,
                              ),
                            ),
                            const SizedBox(height: 10),
                            if (room.pricePerNight != null)
                              Container(
                                padding: EdgeInsets.symmetric(
                                  horizontal: isMobile ? 12 : 14,
                                  vertical: isMobile ? 8 : 10,
                                ),
                                decoration: BoxDecoration(
                                  color: AppTheme.accentGold.withValues(
                                    alpha: 0.16,
                                  ),
                                  borderRadius: BorderRadius.circular(12),
                                  border: Border.all(
                                    color: AppTheme.accentGold,
                                  ),
                                ),
                                child: Text(
                                  room.formattedPricePerNight != null
                                      ? '${room.formattedPricePerNight} / nuit'
                                      : '${room.pricePerNight!.toStringAsFixed(0)} FCFA / nuit',
                                  style: TextStyle(
                                    fontSize: priceSize,
                                    fontWeight: FontWeight.bold,
                                    color: AppTheme.accentGold,
                                  ),
                                ),
                              ),
                            if (description != null &&
                                description.isNotEmpty) ...[
                              SizedBox(height: isMobile ? 12 : 16),
                              Text(
                                description,
                                style: TextStyle(
                                  fontSize: bodySize,
                                  height: 1.45,
                                  color: Colors.white,
                                ),
                              ),
                            ],
                            SizedBox(height: isMobile ? 12 : 14),
                            if (room.capacity != null)
                              Row(
                                children: [
                                  const Icon(
                                    Icons.people_outline,
                                    color: AppTheme.textGray,
                                    size: 18,
                                  ),
                                  const SizedBox(width: 8),
                                  Expanded(
                                    child: Text(
                                      l10n.capacityPeople(room.capacity!),
                                      style: TextStyle(
                                        fontSize: metaSize,
                                        color: Colors.white,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            if (room.floor != null &&
                                room.floor!.isNotEmpty) ...[
                              const SizedBox(height: 8),
                              Row(
                                children: [
                                  const Icon(
                                    Icons.layers_outlined,
                                    color: AppTheme.textGray,
                                    size: 18,
                                  ),
                                  const SizedBox(width: 8),
                                  Expanded(
                                    child: Text(
                                      'Étage ${room.floor}',
                                      style: TextStyle(
                                        fontSize: metaSize,
                                        color: Colors.white,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ],
                            if (room.amenities != null &&
                                room.amenities!.isNotEmpty) ...[
                              SizedBox(height: isMobile ? 12 : 16),
                              Text(
                                l10n.amenities,
                                style: TextStyle(
                                  fontSize: amenitiesTitleSize,
                                  fontWeight: FontWeight.bold,
                                  color: AppTheme.accentGold,
                                ),
                              ),
                              const SizedBox(height: 8),
                              Wrap(
                                spacing: 6,
                                runSpacing: 6,
                                children: room.amenities!.map((amenity) {
                                  return Chip(
                                    visualDensity: VisualDensity.compact,
                                    materialTapTargetSize:
                                        MaterialTapTargetSize.shrinkWrap,
                                    label: Text(
                                      amenity,
                                      style: TextStyle(
                                        color: Colors.white,
                                        fontSize: chipTextSize,
                                      ),
                                    ),
                                    backgroundColor: AppTheme.accentGold
                                        .withValues(alpha: 0.12),
                                    side: const BorderSide(
                                      color: AppTheme.accentGold,
                                    ),
                                  );
                                }).toList(),
                              ),
                            ],
                          ],
                        ),
                      ),
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

  Widget _buildPlaceholder({double height = 240}) {
    return Container(
      width: double.infinity,
      height: height,
      decoration: BoxDecoration(
        color: AppTheme.primaryBlue.withValues(alpha: 0.3),
      ),
      child: const Center(
        child: Icon(Icons.hotel, size: 56, color: AppTheme.accentGold),
      ),
    );
  }
}
