import 'dart:convert';
import 'dart:io';

import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';

import '../config/api_config.dart';
import '../models/guest_session.dart';
import 'api_service.dart';
import 'tablet_session_api.dart';

/// Canal Android utilisé aussi dans AndroidManifest (com.google.firebase.messaging.default_notification_channel_id).
/// Créé avec priorité haute pour que les notifications s'affichent avec son et heads-up.
const String _kNotificationChannelId = 'terangaguest_orders';

/// Gère les notifications push FCM : permissions, token, enregistrement backend, réception.
///
/// Exigences (synchronisées avec le backend) :
/// - Notifications pour : nouvelle commande, commande validée, changement de statut commande,
///   réservations (spa, restaurant, excursions, blanchisserie, palace).
/// - Uniquement le client concerné : token enregistré par guest_id (tablette) ou par user + guest
///   si séjour actif (app). Le backend n'envoie qu'aux tokens liés à ce client.
/// - À faire : après login → registerWithBackendForUser() ; après validation code tablette →
///   registerWithBackendForTabletSession() ; au logout → unregisterFromBackend().
/// Préfixe pour filtrer les logs FCM dans la console (ex: "FCM" dans le debug).
const String _kFcmLogTag = '[FCM]';

/// Élément d'historique pour la page Notifications.
class AppNotificationItem {
  AppNotificationItem({
    required this.title,
    required this.body,
    required this.data,
    required this.createdAt,
  });
  final String title;
  final String body;
  final Map<String, String> data;
  final DateTime createdAt;
}

/// Affiche un extrait du token pour debug (début...fin) sans exposer le token complet.
String _tokenPreview(String? token) {
  if (token == null || token.isEmpty) return '(vide)';
  if (token.length <= 16) return '${token.substring(0, token.length > 8 ? 8 : token.length)}...';
  return '${token.substring(0, 8)}...${token.substring(token.length - 8)}';
}

@pragma('vm:entry-point')
Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  await Firebase.initializeApp();
  debugPrint('$_kFcmLogTag Background message: ${message.messageId}');
}

class NotificationService {
  static final NotificationService _instance = NotificationService._internal();
  factory NotificationService() => _instance;

  NotificationService._internal();

  final ApiService _api = ApiService();
  final FirebaseMessaging _messaging = FirebaseMessaging.instance;
  final FlutterLocalNotificationsPlugin _localNotifications = FlutterLocalNotificationsPlugin();
  String? _currentFcmToken;
  int _notificationId = 0;

  /// Historique des notifications reçues (pour la page Notifications).
  final ValueNotifier<List<AppNotificationItem>> notificationHistory = ValueNotifier(<AppNotificationItem>[]);

  String? get currentFcmToken => _currentFcmToken;

  /// Ajoute une entrée à l'historique (appelé à la réception ou au tap).
  void addNotificationToHistory({required String title, required String body, Map<String, String>? data}) {
    final list = List<AppNotificationItem>.from(notificationHistory.value);
    list.insert(0, AppNotificationItem(
      title: title,
      body: body,
      data: data ?? {},
      createdAt: DateTime.now(),
    ));
    if (list.length > 100) list.removeRange(100, list.length);
    notificationHistory.value = list;
  }

  /// Déclenche une notification locale de test (vérifier canal et permissions).
  Future<void> showTestNotification() async {
    await _showLocalNotification(
      title: 'Test',
      body: 'Si vous voyez ceci, les notifications fonctionnent.',
      payload: null,
    );
    addNotificationToHistory(title: 'Test', body: 'Si vous voyez ceci, les notifications fonctionnent.');
  }

  /// Affiche dans la console l'état actuel du token (pour debug).
  void debugPrintState() {
    debugPrint('$_kFcmLogTag --- State ---');
    debugPrint('$_kFcmLogTag token: ${_tokenPreview(_currentFcmToken)}');
    debugPrint('$_kFcmLogTag ---');
  }

