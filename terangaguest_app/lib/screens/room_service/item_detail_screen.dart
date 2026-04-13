import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../config/theme.dart';
import '../../models/menu_item.dart';
import '../../models/favorite_item.dart';
import '../../providers/favorites_provider.dart';
import '../../providers/locale_provider.dart';
import '../../utils/translatable_text_helper.dart';
import '../../widgets/translatable_text.dart';
import '../../utils/haptic_helper.dart';
import '../../generated/l10n/app_localizations.dart';

class ItemDetailScreen extends StatefulWidget {
  final MenuItem item;

  const ItemDetailScreen({super.key, required this.item});

  @override
  State<ItemDetailScreen> createState() => _ItemDetailScreenState();
}

class _ItemDetailScreenState extends State<ItemDetailScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<FavoritesProvider>().load();
    });
  }

  @override
  void dispose() {
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final priceLabel = widget.item.formattedPrice;
    final nameStr = TranslatableTextHelper.resolveDisplayTextSync(
      widget.item.name,
      context.read<LocaleProvider>().languageCode,
    );
    return Semantics(
      label: '${AppLocalizations.of(context).description} $nameStr, $priceLabel',
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
                  SliverToBoxAdapter(child: _buildDetails()),
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
                    border: Border.all(color: AppTheme.accentGold, width: 1.5),
                  ),
                  child: IconButton(
                    icon: const Icon(
                      Icons.arrow_back,
                      color: AppTheme.accentGold,
                    ),
                    onPressed: () => Navigator.pop(context),
                  ),
                ),
              ),

              // Favori + Badge panier
              Positioned(
                top: MediaQuery.of(context).padding.top + 8,
                right: 8,
                child: Consumer<FavoritesProvider>(
                  builder: (context, fav, _) {
                    final isFav = fav.isFavorite(
                      FavoriteType.menuItem,
                      widget.item.id,
                    );
                    return Container(
                      decoration: BoxDecoration(
                        color: AppTheme.primaryDark.withValues(alpha: 0.8),
                        shape: BoxShape.circle,
                        border: Border.all(
                          color: AppTheme.accentGold,
                          width: 1.5,
                        ),
                      ),
                      child: IconButton(
                        icon: Icon(
                          isFav ? Icons.favorite : Icons.favorite_border,
                          color: isFav ? Colors.red : AppTheme.accentGold,
                        ),
                        onPressed: () {
                          HapticHelper.lightImpact();
                          fav.toggle(
                            FavoriteItem(
                              type: FavoriteType.menuItem,
                              id: widget.item.id,
                              name: TranslatableTextHelper.resolveDisplayTextSync(
                                widget.item.name,
                                context.read<LocaleProvider>().languageCode,
                              ),
                              imageUrl: widget.item.image,
                            ),
                          );
                        },
                      ),
                    );
                  },
                ),
              ),
            ],
          ),
        ),
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
                  errorWidget: (context, url, error) =>
                      _buildImagePlaceholder(),
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
        child: Icon(Icons.restaurant, size: 100, color: AppTheme.accentGold),
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
          TranslatableText(
            widget.item.name,
            locale: context.read<LocaleProvider>().languageCode,
            style: TextStyle(
              fontSize: MediaQuery.of(context).size.width < 600 ? 20 : 28,
              fontWeight: FontWeight.bold,
              color: Colors.white,
            ),
          ),
          const SizedBox(height: 12),

          // Catégorie + Temps de préparation
          Row(
            children: [
              if (widget.item.category != null &&
                  TranslatableTextHelper.resolveDisplayTextSync(widget.item.category!.name, context.read<LocaleProvider>().languageCode).trim().isNotEmpty) ...[
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
                  child: TranslatableText(
                    widget.item.category!.name,
                    locale: context.read<LocaleProvider>().languageCode,
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
                _PrepTimeBadge(minutes: widget.item.preparationTime),
              ],
            ],
          ),
          const SizedBox(height: 20),

          // Prix
          Text(
            widget.item.formattedPrice,
            style: TextStyle(
              fontSize: MediaQuery.of(context).size.width < 600 ? 22 : 32,
              fontWeight: FontWeight.bold,
              color: AppTheme.accentGold,
            ),
          ),
          const SizedBox(height: 24),

          // Description
          if (TranslatableTextHelper.resolveDisplayTextSync(widget.item.description, context.read<LocaleProvider>().languageCode).trim().isNotEmpty) ...[
            Text(
              AppLocalizations.of(context).description,
              style: TextStyle(
                fontSize: MediaQuery.of(context).size.width < 600 ? 14 : 18,
                fontWeight: FontWeight.bold,
                color: Colors.white,
              ),
            ),
            const SizedBox(height: 12),
            TranslatableText(
              widget.item.description,
              locale: context.read<LocaleProvider>().languageCode,
              style: TextStyle(
                fontSize: MediaQuery.of(context).size.width < 600 ? 13 : 15,
                color: AppTheme.textGray,
                height: 1.6,
              ),
            ),
            const SizedBox(height: 24),
          ],
          SizedBox(height: MediaQuery.of(context).padding.bottom + 32),
        ],
      ),
    );
  }
}

/// Badge animé pulsant pour le temps de préparation
class _PrepTimeBadge extends StatefulWidget {
  final int minutes;
  const _PrepTimeBadge({required this.minutes});

  @override
  State<_PrepTimeBadge> createState() => _PrepTimeBadgeState();
}

class _PrepTimeBadgeState extends State<_PrepTimeBadge>
    with SingleTickerProviderStateMixin {
  late AnimationController _controller;
  late Animation<double> _scale;
  late Animation<double> _glow;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      duration: const Duration(milliseconds: 1000),
      vsync: this,
    )..repeat(reverse: true);
    _scale = Tween<double>(
      begin: 0.96,
      end: 1.04,
    ).animate(CurvedAnimation(parent: _controller, curve: Curves.easeInOut));
    _glow = Tween<double>(
      begin: 0.3,
      end: 0.9,
    ).animate(CurvedAnimation(parent: _controller, curve: Curves.easeInOut));
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final isMobile = MediaQuery.of(context).size.width < 600;
    return AnimatedBuilder(
      animation: _controller,
      builder: (context, _) {
        return Transform.scale(
          scale: _scale.value,
          child: Container(
            padding: EdgeInsets.symmetric(
              horizontal: isMobile ? 10 : 14,
              vertical: isMobile ? 5 : 7,
            ),
            decoration: BoxDecoration(
              color: AppTheme.accentGold.withValues(alpha: 0.15),
              borderRadius: BorderRadius.circular(30),
              border: Border.all(
                color: AppTheme.accentGold.withValues(alpha: _glow.value),
                width: 1.5,
              ),
              boxShadow: [
                BoxShadow(
                  color: AppTheme.accentGold.withValues(
                    alpha: _glow.value * 0.4,
                  ),
                  blurRadius: 10,
                  spreadRadius: 1,
                ),
              ],
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Icon(
                  Icons.timer_outlined,
                  size: isMobile ? 15 : 18,
                  color: AppTheme.accentGold,
                ),
                const SizedBox(width: 6),
                Text(
                  '${widget.minutes} min',
                  style: TextStyle(
                    fontSize: isMobile ? 13 : 15,
                    fontWeight: FontWeight.bold,
                    color: AppTheme.accentGold,
                    letterSpacing: 0.5,
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }
}
