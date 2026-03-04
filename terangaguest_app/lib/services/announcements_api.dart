import 'package:flutter/foundation.dart';
import 'package:dio/dio.dart';
import '../config/api_config.dart';
import '../models/announcement.dart';
import 'api_service.dart';

class AnnouncementsApi {
  final ApiService _apiService = ApiService();

  /// Récupère la liste des annonces éligibles pour l'entreprise de l'utilisateur connecté.
  /// Retourne les annonces super admin ciblant l'entreprise + les annonces propres de l'entreprise,
  /// mélangées et triées par display_order.
  Future<List<Announcement>> fetchAnnouncements() async {
    try {
      final response = await _apiService.get(ApiConfig.announcements);
      final data = response.data['data'] as List? ?? [];
      return data
          .map((json) => Announcement.fromJson(json as Map<String, dynamic>))
          .toList();
    } on DioException catch (e) {
      debugPrint('❌ AnnouncementsApi.fetchAnnouncements: $e');
      rethrow;
    }
  }

  /// Enregistre une vue pour l'annonce donnée.
  /// Appelé en fire-and-forget : les erreurs sont silencieuses (ne bloquent pas l'UI).
  Future<void> recordView(int announcementId) async {
    try {
      await _apiService.post('${ApiConfig.announcements}/$announcementId/view');
    } catch (e) {
      // Silencieux : ne pas bloquer l'affichage en cas d'erreur réseau
      debugPrint('⚠️ AnnouncementsApi.recordView($announcementId): $e');
    }
  }
}
