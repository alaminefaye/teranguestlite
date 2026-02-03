class MenuCategory {
  final int id;
  final String name;
  final String? description;
  final String? image;
  final int displayOrder;
  final int itemsCount;

  MenuCategory({
    required this.id,
    required this.name,
    this.description,
    this.image,
    required this.displayOrder,
    required this.itemsCount,
  });

  // Factory constructor pour créer une instance depuis JSON
  factory MenuCategory.fromJson(Map<String, dynamic> json) {
    return MenuCategory(
      id: json['id'] as int,
      name: json['name'] as String,
      description: json['description'] as String?,
      image: json['image'] as String?,
      displayOrder: json['display_order'] as int? ?? 0,
      itemsCount: json['items_count'] as int? ?? 0,
    );
  }

  // Convertir en JSON
  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'description': description,
      'image': image,
      'display_order': displayOrder,
      'items_count': itemsCount,
    };
  }
}
