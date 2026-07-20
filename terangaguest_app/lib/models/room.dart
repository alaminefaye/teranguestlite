import 'dart:convert';

class RoomType {
  final String type;
  final String typeName;
  final String typeLabel;
  final List<Room> rooms;

  RoomType({
    required this.type,
    required this.typeName,
    required this.typeLabel,
    required this.rooms,
  });

  static String _parseTranslatableString(dynamic v) {
    if (v == null) return '';
    if (v is String) return v;
    if (v is Map) {
      String? s(dynamic x) => x is String ? x : (x?.toString());
      final fr = s(v['fr']);
      if (fr != null && fr.trim().isNotEmpty) return fr;
      final en = s(v['en']);
      if (en != null && en.trim().isNotEmpty) return en;
      final es = s(v['es']);
      if (es != null && es.trim().isNotEmpty) return es;
      final ar = s(v['ar']);
      if (ar != null && ar.trim().isNotEmpty) return ar;
      for (final value in v.values) {
        final sv = s(value);
        if (sv != null && sv.trim().isNotEmpty) return sv;
      }
      return '';
    }
    return v.toString();
  }

  factory RoomType.fromJson(Map<String, dynamic> json) {
    final roomsJson = json['rooms'] as List? ?? [];
    final rooms = roomsJson
        .map((e) => Room.fromJson(e as Map<String, dynamic>))
        .toList();

    return RoomType(
      type: json['type'] as String? ?? 'other',
      typeName: _parseTranslatableString(json['type_name']),
      typeLabel: json['type_label'] as String? ?? '',
      rooms: rooms,
    );
  }
}

class Room {
  final int id;
  final String? roomNumber;
  final String? floor;
  final String? type;
  final String typeName;
  final String? description;
  final double? pricePerNight;
  final String? formattedPricePerNight;
  final int? capacity;
  final List<String>? amenities;
  final String? image;
  final String? status;
  final String? statusLabel;

  Room({
    required this.id,
    this.roomNumber,
    this.floor,
    this.type,
    required this.typeName,
    this.description,
    this.pricePerNight,
    this.formattedPricePerNight,
    this.capacity,
    this.amenities,
    this.image,
    this.status,
    this.statusLabel,
  });

  static int _parseIntSafe(dynamic v) {
    if (v == null) return 0;
    if (v is int) return v;
    if (v is num) return v.toInt();
    if (v is String) return int.tryParse(v) ?? 0;
    return 0;
  }

  static String _parseTranslatableString(dynamic v) {
    if (v == null) return '';
    if (v is String) return v;
    if (v is Map) {
      String? s(dynamic x) => x is String ? x : (x?.toString());
      final fr = s(v['fr']);
      if (fr != null && fr.trim().isNotEmpty) return fr;
      final en = s(v['en']);
      if (en != null && en.trim().isNotEmpty) return en;
      final es = s(v['es']);
      if (es != null && es.trim().isNotEmpty) return es;
      final ar = s(v['ar']);
      if (ar != null && ar.trim().isNotEmpty) return ar;
      for (final value in v.values) {
        final sv = s(value);
        if (sv != null && sv.trim().isNotEmpty) return sv;
      }
      return '';
    }
    return v.toString();
  }

  static List<String>? _parseStringList(dynamic v) {
    if (v == null) return null;
    if (v is List) {
      return v
          .map((e) => e?.toString() ?? '')
          .where((e) => e.isNotEmpty)
          .toList();
    }
    if (v is String) {
      final s = v.trim();
      if (s.isEmpty) return null;
      try {
        final decoded = jsonDecode(s);
        if (decoded is List) {
          return decoded
              .map((e) => e?.toString() ?? '')
              .where((e) => e.isNotEmpty)
              .toList();
        }
      } catch (_) {}
      return [s];
    }
    return null;
  }

  static double _parseDouble(dynamic value) {
    if (value == null) return 0.0;
    if (value is num) return value.toDouble();
    if (value is String) return double.tryParse(value) ?? 0.0;
    return 0.0;
  }

  factory Room.fromJson(Map<String, dynamic> json) {
    return Room(
      id: _parseIntSafe(json['id']),
      roomNumber: json['room_number'] as String?,
      floor: json['floor'] as String?,
      type: json['type'] as String?,
      typeName: _parseTranslatableString(json['type_name']),
      description: _parseTranslatableString(json['description']).trim().isEmpty
          ? null
          : _parseTranslatableString(json['description']),
      pricePerNight: json['price_per_night'] != null
          ? _parseDouble(json['price_per_night'])
          : null,
      formattedPricePerNight: json['formatted_price_per_night'] as String?,
      capacity: json['capacity'] as int?,
      amenities: _parseStringList(json['amenities']),
      image: json['image'] as String?,
      status: json['status'] as String?,
      statusLabel: json['status_label'] as String?,
    );
  }
}
