import 'dart:async';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../config/theme.dart';
import '../../config/api_config.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../models/order.dart';
import '../../providers/auth_provider.dart';
import '../../services/admin_api.dart';
import '../../services/orders_api.dart';
import '../../utils/haptic_helper.dart';
import '../../utils/layout_helper.dart';
import '../../utils/navigation_helper.dart';
import '../../widgets/service_card.dart';
import '../auth/login_screen.dart';
import '../orders/order_detail_screen.dart';
import '../orders/orders_list_screen.dart';
import '../restaurants/my_reservations_screen.dart';
import '../spa/my_spa_reservations_screen.dart';
import '../excursions/my_excursion_bookings_screen.dart';
import '../laundry/my_laundry_requests_screen.dart';
import '../palace/my_palace_requests_screen.dart';
import '../hotel_infos/assistance_emergency_screen.dart';
import '../hotel_infos/emergency_requests_screen.dart';
import '../notifications/notifications_screen.dart';
import 'admin_chat_conversations_screen.dart';

class _AdminEventAlert {
  final String title;
  final String message;
  final IconData icon;
  final String? routeKey;

  const _AdminEventAlert({
    required this.title,
    required this.message,
    required this.icon,
    this.routeKey,
  });
}

/// Page d'accueil pour les administrateurs / staff dans l'app mobile.
/// Affiche des boxes pour accéder aux différents modules de gestion.
class AdminHomeScreen extends StatefulWidget {
  const AdminHomeScreen({super.key});

  @override
  State<AdminHomeScreen> createState() => _AdminHomeScreenState();
}

class _AdminHomeScreenState extends State<AdminHomeScreen> {
  final AdminApi _adminApi = AdminApi();
  final OrdersApi _ordersApi = OrdersApi();
  AdminSummary? _summary;
  bool _isLoading = false;
  Timer? _summaryTimer;
  bool _isShowingNewOrderDialog = false;
  final List<Order> _newOrdersQueue = [];
  final Set<int> _alertedOrderIds = {};
  bool _isShowingAdminEventDialog = false;
  final List<_AdminEventAlert> _adminEventQueue = [];

  @override
  void initState() {
    super.initState();
    _loadSummary();
    _summaryTimer = Timer.periodic(
      const Duration(seconds: 15),
      (_) => _loadSummary(),
    );
  }

  @override
  void dispose() {
    _summaryTimer?.cancel();
    super.dispose();
  }

