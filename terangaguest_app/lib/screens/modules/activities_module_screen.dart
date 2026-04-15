import 'package:flutter/material.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../utils/haptic_helper.dart';
import '../../utils/layout_helper.dart';
import '../../utils/navigation_helper.dart';
import '../../widgets/service_card.dart';
import '../excursions/excursions_list_screen.dart';
import '../leisure/wellness_sport_leisure_screen.dart';
import '../spa/spa_services_list_screen.dart';

class ActivitiesModuleScreen extends StatelessWidget {
  const ActivitiesModuleScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final crossAxisCount = LayoutHelper.gridCrossAxisCount(context);
    final aspectRatio = LayoutHelper.dashboardCellAspectRatio(context);
    final spacing = LayoutHelper.gridSpacing(context);

    final modules = [
      (
        l10n.spaWellness,
        Icons.spa_outlined,
        'assets/images/box_wellness.png',
        () => context.navigateTo(const SpaServicesListScreen()),
      ),
      (
        l10n.wellnessSportLeisure,
        Icons.fitness_center_outlined,
        'assets/images/box_wellness.png',
        () => context.navigateTo(const WellnessSportLeisureScreen()),
      ),
      (
        l10n.excursions,
        Icons.map_outlined,
        'assets/images/box_excursion.png',
        () => context.navigateTo(const ExcursionsListScreen()),
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
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            'Activités',
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
                            l10n.wellnessSportLeisureSubtitle,
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
                    itemCount: modules.length,
                    itemBuilder: (context, index) {
                      final (title, icon, image, onTap) = modules[index];
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
