/// Modèles pour la galerie hébergements (Chambres & Suites).
/// Correspond à l'endpoint GET /vitrine/rooms/gallery

class RoomGalleryPhoto {
  final int id;
  final String? title;
  final String? description;
  final String url;
  final String? originalExtension;
  final int displayOrder;

  const RoomGalleryPhoto({
    required this.id,
    required this.url,
    this.title,
    this.description,
    this.originalExtension,
    required this.displayOrder,
  });

  factory RoomGalleryPhoto.fromJson(Map<String, dynamic> json) {
    return RoomGalleryPhoto(
      id: json['id'] as int? ?? 0,
      url: json['url'] as String? ?? '',
      title: json['title'] as String?,
      description: json['description'] as String?,
      originalExtension: json['original_extension'] as String?,
      displayOrder: json['display_order'] as int? ?? 0,
    );
  }
}

class RoomGalleryCategory {
  final String type; // 'chambre' | 'suite'
  final String typeLabel;
  final int total;
  final List<RoomGalleryPhoto> photos;

  const RoomGalleryCategory({
    required this.type,
    required this.typeLabel,
    required this.total,
    required this.photos,
  });

  factory RoomGalleryCategory.fromJson(Map<String, dynamic> json) {
    final rawPhotos = json['photos'] as List<dynamic>? ?? [];
    return RoomGalleryCategory(
      type: json['type'] as String? ?? '',
      typeLabel: json['type_label'] as String? ?? '',
      total: json['total'] as int? ?? 0,
      photos: rawPhotos
          .map((e) => RoomGalleryPhoto.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }

  bool get hasPhotos => photos.isNotEmpty;
}
