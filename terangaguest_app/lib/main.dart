import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:intl/date_symbol_data_local.dart';
import 'package:provider/provider.dart';
import 'config/theme.dart';
import 'generated/l10n/app_localizations.dart';
import 'screens/auth/splash_screen.dart';
import 'screens/orders/order_detail_screen.dart';
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
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<LocaleProvider>().load();
    });
    _setupFcmListeners();
  }

  void _setupFcmListeners() {
    FirebaseMessaging.onMessage.listen((message) {
      final data = message.data;
      if (data['type'] != 'order_status') return;

      final ctx = rootNavigatorKey.currentContext;
      if (ctx == null) return;

      final l10n = AppLocalizations.of(ctx);
      final orderIdRaw = data['order_id'] as String?;
      final orderNumber = data['order_number'] as String? ?? '';
      final status = data['status'] as String? ?? '';
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
                  _statusMessage(l10n, statusLabel),
                  style: const TextStyle(color: Colors.white, fontSize: 14),
                ),
              ],
            ),
            actions: [
              TextButton(
                onPressed: () {
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
    });
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
