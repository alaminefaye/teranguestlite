import 'package:flutter/material.dart';
import 'package:dio/dio.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../services/admin_api.dart';
import '../../utils/haptic_helper.dart';
import '../../utils/layout_helper.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  final AdminApi _adminApi = AdminApi();
  AdminSummary? _summary;
  bool _isLoading = false;
  String? _error;
  bool _notAvailable = false;

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
        if (e is DioException && e.response?.statusCode == 404) {
          _notAvailable = true;
        } else {
          _error =
              'Impossible de charger les notifications. Veuillez réessayer.';
        }
      });
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
            title: 'Commandes Room Service en attente',
            detail:
                '${summary.ordersPending} commande(s) à traiter pour le room service.',
          ),
        );
      }
      if (summary.restaurantPending > 0) {
        notifications.add(
          _NotificationItem(
            icon: Icons.restaurant_menu_outlined,
            title: 'Réservations Restaurants en attente',
            detail:
                '${summary.restaurantPending} réservation(s) restaurant à confirmer.',
          ),
        );
      }
      if (summary.spaPending > 0) {
        notifications.add(
          _NotificationItem(
            icon: Icons.spa_outlined,
            title: 'Réservations Spa & Bien-être en attente',
            detail:
                '${summary.spaPending} réservation(s) spa à prendre en charge.',
          ),
        );
      }
      if (summary.excursionsPending > 0) {
        notifications.add(
          _NotificationItem(
            icon: Icons.hiking_outlined,
            title: 'Excursions & Activités en attente',
            detail:
                '${summary.excursionsPending} demande(s) d’excursions à traiter.',
          ),
        );
      }
      if (summary.laundryPending > 0) {
        notifications.add(
          _NotificationItem(
            icon: Icons.local_laundry_service_outlined,
            title: 'Demandes Blanchisserie en attente',
            detail:
                '${summary.laundryPending} demande(s) blanchisserie à prendre en charge.',
          ),
        );
      }
      if (summary.palacePending > 0) {
        notifications.add(
          _NotificationItem(
            icon: Icons.workspace_premium_outlined,
            title: 'Services Palace / Conciergerie en attente',
            detail:
                '${summary.palacePending} demande(s) palace / conciergerie en cours.',
          ),
        );
      }
      if (summary.emergencyOpen > 0) {
        notifications.add(
          _NotificationItem(
            icon: Icons.health_and_safety_outlined,
            title: 'Assistance & Urgence',
            detail:
                '${summary.emergencyOpen} alerte(s) Assistance & Urgence ouvertes.',
          ),
        );
      }
      if (summary.chatUnreadConversations > 0) {
        notifications.add(
          _NotificationItem(
            icon: Icons.chat_bubble_outline,
            title: 'Messages / Chat client',
            detail:
                '${summary.chatUnreadConversations} conversation(s) client non lue(s).',
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
                          fontSize: 24,
                          fontWeight: FontWeight.bold,
                          color: Colors.white,
                        ),
                      ),
                    ),
                    IconButton(
                      icon: const Icon(
                        Icons.refresh,
                        color: AppTheme.accentGold,
                      ),
                      onPressed: _loadSummary,
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
                                child: const Text('Réessayer'),
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
                          return _NotificationTile(item: n);
                        },
                        separatorBuilder: (_, __) => const SizedBox(height: 12),
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

  const _NotificationItem({
    required this.icon,
    required this.title,
    required this.detail,
  });
}

class _NotificationTile extends StatelessWidget {
  final _NotificationItem item;

  const _NotificationTile({required this.item});

  @override
  Widget build(BuildContext context) {
    return Container(
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
                    fontSize: 16,
                    fontWeight: FontWeight.w600,
                    color: Colors.white,
                  ),
                ),
                const SizedBox(height: 6),
                Text(
                  item.detail,
                  style: const TextStyle(
                    fontSize: 13,
                    color: AppTheme.textGray,
                    height: 1.4,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
