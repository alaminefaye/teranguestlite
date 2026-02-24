import 'package:flutter/material.dart';
import '../config/theme.dart';
import '../generated/l10n/app_localizations.dart';
import '../models/order.dart';
import '../utils/layout_helper.dart';
import 'package:intl/intl.dart';

class OrderCard extends StatelessWidget {
  final Order order;
  final VoidCallback onTap;

  const OrderCard({super.key, required this.order, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final orderLabel = AppLocalizations.of(context).orderLabel(order.id);
    final hasRoomOrGuest = order.roomNumber != null || order.guestName != null;
    final roomGuestText = () {
      final parts = <String>[];
      if (order.roomNumber != null && order.roomNumber!.isNotEmpty) {
        parts.add('Chambre ${order.roomNumber}');
      }
      if (order.guestName != null && order.guestName!.isNotEmpty) {
        if (parts.isNotEmpty) {
          parts.add('– ${order.guestName}');
        } else {
          parts.add(order.guestName!);
        }
      }
      return parts.join(' ');
    }();
    return Semantics(
      button: true,
      label: orderLabel,
      child: GestureDetector(
        onTap: onTap,
        child: Transform(
          transform: Matrix4.identity()
            ..setEntry(3, 2, 0.001) // Perspective 3D
            ..rotateX(-0.05)
            ..rotateY(0.02),
          alignment: Alignment.center,
          child: Container(
            decoration: BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
                colors: [AppTheme.primaryBlue, AppTheme.primaryDark],
              ),
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: AppTheme.accentGold, width: 1.5),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.4),
                  blurRadius: 20,
                  spreadRadius: 2,
                  offset: const Offset(0, 10),
                ),
                BoxShadow(
                  color: AppTheme.accentGold.withValues(alpha: 0.1),
                  blurRadius: 15,
                  spreadRadius: -2,
                  offset: const Offset(0, -4),
                ),
              ],
            ),
            child: LayoutBuilder(
              builder: (context, constraints) {
                final isMobile = LayoutHelper.width(context) < 600;
                final pad = isMobile ? 12.0 : 16.0;
                final titleSize = isMobile ? 15.0 : 16.0;
                final detailSize = 12.0;
                final totalSize = isMobile ? 16.0 : 18.0;
                return Padding(
                  padding: EdgeInsets.all(pad),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Expanded(
                            child: Text(
                              order.orderNumber,
                              style: TextStyle(
                                fontSize: titleSize,
                                fontWeight: FontWeight.bold,
                                color: AppTheme.accentGold,
                              ),
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                          const SizedBox(width: 8),
                          _buildStatusBadge(isMobile),
                        ],
                      ),
                      const SizedBox(height: 8),
                      Row(
                        children: [
                          const Icon(
                            Icons.access_time,
                            size: 14,
                            color: AppTheme.textGray,
                          ),
                          const SizedBox(width: 6),
                          Text(
                            DateFormat(
                              'dd/MM/yyyy HH:mm',
                              'fr_FR',
                            ).format(order.createdAt),
                            style: TextStyle(
                              fontSize: detailSize,
                              color: AppTheme.textGray,
                            ),
                          ),
                        ],
                      ),
                      if (hasRoomOrGuest) const SizedBox(height: 6),
                      if (hasRoomOrGuest)
                        Row(
                          children: [
                            const Icon(
                              Icons.meeting_room,
                              size: 14,
                              color: AppTheme.textGray,
                            ),
                            const SizedBox(width: 6),
                            Expanded(
                              child: Text(
                                roomGuestText,
                                style: TextStyle(
                                  fontSize: detailSize,
                                  color: AppTheme.textGray,
                                ),
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          ],
                        ),
                      const SizedBox(height: 6),
                      Row(
                        children: [
                          const Icon(
                            Icons.restaurant_menu,
                            size: 14,
                            color: AppTheme.textGray,
                          ),
                          const SizedBox(width: 6),
                          Text(
                            '${order.itemsCount} article${order.itemsCount > 1 ? 's' : ''}',
                            style: TextStyle(
                              fontSize: detailSize,
                              color: AppTheme.textGray,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 10),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(
                            order.formattedTotal,
                            style: TextStyle(
                              fontSize: totalSize,
                              fontWeight: FontWeight.w900,
                              color: AppTheme.accentGold,
                            ),
                          ),
                          const Icon(
                            Icons.arrow_forward_ios,
                            size: 16,
                            color: AppTheme.accentGold,
                          ),
                        ],
                      ),
                    ],
                  ),
                );
              },
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildStatusBadge([bool isMobile = false]) {
    final statusColors = _getStatusColor(order.status);

    return Container(
      padding: EdgeInsets.symmetric(
        horizontal: isMobile ? 8 : 10,
        vertical: 4,
      ),
      decoration: BoxDecoration(
        color: statusColors['bg'],
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: statusColors['border']!, width: 1),
      ),
      child: Text(
        order.statusLabel,
        style: TextStyle(
          fontSize: isMobile ? 10 : 11,
          fontWeight: FontWeight.bold,
          color: statusColors['text'],
        ),
      ),
    );
  }

  Map<String, Color> _getStatusColor(String status) {
    switch (status) {
      case 'pending':
        return {
          'bg': Colors.orange.withValues(alpha: 0.2),
          'border': Colors.orange,
          'text': Colors.orange,
        };
      case 'confirmed':
        return {
          'bg': Colors.blue.withValues(alpha: 0.2),
          'border': Colors.blue,
          'text': Colors.blue,
        };
      case 'preparing':
        return {
          'bg': Colors.purple.withValues(alpha: 0.2),
          'border': Colors.purple,
          'text': Colors.purple,
        };
      case 'delivering':
        return {
          'bg': Colors.cyan.withValues(alpha: 0.2),
          'border': Colors.cyan,
          'text': Colors.cyan,
        };
      case 'delivered':
        return {
          'bg': Colors.green.withValues(alpha: 0.2),
          'border': Colors.green,
          'text': Colors.green,
        };
      case 'cancelled':
        return {
          'bg': Colors.red.withValues(alpha: 0.2),
          'border': Colors.red,
          'text': Colors.red,
        };
      default:
        return {
          'bg': AppTheme.textGray.withValues(alpha: 0.2),
          'border': AppTheme.textGray,
          'text': AppTheme.textGray,
        };
    }
  }
}