  /// Initialise Firebase et les notifications (permissions + token + handlers).
  /// À appeler au démarrage de l'app (main.dart).
  Future<void> init() async {
    debugPrint('$_kFcmLogTag init() start');
    try {
      await Firebase.initializeApp();
      debugPrint('$_kFcmLogTag Firebase.initializeApp() OK');
    } catch (e) {
      debugPrint('$_kFcmLogTag Firebase already initialized or error: $e');
    }

    // Gestion des messages en arrière-plan / terminé
    FirebaseMessaging.onBackgroundMessage(_firebaseMessagingBackgroundHandler);
    debugPrint('$_kFcmLogTag Background message handler registered');

    // Notifications locales : affichage en premier plan + canal Android priorité haute
    await _initLocalNotifications();

    // Présentation en premier plan (iOS)
    if (Platform.isIOS) {
      await _messaging.setForegroundNotificationPresentationOptions(
        alert: true,
        badge: true,
        sound: true,
      );
      debugPrint('$_kFcmLogTag iOS foreground presentation options set');
    }

    // Demander les permissions (iOS + Android 13+)
    final permitted = await requestPermission();
    debugPrint('$_kFcmLogTag Permission granted: $permitted');

    // Récupérer le token
    await _refreshToken();
    debugPrint('$_kFcmLogTag init() token after refresh: ${_tokenPreview(_currentFcmToken)}');

    // Écouter le rafraîchissement du token
    _messaging.onTokenRefresh.listen((_) {
      _refreshToken();
      debugPrint('$_kFcmLogTag onTokenRefresh: new token ${_tokenPreview(_currentFcmToken)}');
    });

    // Messages en premier plan
    FirebaseMessaging.onMessage.listen(_handleForegroundMessage);
    FirebaseMessaging.onMessageOpenedApp.listen(_handleNotificationTap);

    // Notification qui a ouvert l'app (app fermée)
    final initial = await _messaging.getInitialMessage();
    if (initial != null) {
      debugPrint('$_kFcmLogTag App opened from notification (cold start): ${initial.messageId}');
      _handleNotificationTap(initial);
    }
    debugPrint('$_kFcmLogTag init() done');
  }

  Future<void> _initLocalNotifications() async {
    const android = AndroidInitializationSettings('@mipmap/ic_launcher');
    const ios = DarwinInitializationSettings(
      requestAlertPermission: false,
      requestBadgePermission: false,
      requestSoundPermission: false,
    );
    const settings = InitializationSettings(android: android, iOS: ios);
    await _localNotifications.initialize(
      settings,
      onDidReceiveNotificationResponse: _onLocalNotificationTap,
    );
    // Canal Android : priorité haute pour son + heads-up (aligné sur AndroidManifest)
    if (Platform.isAndroid) {
      final channel = AndroidNotificationChannel(
        _kNotificationChannelId,
        'Commandes et réservations',
        description: 'Notifications de commandes et réservations',
        importance: Importance.high,
        playSound: true,
      );
      await _localNotifications
          .resolvePlatformSpecificImplementation<AndroidFlutterLocalNotificationsPlugin>()
          ?.createNotificationChannel(channel);
      debugPrint('$_kFcmLogTag Android notification channel created: $_kNotificationChannelId');
    }
  }

  void _onLocalNotificationTap(NotificationResponse response) {
    final payload = response.payload;
    if (payload == null || payload.isEmpty) return;
    try {
      final data = Map<String, String>.from(jsonDecode(payload) as Map<dynamic, dynamic>);
      debugPrint('$_kFcmLogTag Notification tap (local): $data');
      final screen = data['screen'];
      final orderId = data['order_id'];
      if (screen != null) debugPrint('Navigate to: $screen id: $orderId');
      // TODO: navigation via GlobalKey ou route si besoin
    } catch (_) {}
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
    debugPrint('$_kFcmLogTag requestPermission: ${settings.authorizationStatus} -> granted=$granted');
    return granted;
  }

  Future<void> _refreshToken() async {
    try {
      final token = await _messaging.getToken();
      if (token != null && token.isNotEmpty) {
        _currentFcmToken = token;
        debugPrint('$_kFcmLogTag Token refreshed: ${_tokenPreview(token)}');
      } else {
        debugPrint('$_kFcmLogTag getToken returned null or empty');
      }
    } catch (e) {
      debugPrint('$_kFcmLogTag getToken error: $e');
    }
  }

