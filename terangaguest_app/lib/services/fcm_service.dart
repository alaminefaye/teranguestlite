import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/foundation.dart';
import 'package:dio/dio.dart';
import '../config/api_config.dart';
import 'api_service.dart';

/// Enregistre le token FCM auprès du backend pour recevoir les notifications push.
class FcmService {
  final ApiService _api = ApiService();

  /// Récupère le token FCM et l'envoie au backend (à appeler après connexion).
  Future<void> registerTokenIfNeeded() async {
    try {
      await requestPermission();
      final token = await FirebaseMessaging.instance.getToken();
      if (token == null || token.isEmpty) return;
      await _api.post(
        ApiConfig.fcmToken,
        data: {'fcm_token': token},
      );
      debugPrint('FCM token enregistré');
    } on DioException catch (e) {
      debugPrint('FCM register error: $e');
    } catch (e) {
      debugPrint('FCM token error: $e');
    }
  }

  /// Supprime le token côté backend (à appeler à la déconnexion).
  Future<void> unregisterToken() async {
    try {
      await _api.delete(ApiConfig.fcmToken);
    } catch (e) {
      debugPrint('FCM unregister error: $e');
    }
  }

  /// Demande les autorisations de notification (iOS).
  Future<bool> requestPermission() async {
    final settings = await FirebaseMessaging.instance.requestPermission(
      alert: true,
      badge: true,
      sound: true,
    );
    return settings.authorizationStatus == AuthorizationStatus.authorized ||
        settings.authorizationStatus == AuthorizationStatus.provisional;
  }
}
