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
  });

  factory Excursion.fromJson(Map<String, dynamic> json) {
    return Excursion(
      id: json['id'] as int,
      name: json['name'] as String,
      description: json['description'] as String?,
      priceAdult: _parseDouble(json['price_adult']),
      priceChild: _parseDouble(json['price_child']),
      duration: _parseInt(json['duration']) ?? 0,
      image: json['image'] as String?,
      isAvailable: json['is_available'] as bool? ?? true,
      destination: json['destination'] as String?,
      inclusions: json['inclusions'] != null
          ? List<String>.from(json['inclusions'] as List)
          : null,
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
  });

  factory ExcursionBooking.fromJson(Map<String, dynamic> json) {
    return ExcursionBooking(
      id: json['id'] as int,
      excursionId: json['excursion_id'] as int,
      excursionName: json['excursion_name'] as String,
      date: DateTime.parse(json['date'] as String),
      adultsCount: _parseInt(json['adults_count']) ?? 0,
      childrenCount: _parseInt(json['children_count']) ?? 0,
      totalPrice: _parseDouble(json['total_price']),
      status: json['status'] as String,
      specialRequests: json['special_requests'] as String?,
      createdAt: DateTime.parse(json['created_at'] as String),
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
