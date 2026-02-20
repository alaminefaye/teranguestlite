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
  final String? roomNumber;
  final String? guestName;

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
    this.roomNumber,
    this.guestName,
  });

  factory Order.fromJson(Map<String, dynamic> json) {
    return Order(
      id: _parseInt(json['id']),
      orderNumber: _parseString(json['order_number']),
      status: _parseString(json['status']),
      total: _parseDouble(json['total_amount'] ?? json['total']),
      instructions: _parseStringNullable(
        json['special_instructions'] ?? json['instructions'],
      ),
      createdAt: DateTime.parse(_parseString(json['created_at'])),
      deliveryTime: () {
        final raw = json['estimated_delivery'] ?? json['delivery_time'];
        if (raw == null) return null;
        return DateTime.tryParse(_parseString(raw));
      }(),
      itemsCount: _parseInt(json['items_count']),
      items: json['items'] != null
          ? (json['items'] as List)
                .map(
                  (item) => OrderItem.fromJson(
                    item is Map<String, dynamic>
                        ? item
                        : Map<String, dynamic>.from(item as Map),
                  ),
                )
                .toList()
          : null,
      roomNumber: _parseStringNullable(json['room_number']),
      guestName: _parseStringNullable(json['guest_name']),
    );
  }

  static String _parseString(dynamic value) {
    if (value == null) return '';
    if (value is String) return value;
    return value.toString();
  }

  static String? _parseStringNullable(dynamic value) {
    if (value == null) return null;
    if (value is String) return value.isEmpty ? null : value;
    final s = value.toString();
    return s.isEmpty ? null : s;
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

  /// Annulation possible tant que la commande n'est pas "Prête" (ready), en livraison ou livrée.
  bool get canCancel {
    return status == 'pending' ||
        status == 'confirmed' ||
        status == 'preparing';
  }

  String get statusLabel {
    switch (status) {
      case 'pending':
        return 'En attente';
      case 'confirmed':
        return 'Confirmée';
      case 'preparing':
        return 'En préparation';
      case 'ready':
        return 'Prête';
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
    final menuItemData = json['menu_item'] is Map
        ? json['menu_item'] as Map<String, dynamic>?
        : null;
    final id = _parseInt(json['id']);
    final menuItemId = _parseInt(menuItemData?['id'] ?? json['menu_item_id']);
    final name = _parseString(
      menuItemData?['name'] ?? json['name'] ?? json['item_name'],
    );
    final image = _parseStringNullable(menuItemData?['image'] ?? json['image']);

    return OrderItem(
      id: id != 0 ? id : menuItemId,
      menuItemId: menuItemId,
      name: name,
      quantity: _parseInt(json['quantity']),
      price: _parseDouble(json['unit_price'] ?? json['price']),
      image: image,
    );
  }

  static String _parseString(dynamic value) {
    if (value == null) return '';
    if (value is String) return value;
    return value.toString();
  }

  static String? _parseStringNullable(dynamic value) {
    if (value == null) return null;
    if (value is String) return value.isEmpty ? null : value;
    final s = value.toString();
    return s.isEmpty ? null : s;
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
