class MenuCategory {
  final int id;
  /// String ou Map fr/en/es/ar (pour TranslatableText).
  final dynamic name;
  final dynamic description;
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
      name: json['name'],
      description: json['description'],
      image: json['image'] as String?,
      displayOrder: json['display_order'] as int? ?? 0,
      itemsCount: _parseInt(json['items_count']),
    );
  }

  // Helper pour parser un count qui peut être string ou int
  static int _parseInt(dynamic value) {
    if (value == null) return 0;
    if (value is int) return value;
    if (value is String) return int.tryParse(value) ?? 0;
    return 0;
  }

  // Convertir en JSON
  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name is String ? name : null,
      'description': description is String ? description : null,
      'image': image,
      'display_order': displayOrder,
      'items_count': itemsCount,
    };
  }
}
