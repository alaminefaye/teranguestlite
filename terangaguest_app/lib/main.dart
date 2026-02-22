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
    });
    _setupFcmListeners();
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
      }
    });
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
      } else if (status == 'honored') {
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
      case 'honored':
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
                    child: child,
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
