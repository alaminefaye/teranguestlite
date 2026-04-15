import 'package:flutter/material.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../utils/haptic_helper.dart';
import '../../utils/layout_helper.dart';
import '../../utils/navigation_helper.dart';
import '../../widgets/service_card.dart';
import '../amenities/amenities_concierge_screen.dart';
import '../hotel_infos/gallery_screen.dart';
import '../hotel_infos/guides_screen.dart';
import '../laundry/laundry_list_screen.dart';

class RoomModuleScreen extends StatelessWidget {
  const RoomModuleScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final crossAxisCount = LayoutHelper.gridCrossAxisCount(context);
    final aspectRatio = LayoutHelper.dashboardCellAspectRatio(context);
    final spacing = LayoutHelper.gridSpacing(context);

    final items = [
      (
        l10n.guidesInfos,
        Icons.library_books_outlined,
        'assets/images/info_guides.png',
        () => context.navigateTo(const GuidesScreen()),
      ),
      (
        l10n.gallery,
        Icons.photo_library_outlined,
        'assets/images/info_galerie.png',
        () => context.navigateTo(const GalleryScreen()),
      ),
      (
        l10n.amenitiesConcierge,
        Icons.room_service_outlined,
        'assets/images/amenity_toiletries.png',
        () => context.navigateTo(const AmenitiesConciergeScreen()),
      ),
      (
        l10n.laundry,
        Icons.local_laundry_service_outlined,
        'assets/images/sub_laundry.png',
        () => context.navigateTo(const LaundryListScreen()),
      ),
    ];

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
                        Navigator.of(context).pop();
                      },
                    ),
                    const SizedBox(width: 12),
                    const Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            'Chambre',
                            style: TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                              color: AppTheme.accentGold,
                            ),
                          ),
                          SizedBox(height: 4),
                          Text(
                            'Informations & services',
                            style: TextStyle(
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
              Expanded(
                child: Padding(
                  padding: LayoutHelper.horizontalPadding(context),
                  child: GridView.builder(
                    padding: EdgeInsets.symmetric(vertical: spacing),
                    gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                      crossAxisCount: crossAxisCount,
                      crossAxisSpacing: spacing,
                      mainAxisSpacing: spacing,
                      childAspectRatio: aspectRatio,
                    ),
                    itemCount: items.length,
                    itemBuilder: (context, index) {
                      final (title, icon, image, onTap) = items[index];
                      return ServiceCard(
                        title: title,
                        icon: icon,
                        imagePath: image,
                        onTap: () {
                          HapticHelper.lightImpact();
                          onTap();
                        },
                      );
                    },
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
