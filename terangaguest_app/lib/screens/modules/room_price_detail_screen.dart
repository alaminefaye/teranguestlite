import 'package:cached_network_image/cached_network_image.dart';
import 'package:cached_network_image_platform_interface/cached_network_image_platform_interface.dart';
import 'package:flutter/material.dart';

import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../models/room.dart';
import '../../utils/haptic_helper.dart';

class RoomPriceDetailScreen extends StatelessWidget {
  const RoomPriceDetailScreen({
    super.key,
    required this.room,
    required this.sectionTitle,
  });

  final Room room;
  final String sectionTitle;

  @override
  Widget build(BuildContext context) {
    final hasImage = room.image != null && room.image!.isNotEmpty;
    final title = (room.description != null && room.description!.isNotEmpty)
        ? room.description!
        : room.typeName;
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
                        title,
                        style: const TextStyle(
                          fontSize: 22,
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
                  padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      ClipRRect(
                        borderRadius: BorderRadius.circular(18),
                        child: hasImage
                            ? CachedNetworkImage(
                                imageRenderMethodForWeb:
                                    ImageRenderMethodForWeb.HtmlImage,
                                imageUrl: room.image!,
                                width: double.infinity,
                                height: 240,
                                fit: BoxFit.cover,
                                placeholder: (context, url) =>
                                    _buildPlaceholder(),
                                errorWidget: (context, url, error) =>
                                    _buildPlaceholder(),
                              )
                            : _buildPlaceholder(),
                      ),
                      const SizedBox(height: 20),
                      Container(
                        width: double.infinity,
                        padding: const EdgeInsets.all(18),
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
                            width: 1.5,
                          ),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              sectionTitle,
                              style: const TextStyle(
                                fontSize: 14,
                                fontWeight: FontWeight.w600,
                                color: AppTheme.textGray,
                              ),
                            ),
                            const SizedBox(height: 8),
                            Text(
                              title,
                              style: const TextStyle(
                                fontSize: 24,
                                fontWeight: FontWeight.bold,
                                color: Colors.white,
                              ),
                            ),
                            const SizedBox(height: 12),
                            if (room.pricePerNight != null)
                              Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 14,
                                  vertical: 10,
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
                                  style: const TextStyle(
                                    fontSize: 20,
                                    fontWeight: FontWeight.bold,
                                    color: AppTheme.accentGold,
                                  ),
                                ),
                              ),
                            const SizedBox(height: 16),
                            if (room.capacity != null)
                              Row(
                                children: [
                                  const Icon(
                                    Icons.people_outline,
                                    color: AppTheme.textGray,
                                  ),
                                  const SizedBox(width: 8),
                                  Expanded(
                                    child: Text(
                                      l10n.capacityPeople(room.capacity!),
                                      style: const TextStyle(
                                        fontSize: 15,
                                        color: Colors.white,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            if (room.floor != null &&
                                room.floor!.isNotEmpty) ...[
                              const SizedBox(height: 10),
                              Row(
                                children: [
                                  const Icon(
                                    Icons.layers_outlined,
                                    color: AppTheme.textGray,
                                  ),
                                  const SizedBox(width: 8),
                                  Expanded(
                                    child: Text(
                                      'Étage ${room.floor}',
                                      style: const TextStyle(
                                        fontSize: 15,
                                        color: Colors.white,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ],
                            if (room.amenities != null &&
                                room.amenities!.isNotEmpty) ...[
                              const SizedBox(height: 18),
                              Text(
                                l10n.amenities,
                                style: const TextStyle(
                                  fontSize: 16,
                                  fontWeight: FontWeight.bold,
                                  color: AppTheme.accentGold,
                                ),
                              ),
                              const SizedBox(height: 10),
                              Wrap(
                                spacing: 8,
                                runSpacing: 8,
                                children: room.amenities!.map((amenity) {
                                  return Chip(
                                    label: Text(
                                      amenity,
                                      style: const TextStyle(
                                        color: Colors.white,
                                        fontSize: 12,
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

  Widget _buildPlaceholder() {
    return Container(
      width: double.infinity,
      height: 240,
      decoration: BoxDecoration(
        color: AppTheme.primaryBlue.withValues(alpha: 0.3),
      ),
      child: const Center(
        child: Icon(Icons.hotel, size: 72, color: AppTheme.accentGold),
      ),
    );
  }
}
