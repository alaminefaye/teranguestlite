class Excursion {
  final int id;
  final String name;
  final String? description;
  final double priceAdult;
  final double priceChild;
  final int duration; // en heures
  final String? image;
  final bool isAvailable;
  final String? destination;
  final List<String>? inclusions;
  /// Horaires et planning détaillé (ex: Départ 09h00, retour 18h)
  final String? scheduleDescription;
  /// Tranche d'âge applicable aux enfants (ex: 3-12 ans)
  final String? childrenAgeRange;
  final String? departureTime;

  Excursion({
    required this.id,
    required this.name,
    this.description,
    required this.priceAdult,
    required this.priceChild,
    required this.duration,
    this.image,
    required this.isAvailable,
    this.destination,
    this.inclusions,
    this.scheduleDescription,
    this.childrenAgeRange,
    this.departureTime,
  });

  static int _parseIntSafe(dynamic v) {
    if (v == null) return 0;
    if (v is int) return v;
    if (v is num) return v.toInt();
    if (v is String) return int.tryParse(v) ?? 0;
    return 0;
  }

  factory Excursion.fromJson(Map<String, dynamic> json) {
    return Excursion(
      id: _parseIntSafe(json['id']),
      name: json['name'] as String? ?? '',
      description: json['description'] as String?,
      priceAdult: _parseDouble(json['price_adult']),
      priceChild: _parseDouble(json['price_child']),
      duration: _parseInt(json['duration_hours']) ?? _parseInt(json['duration']) ?? 0,
      image: json['image'] as String?,
      isAvailable: json['is_available'] as bool? ?? true,
      destination: json['destination'] as String?,
      inclusions: json['inclusions'] != null
          ? List<String>.from(json['inclusions'] as List)
          : (json['included'] != null
              ? List<String>.from(json['included'] as List)
              : null),
      scheduleDescription: json['schedule_description'] as String?,
      childrenAgeRange: json['children_age_range'] as String?,
      departureTime: json['departure_time'] as String?,
    );
  }

  static double _parseDouble(dynamic value) {
    if (value == null) return 0.0;
    if (value is num) return value.toDouble();
    if (value is String) return double.tryParse(value) ?? 0.0;
    return 0.0;
  }

  static int? _parseInt(dynamic value) {
    if (value == null) return null;
    if (value is int) return value;
    if (value is String) return int.tryParse(value);
    return null;
  }

  String get formattedPriceAdult => '${priceAdult.toStringAsFixed(0)} FCFA';
  String get formattedPriceChild => '${priceChild.toStringAsFixed(0)} FCFA';
  String get formattedDuration => '${duration}h';

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'description': description,
      'price_adult': priceAdult,
      'price_child': priceChild,
      'duration': duration,
      'image': image,
      'is_available': isAvailable,
      'destination': destination,
      'inclusions': inclusions,
    };
  }
}

class ExcursionBooking {
  final int id;
  final int excursionId;
  final String excursionName;
  final DateTime date;
  final int adultsCount;
  final int childrenCount;
  final double totalPrice;
  final String status;
  final String? specialRequests;
  final DateTime createdAt;
  final String? roomNumber;
  final String? guestName;

  ExcursionBooking({
    required this.id,
    required this.excursionId,
    required this.excursionName,
    required this.date,
    required this.adultsCount,
    required this.childrenCount,
    required this.totalPrice,
    required this.status,
    this.specialRequests,
    required this.createdAt,
    this.roomNumber,
    this.guestName,
  });

  factory ExcursionBooking.fromJson(Map<String, dynamic> json) {
    final excursion = json['excursion'] as Map<String, dynamic>?;
    final excursionId = excursion != null
        ? Excursion._parseIntSafe(excursion['id'])
        : Excursion._parseIntSafe(json['excursion_id']);
    final excursionName = excursion != null
        ? (excursion['name'] as String? ?? '')
        : (json['excursion_name'] as String? ?? '');
    final dateStr = json['date'] as String?;
    final date = dateStr != null
        ? (DateTime.tryParse(dateStr) ?? DateTime.now())
        : DateTime.now();
    final adultsCount = Excursion._parseIntSafe(
      json['adults'] ?? json['adults_count'],
    );
    final childrenCount = Excursion._parseIntSafe(
      json['children'] ?? json['children_count'],
    );
    final createdAtStr = json['created_at'] as String?;
    final createdAt = createdAtStr != null
        ? (DateTime.tryParse(createdAtStr) ?? DateTime.now())
        : DateTime.now();

    return ExcursionBooking(
      id: Excursion._parseIntSafe(json['id']),
      excursionId: excursionId,
      excursionName: excursionName,
      date: date,
      adultsCount: adultsCount,
      childrenCount: childrenCount,
      totalPrice: _parseDouble(json['total_price']),
      status: json['status'] as String? ?? 'confirmed',
      specialRequests: json['special_requests'] as String?,
      createdAt: createdAt,
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
  int get totalParticipants => adultsCount + childrenCount;

  String get statusLabel {
    switch (status) {
      case 'pending':
        return 'En attente';
      case 'confirmed':
        return 'Confirmée';
      case 'completed':
        return 'Terminée';
      case 'cancelled':
        return 'Annulée';
      default:
        return status;
    }
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'excursion_id': excursionId,
      'excursion_name': excursionName,
      'date': date.toIso8601String(),
      'adults_count': adultsCount,
      'children_count': childrenCount,
      'total_price': totalPrice,
      'status': status,
      'special_requests': specialRequests,
      'created_at': createdAt.toIso8601String(),
    };
  }
}
