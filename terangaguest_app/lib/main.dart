import 'dart:async';
import 'package:firebase_core/firebase_core.dart';
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
import 'utils/navigation_helper.dart';
import 'screens/admin/admin_chat_conversations_screen.dart';
import 'screens/hotel_infos/chatbot_screen.dart';
import 'widgets/idle_overlay.dart';

final GlobalKey<NavigatorState> rootNavigatorKey = GlobalKey<NavigatorState>();

void main() async {
  // IMPORTANT: Ensure bindings are initialized FIRST
  WidgetsFlutterBinding.ensureInitialized();

  // Firebase (notifications push) — utilise google-services.json / GoogleService-Info.plist
  await Firebase.initializeApp();

  // Initialiser le locale français pour les dates
  await initializeDateFormatting('fr_FR', null);

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
  SystemChrome.setPreferredOrientations([
    DeviceOrientation.landscapeLeft,
    DeviceOrientation.landscapeRight,
    DeviceOrientation.portraitUp,
  ]).then((_) {
    runApp(const MyApp());
  });
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

class _LocalizedAppState extends State<_LocalizedApp> {
  late final AudioPlayer _notificationPlayer;
  Timer? _notificationSoundTimer;

  @override
  void initState() {
    super.initState();
    _notificationPlayer = AudioPlayer();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<LocaleProvider>().load();
      _setupFcmListeners();
      _handleInitialFcmMessage();
    });
  }

  @override
  void dispose() {
    _notificationSoundTimer?.cancel();
    _notificationPlayer.dispose();
    super.dispose();
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
      } else if (type == 'spa_reservation_rescheduled') {
        _handleSpaRescheduleNotification(data);
      } else if (type == 'spa_reservation_status') {
        _handleSpaStatusNotification(data);
      } else if (type == 'restaurant_reservation_status') {
        _handleRestaurantStatusNotification(data);
      } else if (type == 'chat_message') {
        _handleChatMessageNotification(data);
      }
    });

    FirebaseMessaging.onMessageOpenedApp.listen((message) {
      _handleOpenedFromNotification(message.data);
    });
  }

  Future<void> _handleInitialFcmMessage() async {
    final message = await FirebaseMessaging.instance.getInitialMessage();
    if (message != null) {
      _handleOpenedFromNotification(message.data);
    }
  }

  void _handleChatMessageNotification(Map<String, dynamic> data) {
    _startNotificationSoundLoop();

    final ctx = rootNavigatorKey.currentContext;
    if (ctx == null) return;

    final l10n = AppLocalizations.of(ctx);
    final auth = Provider.of<AuthProvider>(ctx, listen: false);
    final isStaffOrAdmin = auth.isAdmin || auth.isStaff;

    final conversationIdRaw = data['conversation_id'] as String?;
    final conversationId = int.tryParse(conversationIdRaw ?? '');
    final guestName = data['guest_name'] as String? ?? 'Client chambre';
    final roomLabel = data['room_label'] as String? ?? '';
    final preview = data['message_preview'] as String? ?? '';
    final messageType = data['message_type'] as String? ?? 'text';

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
    );
  }

  void _handleOrderStatusNotification(Map<String, dynamic> data) {
    _startNotificationSoundLoop();

    final ctx = rootNavigatorKey.currentContext;
    if (ctx == null) return;

    final l10n = AppLocalizations.of(ctx);
    final orderIdRaw = data['order_id'] as String?;
    final orderNumber = data['order_number'] as String? ?? '';
    final status = data['status'] as String? ?? '';
    final rawReason = data['reason'] as String?;
    final reason = rawReason != null && rawReason.trim().isNotEmpty
        ? rawReason.trim()
        : null;
    final orderId = int.tryParse(orderIdRaw ?? '');

    final statusLabel = _statusLabel(l10n, status);

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
    );
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
    );
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
    );
  }

  void _handleOpenedFromNotification(Map<String, dynamic> data) {
    final type = data['type'] as String?;
    final navigator = rootNavigatorKey.currentState;
    final ctx = rootNavigatorKey.currentContext;
    if (navigator == null || ctx == null) return;

    final auth = Provider.of<AuthProvider>(ctx, listen: false);
    if (!auth.isAuthenticated) return;

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

    if (type == 'order' || type == 'order_status') {
      final orderIdRaw = data['order_id'] as String?;
      final orderId = int.tryParse(orderIdRaw ?? '');
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

    if (type == 'excursion_booking') {
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

    if (type == 'palace') {
      navigator.push(
        NavigationHelper.slideRoute(const MyPalaceRequestsScreen()),
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
        detailsParts.add('Chambre $roomNumber');
      }
      if (guestName != null && guestName.isNotEmpty) {
        detailsParts.add(guestName);
      }
      final detailsSuffix = detailsParts.isEmpty
          ? ''
          : ' (${detailsParts.join(' – ')})';

      if (status == 'cancelled') {
        title = 'Réservation restaurant annulée par le client';
        message =
            'Le client a annulé la réservation au restaurant $restaurantName prévue le $date à $time$detailsSuffix.';
        if (reason != null && reason.isNotEmpty) {
          message += '\nMotif : $reason';
        }
      } else {
        title = 'Réservation restaurant mise à jour';
        final label = _restaurantStatusLabel(l10n, status);
        message = 'Statut de la réservation restaurant mis à jour : $label.';
      }
    } else {
      title = 'Réservation restaurant';
      if (status == 'confirmed') {
        message =
            'Votre réservation au restaurant $restaurantName est confirmée pour le $date à $time.';
      } else if (status == 'cancelled') {
        message = l10n.reservationCancelledMessage;
        if (reason != null && reason.isNotEmpty) {
          message += '\nMotif : $reason';
        }
      } else if (status == 'completed') {
        message =
            'Merci, votre réservation au restaurant $restaurantName a été honorée.';
      } else {
        final label = _restaurantStatusLabel(l10n, status);
        message = 'Statut de la réservation restaurant : $label';
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
                  NavigationHelper.slideRoute(
                    const MyRestaurantReservationsScreen(),
                  ),
                );
              },
              child: Text(
                isStaff
                    ? 'Voir les réservations restaurants à traiter'
                    : 'Voir mes réservations restaurants',
                style: const TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.w700,
                ),
              ),
            ),
          ],
        );
      },
    );
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
                      idleDuration: const Duration(minutes: 1), // → 1h en prod
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
}
