class User {
  final int id;
  final String name;
  final String email;
  final String role;
  final int? enterpriseId;
  final String? department;

  /// Sections que le staff peut gérer (tuiles Espace Admin + notifications). Vide ou null = admin voit tout.
  final List<String>? managedSections;
  final String? roomNumber;
  final bool mustChangePassword;
  final String? fcmToken;
  final Enterprise? enterprise;
  final DateTime? createdAt;

  /// True si l'utilisateur a un séjour actif (réservation de chambre) et peut faire des réservations (spa, restaurant, etc.)
  final bool canReserve;

  User({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
    this.enterpriseId,
    this.department,
    this.managedSections,
    this.roomNumber,
    this.mustChangePassword = false,
    this.fcmToken,
    this.enterprise,
    this.createdAt,
    bool? canReserve,
  }) : canReserve = canReserve ?? false;

  // Factory constructor pour créer une instance depuis JSON
  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'] as int,
      name: json['name'] as String,
      email: json['email'] as String,
      role: json['role'] as String,
      enterpriseId: _parseId(json['enterprise_id']),
      department: json['department'] as String?,
      managedSections: _parseStringList(json['managed_sections']),
      roomNumber: _parseStringNullable(json['room_number']),
      mustChangePassword: json['must_change_password'] as bool? ?? false,
      fcmToken: json['fcm_token'] as String?,
      enterprise: json['enterprise'] != null
          ? Enterprise.fromJson(json['enterprise'] as Map<String, dynamic>)
          : null,
      createdAt: json['created_at'] != null
          ? DateTime.parse(json['created_at'] as String)
          : null,
      canReserve: json['can_reserve'] == true,
    );
  }

  // Helper pour parser un ID qui peut être string ou int
  static int? _parseId(dynamic value) {
    if (value == null) return null;
    if (value is int) return value;
    if (value is String) return int.tryParse(value);
    return null;
  }

  static String? _parseStringNullable(dynamic value) {
    if (value == null) return null;
    if (value is String) return value;
    if (value is int) return value.toString();
    return null;
  }

  static List<String>? _parseStringList(dynamic value) {
    if (value == null) return null;
    if (value is! List) return null;
    return value
        .map((e) => e?.toString() ?? '')
        .where((s) => s.isNotEmpty)
        .toList();
  }

  // Convertir en JSON
  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'role': role,
      'enterprise_id': enterpriseId,
      'department': department,
      'managed_sections': managedSections,
      'room_number': roomNumber,
      'must_change_password': mustChangePassword,
      'fcm_token': fcmToken,
      'enterprise': enterprise?.toJson(),
      'created_at': createdAt?.toIso8601String(),
      'can_reserve': canReserve,
    };
  }

  // Copier avec des modifications
  User copyWith({
    int? id,
    String? name,
    String? email,
    String? role,
    int? enterpriseId,
    String? department,
    List<String>? managedSections,
    String? roomNumber,
    bool? mustChangePassword,
    String? fcmToken,
    Enterprise? enterprise,
    DateTime? createdAt,
    bool? canReserve,
  }) {
    return User(
      id: id ?? this.id,
      name: name ?? this.name,
      email: email ?? this.email,
      role: role ?? this.role,
      enterpriseId: enterpriseId ?? this.enterpriseId,
      department: department ?? this.department,
      managedSections: managedSections ?? this.managedSections,
      roomNumber: roomNumber ?? this.roomNumber,
      mustChangePassword: mustChangePassword ?? this.mustChangePassword,
      fcmToken: fcmToken ?? this.fcmToken,
      enterprise: enterprise ?? this.enterprise,
      createdAt: createdAt ?? this.createdAt,
      canReserve: canReserve ?? this.canReserve,
    );
  }

  // Getters utiles
  bool get isGuest => role == 'guest';
  bool get isStaff => role == 'staff';
  bool get isAdmin => role == 'admin' || role == 'super_admin';

  String get displayRole {
    switch (role) {
      case 'guest':
        return 'Client';
      case 'staff':
        return 'Personnel';
      case 'admin':
        return 'Administrateur';
      default:
        return role;
    }
  }
}

