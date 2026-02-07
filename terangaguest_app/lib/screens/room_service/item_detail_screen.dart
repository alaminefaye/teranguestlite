import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../config/theme.dart';
import '../../models/menu_item.dart';
import '../../models/favorite_item.dart';
import '../../providers/cart_provider.dart';
import '../../providers/favorites_provider.dart';
import '../../widgets/quantity_selector.dart';
import '../../widgets/cart_badge.dart';
import '../../widgets/animated_button.dart';
import '../../utils/haptic_helper.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../utils/navigation_helper.dart';
import 'cart_screen.dart';

class ItemDetailScreen extends StatefulWidget {
  final MenuItem item;

  const ItemDetailScreen({
    super.key,
    required this.item,
  });

  @override
  State<ItemDetailScreen> createState() => _ItemDetailScreenState();
}

class _ItemDetailScreenState extends State<ItemDetailScreen> {
  int _quantity = 1;
  final TextEditingController _instructionsController = TextEditingController();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<FavoritesProvider>().load();
    });
  }

  @override
  void dispose() {
    _instructionsController.dispose();
    super.dispose();
  }

  void _incrementQuantity() {
    setState(() {
      _quantity++;
    });
  }

  void _decrementQuantity() {
    if (_quantity > 1) {
      setState(() {
        _quantity--;
      });
    }
  }

  void _addToCart(BuildContext context) {
    HapticHelper.addToCart();
    final cartProvider = Provider.of<CartProvider>(context, listen: false);

    cartProvider.addItem(
      widget.item,
      quantity: _quantity,
      specialInstructions: _instructionsController.text.isEmpty
          ? null
          : _instructionsController.text,
    );

    // Récupérer le Navigator avant le pop pour que "VOIR PANIER" fonctionne
    final navigator = Navigator.maybeOf(context);

    // Afficher un message de confirmation
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            const Icon(Icons.check_circle, color: Colors.white),
            const SizedBox(width: 12),
            Expanded(
              child: Text(
                '${widget.item.name} ajouté au panier',
                style: const TextStyle(color: Colors.white),
              ),
            ),
          ],
        ),
        backgroundColor: Colors.green,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(10),
        ),
        duration: const Duration(seconds: 2),
        action: SnackBarAction(
          label: AppLocalizations.of(context).viewCartCaps,
          textColor: Colors.white,
          onPressed: () {
            navigator?.push(
              NavigationHelper.slideFadeRoute(const CartScreen()),
            );
          },
        ),
      ),
    );

    // Retourner à l'écran précédent
    Navigator.pop(context);
  }

  @override
  Widget build(BuildContext context) {
    final priceLabel = '${widget.item.price.toStringAsFixed(0)} FCFA';
    return Semantics(
      label: '${AppLocalizations.of(context).description} ${widget.item.name}, $priceLabel',
      child: Scaffold(
      body: Container(
        decoration: BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [AppTheme.primaryDark, AppTheme.primaryBlue],
          ),
        ),
        child: Stack(
          children: [
            // Contenu scrollable
            CustomScrollView(
              slivers: [
                // Image en haut
                _buildImageHeader(),

                // Détails
                SliverToBoxAdapter(
                  child: _buildDetails(),
                ),
              ],
            ),

            // Bouton retour
            Positioned(
              top: MediaQuery.of(context).padding.top + 8,
              left: 8,
              child: Container(
                decoration: BoxDecoration(
                  color: AppTheme.primaryDark.withValues(alpha: 0.8),
                  shape: BoxShape.circle,
                  border: Border.all(
                    color: AppTheme.accentGold,
                    width: 1.5,
                  ),
                ),
                child: IconButton(
                  icon: const Icon(Icons.arrow_back, color: AppTheme.accentGold),
                  onPressed: () => Navigator.pop(context),
                ),
              ),
            ),

            // Favori + Badge panier
            Positioned(
              top: MediaQuery.of(context).padding.top + 8,
              right: 8,
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Consumer<FavoritesProvider>(
                    builder: (context, fav, _) {
                      final isFav = fav.isFavorite(FavoriteType.menuItem, widget.item.id);
                      return Container(
                        decoration: BoxDecoration(
                          color: AppTheme.primaryDark.withValues(alpha: 0.8),
                          shape: BoxShape.circle,
                          border: Border.all(color: AppTheme.accentGold, width: 1.5),
                        ),
                        child: IconButton(
                          icon: Icon(
                            isFav ? Icons.favorite : Icons.favorite_border,
                            color: isFav ? Colors.red : AppTheme.accentGold,
                          ),
                          onPressed: () {
                            HapticHelper.lightImpact();
                            fav.toggle(FavoriteItem(
                              type: FavoriteType.menuItem,
                              id: widget.item.id,
                              name: widget.item.name,
                              imageUrl: widget.item.image,
                            ));
                          },
                        ),
                      );
                    },
                  ),
                  const SizedBox(width: 8),
                  Container(
                    decoration: BoxDecoration(
                      color: AppTheme.primaryDark.withValues(alpha: 0.8),
                      shape: BoxShape.circle,
                      border: Border.all(color: AppTheme.accentGold, width: 1.5),
                    ),
                    child: const CartBadge(),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
      bottomNavigationBar: _buildBottomBar(context),
    ),
    );
  }

  Widget _buildImageHeader() {
    final size = MediaQuery.sizeOf(context);
    final screenHeight = size.height;
    final isLandscape = size.width > size.height;
    // En paysage : image très réduite pour laisser la place au contenu (éviter coupure)
    final double expandedHeight;
    if (isLandscape) {
      expandedHeight = (screenHeight * 0.22).clamp(100.0, 160.0);
    } else {
      final isTablet = size.shortestSide >= 600;
      expandedHeight = isTablet
          ? (screenHeight * 0.28).clamp(200.0, 280.0)
          : 300.0;
    }
    return SliverAppBar(
      expandedHeight: expandedHeight,
      automaticallyImplyLeading: false,
      pinned: false,
      floating: true,
      snap: true,
      flexibleSpace: FlexibleSpaceBar(
        background: widget.item.image != null
            ? Container(
                color: AppTheme.primaryDark,
                child: CachedNetworkImage(
                  imageUrl: widget.item.image!,
                  fit: BoxFit.contain,
                  placeholder: (context, url) => _buildImagePlaceholder(),
                  errorWidget: (context, url, error) => _buildImagePlaceholder(),
                ),
              )
            : _buildImagePlaceholder(),
      ),
    );
  }

  Widget _buildImagePlaceholder() {
    return Container(
      color: AppTheme.primaryBlue,
      child: const Center(
        child: Icon(
          Icons.restaurant,
          size: 100,
          color: AppTheme.accentGold,
        ),
      ),
    );
  }

  Widget _buildDetails() {
    return Container(
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topCenter,
          end: Alignment.bottomCenter,
          colors: [AppTheme.primaryDark, AppTheme.primaryBlue],
        ),
        borderRadius: const BorderRadius.only(
          topLeft: Radius.circular(30),
          topRight: Radius.circular(30),
        ),
      ),
      padding: const EdgeInsets.all(24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Nom
          Text(
            widget.item.name,
            style: const TextStyle(
              fontSize: 28,
              fontWeight: FontWeight.bold,
              color: Colors.white,
            ),
          ),
          const SizedBox(height: 12),

          // Catégorie + Temps de préparation
          Row(
            children: [
              if (widget.item.category != null &&
                  (widget.item.category?.name ?? '').isNotEmpty) ...[
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 12,
                    vertical: 6,
                  ),
                  decoration: BoxDecoration(
                    color: AppTheme.accentGold.withValues(alpha: 0.2),
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(
                      color: AppTheme.accentGold.withValues(alpha: 0.3),
                    ),
                  ),
                  child: Text(
                    widget.item.category!.name,
                    style: const TextStyle(
                      fontSize: 13,
                      color: AppTheme.accentGold,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ),
                const SizedBox(width: 12),
              ],
              if (widget.item.preparationTime > 0) ...[
                const Icon(
                  Icons.access_time,
                  size: 16,
                  color: AppTheme.textGray,
                ),
                const SizedBox(width: 6),
                Text(
                  '${widget.item.preparationTime} min',
                  style: const TextStyle(
                    fontSize: 14,
                    color: AppTheme.textGray,
                  ),
                ),
              ],
            ],
          ),
          const SizedBox(height: 20),

          // Prix
          Text(
            widget.item.formattedPrice,
            style: const TextStyle(
              fontSize: 32,
              fontWeight: FontWeight.bold,
              color: AppTheme.accentGold,
            ),
          ),
          const SizedBox(height: 24),

          // Description
          if (widget.item.description != null) ...[
            Text(
              AppLocalizations.of(context).description,
              style: const TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
                color: Colors.white,
              ),
            ),
            const SizedBox(height: 12),
            Text(
              widget.item.description!,
              style: const TextStyle(
                fontSize: 15,
                color: AppTheme.textGray,
                height: 1.6,
              ),
            ),
            const SizedBox(height: 24),
          ],

          // Quantité
          const Text(
            'Quantité',
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: Colors.white,
            ),
          ),
          const SizedBox(height: 12),
          QuantitySelector(
            quantity: _quantity,
            onIncrement: _incrementQuantity,
            onDecrement: _decrementQuantity,
          ),
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
            controller: _instructionsController,
            maxLines: 3,
            style: const TextStyle(color: Colors.white),
            decoration: InputDecoration(
              hintText: AppLocalizations.of(context).specialInstructionsExample,
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
          SizedBox(height: MediaQuery.of(context).padding.bottom + 100), // Espace pour scroll + bouton fixe
        ],
      ),
    );
  }

  Widget _buildBottomBar(BuildContext context) {
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
      child: AnimatedButton(
        text: widget.item.isAvailable ? AppLocalizations.of(context).addToCart : AppLocalizations.of(context).unavailable,
        icon: Icons.add_shopping_cart,
        onPressed: widget.item.isAvailable ? () => _addToCart(context) : null,
        width: double.infinity,
        height: 56,
        backgroundColor: AppTheme.accentGold,
        textColor: AppTheme.primaryDark,
        enableHaptic: false, // _addToCart already calls HapticHelper.addToCart
      ),
    );
  }
}
