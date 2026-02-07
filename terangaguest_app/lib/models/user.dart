class User {
  final int id;
  final String name;
  final String email;
  final String role;
  final int? enterpriseId;
  final String? department;
  final String? roomNumber;
  final bool mustChangePassword;
  final String? fcmToken;
  final Enterprise? enterprise;
  final DateTime? createdAt;

  User({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
    this.enterpriseId,
    this.department,
    this.roomNumber,
    this.mustChangePassword = false,
    this.fcmToken,
    this.enterprise,
    this.createdAt,
  });

  // Factory constructor pour créer une instance depuis JSON
  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'] as int,
      name: json['name'] as String,
      email: json['email'] as String,
      role: json['role'] as String,
      enterpriseId: _parseId(json['enterprise_id']),
      department: json['department'] as String?,
      roomNumber: _parseStringNullable(json['room_number']),
      mustChangePassword: json['must_change_password'] as bool? ?? false,
      fcmToken: json['fcm_token'] as String?,
      enterprise: json['enterprise'] != null
          ? Enterprise.fromJson(json['enterprise'] as Map<String, dynamic>)
          : null,
      createdAt: json['created_at'] != null
          ? DateTime.parse(json['created_at'] as String)
          : null,
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

  // Convertir en JSON
  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'role': role,
      'enterprise_id': enterpriseId,
      'department': department,
      'room_number': roomNumber,
      'must_change_password': mustChangePassword,
      'fcm_token': fcmToken,
      'enterprise': enterprise?.toJson(),
      'created_at': createdAt?.toIso8601String(),
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
    String? roomNumber,
    bool? mustChangePassword,
    String? fcmToken,
    Enterprise? enterprise,
    DateTime? createdAt,
  }) {
    return User(
      id: id ?? this.id,
      name: name ?? this.name,
      email: email ?? this.email,
      role: role ?? this.role,
      enterpriseId: enterpriseId ?? this.enterpriseId,
      department: department ?? this.department,
      roomNumber: roomNumber ?? this.roomNumber,
      mustChangePassword: mustChangePassword ?? this.mustChangePassword,
      fcmToken: fcmToken ?? this.fcmToken,
      enterprise: enterprise ?? this.enterprise,
      createdAt: createdAt ?? this.createdAt,
    );
  }

  // Getters utiles
  bool get isGuest => role == 'guest';
  bool get isStaff => role == 'staff';
  bool get isAdmin => role == 'admin';

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

// Classe pour l'entreprise dans User
class Enterprise {
  final int id;
  final String name;
  final String? logo;
  final String? type;

  Enterprise({
    required this.id,
    required this.name,
    this.logo,
    this.type,
  });

  factory Enterprise.fromJson(Map<String, dynamic> json) {
    return Enterprise(
      id: _parseIdSafe(json['id']),
      name: json['name'] as String,
      logo: json['logo'] as String?,
      type: json['type'] as String?,
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
      'type': type,
    };
  }
}
