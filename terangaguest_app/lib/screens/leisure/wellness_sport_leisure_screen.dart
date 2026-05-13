import 'package:flutter/material.dart';
import '../../config/api_config.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../models/user.dart';
import '../../services/api_service.dart';
import '../../utils/navigation_helper.dart';
import '../../utils/haptic_helper.dart';
import '../../utils/layout_helper.dart';
import '../../widgets/service_card.dart';
import '../../models/leisure_category.dart';
import '../../services/leisure_api.dart';
import '../common/in_app_document_screen.dart';
import 'leisure_sub_list_screen.dart';

/// Écran « BIEN-ÊTRE, SPORT & LOISIRS » : 2 boxes (Sport, Loisirs). Données dynamiques depuis l'API.
/// Données dynamiques depuis l’API (leisure-categories).
class WellnessSportLeisureScreen extends StatefulWidget {
  final String? onlyMainType;
  final String? titleOverride;
  final String? subtitleOverride;

  const WellnessSportLeisureScreen({
    super.key,
    this.onlyMainType,
    this.titleOverride,
    this.subtitleOverride,
  });

  @override
  State<WellnessSportLeisureScreen> createState() =>
      _WellnessSportLeisureScreenState();
}

class _WellnessSportLeisureScreenState
    extends State<WellnessSportLeisureScreen> {
  List<LeisureMainCategoryDto>? _mainCategories;
  Enterprise? _enterprise;
  bool _loading = true;
  bool _autoNavigated = false;

  static IconData _iconForMainType(String type) {
    return type == 'sport' ? Icons.sports_soccer_outlined : Icons.spa_outlined;
  }

  static String? _imageForMainType(String type) {
    if (type == 'sport') return 'assets/images/sub_sport.png';
    if (type == 'loisirs') return 'assets/images/sub_loisirs.png';
    return null;
  }

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _loadAll());
  }

  Future<void> _loadAll() async {
    // Charger en parallèle : catégories + settings enterprise (pour mode document)
    await Future.wait([_loadCategories(), _loadEnterprise()]);
  }

  Future<void> _loadCategories() async {
    try {
      final list = await LeisureApi().getCategories();
      if (mounted) {
        setState(() {
          _mainCategories = list;
        });
      }
    } catch (_) {
      if (mounted) {
        setState(() {
          _mainCategories = null;
        });
      }
    }
  }

  Future<void> _loadEnterprise() async {
    try {
      final response = await ApiService().get(ApiConfig.vitrineEnterprise);
      final data = response.data;
      if (data is Map && data['success'] == true && data['data'] is Map) {
        final ent = Enterprise.fromJson(
          Map<String, dynamic>.from(data['data'] as Map),
        );
        if (mounted) setState(() => _enterprise = ent);
      }
    } catch (_) {}
    if (mounted) setState(() => _loading = false);
  }

  /// Retourne true si le type donné doit ouvrir un document (PDF/image)
  bool _isDocumentMode(String type) {
    if (_enterprise == null) return false;
    if (type == 'sport') {
      return _enterprise!.sportDisplayMode == 'document' &&
          _enterprise!.sportDocumentUrl != null &&
          _enterprise!.sportDocumentUrl!.isNotEmpty;
    }
    return false;
  }

  /// Ouvre le document associé à un type de catégorie
  void _openDocument(BuildContext context, String type, String title) {
    String? url;
    if (type == 'sport') url = _enterprise?.sportDocumentUrl;
    if (url == null || url.isEmpty) return;
    context.navigateTo(InAppDocumentScreen(title: title, url: url));
  }

  void _onMainCategoryTap(BuildContext context, LeisureMainCategoryDto mainCat, String cardTitle) {
    HapticHelper.lightImpact();
    if (_isDocumentMode(mainCat.type)) {
      _openDocument(context, mainCat.type, cardTitle);
    } else {
      context.navigateTo(LeisureSubListScreen(mainCategory: mainCat));
    }
  }

  static List<LeisureMainCategoryDto> _fallbackMainCategories(
    AppLocalizations l10n,
  ) {
    return [
      LeisureMainCategoryDto(
        id: 0,
        name: l10n.sportCategory,
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
        name: l10n.leisureCategory,
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

    var list = (_mainCategories != null && _mainCategories!.isNotEmpty)
        ? _mainCategories!
        : _fallbackMainCategories(l10n);

    final only = widget.onlyMainType?.trim();
    if (only != null && only.isNotEmpty) {
      list = list.where((c) => c.type == only).toList();
    }

    if (!_loading &&
        !_autoNavigated &&
        only != null &&
        only.isNotEmpty &&
        list.length == 1) {
      final mainCat = list.first;
      _autoNavigated = true;
      WidgetsBinding.instance.addPostFrameCallback((_) {
        if (!mounted) return;
        // Si mode document activé → ouvrir le PDF directement
        if (_isDocumentMode(mainCat.type)) {
          final title = widget.titleOverride ?? mainCat.name;
          NavigationHelper.replaceWith(
            context,
            InAppDocumentScreen(
              title: title,
              url: _enterprise!.sportDocumentUrl!,
            ),
          );
        } else {
          final mainCatForNav = LeisureMainCategoryDto(
            id: mainCat.id,
            name: widget.titleOverride ?? mainCat.name,
            description: widget.subtitleOverride ?? mainCat.description,
            type: mainCat.type,
            displayOrder: mainCat.displayOrder,
            children: mainCat.children,
          );
          NavigationHelper.replaceWith(
            context,
            LeisureSubListScreen(mainCategory: mainCatForNav),
          );
        }
      });
    }

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
                            final cardTitle = mainCat.name.trim().isNotEmpty
                                ? mainCat.name
                                : (mainCat.type == 'sport'
                                    ? l10n.sportCategory
                                    : l10n.leisureCategory);
                            return ServiceCard(
                              title: cardTitle,
                              icon: _iconForMainType(mainCat.type),
                              imagePath: _imageForMainType(mainCat.type),
                              onTap: () => _onMainCategoryTap(context, mainCat, cardTitle),
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
                  widget.titleOverride ?? l10n.wellnessSportLeisure,
                  style: TextStyle(
                    fontSize: MediaQuery.of(context).size.width < 600 ? 18 : 28,
                    fontWeight: FontWeight.bold,
                    color: AppTheme.accentGold,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  widget.subtitleOverride ?? l10n.wellnessSportLeisureSubtitle,
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
