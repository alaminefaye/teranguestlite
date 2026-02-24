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
  final String? guestPhone;

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
    this.guestPhone,
  });

  factory Order.fromJson(Map<String, dynamic> json) {
    final createdAtRaw = _parseString(json['created_at']);
    final createdAt = createdAtRaw.isNotEmpty
        ? (DateTime.tryParse(createdAtRaw) ?? DateTime.now())
        : DateTime.now();
    final deliveryRaw = json['estimated_delivery'] ?? json['delivery_time'];
    final deliveryTime = deliveryRaw != null
        ? DateTime.tryParse(_parseString(deliveryRaw))
        : null;

    return Order(
      id: _parseInt(json['id']),
      orderNumber: _parseString(json['order_number']),
      status: _parseString(json['status']).isEmpty ? 'pending' : _parseString(json['status']),
      total: _parseDouble(json['total_amount'] ?? json['total']),
      instructions: _parseStringNullable(
        json['special_instructions'] ?? json['instructions'],
      ),
      createdAt: createdAt,
      deliveryTime: deliveryTime,
      itemsCount: _parseInt(json['items_count']),
      items: _parseOrderItems(json['items'] ?? json['order_items']),
      roomNumber: _parseStringNullable(json['room_number']),
      guestName: _parseStringNullable(json['guest_name']),
      guestPhone: _parseStringNullable(json['guest_phone']),
    );
  }

  static List<OrderItem>? _parseOrderItems(dynamic raw) {
    if (raw == null) return null;
    if (raw is! List || raw.isEmpty) return null;
    final list = <OrderItem>[];
    for (final item in raw) {
      Map<String, dynamic>? map;
      if (item is Map<String, dynamic>) {
        map = item;
      } else if (item is Map) {
        try {
          map = Map<String, dynamic>.from(item);
        } catch (_) {
          continue;
        }
      } else {
        continue;
      }
      try {
        list.add(OrderItem.fromJson(map));
      } catch (_) {
        // skip invalid item
      }
    }
    return list.isEmpty ? null : list;
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

  String get formattedTotal {
    if (total.isNaN || !total.isFinite) return '0 FCFA';
    return '${total.toStringAsFixed(0)} FCFA';
  }

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
