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
                                ? 'Services Palace / Conciergerie'
                                : l10n.myRequests,
                            style: const TextStyle(
                              fontSize: 24,
                              fontWeight: FontWeight.bold,
                              color: Colors.white,
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
              Expanded(child: _buildContent()),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildContent() {
    final l10n = AppLocalizations.of(context);
    return Consumer<PalaceProvider>(
      builder: (context, provider, child) {
        if (provider.isLoading) {
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
          onRefresh: () => provider.fetchMyPalaceRequests(),
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
              itemCount: provider.requests.length,
              itemBuilder: (context, index) {
                final request = provider.requests[index];
                final detailsText = (request.details ?? '').trim();
                final hasDetails = detailsText.isNotEmpty;
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
                                request.serviceName,
                                style: const TextStyle(
                                  fontSize: 16,
                                  fontWeight: FontWeight.bold,
                                  color: AppTheme.accentGold,
                                ),
                                maxLines: 2,
                                overflow: TextOverflow.ellipsis,
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
                              if (hasDetails) const SizedBox(height: 6),
                              if (hasDetails)
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
                );
              },
            ),
          ),
        );
      },
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
      actions.add({'action': 'cancel', 'label': 'Annuler'});
    } else if (request.status == 'in_progress') {
      actions.add({'action': 'complete', 'label': 'Marquer comme terminée'});
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

    if (action == 'accept') {
      title = 'Accepter la demande';
      message = 'Accepter cette demande de service palace / conciergerie ?';
    } else if (action == 'complete') {
      title = 'Marquer comme terminée';
      message = 'Marquer cette demande comme terminée ?';
    } else if (action == 'cancel') {
      title = l10n.cancel;
      message = 'Annuler cette demande de service palace ?';
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
      await context.read<PalaceProvider>().updatePalaceRequestStatus(
        requestId: request.id,
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
      final messageError = e.toString().replaceFirst('Exception: ', '');
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('${l10n.errorPrefix}$messageError'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }
}
