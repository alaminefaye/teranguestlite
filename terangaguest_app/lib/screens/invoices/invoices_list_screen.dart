import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../models/order.dart';
import '../../providers/orders_provider.dart';
import '../../providers/tablet_session_provider.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/error_state.dart';
import 'invoice_receipt_dialog.dart';

class InvoicesListScreen extends StatefulWidget {
  const InvoicesListScreen({super.key});

  @override
  State<InvoicesListScreen> createState() => _InvoicesListScreenState();
}

class _InvoicesListScreenState extends State<InvoicesListScreen> {
  final ScrollController _scrollController = ScrollController();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!mounted) return;
      // Injecter le code client du guest connecté (tablette)
      final clientCode = context
          .read<TabletSessionProvider>()
          .clientCodeForPreFill;
      context.read<OrdersProvider>().setClientCode(clientCode);
      context.read<OrdersProvider>().fetchOrders();
    });
    _scrollController.addListener(_onScroll);
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  void _onScroll() {
    if (_scrollController.position.pixels >=
        _scrollController.position.maxScrollExtent - 200) {
      context.read<OrdersProvider>().loadMoreOrders();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.primaryDark,
      body: SafeArea(
        child: Column(
          children: [
            _buildHeader(context),
            Expanded(
              child: Consumer<OrdersProvider>(
                builder: (context, provider, child) {
                  if (provider.isLoading && provider.orders.isEmpty) {
                    return const Center(
                      child: CircularProgressIndicator(
                        valueColor: AlwaysStoppedAnimation<Color>(
                          AppTheme.accentGold,
                        ),
                      ),
                    );
                  }

                  if (provider.errorMessage != null &&
                      provider.orders.isEmpty) {
                    return ErrorStateWidget(
                      message: provider.errorMessage!,
                      hint: AppLocalizations.of(context).errorHint,
                      onRetry: () => provider.fetchOrders(),
                    );
                  }

                  // Ne garder que les commandes livrées = "factures"
                  final deliveredOrders = provider.orders
                      .where((o) => o.status == 'delivered')
                      .toList();

                  if (deliveredOrders.isEmpty) {
                    return EmptyStateWidget(
                      icon: Icons.receipt_long,
                      title: AppLocalizations.of(context).noInvoicesTitle,
                      subtitle: AppLocalizations.of(context).noInvoicesSubtitle,
                    );
                  }

                  return RefreshIndicator(
                    color: AppTheme.accentGold,
                    backgroundColor: AppTheme.primaryDark,
                    onRefresh: () => provider.fetchOrders(),
                    child: ListView.separated(
                      controller: _scrollController,
                      padding: const EdgeInsets.all(20),
                      itemCount:
                          deliveredOrders.length +
                          (provider.hasMorePages ? 1 : 0),
                      separatorBuilder: (context, index) =>
                          const SizedBox(height: 16),
                      itemBuilder: (context, index) {
                        if (index == deliveredOrders.length) {
                          return const Center(
                            child: Padding(
                              padding: EdgeInsets.all(16.0),
                              child: CircularProgressIndicator(
                                valueColor: AlwaysStoppedAnimation<Color>(
                                  AppTheme.accentGold,
                                ),
                              ),
                            ),
                          );
                        }
                        final order = deliveredOrders[index];
                        return _buildInvoiceCard(context, order);
                      },
                    ),
                  );
                },
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildHeader(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.all(20.0),
      child: Row(
        children: [
          IconButton(
            icon: const Icon(Icons.arrow_back, color: AppTheme.accentGold),
            onPressed: () => Navigator.pop(context),
          ),
          const SizedBox(width: 12),
          Text(
            AppLocalizations.of(context).myInvoices,
            style: const TextStyle(
              fontSize: 24,
              fontWeight: FontWeight.bold,
              color: AppTheme.accentGold,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildInvoiceCard(BuildContext context, Order order) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: () {
          showDialog(
            context: context,
            barrierDismissible: true,
            builder: (ctx) => InvoiceReceiptDialog(
              orderId: order.id,
              orderNumber: order.orderNumber,
            ),
          );
        },
        borderRadius: BorderRadius.circular(16),
        child: Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: AppTheme.primaryBlue.withValues(alpha: 0.5),
            borderRadius: BorderRadius.circular(16),
            border: Border.all(
              color: AppTheme.accentGold.withValues(alpha: 0.3),
            ),
          ),
          child: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: AppTheme.accentGold.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(Icons.receipt, color: AppTheme.accentGold),
              ),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      order.orderNumber,
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      order.formattedTotal,
                      style: const TextStyle(
                        color: AppTheme.accentGold,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ],
                ),
              ),
              const Icon(
                Icons.arrow_forward_ios,
                size: 16,
                color: AppTheme.textGray,
              ),
            ],
          ),
        ),
      ),
    );
  }
}
