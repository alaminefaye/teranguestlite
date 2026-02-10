import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../services/notification_service.dart';
import '../../utils/navigation_helper.dart';
import '../../utils/haptic_helper.dart';
import '../orders/order_detail_screen.dart';

class NotificationsScreen extends StatelessWidget {
  const NotificationsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final ns = NotificationService();

    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(
          gradient: AppTheme.backgroundGradient,
        ),
        child: SafeArea(
          child: Column(
            children: [
              _buildHeader(context, l10n, ns),
              Expanded(child: _buildContent(context, ns)),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildHeader(BuildContext context, AppLocalizations l10n, NotificationService ns) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 12),
      child: Row(
        children: [
          IconButton(
            icon: const Icon(Icons.arrow_back, color: AppTheme.accentGold),
            onPressed: () {
              HapticHelper.lightImpact();
              Navigator.pop(context);
            },
          ),
          Expanded(
            child: Text(
              l10n.notifications,
              style: Theme.of(context).textTheme.headlineMedium,
              textAlign: TextAlign.center,
            ),
          ),
          // Bouton test notification
          TextButton.icon(
            onPressed: () async {
              HapticHelper.lightImpact();
              await ns.showTestNotification();
              if (context.mounted) {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(
                    content: Text(l10n.notificationsOn),
                    backgroundColor: AppTheme.accentGold,
                    duration: const Duration(seconds: 2),
                  ),
                );
              }
            },
            icon: const Icon(Icons.notifications_active, color: AppTheme.accentGold, size: 20),
            label: Text(
              'Test',
              style: TextStyle(color: AppTheme.accentGold, fontSize: 14),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildContent(BuildContext context, NotificationService ns) {
    return ValueListenableBuilder<List<AppNotificationItem>>(
      valueListenable: ns.notificationHistory,
      builder: (context, list, _) {
        if (list.isEmpty) {
          return Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(Icons.notifications_none, size: 72, color: AppTheme.textGray),
                const SizedBox(height: 16),
                Text(
                  'Aucune notification',
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.w600,
                    color: AppTheme.textWhite,
                  ),
                ),
                const SizedBox(height: 8),
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 32),
                  child: Text(
                    'Vos notifications de commandes et réservations apparaîtront ici.',
                    textAlign: TextAlign.center,
                    style: TextStyle(fontSize: 14, color: AppTheme.textGray),
                  ),
                ),
                const SizedBox(height: 24),
                OutlinedButton.icon(
                  onPressed: () async {
                    HapticHelper.lightImpact();
                    await ns.showTestNotification();
                    if (context.mounted) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(
                          content: Text('Notification de test envoyée'),
                          backgroundColor: AppTheme.accentGold,
                          duration: const Duration(seconds: 2),
                        ),
                      );
                    }
                  },
                  icon: const Icon(Icons.notifications_active, size: 20, color: AppTheme.accentGold),
                  label: const Text('Envoyer une notification de test', style: TextStyle(color: AppTheme.accentGold)),
                  style: OutlinedButton.styleFrom(
                    side: const BorderSide(color: AppTheme.accentGold),
                  ),
                ),
              ],
            ),
          );
        }
        return ListView.builder(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
          itemCount: list.length,
          itemBuilder: (context, index) {
            final item = list[index];
            return _NotificationTile(
              item: item,
              onTap: () => _onNotificationTap(context, item),
            );
          },
        );
      },
    );
  }

  void _onNotificationTap(BuildContext context, AppNotificationItem item) {
    final screen = item.data['screen'];
    final orderIdStr = item.data['order_id'];
    if (screen == 'OrderDetails' && orderIdStr != null && orderIdStr.isNotEmpty) {
      final orderId = int.tryParse(orderIdStr);
      if (orderId != null) {
        HapticHelper.lightImpact();
        context.navigateTo(OrderDetailScreen(orderId: orderId));
      }
    }
  }
}

class _NotificationTile extends StatelessWidget {
  const _NotificationTile({required this.item, required this.onTap});

  final AppNotificationItem item;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final dateStr = DateFormat('dd/MM/yyyy HH:mm').format(item.createdAt);
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      color: AppTheme.primaryBlue.withValues(alpha: 0.6),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
        side: const BorderSide(color: AppTheme.cardBorder, width: 1),
      ),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Icon(Icons.notifications, color: AppTheme.accentGold, size: 20),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      item.title,
                      style: const TextStyle(
                        fontWeight: FontWeight.w600,
                        fontSize: 16,
                        color: AppTheme.textWhite,
                      ),
                    ),
                  ),
                  Text(
                    dateStr,
                    style: TextStyle(fontSize: 12, color: AppTheme.textGray),
                  ),
                ],
              ),
              if (item.body.isNotEmpty) ...[
                const SizedBox(height: 6),
                Text(
                  item.body,
                  style: TextStyle(fontSize: 14, color: AppTheme.textGray),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }
}
