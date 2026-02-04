import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../providers/laundry_provider.dart';
import '../../widgets/empty_state.dart';

class MyLaundryRequestsScreen extends StatefulWidget {
  const MyLaundryRequestsScreen({super.key});

  @override
  State<MyLaundryRequestsScreen> createState() =>
      _MyLaundryRequestsScreenState();
}

class _MyLaundryRequestsScreenState extends State<MyLaundryRequestsScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<LaundryProvider>().fetchMyLaundryRequests();
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
              Padding(
                padding: const EdgeInsets.all(20.0),
                child: Row(
                  children: [
                    IconButton(
                      icon: const Icon(Icons.arrow_back,
                          color: AppTheme.accentGold),
                      onPressed: () => Navigator.pop(context),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            AppLocalizations.of(context).myRequests,
                            style: const TextStyle(
                                fontSize: 24,
                                fontWeight: FontWeight.bold,
                                color: Colors.white),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            AppLocalizations.of(context).laundry,
                            style: const TextStyle(
                                fontSize: 13, color: AppTheme.textGray),
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
    return Consumer<LaundryProvider>(
      builder: (context, provider, child) {
        if (provider.isLoading) {
          return const Center(
              child: CircularProgressIndicator(
                  valueColor:
                      AlwaysStoppedAnimation<Color>(AppTheme.accentGold)));
        }

        if (provider.requests.isEmpty) {
          final l10n = AppLocalizations.of(context);
          return EmptyStateWidget(
            icon: Icons.local_laundry_service_outlined,
            title: l10n.noLaundryRequest,
            subtitle: l10n.noLaundryRequestHint,
          );
        }

        return RefreshIndicator(
          color: AppTheme.accentGold,
          onRefresh: () => provider.fetchMyLaundryRequests(),
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 60.0),
            child: GridView.builder(
              padding: const EdgeInsets.symmetric(vertical: 20),
              gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                crossAxisCount: 4,
                childAspectRatio: 0.9,
                crossAxisSpacing: 20,
                mainAxisSpacing: 20,
              ),
              itemCount: provider.requests.length,
              itemBuilder: (context, index) {
                final request = provider.requests[index];
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
                      border:
                          Border.all(color: AppTheme.accentGold, width: 1.5),
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
                              Text(AppLocalizations.of(context).requestNumber(request.id),
                                  style: const TextStyle(
                                      fontSize: 16,
                                      fontWeight: FontWeight.bold,
                                      color: AppTheme.accentGold)),
                              const SizedBox(height: 8),
                              _buildStatusBadge(context, request.status),
                            ],
                          ),
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  const Icon(Icons.calendar_today,
                                      size: 14, color: AppTheme.textGray),
                                  const SizedBox(width: 6),
                                  Expanded(
                                    child: Text(
                                      DateFormat('dd/MM/yyyy', 'fr_FR')
                                          .format(request.createdAt),
                                      style: const TextStyle(
                                          fontSize: 12,
                                          color: AppTheme.textGray),
                                    ),
                                  ),
                                ],
                              ),
                              const SizedBox(height: 6),
                              Row(
                                children: [
                                  const Icon(Icons.shopping_basket,
                                      size: 14, color: AppTheme.textGray),
                                  const SizedBox(width: 6),
                                  Text(AppLocalizations.of(context).articleCount(request.totalItems),
                                      style: const TextStyle(
                                          fontSize: 12,
                                          color: AppTheme.textGray)),
                                ],
                              ),
                              const SizedBox(height: 6),
                              Text(request.formattedTotalPrice,
                                  style: const TextStyle(
                                      fontSize: 14,
                                      fontWeight: FontWeight.bold,
                                      color: AppTheme.accentGold)),
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
      child: Text(_getStatusLabel(context, status),
          style: TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.bold,
              color: statusColors['text'])),
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
}
