import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../utils/layout_helper.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../providers/orders_provider.dart';
import '../../providers/auth_provider.dart';
import '../../widgets/order_card.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/error_state.dart';
import '../../utils/navigation_helper.dart';
import '../../utils/haptic_helper.dart';
import '../dashboard/dashboard_screen.dart';
import 'order_detail_screen.dart';

class OrdersListScreen extends StatefulWidget {
  /// Si true, la liste est vidée puis rechargée à l'ouverture (après un court
  /// délai) pour afficher la commande venant d'être créée.
  final bool fromOrderCreation;

  const OrdersListScreen({super.key, this.fromOrderCreation = false});

  @override
  State<OrdersListScreen> createState() => _OrdersListScreenState();
}

class _OrdersListScreenState extends State<OrdersListScreen> {
  String? _selectedStatus;
  String _selectedPeriod = 'all';

  List<Map<String, String>> _statusFilters(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return [
      {'value': '', 'label': l10n.filterAll},
      {'value': 'pending', 'label': l10n.filterPending},
      {'value': 'confirmed', 'label': l10n.filterConfirmed},
      {'value': 'preparing', 'label': l10n.filterPreparing},
      {'value': 'delivering', 'label': l10n.filterDelivering},
      {'value': 'delivered', 'label': l10n.filterDelivered},
    ];
  }

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
      OrdersProvider? provider;
      try {
        provider = context.read<OrdersProvider>();
      } catch (_) {
        return;
      }
      _loadOrders(provider);
    });
  }

  Future<void> _loadOrders(OrdersProvider provider) async {
    if (widget.fromOrderCreation) {
      provider.clearOrdersAndSetLoading();
      await Future.delayed(const Duration(milliseconds: 500));
      if (!mounted) return;
      context.read<OrdersProvider>().refreshOrders();
    } else {
      _safeFetchOrders(
        status: _selectedStatus,
        period: _selectedPeriod == 'all' ? null : _selectedPeriod,
      );
    }
  }

  void _safeFetchOrders({String? status, String? period}) {
    if (!mounted) return;
    try {
      context.read<OrdersProvider>().fetchOrders(
        status: status,
        period: period,
      );
    } catch (_) {}
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
              // Header
              _buildHeader(),

              _buildFilters(),

              // Liste des commandes
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
    final w = MediaQuery.sizeOf(context).width;
    final isMobile = w < 600;
    // Même style que l'écran Détail Commande : titre or, tailles uniformes
    final titleSize = isMobile ? 20.0 : 24.0;
    final pad = isMobile ? 12.0 : 20.0;

    return Padding(
      padding: EdgeInsets.all(pad),
      child: Row(
        children: [
          IconButton(
            icon: const Icon(Icons.arrow_back, color: AppTheme.accentGold),
            onPressed: () {
              HapticHelper.lightImpact();
              if (Navigator.canPop(context)) {
                Navigator.pop(context);
              } else {
                NavigationHelper.navigateAndRemoveUntil(
                  context,
                  const DashboardScreen(),
                );
              }
            },
          ),
          SizedBox(width: isMobile ? 8 : 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  isStaffOrAdmin ? 'Commandes Room Service' : l10n.myOrders,
                  style: TextStyle(
                    fontSize: titleSize,
                    fontWeight: FontWeight.bold,
                    color: AppTheme.accentGold,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  isStaffOrAdmin
                      ? 'Suivi et traitement des commandes room service'
                      : l10n.ordersHistorySubtitle,
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
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            height: 50,
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              itemCount: _statusFilters(context).length,
              itemBuilder: (context, index) {
                final filter = _statusFilters(context)[index];
                final isSelected =
                    _selectedStatus == filter['value'] ||
                    (_selectedStatus == null && filter['value'] == '');

                return Padding(
                  padding: const EdgeInsets.only(right: 10),
                  child: GestureDetector(
                    onTap: () {
                      setState(() {
                        _selectedStatus = filter['value']!.isEmpty
                            ? null
                            : filter['value'];
                      });
                      _safeFetchOrders(
                        status: _selectedStatus,
                        period: _selectedPeriod == 'all'
                            ? null
                            : _selectedPeriod,
                      );
                    },
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 20,
                        vertical: 12,
                      ),
                      decoration: BoxDecoration(
                        gradient: isSelected
                            ? LinearGradient(
                                colors: [
                                  AppTheme.accentGold,
                                  AppTheme.accentGold.withValues(alpha: 0.8),
                                ],
                              )
                            : null,
                        color: isSelected
                            ? null
                            : AppTheme.primaryBlue.withValues(alpha: 0.5),
                        borderRadius: BorderRadius.circular(25),
                        border: Border.all(
                          color: isSelected
                              ? AppTheme.accentGold
                              : AppTheme.accentGold.withValues(alpha: 0.3),
                        ),
                      ),
                      child: Text(
                        filter['label']!,
                        style: TextStyle(
                          color: isSelected
                              ? AppTheme.primaryDark
                              : AppTheme.textGray,
                          fontWeight: isSelected
                              ? FontWeight.bold
                              : FontWeight.normal,
                          fontSize: 13,
                        ),
                      ),
                    ),
                  ),
                );
              },
            ),
          ),
          const SizedBox(height: 8),
          SizedBox(
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
                      _safeFetchOrders(
                        status: _selectedStatus,
                        period: _selectedPeriod == 'all'
                            ? null
                            : _selectedPeriod,
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
        ],
      ),
    );
  }

  Widget _buildContent() {
    return Consumer<OrdersProvider>(
      builder: (context, provider, child) {
        if (provider.isLoading && provider.orders.isEmpty) {
          return const Center(
            child: CircularProgressIndicator(
              valueColor: AlwaysStoppedAnimation<Color>(AppTheme.accentGold),
            ),
          );
        }

        if (provider.errorMessage != null) {
          return ErrorStateWidget(
            message: provider.errorMessage!,
            hint: AppLocalizations.of(context).errorHint,
            onRetry: () => provider.refreshOrders(),
          );
        }

        if (provider.orders.isEmpty) {
          final l10n = AppLocalizations.of(context);
          return EmptyStateWidget(
            icon: Icons.shopping_bag_outlined,
            title: _selectedStatus == null
                ? l10n.noOrder
                : l10n.noOrderForStatus(
                    _getStatusLabel(context, _selectedStatus!),
                  ),
            subtitle: l10n.noOrderSubtitle,
          );
        }

        return RefreshIndicator(
          color: AppTheme.accentGold,
          onRefresh: () async {
            try {
              await provider.refreshOrders();
            } catch (_) {}
          },
          child: NotificationListener<ScrollNotification>(
            onNotification: (ScrollNotification scrollInfo) {
              if (scrollInfo.metrics.pixels ==
                  scrollInfo.metrics.maxScrollExtent) {
                provider.loadMoreOrders();
              }
              return false;
            },
            child: Padding(
              padding: LayoutHelper.horizontalPadding(context),
              child: GridView.builder(
                padding: EdgeInsets.symmetric(vertical: 20),
                gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                  crossAxisCount: LayoutHelper.gridCrossAxisCount(context),
                  childAspectRatio: LayoutHelper.listCellAspectRatio(context),
                  crossAxisSpacing: LayoutHelper.gridSpacing(context),
                  mainAxisSpacing: LayoutHelper.gridSpacing(context),
                ),
                itemCount:
                    provider.orders.length + (provider.hasMorePages ? 1 : 0),
                itemBuilder: (context, index) {
                  if (index == provider.orders.length) {
                    return const Center(
                      child: CircularProgressIndicator(
                        valueColor: AlwaysStoppedAnimation<Color>(
                          AppTheme.accentGold,
                        ),
                      ),
                    );
                  }

                  final order = provider.orders[index];
                  try {
                    return OrderCard(
                      order: order,
                      onTap: () {
                        HapticHelper.lightImpact();
                        // Navigator racine pour éviter crash côté guest (Mes Commandes)
                        final rootNav = Navigator.maybeOf(
                          context,
                          rootNavigator: true,
                        );
                        rootNav?.push(
                          NavigationHelper.slideFadeRoute(
                            OrderDetailScreen(
                              orderId: order.id,
                              orderPreview: order,
                            ),
                          ),
                        );
                      },
                    );
                  } catch (_) {
                    return const SizedBox.shrink();
                  }
                },
              ),
            ),
          ),
        );
      },
    );
  }

  String _getStatusLabel(BuildContext context, String status) {
    final l10n = AppLocalizations.of(context);
    switch (status) {
      case 'pending':
        return l10n.orderStatusPending;
      case 'confirmed':
        return l10n.orderStatusConfirmed;
      case 'preparing':
        return l10n.orderStatusPreparing;
      case 'delivering':
        return l10n.orderStatusDelivering;
      case 'delivered':
        return l10n.orderStatusDelivered;
      default:
        return status;
    }
  }
}
