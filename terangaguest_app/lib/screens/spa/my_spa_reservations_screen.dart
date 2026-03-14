import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../models/spa.dart';
import '../../widgets/empty_state.dart';
import '../../providers/locale_provider.dart';
import '../../providers/spa_provider.dart';
import '../../providers/tablet_session_provider.dart';
import '../../widgets/translatable_text.dart';
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
  String _selectedPeriod = 'all';

  List<Map<String, String>> _periodFilters() {
    return [
      {'value': 'all', 'label': 'Toutes les dates'},
      {'value': 'today', 'label': 'Aujourd\'hui'},
      {'value': 'week', 'label': 'Cette semaine'},
      {'value': 'month', 'label': 'Ce mois'},
    ];
  }

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!mounted) return;
      // Injecter le code client du guest connecté (tablette)
      final clientCode = context
          .read<TabletSessionProvider>()
          .clientCodeForPreFill;
      context.read<SpaProvider>().setClientCode(clientCode);
      context.read<SpaProvider>().fetchMySpaReservations();
    });
  }

  @override
  Widget build(BuildContext context) {
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
              _buildHeader(),
              if (isStaffOrAdmin) _buildFilters(),
              Expanded(child: _buildContent(isStaffOrAdmin)),
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
    final w = MediaQuery.sizeOf(context).width;
    final isMobile = w < 600;
    final titleSize = isMobile ? 20.0 : 24.0;
    final pad = isMobile ? 12.0 : 20.0;

    return Padding(
      padding: EdgeInsets.all(pad),
      child: Row(
        children: [
          IconButton(
            icon: const Icon(Icons.arrow_back, color: AppTheme.accentGold),
            onPressed: () => Navigator.pop(context),
          ),
          SizedBox(width: isMobile ? 8 : 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  isStaffOrAdmin
                      ? 'Réservations Spa & Bien-être'
                      : l10n.mySpaReservations,
                  style: TextStyle(
                    fontSize: titleSize,
                    fontWeight: FontWeight.bold,
                    color: AppTheme.accentGold,
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

  Widget _buildFilters() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 20),
      child: SizedBox(
        height: 40,
        child: ListView.builder(
          scrollDirection: Axis.horizontal,
          itemCount: _periodFilters().length,
          itemBuilder: (context, index) {
            final filter = _periodFilters()[index];
            final isSelected = _selectedPeriod == filter['value'];

            return Padding(
              padding: const EdgeInsets.only(right: 10),
              child: GestureDetector(
                onTap: () {
                  setState(() {
                    _selectedPeriod = filter['value']!;
                  });
                  context.read<SpaProvider>().fetchMySpaReservations(
                    period: _selectedPeriod == 'all' ? null : _selectedPeriod,
                  );
                },
                child: Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 16,
                    vertical: 8,
                  ),
                  decoration: BoxDecoration(
                    color: isSelected
                        ? AppTheme.accentGold.withValues(alpha: 0.15)
                        : AppTheme.primaryBlue.withValues(alpha: 0.3),
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(
                      color: isSelected
                          ? AppTheme.accentGold
                          : AppTheme.accentGold.withValues(alpha: 0.2),
                    ),
                  ),
                  child: Text(
                    filter['label']!,
                    style: TextStyle(
                      color: isSelected
                          ? AppTheme.accentGold
                          : AppTheme.textGray,
                      fontWeight: isSelected
                          ? FontWeight.w600
                          : FontWeight.normal,
                      fontSize: 12,
                    ),
                  ),
                ),
              ),
            );
          },
        ),
      ),
    );
  }

  Widget _buildContent(bool isStaffOrAdmin) {
    return Consumer<SpaProvider>(
      builder: (context, provider, child) {
        if (provider.isLoading && provider.reservations.isEmpty) {
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
          onRefresh: () => provider.fetchMySpaReservations(
            period: isStaffOrAdmin && _selectedPeriod != 'all'
                ? _selectedPeriod
                : null,
          ),
          child: NotificationListener<ScrollNotification>(
            onNotification: (ScrollNotification scrollInfo) {
              if (!isStaffOrAdmin) return false;
              if (scrollInfo.metrics.pixels ==
                  scrollInfo.metrics.maxScrollExtent) {
                provider.loadMoreSpaReservations();
              }
              return false;
            },
            child: Padding(
              padding: LayoutHelper.horizontalPadding(context),
              child: GridView.builder(
                padding: EdgeInsets.symmetric(
                  vertical: LayoutHelper.gridSpacing(context),
                ),
                gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                  crossAxisCount: LayoutHelper.gridCrossAxisCount(context),
                  childAspectRatio: _spaReservationCardAspectRatio(context),
                  crossAxisSpacing: LayoutHelper.gridSpacing(context),
                  mainAxisSpacing: LayoutHelper.gridSpacing(context),
                ),
                itemCount:
                    provider.reservations.length +
                    (isStaffOrAdmin && provider.hasMoreReservationPages
                        ? 1
                        : 0),
                itemBuilder: (context, index) {
                  if (isStaffOrAdmin &&
                      index == provider.reservations.length &&
                      provider.hasMoreReservationPages) {
                    return const Center(
                      child: CircularProgressIndicator(
                        valueColor: AlwaysStoppedAnimation<Color>(
                          AppTheme.accentGold,
                        ),
                      ),
                    );
                  }

                  final reservation = provider.reservations[index];
                  return InkWell(
                    onTap: () async {
                      final updated = await context.navigateTo(
                        SpaReservationDetailScreen(reservation: reservation),
                      );
                      if (updated == true && context.mounted) {
                        context.read<SpaProvider>().fetchMySpaReservations(
                          period: isStaffOrAdmin && _selectedPeriod != 'all'
                              ? _selectedPeriod
                              : null,
                        );
                      }
                    },
                    borderRadius: BorderRadius.circular(16),
                    child: _buildReservationCard(reservation),
                  );
                },
              ),
            ),
          ),
        );
      },
    );
  }

  /// Ratio plus bas sur mobile pour éviter "Bottom Overflowed" (cartes plus hautes).
  double _spaReservationCardAspectRatio(BuildContext context) {
    final cols = LayoutHelper.gridCrossAxisCount(context);
    final ratio = LayoutHelper.listCellAspectRatio(context);
    if (cols == 2 && LayoutHelper.width(context) < 600) return 0.68;
    return ratio;
  }

  Widget _buildReservationCard(SpaReservation reservation) {
    final auth = context.read<AuthProvider>();
    final isStaffOrAdmin = auth.isAdmin || auth.isStaff;
    final isPendingReschedule = reservation.status == 'pending_reschedule';
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
        clipBehavior: Clip.antiAlias,
        child: SingleChildScrollView(
          padding: EdgeInsets.all(
            LayoutHelper.width(context) < 600 ? 12.0 : 16.0,
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisSize: MainAxisSize.min,
            children: [
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  TranslatableText(
                    reservation.serviceName,
                    locale: context.read<LocaleProvider>().languageCode,
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
              const SizedBox(height: 10),
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
                            Localizations.localeOf(context).languageCode,
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
                  if (!isStaffOrAdmin && isPendingReschedule) ...[
                    const SizedBox(height: 8),
                    Text(
                      'Touchez la carte pour accepter ou annuler le nouvel horaire.',
                      style: const TextStyle(
                        fontSize: 10,
                        color: AppTheme.textGray,
                      ),
                    ),
                  ],
                  if (isStaffOrAdmin ||
                      reservation.status == 'pending' ||
                      reservation.status == 'confirmed')
                    _buildActions(reservation),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildActions(SpaReservation reservation) {
    final auth = context.read<AuthProvider>();
    final isStaffOrAdmin = auth.isAdmin || auth.isStaff;

    final actions = <Map<String, String>>[];

    if (reservation.status == 'pending') {
      if (isStaffOrAdmin) {
        actions.add({'action': 'confirm', 'label': 'Confirmer'});
      }
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
        Row(
          children: actions.map((a) {
            final isCancel = a['action'] == 'cancel';
            final isLast = a == actions.last;
            return Expanded(
              child: Container(
                height: 34,
                margin: EdgeInsets.only(right: isLast ? 0 : 8),
                child: ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: isCancel
                        ? Colors.red
                        : AppTheme.accentGold,
                    foregroundColor: isCancel
                        ? Colors.white
                        : AppTheme.primaryDark,
                    padding: const EdgeInsets.symmetric(horizontal: 4),
                  ),
                  onPressed: () => _handleAction(reservation, a['action']!),
                  child: Text(
                    a['label']!,
                    style: const TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w600,
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
              ),
            );
          }).toList(),
        ),
      ],
    );
  }

  Future<void> _handleAction(SpaReservation reservation, String action) async {
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
    final reasonController = TextEditingController();
    String? validationError;

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
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setState) => AlertDialog(
          backgroundColor: AppTheme.primaryBlue,
          title: Text(
            title,
            style: const TextStyle(color: AppTheme.accentGold),
          ),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(message, style: const TextStyle(color: Colors.white)),
              if (action == 'cancel') ...[
                const SizedBox(height: 12),
                TextField(
                  controller: reasonController,
                  maxLines: 3,
                  style: const TextStyle(color: Colors.white),
                  decoration: InputDecoration(
                    hintText: l10n.cancellationReasonHint,
                    hintStyle: const TextStyle(color: AppTheme.textGray),
                    enabledBorder: const OutlineInputBorder(
                      borderSide: BorderSide(color: AppTheme.textGray),
                    ),
                    focusedBorder: const OutlineInputBorder(
                      borderSide: BorderSide(color: AppTheme.accentGold),
                    ),
                    errorText: validationError,
                  ),
                ),
              ],
            ],
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
              onPressed: () {
                if (action == 'cancel') {
                  final text = reasonController.text.trim();
                  if (text.isEmpty) {
                    setState(() {
                      validationError = 'Veuillez préciser un motif.';
                    });
                    return;
                  }
                }
                Navigator.pop(ctx, true);
              },
              child: Text(
                l10n.ok,
                style: const TextStyle(color: AppTheme.accentGold),
              ),
            ),
          ],
        ),
      ),
    );

    if (!mounted || ok != true) return;

    try {
      await context.read<SpaProvider>().updateSpaReservationStatus(
        reservationId: reservation.id,
        action: action,
        date: newDate,
        time: newTime,
        reason: action == 'cancel' ? reasonController.text.trim() : null,
      );
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(AppLocalizations.of(context).statusUpdated),
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
      case 'pending_reschedule':
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
}