  Future<void> _loadSummary() async {
    if (_isLoading) return;
    final previous = _summary;
    setState(() {
      _isLoading = true;
    });

    try {
      final data = await _adminApi.getSummary();
      if (!mounted) return;
      setState(() {
        _summary = data;
        _isLoading = false;
      });
      _handleSummaryDelta(previous, data);
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _isLoading = false;
      });
    }
  }

  Future<void> _handleSummaryDelta(
    AdminSummary? oldSummary,
    AdminSummary newSummary,
  ) async {
    if (oldSummary == null) return;
    final messages = <String>[];
    final hasNewOrder = newSummary.ordersPending > oldSummary.ordersPending;
    if (hasNewOrder) {
      _enqueueNewOrdersForAlert();
      messages.add('Nouvelle commande Room Service à traiter');
    }
    if (newSummary.restaurantPending > oldSummary.restaurantPending) {
      messages.add('Nouvelle réservation restaurant à traiter');
      _adminEventQueue.add(
        const _AdminEventAlert(
          title: 'Nouvelle réservation restaurant',
          message: 'Vous avez une nouvelle réservation restaurant à traiter.',
          icon: Icons.restaurant_menu,
          routeKey: 'admin-restaurants',
        ),
      );
    }
    if (newSummary.spaPending > oldSummary.spaPending) {
      messages.add('Nouvelle réservation Spa & Bien-être à traiter');
      _adminEventQueue.add(
        const _AdminEventAlert(
          title: 'Nouvelle réservation Spa & Bien-être',
          message: 'Vous avez une nouvelle réservation spa à traiter.',
          icon: Icons.spa,
          routeKey: 'admin-spa',
        ),
      );
    }
    if (newSummary.excursionsPending > oldSummary.excursionsPending) {
      messages.add('Nouvelle demande Excursions & Activités à traiter');
      _adminEventQueue.add(
        const _AdminEventAlert(
          title: 'Nouvelle demande Excursions & Activités',
          message: 'Vous avez une nouvelle demande excursions à traiter.',
          icon: Icons.landscape,
          routeKey: 'admin-excursions',
        ),
      );
    }
    if (newSummary.laundryPending > oldSummary.laundryPending) {
      messages.add('Nouvelle demande Blanchisserie à traiter');
      _adminEventQueue.add(
        const _AdminEventAlert(
          title: 'Nouvelle demande Blanchisserie',
          message: 'Vous avez une nouvelle demande blanchisserie à traiter.',
          icon: Icons.local_laundry_service,
          routeKey: 'admin-laundry',
        ),
      );
    }
    if (newSummary.palacePending > oldSummary.palacePending) {
      messages.add('Nouvelle demande Palace / Conciergerie à traiter');
      _adminEventQueue.add(
        const _AdminEventAlert(
          title: 'Nouvelle demande Palace / Conciergerie',
          message: 'Vous avez une nouvelle demande palace/conciergerie.',
          icon: Icons.account_balance,
          routeKey: 'admin-palace',
        ),
      );
    }
    if (newSummary.emergencyOpen > oldSummary.emergencyOpen) {
      messages.add('Nouvelle alerte Assistance & Urgence');
      _adminEventQueue.add(
        const _AdminEventAlert(
          title: 'Nouvelle alerte Assistance & Urgence',
          message: 'Une nouvelle alerte assistance/urgence est ouverte.',
          icon: Icons.warning_amber_rounded,
          routeKey: 'admin-emergency',
        ),
      );
    }
    if (newSummary.chatUnreadConversations >
        oldSummary.chatUnreadConversations) {
      messages.add('Nouveau message client dans le chat');
      _adminEventQueue.add(
        const _AdminEventAlert(
          title: 'Nouveau message client',
          message: 'Vous avez un nouveau message client dans le chat.',
          icon: Icons.chat_bubble_outline,
          routeKey: 'admin-chat',
        ),
      );
    }
    if (messages.isEmpty) return;
    if (!mounted) return;
    final text = messages.join('\n');
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(text),
        backgroundColor: AppTheme.accentGold,
        duration: const Duration(seconds: 4),
      ),
    );
    await _showAdminEventAlerts();
  }

  Future<void> _showAdminEventAlerts() async {
    if (_isShowingAdminEventDialog) return;
    if (_adminEventQueue.isEmpty) return;
    _isShowingAdminEventDialog = true;
    while (_adminEventQueue.isNotEmpty && mounted) {
      final alert = _adminEventQueue.removeAt(0);
      final openSection = await showDialog<bool>(
        context: context,
        barrierDismissible: true,
        builder: (dialogContext) {
          return AlertDialog(
            backgroundColor: AppTheme.primaryBlue,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(18),
              side: const BorderSide(color: AppTheme.accentGold, width: 1.5),
            ),
            title: Row(
              children: [
                Container(
                  width: 40,
                  height: 40,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    color: AppTheme.accentGold.withValues(alpha: 0.15),
                  ),
                  child: Icon(alert.icon, color: AppTheme.accentGold),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Text(
                    alert.title,
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ],
            ),
            content: Text(
              alert.message,
              style: const TextStyle(color: Colors.white, fontSize: 14),
            ),
            actions: [
              TextButton(
                onPressed: () {
                  Navigator.of(dialogContext, rootNavigator: true).pop(false);
                },
                child: const Text(
                  'Fermer',
                  style: TextStyle(
                    color: AppTheme.textGray,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
              if (alert.routeKey != null)
                TextButton(
                  onPressed: () {
                    Navigator.of(dialogContext, rootNavigator: true).pop(true);
                  },
                  child: const Text(
                    'Ouvrir',
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
      if (!mounted) break;
      if (openSection == true && alert.routeKey != null) {
        _openAdminSection(alert.routeKey!);
      }
    }
    _isShowingAdminEventDialog = false;
  }

  void _openAdminSection(String routeKey) {
    final l10n = AppLocalizations.of(context);
    _handleTileTap(
      context,
      _AdminTile(label: '', icon: Icons.circle, routeKey: routeKey),
      l10n,
    );
  }

  Future<void> _enqueueNewOrdersForAlert() async {
    try {
      final result = await _ordersApi.getOrders(
        status: 'pending',
        page: 1,
        perPage: 10,
      );
      final orders = result['orders'] as List<Order>;
      for (final order in orders) {
        if (!_alertedOrderIds.contains(order.id)) {
          _alertedOrderIds.add(order.id);
          try {
            final detail = await _ordersApi.getOrderDetail(order.id);
            final combined = Order(
              id: order.id,
              orderNumber: order.orderNumber.isNotEmpty
                  ? order.orderNumber
                  : detail.orderNumber,
              status: detail.status.isNotEmpty ? detail.status : order.status,
              total: detail.total != 0 ? detail.total : order.total,
              instructions: detail.instructions ?? order.instructions,
              createdAt: detail.createdAt,
              deliveryTime: detail.deliveryTime,
              itemsCount: detail.itemsCount,
              items: detail.items,
              roomNumber: order.roomNumber ?? detail.roomNumber,
              guestName: order.guestName ?? detail.guestName,
              guestPhone: order.guestPhone ?? detail.guestPhone,
            );
            _newOrdersQueue.add(combined);
          } catch (_) {
            _newOrdersQueue.add(order);
          }
        }
      }
      if (!_isShowingNewOrderDialog && _newOrdersQueue.isNotEmpty && mounted) {
        _showNewOrdersCarousel();
      }
    } catch (_) {}
  }

  Future<void> _showNewOrdersCarousel() async {
    if (!mounted) return;
    if (_newOrdersQueue.isEmpty) {
      _isShowingNewOrderDialog = false;
      return;
    }
    _isShowingNewOrderDialog = true;
    final ordersToShow = List<Order>.from(_newOrdersQueue);
    _newOrdersQueue.clear();

    await HapticHelper.heavyImpact();

    final selectedOrder = await showDialog<Order?>(
      context: context,
      barrierDismissible: true,
      builder: (dialogContext) {
        return _NewOrdersCarouselDialog(orders: ordersToShow);
      },
    );

    if (!mounted) {
      _isShowingNewOrderDialog = false;
      return;
    }
    _isShowingNewOrderDialog = false;
    if (selectedOrder != null) {
      Navigator.of(context).push(
        MaterialPageRoute(
          builder: (_) => OrderDetailScreen(orderId: selectedOrder.id),
        ),
      );
    }
    if (_newOrdersQueue.isNotEmpty) {
      _showNewOrdersCarousel();
    }
  }

  Future<void> _handleLogout(BuildContext context) async {
    final l10n = AppLocalizations.of(context);
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        backgroundColor: AppTheme.primaryBlue,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(16),
          side: const BorderSide(color: AppTheme.accentGold, width: 1),
        ),
        title: Text(l10n.logout, style: const TextStyle(color: Colors.white)),
        content: Text(
          l10n.logoutConfirm,
          style: const TextStyle(color: AppTheme.textGray),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: Text(
              l10n.cancel,
              style: const TextStyle(color: AppTheme.textGray),
            ),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            child: Text(
              l10n.logout,
              style: const TextStyle(
                color: Colors.red,
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
        ],
      ),
    );

    if (confirmed == true && context.mounted) {
      HapticHelper.lightImpact();
      final authProvider = Provider.of<AuthProvider>(context, listen: false);
      await authProvider.logout();

      if (context.mounted) {
        NavigationHelper.navigateAndRemoveUntil(context, const LoginScreen());
      }
    }
  }

  void _openNotifications(BuildContext context) {
    HapticHelper.lightImpact();
    Navigator.of(
      context,
    ).push(MaterialPageRoute(builder: (_) => const NotificationsScreen()));
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final user = context.watch<AuthProvider>().user;
    final enterpriseName = user?.enterprise?.name ?? 'Votre établissement';

    final tiles = [
      _AdminTile(
        icon: Icons.room_service_outlined,
        label: 'Commandes Room Service',
        routeKey: 'admin-room-service',
        badge: _summary?.ordersPending ?? 0,
      ),
      _AdminTile(
        icon: Icons.restaurant_menu_outlined,
        label: 'Réservations Restaurants',
        routeKey: 'admin-restaurants',
        badge: _summary?.restaurantPending ?? 0,
      ),
      _AdminTile(
        icon: Icons.spa_outlined,
        label: 'Réservations Spa & Bien-être',
        routeKey: 'admin-spa',
        badge: _summary?.spaPending ?? 0,
      ),
      _AdminTile(
        icon: Icons.hiking_outlined,
        label: 'Excursions & Activités',
        routeKey: 'admin-excursions',
        badge: _summary?.excursionsPending ?? 0,
      ),
      _AdminTile(
        icon: Icons.local_laundry_service_outlined,
        label: 'Demandes Blanchisserie',
        routeKey: 'admin-laundry',
        badge: _summary?.laundryPending ?? 0,
      ),
      _AdminTile(
        icon: Icons.workspace_premium_outlined,
        label: 'Services Palace / Conciergerie',
        routeKey: 'admin-palace',
        badge: _summary?.palacePending ?? 0,
      ),
      _AdminTile(
        icon: Icons.health_and_safety_outlined,
        label: 'Assistance & Urgence',
        routeKey: 'admin-emergency',
        badge: _summary?.emergencyOpen ?? 0,
      ),
      _AdminTile(
        icon: Icons.chat_bubble_outline,
        label: 'Messages / Chat client',
        routeKey: 'admin-chat',
        badge: _summary?.chatUnreadConversations ?? 0,
      ),
    ];

    final crossAxisCount = LayoutHelper.gridCrossAxisCount(context);
    final aspectRatio = LayoutHelper.dashboardCellAspectRatio(context);
    final spacing = LayoutHelper.gridSpacing(context);

    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(gradient: AppTheme.backgroundGradient),
        child: SafeArea(
          child: Column(
            children: [
              _buildAdminHero(context, enterpriseName),
              Expanded(
                child: Padding(
                  padding: LayoutHelper.horizontalPadding(context),
                  child: GridView.builder(
                    padding: EdgeInsets.symmetric(vertical: spacing),
                    gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                      crossAxisCount: crossAxisCount,
                      crossAxisSpacing: spacing,
                      mainAxisSpacing: spacing,
                      childAspectRatio: aspectRatio,
                    ),
                    itemCount: tiles.length,
                    itemBuilder: (context, index) {
                      final tile = tiles[index];
                      return ServiceCard(
                        title: tile.label,
                        icon: tile.icon,
                        badge: tile.badge > 0 ? tile.badge.toString() : null,
                        isLoading: _isLoading && _summary == null,
                        onTap: () {
                          HapticHelper.lightImpact();
                          _handleTileTap(context, tile, l10n);
                        },
                      );
                    },
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildAdminHero(BuildContext context, String enterpriseName) {
    final l10n = AppLocalizations.of(context);
    final user = context.watch<AuthProvider>().user;
    final enterprise = user?.enterprise;
    final displayName = enterprise?.name.trim() ?? '';
    final coverPath = enterprise?.coverPhoto;
    final logoPath = enterprise?.logo;
    final backgroundImageUrl = () {
      String? p = coverPath;
      if (p != null && p.trim().isNotEmpty) {
        final trimmed = p.trim();
        if (trimmed.startsWith('http')) return trimmed;
        return ApiConfig.storageUrl(trimmed);
      }
      p = logoPath;
      if (p != null && p.trim().isNotEmpty) {
        final trimmed = p.trim();
        if (trimmed.startsWith('http')) return trimmed;
        return ApiConfig.storageUrl(trimmed);
      }
      return null;
    }();
    final logoUrl = () {
      final p = logoPath;
      if (p == null || p.trim().isEmpty) return null;
      final trimmed = p.trim();
      if (trimmed.startsWith('http')) return trimmed;
      return ApiConfig.storageUrl(trimmed);
    }();
    final h = MediaQuery.sizeOf(context).height;
    final isLandscape =
        MediaQuery.of(context).orientation == Orientation.landscape;
    final isCompact = isLandscape ? (h < 900) : (h < 600);
    final isVeryCompact = h < 500;
    final pad = LayoutHelper.horizontalPaddingValue(context);
    final logoSize = isVeryCompact ? 88.0 : (isCompact ? 120.0 : 160.0);
    final subtitleSize = isVeryCompact ? 10.0 : (isCompact ? 12.0 : 14.0);
    final nameOnBannerSize = isVeryCompact ? 18.0 : (isCompact ? 24.0 : 30.0);
    final heroHeight = isVeryCompact ? 180.0 : (isCompact ? 220.0 : 260.0);

    return SizedBox(
      height: heroHeight,
      width: double.infinity,
      child: Stack(
        fit: StackFit.expand,
        children: [
          if (backgroundImageUrl != null && backgroundImageUrl.isNotEmpty)
            CachedNetworkImage(
              imageUrl: backgroundImageUrl,
              fit: BoxFit.cover,
              placeholder: (_, __) => Container(
                decoration: const BoxDecoration(
                  gradient: AppTheme.backgroundGradient,
                ),
              ),
              errorWidget: (_, __, ___) => Container(
                decoration: const BoxDecoration(
                  gradient: AppTheme.backgroundGradient,
                ),
              ),
            )
          else
            Container(
              decoration: const BoxDecoration(
                gradient: AppTheme.backgroundGradient,
              ),
            ),
          Positioned.fill(
            child: DecoratedBox(
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                  colors: [
                    Colors.black26,
                    Colors.transparent,
                    Colors.black45,
                    Colors.black.withValues(alpha: 0.75),
                  ],
                  stops: const [0.0, 0.25, 0.6, 1.0],
                ),
              ),
              child: const SizedBox.shrink(),
            ),
          ),
          Align(
            alignment: const Alignment(0, -0.95),
            child: Padding(
              padding: EdgeInsets.only(
                left: pad,
                right: pad,
                top: 12,
                bottom: 12,
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  if (logoUrl != null && logoUrl.isNotEmpty)
                    CachedNetworkImage(
                      imageUrl: logoUrl,
                      height: logoSize,
                      fit: BoxFit.contain,
                      placeholder: (_, __) => SizedBox(
                        height: logoSize,
                        child: Center(
                          child: SizedBox(
                            width: logoSize * 0.4,
                            height: logoSize * 0.4,
                            child: const CircularProgressIndicator(
                              strokeWidth: 2,
                              color: AppTheme.accentGold,
                            ),
                          ),
                        ),
                      ),
                      errorWidget: (_, __, ___) => const SizedBox.shrink(),
                    ),
                  if (logoUrl != null && logoUrl.isNotEmpty)
                    SizedBox(height: isVeryCompact ? 12 : 18),
                  Text(
                    l10n.welcomeSubtitle,
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      color: AppTheme.textWhite,
                      fontSize: subtitleSize,
                      fontWeight: FontWeight.w400,
                      shadows: [
                        Shadow(
                          color: Colors.black.withValues(alpha: 0.8),
                          offset: const Offset(0, 1),
                          blurRadius: 3,
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
          Align(
            alignment: Alignment.bottomCenter,
            child: Padding(
              padding: EdgeInsets.only(
                left: pad + 8,
                top: 20,
                bottom: 16,
                right: pad + 8,
              ),
              child: Text(
                'Bienvenue au ${displayName.isNotEmpty ? displayName : l10n.welcomeTitle}',
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: nameOnBannerSize,
                  fontWeight: FontWeight.w800,
                  color: Colors.white,
                  shadows: [
                    Shadow(
                      color: Colors.black.withValues(alpha: 0.9),
                      offset: const Offset(0, 1),
                      blurRadius: 6,
                    ),
                    Shadow(
                      color: Colors.black.withValues(alpha: 0.6),
                      offset: const Offset(0, 2),
                      blurRadius: 8,
                    ),
                  ],
                ),
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
              ),
            ),
          ),
          Positioned(
            top: 8,
            left: 8,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
              decoration: BoxDecoration(
                color: AppTheme.primaryDark.withValues(alpha: 0.65),
                borderRadius: BorderRadius.circular(28),
                border: Border.all(
                  color: AppTheme.accentGold.withValues(alpha: 0.4),
                  width: 1,
                ),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.5),
                    blurRadius: 12,
                    offset: const Offset(0, 2),
                  ),
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.3),
                    blurRadius: 6,
                    offset: const Offset(0, 1),
                  ),
                ],
              ),
              child: const Text(
                'Espace Administrateur',
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 16,
                  fontWeight: FontWeight.w700,
                ),
              ),
            ),
          ),
          Positioned(
            top: 8,
            right: 8,
            child: _buildAppBar(context, enterpriseName),
          ),
        ],
      ),
    );
  }

  Widget _buildAppBar(BuildContext context, String enterpriseName) {
    final summary = _summary;
    final hasAlerts =
        summary != null &&
        (summary.ordersPending > 0 ||
            summary.restaurantPending > 0 ||
            summary.spaPending > 0 ||
            summary.excursionsPending > 0 ||
            summary.laundryPending > 0 ||
            summary.palacePending > 0 ||
            summary.emergencyOpen > 0 ||
            summary.chatUnreadConversations > 0);
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Container(
            decoration: BoxDecoration(
              color: AppTheme.primaryDark.withValues(alpha: 0.65),
              borderRadius: BorderRadius.circular(28),
              border: Border.all(
                color: AppTheme.accentGold.withValues(alpha: 0.4),
                width: 1,
              ),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.5),
                  blurRadius: 8,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Stack(
                  clipBehavior: Clip.none,
                  children: [
                    IconButton(
                      onPressed: () {
                        _openNotifications(context);
                      },
                      iconSize: 22,
                      padding: const EdgeInsets.all(6),
                      icon: const Icon(
                        Icons.notifications_none,
                        color: AppTheme.accentGold,
                      ),
                    ),
                    if (hasAlerts)
                      Positioned(
                        right: 6,
                        top: 6,
                        child: Container(
                          width: 8,
                          height: 8,
                          decoration: BoxDecoration(
                            color: Colors.red,
                            shape: BoxShape.circle,
                            border: Border.all(
                              color: AppTheme.primaryDark,
                              width: 1,
                            ),
                          ),
                        ),
                      ),
                  ],
                ),
                IconButton(
                  onPressed: () {
                    _handleLogout(context);
                  },
                  iconSize: 22,
                  padding: const EdgeInsets.all(6),
                  icon: const Icon(Icons.logout, color: AppTheme.accentGold),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  void _handleTileTap(
    BuildContext context,
    _AdminTile tile,
    AppLocalizations l10n,
  ) {
    switch (tile.routeKey) {
      case 'admin-room-service':
        Navigator.of(
          context,
        ).push(MaterialPageRoute(builder: (_) => const OrdersListScreen()));
        break;
      case 'admin-restaurants':
        Navigator.of(context).push(
          MaterialPageRoute(
            builder: (_) => const MyRestaurantReservationsScreen(),
          ),
        );
        break;
      case 'admin-spa':
        Navigator.of(context).push(
          MaterialPageRoute(builder: (_) => const MySpaReservationsScreen()),
        );
        break;
      case 'admin-excursions':
        Navigator.of(context).push(
          MaterialPageRoute(builder: (_) => const MyExcursionBookingsScreen()),
        );
        break;
      case 'admin-laundry':
        Navigator.of(context).push(
          MaterialPageRoute(builder: (_) => const MyLaundryRequestsScreen()),
        );
        break;
      case 'admin-palace':
        Navigator.of(context).push(
          MaterialPageRoute(builder: (_) => const MyPalaceRequestsScreen()),
        );
        break;
      case 'admin-emergency':
        Navigator.of(context).push(
          MaterialPageRoute(builder: (_) => const EmergencyRequestsScreen()),
        );
        break;
      case 'admin-chat':
        Navigator.of(context).push(
          MaterialPageRoute(
            builder: (_) => const AdminChatConversationsScreen(),
          ),
        );
        break;
      default:
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              'Section "${tile.label}" en préparation pour la version staff mobile.',
            ),
            backgroundColor: AppTheme.accentGold,
            duration: const Duration(seconds: 2),
          ),
        );
    }
  }
}

class _AdminTile {
  final IconData icon;
  final String label;
  final String routeKey;
  final int badge;

  const _AdminTile({
    required this.icon,
    required this.label,
    required this.routeKey,
    this.badge = 0,
  });
}

class _NewOrdersCarouselDialog extends StatefulWidget {
  const _NewOrdersCarouselDialog({required this.orders});

  final List<Order> orders;

  @override
  State<_NewOrdersCarouselDialog> createState() =>
      _NewOrdersCarouselDialogState();
}

class _NewOrdersCarouselDialogState extends State<_NewOrdersCarouselDialog> {
  static const int _totalSeconds = 60;
  late int _remainingSeconds;
  Timer? _timer;
  int _currentIndex = 0;

  @override
  void initState() {
    super.initState();
    _remainingSeconds = _totalSeconds;
    _timer = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (!mounted) return;
      setState(() {
        _remainingSeconds -= 1;
      });
      if (_remainingSeconds <= 0) {
        timer.cancel();
        if (mounted) {
          Navigator.of(context).maybePop();
        }
      }
    });
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  double get _progress => _remainingSeconds / _totalSeconds;

  Order get _currentOrder => widget.orders[_currentIndex];

  @override
  Widget build(BuildContext context) {
    final order = _currentOrder;
    final roomLabel = order.roomNumber != null && order.roomNumber!.isNotEmpty
        ? 'Chambre ${order.roomNumber}'
        : 'Chambre';
    final guestName = order.guestName != null && order.guestName!.isNotEmpty
        ? order.guestName!
        : 'Client';

    return AlertDialog(
      backgroundColor: AppTheme.primaryBlue,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(18),
        side: const BorderSide(color: AppTheme.accentGold, width: 1.5),
      ),
      titlePadding: const EdgeInsets.fromLTRB(20, 20, 20, 0),
      contentPadding: const EdgeInsets.fromLTRB(20, 16, 20, 12),
      title: Row(
        children: [
          Container(
            width: 40,
            height: 40,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: AppTheme.accentGold.withValues(alpha: 0.15),
            ),
            child: const Icon(
              Icons.room_service_outlined,
              color: AppTheme.accentGold,
            ),
          ),
          const SizedBox(width: 12),
          const Expanded(
            child: Text(
              'Nouvelle commande Room Service',
              style: TextStyle(
                color: Colors.white,
                fontSize: 18,
                fontWeight: FontWeight.bold,
              ),
            ),
          ),
        ],
      ),
      content: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (order.items != null &&
              order.items!.isNotEmpty &&
              order.items!.first.image != null &&
              order.items!.first.image!.isNotEmpty)
            Container(
              margin: const EdgeInsets.only(bottom: 12),
              height: 90,
              width: double.infinity,
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(14),
                color: AppTheme.primaryDark.withValues(alpha: 0.6),
              ),
              child: ClipRRect(
                borderRadius: BorderRadius.circular(14),
                child: CachedNetworkImage(
                  imageUrl: order.items!.first.image!,
                  fit: BoxFit.cover,
                  placeholder: (context, url) => Container(
                    color: AppTheme.primaryBlue.withValues(alpha: 0.3),
                    child: const Center(
                      child: Icon(
                        Icons.restaurant,
                        size: 32,
                        color: AppTheme.accentGold,
                      ),
                    ),
                  ),
                  errorWidget: (context, url, error) => Container(
                    color: AppTheme.primaryBlue.withValues(alpha: 0.3),
                    child: const Center(
                      child: Icon(
                        Icons.restaurant,
                        size: 32,
                        color: AppTheme.accentGold,
                      ),
                    ),
                  ),
                ),
              ),
            ),
          if (order.orderNumber.isNotEmpty)
            Text(
              'Commande ${order.orderNumber}',
              style: const TextStyle(
                color: AppTheme.accentGold,
                fontSize: 14,
                fontWeight: FontWeight.w600,
              ),
            ),
          const SizedBox(height: 8),
          Text(
            roomLabel,
            style: const TextStyle(
              color: Colors.white,
              fontSize: 15,
              fontWeight: FontWeight.w700,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            guestName,
            style: const TextStyle(color: AppTheme.textGray, fontSize: 14),
          ),
          const SizedBox(height: 16),
          Center(
            child: SizedBox(
              width: 72,
              height: 72,
              child: Stack(
                alignment: Alignment.center,
                children: [
                  CircularProgressIndicator(
                    value: _progress,
                    strokeWidth: 6,
                    backgroundColor: Colors.white.withValues(alpha: 0.15),
                    valueColor: const AlwaysStoppedAnimation<Color>(
                      AppTheme.accentGold,
                    ),
                  ),
                  Text(
                    '${_remainingSeconds}s',
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ],
              ),
            ),
          ),
          if (widget.orders.length > 1)
            Padding(
              padding: const EdgeInsets.only(top: 8),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  IconButton(
                    icon: const Icon(Icons.chevron_left, color: Colors.white),
                    onPressed: _currentIndex > 0
                        ? () {
                            setState(() {
                              _currentIndex--;
                            });
                          }
                        : null,
                  ),
                  Text(
                    '${_currentIndex + 1}/${widget.orders.length}',
                    style: const TextStyle(
                      color: Colors.white,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.chevron_right, color: Colors.white),
                    onPressed: _currentIndex < widget.orders.length - 1
                        ? () {
                            setState(() {
                              _currentIndex++;
                            });
                          }
                        : null,
                  ),
                ],
              ),
            ),
          const SizedBox(height: 10),
          const Text(
            'Cette alerte disparaîtra automatiquement dans une minute.',
            textAlign: TextAlign.center,
            style: TextStyle(color: AppTheme.textGray, fontSize: 12),
          ),
        ],
      ),
      actions: [
        TextButton(
          onPressed: () {
            Navigator.of(context, rootNavigator: true).pop();
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
            final order = widget.orders[_currentIndex];
            Navigator.of(context, rootNavigator: true).pop(order);
          },
          child: const Text(
            'Ouvrir la commande',
            style: TextStyle(color: Colors.white, fontWeight: FontWeight.w700),
          ),
        ),
      ],
    );
  }
}
