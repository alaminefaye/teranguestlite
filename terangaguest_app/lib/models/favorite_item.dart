/// Type d'élément pouvant être mis en favori.
enum FavoriteType {
  menuItem,
  restaurant,
  spa,
  excursion,
}

/// Un favori stocké localement (sans backend).
class FavoriteItem {
  final FavoriteType type;
  final int id;
  final String name;
  final String? imageUrl;

  const FavoriteItem({
    required this.type,
    required this.id,
    required this.name,
    this.imageUrl,
  });

  String get typeKey {
    switch (type) {
      case FavoriteType.menuItem:
        return 'menu_item';
      case FavoriteType.restaurant:
        return 'restaurant';
      case FavoriteType.spa:
        return 'spa';
      case FavoriteType.excursion:
        return 'excursion';
    }
  }

  Map<String, dynamic> toJson() => {
        't': typeKey,
        'id': id,
        'name': name,
        'image': imageUrl,
      };

  static FavoriteItem fromJson(Map<String, dynamic> json) {
    final String t = json['t'] as String? ?? 'menu_item';
    FavoriteType type;
    switch (t) {
      case 'restaurant':
        type = FavoriteType.restaurant;
        break;
      case 'spa':
        type = FavoriteType.spa;
        break;
      case 'excursion':
        type = FavoriteType.excursion;
        break;
      default:
        type = FavoriteType.menuItem;
    }
    return FavoriteItem(
      type: type,
      id: json['id'] as int,
      name: json['name'] as String? ?? '',
      imageUrl: json['image'] as String?,
    );
  }
}
