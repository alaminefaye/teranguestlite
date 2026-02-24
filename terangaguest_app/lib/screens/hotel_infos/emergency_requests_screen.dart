import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../providers/palace_provider.dart';
import '../../providers/auth_provider.dart';
import '../../models/palace.dart';
import '../../utils/layout_helper.dart';
import '../../widgets/empty_state.dart';
import '../palace/my_palace_requests_screen.dart'
    show PalaceRequestDetailScreen;

class EmergencyRequestsScreen extends StatefulWidget {
  const EmergencyRequestsScreen({super.key});

  @override
  State<EmergencyRequestsScreen> createState() =>
      _EmergencyRequestsScreenState();
}

class _EmergencyRequestsScreenState extends State<EmergencyRequestsScreen> {
  bool _loading = false;
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
    // Éviter "setState/markNeedsBuild called during build" : charger après le 1er frame
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted) _load();
    });
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      await context.read<PalaceProvider>().fetchEmergencyPalaceRequests(
        period: _selectedPeriod == 'all' ? null : _selectedPeriod,
      );
    } finally {
      if (mounted) {
        setState(() => _loading = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final provider = context.watch<PalaceProvider>();
    final auth = context.watch<AuthProvider>();
    final isStaffOrAdmin = auth.isAdmin || auth.isStaff;
    final emergencyRequests = provider.emergencyRequests;
    final spacing = LayoutHelper.gridSpacing(context);
    final w = MediaQuery.sizeOf(context).width;
    final isMobile = w < 600;
    final titleSize = isMobile ? 20.0 : 24.0;
    final pad = isMobile ? 12.0 : 20.0;

    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(gradient: AppTheme.backgroundGradient),
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
                            'Assistance & Urgence',
                            style: TextStyle(
                              fontSize: titleSize,
                              fontWeight: FontWeight.bold,
                              color: AppTheme.accentGold,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            isStaffOrAdmin
                                ? 'Alertes médecin / sécurité en cours'
                                : 'Vos demandes Assistance & Urgence',
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
              Expanded(
                child: RefreshIndicator(
                  onRefresh: _load,
                  color: AppTheme.accentGold,
                  child: Builder(
                    builder: (context) {
                      if (_loading) {
                        return const Center(
                          child: CircularProgressIndicator(
                            valueColor: AlwaysStoppedAnimation<Color>(
                              AppTheme.accentGold,
                            ),
                          ),
                        );
                      }

                      if (emergencyRequests.isEmpty) {
                        return Padding(
                          padding: LayoutHelper.horizontalPadding(context),
                          child: ListView(
                            padding: EdgeInsets.symmetric(vertical: spacing),
                            children: const [
                              EmptyStateWidget(
                                icon: Icons.health_and_safety_outlined,
                                title:
                                    'Aucune alerte Assistance & Urgence en cours.',
                              ),
                            ],
                          ),
                        );
                      }

                      return Padding(
                        padding: LayoutHelper.horizontalPadding(context),
                        child: NotificationListener<ScrollNotification>(
                          onNotification: (ScrollNotification scrollInfo) {
                            if (!isStaffOrAdmin) return false;
                            if (scrollInfo.metrics.pixels ==
                                scrollInfo.metrics.maxScrollExtent) {
                              context
                                  .read<PalaceProvider>()
                                  .loadMoreEmergencyPalaceRequests();
                            }
                            return false;
                          },
                          child: GridView.builder(
                            padding: EdgeInsets.symmetric(vertical: spacing),
                            gridDelegate:
                                SliverGridDelegateWithFixedCrossAxisCount(
                                  crossAxisCount:
                                      LayoutHelper.gridCrossAxisCount(context),
                                  childAspectRatio: _emergencyCardAspectRatio(
                                    context,
                                  ),
                                  crossAxisSpacing: spacing,
                                  mainAxisSpacing: spacing,
                                ),
                            itemCount:
                                emergencyRequests.length +
                                (isStaffOrAdmin &&
                                        provider.hasMoreEmergencyPages
                                    ? 1
                                    : 0),
                            itemBuilder: (context, index) {
                              if (isStaffOrAdmin &&
                                  index == emergencyRequests.length &&
                                  provider.hasMoreEmergencyPages) {
                                return const Center(
                                  child: CircularProgressIndicator(
                                    valueColor: AlwaysStoppedAnimation<Color>(
                                      AppTheme.accentGold,
                                    ),
                                  ),
                                );
                              }

                              final request = emergencyRequests[index];
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
                              final statusText = _statusLabel(
                                l10n,
                                request.status,
                              );

                              return GestureDetector(
                                onTap: () {
                                  Navigator.of(context).push(
                                    MaterialPageRoute(
                                      builder: (_) => PalaceRequestDetailScreen(
                                        request: request,
                                      ),
                                    ),
                                  );
                                },
                                child: Transform(
                                  transform: Matrix4.identity()
                                    ..setEntry(3, 2, 0.001)
                                    ..rotateX(-0.05)
                                    ..rotateY(0.02),
                                  alignment: Alignment.center,
                                  child: Container(
                                    decoration: BoxDecoration(
                                      gradient: const LinearGradient(
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
                                          color: Colors.black.withValues(
                                            alpha: 0.4,
                                          ),
                                          blurRadius: 20,
                                          spreadRadius: 2,
                                          offset: const Offset(0, 10),
                                        ),
                                        BoxShadow(
                                          color: AppTheme.accentGold.withValues(
                                            alpha: 0.1,
                                          ),
                                          blurRadius: 15,
                                          spreadRadius: -2,
                                          offset: const Offset(0, -4),
                                        ),
                                      ],
                                    ),
                                    child: Padding(
                                      padding: const EdgeInsets.all(16.0),
                                      child: Column(
                                        crossAxisAlignment:
                                            CrossAxisAlignment.start,
                                        mainAxisAlignment:
                                            MainAxisAlignment.spaceBetween,
                                        children: [
                                          Column(
                                            crossAxisAlignment:
                                                CrossAxisAlignment.start,
                                            children: [
                                              Text(
                                                request.requestNumber ??
                                                    request.serviceName,
                                                style: const TextStyle(
                                                  fontSize: 16,
                                                  fontWeight: FontWeight.bold,
                                                  color: AppTheme.accentGold,
                                                ),
                                                maxLines: 2,
                                                overflow: TextOverflow.ellipsis,
                                              ),
                                              const SizedBox(height: 4),
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
                                              Container(
                                                padding:
                                                    const EdgeInsets.symmetric(
                                                      horizontal: 10,
                                                      vertical: 4,
                                                    ),
                                                decoration: BoxDecoration(
                                                  color:
                                                      request.status ==
                                                          'pending'
                                                      ? Colors.orange
                                                            .withValues(
                                                              alpha: 0.2,
                                                            )
                                                      : Colors.blue.withValues(
                                                          alpha: 0.2,
                                                        ),
                                                  borderRadius:
                                                      BorderRadius.circular(12),
                                                  border: Border.all(
                                                    color:
                                                        request.status ==
                                                            'pending'
                                                        ? Colors.orange
                                                        : Colors.blue,
                                                    width: 1,
                                                  ),
                                                ),
                                                child: Text(
                                                  statusText,
                                                  style: TextStyle(
                                                    fontSize: 11,
                                                    fontWeight: FontWeight.bold,
                                                    color:
                                                        request.status ==
                                                            'pending'
                                                        ? Colors.orange
                                                        : Colors.blue,
                                                  ),
                                                ),
                                              ),
                                            ],
                                          ),
                                          Column(
                                            crossAxisAlignment:
                                                CrossAxisAlignment.start,
                                            children: [
                                              Row(
                                                children: [
                                                  const Icon(
                                                    Icons.calendar_today,
                                                    size: 14,
                                                    color: AppTheme.textGray,
                                                  ),
                                                  const SizedBox(width: 6),
                                                  Text(
                                                    '${request.createdAt.day.toString().padLeft(2, '0')}/${request.createdAt.month.toString().padLeft(2, '0')} ${request.createdAt.hour.toString().padLeft(2, '0')}:${request.createdAt.minute.toString().padLeft(2, '0')}',
                                                    style: const TextStyle(
                                                      fontSize: 12,
                                                      color: AppTheme.textGray,
                                                    ),
                                                  ),
                                                ],
                                              ),
                                              if (roomGuestText.isNotEmpty) ...[
                                                const SizedBox(height: 6),
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
                                                          color:
                                                              AppTheme.textGray,
                                                        ),
                                                      ),
                                                    ),
                                                  ],
                                                ),
                                              ],
                                              if (isStaffOrAdmin &&
                                                  request.status ==
                                                      'pending') ...[
                                                const SizedBox(height: 10),
                                                Row(
                                                  children: [
                                                    Expanded(
                                                      child: SizedBox(
                                                        height: 34,
                                                        child: ElevatedButton(
                                                          style: ElevatedButton.styleFrom(
                                                            backgroundColor:
                                                                AppTheme
                                                                    .accentGold,
                                                            foregroundColor:
                                                                AppTheme
                                                                    .primaryDark,
                                                            padding:
                                                                const EdgeInsets.symmetric(
                                                                  horizontal: 8,
                                                                ),
                                                          ),
                                                          onPressed: () =>
                                                              _handleCardAction(
                                                                context,
                                                                request,
                                                                'accept',
                                                              ),
                                                          child: const Text(
                                                            'Accepter',
                                                            style: TextStyle(
                                                              fontSize: 11,
                                                              fontWeight:
                                                                  FontWeight
                                                                      .w600,
                                                            ),
                                                          ),
                                                        ),
                                                      ),
                                                    ),
                                                    const SizedBox(width: 8),
                                                    Expanded(
                                                      child: SizedBox(
                                                        height: 34,
                                                        child: ElevatedButton(
                                                          style: ElevatedButton.styleFrom(
                                                            backgroundColor:
                                                                Colors.red,
                                                            foregroundColor:
                                                                Colors.white,
                                                            padding:
                                                                const EdgeInsets.symmetric(
                                                                  horizontal: 8,
                                                                ),
                                                          ),
                                                          onPressed: () =>
                                                              _handleCardAction(
                                                                context,
                                                                request,
                                                                'cancel',
                                                              ),
                                                          child: const Text(
                                                            'Annuler',
                                                            style: TextStyle(
                                                              fontSize: 11,
                                                              fontWeight:
                                                                  FontWeight
                                                                      .w600,
                                                            ),
                                                          ),
                                                        ),
                                                      ),
                                                    ),
                                                  ],
                                                ),
                                              ],
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
                      );
                    },
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  double _emergencyCardAspectRatio(BuildContext context) {
    final cols = LayoutHelper.gridCrossAxisCount(context);
    final ratio = LayoutHelper.listCellAspectRatio(context);
    if (cols == 2 && LayoutHelper.width(context) < 600) return 0.72;
    return ratio;
  }

  Future<void> _handleCardAction(
    BuildContext context,
    PalaceRequest request,
    String action,
  ) async {
    final l10n = AppLocalizations.of(context);
    final reasonController = TextEditingController();
    String? validationError;

    String title;
    String message;
    if (action == 'accept') {
      title = 'Accepter la demande';
      message = 'Accepter cette alerte Assistance & Urgence ?';
    } else {
      title = l10n.cancel;
      message = 'Annuler cette alerte ?';
    }

    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setDialogState) => AlertDialog(
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
                    hintText: "Motif (optionnel)",
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
                    setDialogState(
                      () => validationError = 'Veuillez préciser un motif.',
                    );
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
      await context.read<PalaceProvider>().updatePalaceRequestStatus(
        requestId: request.id,
        action: action,
        reason: action == 'cancel' ? reasonController.text.trim() : null,
      );
      if (!context.mounted) return;
      await context.read<PalaceProvider>().fetchEmergencyPalaceRequests(
        period: _selectedPeriod == 'all' ? null : _selectedPeriod,
      );
      if (!context.mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Statut mis à jour'),
          backgroundColor: Colors.green,
        ),
      );
    } catch (e) {
      if (!context.mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            '${l10n.errorPrefix}${e.toString().replaceFirst('Exception: ', '')}',
          ),
          backgroundColor: Colors.red,
        ),
      );
    }
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
                  _load();
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

  String _statusLabel(AppLocalizations l10n, String status) {
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
}
