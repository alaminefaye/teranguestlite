/// Modèle représentant une annonce (affiche et/ou vidéo).
class Announcement {
  final int id;
  final String? title;
  final String? posterUrl;
  final String? videoUrl;
  final String type; // 'poster_only' | 'video_only' | 'both'
  final int displayOrder;
  final int
  displayDurationMinutes; // durée en minutes pour affiche seule, défaut 1

  const Announcement({
    required this.id,
    this.title,
    this.posterUrl,
    this.videoUrl,
    required this.type,
    required this.displayOrder,
    required this.displayDurationMinutes,
  });

  factory Announcement.fromJson(Map<String, dynamic> json) {
    return Announcement(
      id: json['id'] as int,
      title: json['title'] as String?,
      posterUrl: json['poster_url'] as String?,
      videoUrl: json['video_url'] as String?,
      type: json['type'] as String? ?? 'poster_only',
      displayOrder: json['display_order'] as int? ?? 0,
      displayDurationMinutes: json['display_duration_minutes'] as int? ?? 1,
    );
  }

  bool get hasPoster => posterUrl != null && posterUrl!.isNotEmpty;
  bool get hasVideo => videoUrl != null && videoUrl!.isNotEmpty;

  Duration get displayDuration => Duration(minutes: displayDurationMinutes);
}
