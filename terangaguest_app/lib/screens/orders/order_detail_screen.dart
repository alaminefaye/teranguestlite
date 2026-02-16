import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../config/theme.dart';
import '../../models/order.dart';
import '../../providers/orders_provider.dart';
import '../../providers/auth_provider.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/error_state.dart';
import '../../widgets/animated_button.dart';

class OrderDetailScreen extends StatefulWidget {
  final int orderId;

  const OrderDetailScreen({super.key, required this.orderId});

  @override
  State<OrderDetailScreen> createState() => _OrderDetailScreenState();
}

class _OrderDetailScreenState extends State<OrderDetailScreen>
    with TickerProviderStateMixin {
  Order? _order;
  bool _isLoading = true;
  String? _errorMessage;

  late AnimationController _entranceController;
  late AnimationController _pulseController;
  late Animation<double> _headerAnim;
  late Animation<double> _timelineAnim;
  late Animation<double> _itemsAnim;
  late Animation<double> _summaryAnim;
  late Animation<double> _buttonsAnim;

  @override
  void initState() {
    super.initState();
    _loadOrderDetail();

    _entranceController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1400),
    );
    _pulseController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1200),
    )..repeat(reverse: true);

    _headerAnim = CurvedAnimation(
      parent: _entranceController,
      curve: const Interval(0.0, 0.25, curve: Curves.easeOutCubic),
    );
    _timelineAnim = CurvedAnimation(
      parent: _entranceController,
      curve: const Interval(0.12, 0.5, curve: Curves.easeOutCubic),
    );
    _itemsAnim = CurvedAnimation(
      parent: _entranceController,
      curve: const Interval(0.35, 0.7, curve: Curves.easeOutCubic),
    );
    _summaryAnim = CurvedAnimation(
      parent: _entranceController,
      curve: const Interval(0.6, 0.88, curve: Curves.easeOutCubic),
    );
    _buttonsAnim = CurvedAnimation(
      parent: _entranceController,
      curve: const Interval(0.8, 1.0, curve: Curves.easeOutCubic),
    );
  }

  @override
  void dispose() {
    _entranceController.dispose();
    _pulseController.dispose();
    super.dispose();
  }

  void _startEntranceAnimations() {
    _entranceController.forward();
  }

  Future<void> _loadOrderDetail() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final order = await context.read<OrdersProvider>().fetchOrderDetail(
        widget.orderId,
      );
      setState(() {
        _order = order;
        _isLoading = false;
      });
      WidgetsBinding.instance.addPostFrameCallback(
        (_) => _startEntranceAnimations(),
      );
    } catch (e) {
      setState(() {
        _errorMessage = e.toString();
        _isLoading = false;
      });
    }
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

              // Contenu
              Expanded(child: _buildContent()),
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
            onPressed: () => Navigator.pop(context),
          ),
          const SizedBox(width: 12),

          // Titre
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  AppLocalizations.of(context).orderDetailTitle,
                  style: const TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.bold,
                    color: Colors.white,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  AppLocalizations.of(context).orderTrackingSubtitle,
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

  Widget _buildContent() {
    if (_isLoading) {
      return const Center(
        child: CircularProgressIndicator(
          valueColor: AlwaysStoppedAnimation<Color>(AppTheme.accentGold),
        ),
      );
    }

    if (_errorMessage != null) {
      return ErrorStateWidget(
        message: _errorMessage!,
        hint: AppLocalizations.of(context).errorHint,
        onRetry: _loadOrderDetail,
      );
    }

    if (_order == null) {
      final l10n = AppLocalizations.of(context);
      return EmptyStateWidget(
        icon: Icons.receipt_long_outlined,
        title: l10n.orderNotFound,
        subtitle: l10n.orderNotFoundHint,
      );
    }

    return SingleChildScrollView(
      padding: const EdgeInsets.symmetric(horizontal: 60, vertical: 20),
      child: AnimatedBuilder(
        animation: _entranceController,
        builder: (context, child) {
          return Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              FadeTransition(
                opacity: _headerAnim,
                child: SlideTransition(
                  position: Tween<Offset>(
                    begin: const Offset(0, 0.15),
                    end: Offset.zero,
                  ).animate(_headerAnim),
                  child: ScaleTransition(
                    scale: Tween<double>(begin: 0.96, end: 1.0).animate(
                      CurvedAnimation(
                        parent: _entranceController,
                        curve: const Interval(
                          0.0,
                          0.25,
                          curve: Curves.easeOutCubic,
                        ),
                      ),
                    ),
                    child: _buildOrderHeader(),
                  ),
                ),
              ),
              const SizedBox(height: 30),
              FadeTransition(
                opacity: _timelineAnim,
                child: SlideTransition(
                  position: Tween<Offset>(
                    begin: const Offset(0, 0.12),
                    end: Offset.zero,
                  ).animate(_timelineAnim),
                  child: _buildTimeline(),
                ),
              ),
              const SizedBox(height: 30),
              FadeTransition(
                opacity: _itemsAnim,
                child: SlideTransition(
                  position: Tween<Offset>(
                    begin: const Offset(0, 0.1),
                    end: Offset.zero,
                  ).animate(_itemsAnim),
                  child: _buildOrderItems(),
                ),
              ),
              const SizedBox(height: 30),
              FadeTransition(
                opacity: _summaryAnim,
                child: SlideTransition(
                  position: Tween<Offset>(
                    begin: const Offset(0, 0.08),
                    end: Offset.zero,
                  ).animate(_summaryAnim),
                  child: _buildSummary(),
                ),
              ),
              const SizedBox(height: 30),
              FadeTransition(
                opacity: _buttonsAnim,
                child: SlideTransition(
                  position: Tween<Offset>(
                    begin: const Offset(0, 0.06),
                    end: Offset.zero,
                  ).animate(_buttonsAnim),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      _buildStaffActions(),
                      if (_order!.status == 'delivered') ...[
                        const SizedBox(height: 16),
                        _buildReorderButton(),
                      ],
                    ],
                  ),
                ),
              ),
            ],
          );
        },
      ),
    );
  }

  Widget _buildOrderHeader() {
    final auth = context.read<AuthProvider>();
    final isStaffOrAdmin = auth.isAdmin || auth.isStaff;
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [AppTheme.primaryBlue, AppTheme.primaryDark],
        ),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppTheme.accentGold, width: 1.5),
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                _order!.orderNumber,
                style: const TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.bold,
                  color: AppTheme.accentGold,
                ),
              ),
              const SizedBox(height: 8),
              Text(
                DateFormat(
                  'dd/MM/yyyy à HH:mm',
                  'fr_FR',
                ).format(_order!.createdAt),
                style: const TextStyle(fontSize: 13, color: AppTheme.textGray),
              ),
            ],
          ),
          Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              _buildStatusBadge(_order!.status),
              if (_order!.canCancel && !isStaffOrAdmin) ...[
                const SizedBox(width: 12),
                _buildCancelButtonInline(),
              ],
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildTimeline() {
    final l10n = AppLocalizations.of(context);
    final statuses = [
      {
        'key': 'pending',
        'label': l10n.statusPending,
        'icon': Icons.access_time,
      },
      {
        'key': 'confirmed',
        'label': l10n.statusConfirmed,
        'icon': Icons.check_circle,
      },
      {
        'key': 'preparing',
        'label': l10n.statusPreparing,
        'icon': Icons.restaurant,
      },
      {
        'key': 'ready',
        'label': l10n.statusReady,
        'icon': Icons.breakfast_dining,
      },
      {
        'key': 'delivering',
        'label': l10n.statusDelivering,
        'icon': Icons.delivery_dining,
      },
      {
        'key': 'delivered',
        'label': l10n.statusDelivered,
        'icon': Icons.done_all,
      },
    ];

    int currentIndex = statuses.indexWhere((s) => s['key'] == _order!.status);
    if (currentIndex < 0) currentIndex = 0;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          l10n.orderTracking,
          style: const TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.bold,
            color: Colors.white,
          ),
        ),
        const SizedBox(height: 20),
        ...List.generate(statuses.length, (index) {
          final status = statuses[index];
          final isCompleted = index <= currentIndex;
          final isCurrent = index == currentIndex;
          final isLast = index == statuses.length - 1;
          final stepAnim = CurvedAnimation(
            parent: _entranceController,
            curve: Interval(
              0.2 + index * 0.08,
              0.2 + index * 0.08 + 0.18,
              curve: Curves.easeOutCubic,
            ),
          );

          return FadeTransition(
            opacity: stepAnim,
            child: SlideTransition(
              position: Tween<Offset>(
                begin: const Offset(-0.03, 0.06),
                end: Offset.zero,
              ).animate(stepAnim),
              child: Column(
                children: [
                  Row(
                    children: [
                      AnimatedBuilder(
                        animation: _pulseController,
                        builder: (context, child) {
                          final scale = isCurrent
                              ? 1.0 + 0.06 * _pulseController.value
                              : 1.0;
                          return Transform.scale(scale: scale, child: child);
                        },
                        child: AnimatedContainer(
                          duration: const Duration(milliseconds: 400),
                          curve: Curves.easeOut,
                          width: 40,
                          height: 40,
                          decoration: BoxDecoration(
                            color: isCompleted
                                ? AppTheme.accentGold
                                : AppTheme.primaryBlue,
                            shape: BoxShape.circle,
                            border: Border.all(
                              color: AppTheme.accentGold,
                              width: isCurrent ? 2.5 : 2,
                            ),
                            boxShadow: isCompleted
                                ? [
                                    BoxShadow(
                                      color: AppTheme.accentGold.withValues(
                                        alpha: 0.4,
                                      ),
                                      blurRadius: isCurrent ? 12 : 6,
                                      spreadRadius: isCurrent ? 1 : 0,
                                    ),
                                  ]
                                : null,
                          ),
                          child: Icon(
                            status['icon'] as IconData,
                            color: isCompleted
                                ? AppTheme.primaryDark
                                : AppTheme.textGray,
                            size: 20,
                          ),
                        ),
                      ),
                      const SizedBox(width: 16),
                      Expanded(
                        child: AnimatedDefaultTextStyle(
                          duration: const Duration(milliseconds: 300),
                          style: TextStyle(
                            fontSize: 15,
                            fontWeight: isCompleted
                                ? FontWeight.bold
                                : FontWeight.normal,
                            color: isCompleted
                                ? Colors.white
                                : AppTheme.textGray,
                          ),
                          child: Text(status['label'] as String),
                        ),
                      ),
                    ],
                  ),
                  if (!isLast)
                    AnimatedContainer(
                      duration: const Duration(milliseconds: 450),
                      curve: Curves.easeOutCubic,
                      margin: const EdgeInsets.only(left: 19),
                      width: 2,
                      height: 30,
                      decoration: BoxDecoration(
                        color: isCompleted
                            ? AppTheme.accentGold
                            : AppTheme.textGray.withValues(alpha: 0.3),
                        borderRadius: BorderRadius.circular(1),
                      ),
                    ),
                ],
              ),
            ),
          );
        }),
      ],
    );
  }

  Widget _buildOrderItems() {
    final items = _order!.items ?? [];
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          AppLocalizations.of(context).orderItems,
          style: const TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.bold,
            color: Colors.white,
          ),
        ),
        const SizedBox(height: 16),
        ...List.generate(items.length, (index) {
          final item = items[index];
          final itemAnim = CurvedAnimation(
            parent: _entranceController,
            curve: Interval(
              0.35 + index * 0.06,
              0.35 + index * 0.06 + 0.2,
              curve: Curves.easeOutCubic,
            ),
          );
          return FadeTransition(
            opacity: itemAnim,
            child: SlideTransition(
              position: Tween<Offset>(
                begin: const Offset(0.02, 0.04),
                end: Offset.zero,
              ).animate(itemAnim),
              child: Container(
                margin: const EdgeInsets.only(bottom: 12),
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: [
                      AppTheme.primaryBlue.withValues(alpha: 0.6),
                      AppTheme.primaryDark.withValues(alpha: 0.8),
                    ],
                  ),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(
                    color: AppTheme.accentGold.withValues(alpha: 0.3),
                  ),
                ),
                child: Row(
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            item.name,
                            style: const TextStyle(
                              fontSize: 15,
                              fontWeight: FontWeight.bold,
                              color: Colors.white,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            '${AppLocalizations.of(context).quantity} ${item.quantity}',
                            style: const TextStyle(
                              fontSize: 13,
                              color: AppTheme.textGray,
                            ),
                          ),
                        ],
                      ),
                    ),
                    Text(
                      item.formattedSubtotal,
                      style: const TextStyle(
                        fontSize: 15,
                        fontWeight: FontWeight.bold,
                        color: AppTheme.accentGold,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          );
        }),
      ],
    );
  }

  Widget _buildSummary() {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [AppTheme.primaryBlue, AppTheme.primaryDark],
        ),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppTheme.accentGold, width: 1.5),
      ),
      child: Column(
        children: [
          if (_order!.instructions != null &&
              _order!.instructions!.isNotEmpty) ...[
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Icon(
                  Icons.info_outline,
                  color: AppTheme.accentGold,
                  size: 20,
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        AppLocalizations.of(context).specialInstructions,
                        style: const TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.bold,
                          color: AppTheme.textGray,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        _order!.instructions!,
                        style: const TextStyle(
                          fontSize: 14,
                          color: Colors.white,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const Divider(height: 30, color: AppTheme.textGray),
          ],
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                AppLocalizations.of(context).total,
                style: const TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: Colors.white,
                ),
              ),
              Text(
                _order!.formattedTotal,
                style: const TextStyle(
                  fontSize: 22,
                  fontWeight: FontWeight.w900,
                  color: AppTheme.accentGold,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  /// Bouton annuler à côté du statut (dans l'en-tête).
  Widget _buildCancelButtonInline() {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: _handleCancelOrder,
        borderRadius: BorderRadius.circular(10),
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
          decoration: BoxDecoration(
            border: Border.all(color: Colors.red.withValues(alpha: 0.8)),
            borderRadius: BorderRadius.circular(10),
          ),
          child: const Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(Icons.cancel_outlined, size: 18, color: Colors.red),
              SizedBox(width: 6),
              Text(
                'Annuler',
                style: TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.w600,
                  color: Colors.red,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildReorderButton() {
    return AnimatedButton(
      text: AppLocalizations.of(context).reorder,
      onPressed: _handleReorder,
      width: double.infinity,
      height: 56,
      backgroundColor: AppTheme.accentGold,
      textColor: AppTheme.primaryDark,
    );
  }

  Widget _buildStatusBadge(String status) {
    final statusColors = _getStatusColor(status);

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: statusColors['bg'],
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: statusColors['border']!, width: 1),
      ),
      child: Text(
        _order!.statusLabel,
        style: TextStyle(
          fontSize: 12,
          fontWeight: FontWeight.bold,
          color: statusColors['text'],
        ),
      ),
    );
  }

  Widget _buildStaffActions() {
    final auth = context.read<AuthProvider>();
    final isStaffOrAdmin = auth.isAdmin || auth.isStaff;
    if (!isStaffOrAdmin || _order == null) {
      return const SizedBox.shrink();
    }

    final status = _order!.status;
    final actions = <Map<String, String>>[];

    if (status == 'pending') {
      actions.add({'action': 'confirm', 'label': 'Confirmer la commande'});
    } else if (status == 'confirmed') {
      actions.add({'action': 'prepare', 'label': 'Lancer la préparation'});
    } else if (status == 'preparing') {
      actions.add({'action': 'mark_ready', 'label': 'Marquer comme prête'});
    } else if (status == 'ready') {
      actions.add({'action': 'deliver', 'label': 'Mettre en livraison'});
    } else if (status == 'delivering') {
      actions.add({'action': 'complete', 'label': 'Marquer comme livrée'});
    }

    if (actions.isEmpty) {
      return const SizedBox.shrink();
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: actions.map((a) {
        return Padding(
          padding: const EdgeInsets.only(bottom: 12),
          child: AnimatedButton(
            text: a['label']!,
            onPressed: () => _handleStaffAction(a['action']!),
            width: double.infinity,
            height: 52,
            backgroundColor: AppTheme.accentGold,
            textColor: AppTheme.primaryDark,
          ),
        );
      }).toList(),
    );
  }

  Future<void> _handleStaffAction(String action) async {
    if (_order == null) return;

    try {
      showDialog(
        context: context,
        barrierDismissible: false,
        builder: (ctx) => const Center(
          child: CircularProgressIndicator(
            valueColor: AlwaysStoppedAnimation<Color>(AppTheme.accentGold),
          ),
        ),
      );

      await context.read<OrdersProvider>().updateOrderStatus(
        orderId: _order!.id,
        action: action,
      );

      await _loadOrderDetail();

      if (!mounted) return;
      Navigator.pop(context);

      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: const Text('Statut de la commande mis à jour'),
          backgroundColor: Colors.green,
        ),
      );
    } catch (e) {
      if (!mounted) return;
      Navigator.pop(context);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('${AppLocalizations.of(context).errorPrefix}$e'),
          backgroundColor: Colors.red,
        ),
      );
    }
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
          'bg': Colors.blue.withValues(alpha: 0.2),
          'border': Colors.blue,
          'text': Colors.blue,
        };
      case 'preparing':
        return {
          'bg': Colors.purple.withValues(alpha: 0.2),
          'border': Colors.purple,
          'text': Colors.purple,
        };
      case 'ready':
        return {
          'bg': AppTheme.accentGold.withValues(alpha: 0.2),
          'border': AppTheme.accentGold,
          'text': AppTheme.accentGold,
        };
      case 'delivering':
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

  Future<void> _handleCancelOrder() async {
    final l10n = AppLocalizations.of(context);
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Annuler la commande'),
        content: const Text('Voulez-vous vraiment annuler cette commande ?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: Text(l10n.cancel),
          ),
          TextButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: TextButton.styleFrom(foregroundColor: Colors.red),
            child: const Text('Oui, annuler'),
          ),
        ],
      ),
    );
    if (confirmed != true || !mounted) return;

    try {
      await context.read<OrdersProvider>().cancelOrder(widget.orderId);
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Commande annulée'),
          backgroundColor: Colors.green,
        ),
      );
      Navigator.pop(context);
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

  Future<void> _handleReorder() async {
    try {
      // Afficher loader
      showDialog(
        context: context,
        barrierDismissible: false,
        builder: (context) => const Center(
          child: CircularProgressIndicator(
            valueColor: AlwaysStoppedAnimation<Color>(AppTheme.accentGold),
          ),
        ),
      );

      // Appeler l'API reorder
      await context.read<OrdersProvider>().reorderOrder(widget.orderId);

      // Fermer le loader
      if (mounted) Navigator.pop(context);

      // Afficher succès
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(AppLocalizations.of(context).itemsAddedToCart),
            backgroundColor: Colors.green,
            duration: const Duration(seconds: 2),
          ),
        );

        // Retourner au dashboard ou au panier
        Navigator.pop(context);
      }
    } catch (e) {
      // Fermer le loader
      if (mounted) Navigator.pop(context);

      // Afficher erreur
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('${AppLocalizations.of(context).errorPrefix}$e'),
            backgroundColor: Colors.red,
            duration: const Duration(seconds: 3),
          ),
        );
      }
    }
  }
}
