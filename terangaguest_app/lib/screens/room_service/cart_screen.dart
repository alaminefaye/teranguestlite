import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../config/theme.dart';
import '../../providers/cart_provider.dart';
import '../../widgets/quantity_selector.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/animated_button.dart';
import '../../utils/haptic_helper.dart';
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
  void dispose() {
    _specialInstructionsController.dispose();
    super.dispose();
  }

  Future<void> _checkout(BuildContext context) async {
    final cartProvider = Provider.of<CartProvider>(context, listen: false);

    if (cartProvider.isEmpty) {
      HapticHelper.error();
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(AppLocalizations.of(context).emptyCart),
          backgroundColor: Colors.red,
        ),
      );
      return;
    }

    // Feedback confirmation checkout
    HapticHelper.confirm();
    
    setState(() {
      _isProcessing = true;
    });

    try {
      final result = await cartProvider.checkout(
        specialInstructions: _specialInstructionsController.text.isEmpty
            ? null
            : _specialInstructionsController.text,
      );

      setState(() {
        _isProcessing = false;
      });

      if (!context.mounted) return;

      // Feedback succès
      HapticHelper.success();

      // Naviguer vers l'écran de confirmation avec animation
      NavigationHelper.replaceWith(
        context,
        OrderConfirmationScreen(orderData: result),
      );
    } catch (e) {
      setState(() {
        _isProcessing = false;
      });

      if (!context.mounted) return;

      // Feedback erreur
      HapticHelper.error();

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(e.toString().replaceAll('Exception: ', '')),
          backgroundColor: Colors.red,
          duration: const Duration(seconds: 4),
        ),
      );
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
              Expanded(
                child: Consumer<CartProvider>(
                  builder: (context, cart, child) {
                    if (cart.isEmpty) {
                      return _buildEmptyCart(context);
                    }
                    return _buildCartItems(cart);
                  },
                ),
              ),

              // Bottom bar avec total et bouton checkout
              Consumer<CartProvider>(
                builder: (context, cart, child) {
                  if (cart.isEmpty) return const SizedBox.shrink();
                  return _buildBottomBar(context, cart);
                },
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
                  AppLocalizations.of(context).myCart,
                  style: const TextStyle(
                    fontSize: 28,
                    fontWeight: FontWeight.bold,
                    color: Colors.white,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  AppLocalizations.of(context).verifyOrder,
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
                  _showClearCartDialog(context, cart);
                },
              );
            },
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyCart(BuildContext context) {
    final l10n = AppLocalizations.of(context);
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

  Widget _buildCartItems(CartProvider cart) {
    return ListView(
      padding: const EdgeInsets.all(20),
      children: [
        // Liste des articles
        ...cart.items.map((cartItem) => _buildCartItemTile(cartItem, cart)),

        const SizedBox(height: 24),

        // Instructions spéciales
        Text(
          AppLocalizations.of(context).specialInstructions,
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
            hintText: AppLocalizations.of(context).specialInstructionsHint,
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
                child: cartItem.menuItem.image != null
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
          if (cartItem.specialInstructions != null &&
              cartItem.specialInstructions!.isNotEmpty) ...[
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
                      cartItem.specialInstructions!,
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

  Widget _buildBottomBar(BuildContext context, CartProvider cart) {
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
            text: AppLocalizations.of(context).placeOrder,
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

  void _showClearCartDialog(BuildContext context, CartProvider cart) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        backgroundColor: AppTheme.primaryBlue,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(16),
          side: const BorderSide(
            color: AppTheme.accentGold,
            width: 1,
          ),
        ),
        title: Text(
          AppLocalizations.of(context).clear,
          style: const TextStyle(color: Colors.white),
        ),
        content: Text(
          AppLocalizations.of(context).clearCartConfirm,
          style: const TextStyle(color: AppTheme.textGray),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text(
              AppLocalizations.of(context).cancel,
              style: const TextStyle(color: AppTheme.textGray),
            ),
          ),
          AnimatedButton(
            text: AppLocalizations.of(context).clear,
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
