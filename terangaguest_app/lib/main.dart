import 'dart:async';
import 'package:flutter/foundation.dart' show kIsWeb;

import 'package:firebase_core/firebase_core.dart';
import 'src/platform_check_stub.dart'
    if (dart.library.io) 'src/platform_check_io.dart'
    as platform_check;
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:intl/date_symbol_data_local.dart';
import 'package:provider/provider.dart';
import 'package:audioplayers/audioplayers.dart';
import 'config/theme.dart';
import 'generated/l10n/app_localizations.dart';
import 'screens/auth/splash_screen.dart';
import 'screens/orders/order_detail_screen.dart';
import 'screens/invoices/invoice_receipt_dialog.dart';
import 'screens/spa/my_spa_reservations_screen.dart';
import 'screens/restaurants/my_reservations_screen.dart';
import 'screens/excursions/my_excursion_bookings_screen.dart';
import 'screens/laundry/my_laundry_requests_screen.dart';
import 'screens/palace/my_palace_requests_screen.dart';
import 'providers/cart_provider.dart';
import 'providers/auth_provider.dart';
import 'providers/orders_provider.dart';
import 'providers/restaurants_provider.dart';
import 'providers/spa_provider.dart';
import 'providers/excursions_provider.dart';
import 'providers/laundry_provider.dart';
import 'providers/palace_provider.dart';
import 'providers/favorites_provider.dart';
import 'providers/locale_provider.dart';
import 'providers/tablet_session_provider.dart';
import 'providers/chat_unread_provider.dart';
import 'providers/announcements_provider.dart';
import 'providers/currency_provider.dart';
import 'services/fcm_service.dart';
import 'services/notifications_api.dart';
import 'utils/navigation_helper.dart';
import 'screens/admin/admin_chat_conversations_screen.dart';
import 'screens/hotel_infos/chatbot_screen.dart';
import 'screens/hotel_infos/emergency_requests_screen.dart';
import 'widgets/idle_overlay.dart';
import 'screens/dashboard/dashboard_screen.dart';

/// Handler appelé quand une notification est reçue en arrière-plan ou app terminée.
/// Doit être une fonction top-level pour que le système puisse l'exécuter même si l'app est killée.
@pragma('vm:entry-point')
Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  await Firebase.initializeApp();
  // On ne peut pas naviguer ici ; le tap utilisateur sera géré par getInitialMessage()
  // quand l'utilisateur ouvrira l'app en cliquant sur la notification.
}

/// Message initial capturé au démarrage (app ouverte en tapant sur une notif). Sur Android
/// il faut le lire le plus tôt possible, d'où la capture dans main().
RemoteMessage? _pendingInitialFcmMessage;

final GlobalKey<NavigatorState> rootNavigatorKey = GlobalKey<NavigatorState>();

void main() {
  runZonedGuarded(
    () async {
      // IMPORTANT: Ensure bindings are initialized FIRST, in the same zone as runApp
      WidgetsFlutterBinding.ensureInitialized();

      // Pour le web, on ignore FCM car on veut juste afficher le site via QR Code.
      if (!kIsWeb) {
        // Firebase (notifications push) — utilise google-services.json / GoogleService-Info.plist
        await Firebase.initializeApp();

        // Permet de recevoir les notifications push même quand l'app est fermée (terminated)
        FirebaseMessaging.onBackgroundMessage(
          _firebaseMessagingBackgroundHandler,
        );

        // Android : il faut lire getInitialMessage() le plus tôt possible pour capturer l'intent (tap sur notif = app lancée).
        // iOS : ne pas await ici sinon blocage du main → écran blanc ; on le récupère en arrière-plan.
        if (platform_check.isAndroid) {
          _pendingInitialFcmMessage = await FirebaseMessaging.instance
              .getInitialMessage();
        } else {
          FirebaseMessaging.instance.getInitialMessage().then((msg) {
            _pendingInitialFcmMessage = msg;
          });
        }
      }

      // Initialiser les locales pour les dates
      await initializeDateFormatting('fr_FR', null);
      await initializeDateFormatting('en_US', null);
      await initializeDateFormatting('es_ES', null);
      await initializeDateFormatting('ar_SA', null);

      // Configuration de la barre de statut
      SystemChrome.setSystemUIOverlayStyle(
        const SystemUiOverlayStyle(
          statusBarColor: Colors.transparent,
          statusBarIconBrightness: Brightness.light,
          systemNavigationBarColor: AppTheme.primaryDark,
          systemNavigationBarIconBrightness: Brightness.light,
        ),
      );

      // Par défaut : mode paysage (tablette in-room), avec portrait autorisé si besoin
      await SystemChrome.setPreferredOrientations([
        DeviceOrientation.landscapeLeft,
        DeviceOrientation.landscapeRight,
        DeviceOrientation.portraitUp,
      ]);

      runApp(const MyApp());
    },
    (error, stackTrace) {
      debugPrint('*** UNCAUGHT ASYNC ERROR ***');
      debugPrint(error.toString());
      debugPrint(stackTrace.toString());
    },
  );
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthProvider()),
        ChangeNotifierProvider(create: (_) => CartProvider()),
        ChangeNotifierProvider(create: (_) => OrdersProvider()),
        ChangeNotifierProvider(create: (_) => RestaurantsProvider()),
        ChangeNotifierProvider(create: (_) => SpaProvider()),
        ChangeNotifierProvider(create: (_) => ExcursionsProvider()),
        ChangeNotifierProvider(create: (_) => LaundryProvider()),
        ChangeNotifierProvider(create: (_) => PalaceProvider()),
        ChangeNotifierProvider(create: (_) => FavoritesProvider()),
        ChangeNotifierProvider(create: (_) => LocaleProvider()),
        ChangeNotifierProvider(create: (_) => TabletSessionProvider()),
        ChangeNotifierProvider(create: (_) => ChatUnreadProvider()),
        ChangeNotifierProvider(create: (_) => AnnouncementsProvider()),
        ChangeNotifierProvider(create: (_) {
          final p = CurrencyProvider();
          p.load(); // Taux de change (cache 24h), devise sauvegardée
          return p;
        }),
      ],
      child: const _LocalizedApp(),
    );
  }
}

class _LocalizedApp extends StatefulWidget {
  const _LocalizedApp();

  @override
  State<_LocalizedApp> createState() => _LocalizedAppState();
}

