import 'package:flutter/material.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../utils/navigation_helper.dart';
import '../../utils/haptic_helper.dart';
import '../../utils/layout_helper.dart';
import '../../widgets/service_card.dart';
import '../../models/leisure_category.dart';
import '../../services/leisure_api.dart';
import 'leisure_sub_list_screen.dart';

/// Écran « BIEN-ÊTRE, SPORT & LOISIRS » : 2 boxes (Sport, Loisirs). Données dynamiques depuis l'API.
/// Données dynamiques depuis l’API (leisure-categories).
class WellnessSportLeisureScreen extends StatefulWidget {
  const WellnessSportLeisureScreen({super.key});

  @override
  State<WellnessSportLeisureScreen> createState() =>
      _WellnessSportLeisureScreenState();
}

class _WellnessSportLeisureScreenState
    extends State<WellnessSportLeisureScreen> {
  List<LeisureMainCategoryDto>? _mainCategories;
  bool _loading = true;

  static IconData _iconForMainType(String type) {
    return type == 'sport' ? Icons.sports_soccer_outlined : Icons.spa_outlined;
  }

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _loadCategories());
  }

  Future<void> _loadCategories() async {
    try {
      final list = await LeisureApi().getCategories();
      if (mounted) {
        setState(() {
          _mainCategories = list;
          _loading = false;
        });
      }
    } catch (_) {
      if (mounted) {
        setState(() {
          _mainCategories = null;
          _loading = false;
        });
      }
    }
  }

  static List<LeisureMainCategoryDto> _fallbackMainCategories(
    AppLocalizations l10n,
  ) {
    return [
      LeisureMainCategoryDto(
        id: 0,
        name: 'Sport',
        description: null,
        type: 'sport',
        displayOrder: 0,
        children: [
          LeisureCategoryDto(
            id: 1,
            name: l10n.golfTitle,
            description: null,
            type: 'golf',
            displayOrder: 0,
          ),
          LeisureCategoryDto(
            id: 2,
            name: l10n.tennisTitle,
            description: null,
            type: 'tennis',
            displayOrder: 1,
          ),
          LeisureCategoryDto(
            id: 3,
            name: l10n.sportFitnessTitle,
            description: null,
            type: 'fitness',
            displayOrder: 2,
          ),
        ],
      ),
      LeisureMainCategoryDto(
        id: 0,
        name: 'Loisirs',
        description: null,
        type: 'loisirs',
        displayOrder: 1,
        children: [
          LeisureCategoryDto(
            id: 4,
            name: l10n.spaWellness,
            description: null,
            type: 'spa',
            displayOrder: 0,
          ),
        ],
      ),
    ];
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final crossAxisCount = LayoutHelper.gridCrossAxisCount(context);
    final aspectRatio = LayoutHelper.dashboardCellAspectRatio(context);
    final spacing = LayoutHelper.gridSpacing(context);

    final list = (_mainCategories != null && _mainCategories!.isNotEmpty)
        ? _mainCategories!
        : _fallbackMainCategories(l10n);

    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(gradient: AppTheme.backgroundGradient),
        child: SafeArea(
          child: Column(
            children: [
              _buildAppBar(context, l10n),
              Expanded(
                child: _loading
                    ? const Center(
                        child: CircularProgressIndicator(
                          valueColor: AlwaysStoppedAnimation<Color>(
                            AppTheme.accentGold,
                          ),
                        ),
                      )
                    : Padding(
                        padding: LayoutHelper.horizontalPadding(context),
                        child: GridView.builder(
                          padding: EdgeInsets.symmetric(vertical: spacing),
                          gridDelegate:
                              SliverGridDelegateWithFixedCrossAxisCount(
                                crossAxisCount: crossAxisCount,
                                crossAxisSpacing: spacing,
                                mainAxisSpacing: spacing,
                                childAspectRatio: aspectRatio,
                              ),
                          itemCount: list.length,
                          itemBuilder: (context, index) {
                            final mainCat = list[index];
                            return ServiceCard(
                              title: mainCat.name,
                              icon: _iconForMainType(mainCat.type),
                              onTap: () {
                                HapticHelper.lightImpact();
                                context.navigateTo(
                                  LeisureSubListScreen(mainCategory: mainCat),
                                );
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

  Widget _buildAppBar(BuildContext context, AppLocalizations l10n) {
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
                  l10n.wellnessSportLeisure,
                  style: TextStyle(
                    fontSize: MediaQuery.of(context).size.width < 600 ? 18 : 28,
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
    );
  }
}
