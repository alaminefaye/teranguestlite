import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../providers/laundry_provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/tablet_session_provider.dart';
import '../../utils/layout_helper.dart';
import '../../widgets/empty_state.dart';
import '../../models/laundry.dart';

class MyLaundryRequestsScreen extends StatefulWidget {
  const MyLaundryRequestsScreen({super.key});

  @override
  State<MyLaundryRequestsScreen> createState() =>
      _MyLaundryRequestsScreenState();
}

class _MyLaundryRequestsScreenState extends State<MyLaundryRequestsScreen> {
  String _selectedPeriod = 'all';

  List<Map<String, String>> _periodFilters(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return [
      {'value': 'all', 'label': l10n.periodAllDates},
      {'value': 'today', 'label': l10n.periodToday},
      {'value': 'week', 'label': l10n.periodThisWeek},
      {'value': 'month', 'label': l10n.periodThisMonth},
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
      context.read<LaundryProvider>().setClientCode(clientCode);
      context.read<LaundryProvider>().fetchMyLaundryRequests();
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
                                ? 'Demandes Blanchisserie'
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
                                ? 'Suivi des demandes de blanchisserie'
                                : l10n.laundry,
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
          itemCount: _periodFilters(context).length,
          itemBuilder: (context, index) {
            final filter = _periodFilters(context)[index];
            final isSelected = _selectedPeriod == filter['value'];

            return Padding(
              padding: const EdgeInsets.only(right: 10),
              child: GestureDetector(
                onTap: () {
                  setState(() {
                    _selectedPeriod = filter['value']!;
                  });
                  context.read<LaundryProvider>().fetchMyLaundryRequests(
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
    return Consumer<LaundryProvider>(
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
            icon: Icons.local_laundry_service_outlined,
            title: l10n.noLaundryRequest,
            subtitle: l10n.noLaundryRequestHint,
          );
        }

        return RefreshIndicator(
          color: AppTheme.accentGold,
          onRefresh: () => provider.fetchMyLaundryRequests(
            period: isStaffOrAdmin && _selectedPeriod != 'all'
                ? _selectedPeriod
                : null,
          ),
          child: NotificationListener<ScrollNotification>(
            onNotification: (ScrollNotification scrollInfo) {
              if (!isStaffOrAdmin) return false;
              if (scrollInfo.metrics.pixels ==
                  scrollInfo.metrics.maxScrollExtent) {
                provider.loadMoreLaundryRequests();
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
                  final hasRoomOrGuest =
                      request.roomNumber != null || request.guestName != null;
                  final roomGuestText = () {
                    final parts = <String>[];
                    if (request.roomNumber != null &&
                        request.roomNumber!.isNotEmpty) {
                      parts.add('${l10n.identityRoom} ${request.roomNumber}');
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

                  return Transform(
                    transform: Matrix4.identity()
                      ..setEntry(3, 2, 0.001)
                      ..rotateX(-0.05)
                      ..rotateY(0.02),
                    alignment: Alignment.center,
                    child: GestureDetector(
                      onTap: () => _showLaundryDetails(request),
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
                                    AppLocalizations.of(
                                      context,
                                    ).requestNumber(request.id),
                                    style: const TextStyle(
                                      fontSize: 16,
                                      fontWeight: FontWeight.bold,
                                      color: AppTheme.accentGold,
                                    ),
                                  ),
                                  const SizedBox(height: 8),
                                  _buildStatusBadge(context, request.status),
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
                                            Localizations.localeOf(context).languageCode,
                                          ).format(request.createdAt),
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
                                        Icons.shopping_basket,
                                        size: 14,
                                        color: AppTheme.textGray,
                                      ),
                                      const SizedBox(width: 6),
                                      Text(
                                        AppLocalizations.of(
                                          context,
                                        ).articleCount(request.totalItems),
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
                                    request.formattedTotalPrice,
                                    style: const TextStyle(
                                      fontSize: 14,
                                      fontWeight: FontWeight.bold,
                                      color: AppTheme.accentGold,
                                    ),
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

  Future<void> _showLaundryDetails(LaundryRequest request) async {
    await Navigator.of(context).push(
      MaterialPageRoute(
        builder: (_) => LaundryRequestDetailScreen(request: request),
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
      case 'picked_up':
        return {
          'bg': Colors.blue.withValues(alpha: 0.2),
          'border': Colors.blue,
          'text': Colors.blue,
        };
      case 'processing':
        return {
          'bg': Colors.purple.withValues(alpha: 0.2),
          'border': Colors.purple,
          'text': Colors.purple,
        };
      case 'ready':
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

  String _getStatusLabel(BuildContext context, String status) {
    final l10n = AppLocalizations.of(context);
    switch (status) {
      case 'pending':
        return l10n.statusPending;
      case 'picked_up':
        return l10n.statusPickedUp;
      case 'processing':
        return l10n.statusProcessing;
      case 'ready':
        return l10n.statusReady;
      case 'delivered':
        return l10n.statusDelivered;
      case 'cancelled':
        return l10n.statusCancelled;
      default:
        return status;
    }
  }

  Widget _buildStaffActions(LaundryRequest request) {
    final auth = context.read<AuthProvider>();
    final isStaffOrAdmin = auth.isAdmin || auth.isStaff;
    if (!isStaffOrAdmin) {
      return const SizedBox.shrink();
    }

    final actions = <Map<String, String>>[];

    if (request.status == 'pending') {
      actions.add({'action': 'pickup', 'label': 'Prendre en charge'});
      actions.add({'action': 'cancel', 'label': 'Annuler'});
    } else if (request.status == 'picked_up') {
      actions.add({'action': 'ready', 'label': 'Marquer comme prête'});
      actions.add({'action': 'cancel', 'label': 'Annuler'});
    } else if (request.status == 'ready') {
      actions.add({'action': 'deliver', 'label': 'Marquer comme livrée'});
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

  Future<void> _handleStaffAction(LaundryRequest request, String action) async {
    final l10n = AppLocalizations.of(context);

    String title;
    String message;
    final reasonController = TextEditingController();
    String? validationError;

    if (action == 'pickup') {
      title = 'Prendre en charge';
      message = 'Prendre en charge cette demande de blanchisserie ?';
    } else if (action == 'ready') {
      title = 'Marquer comme prête';
      message = 'Marquer cette demande comme prête ?';
    } else if (action == 'deliver') {
      title = 'Marquer comme livrée';
      message = 'Marquer cette demande comme livrée ?';
    } else if (action == 'cancel') {
      title = l10n.cancel;
      message = 'Annuler cette demande de blanchisserie ?';
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

    if (!mounted || ok != true) return;

    try {
      await context.read<LaundryProvider>().updateLaundryRequestStatus(
        requestId: request.id,
        action: action,
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
      final messageError = e.toString().replaceFirst('Exception: ', '');
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            '${AppLocalizations.of(context).errorPrefix}$messageError',
          ),
          backgroundColor: Colors.red,
        ),
      );
    }
  }
}

class LaundryRequestDetailScreen extends StatelessWidget {
  final LaundryRequest request;

  const LaundryRequestDetailScreen({super.key, required this.request});

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final items = request.items;
    final hasItems = items.isNotEmpty;
    final hasInstructions =
        request.specialInstructions != null &&
        request.specialInstructions!.trim().isNotEmpty;

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
                            AppLocalizations.of(
                              context,
                            ).requestNumber(request.id),
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
                          Text(
                            l10n.laundryRequestDetailTitle,
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
                                  Text(
                                    l10n.laundry,
                                    style: const TextStyle(
                                      fontSize: 13,
                                      color: AppTheme.textGray,
                                    ),
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    DateFormat(
                                      'dd/MM/yyyy à HH:mm',
                                      Localizations.localeOf(context).languageCode,
                                    ).format(request.createdAt),
                                    style: const TextStyle(
                                      fontSize: 14,
                                      color: Colors.white,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            const SizedBox(width: 12),
                            _StatusBadge(status: request.status),
                          ],
                        ),
                        const SizedBox(height: 16),
                        if (request.roomNumber != null &&
                            request.roomNumber!.trim().isNotEmpty)
                          _InfoRow(
                            icon: Icons.meeting_room,
                            text: '${l10n.identityRoom} ${request.roomNumber}',
                          ),
                        if (request.guestName != null &&
                            request.guestName!.trim().isNotEmpty) ...[
                          const SizedBox(height: 8),
                          _InfoRow(
                            icon: Icons.person,
                            text: request.guestName!,
                          ),
                        ],
                        const SizedBox(height: 16),
                        Text(
                          l10n.articleCount(request.totalItems),
                          style: const TextStyle(
                            fontSize: 15,
                            fontWeight: FontWeight.w600,
                            color: Colors.white,
                          ),
                        ),
                        const SizedBox(height: 8),
                        if (hasItems)
                          ListView.separated(
                            shrinkWrap: true,
                            physics: const NeverScrollableScrollPhysics(),
                            itemCount: items.length,
                            separatorBuilder: (context, index) =>
                                const SizedBox(height: 6),
                            itemBuilder: (ctx, index) {
                              final item = items[index];
                              return Row(
                                children: [
                                  Expanded(
                                    child: Text(
                                      '${item.quantity} x ${item.serviceName}',
                                      style: const TextStyle(
                                        fontSize: 14,
                                        color: Colors.white,
                                      ),
                                    ),
                                  ),
                                  const SizedBox(width: 8),
                                  Text(
                                    item.formattedSubtotal,
                                    style: const TextStyle(
                                      fontSize: 14,
                                      fontWeight: FontWeight.w600,
                                      color: AppTheme.accentGold,
                                    ),
                                  ),
                                ],
                              );
                            },
                          )
                        else
                          Text(
                            l10n.laundryNoItemsInRequest,
                            style: const TextStyle(
                              fontSize: 13,
                              color: AppTheme.textGray,
                            ),
                          ),
                        if (hasInstructions) ...[
                          const SizedBox(height: 16),
                          Container(
                            padding: const EdgeInsets.all(12),
                            decoration: BoxDecoration(
                              color: AppTheme.primaryDark.withValues(
                                alpha: 0.5,
                              ),
                              borderRadius: BorderRadius.circular(8),
                              border: Border.all(
                                color: AppTheme.accentGold.withValues(
                                  alpha: 0.3,
                                ),
                              ),
                            ),
                            child: Row(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Icon(
                                  Icons.info_outline,
                                  size: 18,
                                  color: AppTheme.accentGold,
                                ),
                                const SizedBox(width: 8),
                                Expanded(
                                  child: Text(
                                    request.specialInstructions!,
                                    style: const TextStyle(
                                      fontSize: 13,
                                      color: Colors.white,
                                      fontStyle: FontStyle.italic,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                        const SizedBox(height: 16),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              l10n.total,
                              style: const TextStyle(
                                fontSize: 15,
                                color: AppTheme.textGray,
                              ),
                            ),
                            Text(
                              request.formattedTotalPrice,
                              style: const TextStyle(
                                fontSize: 18,
                                fontWeight: FontWeight.bold,
                                color: AppTheme.accentGold,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 16),
                        _StaffActionsForDetail(request: request),
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

class _StaffActionsForDetail extends StatelessWidget {
  final LaundryRequest request;

  const _StaffActionsForDetail({required this.request});

  @override
  Widget build(BuildContext context) {
    final auth = context.read<AuthProvider>();
    final isStaffOrAdmin = auth.isAdmin || auth.isStaff;
    if (!isStaffOrAdmin) {
      return const SizedBox.shrink();
    }

    final actions = <Map<String, String>>[];

    if (request.status == 'pending') {
      actions.add({'action': 'pickup', 'label': 'Prendre en charge'});
      actions.add({'action': 'cancel', 'label': 'Annuler'});
    } else if (request.status == 'picked_up') {
      actions.add({'action': 'ready', 'label': 'Marquer comme prête'});
      actions.add({'action': 'cancel', 'label': 'Annuler'});
    } else if (request.status == 'ready') {
      actions.add({'action': 'deliver', 'label': 'Marquer comme livrée'});
      actions.add({'action': 'cancel', 'label': 'Annuler'});
    }

    if (actions.isEmpty) {
      return const SizedBox.shrink();
    }

    final l10n = AppLocalizations.of(context);
    final messenger = ScaffoldMessenger.of(context);
    final navigator = Navigator.of(context);
    final laundryProvider = context.read<LaundryProvider>();
    final errorPrefix = l10n.errorPrefix;

    Future<void> handleAction(String action) async {
      String title;
      String message;

      if (action == 'pickup') {
        title = 'Prendre en charge';
        message = 'Prendre en charge cette demande de blanchisserie ?';
      } else if (action == 'ready') {
        title = 'Marquer comme prête';
        message = 'Marquer cette demande comme prête ?';
      } else if (action == 'deliver') {
        title = 'Marquer comme livrée';
        message = 'Marquer cette demande comme livrée ?';
      } else if (action == 'cancel') {
        title = l10n.cancel;
        message = 'Annuler cette demande de blanchisserie ?';
      } else {
        return;
      }

      final ok = await showDialog<bool>(
        context: context,
        builder: (ctx) => AlertDialog(
          backgroundColor: AppTheme.primaryBlue,
          title: Text(
            title,
            style: const TextStyle(color: AppTheme.accentGold),
          ),
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

      if (ok != true) return;

      try {
        await laundryProvider.updateLaundryRequestStatus(
          requestId: request.id,
          action: action,
        );
        messenger.showSnackBar(
          SnackBar(
            content: Text(l10n.statusUpdated),
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
        const SizedBox(height: 8),
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
                      onPressed: () => handleAction(actions[i]['action']!),
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
}

class _StatusBadge extends StatelessWidget {
  final String status;

  const _StatusBadge({required this.status});

  @override
  Widget build(BuildContext context) {
    final colors = _statusColors(status);
    final label = _statusLabel(context, status);

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: colors['bg'],
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: colors['border']!, width: 1),
      ),
      child: Text(
        label,
        style: TextStyle(
          fontSize: 11,
          fontWeight: FontWeight.bold,
          color: colors['text'],
        ),
      ),
    );
  }

  Map<String, Color> _statusColors(String status) {
    switch (status) {
      case 'pending':
        return {
          'bg': Colors.orange.withValues(alpha: 0.2),
          'border': Colors.orange,
          'text': Colors.orange,
        };
      case 'picked_up':
        return {
          'bg': Colors.blue.withValues(alpha: 0.2),
          'border': Colors.blue,
          'text': Colors.blue,
        };
      case 'processing':
        return {
          'bg': Colors.purple.withValues(alpha: 0.2),
          'border': Colors.purple,
          'text': Colors.purple,
        };
      case 'ready':
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

  String _statusLabel(BuildContext context, String status) {
    final l10n = AppLocalizations.of(context);
    switch (status) {
      case 'pending':
        return l10n.statusPending;
      case 'picked_up':
        return l10n.statusPickedUp;
      case 'processing':
        return l10n.statusProcessing;
      case 'ready':
        return l10n.statusReady;
      case 'delivered':
        return l10n.statusDelivered;
      case 'cancelled':
        return l10n.statusCancelled;
      default:
        return status;
    }
  }
}

class _InfoRow extends StatelessWidget {
  final IconData icon;
  final String text;

  const _InfoRow({required this.icon, required this.text});

  @override
  Widget build(BuildContext context) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Icon(icon, size: 18, color: AppTheme.accentGold),
        const SizedBox(width: 10),
        Expanded(
          child: Text(
            text,
            style: const TextStyle(fontSize: 14, color: Colors.white),
          ),
        ),
      ],
    );
  }
}
