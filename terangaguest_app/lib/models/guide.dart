class GuideCategory {
  final int id;
  final String name;
  final String? image;
  final int order;
  final bool isActive;
  final List<GuideItem>? items;

  GuideCategory({
    required this.id,
    required this.name,
    this.image,
    required this.order,
    required this.isActive,
    this.items,
  });

  factory GuideCategory.fromJson(Map<String, dynamic> json) {
    return GuideCategory(
      id: json['id'],
      name: json['name'],
      image: json['image'],
      order: json['order'] ?? 0,
      isActive: json['is_active'] ?? true,
      items: json['items'] != null
          ? (json['items'] as List).map((i) => GuideItem.fromJson(i)).toList()
          : null,
    );
  }
}

class GuideItem {
  final int id;
  final int categoryId;
  final String title;
  final String? description;
  final String? phone;
  final String? address;
  final double? latitude;
  final double? longitude;
  final String? image;
  final int order;
  final bool isActive;

  GuideItem({
    required this.id,
    required this.categoryId,
    required this.title,
    this.description,
    this.phone,
    this.address,
    this.latitude,
    this.longitude,
    this.image,
    required this.order,
    required this.isActive,
  });

  factory GuideItem.fromJson(Map<String, dynamic> json) {
    return GuideItem(
      id: json['id'],
      categoryId: json['guide_category_id'],
      title: json['title'],
      description: json['description'],
      phone: json['phone'],
      address: json['address'],
      latitude: json['latitude'] != null
          ? double.parse(json['latitude'].toString())
          : null,
      longitude: json['longitude'] != null
          ? double.parse(json['longitude'].toString())
          : null,
      image: json['image'],
      order: json['order'] ?? 0,
      isActive: json['is_active'] ?? true,
    );
  }
}
