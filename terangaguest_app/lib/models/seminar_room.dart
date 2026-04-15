import 'dart:convert';

class SeminarRoom {
  final int id;
  final String name;
  final String? description;
  final int? capacity;
  final List<String> equipments;
  final String? image;
  final String? contactPhone;
  final String? contactEmail;

  SeminarRoom({
    required this.id,
    required this.name,
    this.description,
    this.capacity,
    required this.equipments,
    this.image,
    this.contactPhone,
    this.contactEmail,
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

  static List<String> _parseStringList(dynamic v) {
    if (v == null) return const [];
    if (v is List) {
      return v.map((e) => e?.toString() ?? '').where((e) => e.isNotEmpty).toList();
    }
    if (v is String) {
      final s = v.trim();
      if (s.isEmpty) return const [];
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
    return const [];
  }

  factory SeminarRoom.fromJson(Map<String, dynamic> json) {
    final desc = _parseTranslatableString(json['description']).trim();
    return SeminarRoom(
      id: _parseIntSafe(json['id']),
      name: _parseTranslatableString(json['name']),
      description: desc.isEmpty ? null : desc,
      capacity: json['capacity'] == null ? null : _parseIntSafe(json['capacity']),
      equipments: _parseStringList(json['equipments']),
      image: json['image'] as String?,
      contactPhone: json['contact_phone'] as String?,
      contactEmail: json['contact_email'] as String?,
    );
  }
}

