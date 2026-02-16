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

  /// True si le service concerne un véhicule (taxi ou location avec chauffeur).
  bool get isVehicleService {
    final lower = name.toLowerCase();
    return lower.contains('voiture') ||
        lower.contains('chauffeur') ||
        lower.contains('location');
  }

  /// True si le service est réservé au module Hotel Infos & Sécurité (médecin, urgence).
  bool get isHotelSecurityOnly {
    final lower = name.toLowerCase();
    return lower.contains('médecin') ||
        (lower.contains('urgence') && lower.contains('sécurité'));
  }

  /// True si le service est réservé au module EXPLORATION & MOBILITÉ (ne pas afficher dans « Autres services »).
  bool get isExplorationMobilityOnly {
    final lower = name.toLowerCase();
    return lower.contains('transfert') ||
        lower.contains('vtc') ||
        lower.contains('navette') ||
        (lower.contains('location') &&
            (lower.contains('voiture') ||
                lower.contains('véhicule') ||
                lower.contains('chauffeur'))) ||
        lower.contains('visites guidées') ||
        lower.contains('visite guidée');
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
  final String? roomNumber;
  final String? guestName;
  final String? emergencyType;

  PalaceRequest({
    required this.id,
    required this.serviceId,
    required this.serviceName,
    this.details,
    required this.status,
    required this.createdAt,
    this.scheduledTime,
    this.roomNumber,
    this.guestName,
    this.emergencyType,
  });

  static int _parseInt(dynamic v) {
    if (v == null) return 0;
    if (v is int) return v;
    if (v is num) return v.toInt();
    if (v is String) return int.tryParse(v) ?? 0;
    return 0;
  }

  factory PalaceRequest.fromJson(Map<String, dynamic> json) {
    final palaceService = json['palace_service'] as Map<String, dynamic>?;
    final serviceId = palaceService != null
        ? _parseInt(palaceService['id'])
        : _parseInt(json['service_id']);
    final serviceName = palaceService != null
        ? (palaceService['name'] as String? ?? '')
        : (json['service_name'] as String? ?? '');
    final details =
        json['description'] as String? ?? json['details'] as String?;
    final status = json['status'] as String? ?? 'pending';
    final createdAtRaw = json['created_at'];
    final createdAt = createdAtRaw != null
        ? DateTime.tryParse(createdAtRaw as String) ?? DateTime.now()
        : DateTime.now();
    final requestedFor = json['requested_for'];
    final scheduledTime = requestedFor != null
        ? DateTime.tryParse(requestedFor as String)
        : (json['scheduled_time'] != null
              ? DateTime.tryParse(json['scheduled_time'] as String)
              : null);

    return PalaceRequest(
      id: _parseInt(json['id']),
      serviceId: serviceId,
      serviceName: serviceName,
      details: details,
      status: status,
      createdAt: createdAt,
      scheduledTime: scheduledTime,
      roomNumber: json['room_number'] as String?,
      guestName: json['guest_name'] as String?,
      emergencyType: json['emergency_type'] as String?,
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
      'room_number': roomNumber,
      'guest_name': guestName,
      'emergency_type': emergencyType,
    };
  }
}
