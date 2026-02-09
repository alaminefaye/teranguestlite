import 'dart:io';

import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/foundation.dart';

import '../config/api_config.dart';
import '../models/guest_session.dart';
import 'api_service.dart';
import 'tablet_session_api.dart';

/// Gère les notifications push FCM : permissions, token, enregistrement backend, réception.
///
/// Exigences (synchronisées avec le backend) :
/// - Notifications pour : nouvelle commande, commande validée, changement de statut commande,
///   réservations (spa, restaurant, excursions, blanchisserie, palace).
/// - Uniquement le client concerné : token enregistré par guest_id (tablette) ou par user + guest
///   si séjour actif (app). Le backend n'envoie qu'aux tokens liés à ce client.
/// - À faire : après login → registerWithBackendForUser() ; après validation code tablette →
///   registerWithBackendForTabletSession() ; au logout → unregisterFromBackend().
@pragma('vm:entry-point')
Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  await Firebase.initializeApp();
  debugPrint('Background message: ${message.messageId}');
}

class NotificationService {
  static final NotificationService _instance = NotificationService._internal();
  factory NotificationService() => _instance;

  NotificationService._internal();

  final ApiService _api = ApiService();
  final FirebaseMessaging _messaging = FirebaseMessaging.instance;
  String? _currentFcmToken;

  String? get currentFcmToken => _currentFcmToken;

  /// Initialise Firebase et les notifications (permissions + token + handlers).
  /// À appeler au démarrage de l'app (main.dart).
  Future<void> init() async {
    try {
      await Firebase.initializeApp();
    } catch (e) {
      debugPrint('Firebase already initialized or error: $e');
    }

    // Gestion des messages en arrière-plan / terminé
    FirebaseMessaging.onBackgroundMessage(_firebaseMessagingBackgroundHandler);

    // Présentation en premier plan (iOS)
    if (Platform.isIOS) {
      await _messaging.setForegroundNotificationPresentationOptions(
        alert: true,
        badge: true,
        sound: true,
      );
    }

    // Demander les permissions (iOS + Android 13+)
    await requestPermission();

    // Récupérer le token
    await _refreshToken();

    // Écouter le rafraîchissement du token
    _messaging.onTokenRefresh.listen((_) => _refreshToken());

    // Messages en premier plan
    FirebaseMessaging.onMessage.listen(_handleForegroundMessage);

    // Clic sur une notification (app en arrière-plan ou fermée)
    FirebaseMessaging.onMessageOpenedApp.listen(_handleNotificationTap);

    // Notification qui a ouvert l'app (app fermée)
    final initial = await _messaging.getInitialMessage();
    if (initial != null) _handleNotificationTap(initial);
  }

  /// Demande la permission de notification (iOS et Android 13+).
  Future<bool> requestPermission() async {
    final settings = await _messaging.requestPermission(
      alert: true,
      badge: true,
      sound: true,
      provisional: false,
    );
    final granted = settings.authorizationStatus == AuthorizationStatus.authorized ||
        settings.authorizationStatus == AuthorizationStatus.provisional;
    if (!granted) {
      debugPrint('Notification permission not granted: ${settings.authorizationStatus}');
    }
    return granted;
  }

  Future<void> _refreshToken() async {
    try {
      final token = await _messaging.getToken();
      if (token != null && token.isNotEmpty) {
        _currentFcmToken = token;
        debugPrint('FCM token refreshed');
      }
    } catch (e) {
      debugPrint('FCM getToken error: $e');
    }
  }

  /// Enregistre le token FCM côté backend pour l'utilisateur connecté.
  /// À appeler après un login réussi (AuthProvider).
  Future<void> registerWithBackendForUser() async {
    final token = _currentFcmToken ?? await _messaging.getToken();
    if (token == null || token.isEmpty) return;
    try {
      await _api.post(
        ApiConfig.fcmToken,
        data: {'fcm_token': token},
      );
      debugPrint('FCM token registered for user');
    } catch (e) {
      debugPrint('FCM register for user error: $e');
    }
  }

  /// Enregistre le token FCM pour la session tablette (guest).
  /// À appeler après validation du code client (TabletSessionProvider).
  Future<void> registerWithBackendForTabletSession(GuestSession session) async {
    final token = _currentFcmToken ?? await _messaging.getToken();
    if (token == null || token.isEmpty) return;
    try {
      await TabletSessionApi().registerFcmToken(session: session, fcmToken: token);
      debugPrint('FCM token registered for tablet guest');
    } catch (e) {
      debugPrint('FCM register for tablet error: $e');
    }
  }

  /// Supprime le token côté backend (à appeler au logout).
  Future<void> unregisterFromBackend() async {
    try {
      await _api.delete(ApiConfig.fcmToken);
    } catch (e) {
      debugPrint('FCM unregister error: $e');
    }
  }

  void _handleForegroundMessage(RemoteMessage message) {
    debugPrint('Foreground: ${message.notification?.title}');
    // Optionnel : afficher une snackbar ou in-app notification
  }

  void _handleNotificationTap(RemoteMessage message) {
    debugPrint('Notification tap: ${message.data}');
    final type = message.data['type'];
    final screen = message.data['screen'];
    final orderId = message.data['order_id'];
    // La navigation peut être gérée via un GlobalKey<NavigatorState> ou un route observer
    // selon votre architecture. Ici on se contente du log.
    if (screen != null) debugPrint('Navigate to: $screen id: $orderId type: $type');
  }
}
