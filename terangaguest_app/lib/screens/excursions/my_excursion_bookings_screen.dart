import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../providers/excursions_provider.dart';
import '../../models/excursion.dart';
import '../../providers/auth_provider.dart';
import '../../providers/tablet_session_provider.dart';
import '../../utils/layout_helper.dart';
import '../../widgets/empty_state.dart';
import '../../utils/navigation_helper.dart';

class MyExcursionBookingsScreen extends StatefulWidget {
  const MyExcursionBookingsScreen({super.key});

  @override
  State<MyExcursionBookingsScreen> createState() =>
      _MyExcursionBookingsScreenState();
}

class _MyExcursionBookingsScreenState extends State<MyExcursionBookingsScreen> {
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
      context.read<ExcursionsProvider>().setClientCode(clientCode);
      context.read<ExcursionsProvider>().fetchMyExcursionBookings();
    });
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final auth = context.watch<AuthProvider>();
    final isStaffOrAdmin = auth.isAdmin || auth.isStaff;
    final w = MediaQuery.sizeOf(context).width;
    final isMobile = w < 600;
    final titleSize = isMobile ? 20.0 : 24.0;
    final pad = isMobile ? 12.0 : 20.0;
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
                padding: EdgeInsets.all(pad),
                child: Row(
                  children: [
                    IconButton(
                      icon: const Icon(
                        Icons.arrow_back,
                        color: AppTheme.accentGold,
                      ),
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
                                ? 'Réservations Excursions & Activités'
                                : l10n.myExcursionsShort,
                            style: TextStyle(
                              fontSize: titleSize,
                              fontWeight: FontWeight.bold,
                              color: AppTheme.accentGold,
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
              if (isStaffOrAdmin) _buildFilters(),
              Expanded(child: _buildContent(isStaffOrAdmin)),
            ],
          ),
        ),
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
                  context.read<ExcursionsProvider>().fetchMyExcursionBookings(
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
    return Consumer<ExcursionsProvider>(
      builder: (context, provider, child) {
        if (provider.isLoading && provider.bookings.isEmpty) {
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
          onRefresh: () => provider.fetchMyExcursionBookings(
            period: isStaffOrAdmin && _selectedPeriod != 'all'
                ? _selectedPeriod
                : null,
          ),
          child: NotificationListener<ScrollNotification>(
            onNotification: (ScrollNotification scrollInfo) {
              if (!isStaffOrAdmin) return false;
              if (scrollInfo.metrics.pixels ==
                  scrollInfo.metrics.maxScrollExtent) {
                provider.loadMoreExcursionBookings();
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
                  childAspectRatio: LayoutHelper.listCellAspectRatio(context),
                  crossAxisSpacing: LayoutHelper.gridSpacing(context),
                  mainAxisSpacing: LayoutHelper.gridSpacing(context),
                ),
                itemCount:
                    provider.bookings.length +
                    (isStaffOrAdmin && provider.hasMoreBookingPages ? 1 : 0),
                itemBuilder: (context, index) {
                  if (isStaffOrAdmin &&
                      index == provider.bookings.length &&
                      provider.hasMoreBookingPages) {
                    return const Center(
                      child: CircularProgressIndicator(
                        valueColor: AlwaysStoppedAnimation<Color>(
                          AppTheme.accentGold,
                        ),
                      ),
                    );
                  }

                  final booking = provider.bookings[index];
                  return _buildBookingCard(context, booking);
                },
              ),
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

    return InkWell(
      onTap: () async {
        final updated = await context.navigateTo(
          ExcursionBookingDetailScreen(booking: booking),
        );
        if (updated == true && mounted && context.mounted) {
          await context.read<ExcursionsProvider>().fetchMyExcursionBookings(
            period: isStaffOrAdmin && _selectedPeriod != 'all'
                ? _selectedPeriod
                : null,
          );
        }
      },
      borderRadius: BorderRadius.circular(16),
      child: Transform(
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
    final provider = context.read<ExcursionsProvider>();
    final messenger = ScaffoldMessenger.of(context);

    String title;
    String message;
    final reasonController = TextEditingController();
    String? validationError;

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
                    hintText: "Motif de l'annulation",
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

    if (ok != true || !mounted) return;

    try {
      await provider.updateExcursionBookingStatus(
        bookingId: booking.id,
        action: action,
        reason: action == 'cancel' ? reasonController.text.trim() : null,
      );
      if (!mounted) return;
      messenger.showSnackBar(
        SnackBar(
          content: Text(AppLocalizations.of(context).statusUpdated),
          backgroundColor: Colors.green,
        ),
      );
    } catch (e) {
      if (!mounted) return;
      messenger.showSnackBar(
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

class ExcursionBookingDetailScreen extends StatelessWidget {
  final ExcursionBooking booking;

  const ExcursionBookingDetailScreen({super.key, required this.booking});

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final auth = context.watch<AuthProvider>();
    final isStaffOrAdmin = auth.isAdmin || auth.isStaff;

    final roomGuestParts = <String>[];
    if (booking.roomNumber != null && booking.roomNumber!.isNotEmpty) {
      roomGuestParts.add('Chambre ${booking.roomNumber}');
    }
    if (booking.guestName != null && booking.guestName!.isNotEmpty) {
      roomGuestParts.add(booking.guestName!);
    }
    final roomGuestText = roomGuestParts.isEmpty
        ? null
        : roomGuestParts.join(' – ');

    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(
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
                padding: EdgeInsets.all(
                  MediaQuery.sizeOf(context).width < 600 ? 12.0 : 20.0,
                ),
                child: Row(
                  children: [
                    IconButton(
                      icon: const Icon(
                        Icons.arrow_back,
                        color: AppTheme.accentGold,
                      ),
                      onPressed: () => Navigator.pop(context),
                    ),
                    SizedBox(
                      width: MediaQuery.sizeOf(context).width < 600 ? 8 : 12,
                    ),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            booking.excursionName,
                            style: TextStyle(
                              fontSize: MediaQuery.sizeOf(context).width < 600
                                  ? 20.0
                                  : 24.0,
                              fontWeight: FontWeight.bold,
                              color: AppTheme.accentGold,
                            ),
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                          ),
                          const SizedBox(height: 4),
                          const Text(
                            'Détail de la réservation',
                            style: TextStyle(
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
              Expanded(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 40,
                    vertical: 10,
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      _buildMainCard(context, l10n, roomGuestText),
                      const SizedBox(height: 24),
                      if (booking.specialRequests != null &&
                          booking.specialRequests!.trim().isNotEmpty)
                        _buildSpecialRequests(context),
                      if (isStaffOrAdmin) _buildStaffActions(context),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildMainCard(
    BuildContext context,
    AppLocalizations l10n,
    String? roomGuestText,
  ) {
    final dateLabel = DateFormat('EEEE d MMMM yyyy', 'fr_FR')
        .format(booking.date)
        .replaceFirstMapped(RegExp(r'^\w'), (m) => m.group(0)!.toUpperCase());

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: AppTheme.primaryBlue.withValues(alpha: 0.6),
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: AppTheme.accentGold, width: 1.5),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(
                child: Text(
                  booking.excursionName,
                  style: const TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                    color: AppTheme.accentGold,
                  ),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
              const SizedBox(width: 12),
              _buildDetailStatusBadge(context),
            ],
          ),
          const SizedBox(height: 16),
          _buildInfoRow(icon: Icons.calendar_today, label: dateLabel),
          const SizedBox(height: 10),
          _buildInfoRow(
            icon: Icons.people,
            label:
                '${booking.totalParticipants} ${AppLocalizations.of(context).personsShort}',
          ),
          if (roomGuestText != null) const SizedBox(height: 10),
          if (roomGuestText != null)
            _buildInfoRow(icon: Icons.meeting_room, label: roomGuestText),
          const SizedBox(height: 10),
          _buildInfoRow(
            icon: Icons.price_check,
            label: booking.formattedTotalPrice,
          ),
        ],
      ),
    );
  }

  Widget _buildDetailStatusBadge(BuildContext context) {
    Color bg;
    Color border;
    Color text;

    switch (booking.status) {
      case 'pending':
        bg = Colors.orange.withValues(alpha: 0.2);
        border = Colors.orange;
        text = Colors.orange;
        break;
      case 'confirmed':
        bg = Colors.green.withValues(alpha: 0.2);
        border = Colors.green;
        text = Colors.green;
        break;
      case 'completed':
        bg = Colors.blue.withValues(alpha: 0.2);
        border = Colors.blue;
        text = Colors.blue;
        break;
      case 'cancelled':
        bg = Colors.red.withValues(alpha: 0.2);
        border = Colors.red;
        text = Colors.red;
        break;
      default:
        bg = AppTheme.textGray.withValues(alpha: 0.2);
        border = AppTheme.textGray;
        text = AppTheme.textGray;
        break;
    }

    final l10n = AppLocalizations.of(context);
    String label;
    switch (booking.status) {
      case 'pending':
        label = l10n.statusPending;
        break;
      case 'confirmed':
        label = l10n.statusConfirmed;
        break;
      case 'completed':
        label = l10n.statusCompleted;
        break;
      case 'cancelled':
        label = l10n.statusCancelled;
        break;
      default:
        label = booking.status;
        break;
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: border, width: 1),
      ),
      child: Text(
        label,
        style: TextStyle(
          fontSize: 11,
          fontWeight: FontWeight.bold,
          color: text,
        ),
      ),
    );
  }

  Widget _buildInfoRow({required IconData icon, required String label}) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.center,
      children: [
        Icon(icon, size: 18, color: AppTheme.textGray),
        const SizedBox(width: 10),
        Expanded(
          child: Text(
            label,
            style: const TextStyle(fontSize: 15, color: Colors.white),
          ),
        ),
      ],
    );
  }

  Widget _buildSpecialRequests(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          AppLocalizations.of(context).specialRequests,
          style: TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.w600,
            color: AppTheme.accentGold,
          ),
        ),
        const SizedBox(height: 8),
        Container(
          width: double.infinity,
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: AppTheme.primaryBlue.withValues(alpha: 0.5),
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: AppTheme.textGray.withValues(alpha: 0.3)),
          ),
          child: Text(
            booking.specialRequests!.trim(),
            style: const TextStyle(fontSize: 14, color: Colors.white70),
          ),
        ),
      ],
    );
  }

  Widget _buildStaffActions(BuildContext context) {
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
        const SizedBox(height: 24),
        Row(
          children: [
            for (var i = 0; i < actions.length; i++)
              Expanded(
                child: Padding(
                  padding: EdgeInsets.only(
                    right: i == actions.length - 1 ? 0 : 8,
                  ),
                  child: SizedBox(
                    height: 44,
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
                          _handleStaffAction(context, actions[i]['action']!),
                      child: Text(
                        actions[i]['label']!,
                        style: const TextStyle(
                          fontSize: 14,
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

  Future<void> _handleStaffAction(BuildContext context, String action) async {
    final l10n = AppLocalizations.of(context);
    final provider = context.read<ExcursionsProvider>();
    final messenger = ScaffoldMessenger.of(context);
    final navigator = Navigator.of(context);

    String title;
    String message;
    final reasonController = TextEditingController();
    String? validationError;

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
                    hintText: "Motif de l'annulation",
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

    if (ok != true) return;

    try {
      await provider.updateExcursionBookingStatus(
        bookingId: booking.id,
        action: action,
        reason: action == 'cancel' ? reasonController.text.trim() : null,
      );
      // (StatelessWidget) pas de mounted check necessaire ici avec le l10n au prealable.
      messenger.showSnackBar(
        SnackBar(
          content: Text(l10n.statusUpdated),
          backgroundColor: Colors.green,
        ),
      );
      navigator.pop(true);
    } catch (e) {
      messenger.showSnackBar(
        SnackBar(
          content: Text('${l10n.errorPrefix}$e'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }
}
