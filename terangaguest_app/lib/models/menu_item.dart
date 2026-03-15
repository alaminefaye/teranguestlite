class MenuItem {
  final int id;
  /// String (ancienne API) ou Map fr/en/es/ar (fallback traduction).
  final dynamic name;
  final dynamic description;
  final double price;
  final String formattedPrice;
  final String? image;
  final int preparationTime;
  final bool isAvailable;
  final MenuItemCategory? category;

  MenuItem({
    required this.id,
    required this.name,
    this.description,
    required this.price,
    required this.formattedPrice,
    this.image,
    required this.preparationTime,
    required this.isAvailable,
    this.category,
  });

  // Factory constructor pour créer une instance depuis JSON
  factory MenuItem.fromJson(Map<String, dynamic> json) {
    return MenuItem(
      id: json['id'] as int,
      name: json['name'],
      description: json['description'],
      price: _parsePrice(json['price']),
      formattedPrice:
          json['formatted_price'] as String? ?? '${json['price']} FCFA',
      image: json['image'] as String?,
      preparationTime: json['preparation_time'] as int? ?? 0,
      isAvailable: json['is_available'] as bool? ?? true,
      category: json['category'] != null
          ? MenuItemCategory.fromJson(json['category'] as Map<String, dynamic>)
          : null,
    );
  }

  // Helper pour parser un prix qui peut être string ou number
  static double _parsePrice(dynamic value) {
    if (value == null) return 0.0;
    if (value is num) return value.toDouble();
    if (value is String) return double.tryParse(value) ?? 0.0;
    return 0.0;
  }

  // Convertir en JSON
  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name is String ? name : null,
      'description': description is String ? description : null,
      'price': price,
      'formatted_price': formattedPrice,
      'image': image,
      'preparation_time': preparationTime,
      'is_available': isAvailable,
      'category': category?.toJson(),
    };
  }
}

// Classe pour la catégorie dans MenuItem
class MenuItemCategory {
  final int id;
  /// String ou Map fr/en/es/ar (pour TranslatableText).
  final dynamic name;

  MenuItemCategory({required this.id, required this.name});

  factory MenuItemCategory.fromJson(Map<String, dynamic> json) {
    return MenuItemCategory(
      id: json['id'] as int? ?? 0,
      name: json['name'] ?? '',
    );
  }

  Map<String, dynamic> toJson() {
    return {'id': id, 'name': name is String ? name : null};
  }
}
