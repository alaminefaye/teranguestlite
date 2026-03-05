/// Établissement (autre site du groupe) — liste.
class Establishment {
  final int id;
  final String name;
  final String? location;
  final String? coverPhoto;

  Establishment({
    required this.id,
    required this.name,
    this.location,
    this.coverPhoto,
  });

  static int _parseId(dynamic v) {
    if (v == null) return 0;
    if (v is int) return v;
    if (v is num) return v.toInt();
    if (v is String) return int.tryParse(v) ?? 0;
    return 0;
  }

  factory Establishment.fromJson(Map<String, dynamic> json) {
    final cover = json['cover_photo'] as String?;
    return Establishment(
      id: _parseId(json['id']),
      name: json['name'] as String? ?? '',
      location: json['location'] as String?,
      coverPhoto: cover != null && cover.isNotEmpty ? cover : null,
    );
  }
}

/// Détail d'un établissement avec galerie photos.
class EstablishmentDetail {
  final int id;
  final String name;
  final String? location;
  final String? coverPhoto;
  final String? description;
  final String? address;
  final String? phone;
  final String? website;
  final List<EstablishmentPhoto> photos;

  EstablishmentDetail({
    required this.id,
    required this.name,
    this.location,
    this.coverPhoto,
    this.description,
    this.address,
    this.phone,
    this.website,
    required this.photos,
  });

  static int _parseId(dynamic v) {
    if (v == null) return 0;
    if (v is int) return v;
    if (v is num) return v.toInt();
    if (v is String) return int.tryParse(v) ?? 0;
    return 0;
  }

  factory EstablishmentDetail.fromJson(Map<String, dynamic> json) {
    final photosList = json['photos'] as List<dynamic>? ?? [];
    return EstablishmentDetail(
      id: _parseId(json['id']),
      name: json['name'] as String? ?? '',
      location: json['location'] as String?,
      coverPhoto: json['cover_photo'] as String?,
      description: json['description'] as String?,
      address: json['address'] as String?,
      phone: json['phone'] as String?,
      website: json['website'] as String?,
      photos: photosList
          .map((e) => EstablishmentPhoto.fromJson(e as Map<String, dynamic>))
          .toList(),
    );
  }
}

class EstablishmentPhoto {
  final int id;
  final String? url;
  final String? caption;

  EstablishmentPhoto({required this.id, this.url, this.caption});

  static int _parseId(dynamic v) {
    if (v == null) return 0;
    if (v is int) return v;
    if (v is num) return v.toInt();
    if (v is String) return int.tryParse(v) ?? 0;
    return 0;
  }

  factory EstablishmentPhoto.fromJson(Map<String, dynamic> json) {
    return EstablishmentPhoto(
      id: _parseId(json['id']),
      url: json['url'] as String?,
      caption: json['caption'] as String?,
    );
  }
}
