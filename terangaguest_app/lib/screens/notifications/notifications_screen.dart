import 'package:flutter/material.dart';
import 'package:dio/dio.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../providers/auth_provider.dart';
import '../../screens/admin/admin_chat_conversations_screen.dart';
import '../../screens/excursions/my_excursion_bookings_screen.dart';
import '../../screens/hotel_infos/emergency_requests_screen.dart';
import '../../screens/laundry/my_laundry_requests_screen.dart';
import '../../screens/orders/orders_list_screen.dart';
import '../../screens/palace/my_palace_requests_screen.dart';
import '../../screens/restaurants/my_reservations_screen.dart';
import '../../screens/spa/my_spa_reservations_screen.dart';
import '../../services/admin_api.dart';
import '../../services/notifications_api.dart';
import '../../utils/haptic_helper.dart';
import '../../utils/layout_helper.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  final AdminApi _adminApi = AdminApi();
  final NotificationsApi _notificationsApi = NotificationsApi();
  AdminSummary? _summary;
  bool _isLoading = false;
  String? _error;
  bool _notAvailable = false;
  bool _updating = false;

  @override
  void initState() {
    super.initState();
    _loadSummary();
  }

  Future<void> _loadSummary() async {
    setState(() {
      _isLoading = true;
      _error = null;
      _notAvailable = false;
    });
    // L’API admin-summary est réservée au staff : les invités (guests) voient un état « bientôt disponible » au lieu d’une erreur.
    final isAdmin = context.read<AuthProvider>().isAdmin;
    if (!isAdmin) {
      if (!mounted) return;
      setState(() {
        _notAvailable = true;
        _isLoading = false;
      });
      return;
    }
    try {
      final data = await _adminApi.getSummary();
      if (!mounted) return;
      setState(() {
        _summary = data;
        _isLoading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _isLoading = false;
        if (e is DioException &&
            (e.response?.statusCode == 404 || e.response?.statusCode == 403)) {
          _notAvailable = true;
        } else {
          _error =
              'Impossible de charger les notifications. Veuillez réessayer.';
        }
      });
    }
  }

  Future<void> _markAllAsRead() async {
    if (_updating) return;
    setState(() {
      _updating = true;
    });
    try {
      await _notificationsApi.markAllAsRead();
      if (!mounted) return;
      setState(() {
        _summary = null;
        _error = null;
        _notAvailable = false;
        _isLoading = false;
      });
      HapticHelper.lightImpact();
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(AppLocalizations.of(context).notificationsMarkedAsRead),
          backgroundColor: AppTheme.primaryBlue,
        ),
      );
    } catch (_) {
    } finally {
      if (mounted) {
        setState(() {
          _updating = false;
        });
      }
    }
  }

  Future<void> _deleteAll() async {
    if (_updating) return;
    setState(() {
      _updating = true;
    });
    try {
      await _notificationsApi.cleanupRead();
      if (!mounted) return;
      setState(() {
        _summary = null;
        _error = null;
        _notAvailable = false;
        _isLoading = false;
      });
      HapticHelper.lightImpact();
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(AppLocalizations.of(context).notificationsDeleted),
          backgroundColor: AppTheme.primaryBlue,
        ),
      );
    } catch (_) {
    } finally {
      if (mounted) {
        setState(() {
          _updating = false;
        });
      }
    }
  }

  void _openSection(String routeKey) {
    switch (routeKey) {
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
          const SnackBar(
            content: Text(
              'Section en préparation pour la version staff mobile.',
            ),
            backgroundColor: AppTheme.accentGold,
            duration: Duration(seconds: 2),
          ),
        );
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final summary = _summary;

    final notifications = <_NotificationItem>[];
    if (summary != null) {
      if (summary.ordersPending > 0) {
        notifications.add(
          _NotificationItem(
            icon: Icons.room_service_outlined,
            title: l10n.notificationOrdersPending,
            detail:
                '${summary.ordersPending} commande(s) à traiter pour le room service.',
            routeKey: 'admin-room-service',
          ),
        );
      }
      if (summary.restaurantPending > 0) {
        notifications.add(
          _NotificationItem(
            icon: Icons.restaurant_menu_outlined,
            title: l10n.notificationRestaurantsPending,
            detail:
                '${summary.restaurantPending} réservation(s) restaurant à confirmer.',
            routeKey: 'admin-restaurants',
          ),
        );
      }
      if (summary.spaPending > 0) {
        notifications.add(
          _NotificationItem(
            icon: Icons.spa_outlined,
            title: l10n.notificationSpaPending,
            detail:
                '${summary.spaPending} réservation(s) spa à prendre en charge.',
            routeKey: 'admin-spa',
          ),
        );
      }
      if (summary.excursionsPending > 0) {
        notifications.add(
          _NotificationItem(
            icon: Icons.hiking_outlined,
            title: l10n.notificationExcursionsPending,
            detail:
                '${summary.excursionsPending} demande(s) d’excursions à traiter.',
            routeKey: 'admin-excursions',
          ),
        );
      }
      if (summary.laundryPending > 0) {
        notifications.add(
          _NotificationItem(
            icon: Icons.local_laundry_service_outlined,
            title: l10n.notificationLaundryPending,
            detail:
                '${summary.laundryPending} demande(s) blanchisserie à prendre en charge.',
            routeKey: 'admin-laundry',
          ),
        );
      }
      if (summary.palacePending > 0) {
        notifications.add(
          _NotificationItem(
            icon: Icons.workspace_premium_outlined,
            title: l10n.notificationPalacePending,
            detail:
                '${summary.palacePending} demande(s) palace / conciergerie en cours.',
            routeKey: 'admin-palace',
          ),
        );
      }
      if (summary.emergencyOpen > 0) {
        notifications.add(
          _NotificationItem(
            icon: Icons.health_and_safety_outlined,
            title: l10n.notificationEmergency,
            detail:
                '${summary.emergencyOpen} alerte(s) Assistance & Urgence ouvertes.',
            routeKey: 'admin-emergency',
          ),
        );
      }
      if (summary.chatUnreadConversations > 0) {
        notifications.add(
          _NotificationItem(
            icon: Icons.chat_bubble_outline,
            title: l10n.notificationChat,
            detail:
                '${summary.chatUnreadConversations} conversation(s) client non lue(s).',
            routeKey: 'admin-chat',
          ),
        );
      }
    }

    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(gradient: AppTheme.backgroundGradient),
        child: SafeArea(
          child: Column(
            children: [
              Padding(
                padding: const EdgeInsets.all(20.0),
                child: Row(
                  children: [
                    IconButton(
                      icon: const Icon(
                        Icons.arrow_back,
                        color: AppTheme.accentGold,
                      ),
                      onPressed: () {
                        HapticHelper.lightImpact();
                        Navigator.of(context).pop();
                      },
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Text(
                        l10n.notifications,
                        style: const TextStyle(
                          fontSize: 20,
                          fontWeight: FontWeight.bold,
                          color: Colors.white,
                        ),
                      ),
                    ),
                    Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        IconButton(
                          icon: const Icon(
                            Icons.done_all_rounded,
                            color: AppTheme.accentGold,
                          ),
                          onPressed: _updating ? null : () => _markAllAsRead(),
                        ),
                        IconButton(
                          icon: const Icon(
                            Icons.delete_sweep_rounded,
                            color: AppTheme.accentGold,
                          ),
                          onPressed: _updating ? null : () => _deleteAll(),
                        ),
                        IconButton(
                          icon: const Icon(
                            Icons.refresh,
                            color: AppTheme.accentGold,
                          ),
                          onPressed: _updating ? null : _loadSummary,
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              Expanded(
                child: Builder(
                  builder: (context) {
                    if (_isLoading && summary == null && !_notAvailable) {
                      return const Center(
                        child: CircularProgressIndicator(
                          valueColor: AlwaysStoppedAnimation<Color>(
                            AppTheme.accentGold,
                          ),
                        ),
                      );
                    }
                    if (_notAvailable) {
                      return Center(
                        child: Column(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Container(
                              padding: const EdgeInsets.all(16),
                              decoration: BoxDecoration(
                                shape: BoxShape.circle,
                                border: Border.all(
                                  color: AppTheme.accentGold,
                                  width: 2,
                                ),
                                color: AppTheme.primaryDark.withValues(
                                  alpha: 0.4,
                                ),
                              ),
                              child: const Icon(
                                Icons.notifications_none,
                                size: 40,
                                color: AppTheme.accentGold,
                              ),
                            ),
                            const SizedBox(height: 20),
                            Text(
                              l10n.comingSoon,
                              textAlign: TextAlign.center,
                              style: const TextStyle(
                                fontSize: 16,
                                color: AppTheme.textGray,
                              ),
                            ),
                          ],
                        ),
                      );
                    }
                    if (_error != null) {
                      return Center(
                        child: Padding(
                          padding: const EdgeInsets.all(24),
                          child: Column(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(
                                Icons.error_outline,
                                color: Colors.redAccent,
                                size: 40,
                              ),
                              const SizedBox(height: 16),
                              Text(
                                'Erreur lors du chargement des notifications.',
                                textAlign: TextAlign.center,
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 16,
                                ),
                              ),
                              const SizedBox(height: 8),
                              const Text(
                                'Vérifiez votre connexion ou réessayez plus tard.',
                                textAlign: TextAlign.center,
                                style: TextStyle(
                                  color: AppTheme.textGray,
                                  fontSize: 13,
                                ),
                              ),
                              const SizedBox(height: 20),
                              ElevatedButton(
                                onPressed: _loadSummary,
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: AppTheme.accentGold,
                                  foregroundColor: AppTheme.primaryDark,
                                  shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                ),
                                child: Text(l10n.retry),
                              ),
                            ],
                          ),
                        ),
                      );
                    }
                    if (notifications.isEmpty) {
                      return Center(
                        child: Column(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Container(
                              padding: const EdgeInsets.all(16),
                              decoration: BoxDecoration(
                                shape: BoxShape.circle,
                                border: Border.all(
                                  color: AppTheme.accentGold,
                                  width: 2,
                                ),
                                color: AppTheme.primaryDark.withValues(
                                  alpha: 0.4,
                                ),
                              ),
                              child: const Icon(
                                Icons.notifications_none,
                                size: 40,
                                color: AppTheme.accentGold,
                              ),
                            ),
                            const SizedBox(height: 20),
                            const Text(
                              'Aucune nouvelle notification pour le moment.',
                              textAlign: TextAlign.center,
                              style: TextStyle(
                                fontSize: 16,
                                color: AppTheme.textGray,
                              ),
                            ),
                          ],
                        ),
                      );
                    }

                    return RefreshIndicator(
                      color: AppTheme.accentGold,
                      onRefresh: () => _loadSummary(),
                      child: ListView.separated(
                        padding: EdgeInsets.symmetric(
                          horizontal: LayoutHelper.horizontalPaddingValue(
                            context,
                          ),
                          vertical: 16,
                        ),
                        itemBuilder: (context, index) {
                          final n = notifications[index];
                          return _NotificationTile(
                            item: n,
                            onTap: () {
                              HapticHelper.lightImpact();
                              _openSection(n.routeKey);
                            },
                          );
                        },
                        separatorBuilder: (context, index) =>
                            const SizedBox(height: 12),
                        itemCount: notifications.length,
                      ),
                    );
                  },
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _NotificationItem {
  final IconData icon;
  final String title;
  final String detail;
  final String routeKey;

  const _NotificationItem({
    required this.icon,
    required this.title,
    required this.detail,
    required this.routeKey,
  });
}

class _NotificationTile extends StatelessWidget {
  final _NotificationItem item;
  final VoidCallback onTap;

  const _NotificationTile({required this.item, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        borderRadius: BorderRadius.circular(16),
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: AppTheme.primaryBlue.withValues(alpha: 0.65),
            borderRadius: BorderRadius.circular(16),
            border: Border.all(
              color: AppTheme.accentGold.withValues(alpha: 0.5),
              width: 1,
            ),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.45),
                blurRadius: 10,
                offset: const Offset(0, 2),
              ),
            ],
          ),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: AppTheme.primaryDark.withValues(alpha: 0.8),
                  border: Border.all(
                    color: AppTheme.accentGold.withValues(alpha: 0.6),
                    width: 1,
                  ),
                ),
                child: Icon(item.icon, color: AppTheme.accentGold, size: 22),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      item.title,
                      style: const TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.w600,
                        color: Colors.white,
                      ),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      item.detail,
                      style: const TextStyle(
                        fontSize: 12,
                        color: AppTheme.textGray,
                        height: 1.4,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
