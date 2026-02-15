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

class _WellnessSportLeisureScreenState extends State<WellnessSportLeisureScreen> {
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

  static List<LeisureMainCategoryDto> _fallbackMainCategories(AppLocalizations l10n) {
    return [
      LeisureMainCategoryDto(
        id: 0,
        name: 'Sport',
        description: null,
        type: 'sport',
        displayOrder: 0,
        children: [
          LeisureCategoryDto(id: 1, name: l10n.golfTennisTitle, description: null, type: 'golf_tennis', displayOrder: 0),
          LeisureCategoryDto(id: 2, name: l10n.sportFitnessTitle, description: null, type: 'fitness', displayOrder: 1),
        ],
      ),
      LeisureMainCategoryDto(
        id: 0,
        name: 'Loisirs',
        description: null,
        type: 'loisirs',
        displayOrder: 1,
        children: [
          LeisureCategoryDto(id: 3, name: l10n.spaWellness, description: null, type: 'spa', displayOrder: 0),
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
        decoration: const BoxDecoration(
          gradient: AppTheme.backgroundGradient,
        ),
        child: SafeArea(
          child: Column(
            children: [
              _buildAppBar(context, l10n),
              Expanded(
                child: _loading
                    ? const Center(
                        child: CircularProgressIndicator(
                          valueColor: AlwaysStoppedAnimation<Color>(AppTheme.accentGold),
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
                          itemCount: list.length,
                          itemBuilder: (context, index) {
                            final mainCat = list[index];
                            return ServiceCard(
                              title: mainCat.name,
                              icon: _iconForMainType(mainCat.type),
                              onTap: () {
                                HapticHelper.lightImpact();
                                context.navigateTo(LeisureSubListScreen(mainCategory: mainCat));
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
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 12),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Row(
            children: [
              IconButton(
                onPressed: () {
                  HapticHelper.lightImpact();
                  Navigator.of(context).pop();
                },
                icon: const Icon(Icons.arrow_back_ios_new, color: AppTheme.accentGold),
              ),
              Expanded(
                child: Text(
                  l10n.wellnessSportLeisure,
                  textAlign: TextAlign.center,
                  style: const TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.w800,
                    color: AppTheme.accentGold,
                    letterSpacing: 0.5,
                  ),
                ),
              ),
              const SizedBox(width: 48),
            ],
          ),
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 0, 16, 12),
            child: Text(
              l10n.wellnessSportLeisureSubtitle,
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 13,
                color: AppTheme.textGray,
                height: 1.3,
              ),
            ),
          ),
        ],
      ),
    );
  }
}
