import 'package:flutter/material.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../utils/navigation_helper.dart';
import '../../utils/haptic_helper.dart';
import '../../utils/layout_helper.dart';
import '../../widgets/service_card.dart';
import '../exploration/vehicle_list_screen.dart';
import '../excursions/excursions_list_screen.dart';
import '../exploration/guided_tours_request_screen.dart';
import '../exploration/transfers_request_screen.dart';

/// Hub « EXPLORATION & MOBILITÉ » : Location véhicule, Découverte & Sites touristiques,
/// Visites guidées, Transferts & VTC.
class ExplorationMobilityScreen extends StatelessWidget {
  const ExplorationMobilityScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);

    final subServices = [
      (
        l10n.vehicleRental,
        Icons.directions_car_outlined,
        () {
          HapticHelper.lightImpact();
          context.navigateTo(const VehicleListScreen());
        },
      ),
      (
        l10n.sitesTouristiques,
        Icons.place_outlined,
        () {
          HapticHelper.lightImpact();
          context.navigateTo(const ExcursionsListScreen());
        },
      ),
      (
        l10n.guidedTours,
        Icons.tour_outlined,
        () {
          HapticHelper.lightImpact();
          context.navigateTo(const GuidedToursRequestScreen());
        },
      ),
      (
        l10n.transfersVtc,
        Icons.local_taxi_outlined,
        () {
          HapticHelper.lightImpact();
          context.navigateTo(const TransfersRequestScreen());
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
              _buildAppBar(
                context,
                l10n.explorationMobility,
                l10n.explorationMobilitySubtitle,
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
                    itemCount: subServices.length,
                    itemBuilder: (context, index) {
                      final (title, icon, onTap) = subServices[index];
                      return ServiceCard(
                        title: title,
                        icon: icon,
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

  Widget _buildAppBar(BuildContext context, String title, String subtitle) {
    return Padding(
      padding: const EdgeInsets.all(20.0),
      child: Row(
        children: [
          IconButton(
            icon: const Icon(Icons.arrow_back, color: AppTheme.accentGold),
            onPressed: () {
              HapticHelper.lightImpact();
              Navigator.of(context).pop();
            },
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  title,
                  style: TextStyle(
                    fontSize: MediaQuery.of(context).size.width < 600 ? 18 : 28,
                    fontWeight: FontWeight.bold,
                    color: AppTheme.accentGold,
                  ),
                ),
                if (subtitle.isNotEmpty) ...[
                  const SizedBox(height: 4),
                  Text(
                    subtitle,
                    style: const TextStyle(
                      fontSize: 14,
                      color: AppTheme.textGray,
                    ),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }
}
