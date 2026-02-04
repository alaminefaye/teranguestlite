class Order {
  final int id;
  final String orderNumber;
  final String status;
  final double total;
  final String? instructions;
  final DateTime createdAt;
  final DateTime? deliveryTime;
  final int itemsCount;
  final List<OrderItem>? items;

  Order({
    required this.id,
    required this.orderNumber,
    required this.status,
    required this.total,
    this.instructions,
    required this.createdAt,
    this.deliveryTime,
    required this.itemsCount,
    this.items,
  });

  factory Order.fromJson(Map<String, dynamic> json) {
    return Order(
      id: json['id'] as int,
      orderNumber: json['order_number'] as String,
      status: json['status'] as String,
      total: _parseDouble(json['total_amount'] ?? json['total']),
      instructions: json['special_instructions'] ?? json['instructions'] as String?,
      createdAt: DateTime.parse(json['created_at'] as String),
      deliveryTime: json['estimated_delivery'] != null
          ? DateTime.parse(json['estimated_delivery'] as String)
          : (json['delivery_time'] != null
              ? DateTime.parse(json['delivery_time'] as String)
              : null),
      itemsCount: _parseInt(json['items_count']),
      items: json['items'] != null
          ? (json['items'] as List)
              .map((item) => OrderItem.fromJson(item as Map<String, dynamic>))
              .toList()
          : null,
    );
  }

  static double _parseDouble(dynamic value) {
    if (value == null) return 0.0;
    if (value is num) return value.toDouble();
    if (value is String) return double.tryParse(value) ?? 0.0;
    return 0.0;
  }

  static int _parseInt(dynamic value) {
    if (value == null) return 0;
    if (value is int) return value;
    if (value is String) return int.tryParse(value) ?? 0;
    return 0;
  }

  String get formattedTotal => '${total.toStringAsFixed(0)} FCFA';

  String get statusLabel {
    switch (status) {
      case 'pending':
        return 'En attente';
      case 'confirmed':
        return 'Confirmée';
      case 'preparing':
        return 'En préparation';
      case 'delivering':
        return 'En livraison';
      case 'delivered':
        return 'Livrée';
      case 'cancelled':
        return 'Annulée';
      default:
        return status;
    }
  }
}

class OrderItem {
  final int id;
  final int menuItemId;
  final String name;
  final int quantity;
  final double price;
  final String? image;

  OrderItem({
    required this.id,
    required this.menuItemId,
    required this.name,
    required this.quantity,
    required this.price,
    this.image,
  });

  factory OrderItem.fromJson(Map<String, dynamic> json) {
    // Support pour les 2 formats d'API
    final menuItemData = json['menu_item'] as Map<String, dynamic>?;
    
    return OrderItem(
      id: json['id'] as int,
      menuItemId: menuItemData?['id'] ?? json['menu_item_id'] as int,
      name: menuItemData?['name'] ?? json['name'] as String,
      quantity: _parseInt(json['quantity']),
      price: _parseDouble(json['unit_price'] ?? json['price']),
      image: menuItemData?['image'] ?? json['image'] as String?,
    );
  }

  static double _parseDouble(dynamic value) {
    if (value == null) return 0.0;
    if (value is num) return value.toDouble();
    if (value is String) return double.tryParse(value) ?? 0.0;
    return 0.0;
  }

  static int _parseInt(dynamic value) {
    if (value == null) return 0;
    if (value is int) return value;
    if (value is String) return int.tryParse(value) ?? 0;
    return 0;
  }

  double get subtotal => price * quantity;
  String get formattedPrice => '${price.toStringAsFixed(0)} FCFA';
  String get formattedSubtotal => '${subtotal.toStringAsFixed(0)} FCFA';
}
