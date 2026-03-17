class Restaurant {
  final int id;
  /// String ou Map fr/en/es/ar (pour TranslatableText).
  final dynamic name;
  final dynamic description;
  final dynamic type;
  final dynamic cuisine;
  final int? capacity;
  final String? image;
  final Map<String, String>? openingHours;
  final bool isOpen;
  final List<String>? amenities;

  Restaurant({
    required this.id,
    required this.name,
    this.description,
    this.type,
    this.cuisine,
    this.capacity,
    this.image,
    this.openingHours,
    required this.isOpen,
    this.amenities,
  });

  factory Restaurant.fromJson(Map<String, dynamic> json) {
    return Restaurant(
      id: json['id'] as int,
      name: json['name'],
      description: json['description'],
      type: json['type'],
      cuisine: json['cuisine_type'] ?? json['cuisine'],
      capacity: _parseInt(json['capacity']),
      image: json['image'] as String?,
      openingHours: _parseOpeningHours(json['opening_hours']),
      isOpen: json['is_open_now'] as bool? ?? json['is_open'] as bool? ?? false,
      amenities: _parseAmenities(json['amenities']),
    );
  }

  static Map<String, String>? _parseOpeningHours(dynamic raw) {
    if (raw == null) return null;
    final map = raw is Map ? raw as Map<String, dynamic> : null;
    if (map == null) return null;
    final Map<String, String> result = {};
    for (final entry in map.entries) {
      if (entry.value is String) {
        result[entry.key] = entry.value as String;
      } else if (entry.value is Map) {
        final m = entry.value as Map<String, dynamic>;
        final open = m['open'] as String? ?? '';
        final close = m['close'] as String? ?? '';
        result[entry.key] = '$open - $close';
      }
    }
    return result.isEmpty ? null : result;
  }

  static List<String>? _parseAmenities(dynamic raw) {
    if (raw == null) return null;
    if (raw is! List) return null;
    return raw
        .map((e) => e is String ? e : e?.toString() ?? '')
        .where((s) => s.isNotEmpty)
        .toList();
  }

  static int? _parseInt(dynamic value) {
    if (value == null) return null;
    if (value is int) return value;
    if (value is String) return int.tryParse(value);
    return null;
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name is String ? name : name,
      'description': description is String ? description : description,
      'type': type,
      'cuisine': cuisine,
      'capacity': capacity,
      'image': image,
      'opening_hours': openingHours,
      'is_open': isOpen,
      'amenities': amenities,
    };
  }
}

class RestaurantReservation {
  final int id;
  final int restaurantId;
  final String restaurantName;
  final DateTime date;
  final String time;
  final int guests;
  final String status;
  final String? specialRequests;
  final DateTime createdAt;
  final String? roomNumber;
  final String? guestName;
  final String? cancellationReason;

  RestaurantReservation({
    required this.id,
    required this.restaurantId,
    required this.restaurantName,
    required this.date,
    required this.time,
    required this.guests,
    required this.status,
    this.specialRequests,
    required this.createdAt,
    this.roomNumber,
    this.guestName,
    this.cancellationReason,
  });

  factory RestaurantReservation.fromJson(Map<String, dynamic> json) {
    final restaurant = json['restaurant'] as Map<String, dynamic>?;
    return RestaurantReservation(
      id: json['id'] as int? ?? 0,
      restaurantId: restaurant != null
          ? (restaurant['id'] as int? ?? 0)
          : (json['restaurant_id'] as int? ?? 0),
      restaurantName: restaurant != null
          ? (restaurant['name']?.toString() ?? '')
          : (json['restaurant_name']?.toString() ?? ''),
      date: DateTime.tryParse(json['date']?.toString() ?? '') ?? DateTime.now(),
      time: json['time']?.toString() ?? '',
      guests: _parseInt(json['guests']) ?? 1,
      status: json['status']?.toString() ?? 'pending',
      specialRequests: json['special_requests']?.toString(),
      createdAt:
          DateTime.tryParse(json['created_at']?.toString() ?? '') ??
          DateTime.now(),
      roomNumber: json['room_number']?.toString(),
      guestName: json['guest_name']?.toString(),
      cancellationReason: json['cancellation_reason']?.toString(),
    );
  }

  static int? _parseInt(dynamic value) {
    if (value == null) return null;
    if (value is int) return value;
    if (value is String) return int.tryParse(value);
    return null;
  }

  String get statusLabelKey {
    switch (status) {
      case 'pending':
        return 'statusPending';
      case 'confirmed':
        return 'statusConfirmed';
      case 'cancelled':
        return 'statusCancelled';
      case 'honored':
        return 'statusHonored';
      default:
        return status;
    }
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'restaurant_id': restaurantId,
      'restaurant_name': restaurantName,
      'date': date.toIso8601String(),
      'time': time,
      'guests': guests,
      'status': status,
      'special_requests': specialRequests,
      'created_at': createdAt.toIso8601String(),
    };
  }
}
