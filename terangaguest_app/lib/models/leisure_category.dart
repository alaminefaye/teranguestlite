/// Catégorie principale (Sport ou Loisirs) avec ses activités enfants.
class LeisureMainCategoryDto {
  final int id;
  final String name;
  final String? description;
  final String type; // sport | loisirs
  final int displayOrder;
  final List<LeisureCategoryDto> children;

  LeisureMainCategoryDto({
    required this.id,
    required this.name,
    this.description,
    required this.type,
    required this.displayOrder,
    required this.children,
  });

  factory LeisureMainCategoryDto.fromJson(Map<String, dynamic> json) {
    final childrenList = json['children'] as List<dynamic>? ?? [];
    return LeisureMainCategoryDto(
      id: json['id'] as int,
      name: json['name'] as String,
      description: json['description'] as String?,
      type: json['type'] as String? ?? 'sport',
      displayOrder: json['display_order'] as int? ?? 0,
      children: childrenList
          .map((e) => LeisureCategoryDto.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }
}

/// Sous-catégorie / activité (Golf & Tennis, Fitness, Spa, etc.).
class LeisureCategoryDto {
  final int id;
  final String name;
  final String? description;
  final String type; // golf_tennis, fitness, spa, other
  final int displayOrder;

  LeisureCategoryDto({
    required this.id,
    required this.name,
    this.description,
    required this.type,
    required this.displayOrder,
  });

  factory LeisureCategoryDto.fromJson(Map<String, dynamic> json) {
    return LeisureCategoryDto(
      id: json['id'] as int,
      name: json['name'] as String,
      description: json['description'] as String?,
      type: json['type'] as String? ?? 'other',
      displayOrder: json['display_order'] as int? ?? 0,
    );
  }
}
