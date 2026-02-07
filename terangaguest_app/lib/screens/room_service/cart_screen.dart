import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../providers/cart_provider.dart';
import '../../providers/tablet_session_provider.dart';
import '../../widgets/quantity_selector.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/animated_button.dart';
import '../../utils/haptic_helper.dart';
import '../../models/guest_session.dart';
import '../../utils/navigation_helper.dart';
import 'order_confirmation_screen.dart';

class CartScreen extends StatefulWidget {
  const CartScreen({super.key});

  @override
  State<CartScreen> createState() => _CartScreenState();
}

class _CartScreenState extends State<CartScreen> {
  final TextEditingController _specialInstructionsController =
      TextEditingController();
  bool _isProcessing = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) async {
      final tabletSession = context.read<TabletSessionProvider>();
      await tabletSession.load();
      // Récupérer automatiquement le numéro de chambre depuis l'utilisateur connecté (API)
      if ((tabletSession.roomNumber ?? '').trim().isEmpty) {
        final authUser = context.read<AuthProvider>().user;
        if (authUser?.roomNumber != null && authUser!.roomNumber!.trim().isNotEmpty) {
          await tabletSession.setRoomNumber(authUser.roomNumber!.trim());
        }
      }
    });
  }

  @override
  void dispose() {
    _specialInstructionsController.dispose();
    super.dispose();
  }

  Future<void> _checkout(BuildContext context) async {
    final cartProvider = Provider.of<CartProvider>(context, listen: false);
    final tabletSession = Provider.of<TabletSessionProvider>(context, listen: false);

    if (cartProvider.isEmpty) {
      HapticHelper.error();
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(_l10n(context).emptyCart),
          backgroundColor: Colors.red,
        ),
      );
      return;
    }

    final specialInstructions = _specialInstructionsController.text.isEmpty
        ? null
        : _specialInstructionsController.text;

    // Spec tablette : le code client est toujours demandé à la validation (pas de checkout "utilisateur connecté").
    // Si pas de session tablette → afficher "Entrez votre code", puis valider la commande avec la session.
    if (!tabletSession.hasSession) {
      // Récupérer automatiquement le numéro de chambre depuis l'utilisateur connecté (API)
      if ((tabletSession.roomNumber ?? '').trim().isEmpty) {
        final authUser = context.read<AuthProvider>().user;
        if (authUser?.roomNumber != null && authUser!.roomNumber!.trim().isNotEmpty) {
          await tabletSession.setRoomNumber(authUser.roomNumber!.trim());
        }
      }
      final success = await _showGuestCodeDialog(context, tabletSession);
      if (!success || !context.mounted) return;
      if (!tabletSession.hasSession) return;
    }

    // Confirmation identité + moyen de paiement.
    final confirmResult = await _showConfirmIdentityDialog(context, tabletSession.session!);
    if (!confirmResult.confirmed || confirmResult.paymentMethod == null || !context.mounted) return;

    HapticHelper.confirm();
    setState(() => _isProcessing = true);

    try {
      final result = await cartProvider.checkoutWithTabletSession(
        tabletSession.session!,
        specialInstructions: specialInstructions,
        paymentMethod: confirmResult.paymentMethod!,
      );

      setState(() => _isProcessing = false);
      if (!context.mounted) return;
      HapticHelper.success();
      NavigationHelper.replaceWith(
        context,
        OrderConfirmationScreen(orderData: result),
      );
    } catch (e) {
      setState(() => _isProcessing = false);
      if (!context.mounted) return;
      HapticHelper.error();
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(e.toString().replaceFirst('Exception: ', '')),
          backgroundColor: Colors.red,
          duration: const Duration(seconds: 4),
        ),
      );
    }
  }

  /// Affiche le dialogue "Entrez votre code client" pour la tablette.
  /// La chambre est en lecture seule (tablette reliée à une chambre) ; seul le code est saisi.
  Future<bool> _showGuestCodeDialog(
    BuildContext context,
    TabletSessionProvider tabletSession,
  ) async {
    String? code;
    final codeController = TextEditingController();
    final roomController = TextEditingController(text: tabletSession.roomNumber);

    final ok = await showDialog<bool>(
      context: context,
      barrierDismissible: false,
      builder: (ctx) {
        return Consumer<TabletSessionProvider>(
          builder: (ctx, ts, _) {
            final loading = ts.isLoading;
            final error = ts.error;
            final currentRoom = (ts.roomNumber ?? '').trim();
            final showRoomSetup = currentRoom.isEmpty;

            return AlertDialog(
              backgroundColor: AppTheme.primaryBlue,
              title: const Text(
                'Code client',
                style: TextStyle(color: AppTheme.accentGold),
              ),
              content: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    const Text(
                      'Entrez le code reçu à l\'enregistrement pour valider la commande.',
                      style: TextStyle(color: Colors.white70, fontSize: 14),
                    ),
                    const SizedBox(height: 16),
                    // Chambre : lecture seule si déjà définie, sinon champ unique "Définir la chambre"
                    if (showRoomSetup) ...[
                      TextField(
                        controller: roomController,
                        decoration: InputDecoration(
                          labelText: 'Numéro de chambre (cette tablette)',
                          labelStyle: const TextStyle(color: AppTheme.textGray),
                          filled: true,
                          fillColor: Colors.white.withValues(alpha: 0.1),
                          border: const OutlineInputBorder(),
                          hintText: 'ex: 101',
                        ),
                        keyboardType: TextInputType.number,
                        style: const TextStyle(color: Colors.white),
                        readOnly: false,
                      ),
                      const SizedBox(height: 8),
                      Text(
                        'La chambre sera mémorisée pour les prochaines validations.',
                        style: TextStyle(color: Colors.white54, fontSize: 12),
                      ),
                      const SizedBox(height: 12),
                    ] else ...[
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 14),
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.08),
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(color: AppTheme.accentGold.withValues(alpha: 0.3)),
                        ),
                        child: Row(
                          children: [
                            const Icon(Icons.bed, color: AppTheme.accentGold, size: 20),
                            const SizedBox(width: 10),
                            Text(
                              'Chambre : ${ts.roomNumber ?? "—"}',
                              style: const TextStyle(
                                color: Colors.white,
                                fontSize: 16,
                                fontWeight: FontWeight.w500,
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 12),
                    ],
                    TextField(
                      controller: codeController,
                      decoration: InputDecoration(
                        labelText: 'Code à 6 chiffres',
                        labelStyle: const TextStyle(color: AppTheme.textGray),
                        filled: true,
                        fillColor: Colors.white.withValues(alpha: 0.1),
                        border: const OutlineInputBorder(),
                      ),
                      keyboardType: TextInputType.number,
                      obscureText: false,
                      maxLength: 6,
                      style: const TextStyle(color: Colors.white, letterSpacing: 8),
                      onChanged: (v) => code = v.trim(),
                    ),
                    if (error != null) ...[
                      const SizedBox(height: 8),
                      Text(error, style: const TextStyle(color: Colors.red, fontSize: 12)),
                    ],
                  ],
                ),
              ),
              actions: [
                TextButton(
                  onPressed: loading ? null : () => Navigator.of(ctx).pop(false),
                  child: const Text('Annuler', style: TextStyle(color: Colors.white70)),
                ),
                FilledButton(
                  onPressed: loading
                      ? null
                      : () async {
                          final c = (code ?? codeController.text.trim());
                          if (c.isEmpty) {
                            ScaffoldMessenger.of(ctx).showSnackBar(
                              const SnackBar(
                                content: Text('Entrez le code à 6 chiffres.'),
                                backgroundColor: Colors.orange,
                              ),
                            );
                            return;
                          }
                          final r = showRoomSetup
                              ? roomController.text.trim()
                              : (ts.roomNumber ?? '').trim();
                          if (r.isEmpty) {
                            ScaffoldMessenger.of(ctx).showSnackBar(
                              const SnackBar(
                                content: Text('Définissez le numéro de chambre de cette tablette.'),
                                backgroundColor: Colors.orange,
                              ),
                            );
                            return;
                          }
                          if (showRoomSetup) await ts.setRoomNumber(r);
                          try {
                            await ts.validateCode(c);
                            if (!ctx.mounted) return;
                            Navigator.of(ctx).pop(true);
                          } catch (e) {
                            final message = e.toString().replaceFirst('Exception: ', '');
                            if (ctx.mounted) {
                              ScaffoldMessenger.of(ctx).showSnackBar(
                                SnackBar(
                                  content: Text(message),
                                  backgroundColor: Colors.red,
                                  duration: const Duration(seconds: 5),
                                ),
                              );
                            }
                          }
                        },
                  style: FilledButton.styleFrom(backgroundColor: AppTheme.accentGold),
                  child: loading
                      ? const SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                        )
                      : const Text('Valider'),
                ),
              ],
            );
          },
        );
      },
    );
    tabletSession.clearError();
    return ok == true;
  }

  /// Résultat de la boîte de dialogue de confirmation (identité + moyen de paiement).
  static const _paymentMethods = [
    ('cash', 'Espèce'),
    ('room_bill', 'Mettre sur la note de la chambre'),
    ('wave', 'Wave'),
    ('orange_money', 'Orange Money'),
  ];

  /// Affiche les infos client (nom, chambre, tél) + choix du moyen de paiement avant envoi.
  Future<({bool confirmed, String? paymentMethod})> _showConfirmIdentityDialog(BuildContext context, GuestSession session) async {
    String? selectedPayment = 'room_bill';
    final result = await showDialog<({bool confirmed, String? paymentMethod})>(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setState) {
          return AlertDialog(
            backgroundColor: AppTheme.primaryBlue,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(16),
              side: const BorderSide(color: AppTheme.accentGold, width: 1),
            ),
            title: const Text(
              'Confirmer que c\'est bien vous',
              style: TextStyle(color: AppTheme.accentGold, fontSize: 20),
            ),
            content: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Vérifiez vos informations avant d\'envoyer la commande :',
                    style: TextStyle(color: Colors.white70, fontSize: 14),
                  ),
                  const SizedBox(height: 16),
                  _buildIdentityRow(Icons.person_outline, 'Nom', session.guestName),
                  const SizedBox(height: 10),
                  _buildIdentityRow(Icons.bed, 'Chambre', session.roomNumber),
                  const SizedBox(height: 10),
                  _buildIdentityRow(
                    Icons.phone_outlined,
                    'Téléphone',
                    (session.guestPhone ?? '').isNotEmpty ? (session.guestPhone ?? '') : '—',
                  ),
                  if ((session.guestEmail ?? '').isNotEmpty) ...[
                    const SizedBox(height: 10),
                    _buildIdentityRow(Icons.email_outlined, 'Email', session.guestEmail ?? ''),
                  ],
                  const SizedBox(height: 20),
                  const Text(
                    'Moyen de paiement',
                    style: TextStyle(color: AppTheme.accentGold, fontSize: 16, fontWeight: FontWeight.w600),
                  ),
                  const SizedBox(height: 10),
                  ..._paymentMethods.map((e) => RadioListTile<String>(
                    value: e.$1,
                    groupValue: selectedPayment,
                    onChanged: (v) => setState(() => selectedPayment = v),
                    title: Text(e.$2, style: const TextStyle(color: Colors.white, fontSize: 15)),
                    activeColor: AppTheme.accentGold,
                    dense: true,
                    contentPadding: EdgeInsets.zero,
                  )),
                ],
              ),
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.of(ctx).pop((confirmed: false, paymentMethod: null as String?)),
                child: Text('Annuler', style: TextStyle(color: AppTheme.textGray)),
              ),
              FilledButton(
                onPressed: () => Navigator.of(ctx).pop((confirmed: true, paymentMethod: selectedPayment)),
                style: FilledButton.styleFrom(backgroundColor: AppTheme.accentGold, foregroundColor: AppTheme.primaryDark),
                child: const Text('Confirmer la commande', style: TextStyle(fontWeight: FontWeight.bold)),
              ),
            ],
          );
        },
      ),
    );
    return result ?? (confirmed: false, paymentMethod: null);
  }

  Widget _buildIdentityRow(IconData icon, String label, String value) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Icon(icon, size: 20, color: AppTheme.accentGold),
        const SizedBox(width: 10),
        Expanded(
          child: RichText(
            text: TextSpan(
              style: const TextStyle(color: Colors.white, fontSize: 15),
              children: [
                TextSpan(text: '$label : ', style: TextStyle(color: AppTheme.textGray, fontWeight: FontWeight.w500)),
                TextSpan(text: value),
              ],
            ),
          ),
        ),
      ],
    );
  }

  /// Localisations avec repli si of(context) est null (évite le crash "Null check operator").
  AppLocalizations _l10n(BuildContext context) {
    return Localizations.of<AppLocalizations>(context, AppLocalizations) ??
        lookupAppLocalizations(const Locale('fr'));
  }

  @override
  Widget build(BuildContext context) {
    final l10n = _l10n(context);
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
              _buildHeader(context, l10n),

              // Contenu
              Expanded(
                child: Consumer<CartProvider>(
                  builder: (context, cart, child) {
                    if (cart.isEmpty) {
                      return _buildEmptyCart(context, l10n);
                    }
                    return _buildCartItems(context, cart, l10n);
                  },
                ),
              ),

              // Bottom bar avec total et bouton checkout
              Consumer<CartProvider>(
                builder: (context, cart, child) {
                  if (cart.isEmpty) return const SizedBox.shrink();
                  return _buildBottomBar(context, cart, l10n);
                },
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildHeader(BuildContext context, AppLocalizations l10n) {
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
                  l10n.myCart,
                  style: const TextStyle(
                    fontSize: 28,
                    fontWeight: FontWeight.bold,
                    color: Colors.white,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  l10n.verifyOrder,
                  style: const TextStyle(
                    fontSize: 14,
                    color: AppTheme.textGray,
                  ),
                ),
              ],
            ),
          ),

          // Bouton vider le panier
          Consumer<CartProvider>(
            builder: (context, cart, child) {
              if (cart.isEmpty) return const SizedBox.shrink();
              return IconButton(
                icon: const Icon(Icons.delete_outline, color: Colors.red),
                onPressed: () {
                  _showClearCartDialog(context, cart, _l10n(context));
                },
              );
            },
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyCart(BuildContext context, AppLocalizations l10n) {
    return EmptyStateWidget(
      icon: Icons.shopping_cart_outlined,
      title: l10n.emptyCart,
      subtitle: l10n.emptyCartHint,
      iconSize: 100,
      iconColor: AppTheme.textGray.withValues(alpha: 0.5),
      action: AnimatedButton(
        text: l10n.browseMenu,
        onPressed: () => Navigator.pop(context),
        backgroundColor: AppTheme.accentGold,
        textColor: AppTheme.primaryDark,
      ),
    );
  }

  Widget _buildCartItems(
      BuildContext context, CartProvider cart, AppLocalizations l10n) {
    return ListView(
      padding: const EdgeInsets.all(20),
      children: [
        // Liste des articles
        ...cart.items.map((cartItem) => _buildCartItemTile(cartItem, cart)),

        const SizedBox(height: 24),

        // Instructions spéciales
        Text(
          l10n.specialInstructions,
          style: const TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.bold,
            color: Colors.white,
          ),
        ),
        const SizedBox(height: 12),
        TextField(
          controller: _specialInstructionsController,
          maxLines: 3,
          style: const TextStyle(color: Colors.white),
          decoration: InputDecoration(
            hintText: l10n.specialInstructionsHint,
            hintStyle: TextStyle(
              color: AppTheme.textGray.withValues(alpha: 0.6),
            ),
            filled: true,
            fillColor: AppTheme.primaryBlue.withValues(alpha: 0.5),
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: BorderSide(
                color: AppTheme.accentGold.withValues(alpha: 0.3),
              ),
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: BorderSide(
                color: AppTheme.accentGold.withValues(alpha: 0.3),
              ),
            ),
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(
                color: AppTheme.accentGold,
                width: 1.5,
              ),
            ),
          ),
        ),

        const SizedBox(height: 100), // Espace pour le bottom bar
      ],
    );
  }

  Widget _buildCartItemTile(dynamic cartItem, CartProvider cart) {
    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [
            AppTheme.primaryBlue.withValues(alpha: 0.6),
            AppTheme.primaryDark.withValues(alpha: 0.8),
          ],
        ),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: AppTheme.accentGold.withValues(alpha: 0.3),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Image
              ClipRRect(
                borderRadius: BorderRadius.circular(12),
                child: (cartItem.menuItem.image != null &&
                        cartItem.menuItem.image!.isNotEmpty)
                    ? CachedNetworkImage(
                        imageUrl: cartItem.menuItem.image!,
                        width: 80,
                        height: 80,
                        fit: BoxFit.cover,
                        placeholder: (context, url) => _buildItemPlaceholder(),
                        errorWidget: (context, url, error) => _buildItemPlaceholder(),
                      )
                    : _buildItemPlaceholder(),
              ),
              const SizedBox(width: 12),

              // Infos
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Expanded(
                          child: Text(
                            cartItem.menuItem.name,
                            style: const TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                              color: Colors.white,
                            ),
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                        IconButton(
                          icon: const Icon(
                            Icons.delete_outline,
                            color: Colors.red,
                            size: 20,
                          ),
                          onPressed: () {
                            HapticHelper.mediumImpact();
                            cart.removeItem(cartItem.menuItem.id);
                          },
                        ),
                      ],
                    ),
                    const SizedBox(height: 4),
                    Text(
                      cartItem.menuItem.formattedPrice,
                      style: const TextStyle(
                        fontSize: 15,
                        color: AppTheme.accentGold,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        QuantitySelector(
                          quantity: cartItem.quantity,
                          onIncrement: () {
                            HapticHelper.lightImpact();
                            cart.incrementQuantity(cartItem.menuItem.id);
                          },
                          onDecrement: () {
                            HapticHelper.lightImpact();
                            cart.decrementQuantity(cartItem.menuItem.id);
                          },
                        ),
                        Text(
                          cartItem.formattedSubtotal,
                          style: const TextStyle(
                            fontSize: 17,
                            fontWeight: FontWeight.bold,
                            color: AppTheme.accentGold,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ],
          ),

          // Instructions spéciales de l'article
          if ((cartItem.specialInstructions ?? '').isNotEmpty) ...[
            const SizedBox(height: 12),
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: AppTheme.primaryBlue.withValues(alpha: 0.3),
                borderRadius: BorderRadius.circular(8),
                border: Border.all(
                  color: AppTheme.accentGold.withValues(alpha: 0.2),
                ),
              ),
              child: Row(
                children: [
                  const Icon(
                    Icons.info_outline,
                    size: 16,
                    color: AppTheme.accentGold,
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      cartItem.specialInstructions ?? '',
                      style: const TextStyle(
                        fontSize: 13,
                        color: AppTheme.textGray,
                        fontStyle: FontStyle.italic,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildItemPlaceholder() {
    return Container(
      width: 80,
      height: 80,
      decoration: BoxDecoration(
        color: AppTheme.primaryBlue,
        borderRadius: BorderRadius.circular(12),
      ),
      child: const Icon(
        Icons.restaurant,
        color: AppTheme.accentGold,
        size: 32,
      ),
    );
  }

  Widget _buildBottomBar(
      BuildContext context, CartProvider cart, AppLocalizations l10n) {
    return Container(
      padding: EdgeInsets.only(
        left: 24,
        right: 24,
        top: 16,
        bottom: MediaQuery.of(context).padding.bottom + 16,
      ),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topCenter,
          end: Alignment.bottomCenter,
          colors: [
            AppTheme.primaryDark.withValues(alpha: 0.95),
            AppTheme.primaryDark,
          ],
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.3),
            blurRadius: 10,
            offset: const Offset(0, -2),
          ),
        ],
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          // Total
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Total',
                    style: TextStyle(
                      fontSize: 14,
                      color: AppTheme.textGray,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    cart.formattedTotal,
                    style: const TextStyle(
                      fontSize: 28,
                      fontWeight: FontWeight.bold,
                      color: AppTheme.accentGold,
                    ),
                  ),
                ],
              ),
              Text(
                '${cart.totalItemsQuantity} article${cart.totalItemsQuantity > 1 ? 's' : ''}',
                style: const TextStyle(
                  fontSize: 14,
                  color: AppTheme.textGray,
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),

          // Bouton Commander
          AnimatedButton(
            text: l10n.placeOrder,
            icon: Icons.check_circle,
            onPressed: _isProcessing ? null : () => _checkout(context),
            isLoading: _isProcessing,
            width: double.infinity,
            height: 56,
          ),
        ],
      ),
    );
  }

  void _showClearCartDialog(
      BuildContext context, CartProvider cart, AppLocalizations l10n) {
    showDialog(
      context: context,
      builder: (dialogContext) => AlertDialog(
        backgroundColor: AppTheme.primaryBlue,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(16),
          side: const BorderSide(
            color: AppTheme.accentGold,
            width: 1,
          ),
        ),
        title: Text(
          l10n.clear,
          style: const TextStyle(color: Colors.white),
        ),
        content: Text(
          l10n.clearCartConfirm,
          style: const TextStyle(color: AppTheme.textGray),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text(
              l10n.cancel,
              style: const TextStyle(color: AppTheme.textGray),
            ),
          ),
          AnimatedButton(
            text: l10n.clear,
            onPressed: () {
              HapticHelper.heavyImpact();
              cart.clear();
              Navigator.pop(context);
            },
            backgroundColor: Colors.red,
            textColor: Colors.white,
            enableHaptic: false,
          ),
        ],
      ),
    );
  }
}
