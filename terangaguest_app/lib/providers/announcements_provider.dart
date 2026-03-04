import 'package:flutter/foundation.dart';
import '../models/announcement.dart';
import '../services/announcements_api.dart';

class AnnouncementsProvider with ChangeNotifier {
  final AnnouncementsApi _api = AnnouncementsApi();

  List<Announcement> _announcements = [];
  bool _isLoading = false;
  String? _error;

  List<Announcement> get announcements => _announcements;
  bool get isLoading => _isLoading;
  String? get error => _error;
  bool get hasAnnouncements => _announcements.isNotEmpty;

  /// Charge les annonces éligibles depuis l'API.
  /// Silencieux en cas d'erreur (on ne bloque pas l'UI).
  Future<void> loadAnnouncements() async {
    if (_isLoading) return;
    _isLoading = true;
    _error = null;
    notifyListeners();

    try {
      _announcements = await _api.fetchAnnouncements();
      _error = null;
    } catch (e) {
      _error = e.toString();
      debugPrint('⚠️ AnnouncementsProvider.loadAnnouncements: $e');
      // Ne pas écraser la liste existante si on avait déjà des données
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Forcer le rechargement (ex. après retour en veille ou changement de session).
  Future<void> refresh() async {
    _announcements = [];
    await loadAnnouncements();
  }

  /// Enregistre une vue pour l'annonce donnée (fire-and-forget).
  /// N'attend pas le résultat et ne bloque jamais l'UI.
  void recordView(int announcementId) {
    _api.recordView(announcementId);
  }

  /// Vide les données (appelé lors d'un changement de session/checkout).
  void clearUserData() {
    _announcements = [];
    _error = null;
    notifyListeners();
  }
}
