class SpaService {
  final int id;
  final String name;
  final String? description;
  final double price;
  final int duration; // en minutes
  final String? image;
  final bool isAvailable;
  final String? category;

  SpaService({
    required this.id,
    required this.name,
    this.description,
    required this.price,
    required this.duration,
    this.image,
    required this.isAvailable,
    this.category,
  });

  factory SpaService.fromJson(Map<String, dynamic> json) {
    return SpaService(
      id: json['id'] as int,
      name: json['name'] as String,
      description: json['description'] as String?,
      price: _parseDouble(json['price']),
      duration: _parseInt(json['duration']) ?? 0,
      image: json['image'] as String?,
      isAvailable: json['is_available'] as bool? ?? true,
      category: json['category'] as String?,
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

  String get formattedPrice => '${price.toStringAsFixed(0)} FCFA';
  String get formattedDuration {
    if (duration < 60) return '$duration min';
    final hours = duration ~/ 60;
    final mins = duration % 60;
    if (mins == 0) return '${hours}h';
    return '${hours}h${mins}min';
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'description': description,
      'price': price,
      'duration': duration,
      'image': image,
      'is_available': isAvailable,
      'category': category,
    };
  }
}

class SpaReservation {
  final int id;
  final int serviceId;
  final String serviceName;
  final DateTime date;
  final String time;
  final String status;
  final String? specialRequests;
  final double price;
  final DateTime createdAt;

  SpaReservation({
    required this.id,
    required this.serviceId,
    required this.serviceName,
    required this.date,
    required this.time,
    required this.status,
    this.specialRequests,
    required this.price,
    required this.createdAt,
  });

  factory SpaReservation.fromJson(Map<String, dynamic> json) {
    final spaService = json['spa_service'] as Map<String, dynamic>?;
    return SpaReservation(
      id: json['id'] as int,
      serviceId: spaService != null ? (spaService['id'] as int) : (json['service_id'] as int? ?? 0),
      serviceName: spaService != null ? (spaService['name'] as String) : (json['service_name'] as String? ?? ''),
      date: DateTime.parse(json['date'] as String),
      time: json['time'] as String,
      status: json['status'] as String,
      specialRequests: json['special_requests'] as String?,
      price: _parseDouble(json['price']),
      createdAt: DateTime.parse(json['created_at'] as String),
    );
  }

  static double _parseDouble(dynamic value) {
    if (value == null) return 0.0;
    if (value is num) return value.toDouble();
    if (value is String) return double.tryParse(value) ?? 0.0;
    return 0.0;
  }

  String get formattedPrice => '${price.toStringAsFixed(0)} FCFA';

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
      'service_id': serviceId,
      'service_name': serviceName,
      'date': date.toIso8601String(),
      'time': time,
      'status': status,
      'special_requests': specialRequests,
      'price': price,
      'created_at': createdAt.toIso8601String(),
    };
  }
}
