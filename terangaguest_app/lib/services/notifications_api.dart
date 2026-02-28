import 'package:flutter/foundation.dart';
import 'package:dio/dio.dart';
import '../config/api_config.dart';
import 'api_service.dart';

class NotificationsApi {
  final ApiService _apiService = ApiService();

  /// Récupère les notifications non lues de l'utilisateur connecté.
  Future<List<Map<String, dynamic>>> fetchUnread() async {
    try {
      final response = await _apiService.get('${ApiConfig.notifications}/unread');
      final data = response.data;
      if (data is Map && data['data'] is List) {
        return List<Map<String, dynamic>>.from(
          (data['data'] as List).whereType<Map>().map((e) => Map<String, dynamic>.from(e)),
        );
      }
      return [];
    } on DioException catch (e) {
      debugPrint('❌ API Error (notifications unread): $e');
      return [];
    } catch (e) {
      debugPrint('❌ Error parsing notifications: $e');
      return [];
    }
  }

  /// Marque une notification comme lue.
  Future<void> markAsRead(int notificationId) async {
    try {
      await _apiService.post('${ApiConfig.notifications}/$notificationId/read');
    } on DioException catch (e) {
      debugPrint('❌ API Error (notifications mark-read): $e');
    }
  }

  Future<void> markAllAsRead() async {
    try {
      await _apiService.post('${ApiConfig.notifications}/mark-all-read');
    } on DioException catch (e) {
      debugPrint('❌ API Error (notifications mark-all-read): $e');
      rethrow;
    }
  }

  Future<void> cleanupRead() async {
    try {
      await _apiService.delete('${ApiConfig.notifications}/cleanup');
    } on DioException catch (e) {
      debugPrint('❌ API Error (notifications cleanup): $e');
      rethrow;
    }
  }
}
