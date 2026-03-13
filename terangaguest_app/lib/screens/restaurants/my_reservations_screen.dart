import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../models/restaurant.dart';
import '../../widgets/empty_state.dart';
import '../../providers/restaurants_provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/tablet_session_provider.dart';
import '../../utils/layout_helper.dart';
import '../../utils/navigation_helper.dart';

class MyRestaurantReservationsScreen extends StatefulWidget {
  const MyRestaurantReservationsScreen({super.key});

  @override
  State<MyRestaurantReservationsScreen> createState() =>
      _MyRestaurantReservationsScreenState();
}

class _MyRestaurantReservationsScreenState
    extends State<MyRestaurantReservationsScreen> {
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
      context.read<RestaurantsProvider>().setClientCode(clientCode);
      context.read<RestaurantsProvider>().fetchMyReservations();
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
                      ? 'Réservations Restaurants'
                      : l10n.myReservations,
                  style: TextStyle(
                    fontSize: titleSize,
                    fontWeight: FontWeight.bold,
                    color: AppTheme.accentGold,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  isStaffOrAdmin
                      ? 'Suivi des réservations restaurants'
                      : l10n.restaurantsBars,
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
                  context.read<RestaurantsProvider>().fetchMyReservations(
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
    return Consumer<RestaurantsProvider>(
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
            icon: Icons.restaurant_outlined,
            title: l10n.noRestaurantReservation,
            subtitle: l10n.noRestaurantReservationHint,
          );
        }

        return RefreshIndicator(
          color: AppTheme.accentGold,
          onRefresh: () => provider.fetchMyReservations(
            period: isStaffOrAdmin && _selectedPeriod != 'all'
                ? _selectedPeriod
                : null,
          ),
          child: NotificationListener<ScrollNotification>(
            onNotification: (ScrollNotification scrollInfo) {
              if (!isStaffOrAdmin) return false;
              if (scrollInfo.metrics.pixels ==
                  scrollInfo.metrics.maxScrollExtent) {
                provider.loadMoreReservations();
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
                  childAspectRatio: _reservationCardAspectRatio(context),
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
                        RestaurantReservationDetailScreen(
                          reservation: reservation,
                        ),
                      );
                      if (updated == true && context.mounted) {
                        context.read<RestaurantsProvider>().fetchMyReservations(
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
  double _reservationCardAspectRatio(BuildContext context) {
    final cols = LayoutHelper.gridCrossAxisCount(context);
    final ratio = LayoutHelper.listCellAspectRatio(context);
    if (cols == 2 && LayoutHelper.width(context) < 600) return 0.72;
    return ratio;
  }

  Widget _buildReservationCard(RestaurantReservation reservation) {
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
        clipBehavior: Clip.antiAlias,
        child: SingleChildScrollView(
          padding: EdgeInsets.all(
            LayoutHelper.width(context) < 600 ? 12.0 : 16.0,
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisSize: MainAxisSize.min,
            children: [
              // Restaurant + Badge
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    reservation.restaurantName,
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

              // Date et heure
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
                  Row(
                    children: [
                      const Icon(
                        Icons.people,
                        size: 14,
                        color: AppTheme.textGray,
                      ),
                      const SizedBox(width: 6),
                      Text(
                        '${reservation.guests} ${AppLocalizations.of(context).personsShort}',
                        style: const TextStyle(
                          fontSize: 12,
                          color: AppTheme.textGray,
                        ),
                      ),
                    ],
                  ),
                  if (!isStaffOrAdmin &&
                      _canCancelReservation(reservation)) ...[
                    const SizedBox(height: 12),
                    SizedBox(
                      width: double.infinity,
                      child: OutlinedButton.icon(
                        onPressed: () =>
                            _showCancelDialog(context, reservation),
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

  Widget _buildStaffActions(RestaurantReservation reservation) {
    final auth = context.read<AuthProvider>();
    final isStaffOrAdmin = auth.isAdmin || auth.isStaff;
    if (!isStaffOrAdmin) {
      return const SizedBox.shrink();
    }

    final actions = <Map<String, String>>[];

    if (reservation.status == 'pending') {
      actions.add({'action': 'confirm', 'label': 'Confirmer'});
      actions.add({'action': 'cancel', 'label': 'Annuler'});
    } else if (reservation.status == 'confirmed') {
      actions.add({'action': 'cancel', 'label': 'Annuler'});
      actions.add({'action': 'honor', 'label': 'Marquer honorée'});
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
                      onPressed: () => _handleStaffAction(
                        reservation,
                        actions[i]['action']!,
                      ),
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
    RestaurantReservation reservation,
    String action,
  ) async {
    final l10n = AppLocalizations.of(context);
    final provider = context.read<RestaurantsProvider>();
    final messenger = ScaffoldMessenger.of(context);

    String title;
    String message;
    final reasonController = TextEditingController();
    String? validationError;

    if (action == 'confirm') {
      title = 'Confirmer la réservation';
      message = 'Confirmer cette réservation restaurant ?';
    } else if (action == 'cancel') {
      title = l10n.cancel;
      message = l10n.cancelReservationConfirm;
    } else if (action == 'honor') {
      title = 'Marquer comme honorée';
      message = 'Marquer cette réservation comme honorée ?';
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
      await provider.updateReservationStatus(
        reservationId: reservation.id,
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
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('${l10n.errorPrefix}$e'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  bool _canCancelReservation(RestaurantReservation r) {
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

  Future<void> _showCancelDialog(
    BuildContext context,
    RestaurantReservation reservation,
  ) async {
    final l10n = AppLocalizations.of(context);
    final provider = context.read<RestaurantsProvider>();
    final messenger = ScaffoldMessenger.of(context);
    final reasonController = TextEditingController();
    String? validationError;
    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setState) => AlertDialog(
          backgroundColor: AppTheme.primaryBlue,
          title: Text(
            l10n.cancel,
            style: const TextStyle(color: AppTheme.accentGold),
          ),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                l10n.cancelReservationConfirm,
                style: const TextStyle(color: Colors.white),
              ),
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
                final text = reasonController.text.trim();
                if (text.isEmpty) {
                  setState(() {
                    validationError = 'Veuillez préciser un motif.';
                  });
                  return;
                }
                Navigator.pop(ctx, true);
              },
              child: Text(l10n.ok, style: const TextStyle(color: Colors.red)),
            ),
          ],
        ),
      ),
    );
    if (ok != true || !context.mounted) return;
    try {
      await provider.cancelReservation(
        reservationId: reservation.id,
        reason: reasonController.text.trim(),
      );
      if (context.mounted) {
        messenger.showSnackBar(
          SnackBar(
            content: Text(l10n.reservationCancelledMessage),
            backgroundColor: Colors.green,
          ),
        );
      }
    } catch (e) {
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
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
      case 'cancelled':
        return l10n.statusCancelled;
      case 'completed':
        return l10n.statusCompleted;
      default:
        return status;
    }
  }
}

class RestaurantReservationDetailScreen extends StatelessWidget {
  final RestaurantReservation reservation;

  const RestaurantReservationDetailScreen({
    super.key,
    required this.reservation,
  });

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final auth = context.watch<AuthProvider>();
    final isStaffOrAdmin = auth.isAdmin || auth.isStaff;
    final canCancelReservation = _canCancelReservation(reservation);

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
              _buildHeader(context),
              Expanded(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 24,
                    vertical: 16,
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      _buildInfoCard(context),
                      const SizedBox(height: 24),
                      if (reservation.specialRequests != null &&
                          reservation.specialRequests!.isNotEmpty) ...[
                        Text(
                          l10n.specialRequests,
                          style: const TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                            color: AppTheme.accentGold,
                          ),
                        ),
                        const SizedBox(height: 8),
                        _buildInfoChip(
                          reservation.specialRequests!,
                          Icons.note_outlined,
                        ),
                        const SizedBox(height: 24),
                      ],
                      if (isStaffOrAdmin)
                        _buildStaffActions(context)
                      else if (canCancelReservation)
                        _buildGuestCancelButton(context, l10n),
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

  Widget _buildHeader(BuildContext context) {
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
                  reservation.restaurantName,
                  style: TextStyle(
                    fontSize: titleSize,
                    fontWeight: FontWeight.bold,
                    color: AppTheme.accentGold,
                  ),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
                const SizedBox(height: 4),
                const Text(
                  'Détail de la réservation',
                  style: TextStyle(fontSize: 13, color: AppTheme.textGray),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildInfoCard(BuildContext context) {
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

    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: AppTheme.primaryBlue.withValues(alpha: 0.6),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppTheme.accentGold, width: 1.5),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          _buildStatusChip(context),
          const SizedBox(height: 16),
          _buildInfoRow(
            Icons.calendar_today,
            DateFormat('EEEE d MMMM yyyy', Localizations.localeOf(context).languageCode).format(reservation.date),
          ),
          const SizedBox(height: 10),
          _buildInfoRow(Icons.access_time, reservation.time),
          const SizedBox(height: 10),
          _buildInfoRow(
            Icons.people,
            '${reservation.guests} ${AppLocalizations.of(context).personsShort}',
          ),
          if (hasRoomOrGuest) ...[
            const SizedBox(height: 10),
            _buildInfoRow(Icons.meeting_room, roomGuestText),
          ],
          if (reservation.status == 'cancelled' &&
              reservation.cancellationReason != null &&
              reservation.cancellationReason!.isNotEmpty) ...[
            const SizedBox(height: 10),
            _buildInfoRow(
              Icons.cancel_outlined,
              'Motif : ${reservation.cancellationReason}',
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildStatusChip(BuildContext context) {
    final colors = _getStatusColor();
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: colors['bg'],
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: colors['border']!, width: 1),
      ),
      child: Text(
        _getStatusLabel(context),
        style: TextStyle(
          fontSize: 13,
          fontWeight: FontWeight.bold,
          color: colors['text'],
        ),
      ),
    );
  }

  Map<String, Color> _getStatusColor() {
    switch (reservation.status) {
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
      case 'cancelled':
        return {
          'bg': Colors.red.withValues(alpha: 0.2),
          'border': Colors.red,
          'text': Colors.red,
        };
      case 'honored':
        return {
          'bg': AppTheme.accentGold.withValues(alpha: 0.2),
          'border': AppTheme.accentGold,
          'text': AppTheme.accentGold,
        };
      default:
        return {
          'bg': AppTheme.textGray.withValues(alpha: 0.2),
          'border': AppTheme.textGray,
          'text': AppTheme.textGray,
        };
    }
  }

  String _getStatusLabel(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    switch (reservation.status) {
      case 'pending':
        return l10n.statusPending;
      case 'confirmed':
        return l10n.statusConfirmed;
      case 'cancelled':
        return l10n.statusCancelled;
      case 'honored':
        return l10n.statusCompleted;
      default:
        return reservation.status;
    }
  }

  Widget _buildInfoRow(IconData icon, String text) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Icon(icon, size: 20, color: AppTheme.accentGold),
        const SizedBox(width: 12),
        Expanded(
          child: Text(
            text,
            style: const TextStyle(fontSize: 15, color: Colors.white),
          ),
        ),
      ],
    );
  }

  Widget _buildInfoChip(String text, IconData icon) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppTheme.primaryBlue.withValues(alpha: 0.5),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppTheme.textGray.withValues(alpha: 0.3)),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, size: 20, color: AppTheme.textGray),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              text,
              style: const TextStyle(fontSize: 14, color: Colors.white70),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStaffActions(BuildContext context) {
    final actions = <Map<String, String>>[];

    if (reservation.status == 'pending') {
      actions.add({'action': 'confirm', 'label': 'Confirmer'});
      actions.add({'action': 'cancel', 'label': 'Annuler'});
    } else if (reservation.status == 'confirmed') {
      actions.add({'action': 'cancel', 'label': 'Annuler'});
      actions.add({'action': 'honor', 'label': 'Marquer honorée'});
    }

    if (actions.isEmpty) {
      return const SizedBox.shrink();
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        const SizedBox(height: 16),
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
    final provider = context.read<RestaurantsProvider>();
    final messenger = ScaffoldMessenger.of(context);
    final navigator = Navigator.of(context);

    String title;
    String message;
    final reasonController = TextEditingController();
    String? validationError;

    if (action == 'confirm') {
      title = 'Confirmer la réservation';
      message = 'Confirmer cette réservation restaurant ?';
    } else if (action == 'cancel') {
      title = l10n.cancel;
      message = l10n.cancelReservationConfirm;
    } else if (action == 'honor') {
      title = 'Marquer comme honorée';
      message = 'Marquer cette réservation comme honorée ?';
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

    if (ok != true || !context.mounted) return;

    try {
      await provider.updateReservationStatus(
        reservationId: reservation.id,
        action: action,
        reason: action == 'cancel' ? reasonController.text.trim() : null,
      );
      if (!context.mounted) return;
      messenger.showSnackBar(
        SnackBar(
          content: Text(AppLocalizations.of(context).statusUpdated),
          backgroundColor: Colors.green,
        ),
      );
      navigator.pop(true);
    } catch (e) {
      if (!context.mounted) return;
      messenger.showSnackBar(
        SnackBar(
          content: Text('${l10n.errorPrefix}$e'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  Widget _buildGuestCancelButton(BuildContext context, AppLocalizations l10n) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        const SizedBox(height: 16),
        SizedBox(
          width: double.infinity,
          child: OutlinedButton.icon(
            onPressed: () => _showCancelDialog(context, l10n),
            icon: const Icon(
              Icons.cancel_outlined,
              size: 20,
              color: Colors.red,
            ),
            label: Text(
              l10n.cancel,
              style: const TextStyle(
                color: Colors.red,
                fontWeight: FontWeight.w600,
              ),
            ),
            style: OutlinedButton.styleFrom(
              side: const BorderSide(color: Colors.red),
              padding: const EdgeInsets.symmetric(vertical: 12),
            ),
          ),
        ),
      ],
    );
  }

  Future<void> _showCancelDialog(
    BuildContext context,
    AppLocalizations l10n,
  ) async {
    final provider = context.read<RestaurantsProvider>();
    final messenger = ScaffoldMessenger.of(context);
    final navigator = Navigator.of(context);
    final reasonController = TextEditingController();
    String? validationError;

    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setState) => AlertDialog(
          backgroundColor: AppTheme.primaryBlue,
          title: Text(
            l10n.cancel,
            style: const TextStyle(color: AppTheme.accentGold),
          ),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                l10n.cancelReservationConfirm,
                style: const TextStyle(color: Colors.white),
              ),
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
                final text = reasonController.text.trim();
                if (text.isEmpty) {
                  setState(() {
                    validationError = 'Veuillez préciser un motif.';
                  });
                  return;
                }
                Navigator.pop(ctx, true);
              },
              child: Text(l10n.ok, style: const TextStyle(color: Colors.red)),
            ),
          ],
        ),
      ),
    );
    if (ok != true || !context.mounted) return;
    try {
      await provider.cancelReservation(
        reservationId: reservation.id,
        reason: reasonController.text.trim(),
      );
      if (!context.mounted) return;
      messenger.showSnackBar(
        SnackBar(
          content: Text(l10n.reservationCancelledMessage),
          backgroundColor: Colors.green,
        ),
      );
      navigator.pop(true);
    } catch (e) {
      if (!context.mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('${l10n.errorPrefix}$e'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  bool _canCancelReservation(RestaurantReservation r) {
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
}
