import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../providers/palace_provider.dart';
import '../../providers/auth_provider.dart';
import '../../utils/layout_helper.dart';
import '../../widgets/empty_state.dart';
import '../../models/palace.dart';

class MyPalaceRequestsScreen extends StatefulWidget {
  const MyPalaceRequestsScreen({super.key});

  @override
  State<MyPalaceRequestsScreen> createState() => _MyPalaceRequestsScreenState();
}

class _MyPalaceRequestsScreenState extends State<MyPalaceRequestsScreen> {
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
      context.read<PalaceProvider>().fetchMyPalaceRequests();
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
                                ? 'Services Palace / Conciergerie'
                                : l10n.myRequests,
                            style: TextStyle(
                              fontSize: titleSize,
                              fontWeight: FontWeight.bold,
                              color: AppTheme.accentGold,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            isStaffOrAdmin
                                ? 'Suivi des demandes palace & conciergerie'
                                : l10n.palaceServices,
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
                  context.read<PalaceProvider>().fetchMyPalaceRequests(
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
    final l10n = AppLocalizations.of(context);
    return Consumer<PalaceProvider>(
      builder: (context, provider, child) {
        if (provider.isLoading && provider.requests.isEmpty) {
          return const Center(
            child: CircularProgressIndicator(
              valueColor: AlwaysStoppedAnimation<Color>(AppTheme.accentGold),
            ),
          );
        }

        if (provider.requests.isEmpty) {
          return EmptyStateWidget(
            icon: Icons.star_outline,
            title: l10n.noPalaceRequest,
            subtitle: l10n.noPalaceRequestHint,
          );
        }

        return RefreshIndicator(
          color: AppTheme.accentGold,
          onRefresh: () => provider.fetchMyPalaceRequests(
            period: isStaffOrAdmin && _selectedPeriod != 'all'
                ? _selectedPeriod
                : null,
          ),
          child: NotificationListener<ScrollNotification>(
            onNotification: (ScrollNotification scrollInfo) {
              if (!isStaffOrAdmin) return false;
              if (scrollInfo.metrics.pixels ==
                  scrollInfo.metrics.maxScrollExtent) {
                provider.loadMorePalaceRequests();
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
                  childAspectRatio: _palaceRequestCardAspectRatio(context),
                  crossAxisSpacing: LayoutHelper.gridSpacing(context),
                  mainAxisSpacing: LayoutHelper.gridSpacing(context),
                ),
                itemCount:
                    provider.requests.length +
                    (isStaffOrAdmin && provider.hasMoreRequestPages ? 1 : 0),
                itemBuilder: (context, index) {
                  if (isStaffOrAdmin &&
                      index == provider.requests.length &&
                      provider.hasMoreRequestPages) {
                    return const Center(
                      child: CircularProgressIndicator(
                        valueColor: AlwaysStoppedAnimation<Color>(
                          AppTheme.accentGold,
                        ),
                      ),
                    );
                  }

                  final request = provider.requests[index];
                  final w = MediaQuery.sizeOf(context).width;
                  final detailsText = (request.details ?? '').trim();
                  final hasDetails = detailsText.isNotEmpty;
                  final hasRoomOrGuest =
                      (request.roomNumber != null &&
                          request.roomNumber!.isNotEmpty) ||
                      (request.guestName != null &&
                          request.guestName!.isNotEmpty);
                  final roomGuestText = () {
                    final parts = <String>[];
                    if (request.roomNumber != null &&
                        request.roomNumber!.isNotEmpty) {
                      parts.add('Chambre ${request.roomNumber}');
                    }
                    if (request.guestName != null &&
                        request.guestName!.isNotEmpty) {
                      if (parts.isNotEmpty) {
                        parts.add('– ${request.guestName}');
                      } else {
                        parts.add(request.guestName!);
                      }
                    }
                    return parts.join(' ');
                  }();
                  return GestureDetector(
                    onTap: () => _showPalaceRequestDetails(request),
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
                            colors: [
                              AppTheme.primaryBlue,
                              AppTheme.primaryDark,
                            ],
                          ),
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(
                            color: AppTheme.accentGold,
                            width: 1.5,
                          ),
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
                          padding: EdgeInsets.all(w < 600 ? 12.0 : 16.0),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    request.requestNumber != null &&
                                            request.requestNumber!.isNotEmpty
                                        ? request.requestNumber!
                                        : request.serviceName,
                                    style: const TextStyle(
                                      fontSize: 16,
                                      fontWeight: FontWeight.bold,
                                      color: AppTheme.accentGold,
                                    ),
                                    maxLines: 2,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                  if (request.requestNumber != null &&
                                      request.requestNumber!.isNotEmpty)
                                    const SizedBox(height: 4),
                                  if (request.requestNumber != null &&
                                      request.requestNumber!.isNotEmpty)
                                    Text(
                                      request.serviceName,
                                      style: const TextStyle(
                                        fontSize: 13,
                                        color: AppTheme.textGray,
                                      ),
                                      maxLines: 2,
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                  const SizedBox(height: 8),
                                  _buildStatusBadge(context, request.status),
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
                                            'dd/MM/yyyy HH:mm',
                                            'fr_FR',
                                          ).format(request.createdAt),
                                          style: const TextStyle(
                                            fontSize: 12,
                                            color: AppTheme.textGray,
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                  if (request.scheduledTime != null) ...[
                                    const SizedBox(height: 6),
                                    Row(
                                      children: [
                                        const Icon(
                                          Icons.schedule,
                                          size: 14,
                                          color: AppTheme.accentGold,
                                        ),
                                        const SizedBox(width: 6),
                                        Expanded(
                                          child: Text(
                                            DateFormat(
                                              'dd/MM HH:mm',
                                              'fr_FR',
                                            ).format(request.scheduledTime!),
                                            style: const TextStyle(
                                              fontSize: 12,
                                              color: AppTheme.accentGold,
                                              fontWeight: FontWeight.bold,
                                            ),
                                          ),
                                        ),
                                      ],
                                    ),
                                  ],
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
                                  if (hasDetails) const SizedBox(height: 6),
                                  if (hasDetails)
                                    Row(
                                      children: [
                                        const Icon(
                                          Icons.notes,
                                          size: 14,
                                          color: AppTheme.textGray,
                                        ),
                                        const SizedBox(width: 6),
                                        Expanded(
                                          child: Text(
                                            detailsText,
                                            style: const TextStyle(
                                              fontSize: 12,
                                              color: AppTheme.textGray,
                                            ),
                                            overflow: TextOverflow.ellipsis,
                                          ),
                                        ),
                                      ],
                                    ),
                                  _buildStaffActions(request),
                                ],
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),
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
  double _palaceRequestCardAspectRatio(BuildContext context) {
    final cols = LayoutHelper.gridCrossAxisCount(context);
    final ratio = LayoutHelper.listCellAspectRatio(context);
    if (cols == 2 && LayoutHelper.width(context) < 600) return 0.68;
    return ratio;
  }

  Future<void> _showPalaceRequestDetails(PalaceRequest request) async {
    await Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => PalaceRequestDetailScreen(request: request),
      ),
    );
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
      case 'in_progress':
        return {
          'bg': Colors.blue.withValues(alpha: 0.2),
          'border': Colors.blue,
          'text': Colors.blue,
        };
      case 'completed':
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
      case 'in_progress':
        return l10n.statusInProgress;
      case 'completed':
        return l10n.statusCompleted;
      case 'cancelled':
        return l10n.statusCancelled;
      default:
        return status;
    }
  }

  Widget _buildStaffActions(PalaceRequest request) {
    final auth = context.read<AuthProvider>();
    final isStaffOrAdmin = auth.isAdmin || auth.isStaff;
    if (!isStaffOrAdmin) {
      return const SizedBox.shrink();
    }

    final actions = <Map<String, String>>[];

    if (request.status == 'pending') {
      actions.add({'action': 'accept', 'label': 'Accepter'});
      actions.add({'action': 'cancel', 'label': 'Refuser'});
    } else if (request.status == 'in_progress') {
      actions.add({'action': 'complete', 'label': 'Clôturer'});
      actions.add({'action': 'cancel', 'label': 'Annuler'});
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
                          _handleStaffAction(request, actions[i]['action']!),
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

  Future<void> _handleStaffAction(PalaceRequest request, String action) async {
    final l10n = AppLocalizations.of(context);

    String title;
    String message;
    final reasonController = TextEditingController();
    String? validationError;

    if (action == 'accept') {
      title = 'Accepter la demande';
      message = 'Accepter cette demande de service palace / conciergerie ?';
    } else if (action == 'complete') {
      title = 'Clôturer la demande';
      message = 'Clôturer cette demande ?';
    } else if (action == 'cancel') {
      title = request.status == 'pending' ? 'Refuser la demande' : l10n.cancel;
      message = request.status == 'pending'
          ? 'Refuser cette demande de service palace ?'
          : 'Annuler cette demande de service palace ?';
    } else {
      return;
    }

    final messenger = ScaffoldMessenger.of(context);
    final palaceProvider = context.read<PalaceProvider>();
    final errorPrefix = l10n.errorPrefix;

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
      await palaceProvider.updatePalaceRequestStatus(
        requestId: request.id,
        action: action,
        reason: action == 'cancel' ? reasonController.text.trim() : null,
      );
      messenger.showSnackBar(
        const SnackBar(
          content: Text('Statut mis à jour'),
          backgroundColor: Colors.green,
        ),
      );
    } catch (e) {
      final messageError = e.toString().replaceFirst('Exception: ', '');
      messenger.showSnackBar(
        SnackBar(
          content: Text('$errorPrefix$messageError'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }
}

class PalaceRequestDetailScreen extends StatelessWidget {
  final PalaceRequest request;

  const PalaceRequestDetailScreen({super.key, required this.request});

  @override
  Widget build(BuildContext context) {
    final detailsText = (request.details ?? '').trim();
    final hasDetails = detailsText.isNotEmpty;
    final roomGuestParts = <String>[];
    if (request.roomNumber != null && request.roomNumber!.trim().isNotEmpty) {
      roomGuestParts.add('Chambre ${request.roomNumber}');
    }
    if (request.guestName != null && request.guestName!.trim().isNotEmpty) {
      roomGuestParts.add(request.guestName!.trim());
    }
    final roomGuestText = roomGuestParts.join(' – ');

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
                            request.requestNumber ?? request.serviceName,
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
                            'Détail de la demande palace / conciergerie',
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
                    horizontal: 24,
                    vertical: 16,
                  ),
                  child: Container(
                    padding: const EdgeInsets.all(20),
                    decoration: BoxDecoration(
                      color: AppTheme.primaryBlue.withValues(alpha: 0.6),
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(
                        color: AppTheme.accentGold,
                        width: 1.5,
                      ),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const Text(
                                    'Service palace / conciergerie',
                                    style: TextStyle(
                                      fontSize: 13,
                                      color: AppTheme.textGray,
                                    ),
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    request.serviceName,
                                    style: const TextStyle(
                                      fontSize: 14,
                                      color: Colors.white,
                                    ),
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    DateFormat(
                                      'dd/MM/yyyy à HH:mm',
                                      'fr_FR',
                                    ).format(request.createdAt),
                                    style: const TextStyle(
                                      fontSize: 13,
                                      color: AppTheme.textGray,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            const SizedBox(width: 12),
                            _PalaceStatusBadge(status: request.status),
                          ],
                        ),
                        const SizedBox(height: 16),
                        if (roomGuestText.isNotEmpty)
                          _PalaceInfoRow(
                            icon: Icons.meeting_room,
                            text: roomGuestText,
                          ),
                        if (request.scheduledTime != null) ...[
                          const SizedBox(height: 8),
                          _PalaceInfoRow(
                            icon: Icons.schedule,
                            text:
                                'Prévue pour ${DateFormat('dd/MM/yyyy HH:mm', 'fr_FR').format(request.scheduledTime!)}',
                          ),
                        ],
                        if (hasDetails) ...[
                          const SizedBox(height: 16),
                          const Text(
                            'Détails de la demande',
                            style: TextStyle(
                              fontSize: 15,
                              fontWeight: FontWeight.w600,
                              color: AppTheme.accentGold,
                            ),
                          ),
                          const SizedBox(height: 8),
                          Text(
                            detailsText,
                            style: const TextStyle(
                              fontSize: 13,
                              color: Colors.white,
                            ),
                          ),
                        ],
                        const SizedBox(height: 16),
                        _PalaceStaffActionsForDetail(request: request),
                      ],
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _PalaceInfoRow extends StatelessWidget {
  final IconData icon;
  final String text;

  const _PalaceInfoRow({required this.icon, required this.text});

  @override
  Widget build(BuildContext context) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Icon(icon, size: 18, color: AppTheme.textGray),
        const SizedBox(width: 8),
        Expanded(
          child: Text(
            text,
            style: const TextStyle(fontSize: 13, color: AppTheme.textGray),
          ),
        ),
      ],
    );
  }
}

class _PalaceStatusBadge extends StatelessWidget {
  final String status;

  const _PalaceStatusBadge({required this.status});

  String _label(BuildContext context) {
    switch (status) {
      case 'pending':
        return 'En attente';
      case 'in_progress':
        return 'Acceptée';
      case 'completed':
        return 'Terminée';
      case 'cancelled':
        return 'Annulée';
      default:
        return status;
    }
  }

  @override
  Widget build(BuildContext context) {
    Color bg;
    Color border;
    Color textColor;

    switch (status) {
      case 'pending':
        bg = Colors.orange.withValues(alpha: 0.2);
        border = Colors.orange;
        textColor = Colors.orange;
        break;
      case 'in_progress':
        bg = Colors.blue.withValues(alpha: 0.2);
        border = Colors.blue;
        textColor = Colors.blue;
        break;
      case 'completed':
        bg = Colors.green.withValues(alpha: 0.2);
        border = Colors.green;
        textColor = Colors.green;
        break;
      case 'cancelled':
        bg = Colors.red.withValues(alpha: 0.2);
        border = Colors.red;
        textColor = Colors.red;
        break;
      default:
        bg = AppTheme.textGray.withValues(alpha: 0.2);
        border = AppTheme.textGray;
        textColor = AppTheme.textGray;
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
        _label(context),
        style: TextStyle(
          fontSize: 11,
          fontWeight: FontWeight.bold,
          color: textColor,
        ),
      ),
    );
  }
}

class _PalaceStaffActionsForDetail extends StatelessWidget {
  final PalaceRequest request;

  const _PalaceStaffActionsForDetail({required this.request});

  @override
  Widget build(BuildContext context) {
    final auth = context.read<AuthProvider>();
    final isStaffOrAdmin = auth.isAdmin || auth.isStaff;
    if (!isStaffOrAdmin) {
      return const SizedBox.shrink();
    }

    final actions = <Map<String, String>>[];

    if (request.status == 'pending') {
      actions.add({'action': 'accept', 'label': 'Accepter'});
      actions.add({'action': 'cancel', 'label': 'Refuser'});
    } else if (request.status == 'in_progress') {
      actions.add({'action': 'complete', 'label': 'Clôturer'});
      actions.add({'action': 'cancel', 'label': 'Annuler'});
    }

    if (actions.isEmpty) {
      return const SizedBox.shrink();
    }

    final l10n = AppLocalizations.of(context);

    Future<void> handleAction(String action) async {
      String title;
      String message;
      final reasonController = TextEditingController();
      String? validationError;

      if (action == 'accept') {
        title = 'Accepter la demande';
        message = 'Accepter cette demande de service palace / conciergerie ?';
      } else if (action == 'complete') {
        title = 'Clôturer la demande';
        message = 'Clôturer cette demande ?';
      } else if (action == 'cancel') {
        title = request.status == 'pending'
            ? 'Refuser la demande'
            : l10n.cancel;
        message = request.status == 'pending'
            ? 'Refuser cette demande de service palace ?'
            : 'Annuler cette demande de service palace ?';
      } else {
        return;
      }

      final messenger = ScaffoldMessenger.of(context);
      final navigator = Navigator.of(context);
      final palaceProvider = context.read<PalaceProvider>();
      final errorPrefix = l10n.errorPrefix;

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
        await palaceProvider.updatePalaceRequestStatus(
          requestId: request.id,
          action: action,
          reason: action == 'cancel' ? reasonController.text.trim() : null,
        );

        messenger.showSnackBar(
          const SnackBar(
            content: Text('Statut mis à jour'),
            backgroundColor: Colors.green,
          ),
        );

        navigator.pop();
      } catch (e) {
        final messageError = e.toString().replaceFirst('Exception: ', '');
        messenger.showSnackBar(
          SnackBar(
            content: Text('$errorPrefix$messageError'),
            backgroundColor: Colors.red,
          ),
        );
      }
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
                    height: 40,
                    child: ElevatedButton(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: actions[i]['action'] == 'cancel'
                            ? Colors.red
                            : AppTheme.accentGold,
                        foregroundColor: actions[i]['action'] == 'cancel'
                            ? Colors.white
                            : AppTheme.primaryDark,
                      ),
                      onPressed: () => handleAction(actions[i]['action']!),
                      child: Text(
                        actions[i]['label']!,
                        style: const TextStyle(
                          fontSize: 13,
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
}
