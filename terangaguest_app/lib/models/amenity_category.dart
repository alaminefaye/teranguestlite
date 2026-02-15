/// Catégorie Amenities & Conciergerie (ex. Articles de toilette, Oreillers).
class AmenityCategoryDto {
  final int id;
  final String name;
  final int displayOrder;
  final List<AmenityItemDto> items;

  AmenityCategoryDto({
    required this.id,
    required this.name,
    required this.displayOrder,
    required this.items,
  });

  factory AmenityCategoryDto.fromJson(Map<String, dynamic> json) {
    final itemsList = json['items'] as List<dynamic>? ?? [];
    return AmenityCategoryDto(
      id: json['id'] as int,
      name: json['name'] as String,
      displayOrder: json['display_order'] as int? ?? 0,
      items: itemsList
          .map((e) => AmenityItemDto.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }
}

/// Article d'une catégorie (ex. Savon, Shampooing).
class AmenityItemDto {
  final int id;
  final String name;
  final int displayOrder;

  AmenityItemDto({
    required this.id,
    required this.name,
    required this.displayOrder,
  });

  factory AmenityItemDto.fromJson(Map<String, dynamic> json) {
    return AmenityItemDto(
      id: json['id'] as int,
      name: json['name'] as String,
      displayOrder: json['display_order'] as int? ?? 0,
    );
  }
}
