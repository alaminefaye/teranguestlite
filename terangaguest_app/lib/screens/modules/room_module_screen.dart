import 'package:flutter/material.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../models/guide.dart';
import '../../services/guides_api.dart';
import '../../utils/haptic_helper.dart';
import '../../utils/layout_helper.dart';
import '../../utils/navigation_helper.dart';
import '../../widgets/service_card.dart';
import '../hotel_infos/guide_items_screen.dart';
import '../hotel_infos/guides_screen.dart';
import '../hotel_infos/hotel_infos_screen.dart';

class RoomModuleScreen extends StatefulWidget {
  const RoomModuleScreen({super.key});

  @override
  State<RoomModuleScreen> createState() => _RoomModuleScreenState();
}

class _RoomModuleScreenState extends State<RoomModuleScreen> {
  List<GuideCategory>? _categories;
  bool _loading = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadGuides();
    });
  }

  Future<void> _loadGuides() async {
    if (_loading) return;
    setState(() => _loading = true);
    try {
      final cats = await GuidesApi().getGuides();
      if (!mounted) return;
      setState(() => _categories = cats);
    } catch (_) {
      if (!mounted) return;
      setState(() => _categories = const []);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  GuideCategory? _findCategory({
    required List<GuideCategory> categories,
    required List<String> types,
    required List<String> keywords,
  }) {
    for (final t in types) {
      final found = categories.firstWhere(
        (c) => (c.categoryType ?? '').toLowerCase() == t.toLowerCase(),
        orElse: () => GuideCategory(
          id: -1,
          name: '',
          order: 0,
          isActive: false,
          items: const [],
        ),
      );
      if (found.id != -1) return found;
    }
    for (final k in keywords) {
      final key = k.toLowerCase();
      final found = categories.firstWhere(
        (c) => c.name.toLowerCase().contains(key),
        orElse: () => GuideCategory(
          id: -1,
          name: '',
          order: 0,
          isActive: false,
          items: const [],
        ),
      );
      if (found.id != -1) return found;
    }
    return null;
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final crossAxisCount = LayoutHelper.gridCrossAxisCount(context);
    final aspectRatio = LayoutHelper.dashboardCellAspectRatio(context);
    final spacing = LayoutHelper.gridSpacing(context);

    final categories = _categories ?? <GuideCategory>[];
    final equipmentCategory = _findCategory(
      categories: categories,
      types: const ['equipment_guide', 'equipment', 'guide_equipment'],
      keywords: const ['équip', 'equip', 'utilisation', 'guide'],
    );
    final numbersCategory = _findCategory(
      categories: categories,
      types: const ['useful_numbers', 'numbers', 'contacts'],
      keywords: const ['num', 'urgence', 'contact', 'appel'],
    );

    final items = [
      (
        'Guide utilisation équipements',
        Icons.menu_book_outlined,
        'assets/images/info_guides.png',
        () => equipmentCategory != null
            ? context.navigateTo(GuideItemsScreen(category: equipmentCategory))
            : context.navigateTo(const GuidesScreen()),
      ),
      (
        'Numéros utiles',
        Icons.phone_in_talk_outlined,
        'assets/images/info_urgence.png',
        () => numbersCategory != null
            ? context.navigateTo(GuideItemsScreen(category: numbersCategory))
            : context.navigateTo(const GuidesScreen()),
      ),
      (
        l10n.practicalInfo,
        Icons.info_outline_rounded,
        'assets/images/info_pratique.png',
        () => context.navigateTo(const HotelInfosScreen()),
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
                            'Guide, numéros & infos pratiques',
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