/// Données livret d'accueil (Wi‑Fi, règlement, plan, infos pratiques).
class HotelInfos {
  final String wifiNetwork;
  final String wifiPassword;
  final String houseRules;
  final String? mapUrl;
  final String practicalInfo;

  HotelInfos({
    this.wifiNetwork = '',
    this.wifiPassword = '',
    this.houseRules = '',
    this.mapUrl,
    this.practicalInfo = '',
  });

  factory HotelInfos.fromJson(Map<String, dynamic>? json) {
    if (json == null) return HotelInfos();
    return HotelInfos(
      wifiNetwork: json['wifi_network'] as String? ?? '',
      wifiPassword: json['wifi_password'] as String? ?? '',
      houseRules: json['house_rules'] as String? ?? '',
      mapUrl: json['map_url'] as String?,
      practicalInfo: json['practical_info'] as String? ?? '',
    );
  }
}

/// Assistance & Urgence (médecin, sécurité).
class EmergencySettings {
  final bool doctorEnabled;
  final bool securityEnabled;

  EmergencySettings({this.doctorEnabled = true, this.securityEnabled = true});

  factory EmergencySettings.fromJson(Map<String, dynamic>? json) {
    if (json == null) return EmergencySettings();
    return EmergencySettings(
      doctorEnabled: json['doctor_enabled'] as bool? ?? true,
      securityEnabled: json['security_enabled'] as bool? ?? true,
    );
  }
}

// Classe pour l'entreprise dans User
class Enterprise {
  final int id;
  final String name;
  final String? logo;

  /// Image de couverture pour l'écran d'accueil (grande photo en fond).
  final String? coverPhoto;

  /// Horaires de la salle de sport (affichés dans Sport & Fitness).
  final String? gymHours;
  final String? type;
  final HotelInfos hotelInfos;
  final EmergencySettings emergency;
  final String? chatbotUrl;

  Enterprise({
    required this.id,
    required this.name,
    this.logo,
    this.coverPhoto,
    this.gymHours,
    this.type,
    HotelInfos? hotelInfos,
    EmergencySettings? emergency,
    this.chatbotUrl,
  }) : hotelInfos = hotelInfos ?? HotelInfos(),
       emergency = emergency ?? EmergencySettings();

  factory Enterprise.fromJson(Map<String, dynamic> json) {
    return Enterprise(
      id: _parseIdSafe(json['id']),
      name: json['name'] as String,
      logo: json['logo'] as String?,
      coverPhoto: json['cover_photo'] as String?,
      gymHours: json['gym_hours'] as String?,
      type: json['type'] as String?,
      hotelInfos: HotelInfos.fromJson(
        json['hotel_infos'] as Map<String, dynamic>?,
      ),
      emergency: EmergencySettings.fromJson(
        json['emergency'] as Map<String, dynamic>?,
      ),
      chatbotUrl: json['chatbot_url'] as String?,
    );
  }

  // Helper pour parser un ID qui peut être string ou int
  static int _parseIdSafe(dynamic value) {
    if (value == null) return 0;
    if (value is int) return value;
    if (value is String) return int.tryParse(value) ?? 0;
    return 0;
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'logo': logo,
      'cover_photo': coverPhoto,
      'gym_hours': gymHours,
      'type': type,
      'hotel_infos': {
        'wifi_network': hotelInfos.wifiNetwork,
        'wifi_password': hotelInfos.wifiPassword,
        'house_rules': hotelInfos.houseRules,
        'map_url': hotelInfos.mapUrl,
        'practical_info': hotelInfos.practicalInfo,
      },
      'emergency': {
        'doctor_enabled': emergency.doctorEnabled,
        'security_enabled': emergency.securityEnabled,
      },
      'chatbot_url': chatbotUrl,
    };
  }
}
