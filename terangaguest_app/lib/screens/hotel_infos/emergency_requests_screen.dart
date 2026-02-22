import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../providers/palace_provider.dart';
import '../../providers/auth_provider.dart';
import '../../utils/layout_helper.dart';
import '../../widgets/empty_state.dart';
import '../palace/my_palace_requests_screen.dart'
    show PalaceRequest, PalaceRequestDetailScreen;

class EmergencyRequestsScreen extends StatefulWidget {
  const EmergencyRequestsScreen({super.key});

  @override
  State<EmergencyRequestsScreen> createState() =>
      _EmergencyRequestsScreenState();
}

class _EmergencyRequestsScreenState extends State<EmergencyRequestsScreen> {
  bool _loading = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      await context.read<PalaceProvider>().fetchEmergencyPalaceRequests();
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

    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(gradient: AppTheme.backgroundGradient),
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
                            'Assistance & Urgence',
                            style: const TextStyle(
                              fontSize: 24,
                              fontWeight: FontWeight.bold,
                              color: Colors.white,
                            ),
                          ),
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
              Expanded(
                child: RefreshIndicator(
                  onRefresh: _load,
                  color: AppTheme.accentGold,
                  child: _loading
                      ? const Center(
                          child: CircularProgressIndicator(
                            valueColor: AlwaysStoppedAnimation<Color>(
                              AppTheme.accentGold,
                            ),
                          ),
                        )
                      : emergencyRequests.isEmpty
                      ? Padding(
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
                        )
                      : Padding(
                          padding: LayoutHelper.horizontalPadding(context),
                          child: GridView.builder(
                            padding: EdgeInsets.symmetric(vertical: spacing),
                            gridDelegate:
                                SliverGridDelegateWithFixedCrossAxisCount(
                                  crossAxisCount:
                                      LayoutHelper.gridCrossAxisCount(context),
                                  childAspectRatio:
                                      LayoutHelper.listCellAspectRatio(context),
                                  crossAxisSpacing: spacing,
                                  mainAxisSpacing: spacing,
                                ),
                            itemCount: emergencyRequests.length,
                            itemBuilder: (context, index) {
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
              ),
            ],
          ),
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
