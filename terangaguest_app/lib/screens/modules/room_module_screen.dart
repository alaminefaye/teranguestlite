import 'package:flutter/material.dart';
import '../../config/api_config.dart';
import '../../config/theme.dart';
import '../../models/room.dart';
import '../../models/room_gallery.dart';
import '../../models/user.dart';
import '../../services/api_service.dart';
import '../../utils/haptic_helper.dart';
import '../../utils/layout_helper.dart';
import '../../utils/navigation_helper.dart';
import '../../widgets/service_card.dart';
import '../common/in_app_document_screen.dart';
import 'room_category_detail_screen.dart';
import 'room_gallery_screen.dart';

class RoomModuleScreen extends StatefulWidget {
  const RoomModuleScreen({super.key});

  @override
  State<RoomModuleScreen> createState() => _RoomModuleScreenState();
}

class _RoomModuleScreenState extends State<RoomModuleScreen> {
  List<RoomType>? _roomTypes;
  List<RoomGalleryCategory>? _galleryCategories;
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

    // Charger les photos de la galerie hébergements (Chambres & Suites)
    try {
      final galleryResponse =
          await ApiService().get(ApiConfig.vitrineRoomGallery);
      final galleryData = galleryResponse.data;
      if (galleryData is Map &&
          galleryData['success'] == true &&
          galleryData['data'] is List) {
        final categories = (galleryData['data'] as List)
            .map((e) =>
                RoomGalleryCategory.fromJson(e as Map<String, dynamic>))
            .where((cat) => cat.hasPhotos)
            .toList();

        if (categories.isNotEmpty && mounted) {
          setState(() => _galleryCategories = categories);
        }
      }
    } catch (_) {
      // ignore
    }

    if (!mounted) return;
    setState(() => _checkingRoomBox = false);
    _loadRooms();
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
      final isChambre = title.toLowerCase().contains('chambre');
      final targetType = isChambre ? 'chambre' : 'suite';

      final roomGalleryCat = _galleryCategories?.firstWhere(
        (c) => c.type == targetType && c.hasPhotos,
        orElse: () => RoomGalleryCategory(
            type: '', typeLabel: '', total: 0, photos: []),
      );

      final galleryPhotoUrls =
          roomGalleryCat?.photos.map((p) => p.url).toList() ?? [];

      final rooms = sourceTypes.expand((type) => type.rooms).toList();
      final gallery = [
        ...galleryPhotoUrls,
        ...rooms
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
            .where((image) => image.isNotEmpty),
      ].toSet().toList();

      final String coverImage = galleryPhotoUrls.isNotEmpty
          ? galleryPhotoUrls.first
          : (gallery.isNotEmpty ? gallery.first : fallbackImage);

      return RoomCategoryViewData(
        title: title,
        icon: icon,
        imagePath: coverImage,
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

    final crossAxisCount = LayoutHelper.gridCrossAxisCount(context);
    final aspectRatio = LayoutHelper.dashboardCellAspectRatio(context);
    final spacing = LayoutHelper.gridSpacing(context);
    final roomCategories = _buildRoomCategories();

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

                                final isChambre = category.title
                                    .toLowerCase()
                                    .contains('chambre');
                                final targetType =
                                    isChambre ? 'chambre' : 'suite';

                                final galleryCat =
                                    _galleryCategories?.firstWhere(
                                  (c) => c.type == targetType && c.hasPhotos,
                                  orElse: () => RoomGalleryCategory(
                                      type: '',
                                      typeLabel: '',
                                      total: 0,
                                      photos: []),
                                );

                                if (galleryCat != null &&
                                    galleryCat.hasPhotos &&
                                    _galleryCategories != null &&
                                    _galleryCategories!.isNotEmpty) {
                                  final galleryIndex =
                                      _galleryCategories!.indexOf(galleryCat);
                                  context.navigateTo(
                                    RoomGalleryScreen(
                                      categories: _galleryCategories!,
                                      initialTabIndex:
                                          galleryIndex >= 0 ? galleryIndex : 0,
                                    ),
                                  );
                                } else {
                                  context.navigateTo(
                                    RoomCategoryDetailScreen(
                                        category: category),
                                  );
                                }
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