  /// Enregistre le token FCM côté backend pour l'utilisateur connecté.
  /// À appeler après un login réussi (AuthProvider).
  Future<void> registerWithBackendForUser() async {
    debugPrint('$_kFcmLogTag registerWithBackendForUser() called');
    final token = _currentFcmToken ?? await _messaging.getToken();
    if (token == null || token.isEmpty) {
      debugPrint('$_kFcmLogTag registerWithBackendForUser: no token, skip');
      return;
    }
    try {
      await _api.post(
        ApiConfig.fcmToken,
        data: {'fcm_token': token},
      );
      debugPrint('$_kFcmLogTag registerWithBackendForUser: OK (token ${_tokenPreview(token)})');
    } catch (e) {
      debugPrint('$_kFcmLogTag registerWithBackendForUser: ERROR $e');
    }
  }

  /// Enregistre le token FCM pour la session tablette (guest).
  /// À appeler après validation du code client (TabletSessionProvider).
  Future<void> registerWithBackendForTabletSession(GuestSession session) async {
    debugPrint('$_kFcmLogTag registerWithBackendForTabletSession(guestId=${session.guestId}) called');
    final token = _currentFcmToken ?? await _messaging.getToken();
    if (token == null || token.isEmpty) {
      debugPrint('$_kFcmLogTag registerWithBackendForTabletSession: no token, skip');
      return;
    }
    try {
      await TabletSessionApi().registerFcmToken(session: session, fcmToken: token);
      debugPrint('$_kFcmLogTag registerWithBackendForTabletSession: OK guestId=${session.guestId} token ${_tokenPreview(token)}');
    } catch (e) {
      debugPrint('$_kFcmLogTag registerWithBackendForTabletSession: ERROR $e');
    }
  }

  /// Supprime le token côté backend (à appeler au logout).
  Future<void> unregisterFromBackend() async {
    debugPrint('$_kFcmLogTag unregisterFromBackend() called');
    try {
      await _api.delete(ApiConfig.fcmToken);
      debugPrint('$_kFcmLogTag unregisterFromBackend: OK');
    } catch (e) {
      debugPrint('$_kFcmLogTag unregisterFromBackend: ERROR $e');
    }
  }

  void _handleForegroundMessage(RemoteMessage message) {
    debugPrint('$_kFcmLogTag Foreground message: title=${message.notification?.title} body=${message.notification?.body} data=${message.data}');
    final title = message.notification?.title ?? 'Notification';
    final body = message.notification?.body ?? '';
    final data = Map<String, String>.from(message.data);
    final payload = data.isNotEmpty ? jsonEncode(data) : null;
    _showLocalNotification(title: title, body: body, payload: payload);
    addNotificationToHistory(title: title, body: body, data: data);
  }

  Future<void> _showLocalNotification({required String title, required String body, String? payload}) async {
    final id = _notificationId++;
    const android = AndroidNotificationDetails(
      _kNotificationChannelId,
      'Commandes et réservations',
      channelDescription: 'Notifications de commandes et réservations',
      importance: Importance.high,
      priority: Priority.high,
      playSound: true,
    );
    const ios = DarwinNotificationDetails(presentAlert: true, presentSound: true);
    const details = NotificationDetails(android: android, iOS: ios);
    await _localNotifications.show(id, title, body, details, payload: payload);
  }

  void _handleNotificationTap(RemoteMessage message) {
    debugPrint('$_kFcmLogTag Notification tap: data=${message.data}');
    final title = message.notification?.title ?? 'Notification';
    final body = message.notification?.body ?? '';
    final data = Map<String, String>.from(message.data);
    addNotificationToHistory(title: title, body: body, data: data);
    final type = data['type'];
    final screen = data['screen'];
    final orderId = data['order_id'];
    if (screen != null) debugPrint('Navigate to: $screen id: $orderId type: $type');
  }
}
