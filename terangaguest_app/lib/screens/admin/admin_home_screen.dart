import 'dart:async';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../config/theme.dart';
import '../../config/api_config.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../providers/auth_provider.dart';
import '../../services/admin_api.dart';
import '../../utils/haptic_helper.dart';
import '../../utils/layout_helper.dart';
import '../../utils/navigation_helper.dart';
import '../../widgets/service_card.dart';
import '../auth/login_screen.dart';
import '../orders/orders_list_screen.dart';
import '../restaurants/my_reservations_screen.dart';
import '../spa/my_spa_reservations_screen.dart';
import '../excursions/my_excursion_bookings_screen.dart';
import '../laundry/my_laundry_requests_screen.dart';
import '../palace/my_palace_requests_screen.dart';
import '../hotel_infos/assistance_emergency_screen.dart';
import '../notifications/notifications_screen.dart';
import 'admin_chat_conversations_screen.dart';

/// Page d'accueil pour les administrateurs / staff dans l'app mobile.
/// Affiche des boxes pour accéder aux différents modules de gestion.
class AdminHomeScreen extends StatefulWidget {
  const AdminHomeScreen({super.key});

  @override
  State<AdminHomeScreen> createState() => _AdminHomeScreenState();
}

class _AdminHomeScreenState extends State<AdminHomeScreen> {
  final AdminApi _adminApi = AdminApi();
  AdminSummary? _summary;
  bool _isLoading = false;
  Timer? _summaryTimer;

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

  void _handleSummaryDelta(AdminSummary? oldSummary, AdminSummary newSummary) {
    if (oldSummary == null) return;
    final messages = <String>[];
    if (newSummary.ordersPending > oldSummary.ordersPending) {
      messages.add('Nouvelle commande Room Service à traiter');
    }
    if (newSummary.restaurantPending > oldSummary.restaurantPending) {
      messages.add('Nouvelle réservation restaurant à traiter');
    }
    if (newSummary.spaPending > oldSummary.spaPending) {
      messages.add('Nouvelle réservation Spa & Bien-être à traiter');
    }
    if (newSummary.excursionsPending > oldSummary.excursionsPending) {
      messages.add('Nouvelle demande Excursions & Activités à traiter');
    }
    if (newSummary.laundryPending > oldSummary.laundryPending) {
      messages.add('Nouvelle demande Blanchisserie à traiter');
    }
    if (newSummary.palacePending > oldSummary.palacePending) {
      messages.add('Nouvelle demande Palace / Conciergerie à traiter');
    }
    if (newSummary.emergencyOpen > oldSummary.emergencyOpen) {
      messages.add('Nouvelle alerte Assistance & Urgence');
    }
    if (newSummary.chatUnreadConversations >
        oldSummary.chatUnreadConversations) {
      messages.add('Nouveau message client dans le chat');
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
          MaterialPageRoute(builder: (_) => const AssistanceEmergencyScreen()),
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
