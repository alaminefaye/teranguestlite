import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../generated/l10n/app_localizations.dart';
import '../config/theme.dart';
import '../models/restaurant.dart';

class RestaurantCard extends StatelessWidget {
  final Restaurant restaurant;
  final VoidCallback onTap;

  const RestaurantCard({
    super.key,
    required this.restaurant,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Semantics(
      button: true,
      label: restaurant.name,
      child: GestureDetector(
        onTap: onTap,
        child: Transform(
          transform: Matrix4.identity()
            ..setEntry(3, 2, 0.001) // Perspective 3D
            ..rotateX(-0.05)
            ..rotateY(0.02),
          alignment: Alignment.center,
          child: Container(
            decoration: BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
                colors: [AppTheme.primaryBlue, AppTheme.primaryDark],
              ),
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: AppTheme.accentGold, width: 1.5),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.4),
                  blurRadius: 20,
                  spreadRadius: 2,
                  offset: const Offset(0, 10),
                ),
                BoxShadow(
                  color: AppTheme.accentGold.withValues(alpha: 0.1),
                  blurRadius: 15,
                  spreadRadius: -2,
                  offset: const Offset(0, -4),
                ),
              ],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // Image du restaurant
                Expanded(
                  flex: 5,
                  child: Stack(
                    children: [
                      // Image
                      ClipRRect(
                        borderRadius: const BorderRadius.only(
                          topLeft: Radius.circular(14),
                          topRight: Radius.circular(14),
                        ),
                        child: restaurant.image != null
                            ? CachedNetworkImage(
                                imageUrl: restaurant.image!,
                                width: double.infinity,
                                height: double.infinity,
                                fit: BoxFit.cover,
                                placeholder: (context, url) =>
                                    _buildPlaceholder(),
                                errorWidget: (context, url, error) =>
                                    _buildPlaceholder(),
                              )
                            : _buildPlaceholder(),
                      ),

                      // Badge Ouvert/Fermé
                      Positioned(
                        top: 8,
                        right: 8,
                        child: Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 8,
                            vertical: 4,
                          ),
                          decoration: BoxDecoration(
                            color: restaurant.isOpen
                                ? Colors.green.withValues(alpha: 0.9)
                                : Colors.red.withValues(alpha: 0.9),
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(
                              color: restaurant.isOpen
                                  ? Colors.green
                                  : Colors.red,
                              width: 1,
                            ),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(
                                restaurant.isOpen
                                    ? Icons.check_circle
                                    : Icons.cancel,
                                size: 12,
                                color: Colors.white,
                              ),
                              const SizedBox(width: 4),
                              Text(
                                restaurant.isOpen
                                    ? AppLocalizations.of(context).open
                                    : AppLocalizations.of(context).closed,
                                style: const TextStyle(
                                  fontSize: 11,
                                  color: Colors.white,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ),
                ),

                // Informations : FittedBox pour éviter l'overflow
                Expanded(
                  flex: 3,
                  child: FittedBox(
                    fit: BoxFit.scaleDown,
                    alignment: Alignment.topLeft,
                    child: Padding(
                      padding: const EdgeInsets.all(12.0),
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(
                            restaurant.name,
                            style: const TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                              color: AppTheme.accentGold,
                            ),
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                          ),
                          if (restaurant.type != null ||
                              restaurant.cuisine != null)
                            Padding(
                              padding: const EdgeInsets.only(top: 4.0),
                              child: Text(
                                [
                                  if (restaurant.type != null) restaurant.type!,
                                  if (restaurant.cuisine != null)
                                    restaurant.cuisine!,
                                ].join(' • '),
                                style: const TextStyle(
                                  fontSize: 11,
                                  color: AppTheme.textGray,
                                  height: 1.2,
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          const SizedBox(height: 8),
                          Row(
                            mainAxisSize: MainAxisSize.min,
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              if (restaurant.capacity != null)
                                Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    const Icon(
                                      Icons.people_outline,
                                      size: 14,
                                      color: AppTheme.textGray,
                                    ),
                                    const SizedBox(width: 4),
                                    Text(
                                      '${restaurant.capacity} pers.',
                                      style: const TextStyle(
                                        fontSize: 12,
                                        color: AppTheme.textGray,
                                      ),
                                    ),
                                  ],
                                ),
                              const Icon(
                                Icons.arrow_forward_ios,
                                size: 14,
                                color: AppTheme.accentGold,
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildPlaceholder() {
    return Container(
      width: double.infinity,
      height: double.infinity,
      color: AppTheme.primaryBlue.withValues(alpha: 0.3),
      child: const Center(
        child: Icon(Icons.restaurant, size: 48, color: AppTheme.accentGold),
      ),
    );
  }
}
