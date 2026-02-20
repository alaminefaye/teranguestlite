class LaundryService {
  final int id;
  final String name;
  final String? description;
  final double pricePerItem;
  final bool isAvailable;

  LaundryService({
    required this.id,
    required this.name,
    this.description,
    required this.pricePerItem,
    required this.isAvailable,
  });

  static int _parseIntSafe(dynamic v) {
    if (v == null) return 0;
    if (v is int) return v;
    if (v is num) return v.toInt();
    if (v is String) return int.tryParse(v) ?? 0;
    return 0;
  }

  factory LaundryService.fromJson(Map<String, dynamic> json) {
    return LaundryService(
      id: _parseIntSafe(json['id']),
      name: json['name'] as String? ?? '',
      description: json['description'] as String?,
      pricePerItem: _parseDouble(json['price_per_item'] ?? json['price']),
      isAvailable: json['is_available'] as bool? ?? true,
    );
  }

  static double _parseDouble(dynamic value) {
    if (value == null) return 0.0;
    if (value is num) return value.toDouble();
    if (value is String) return double.tryParse(value) ?? 0.0;
    return 0.0;
  }

  String get formattedPrice => '${pricePerItem.toStringAsFixed(0)} FCFA/pièce';

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'description': description,
      'price_per_item': pricePerItem,
      'is_available': isAvailable,
    };
  }
}

class LaundryRequest {
  final int id;
  final List<LaundryRequestItem> items;
  final double totalPrice;
  final String status;
  final String? specialInstructions;
  final DateTime createdAt;
  final DateTime? pickupTime;
  final DateTime? deliveryTime;
  final String? roomNumber;
  final String? guestName;

  LaundryRequest({
    required this.id,
    required this.items,
    required this.totalPrice,
    required this.status,
    this.specialInstructions,
    required this.createdAt,
    this.pickupTime,
    this.deliveryTime,
    this.roomNumber,
    this.guestName,
  });

  static int _parseIntSafe(dynamic v) {
    if (v == null) return 0;
    if (v is int) return v;
    if (v is num) return v.toInt();
    if (v is String) return int.tryParse(v) ?? 0;
    return 0;
  }

  factory LaundryRequest.fromJson(Map<String, dynamic> json) {
    final itemsList = json['items'] as List? ?? [];
    final createdAtStr = json['created_at'] as String?;
    final createdAt = createdAtStr != null
        ? (DateTime.tryParse(createdAtStr) ?? DateTime.now())
        : DateTime.now();
    final pickupRaw = json['pickup_time'];
    final pickupTime = pickupRaw != null
        ? DateTime.tryParse(pickupRaw.toString())
        : null;
    final deliveryRaw = json['delivery_time'] ?? json['estimated_delivery'];
    final deliveryTime = deliveryRaw != null
        ? DateTime.tryParse(deliveryRaw.toString())
        : null;

    return LaundryRequest(
      id: _parseIntSafe(json['id']),
      items: itemsList
          .map(
            (item) => LaundryRequestItem.fromJson(item as Map<String, dynamic>),
          )
          .toList(),
      totalPrice: _parseDouble(json['total_price'] ?? json['total']),
      status: json['status'] as String? ?? 'pending',
      specialInstructions: json['special_instructions'] as String?,
      createdAt: createdAt,
      pickupTime: pickupTime,
      deliveryTime: deliveryTime,
      roomNumber: json['room_number'] as String?,
      guestName: json['guest_name'] as String?,
    );
  }

  static double _parseDouble(dynamic value) {
    if (value == null) return 0.0;
    if (value is num) return value.toDouble();
    if (value is String) return double.tryParse(value) ?? 0.0;
    return 0.0;
  }

  String get formattedTotalPrice => '${totalPrice.toStringAsFixed(0)} FCFA';

  String get statusLabel {
    switch (status) {
      case 'pending':
        return 'En attente';
      case 'picked_up':
        return 'Récupérée';
      case 'processing':
        return 'En cours';
      case 'ready':
        return 'Prête';
      case 'delivered':
        return 'Livrée';
      case 'cancelled':
        return 'Annulée';
      default:
        return status;
    }
  }

  int get totalItems => items.fold(0, (sum, item) => sum + item.quantity);

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'items': items.map((item) => item.toJson()).toList(),
      'total_price': totalPrice,
      'status': status,
      'special_instructions': specialInstructions,
      'created_at': createdAt.toIso8601String(),
      'pickup_time': pickupTime?.toIso8601String(),
      'delivery_time': deliveryTime?.toIso8601String(),
      'room_number': roomNumber,
      'guest_name': guestName,
    };
  }
}

class LaundryRequestItem {
  final int serviceId;
  final String serviceName;
  final int quantity;
  final double pricePerItem;

  LaundryRequestItem({
    required this.serviceId,
    required this.serviceName,
    required this.quantity,
    required this.pricePerItem,
  });

  factory LaundryRequestItem.fromJson(Map<String, dynamic> json) {
    final service = json['service'] as Map<String, dynamic>?;
    final serviceId = service != null
        ? LaundryRequest._parseIntSafe(service['id'])
        : LaundryRequest._parseIntSafe(json['service_id']);
    final serviceName = service != null
        ? (service['name'] as String? ?? '')
        : (json['service_name'] as String? ?? '');
    final pricePerItem = _parseDouble(
      json['price_per_item'] ?? json['unit_price'] ?? json['price'],
    );
    return LaundryRequestItem(
      serviceId: serviceId,
      serviceName: serviceName,
      quantity: _parseInt(json['quantity']),
      pricePerItem: pricePerItem,
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

  double get subtotal => pricePerItem * quantity;
  String get formattedSubtotal => '${subtotal.toStringAsFixed(0)} FCFA';

  Map<String, dynamic> toJson() {
    return {
      'service_id': serviceId,
      'service_name': serviceName,
      'quantity': quantity,
      'price_per_item': pricePerItem,
    };
  }
}
