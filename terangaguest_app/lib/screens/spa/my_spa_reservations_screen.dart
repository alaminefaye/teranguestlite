import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../models/spa.dart';
import '../../widgets/empty_state.dart';
import '../../providers/spa_provider.dart';
import '../../utils/layout_helper.dart';
import '../../utils/navigation_helper.dart';
import '../../providers/auth_provider.dart';
import 'spa_reservation_detail_screen.dart';

class MySpaReservationsScreen extends StatefulWidget {
  const MySpaReservationsScreen({super.key});

  @override
  State<MySpaReservationsScreen> createState() =>
      _MySpaReservationsScreenState();
}

class _MySpaReservationsScreenState extends State<MySpaReservationsScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<SpaProvider>().fetchMySpaReservations();
    });
  }

  @override
  Widget build(BuildContext context) {
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
              _buildHeader(),
              Expanded(child: _buildContent()),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildHeader() {
    final l10n = AppLocalizations.of(context);
    final auth = context.watch<AuthProvider>();
    final isStaffOrAdmin = auth.isAdmin || auth.isStaff;

    return Padding(
      padding: const EdgeInsets.all(20.0),
      child: Row(
        children: [
          IconButton(
            icon: const Icon(Icons.arrow_back, color: AppTheme.accentGold),
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
                      ? 'Réservations Spa & Bien-être'
                      : l10n.mySpaReservations,
                  style: const TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.bold,
                    color: Colors.white,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  isStaffOrAdmin
                      ? 'Suivi des réservations spa'
                      : l10n.spaWellness,
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
    );
  }

  Widget _buildContent() {
    return Consumer<SpaProvider>(
      builder: (context, provider, child) {
        if (provider.isLoading) {
          return const Center(
            child: CircularProgressIndicator(
              valueColor: AlwaysStoppedAnimation<Color>(AppTheme.accentGold),
            ),
          );
        }

        if (provider.reservations.isEmpty) {
          final l10n = AppLocalizations.of(context);
          return EmptyStateWidget(
            icon: Icons.spa_outlined,
            title: l10n.noSpaReservation,
            subtitle: l10n.noSpaReservationHint,
          );
        }

        return RefreshIndicator(
          color: AppTheme.accentGold,
          onRefresh: () => provider.fetchMySpaReservations(),
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
              itemCount: provider.reservations.length,
              itemBuilder: (context, index) {
                final reservation = provider.reservations[index];
                return InkWell(
                  onTap: () async {
                    final updated = await context.navigateTo(
                      SpaReservationDetailScreen(reservation: reservation),
                    );
                    if (updated == true && context.mounted) {
                      context.read<SpaProvider>().fetchMySpaReservations();
                    }
                  },
                  borderRadius: BorderRadius.circular(16),
                  child: _buildReservationCard(reservation),
                );
              },
            ),
          ),
        );
      },
    );
  }

  Widget _buildReservationCard(SpaReservation reservation) {
    final auth = context.read<AuthProvider>();
    final isStaffOrAdmin = auth.isAdmin || auth.isStaff;
    final hasRoomOrGuest =
        reservation.roomNumber != null || reservation.guestName != null;
    final roomGuestText = () {
      final parts = <String>[];
      if (reservation.roomNumber != null &&
          reservation.roomNumber!.isNotEmpty) {
        parts.add('Chambre ${reservation.roomNumber}');
      }
      if (reservation.guestName != null && reservation.guestName!.isNotEmpty) {
        if (parts.isNotEmpty) {
          parts.add('– ${reservation.guestName}');
        } else {
          parts.add(reservation.guestName!);
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
                    reservation.serviceName,
                    style: const TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                      color: AppTheme.accentGold,
                    ),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 8),
                  _buildStatusBadge(context, reservation.status),
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
                          ).format(reservation.date),
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
                        Icons.access_time,
                        size: 14,
                        color: AppTheme.textGray,
                      ),
                      const SizedBox(width: 6),
                      Text(
                        reservation.time,
                        style: const TextStyle(
                          fontSize: 12,
                          color: AppTheme.textGray,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 6),
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
                  Text(
                    reservation.formattedPrice,
                    style: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.bold,
                      color: AppTheme.accentGold,
                    ),
                  ),
                  if (!isStaffOrAdmin &&
                      _canCancelSpaReservation(reservation)) ...[
                    const SizedBox(height: 12),
                    SizedBox(
                      width: double.infinity,
                      child: OutlinedButton.icon(
                        onPressed: () =>
                            _showCancelSpaDialog(context, reservation),
                        icon: const Icon(
                          Icons.cancel_outlined,
                          size: 18,
                          color: Colors.red,
                        ),
                        label: Text(
                          AppLocalizations.of(context).cancel,
                          style: const TextStyle(
                            color: Colors.red,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        style: OutlinedButton.styleFrom(
                          side: const BorderSide(color: Colors.red),
                          padding: const EdgeInsets.symmetric(vertical: 8),
                        ),
                      ),
                    ),
                  ],
                  if (isStaffOrAdmin) _buildStaffActions(reservation),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildStaffActions(SpaReservation reservation) {
    final auth = context.read<AuthProvider>();
    final isStaffOrAdmin = auth.isAdmin || auth.isStaff;
    if (!isStaffOrAdmin) {
      return const SizedBox.shrink();
    }

    final actions = <Map<String, String>>[];

    if (reservation.status == 'pending') {
      actions.add({'action': 'confirm', 'label': 'Confirmer'});
      actions.add({'action': 'cancel', 'label': 'Annuler'});
      actions.add({'action': 'reschedule', 'label': 'Replanifier'});
    } else if (reservation.status == 'confirmed') {
      actions.add({'action': 'cancel', 'label': 'Annuler'});
      actions.add({'action': 'reschedule', 'label': 'Replanifier'});
    }

    if (actions.isEmpty) {
      return const SizedBox.shrink();
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        const SizedBox(height: 12),
        Wrap(
          spacing: 8,
          runSpacing: 8,
          children: actions.map((a) {
            final isCancel = a['action'] == 'cancel';
            return SizedBox(
              height: 34,
              child: ElevatedButton(
                style: ElevatedButton.styleFrom(
                  backgroundColor: isCancel ? Colors.red : AppTheme.accentGold,
                  foregroundColor: isCancel
                      ? Colors.white
                      : AppTheme.primaryDark,
                  padding: const EdgeInsets.symmetric(horizontal: 10),
                ),
                onPressed: () => _handleStaffAction(reservation, a['action']!),
                child: Text(
                  a['label']!,
                  style: const TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
            );
          }).toList(),
        ),
      ],
    );
  }

  Future<void> _handleStaffAction(
    SpaReservation reservation,
    String action,
  ) async {
    final l10n = AppLocalizations.of(context);

    DateTime? newDate;
    String? newTime;

    if (action == 'reschedule') {
      final pickedDate = await showDatePicker(
        context: context,
        initialDate: reservation.date.isAfter(DateTime.now())
            ? reservation.date
            : DateTime.now(),
        firstDate: DateTime.now(),
        lastDate: DateTime.now().add(const Duration(days: 365)),
      );

      if (!mounted || pickedDate == null) return;

      final initialTimeParts = reservation.time.split(':');
      final initialTimeOfDay = TimeOfDay(
        hour: int.tryParse(initialTimeParts.first) ?? 10,
        minute: initialTimeParts.length > 1
            ? int.tryParse(initialTimeParts[1]) ?? 0
            : 0,
      );

      final pickedTime = await showTimePicker(
        context: context,
        initialTime: initialTimeOfDay,
      );

      if (!mounted || pickedTime == null) return;

      newDate = pickedDate;
      final hour = pickedTime.hour.toString().padLeft(2, '0');
      final minute = pickedTime.minute.toString().padLeft(2, '0');
      newTime = '$hour:$minute';
    }

    String title;
    String message;

    if (action == 'confirm') {
      title = 'Confirmer la réservation';
      message = 'Confirmer cette réservation spa ?';
    } else if (action == 'cancel') {
      title = l10n.cancel;
      message = l10n.cancelReservationConfirm;
    } else if (action == 'reschedule') {
      title = 'Replanifier la réservation';
      message = 'Confirmer la nouvelle date/heure pour cette réservation spa ?';
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

    if (!mounted || ok != true) return;

    try {
      await context.read<SpaProvider>().updateSpaReservationStatus(
        reservationId: reservation.id,
        action: action,
        date: newDate,
        time: newTime,
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

  bool _canCancelSpaReservation(SpaReservation r) {
    if (r.status != 'confirmed') return false;
    final parts = r.time.split(':');
    final hour = parts.isNotEmpty ? (int.tryParse(parts[0]) ?? 0) : 0;
    final minute = parts.length > 1 ? (int.tryParse(parts[1]) ?? 0) : 0;
    final reservationDateTime = DateTime(
      r.date.year,
      r.date.month,
      r.date.day,
      hour,
      minute,
    );
    return reservationDateTime.isAfter(
      DateTime.now().add(const Duration(hours: 24)),
    );
  }

  Future<void> _showCancelSpaDialog(
    BuildContext context,
    SpaReservation reservation,
  ) async {
    final l10n = AppLocalizations.of(context);
    final dialogContext = context;
    final ok = await showDialog<bool>(
      context: dialogContext,
      builder: (ctx) => AlertDialog(
        backgroundColor: AppTheme.primaryBlue,
        title: Text(
          l10n.cancel,
          style: const TextStyle(color: AppTheme.accentGold),
        ),
        content: Text(
          l10n.cancelReservationConfirm,
          style: const TextStyle(color: Colors.white),
        ),
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
            child: Text(l10n.ok, style: const TextStyle(color: Colors.red)),
          ),
        ],
      ),
    );
    if (ok != true || !dialogContext.mounted) return;
    try {
      await dialogContext.read<SpaProvider>().cancelSpaReservation(
        reservation.id,
      );
      if (dialogContext.mounted) {
        ScaffoldMessenger.of(dialogContext).showSnackBar(
          SnackBar(
            content: Text(l10n.reservationCancelledMessage),
            backgroundColor: Colors.green,
          ),
        );
      }
    } catch (e) {
      if (dialogContext.mounted) {
        ScaffoldMessenger.of(dialogContext).showSnackBar(
          SnackBar(
            content: Text('${l10n.errorPrefix}$e'),
            backgroundColor: Colors.red,
          ),
        );
      }
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
