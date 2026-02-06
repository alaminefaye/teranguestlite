class PalaceService {
  final int id;
  final String name;
  final String? description;
  final String? category;
  final bool isAvailable;
  /// URL complète de l'image (fournie par le serveur)
  final String? image;

  PalaceService({
    required this.id,
    required this.name,
    this.description,
    this.category,
    required this.isAvailable,
    this.image,
  });

  factory PalaceService.fromJson(Map<String, dynamic> json) {
    return PalaceService(
      id: json['id'] as int,
      name: json['name'] as String,
      description: json['description'] as String?,
      category: json['category'] as String?,
      isAvailable: json['is_available'] as bool? ?? true,
      image: json['image'] as String?,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'description': description,
      'category': category,
      'is_available': isAvailable,
      'image': image,
    };
  }
}

class PalaceRequest {
  final int id;
  final int serviceId;
  final String serviceName;
  final String? details;
  final String status;
  final DateTime createdAt;
  final DateTime? scheduledTime;

  PalaceRequest({
    required this.id,
    required this.serviceId,
    required this.serviceName,
    this.details,
    required this.status,
    required this.createdAt,
    this.scheduledTime,
  });

  factory PalaceRequest.fromJson(Map<String, dynamic> json) {
    return PalaceRequest(
      id: json['id'] as int,
      serviceId: json['service_id'] as int,
      serviceName: json['service_name'] as String,
      details: json['details'] as String?,
      status: json['status'] as String,
      createdAt: DateTime.parse(json['created_at'] as String),
      scheduledTime: json['scheduled_time'] != null
          ? DateTime.parse(json['scheduled_time'] as String)
          : null,
    );
  }

  String get statusLabel {
    switch (status) {
      case 'pending':
        return 'En attente';
      case 'in_progress':
        return 'En cours';
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
      'details': details,
      'status': status,
      'created_at': createdAt.toIso8601String(),
      'scheduled_time': scheduledTime?.toIso8601String(),
    };
  }
}
