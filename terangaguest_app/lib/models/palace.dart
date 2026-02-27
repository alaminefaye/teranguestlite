class PalaceService {
  final int id;
  final String name;
  final String? description;
  final String? category;
  final bool isAvailable;

  /// URL complète de l'image (fournie par le serveur)
  final String? image;

  /// Indique si ce service est « Visites guidées » (défini par le backend ou déduit du nom).
  final bool isGuidedTours;

  PalaceService({
    required this.id,
    required this.name,
    this.description,
    this.category,
    required this.isAvailable,
    this.image,
    bool? isGuidedTours,
  }) : isGuidedTours = isGuidedTours ?? false;

  factory PalaceService.fromJson(Map<String, dynamic> json) {
    final name = json['name'] as String? ?? '';
    final fromApi = json['is_guided_tours'] as bool?;
    final fromName =
        name.toLowerCase().contains('visites guidées') ||
        name.toLowerCase().contains('visite guidée');
    return PalaceService(
      id: json['id'] as int,
      name: name,
      description: json['description'] as String?,
      category: json['category'] as String?,
      isAvailable: json['is_available'] as bool? ?? true,
      image: json['image'] as String?,
      isGuidedTours: fromApi ?? fromName,
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
  final String? requestNumber;
  final int serviceId;
  final String serviceName;
  final String? details;
  final String status;
  final DateTime createdAt;
  final DateTime? scheduledTime;
  final String? roomNumber;
  final String? guestName;
  final String? cancellationReason;

  PalaceRequest({
    required this.id,
    this.requestNumber,
    required this.serviceId,
    required this.serviceName,
    this.details,
    required this.status,
    required this.createdAt,
    this.scheduledTime,
    this.roomNumber,
    this.guestName,
    this.cancellationReason,
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
    final requestNumber = json['request_number'] as String?;
    final serviceId = palaceService != null
        ? _parseInt(palaceService['id'])
        : _parseInt(json['service_id']);
    var parsedServiceName = palaceService != null
        ? (palaceService['name'] as String? ?? '')
        : (json['service_name'] as String? ?? '');
    var parsedDetails =
        json['description'] as String? ?? json['details'] as String?;

    // --- Amenities & Concierge Fix ---
    // If the backend didn't have a "concierge" category, it might have fallen back
    // to another service (like "Assistance médecin"). But we saved the real
    // amenity label in the first line of the details. Let's extract it.
    if (parsedDetails != null && parsedDetails.isNotEmpty) {
      final lines = parsedDetails.split('\n');
      final firstLine = lines.first.trim().toLowerCase();
      final knownLabels = [
        'articles de toilette',
        'toiletries',
        'oreillers',
        'pillows',
        'kit de rasage',
        'shaving kit',
        'autre',
        'other',
        'amenities',
      ];

      final isKnown = knownLabels.any((l) => firstLine.contains(l));

      // Also if dynamic categories were loaded, they might not match knownLabels.
      // A strong heuristic: if details contains " x1" or similar on the second line...
      bool containsQuantity = false;
      if (lines.length > 1) {
        final secondLine = lines[1].trim();
        containsQuantity =
            RegExp(r'\bx\d+').hasMatch(secondLine) ||
            RegExp(r'\d+\s*[xX]\b').hasMatch(secondLine);
      }

      // NEW HEURISTIC: Leisure request mappings
      // e.g "Sport & Fitness - Réservation coach personnel"
      // e.g "Tennis - Court de tennis"
      final isLeisure =
          firstLine.contains(' - réservation') ||
          firstLine.contains(' - demande') ||
          firstLine.contains(' - tee-time') ||
          firstLine.contains(' - équipement') ||
          firstLine.contains(' - court');

      if (isKnown || containsQuantity || isLeisure) {
        parsedServiceName = lines.first.trim();
        parsedDetails = lines.skip(1).join('\n').trim();
      }
    }

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
    final roomNumber = json['room_number'] as String?;
    final guestName = json['guest_name'] as String?;
    final cancellationReason = json['cancellation_reason'] as String?;

    return PalaceRequest(
      id: _parseInt(json['id']),
      requestNumber: requestNumber,
      serviceId: serviceId,
      serviceName: parsedServiceName,
      details: parsedDetails,
      status: status,
      createdAt: createdAt,
      scheduledTime: scheduledTime,
      roomNumber: roomNumber,
      guestName: guestName,
      cancellationReason: cancellationReason,
    );
  }

  String get statusLabel {
    switch (status) {
      case 'pending':
        return 'En attente';
      case 'in_progress':
        return 'Acceptée';
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
      'request_number': requestNumber,
      'service_id': serviceId,
      'service_name': serviceName,
      'details': details,
      'status': status,
      'created_at': createdAt.toIso8601String(),
      'scheduled_time': scheduledTime?.toIso8601String(),
      'room_number': roomNumber,
      'guest_name': guestName,
    };
  }
}
