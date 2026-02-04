import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../providers/orders_provider.dart';
import '../../widgets/order_card.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/error_state.dart';
import '../../utils/navigation_helper.dart';
import '../../utils/haptic_helper.dart';
import 'order_detail_screen.dart';

class OrdersListScreen extends StatefulWidget {
  const OrdersListScreen({super.key});

  @override
  State<OrdersListScreen> createState() => _OrdersListScreenState();
}

class _OrdersListScreenState extends State<OrdersListScreen> {
  String? _selectedStatus;

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

  @override
  void initState() {
    super.initState();
    // Charger les commandes au démarrage
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<OrdersProvider>().fetchOrders();
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
              // Header
              _buildHeader(),

              // Filtres
              _buildFilters(),

              // Liste des commandes
              Expanded(
                child: _buildContent(),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildHeader() {
    return Padding(
      padding: const EdgeInsets.all(20.0),
      child: Row(
        children: [
          // Bouton retour
          IconButton(
            icon: const Icon(Icons.arrow_back, color: AppTheme.accentGold),
            onPressed: () {
              HapticHelper.lightImpact();
              Navigator.pop(context);
            },
          ),
          const SizedBox(width: 12),

          // Titre
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  AppLocalizations.of(context).myOrders,
                  style: const TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.bold,
                    color: Colors.white,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  AppLocalizations.of(context).ordersHistorySubtitle,
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
    return Container(
      height: 50,
      margin: const EdgeInsets.symmetric(horizontal: 20),
      child: ListView.builder(
        scrollDirection: Axis.horizontal,
        itemCount: _statusFilters(context).length,
        itemBuilder: (context, index) {
          final filter = _statusFilters(context)[index];
          final isSelected = _selectedStatus == filter['value'] ||
              (_selectedStatus == null && filter['value'] == '');

          return Padding(
            padding: const EdgeInsets.only(right: 10),
            child: GestureDetector(
              onTap: () {
                setState(() {
                  _selectedStatus = filter['value']!.isEmpty ? null : filter['value'];
                });
                context.read<OrdersProvider>().fetchOrders(
                      status: _selectedStatus,
                    );
              },
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                decoration: BoxDecoration(
                  gradient: isSelected
                      ? LinearGradient(
                          colors: [AppTheme.accentGold, AppTheme.accentGold.withValues(alpha: 0.8)],
                        )
                      : null,
                  color: isSelected ? null : AppTheme.primaryBlue.withValues(alpha: 0.5),
                  borderRadius: BorderRadius.circular(25),
                  border: Border.all(
                    color: isSelected ? AppTheme.accentGold : AppTheme.accentGold.withValues(alpha: 0.3),
                  ),
                ),
                child: Text(
                  filter['label']!,
                  style: TextStyle(
                    color: isSelected ? AppTheme.primaryDark : AppTheme.textGray,
                    fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                    fontSize: 13,
                  ),
                ),
              ),
            ),
          );
        },
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
                : l10n.noOrderForStatus(_getStatusLabel(context, _selectedStatus!)),
            subtitle: l10n.noOrderSubtitle,
          );
        }

        return RefreshIndicator(
          color: AppTheme.accentGold,
          onRefresh: provider.refreshOrders,
          child: NotificationListener<ScrollNotification>(
            onNotification: (ScrollNotification scrollInfo) {
              if (scrollInfo.metrics.pixels == scrollInfo.metrics.maxScrollExtent) {
                provider.loadMoreOrders();
              }
              return false;
            },
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 60.0),
              child: GridView.builder(
                padding: const EdgeInsets.symmetric(vertical: 20),
                gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                  crossAxisCount: 4, // 4 colonnes comme les autres modules
                  childAspectRatio: 0.9,
                  crossAxisSpacing: 20,
                  mainAxisSpacing: 20,
                ),
                itemCount: provider.orders.length + (provider.hasMorePages ? 1 : 0),
                itemBuilder: (context, index) {
                  if (index == provider.orders.length) {
                    return const Center(
                      child: CircularProgressIndicator(
                        valueColor: AlwaysStoppedAnimation<Color>(AppTheme.accentGold),
                      ),
                    );
                  }

                  final order = provider.orders[index];
                  return OrderCard(
                    order: order,
                    onTap: () {
                      HapticHelper.lightImpact();
                      context.navigateTo(OrderDetailScreen(orderId: order.id));
                    },
                  );
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