class _LocalizedAppState extends State<_LocalizedApp>
    with WidgetsBindingObserver {
  late final AudioPlayer _notificationPlayer;
  Timer? _notificationSoundTimer;
  Timer? _staffPollingTimer;
  final FcmService _fcmService = FcmService();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    _notificationPlayer = AudioPlayer();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<LocaleProvider>().load();
      context
          .read<TabletSessionProvider>()
          .load(); // ← charge la session invité depuis SharedPreferences
      if (!kIsWeb) {
        _setupFcmListeners();
        // Enregistrer le token FCM dès le premier lancement (Android : crée le canal et permet les notifs app fermée)
        _fcmService.registerTokenIfNeeded();
        _handleInitialFcmMessage();
      }
      // Polling des notifications en base (fallback garanti pour le staff)
      _startStaffNotificationPolling();
    });
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    _notificationSoundTimer?.cancel();
    _staffPollingTimer?.cancel();
    _notificationPlayer.dispose();
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    super.didChangeAppLifecycleState(state);
    if (!kIsWeb && state == AppLifecycleState.resumed) {
      _fcmService.registerTokenIfNeeded();
    }
  }

  void _startStaffNotificationPolling() {
    _staffPollingTimer?.cancel();
    // Polling toutes les 30 secondes — indépendant de FCM
    _staffPollingTimer = Timer.periodic(
      const Duration(seconds: 30),
      (_) => _pollStaffNotifications(),
    );
    // Premier appel immédiat après 5 secondes (laisser le temps à l'auth de se charger)
    Timer(const Duration(seconds: 5), _pollStaffNotifications);
  }

  Future<void> _pollStaffNotifications() async {
    try {
      final ctx = rootNavigatorKey.currentContext;
      if (ctx == null) return;

      final auth = ctx.read<AuthProvider>();
      if (!auth.isAuthenticated) return;
      if (!auth.isStaff && !auth.isAdmin) return;

      final notificationsApi = NotificationsApi();
      final unread = await notificationsApi.fetchUnread();

      for (final notif in unread) {
        final type = notif['type'] as String? ?? '';
        if (type != 'room_service_transfer') continue;

        // Seul le département "Service en chambre" doit voir ce popup
        // Les admins et les autres staffs (cuisine, etc.) l'ignorent
        final isRoomServiceStaff =
            auth.isStaff &&
            (auth.user?.department ?? '') == 'Service en chambre';
        if (!isRoomServiceStaff) {
          // Marquer comme lue silencieusement pour ne plus la retrouver au prochain cycle
          final notifId = notif['id'];
          final id = notifId is int
              ? notifId
              : int.tryParse(notifId?.toString() ?? '');
          if (id != null) await notificationsApi.markAsRead(id);
          continue;
        }

        final notifId = notif['id'];
        final id = notifId is int
            ? notifId
            : int.tryParse(notifId?.toString() ?? '');
        if (id == null) continue;

        // Marquer comme lue AVANT d'afficher pour éviter les doublons
        await notificationsApi.markAsRead(id);

        // Construire les données depuis le champ 'data' de la notification
        final rawData = notif['data'];
        Map<String, dynamic> data = {};
        if (rawData is Map<String, dynamic>) {
          data = rawData;
        } else if (rawData is Map) {
          data = Map<String, dynamic>.from(rawData);
        }
        // S'assurer que les champs nécessaires sont présents
        if (!data.containsKey('order_number')) {
          data['order_number'] = notif['title'] ?? '';
        }

        if (!mounted) return;
        _handleRoomServiceTransferNotification(data);

        // Petite pause entre deux popups si plusieurs notifs en attente
        await Future.delayed(const Duration(milliseconds: 500));
      }
    } catch (e) {
      debugPrint('Staff notification polling error: $e');
    }
  }

  void _startNotificationSoundLoop() {
    _notificationSoundTimer?.cancel();
    _notificationPlayer.stop();
    _notificationPlayer.setReleaseMode(ReleaseMode.loop);
    _notificationPlayer.play(AssetSource('notification.mp3'));
    _notificationSoundTimer = Timer(
      const Duration(minutes: 1),
      _stopNotificationSound,
    );
  }

  void _stopNotificationSound() {
    _notificationSoundTimer?.cancel();
    _notificationSoundTimer = null;
    _notificationPlayer.stop();
    _notificationPlayer.setReleaseMode(ReleaseMode.stop);
  }

  void _setupFcmListeners() {
    FirebaseMessaging.onMessage.listen((message) {
      final data = message.data;
      final type = data['type'] as String?;
      if (type == 'order_status') {
        _handleOrderStatusNotification(data);
      } else if (type == 'room_service_transfer') {
        // Reporter l'usage du context au prochain frame pour éviter use_build_context_synchronously
        WidgetsBinding.instance.addPostFrameCallback((_) {
          if (!mounted) return;
          final ctx = rootNavigatorKey.currentContext;
          if (ctx == null) return;
          final auth = ctx.read<AuthProvider>();
          final isRoomService =
              auth.isStaff &&
              (auth.user?.department ?? '') == 'Service en chambre';
          if (isRoomService) {
            _handleRoomServiceTransferNotification(data);
          }
        });
      } else if (type == 'spa_reservation_rescheduled') {
        _handleSpaRescheduleNotification(data);
      } else if (type == 'spa_reservation_status') {
        _handleSpaStatusNotification(data);
      } else if (type == 'restaurant_reservation_status' ||
          type == 'restaurant_reservation') {
        _handleRestaurantStatusNotification(data);
      } else if (type == 'excursion_booking_status' ||
          type == 'excursion_booking') {
        _handleExcursionStatusNotification(data);
      } else if (type == 'chat_message') {
        _handleChatMessageNotification(data, retryCount: 0);
      } else if (type == 'laundry_status') {
        _handleLaundryStatusNotification(data);
      } else if (type == 'palace_request_status' ||
          type == 'palace_status' ||
          type == 'palace' ||
          type == 'palace_request') {
        _handlePalaceStatusNotification(data);
      }
    });

    FirebaseMessaging.onMessageOpenedApp.listen((message) {
      final data = message.data;
      if (data.isEmpty) return;
      // Un frame pour être sûr que l'app est bien au premier plan
      WidgetsBinding.instance.addPostFrameCallback((_) {
        _handleOpenedFromNotification(data);
      });
    });
  }

  Future<void> _handleInitialFcmMessage() async {
    // Utiliser le message capturé dans main() (Android) ou le récupérer maintenant
    final message =
        _pendingInitialFcmMessage ??
        await FirebaseMessaging.instance.getInitialMessage();
    _pendingInitialFcmMessage = null;
    if (message == null) return;
    final data = message.data;
    if (data.isEmpty) return;

    // Le Splash attend 5s puis fait pushReplacement vers Dashboard/Login. On attend que ce soit fait
    // pour pousser l'écran cible (détail commande, chat, etc.) au-dessus du bon écran.
    await Future.delayed(const Duration(milliseconds: 5500));
    // Puis attendre que le navigator et l'auth soient prêts (au cas où initAuth serait un peu lent)
    const maxAttempts = 15;
    const interval = Duration(milliseconds: 400);
    for (var attempt = 0; attempt < maxAttempts; attempt++) {
      await Future.delayed(interval);
      final ctx = rootNavigatorKey.currentContext;
      final navigator = rootNavigatorKey.currentState;
      if (ctx == null || navigator == null || !ctx.mounted) continue;
      try {
        final auth = Provider.of<AuthProvider>(ctx, listen: false);
        if (!auth.isAuthenticated) continue;
        _handleOpenedFromNotification(data);
        return;
      } catch (_) {}
    }
  }

  void _handleChatMessageNotification(
    Map<String, dynamic> data, {
    int retryCount = 0,
  }) {
    final ctx = rootNavigatorKey.currentContext;
    if (ctx == null) {
      if (retryCount < 5) {
        final delays = [400, 600, 1000, 1500, 2000];
        WidgetsBinding.instance.addPostFrameCallback((_) {
          Future.delayed(
            Duration(milliseconds: delays[retryCount.clamp(0, 4)]),
            () {
              _handleChatMessageNotification(data, retryCount: retryCount + 1);
            },
          );
        });
      }
      return;
    }

    final auth = Provider.of<AuthProvider>(ctx, listen: false);
    final isStaffOrAdmin = auth.isAdmin || auth.isStaff;
    final senderType = data['sender_type'] as String?;

    // Ne jamais afficher le popup à l'expéditeur : seul le destinataire le voit.
    // Client envoie → notif aux staff uniquement. Staff envoie → notif au client uniquement.
    if (senderType != null) {
      if (isStaffOrAdmin && senderType == 'staff') {
        return; // Message du staff → je suis le staff, c'est mon envoi
      }
      if (!isStaffOrAdmin && senderType == 'guest') {
        return; // Message du client → je suis le client, c'est mon envoi
      }
    } else {
      // Payload incomplet (sender_type manquant) : ne pas afficher pour éviter d'afficher à l'expéditeur.
      return;
    }

    // Mettre à jour le badge "Hotel Infos & Sécurité" pour le client
    if (!isStaffOrAdmin) {
      try {
        ctx.read<ChatUnreadProvider>().loadUnreadCount();
      } catch (_) {}
    }

    _startNotificationSoundLoop();

    final l10n = AppLocalizations.of(ctx);
    final conversationIdRaw = data['conversation_id'] as String?;
    final conversationId = int.tryParse(conversationIdRaw ?? '');
    final guestName = data['guest_name'] as String? ?? 'Client chambre';
    final roomLabel = data['room_label'] as String? ?? '';
    final preview = data['message_preview'] as String? ?? '';
    final messageType =
        data['msg_type'] as String? ??
        data['message_type'] as String? ??
        'text';

    String typeLabel;
    if (messageType == 'image') {
      typeLabel = 'Image';
    } else if (messageType == 'audio') {
      typeLabel = 'Message vocal';
    } else if (messageType == 'text') {
      typeLabel = 'Message texte';
    } else {
      typeLabel = 'Fichier';
    }

    String title;
    String subtitle;

    if (isStaffOrAdmin) {
      title = 'Nouveau message client';
      subtitle = roomLabel.isNotEmpty ? '$guestName · $roomLabel' : guestName;
    } else {
      title = 'Nouveau message du staff';
      subtitle = guestName;
    }

    final body = preview.isNotEmpty
        ? preview
        : (isStaffOrAdmin
              ? 'Vous avez un nouveau message client dans le chat.'
              : 'Vous avez un nouveau message dans le chat.');

    showDialog(
      context: ctx,
      barrierDismissible: true,
      builder: (dialogContext) {
        return AlertDialog(
          backgroundColor: AppTheme.primaryBlue,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
            side: const BorderSide(color: AppTheme.accentGold, width: 1.5),
          ),
          title: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(
                title,
                style: const TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.bold,
                  fontSize: 18,
                ),
              ),
              const SizedBox(height: 4),
              Text(
                subtitle,
                style: const TextStyle(color: AppTheme.textGray, fontSize: 13),
              ),
            ],
          ),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'Type : $typeLabel',
                style: const TextStyle(
                  color: AppTheme.accentGold,
                  fontSize: 13,
                  fontWeight: FontWeight.w600,
                ),
              ),
              const SizedBox(height: 8),
              Text(
                body,
                style: const TextStyle(color: Colors.white, fontSize: 14),
              ),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () {
                _stopNotificationSound();
                Navigator.of(dialogContext, rootNavigator: true).pop();
              },
              child: Text(
                l10n.close,
                style: const TextStyle(
                  color: AppTheme.accentGold,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),
            if (isStaffOrAdmin && conversationId != null)
              TextButton(
                onPressed: () {
                  _stopNotificationSound();
                  Navigator.of(dialogContext, rootNavigator: true).pop();
                  final navigator = rootNavigatorKey.currentState;
                  if (navigator == null) return;
                  navigator.push(
                    NavigationHelper.slideRoute(
                      AdminChatConversationScreen(
                        conversationId: conversationId,
                        guestName: guestName,
                        roomLabel: roomLabel.isNotEmpty ? roomLabel : null,
                      ),
                    ),
                  );
                },
                child: const Text(
                  'Répondre',
                  style: TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              )
            else if (!isStaffOrAdmin)
              TextButton(
                onPressed: () {
                  _stopNotificationSound();
                  Navigator.of(dialogContext, rootNavigator: true).pop();
                  final navigator = rootNavigatorKey.currentState;
                  if (navigator == null) return;
                  navigator.push(
                    NavigationHelper.slideRoute(const ChatbotScreen()),
                  );
                },
                child: const Text(
                  'Ouvrir le chat',
                  style: TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ),
          ],
        );
      },
    ).then((_) => _stopNotificationSound());
  }

  void _handleOrderStatusNotification(Map<String, dynamic> data) {
    _startNotificationSoundLoop();

    final ctx = rootNavigatorKey.currentContext;
    if (ctx == null) return;

    final auth = Provider.of<AuthProvider>(ctx, listen: false);
    final isStaffOrAdmin = auth.isAdmin || auth.isStaff;

    final l10n = AppLocalizations.of(ctx);
    final orderIdRaw = data['order_id'] as String?;
    final orderNumber = data['order_number'] as String? ?? '';
    final status = data['status'] as String? ?? '';
    final rawReason = data['reason'] as String?;
    final reason = rawReason != null && rawReason.trim().isNotEmpty
        ? rawReason.trim()
        : null;
    final orderId = int.tryParse(orderIdRaw ?? '');
    final roomNumber = data['room_number'] as String?;
    final guestName = data['guest_name'] as String?;

    final statusLabel = _statusLabel(l10n, status);

    // Popup dédié pour le staff/admin quand un client annule une commande
    if (isStaffOrAdmin && status == 'cancelled') {
      final lines = <String>[];
      if (roomNumber != null && roomNumber.isNotEmpty) {
        lines.add('Chambre : $roomNumber');
      }
      if (guestName != null && guestName.isNotEmpty) {
        lines.add('Client : $guestName');
      }
      if (reason != null) {
        lines.add('Motif : $reason');
      }

      showDialog(
        context: ctx,
        barrierDismissible: true,
        builder: (dialogContext) {
          return AlertDialog(
            backgroundColor: AppTheme.primaryBlue,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(16),
              side: const BorderSide(color: Colors.redAccent, width: 1.5),
            ),
            title: Row(
              children: const [
                Icon(Icons.cancel_outlined, color: Colors.redAccent, size: 22),
                SizedBox(width: 8),
                Expanded(
                  child: Text(
                    'Commande annulée par le client',
                    style: TextStyle(
                      color: Colors.redAccent,
                      fontWeight: FontWeight.bold,
                      fontSize: 15,
                    ),
                  ),
                ),
              ],
            ),
            content: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                if (orderNumber.isNotEmpty)
                  Text(
                    'Commande #$orderNumber',
                    style: const TextStyle(
                      color: AppTheme.accentGold,
                      fontWeight: FontWeight.w700,
                      fontSize: 14,
                    ),
                  ),
                if (lines.isNotEmpty) ...[
                  const SizedBox(height: 8),
                  ...lines.map(
                    (line) => Padding(
                      padding: const EdgeInsets.only(top: 4),
                      child: Text(
                        line,
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 14,
                        ),
                      ),
                    ),
                  ),
                ],
              ],
            ),
            actions: [
              TextButton(
                onPressed: () {
                  _stopNotificationSound();
                  Navigator.of(dialogContext, rootNavigator: true).pop();
                },
                child: const Text(
                  'Fermer',
                  style: TextStyle(color: Colors.white70),
                ),
              ),
              if (orderId != null)
                TextButton(
                  onPressed: () {
                    _stopNotificationSound();
                    Navigator.of(dialogContext, rootNavigator: true).pop();
                    rootNavigatorKey.currentState?.push(
                      NavigationHelper.slideRoute(
                        OrderDetailScreen(orderId: orderId),
                      ),
                    );
                  },
                  child: const Text(
                    'Voir la commande',
                    style: TextStyle(
                      color: AppTheme.accentGold,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                ),
            ],
          );
        },
      ).then((_) => _stopNotificationSound());
      return;
    }

    if (status == 'delivered') {
      if (!isStaffOrAdmin) {
        showDialog(
          context: ctx,
          barrierDismissible: true,
          builder: (dialogContext) {
            return InvoiceReceiptDialog(
              orderId: orderId ?? 0,
              orderNumber: orderNumber,
            );
          },
        ).then((_) => _stopNotificationSound());
      }
      return;
    }

    showDialog(
      context: ctx,
      barrierDismissible: true,
      builder: (dialogContext) {
        return AlertDialog(
          backgroundColor: AppTheme.primaryBlue,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
            side: const BorderSide(color: AppTheme.accentGold, width: 1.5),
          ),
          title: Text(
            l10n.orderDetailTitle,
            style: const TextStyle(
              color: Colors.white,
              fontWeight: FontWeight.bold,
            ),
          ),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              if (orderNumber.isNotEmpty)
                Text(
                  '${l10n.orderNumberLabel} $orderNumber',
                  style: const TextStyle(
                    color: AppTheme.accentGold,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              const SizedBox(height: 8),
              Text(
                _statusMessage(l10n, statusLabel) +
                    ((status == 'cancelled' && reason != null)
                        ? '\nMotif : $reason'
                        : ''),
                style: const TextStyle(color: Colors.white, fontSize: 14),
              ),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () {
                _stopNotificationSound();
                Navigator.of(dialogContext, rootNavigator: true).pop();
              },
              child: Text(
                l10n.close,
                style: const TextStyle(
                  color: AppTheme.accentGold,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),
            if (orderId != null)
              TextButton(
                onPressed: () {
                  _stopNotificationSound();
                  Navigator.of(dialogContext, rootNavigator: true).pop();
                  final navigator = rootNavigatorKey.currentState;
                  if (navigator == null) return;
                  navigator.push(
                    NavigationHelper.slideRoute(
                      OrderDetailScreen(orderId: orderId),
                    ),
                  );
                },
                child: Text(
                  l10n.orderTracking,
                  style: const TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ),
          ],
        );
      },
    ).then((_) => _stopNotificationSound());
  }

  void _handleRoomServiceTransferNotification(Map<String, dynamic> data) {
    _startNotificationSoundLoop();

    final ctx = rootNavigatorKey.currentContext;
    if (ctx == null) {
      // Context pas encore prêt : réessayer après le prochain frame
      WidgetsBinding.instance.addPostFrameCallback((_) {
        _handleRoomServiceTransferNotification(data);
      });
      return;
    }

    final orderNumber = data['order_number'] as String? ?? '';
    final roomNumber = data['room_number'] as String? ?? '';
    final guestName = data['guest_name'] as String? ?? '';
    final orderIdRaw = data['order_id'] as String?;
    final orderId = int.tryParse(orderIdRaw ?? '');

    String bodyText = 'La commande $orderNumber est prête en cuisine.';
    if (roomNumber.isNotEmpty) {
      bodyText += '\nChambre : $roomNumber';
    }
    if (guestName.isNotEmpty) {
      bodyText += '\nClient : $guestName';
    }

    showDialog(
      context: ctx,
      barrierDismissible: true,
      builder: (dialogContext) {
        return AlertDialog(
          backgroundColor: AppTheme.primaryDark,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
            side: const BorderSide(color: AppTheme.accentGold, width: 2),
          ),
          title: const Row(
            children: [
              Icon(
                Icons.campaign_rounded,
                color: AppTheme.accentGold,
                size: 26,
              ),
              SizedBox(width: 10),
              Expanded(
                child: Text(
                  'Livraison à effectuer',
                  style: TextStyle(
                    color: AppTheme.accentGold,
                    fontWeight: FontWeight.bold,
                    fontSize: 17,
                  ),
                ),
              ),
            ],
          ),
          content: Text(
            bodyText,
            style: const TextStyle(
              color: Colors.white,
              fontSize: 15,
              height: 1.5,
            ),
          ),
          actions: [
            TextButton(
              onPressed: () {
                _stopNotificationSound();
                Navigator.of(dialogContext, rootNavigator: true).pop();
              },
              child: const Text(
                'Fermer',
                style: TextStyle(color: AppTheme.textGray),
              ),
            ),
            if (orderId != null)
              TextButton(
                onPressed: () {
                  _stopNotificationSound();
                  Navigator.of(dialogContext, rootNavigator: true).pop();
                  final navigator = rootNavigatorKey.currentState;
                  if (navigator == null) return;
                  navigator.push(
                    NavigationHelper.slideRoute(
                      OrderDetailScreen(orderId: orderId),
                    ),
                  );
                },
                child: const Text(
                  'Voir la commande',
                  style: TextStyle(
                    color: AppTheme.accentGold,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ),
          ],
        );
      },
    ).then((_) => _stopNotificationSound());
  }

  void _handleSpaRescheduleNotification(Map<String, dynamic> data) {
    _startNotificationSoundLoop();

    final ctx = rootNavigatorKey.currentContext;
    if (ctx == null) return;

    showDialog(
      context: ctx,
      barrierDismissible: true,
      builder: (dialogContext) {
        return AlertDialog(
          backgroundColor: AppTheme.primaryBlue,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
            side: const BorderSide(color: AppTheme.accentGold, width: 1.5),
          ),
          title: const Text(
            'Nouvel horaire spa proposé',
            style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
          ),
          content: const Text(
            'Un nouvel horaire est proposé pour votre réservation spa. '
            'Ouvrez vos réservations spa pour accepter ou annuler.',
            style: TextStyle(color: Colors.white, fontSize: 14),
          ),
          actions: [
            TextButton(
              onPressed: () {
                _stopNotificationSound();
                Navigator.of(dialogContext, rootNavigator: true).pop();
              },
              child: const Text(
                'Plus tard',
                style: TextStyle(
                  color: AppTheme.accentGold,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),
            TextButton(
              onPressed: () {
                _stopNotificationSound();
                Navigator.of(dialogContext, rootNavigator: true).pop();
                final navigator = rootNavigatorKey.currentState;
                if (navigator == null) return;
                navigator.push(
                  NavigationHelper.slideRoute(const MySpaReservationsScreen()),
                );
              },
              child: const Text(
                'Voir mes réservations spa',
                style: TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.w700,
                ),
              ),
            ),
          ],
        );
      },
    ).then((_) => _stopNotificationSound());
  }

  void _handleSpaStatusNotification(Map<String, dynamic> data) {
    _startNotificationSoundLoop();

    final ctx = rootNavigatorKey.currentContext;
    if (ctx == null) return;

    final l10n = AppLocalizations.of(ctx);
    final status = data['status'] as String? ?? '';
    final serviceName = data['service_name'] as String? ?? 'Spa';
    final screen = data['screen'] as String? ?? '';
    final rawReason = data['reason'] as String?;
    final reason = rawReason != null && rawReason.trim().isNotEmpty
        ? rawReason.trim()
        : null;
    final isStaff = screen == 'AdminSpaReservations';

    String title;
    String message;

    if (isStaff) {
      final date = data['date'] as String? ?? '';
      final time = data['time'] as String? ?? '';
      final roomNumber = data['room_number'] as String?;
      final guestName = data['guest_name'] as String?;

      final detailsParts = <String>[];
      if (roomNumber != null && roomNumber.isNotEmpty) {
        detailsParts.add('Chambre $roomNumber');
      }
      if (guestName != null && guestName.isNotEmpty) {
        detailsParts.add(guestName);
      }
      final detailsSuffix = detailsParts.isEmpty
          ? ''
          : ' (${detailsParts.join(' – ')})';

      if (status == 'confirmed') {
        title = 'Nouvel horaire spa confirmé';
        message =
            'Le client a confirmé le nouvel horaire pour $serviceName le $date à $time$detailsSuffix.';
      } else if (status == 'cancelled') {
        title = 'Réservation spa annulée par le client';
        message =
            'Le client a annulé la réservation $serviceName prévue le $date à $time$detailsSuffix.';
        if (reason != null) {
          message += '\nMotif : $reason';
        }
      } else {
        title = 'Réservation spa mise à jour';
        final label = _spaStatusLabel(l10n, status);
        message = 'Statut de la réservation spa mis à jour : $label.';
      }
    } else {
      title = 'Réservation spa';
      if (status == 'confirmed') {
        message = l10n.spaReservationConfirmedMessage(serviceName);
      } else if (status == 'cancelled') {
        message = l10n.reservationCancelledMessage;
        if (reason != null) {
          message += '\nMotif : $reason';
        }
      } else {
        final label = _spaStatusLabel(l10n, status);
        message = 'Statut de la réservation spa : $label';
      }
    }

    showDialog(
      context: ctx,
      barrierDismissible: true,
      builder: (dialogContext) {
        return AlertDialog(
          backgroundColor: AppTheme.primaryBlue,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
            side: const BorderSide(color: AppTheme.accentGold, width: 1.5),
          ),
          title: Text(
            title,
            style: const TextStyle(
              color: Colors.white,
              fontWeight: FontWeight.bold,
            ),
          ),
          content: Text(
            message,
            style: const TextStyle(color: Colors.white, fontSize: 14),
          ),
          actions: [
            TextButton(
              onPressed: () {
                _stopNotificationSound();
                Navigator.of(dialogContext, rootNavigator: true).pop();
              },
              child: const Text(
                'Fermer',
                style: TextStyle(
                  color: AppTheme.accentGold,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),
            TextButton(
              onPressed: () {
                _stopNotificationSound();
                Navigator.of(dialogContext, rootNavigator: true).pop();
                final navigator = rootNavigatorKey.currentState;
                if (navigator == null) return;
                navigator.push(
                  NavigationHelper.slideRoute(const MySpaReservationsScreen()),
                );
              },
              child: Text(
                isStaff
                    ? 'Voir les réservations spa à traiter'
                    : 'Voir les réservations spa',
                style: const TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.w700,
                ),
              ),
            ),
          ],
        );
      },
    ).then((_) => _stopNotificationSound());
  }

  void _handleOpenedFromNotification(Map<String, dynamic> data) {
    final type = data['type'] as String?;
    final navigator = rootNavigatorKey.currentState;
    final ctx = rootNavigatorKey.currentContext;
    if (navigator == null || ctx == null || !ctx.mounted) return;

    AuthProvider auth;
    try {
      auth = Provider.of<AuthProvider>(ctx, listen: false);
      if (!auth.isAuthenticated) return;
    } catch (_) {
      return;
    }

    if (type == 'chat_message') {
      final isStaffOrAdmin = auth.isAdmin || auth.isStaff;
      final conversationIdRaw = data['conversation_id'] as String?;
      final conversationId = int.tryParse(conversationIdRaw ?? '');
      final guestName = data['guest_name'] as String? ?? 'Client chambre';
      final roomLabel = data['room_label'] as String?;

      if (isStaffOrAdmin && conversationId != null) {
        navigator.push(
          NavigationHelper.slideRoute(
            AdminChatConversationScreen(
              conversationId: conversationId,
              guestName: guestName,
              roomLabel: roomLabel != null && roomLabel.isNotEmpty
                  ? roomLabel
                  : null,
            ),
          ),
        );
      } else {
        navigator.push(NavigationHelper.slideRoute(const ChatbotScreen()));
      }
      return;
    }

    if (type == 'order' ||
        type == 'order_status' ||
        type == 'room_service_transfer') {
      final orderIdValue = data['order_id'];
      final int? orderId = orderIdValue is int
          ? orderIdValue
          : int.tryParse(orderIdValue?.toString() ?? '');
      if (orderId != null) {
        navigator.push(
          NavigationHelper.slideRoute(OrderDetailScreen(orderId: orderId)),
        );
      }
      return;
    }

    if (type == 'spa_reservation' ||
        type == 'spa_reservation_status' ||
        type == 'spa_reservation_rescheduled') {
      navigator.push(
        NavigationHelper.slideRoute(const MySpaReservationsScreen()),
      );
      return;
    }

    if (type == 'restaurant_reservation' ||
        type == 'restaurant_reservation_status') {
      navigator.push(
        NavigationHelper.slideRoute(const MyRestaurantReservationsScreen()),
      );
      return;
    }

    if (type == 'excursion_booking' || type == 'excursion_booking_status') {
      navigator.push(
        NavigationHelper.slideRoute(const MyExcursionBookingsScreen()),
      );
      return;
    }

    if (type == 'laundry' || type == 'laundry_status') {
      navigator.push(
        NavigationHelper.slideRoute(const MyLaundryRequestsScreen()),
      );
      return;
    }

    if (type == 'palace' ||
        type == 'palace_request' ||
        type == 'palace_status') {
      navigator.push(
        NavigationHelper.slideRoute(const MyPalaceRequestsScreen()),
      );
      return;
    }

    if (type == 'emergency' || type == 'assistance_urgence') {
      navigator.push(
        NavigationHelper.slideRoute(const EmergencyRequestsScreen()),
      );
      return;
    }
  }

  String _statusLabel(AppLocalizations l10n, String status) {
    switch (status) {
      case 'pending':
        return l10n.statusPending;
      case 'confirmed':
        return l10n.statusConfirmed;
      case 'preparing':
        return l10n.statusPreparing;
      case 'ready':
        return l10n.statusReady;
      case 'delivering':
        return l10n.statusDelivering;
      case 'delivered':
        return l10n.statusDelivered;
      case 'cancelled':
        return l10n.statusCancelled;
      default:
        return status;
    }
  }

  String _spaStatusLabel(AppLocalizations l10n, String status) {
    switch (status) {
      case 'pending':
      case 'pending_reschedule':
        return l10n.statusPending;
      case 'confirmed':
        return l10n.statusConfirmed;
      case 'completed':
        return l10n.statusCompleted;
      case 'cancelled':
        return l10n.statusCancelled;
      default:
        return status;
    }
  }

  String _laundryStatusLabel(AppLocalizations l10n, String status) {
    switch (status) {
      case 'pending':
        return l10n.statusPending;
      case 'picked_up':
        return l10n.statusPickedUp;
      case 'processing':
        return l10n.statusPreparing; // Reuse preparing for processing
      case 'ready':
        return l10n.statusReady;
      case 'delivered':
        return l10n.statusDelivered;
      case 'cancelled':
        return l10n.statusCancelled;
      default:
        return status;
    }
  }

  Future<void> _handleLaundryStatusNotification(
    Map<String, dynamic> data,
  ) async {
    _startNotificationSoundLoop();

    final ctx = rootNavigatorKey.currentContext;
    if (ctx == null) return;

    final l10n = AppLocalizations.of(ctx);
    final status = data['status'] as String? ?? '';
    final requestIdRaw = data['request_id'] as String?;
    final requestNumber =
        data['request_number'] as String? ?? requestIdRaw ?? '';
    final screen = data['screen'] as String? ?? '';
    final rawReason = data['reason'] as String?;
    final reason = rawReason != null && rawReason.trim().isNotEmpty
        ? rawReason.trim()
        : null;
    final isStaff = screen == 'AdminLaundryRequests';

    String title;
    String message;

    if (isStaff) {
      final roomNumber = data['room_number'] as String?;
      final guestName = data['guest_name'] as String?;

      final detailsParts = <String>[];
      if (roomNumber != null && roomNumber.isNotEmpty) {
        detailsParts.add(l10n.roomLabelLong(roomNumber));
      }
      if (guestName != null && guestName.isNotEmpty) {
        detailsParts.add(guestName);
      }
      final detailsSuffix = detailsParts.isEmpty
          ? ''
          : ' (${detailsParts.join(' – ')})';

      if (status == 'cancelled') {
        title = l10n.laundryRequestCancelledByClient;
        message = l10n.laundryRequestCancelledByClientMessage(requestNumber, detailsSuffix);
        if (reason != null && reason.isNotEmpty) {
          message += '\n${l10n.reasonOptional} : $reason';
        }
      } else {
        title = l10n.laundryRequestUpdated;
        final label = _laundryStatusLabel(l10n, status);
        message = l10n.laundryRequestUpdatedMessage(requestNumber, label, detailsSuffix);
      }
    } else {
      final laundryProvider = Provider.of<LaundryProvider>(ctx, listen: false);
      final intRequestNumber = int.tryParse(requestNumber);

      String generatedItemsDesc = l10n.laundryRequest.toLowerCase();
      if (intRequestNumber != null) {
        var foundRequestList = laundryProvider.requests.where(
          (r) => r.id == intRequestNumber,
        );

        if (foundRequestList.isEmpty) {
          try {
            await laundryProvider.fetchMyLaundryRequests();
            foundRequestList = laundryProvider.requests.where(
              (r) => r.id == intRequestNumber,
            );
          } catch (_) {}
        }

        if (foundRequestList.isNotEmpty) {
          final foundRequest = foundRequestList.first;
          if (foundRequest.items.isNotEmpty) {
            generatedItemsDesc = foundRequest.items
                .map((e) {
                  if (e.quantity > 1) {
                    return '${e.quantity}x ${e.serviceName}';
                  }
                  return e.serviceName.toLowerCase();
                })
                .join(', ');
          }
        }
      }

      title = l10n.laundryRequest;
      if (status == 'picked_up') {
        message = l10n.laundryStatusPickedUpMessage(
          generatedItemsDesc.replaceFirst(generatedItemsDesc[0], generatedItemsDesc[0].toUpperCase()),
          requestNumber,
        );
      } else if (status == 'ready') {
        message = l10n.laundryStatusReadyMessage(
          generatedItemsDesc.replaceFirst(generatedItemsDesc[0], generatedItemsDesc[0].toUpperCase()),
          requestNumber,
        );
      } else if (status == 'delivered') {
        message = l10n.laundryStatusDeliveredMessage(
          generatedItemsDesc.replaceFirst(generatedItemsDesc[0], generatedItemsDesc[0].toUpperCase()),
          requestNumber,
        );
      } else if (status == 'cancelled') {
        message = l10n.laundryStatusCancelledMessage(requestNumber);
        if (reason != null) {
          message += '\n${l10n.reasonOptional} : $reason';
        }
      } else {
        final label = _laundryStatusLabel(l10n, status);
        message = l10n.laundryRequestUpdatedMessage(requestNumber, label, '');
      }
    }

    final itemsDesc = data['items'] as String?;
    final specialInstructions = data['special_instructions'] as String?;

    if (itemsDesc != null && itemsDesc.trim().isNotEmpty) {
      message += '\n\n${l10n.laundryItemsLabel(itemsDesc)}';
    }
    if (specialInstructions != null && specialInstructions.trim().isNotEmpty) {
      message += '\n\n${l10n.specialInstructionsLong(specialInstructions)}';
    }

    if (!ctx.mounted) return;

    showDialog(
      context: ctx,
      barrierDismissible: true,
      builder: (dialogContext) {
        return AlertDialog(
          backgroundColor: AppTheme.primaryBlue,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
            side: const BorderSide(color: AppTheme.accentGold, width: 1.5),
          ),
          title: Text(
            title,
            style: const TextStyle(
              color: Colors.white,
              fontWeight: FontWeight.bold,
            ),
          ),
          content: Text(
            message,
            style: const TextStyle(color: Colors.white, fontSize: 14),
          ),
          actions: [
            TextButton(
              onPressed: () {
                _stopNotificationSound();
                Navigator.of(dialogContext, rootNavigator: true).pop();
              },
              child: Text(
                l10n.closeButton,
                style: const TextStyle(
                  color: AppTheme.accentGold,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),
            TextButton(
              onPressed: () {
                _stopNotificationSound();
                Navigator.of(dialogContext, rootNavigator: true).pop();
                final navigator = rootNavigatorKey.currentState;
                if (navigator == null) return;
                navigator.push(
                  NavigationHelper.slideRoute(const MyLaundryRequestsScreen()),
                );
              },
              child: Text(
                isStaff ? l10n.viewRequests : l10n.viewMyRequests,
                style: const TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.w700,
                ),
              ),
            ),
          ],
        );
      },
    ).then((_) => _stopNotificationSound());
  }

  void _handleRestaurantStatusNotification(Map<String, dynamic> data) {
    _startNotificationSoundLoop();

    final ctx = rootNavigatorKey.currentContext;
    if (ctx == null) return;

    final l10n = AppLocalizations.of(ctx);
    final status = data['status'] as String? ?? '';
    final restaurantName = data['restaurant_name'] as String? ?? 'Restaurant';
    final screen = data['screen'] as String? ?? '';
    final isStaff = screen == 'AdminRestaurantReservations';
    final date = data['date'] as String? ?? '';
    final time = data['time'] as String? ?? '';
    final roomNumber = data['room_number'] as String?;
    final guestName = data['guest_name'] as String?;
    final reason = data['reason'] as String?;

    String title;
    String message;

    if (isStaff) {
      final detailsParts = <String>[];
      if (roomNumber != null && roomNumber.isNotEmpty) {
        detailsParts.add(l10n.roomLabelLong(roomNumber));
      }
      if (guestName != null && guestName.isNotEmpty) {
        detailsParts.add(guestName);
      }
      final detailsSuffix = detailsParts.isEmpty
          ? ''
          : ' (${detailsParts.join(' – ')})';

      if (status == 'pending') {
        title = l10n.newRestaurantReservation;
        message = l10n.newRestaurantReservationMessage(restaurantName, date, time, detailsSuffix);
      } else if (status == 'cancelled') {
        title = l10n.restaurantReservationCancelledByClient;
        message = l10n.restaurantReservationCancelledByClientMessage(restaurantName, date, time, detailsSuffix);
        if (reason != null && reason.isNotEmpty) {
          message += '\n${l10n.reasonOptional} : $reason';
        }
      } else {
        title = l10n.restaurantReservationUpdated;
        final label = _restaurantStatusLabel(l10n, status);
        message = '${l10n.restaurantReservationUpdated} : $label.';
      }
    } else {
      title = l10n.restaurantReservation;
      if (status == 'confirmed') {
        message = l10n.restaurantReservationConfirmedMessage(restaurantName, date, time);
      } else if (status == 'cancelled') {
        message = l10n.reservationCancelledMessage;
        if (reason != null && reason.isNotEmpty) {
          message += '\n${l10n.reasonOptional} : $reason';
        }
      } else if (status == 'completed') {
        message = l10n.restaurantReservationHonoredMessage(restaurantName);
      } else {
        final label = _restaurantStatusLabel(l10n, status);
        message = '${l10n.restaurantReservation} : $label';
      }
    }

    showDialog(
      context: ctx,
      barrierDismissible: true,
      builder: (dialogContext) {
        return AlertDialog(
          backgroundColor: AppTheme.primaryBlue,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
            side: const BorderSide(color: AppTheme.accentGold, width: 1.5),
          ),
          title: Text(
            title,
            style: const TextStyle(
              color: Colors.white,
              fontWeight: FontWeight.bold,
            ),
          ),
          content: Text(
            message,
            style: const TextStyle(color: Colors.white, fontSize: 14),
          ),
          actions: [
            TextButton(
              onPressed: () {
                _stopNotificationSound();
                Navigator.of(dialogContext, rootNavigator: true).pop();
              },
              child: Text(
                l10n.closeButton,
                style: const TextStyle(
                  color: AppTheme.accentGold,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),
            TextButton(
              onPressed: () {
                _stopNotificationSound();
                Navigator.of(dialogContext, rootNavigator: true).pop();
                final navigator = rootNavigatorKey.currentState;
                if (navigator == null) return;
                navigator.push(
                  NavigationHelper.slideRoute(
                    const MyRestaurantReservationsScreen(),
                  ),
                );
              },
              child: Text(
                isStaff
                    ? l10n.viewRequests // Simplified
                    : l10n.viewMyRequests,
                style: const TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.w700,
                ),
              ),
            ),
          ],
        );
      },
    ).then((_) => _stopNotificationSound());
  }

  String _restaurantStatusLabel(AppLocalizations l10n, String status) {
    switch (status) {
      case 'pending':
        return l10n.statusPending;
      case 'confirmed':
        return l10n.statusConfirmed;
      case 'cancelled':
        return l10n.statusCancelled;
      case 'completed':
        return l10n.statusCompleted;
      default:
        return status;
    }
  }

  void _handleExcursionStatusNotification(Map<String, dynamic> data) {
    _startNotificationSoundLoop();

    final ctx = rootNavigatorKey.currentContext;
    if (ctx == null) return;

    final l10n = AppLocalizations.of(ctx);
    final status = data['status'] as String? ?? '';
    final excursionName = data['excursion_name'] as String? ?? 'Excursion';
    final date = data['date'] as String? ?? '';
    final reason = data['reason'] as String?;

    String title = 'Réservation Excursions & Activités';
    String message;
    if (status == 'confirmed') {
      message =
          'Votre réservation excursion « $excursionName » est confirmée pour le $date.';
    } else if (status == 'cancelled') {
      message = l10n.reservationCancelledMessage;
      if (reason != null && reason.isNotEmpty) {
        message += '\nMotif : $reason';
      }
    } else if (status == 'completed') {
      message = 'Votre excursion « $excursionName » du $date a été honorée.';
    } else {
      final label = _restaurantStatusLabel(l10n, status);
      message = 'Statut de votre réservation excursion : $label';
    }

    showDialog(
      context: ctx,
      barrierDismissible: true,
      builder: (dialogContext) {
        return AlertDialog(
          backgroundColor: AppTheme.primaryBlue,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
            side: const BorderSide(color: AppTheme.accentGold, width: 1.5),
          ),
          title: Text(
            title,
            style: const TextStyle(
              color: Colors.white,
              fontWeight: FontWeight.bold,
            ),
          ),
          content: Text(
            message,
            style: const TextStyle(color: Colors.white, fontSize: 14),
          ),
          actions: [
            TextButton(
              onPressed: () {
                _stopNotificationSound();
                Navigator.of(dialogContext, rootNavigator: true).pop();
              },
              child: const Text(
                'Fermer',
                style: TextStyle(
                  color: AppTheme.accentGold,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),
            TextButton(
              onPressed: () {
                _stopNotificationSound();
                Navigator.of(dialogContext, rootNavigator: true).pop();
                final navigator = rootNavigatorKey.currentState;
                if (navigator == null) return;
                navigator.push(
                  NavigationHelper.slideRoute(
                    const MyExcursionBookingsScreen(),
                  ),
                );
              },
              child: Text(
                l10n.myExcursionsShort,
                style: const TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.w700,
                ),
              ),
            ),
          ],
        );
      },
    ).then((_) => _stopNotificationSound());
  }

  String _statusMessage(AppLocalizations l10n, String statusLabel) {
    return '${l10n.orderTrackingSubtitle}: $statusLabel';
  }

  @override
  Widget build(BuildContext context) {
    return Consumer<LocaleProvider>(
      builder: (context, localeProvider, _) {
        final locale = localeProvider.locale;
        final isRtl = locale.languageCode == 'ar';
        // Provide Localizations so that context has AppLocalizations before
        // MaterialApp is built (avoids null when using l10n in title etc.).
        return Localizations(
          locale: locale,
          delegates: AppLocalizations.localizationsDelegates,
          child: Builder(
            builder: (context) {
              return MaterialApp(
                navigatorKey: rootNavigatorKey,
                title: AppLocalizations.of(context).appTitle,
                debugShowCheckedModeBanner: false,
                theme: AppTheme.theme,
                locale: locale,
                localizationsDelegates: AppLocalizations.localizationsDelegates,
                supportedLocales: AppLocalizations.supportedLocales,
                builder: (context, child) {
                  if (child == null) return const SizedBox.shrink();
                  return Directionality(
                    textDirection: isRtl
                        ? TextDirection.rtl
                        : TextDirection.ltr,
                    child: IdleOverlay(
                      idleDuration: const Duration(minutes: 1),
                      onSessionExpired: () {
                        // Session expirée → retour à l'accueil pour le prochain client
                        rootNavigatorKey.currentState?.pushAndRemoveUntil(
                          MaterialPageRoute(
                            builder: (_) => const DashboardScreen(),
                          ),
                          (route) => false,
                        );
                      },
                      child: child,
                    ),
                  );
                },
                home: const SplashScreen(),
              );
            },
          ),
        );
      },
    );
  }

  Future<void> _handlePalaceStatusNotification(
    Map<String, dynamic> data,
  ) async {
    final ctx = rootNavigatorKey.currentContext;
    if (ctx == null) return;

    final status = data['status'] as String? ?? '';
    final requestIdRaw = data['request_id'] as String?;
    final requestNumber =
        data['request_number'] as String? ?? requestIdRaw ?? '';
    final screen = data['screen'] as String? ?? '';
    final rawReason = data['reason'] as String?;
    final reason = rawReason != null && rawReason.trim().isNotEmpty
        ? rawReason.trim()
        : null;
    final isStaff = screen == 'AdminPalaceRequests';

    // If a guest receives a notification with an unrecognized or 'pending' status,
    // it usually means they just created the request themselves or it's an automatic ack.
    // Only show popups for actual admin updates.
    if (!isStaff &&
        ![
          'in_progress',
          'accepted',
          'confirmed',
          'completed',
          'cancelled',
        ].contains(status)) {
      return;
    }

    _startNotificationSoundLoop();

    String title;
    String message;

    final validCtx = rootNavigatorKey.currentContext;
    if (validCtx == null || !validCtx.mounted) return;

    final l10n = AppLocalizations.of(validCtx);
    if (isStaff) {
      final roomNumber = data['room_number'] as String?;
      final guestName = data['guest_name'] as String?;

      final detailsParts = <String>[];
      if (roomNumber != null && roomNumber.isNotEmpty) {
        detailsParts.add(l10n.roomLabelLong(roomNumber));
      }
      if (guestName != null && guestName.isNotEmpty) {
        detailsParts.add(guestName);
      }
      final detailsSuffix = detailsParts.isEmpty
          ? ''
          : ' (${detailsParts.join(' – ')})';

      if (status == 'cancelled') {
        title = l10n.palaceRequestCancelledByClient;
        message = l10n.palaceRequestCancelledByClientMessage(requestNumber, detailsSuffix);
        if (reason != null && reason.isNotEmpty) {
          message += '\n${l10n.reasonOptional} : $reason';
        }
      } else {
        title = l10n.palaceRequestUpdated;
        message = l10n.palaceRequestUpdatedMessage(requestNumber, status, detailsSuffix);
      }
    } else {
      String generatedItemDesc = l10n.palaceRequestDetailed;

      try {
        final palaceProvider = Provider.of<PalaceProvider>(validCtx, listen: false);
        final intRequestId = int.tryParse(requestIdRaw ?? '');

        if (intRequestId != null) {
          var foundRequestList = palaceProvider.requests.where(
            (r) => r.id == intRequestId,
          );

          if (foundRequestList.isEmpty) {
            palaceProvider
                .fetchMyPalaceRequests()
                .then((_) {})
                .catchError((_) {});
          } else {
            final foundRequest = foundRequestList.first;
            if (foundRequest.serviceName.isNotEmpty) {
              generatedItemDesc = foundRequest.serviceName;
            }
          }
        }
      } catch (_) {}

      title = l10n.palaceRequestDetailed;
      if (status == 'in_progress' ||
          status == 'accepted' ||
          status == 'confirmed') {
        message = l10n.palaceRequestInProgressMessage(generatedItemDesc, requestNumber);
      } else if (status == 'completed') {
        message = l10n.palaceRequestCompletedMessage(generatedItemDesc, requestNumber);
      } else if (status == 'cancelled') {
        message = l10n.palaceRequestRefusedMessage(generatedItemDesc, requestNumber);
        if (reason != null) {
          message += '\n${l10n.reasonOptional} : $reason';
        }
      } else {
        message = l10n.palaceRequestUpdatedStatusMessage(generatedItemDesc, requestNumber);
      }
    }

    showDialog(
      context: validCtx,
      barrierDismissible: true,
      useRootNavigator: true,
      builder: (dialogContext) {
        return AlertDialog(
          backgroundColor: AppTheme.primaryBlue,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
            side: const BorderSide(color: AppTheme.accentGold, width: 1.5),
          ),
          title: Text(
            title,
            style: const TextStyle(
              color: Colors.white,
              fontWeight: FontWeight.bold,
            ),
          ),
          content: Text(
            message,
            style: const TextStyle(color: Colors.white, fontSize: 14),
          ),
          actions: [
            TextButton(
              onPressed: () {
                _stopNotificationSound();
                Navigator.of(dialogContext, rootNavigator: true).pop();
              },
              child: Text(
                l10n.closeButton,
                style: const TextStyle(
                  color: AppTheme.accentGold,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),
            TextButton(
              onPressed: () {
                _stopNotificationSound();
                Navigator.of(dialogContext, rootNavigator: true).pop();
                final navigator = rootNavigatorKey.currentState;
                if (navigator == null) return;

                if (isStaff) {
                  // No staff routing to specific yet
                } else {
                  navigator.push(
                    NavigationHelper.slideRoute(const MyPalaceRequestsScreen()),
                  );
                }
              },
              child: Text(
                isStaff ? l10n.actionConfirm : l10n.viewMyRequests,
                style: const TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.w700,
                ),
              ),
            ),
          ],
        );
      },
    ).then((_) => _stopNotificationSound());
  }
}
