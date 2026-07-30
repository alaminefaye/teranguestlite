import 'package:flutter/material.dart';
import '../../config/api_config.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../models/guide.dart';
import '../../models/room.dart';
import '../../models/user.dart';
import '../../services/api_service.dart';
import '../../services/guides_api.dart';
import '../../utils/haptic_helper.dart';
import '../../utils/layout_helper.dart';
import '../../utils/navigation_helper.dart';
import '../../widgets/service_card.dart';
import '../common/in_app_document_screen.dart';
import '../hotel_infos/guide_items_screen.dart';
import '../hotel_infos/guides_screen.dart';
import '../hotel_infos/hotel_infos_screen.dart';
import 'room_category_detail_screen.dart';
import 'room_prices_screen.dart';

class RoomModuleScreen extends StatefulWidget {
  const RoomModuleScreen({super.key});

  @override
  State<RoomModuleScreen> createState() => _RoomModuleScreenState();
}

class _RoomModuleScreenState extends State<RoomModuleScreen> {
  List<GuideCategory>? _categories;
  List<RoomType>? _roomTypes;
  bool _loading = false;
  bool _roomsLoading = false;
  bool _checkingRoomBox = true;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _bootstrapRoomModule());
  }

  Future<void> _bootstrapRoomModule() async {
    try {
      final response = await ApiService().get(ApiConfig.vitrineEnterprise);
      final data = response.data;
      if (data is Map && data['success'] == true && data['data'] is Map) {
        final enterprise = Enterprise.fromJson(
          Map<String, dynamic>.from(data['data'] as Map),
        );
        final docUrl = enterprise.roomBoxDocumentUrl?.trim() ?? '';
        if (enterprise.roomBoxDisplayMode == 'document' && docUrl.isNotEmpty) {
          if (!mounted) return;
          Navigator.of(context).pushReplacement(
            MaterialPageRoute(
              builder: (_) =>
                  InAppDocumentScreen(title: 'Chambres et Suites', url: docUrl),
            ),
          );
          return;
        }
      }
    } catch (_) {
      // catalogue par défaut
    }
    if (!mounted) return;
    setState(() => _checkingRoomBox = false);
    _loadGuides();
    _loadRooms();
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

  Future<void> _loadRooms() async {
    if (_roomsLoading) return;
    setState(() => _roomsLoading = true);
    try {
      final response = await ApiService().get(ApiConfig.vitrineRooms);
      final data = response.data;
      if (data is Map && data['success'] == true && data['data'] is List) {
        final roomTypes = (data['data'] as List)
            .map((e) => RoomType.fromJson(e as Map<String, dynamic>))
            .toList();
        if (!mounted) return;
        setState(() => _roomTypes = roomTypes);
      }
    } catch (_) {
      if (!mounted) return;
      setState(() => _roomTypes = const []);
    } finally {
      if (mounted) setState(() => _roomsLoading = false);
    }
  }

  bool _isSuiteType(RoomType roomType) {
    final haystack =
        '${roomType.type} ${roomType.typeName} ${roomType.typeLabel}'
            .toLowerCase();
    return haystack.contains('suite') || haystack.contains('presidential');
  }

  String _buildCategoryDescription({
    required String title,
    required List<RoomType> roomTypes,
    required List<Room> rooms,
  }) {
    final uniqueDescriptions = rooms
        .map((room) => room.description?.trim() ?? '')
        .where((text) => text.isNotEmpty)
        .toSet()
        .toList();

    if (uniqueDescriptions.isNotEmpty) {
      return uniqueDescriptions.take(2).join('\n\n');
    }

    final labels = roomTypes
        .map(
          (type) => type.typeLabel.isNotEmpty ? type.typeLabel : type.typeName,
        )
        .where((text) => text.trim().isNotEmpty)
        .toSet()
        .toList();

    if (labels.isNotEmpty) {
      return '$title disponibles: ${labels.join(', ')}.';
    }

    return 'Découvrez nos $title disponibles dans l’application.';
  }

  List<RoomCategoryViewData> _buildRoomCategories() {
    final allTypes = _roomTypes ?? const <RoomType>[];
    final suiteTypes = allTypes.where(_isSuiteType).toList();
    final roomTypes = allTypes.where((type) => !_isSuiteType(type)).toList();

    RoomCategoryViewData buildCategory({
      required String title,
      required IconData icon,
      required List<RoomType> sourceTypes,
      required String fallbackImage,
    }) {
      final rooms = sourceTypes.expand((type) => type.rooms).toList();
      final gallery = rooms
          .expand((room) sync* {
            if (room.image?.trim().isNotEmpty ?? false) {
              yield room.image!.trim();
            }
            for (final image in room.galleryImages) {
              if (image.trim().isNotEmpty) {
                yield image.trim();
              }
            }
          })
          .where((image) => image.isNotEmpty)
          .toSet()
          .toList();

      return RoomCategoryViewData(
        title: title,
        icon: icon,
        imagePath: gallery.isNotEmpty ? gallery.first : fallbackImage,
        description: _buildCategoryDescription(
          title: title,
          roomTypes: sourceTypes,
          rooms: rooms,
        ),
        gallery: gallery,
        rooms: rooms,
      );
    }

    return [
      buildCategory(
        title: 'Chambres',
        icon: Icons.bed_outlined,
        sourceTypes: roomTypes,
        fallbackImage: 'assets/images/info_hotel.png',
      ),
      buildCategory(
        title: 'Suites',
        icon: Icons.king_bed_outlined,
        sourceTypes: suiteTypes,
        fallbackImage: 'assets/images/box_hotel_infos.png',
      ),
    ];
  }

  static final _defaultUsefulNumbersCategory = GuideCategory(
    id: -1,
    name: 'Numéros utiles',
    categoryType: 'useful_numbers',
    order: 1,
    isActive: true,
    items: [
      GuideItem(
        id: 1,
        categoryId: -1,
        title: 'BAR PISCINE',
        phone: '2010',
        order: 1,
        isActive: true,
      ),
      GuideItem(
        id: 2,
        categoryId: -1,
        title: 'BAR SAINT-LOUIS',
        phone: '2009',
        order: 2,
        isActive: true,
      ),
      GuideItem(
        id: 3,
        categoryId: -1,
        title: 'BASE NAUTIQ',
        phone: '2702',
        order: 3,
        isActive: true,
      ),
      GuideItem(
        id: 4,
        categoryId: -1,
        title: 'BOUTIQUE',
        phone: '2013',
        order: 4,
        isActive: true,
      ),
      GuideItem(
        id: 5,
        categoryId: -1,
        title: 'INFIRMERIE',
        phone: '2016',
        order: 5,
        isActive: true,
      ),
      GuideItem(
        id: 6,
        categoryId: -1,
        title: 'SALLE DE SPORT',
        phone: '2015',
        order: 6,
        isActive: true,
      ),
      GuideItem(
        id: 7,
        categoryId: -1,
        title: 'SDT EXCURSION',
        phone: '2008',
        order: 7,
        isActive: true,
      ),
      GuideItem(
        id: 8,
        categoryId: -1,
        title: 'RECEPTION',
        phone: '2500',
        order: 8,
        isActive: true,
      ),
      GuideItem(
        id: 9,
        categoryId: -1,
        title: 'RELATION CLIENTELE FRAM',
        phone: '2017',
        order: 9,
        isActive: true,
      ),
      GuideItem(
        id: 10,
        categoryId: -1,
        title: 'ROOM SERVICE',
        phone: '2408',
        order: 10,
        isActive: true,
      ),
      GuideItem(
        id: 11,
        categoryId: -1,
        title: 'SPA',
        phone: '2012',
        order: 11,
        isActive: true,
      ),
    ],
  );

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
    if (_checkingRoomBox) {
      return Scaffold(
        body: Container(
          decoration: const BoxDecoration(
            gradient: AppTheme.backgroundGradient,
          ),
          child: const SafeArea(
            child: Center(
              child: CircularProgressIndicator(
                valueColor: AlwaysStoppedAnimation<Color>(AppTheme.accentGold),
              ),
            ),
          ),
        ),
      );
    }

    final l10n = AppLocalizations.of(context);
    final crossAxisCount = LayoutHelper.gridCrossAxisCount(context);
    final aspectRatio = LayoutHelper.dashboardCellAspectRatio(context);
    final spacing = LayoutHelper.gridSpacing(context);
    final roomCategories = _buildRoomCategories();

    final categories = _categories ?? <GuideCategory>[];
    final equipmentCategory = _findCategory(
      categories: categories,
      types: const ['equipment_guide', 'equipment', 'guide_equipment'],
      keywords: const ['équip', 'equip', 'utilisation', 'guide'],
    );
    final numbersCategory = _findCategory(
      categories: categories,
      types: const ['useful_numbers', 'numbers', 'contacts'],
      keywords: const ['useful', 'utiles'],
    );

    final items = [
      (
        'Guide utilisation équipements',
        Icons.menu_book_outlined,
        'assets/images/info_hotel.png',
        () => equipmentCategory != null
            ? context.navigateTo(GuideItemsScreen(category: equipmentCategory))
            : context.navigateTo(const GuidesScreen()),
      ),
      (
        'Numéros utiles',
        Icons.phone_in_talk_outlined,
        'assets/images/info_urgence.png',
        () {
          final cat =
              (numbersCategory != null &&
                  (numbersCategory.items?.isNotEmpty ?? false))
              ? numbersCategory
              : _defaultUsefulNumbersCategory;
          context.navigateTo(GuideItemsScreen(category: cat));
        },
      ),
      (
        l10n.practicalInfo,
        Icons.info_outline_rounded,
        'assets/images/info_pratique.png',
        () => context.navigateTo(const HotelInfosScreen()),
      ),
      (
        l10n.tarifs,
        Icons.attach_money_outlined,
        'assets/images/info_tarifs.png',
        () => context.navigateTo(const RoomPricesScreen()),
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
                            'Chambres et Suites',
                            style: TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                              color: AppTheme.accentGold,
                            ),
                          ),
                          SizedBox(height: 4),
                          Text(
                            'Chambres, suites, galerie et infos pratiques',
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
                  child: SingleChildScrollView(
                    padding: EdgeInsets.symmetric(vertical: spacing),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Padding(
                          padding: EdgeInsets.only(bottom: 12),
                          child: Text(
                            'Hébergements',
                            style: TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                              color: AppTheme.accentGold,
                            ),
                          ),
                        ),
                        GridView.builder(
                          shrinkWrap: true,
                          physics: const NeverScrollableScrollPhysics(),
                          gridDelegate:
                              SliverGridDelegateWithFixedCrossAxisCount(
                                crossAxisCount: crossAxisCount,
                                crossAxisSpacing: spacing,
                                mainAxisSpacing: spacing,
                                childAspectRatio: aspectRatio,
                              ),
                          itemCount: roomCategories.length,
                          itemBuilder: (context, index) {
                            final category = roomCategories[index];
                            return ServiceCard(
                              title: category.title,
                              icon: category.icon,
                              imagePath: category.imagePath,
                              isLoading: _roomsLoading,
                              onTap: () {
                                if (_roomsLoading) return;
                                HapticHelper.lightImpact();
                                context.navigateTo(
                                  RoomCategoryDetailScreen(category: category),
                                );
                              },
                            );
                          },
                        ),
                        const SizedBox(height: 24),
                        const Padding(
                          padding: EdgeInsets.only(bottom: 12),
                          child: Text(
                            'Services utiles',
                            style: TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                              color: AppTheme.accentGold,
                            ),
                          ),
                        ),
                        GridView.builder(
                          shrinkWrap: true,
                          physics: const NeverScrollableScrollPhysics(),
                          gridDelegate:
                              SliverGridDelegateWithFixedCrossAxisCount(
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
                      ],
                    ),
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
