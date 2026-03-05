import 'package:flutter/material.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../utils/navigation_helper.dart';
import '../../utils/haptic_helper.dart';
import '../../utils/layout_helper.dart';
import '../../widgets/service_card.dart';
import '../room_service/categories_screen.dart';
import '../laundry/laundry_list_screen.dart';
import '../amenities/amenities_concierge_screen.dart';

/// Écran hub « SERVICES EN CHAMBRE & LOGISTIQUE » avec 4 sous-catégories en boxes
/// (même design que le dashboard) : Room Service & Restauration, Blanchisserie,
/// Amenities & Conciergerie, Mini-bar Intelligent.
class RoomAndLogisticsScreen extends StatelessWidget {
  const RoomAndLogisticsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);

    final subServices = [
      (
        l10n.roomServiceRestauration,
        Icons.room_service_outlined,
        'assets/images/box_room_service.png',
        () {
          HapticHelper.lightImpact();
          context.navigateTo(const CategoriesScreen());
        },
      ),
      (
        l10n.laundry,
        Icons.local_laundry_service_outlined,
        'assets/images/sub_laundry.png',
        () {
          HapticHelper.lightImpact();
          context.navigateTo(const LaundryListScreen());
        },
      ),
      (
        l10n.amenitiesConcierge,
        Icons.bedroom_child_outlined,
        'assets/images/box_autres_services.png',
        () {
          HapticHelper.lightImpact();
          context.navigateTo(const AmenitiesConciergeScreen());
        },
      ),
    ];

    final crossAxisCount = LayoutHelper.gridCrossAxisCount(context);
    final aspectRatio = LayoutHelper.dashboardCellAspectRatio(context);
    final spacing = LayoutHelper.gridSpacing(context);

    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(gradient: AppTheme.backgroundGradient),
        child: SafeArea(
          child: Column(
            children: [
              _buildAppBar(context, l10n.servicesChambreLogistique),
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
                    itemCount: subServices.length,
                    itemBuilder: (context, index) {
                      final (title, icon, image, onTap) = subServices[index];
                      return ServiceCard(
                        title: title,
                        icon: icon,
                        imagePath: image,
                        onTap: onTap,
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

  Widget _buildAppBar(BuildContext context, String title) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 12),
      child: Row(
        children: [
          IconButton(
            onPressed: () {
              HapticHelper.lightImpact();
              Navigator.of(context).pop();
            },
            icon: const Icon(
              Icons.arrow_back_ios_new,
              color: AppTheme.accentGold,
            ),
          ),
          Expanded(
            child: Text(
              title,
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.w800,
                color: AppTheme.accentGold,
                letterSpacing: 0.5,
              ),
            ),
          ),
          const SizedBox(width: 48),
        ],
      ),
    );
  }
}
