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

/// Liste des activités d'une catégorie principale (Sport ou Loisirs).
/// Données dynamiques : enfants de la catégorie principale.
class LeisureSubListScreen extends StatelessWidget {
  const LeisureSubListScreen({
    super.key,
    required this.mainCategory,
  });

  final LeisureMainCategoryDto mainCategory;

  static IconData _iconForType(String type) {
    switch (type) {
      case 'spa':
        return Icons.spa_outlined;
      case 'golf_tennis':
        return Icons.sports_golf_outlined;
      case 'fitness':
        return Icons.fitness_center_outlined;
      default:
        return Icons.directions_run_outlined;
    }
  }

  void _onActivityTap(BuildContext context, LeisureCategoryDto child) {
    HapticHelper.lightImpact();
    final l10n = AppLocalizations.of(context);
    if (child.type == 'spa') {
      context.navigateTo(const SpaServicesListScreen());
    } else if (child.type == 'golf_tennis') {
      context.navigateTo(const GolfTennisScreen());
    } else if (child.type == 'fitness') {
      context.navigateTo(const SportFitnessScreen());
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(l10n.comingSoon),
          backgroundColor: AppTheme.accentGold,
          duration: const Duration(seconds: 2),
        ),
      );
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
                              icon: _iconForType(child.type),
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
