import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import 'package:cached_network_image/cached_network_image.dart';

import '../../config/theme.dart';
import '../../config/api_config.dart';
import '../../models/order.dart';
import '../../providers/auth_provider.dart';
import '../../providers/orders_provider.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../widgets/animated_button.dart';

class InvoiceReceiptDialog extends StatefulWidget {
  final int orderId;
  final String orderNumber;

  const InvoiceReceiptDialog({
    super.key,
    required this.orderId,
    required this.orderNumber,
  });

  @override
  State<InvoiceReceiptDialog> createState() => _InvoiceReceiptDialogState();
}

class _InvoiceReceiptDialogState extends State<InvoiceReceiptDialog>
    with SingleTickerProviderStateMixin {
  Order? _order;
  bool _isLoading = true;
  String? _errorMessage;
  late AnimationController _animController;
  late Animation<double> _scaleAnimation;

  @override
  void initState() {
    super.initState();
    _animController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 400),
    );
    _scaleAnimation = CurvedAnimation(
      parent: _animController,
      curve: Curves.easeOutBack,
    );

    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadOrder();
    });
  }

  @override
  void dispose() {
    _animController.dispose();
    super.dispose();
  }

  Future<void> _loadOrder() async {
    try {
      final order = await context.read<OrdersProvider>().fetchOrderDetail(
        widget.orderId,
      );
      if (!mounted) return;
      setState(() {
        _order = order;
        _isLoading = false;
      });
      _animController.forward();
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _errorMessage = AppLocalizations.of(context).errorHint;
        _isLoading = false;
      });
      _animController.forward();
    }
  }

  @override
  Widget build(BuildContext context) {
    final w = MediaQuery.sizeOf(context).width;
    final isMobile = w < 600;

    return Center(
      child: Material(
        color: Colors.transparent,
        child: Container(
          width: isMobile ? w * 0.9 : 500,
          constraints: BoxConstraints(
            maxHeight: MediaQuery.sizeOf(context).height * 0.85,
          ),
          decoration: BoxDecoration(
            color: Colors.white, // Style ticket de caisse
            borderRadius: BorderRadius.circular(16),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.3),
                blurRadius: 20,
                spreadRadius: 5,
              ),
            ],
          ),
          child: _isLoading
              ? const Padding(
                  padding: EdgeInsets.all(40.0),
                  child: Center(
                    child: CircularProgressIndicator(
                      valueColor: AlwaysStoppedAnimation<Color>(
                        AppTheme.primaryDark,
                      ),
                    ),
                  ),
                )
              : _errorMessage != null
              ? _buildErrorState()
              : ScaleTransition(
                  scale: _scaleAnimation,
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      _buildEnterpriseBlock(),
                      _buildHeader(),
                      Flexible(
                        child: SingleChildScrollView(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 20,
                            vertical: 10,
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.stretch,
                            children: [
                              _buildOrderInfo(),
                              const SizedBox(height: 16),
                              _buildDashedDivider(),
                              const SizedBox(height: 16),
                              _buildItemsList(),
                              const SizedBox(height: 16),
                              _buildDashedDivider(),
                              const SizedBox(height: 16),
                              _buildTotals(),
                            ],
                          ),
                        ),
                      ),
                      _buildFooter(context),
                    ],
                  ),
                ),
        ),
      ),
    );
  }

  Widget _buildErrorState() {
    return Padding(
      padding: const EdgeInsets.all(30.0),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Icon(Icons.error_outline, color: Colors.red, size: 48),
          const SizedBox(height: 16),
          Text(
            _errorMessage ?? "Erreur",
            style: const TextStyle(color: Colors.black87),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 20),
          ElevatedButton(
            onPressed: () => Navigator.pop(context),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppTheme.primaryDark,
            ),
            child: const Text('Fermer', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
  }

  /// Bloc en-tête du reçu : logo de l'entreprise + nom, adresse, téléphone, email.
  Widget _buildEnterpriseBlock() {
    final enterprise = context.read<AuthProvider>().user?.enterprise;
    final name = enterprise?.name.trim() ?? '';
    final logoPath = enterprise?.logo;
    final logoUrl = () {
      if (logoPath == null || logoPath.trim().isEmpty) return null;
      final trimmed = logoPath.trim();
      if (trimmed.startsWith('http')) return trimmed;
      return ApiConfig.storageUrl(trimmed);
    }();
    final address = enterprise?.address?.trim();
    final phone = enterprise?.phone?.trim();
    final email = enterprise?.email?.trim();
    final hasAnyContact = (address != null && address.isNotEmpty) ||
        (phone != null && phone.isNotEmpty) ||
        (email != null && email.isNotEmpty);

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.fromLTRB(20, 20, 20, 16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: const BorderRadius.vertical(top: Radius.circular(16)),
      ),
      child: Column(
        children: [
          if (logoUrl != null && logoUrl.isNotEmpty)
            ClipRRect(
              borderRadius: BorderRadius.circular(8),
              child: CachedNetworkImage(
                imageUrl: logoUrl,
                height: 56,
                fit: BoxFit.contain,
                placeholder: (_, __) => SizedBox(
                  height: 56,
                  child: Center(
                    child: SizedBox(
                      width: 24,
                      height: 24,
                      child: CircularProgressIndicator(
                        strokeWidth: 2,
                        valueColor: AlwaysStoppedAnimation<Color>(
                          AppTheme.primaryDark.withValues(alpha: 0.6),
                        ),
                      ),
                    ),
                  ),
                ),
                errorWidget: (_, __, ___) => const SizedBox(height: 56),
              ),
            ),
          if (logoUrl != null && logoUrl.isNotEmpty) const SizedBox(height: 12),
          if (name.isNotEmpty)
            Text(
              name,
              style: const TextStyle(
                color: Colors.black87,
                fontSize: 16,
                fontWeight: FontWeight.bold,
              ),
              textAlign: TextAlign.center,
            ),
          if (hasAnyContact) ...[
            if (name.isNotEmpty) const SizedBox(height: 8),
            if (address != null && address.isNotEmpty)
              Text(
                address,
                style: const TextStyle(
                  color: Colors.black54,
                  fontSize: 12,
                ),
                textAlign: TextAlign.center,
              ),
            if (phone != null && phone.isNotEmpty) ...[
              const SizedBox(height: 4),
              Text(
                phone,
                style: const TextStyle(
                  color: Colors.black54,
                  fontSize: 12,
                ),
                textAlign: TextAlign.center,
              ),
            ],
            if (email != null && email.isNotEmpty) ...[
              const SizedBox(height: 4),
              Text(
                email,
                style: const TextStyle(
                  color: Colors.black54,
                  fontSize: 12,
                ),
                textAlign: TextAlign.center,
              ),
            ],
          ],
        ],
      ),
    );
  }

  Widget _buildHeader() {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: const BoxDecoration(
        color: AppTheme.primaryDark,
        borderRadius: BorderRadius.vertical(top: Radius.circular(0)),
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'REÇU DE COMMANDE',
                  style: TextStyle(
                    color: AppTheme.accentGold,
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                    letterSpacing: 1.2,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  widget.orderNumber,
                  style: const TextStyle(color: Colors.white70, fontSize: 14),
                ),
              ],
            ),
          ),
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.1),
              shape: BoxShape.circle,
            ),
            child: const Icon(
              Icons.check_circle,
              color: AppTheme.accentGold,
              size: 32,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildOrderInfo() {
    final dateFormat = DateFormat('dd/MM/yyyy • HH:mm', 'fr_FR');
    final formattedDate = dateFormat.format(_order!.createdAt);

    // Utiliser delivered_at réel si disponible, sinon l'heure actuelle (cas popup notification)
    final deliveredTime = _order!.deliveredAt ?? DateTime.now();
    final deliveredTimeStr = dateFormat.format(deliveredTime);

    // Nom de l'hôtel depuis le profil de l'utilisateur connecté
    final authProvider = context.read<AuthProvider>();
    final hotelName = authProvider.user?.enterprise?.name ?? 'Hôtel';

    return Column(
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Text(
              'HÔTEL:',
              style: TextStyle(
                color: Colors.black54,
                fontSize: 12,
                fontWeight: FontWeight.w600,
              ),
            ),
            Flexible(
              child: Text(
                hotelName,
                style: const TextStyle(
                  color: Colors.black87,
                  fontSize: 14,
                  fontWeight: FontWeight.bold,
                ),
                textAlign: TextAlign.end,
                overflow: TextOverflow.ellipsis,
              ),
            ),
          ],
        ),
        const SizedBox(height: 8),
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Text(
              'CHAMBRE:',
              style: TextStyle(
                color: Colors.black54,
                fontSize: 12,
                fontWeight: FontWeight.w600,
              ),
            ),
            Text(
              _order?.roomNumber ?? '--',
              style: const TextStyle(
                color: Colors.black87,
                fontSize: 14,
                fontWeight: FontWeight.bold,
              ),
            ),
          ],
        ),
        const SizedBox(height: 8),
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Text(
              'DATE COMMANDE:',
              style: TextStyle(
                color: Colors.black54,
                fontSize: 12,
                fontWeight: FontWeight.w600,
              ),
            ),
            Text(
              formattedDate,
              style: const TextStyle(
                color: Colors.black87,
                fontSize: 13,
                fontWeight: FontWeight.w500,
              ),
            ),
          ],
        ),
        const SizedBox(height: 8),
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Text(
              'LIVRAISON:',
              style: TextStyle(
                color: Colors.black54,
                fontSize: 12,
                fontWeight: FontWeight.w600,
              ),
            ),
            Text(
              deliveredTimeStr,
              style: const TextStyle(
                color: Colors.black87,
                fontSize: 13,
                fontWeight: FontWeight.w500,
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildItemsList() {
    final items = _order?.items ?? [];
    if (items.isEmpty) {
      return const Center(
        child: Text('Aucun article', style: TextStyle(color: Colors.black54)),
      );
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        const Text(
          'ARTICLES',
          style: TextStyle(
            color: Colors.black54,
            fontSize: 12,
            fontWeight: FontWeight.w600,
            letterSpacing: 1,
          ),
        ),
        const SizedBox(height: 12),
        ...items.map((item) {
          return Padding(
            padding: const EdgeInsets.only(bottom: 12),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  '${item.quantity}x',
                  style: const TextStyle(
                    color: Colors.black87,
                    fontWeight: FontWeight.bold,
                    fontSize: 14,
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        item.name,
                        style: const TextStyle(
                          color: Colors.black87,
                          fontWeight: FontWeight.w600,
                          fontSize: 14,
                        ),
                      ),
                      Text(
                        '@ ${item.formattedPrice}',
                        style: const TextStyle(
                          color: Colors.black54,
                          fontSize: 12,
                        ),
                      ),
                    ],
                  ),
                ),
                Text(
                  item.formattedSubtotal,
                  style: const TextStyle(
                    color: Colors.black87,
                    fontWeight: FontWeight.bold,
                    fontSize: 14,
                  ),
                ),
              ],
            ),
          );
        }),
      ],
    );
  }

  Widget _buildTotals() {
    return Column(
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Text(
              'TOTAL À PAYER',
              style: TextStyle(
                color: AppTheme.primaryDark,
                fontSize: 16,
                fontWeight: FontWeight.bold,
              ),
            ),
            Text(
              _order?.formattedTotal ?? '0 FCFA',
              style: const TextStyle(
                color: AppTheme.primaryDark,
                fontSize: 20,
                fontWeight: FontWeight.w900,
              ),
            ),
          ],
        ),
        const SizedBox(height: 8),
        const Text(
          'Toute taxe et frais de service inclus',
          style: TextStyle(
            color: Colors.black45,
            fontSize: 11,
            fontStyle: FontStyle.italic,
          ),
        ),
      ],
    );
  }

  Widget _buildDashedDivider() {
    return LayoutBuilder(
      builder: (context, constraints) {
        final boxWidth = constraints.constrainWidth();
        const dashWidth = 5.0;
        const dashHeight = 1.5;
        final dashCount = (boxWidth / (2 * dashWidth)).floor();
        return Flex(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          direction: Axis.horizontal,
          children: List.generate(dashCount, (_) {
            return SizedBox(
              width: dashWidth,
              height: dashHeight,
              child: const DecoratedBox(
                decoration: BoxDecoration(color: Colors.black26),
              ),
            );
          }),
        );
      },
    );
  }

  Widget _buildFooter(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.grey.shade50,
        borderRadius: const BorderRadius.vertical(bottom: Radius.circular(16)),
      ),
      child: Column(
        children: [
          const Text(
            'Merci pour votre commande !',
            style: TextStyle(
              color: Colors.black87,
              fontWeight: FontWeight.w600,
            ),
          ),
          const SizedBox(height: 16),
          AnimatedButton(
            text: AppLocalizations.of(context).close,
            onPressed: () => Navigator.pop(context),
            backgroundColor: AppTheme.primaryDark,
            textColor: Colors.white,
            width: double.infinity,
            height: 48,
          ),
        ],
      ),
    );
  }
}
