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

  factory LaundryService.fromJson(Map<String, dynamic> json) {
    return LaundryService(
      id: json['id'] as int,
      name: json['name'] as String,
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

  LaundryRequest({
    required this.id,
    required this.items,
    required this.totalPrice,
    required this.status,
    this.specialInstructions,
    required this.createdAt,
    this.pickupTime,
    this.deliveryTime,
  });

  factory LaundryRequest.fromJson(Map<String, dynamic> json) {
    return LaundryRequest(
      id: json['id'] as int,
      items: (json['items'] as List)
          .map((item) =>
              LaundryRequestItem.fromJson(item as Map<String, dynamic>))
          .toList(),
      totalPrice: _parseDouble(json['total_price'] ?? json['total']),
      status: json['status'] as String,
      specialInstructions: json['special_instructions'] as String?,
      createdAt: DateTime.parse(json['created_at'] as String),
      pickupTime: json['pickup_time'] != null
          ? DateTime.parse(json['pickup_time'] as String)
          : null,
      deliveryTime: json['delivery_time'] != null
          ? DateTime.parse(json['delivery_time'] as String)
          : null,
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
    return LaundryRequestItem(
      serviceId: json['service_id'] as int,
      serviceName: json['service_name'] as String,
      quantity: _parseInt(json['quantity']),
      pricePerItem: _parseDouble(json['price_per_item'] ?? json['price']),
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
