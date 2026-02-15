import 'package:flutter/material.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../utils/navigation_helper.dart';
import '../../utils/haptic_helper.dart';
import '../../utils/layout_helper.dart';
import '../../widgets/service_card.dart';
import '../../models/leisure_category.dart';
import '../spa/spa_services_list_screen.dart';
import 'golf_tennis_screen.dart';
import 'sport_fitness_screen.dart';
import 'leisure_request_screen.dart';

/// Liste des activités d'une catégorie principale (Sport ou Loisirs).
/// Données dynamiques : enfants de la catégorie principale.
class LeisureSubListScreen extends StatelessWidget {
  const LeisureSubListScreen({
    super.key,
    required this.mainCategory,
  });

  final LeisureMainCategoryDto mainCategory;

  static IconData _iconForActivity(LeisureCategoryDto child) {
    switch (child.type) {
      case 'spa':
        return Icons.spa_outlined;
      case 'golf':
        return Icons.sports_golf_outlined;
      case 'tennis':
        return Icons.sports_tennis_outlined;
      case 'fitness':
        return Icons.fitness_center_outlined;
      case 'other':
        return _iconForOtherByName(child.name);
      default:
        return _iconForOtherByName(child.name);
    }
  }

  static IconData _iconForOtherByName(String name) {
    final n = name.toLowerCase();
    if (n.contains('squash')) return Icons.sports_tennis_outlined;
    if (n.contains('piscine') || n.contains('pool')) return Icons.pool_outlined;
    if (n.contains('yoga') || n.contains('pilates')) return Icons.self_improvement_outlined;
    if (n.contains('aquagym') || n.contains('natation')) return Icons.pool_outlined;
    if (n.contains('running')) return Icons.directions_run_outlined;
    if (n.contains('vtt') || n.contains('vélo')) return Icons.directions_bike_outlined;
    if (n.contains('beach') || n.contains('volley')) return Icons.sports_volleyball_outlined;
    if (n.contains('cours collectifs') || n.contains('groupe')) return Icons.groups_outlined;
    if (n.contains('hammam') || n.contains('sauna')) return Icons.thermostat_outlined;
    if (n.contains('excursion') || n.contains('découverte')) return Icons.explore_outlined;
    if (n.contains('foot') || n.contains('football')) return Icons.sports_soccer_outlined;
    if (n.contains('basket')) return Icons.sports_basketball_outlined;
    return Icons.directions_run_outlined;
  }

  void _onActivityTap(BuildContext context, LeisureCategoryDto child) {
    HapticHelper.lightImpact();
    if (child.type == 'spa') {
      context.navigateTo(const SpaServicesListScreen());
    } else if (child.type == 'golf' || child.type == 'golf_tennis') {
      context.navigateTo(const GolfTennisScreen(sportType: 'golf'));
    } else if (child.type == 'tennis') {
      context.navigateTo(const GolfTennisScreen(sportType: 'tennis'));
    } else if (child.type == 'fitness') {
      context.navigateTo(const SportFitnessScreen());
    } else {
      context.navigateTo(LeisureRequestScreen(activity: child));
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final crossAxisCount = LayoutHelper.gridCrossAxisCount(context);
    final aspectRatio = LayoutHelper.dashboardCellAspectRatio(context);
    final spacing = LayoutHelper.gridSpacing(context);
    final children = mainCategory.children;

    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(
          gradient: AppTheme.backgroundGradient,
        ),
        child: SafeArea(
          child: Column(
            children: [
              _buildAppBar(context),
              Expanded(
                child: children.isEmpty
                    ? Center(
                        child: Padding(
                          padding: const EdgeInsets.all(24),
                          child: Text(
                            l10n.comingSoon,
                            textAlign: TextAlign.center,
                            style: const TextStyle(
                              color: AppTheme.textGray,
                              fontSize: 15,
                            ),
                          ),
                        ),
                      )
                    : Padding(
                        padding: LayoutHelper.horizontalPadding(context),
                        child: GridView.builder(
                          padding: EdgeInsets.symmetric(vertical: spacing),
                          gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                            crossAxisCount: crossAxisCount,
                            crossAxisSpacing: spacing,
                            mainAxisSpacing: spacing,
                            childAspectRatio: aspectRatio,
                          ),
                          itemCount: children.length,
                          itemBuilder: (context, index) {
                            final child = children[index];
                            return ServiceCard(
                              title: child.name,
                              icon: _iconForActivity(child),
                              onTap: () => _onActivityTap(context, child),
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

  Widget _buildAppBar(BuildContext context) {
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
                  mainCategory.name,
                  style: const TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.bold,
                    color: Colors.white,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  mainCategory.description ?? '',
                  style: const TextStyle(
                    fontSize: 13,
                    color: AppTheme.textGray,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
