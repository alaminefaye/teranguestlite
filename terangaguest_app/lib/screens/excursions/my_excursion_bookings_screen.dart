import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../providers/excursions_provider.dart';
import '../../models/excursion.dart';
import '../../providers/auth_provider.dart';
import '../../utils/layout_helper.dart';
import '../../widgets/empty_state.dart';

class MyExcursionBookingsScreen extends StatefulWidget {
  const MyExcursionBookingsScreen({super.key});

  @override
  State<MyExcursionBookingsScreen> createState() =>
      _MyExcursionBookingsScreenState();
}

class _MyExcursionBookingsScreenState extends State<MyExcursionBookingsScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<ExcursionsProvider>().fetchMyExcursionBookings();
    });
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final auth = context.watch<AuthProvider>();
    final isStaffOrAdmin = auth.isAdmin || auth.isStaff;
    return Scaffold(
      body: Container(
        decoration: BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [AppTheme.primaryDark, AppTheme.primaryBlue],
          ),
        ),
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
                      onPressed: () => Navigator.pop(context),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            isStaffOrAdmin
                                ? 'Réservations Excursions & Activités'
                                : l10n.myExcursionsShort,
                            style: const TextStyle(
                              fontSize: 24,
                              fontWeight: FontWeight.bold,
                              color: Colors.white,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            isStaffOrAdmin
                                ? 'Suivi des excursions & activités'
                                : l10n.reservationsConfirmed,
                            style: const TextStyle(
                              fontSize: 13,
                              color: AppTheme.textGray,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              Expanded(child: _buildContent()),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildContent() {
    return Consumer<ExcursionsProvider>(
      builder: (context, provider, child) {
        if (provider.isLoading) {
          return const Center(
            child: CircularProgressIndicator(
              valueColor: AlwaysStoppedAnimation<Color>(AppTheme.accentGold),
            ),
          );
        }

        if (provider.bookings.isEmpty) {
          final l10n = AppLocalizations.of(context);
          return EmptyStateWidget(
            icon: Icons.landscape_outlined,
            title: l10n.noExcursionBooked,
            subtitle: l10n.noExcursionBookedHint,
          );
        }

        return RefreshIndicator(
          color: AppTheme.accentGold,
          onRefresh: () => provider.fetchMyExcursionBookings(),
          child: Padding(
            padding: LayoutHelper.horizontalPadding(context),
            child: GridView.builder(
              padding: EdgeInsets.symmetric(
                vertical: LayoutHelper.gridSpacing(context),
              ),
              gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                crossAxisCount: LayoutHelper.gridCrossAxisCount(context),
                childAspectRatio: LayoutHelper.listCellAspectRatio(context),
                crossAxisSpacing: LayoutHelper.gridSpacing(context),
                mainAxisSpacing: LayoutHelper.gridSpacing(context),
              ),
              itemCount: provider.bookings.length,
              itemBuilder: (context, index) {
                final booking = provider.bookings[index];
                return _buildBookingCard(context, booking);
              },
            ),
          ),
        );
      },
    );
  }

  Widget _buildBookingCard(BuildContext context, ExcursionBooking booking) {
    final auth = context.read<AuthProvider>();
    final isStaffOrAdmin = auth.isAdmin || auth.isStaff;
    final hasRoomOrGuest =
        booking.roomNumber != null || booking.guestName != null;
    final roomGuestText = () {
      final parts = <String>[];
      if (booking.roomNumber != null && booking.roomNumber!.isNotEmpty) {
        parts.add('Chambre ${booking.roomNumber}');
      }
      if (booking.guestName != null && booking.guestName!.isNotEmpty) {
        if (parts.isNotEmpty) {
          parts.add('– ${booking.guestName}');
        } else {
          parts.add(booking.guestName!);
        }
      }
      return parts.join(' ');
    }();

    return Transform(
      transform: Matrix4.identity()
        ..setEntry(3, 2, 0.001)
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
        child: Padding(
          padding: const EdgeInsets.all(16.0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    booking.excursionName,
                    style: const TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                      color: AppTheme.accentGold,
                    ),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 8),
                  _buildStatusBadge(context, booking.status),
                ],
              ),
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      const Icon(
                        Icons.calendar_today,
                        size: 14,
                        color: AppTheme.textGray,
                      ),
                      const SizedBox(width: 6),
                      Expanded(
                        child: Text(
                          DateFormat(
                            'dd/MM/yyyy',
                            'fr_FR',
                          ).format(booking.date),
                          style: const TextStyle(
                            fontSize: 12,
                            color: AppTheme.textGray,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 6),
                  Row(
                    children: [
                      const Icon(
                        Icons.people,
                        size: 14,
                        color: AppTheme.textGray,
                      ),
                      const SizedBox(width: 6),
                      Text(
                        '${booking.totalParticipants} ${AppLocalizations.of(context).personsShort}',
                        style: const TextStyle(
                          fontSize: 12,
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
                            style: const TextStyle(
                              fontSize: 12,
                              color: AppTheme.textGray,
                            ),
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                      ],
                    ),
                  const SizedBox(height: 6),
                  Text(
                    booking.formattedTotalPrice,
                    style: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.bold,
                      color: AppTheme.accentGold,
                    ),
                  ),
                  if (isStaffOrAdmin) _buildStaffActions(booking),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildStaffActions(ExcursionBooking booking) {
    final auth = context.read<AuthProvider>();
    final isStaffOrAdmin = auth.isAdmin || auth.isStaff;
    if (!isStaffOrAdmin) {
      return const SizedBox.shrink();
    }

    final actions = <Map<String, String>>[];

    if (booking.status == 'pending') {
      actions.add({'action': 'confirm', 'label': 'Confirmer'});
      actions.add({'action': 'cancel', 'label': 'Annuler'});
    } else if (booking.status == 'confirmed') {
      actions.add({'action': 'cancel', 'label': 'Annuler'});
      actions.add({'action': 'complete', 'label': 'Marquer réalisée'});
    }

    if (actions.isEmpty) {
      return const SizedBox.shrink();
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        const SizedBox(height: 12),
        Row(
          children: [
            for (var i = 0; i < actions.length; i++)
              Expanded(
                child: Padding(
                  padding: EdgeInsets.only(
                    right: i == actions.length - 1 ? 0 : 8,
                  ),
                  child: SizedBox(
                    height: 36,
                    child: ElevatedButton(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: actions[i]['action'] == 'cancel'
                            ? Colors.red
                            : AppTheme.accentGold,
                        foregroundColor: actions[i]['action'] == 'cancel'
                            ? Colors.white
                            : AppTheme.primaryDark,
                        padding: const EdgeInsets.symmetric(horizontal: 8),
                      ),
                      onPressed: () =>
                          _handleStaffAction(booking, actions[i]['action']!),
                      child: Text(
                        actions[i]['label']!,
                        style: const TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                  ),
                ),
              ),
          ],
        ),
      ],
    );
  }

  Future<void> _handleStaffAction(
    ExcursionBooking booking,
    String action,
  ) async {
    final l10n = AppLocalizations.of(context);

    String title;
    String message;

    if (action == 'confirm') {
      title = 'Confirmer la réservation';
      message = 'Confirmer cette réservation excursion ?';
    } else if (action == 'cancel') {
      title = l10n.cancel;
      message = l10n.cancelReservationConfirm;
    } else if (action == 'complete') {
      title = 'Marquer comme réalisée';
      message = 'Marquer cette excursion comme réalisée ?';
    } else {
      return;
    }

    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: AppTheme.primaryBlue,
        title: Text(title, style: const TextStyle(color: AppTheme.accentGold)),
        content: Text(message, style: const TextStyle(color: Colors.white)),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: Text(
              l10n.cancel,
              style: const TextStyle(color: AppTheme.textGray),
            ),
          ),
          TextButton(
            onPressed: () => Navigator.pop(ctx, true),
            child: Text(
              l10n.ok,
              style: const TextStyle(color: AppTheme.accentGold),
            ),
          ),
        ],
      ),
    );

    if (ok != true || !mounted) return;

    try {
      await context.read<ExcursionsProvider>().updateExcursionBookingStatus(
        bookingId: booking.id,
        action: action,
      );
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Statut mis à jour'),
          backgroundColor: Colors.green,
        ),
      );
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('${l10n.errorPrefix}$e'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  Widget _buildStatusBadge(BuildContext context, String status) {
    final statusColors = _getStatusColor(status);

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: statusColors['bg'],
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: statusColors['border']!, width: 1),
      ),
      child: Text(
        _getStatusLabel(context, status),
        style: TextStyle(
          fontSize: 11,
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
          'bg': Colors.green.withValues(alpha: 0.2),
          'border': Colors.green,
          'text': Colors.green,
        };
      case 'completed':
        return {
          'bg': Colors.blue.withValues(alpha: 0.2),
          'border': Colors.blue,
          'text': Colors.blue,
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

  String _getStatusLabel(BuildContext context, String status) {
    final l10n = AppLocalizations.of(context);
    switch (status) {
      case 'pending':
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
}
