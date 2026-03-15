import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../config/theme.dart';
import '../../models/restaurant.dart';
import '../../models/favorite_item.dart';
import '../../providers/locale_provider.dart';
import '../../providers/restaurants_provider.dart';
import '../../providers/favorites_provider.dart';
import '../../utils/translatable_text_helper.dart';
import '../../widgets/translatable_text.dart';
import '../../utils/navigation_helper.dart';
import '../../utils/haptic_helper.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/error_state.dart';
import '../../widgets/animated_button.dart';
import 'reserve_restaurant_screen.dart';

class RestaurantDetailScreen extends StatefulWidget {
  final int restaurantId;

  const RestaurantDetailScreen({super.key, required this.restaurantId});

  @override
  State<RestaurantDetailScreen> createState() => _RestaurantDetailScreenState();
}

class _RestaurantDetailScreenState extends State<RestaurantDetailScreen> {
  Restaurant? _restaurant;
  bool _isLoading = true;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<FavoritesProvider>().load();
    });
    _loadRestaurantDetail();
  }

  Future<void> _loadRestaurantDetail() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final restaurant = await context
          .read<RestaurantsProvider>()
          .fetchRestaurantDetail(widget.restaurantId);
      setState(() {
        _restaurant = restaurant;
        _isLoading = false;
      });
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
              _buildHeader(),
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
          IconButton(
            icon: const Icon(Icons.arrow_back, color: AppTheme.accentGold),
            onPressed: () {
              HapticHelper.lightImpact();
              Navigator.pop(context);
            },
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                TranslatableText(
                  _restaurant?.name ?? AppLocalizations.of(context).restaurant,
                  locale: context.read<LocaleProvider>().languageCode,
                  style: TextStyle(
                    fontSize: MediaQuery.of(context).size.width < 600 ? 16 : 24,
                    fontWeight: FontWeight.bold,
                    color: AppTheme.accentGold,
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
                const SizedBox(height: 4),
                Text(
                  _restaurant?.type ?? '',
                  style: const TextStyle(
                    fontSize: 13,
                    color: AppTheme.textGray,
                  ),
                ),
              ],
            ),
          ),
          if (_restaurant != null)
            Consumer<FavoritesProvider>(
              builder: (context, fav, _) {
                final isFav = fav.isFavorite(
                  FavoriteType.restaurant,
                  _restaurant!.id,
                );
                return IconButton(
                  icon: Icon(
                    isFav ? Icons.favorite : Icons.favorite_border,
                    color: isFav ? Colors.red : AppTheme.accentGold,
                  ),
                  onPressed: () {
                    HapticHelper.lightImpact();
                    final locale = context.read<LocaleProvider>().languageCode;
                    fav.toggle(
                      FavoriteItem(
                        type: FavoriteType.restaurant,
                        id: _restaurant!.id,
                        name: TranslatableTextHelper.resolveDisplayTextSync(_restaurant!.name, locale),
                        imageUrl: _restaurant!.image,
                      ),
                    );
                  },
                );
              },
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
        onRetry: _loadRestaurantDetail,
      );
    }

    if (_restaurant == null) {
      final l10n = AppLocalizations.of(context);
      return EmptyStateWidget(
        icon: Icons.restaurant_outlined,
        title: l10n.restaurantNotFound,
        subtitle: l10n.restaurantNotFoundHint,
      );
    }

    return SingleChildScrollView(
      padding: EdgeInsets.symmetric(
        horizontal: MediaQuery.of(context).size.width < 600 ? 16 : 60,
        vertical: 20,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Image
          _buildImage(),

          const SizedBox(height: 30),

          // Informations principales
          _buildMainInfo(),

          const SizedBox(height: 30),

          // Horaires
          if (_restaurant!.openingHours != null) ...[
            _buildOpeningHours(),
            const SizedBox(height: 30),
          ],

          // Commodités
          if (_restaurant!.amenities != null &&
              _restaurant!.amenities!.isNotEmpty) ...[
            _buildAmenities(),
            const SizedBox(height: 30),
          ],

          // Bouton Réserver
          _buildReserveButton(),
        ],
      ),
    );
  }

  Widget _buildImage() {
    return ClipRRect(
      borderRadius: BorderRadius.circular(16),
      child: _restaurant!.image != null
          ? CachedNetworkImage(
              imageUrl: _restaurant!.image!,
              height: 300,
              width: double.infinity,
              fit: BoxFit.cover,
              placeholder: (context, url) => _buildPlaceholder(),
              errorWidget: (context, url, error) => _buildPlaceholder(),
            )
          : _buildPlaceholder(),
    );
  }

  Widget _buildPlaceholder() {
    return Container(
      height: 300,
      width: double.infinity,
      color: AppTheme.primaryBlue.withValues(alpha: 0.3),
      child: const Center(
        child: Icon(Icons.restaurant, size: 80, color: AppTheme.accentGold),
      ),
    );
  }

  Widget _buildMainInfo() {
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
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Type et Cuisine
          if (_restaurant!.type != null || _restaurant!.cuisine != null)
            Row(
              children: [
                if (_restaurant!.type != null) ...[
                  const Icon(
                    Icons.category,
                    size: 16,
                    color: AppTheme.accentGold,
                  ),
                  const SizedBox(width: 8),
                  Text(
                    _restaurant!.type!,
                    style: const TextStyle(
                      fontSize: 14,
                      color: AppTheme.textGray,
                    ),
                  ),
                ],
                if (_restaurant!.type != null && _restaurant!.cuisine != null)
                  const Padding(
                    padding: EdgeInsets.symmetric(horizontal: 12),
                    child: Text(
                      '•',
                      style: TextStyle(color: AppTheme.textGray),
                    ),
                  ),
                if (_restaurant!.cuisine != null) ...[
                  const Icon(
                    Icons.restaurant_menu,
                    size: 16,
                    color: AppTheme.accentGold,
                  ),
                  const SizedBox(width: 8),
                  Text(
                    _restaurant!.cuisine!,
                    style: const TextStyle(
                      fontSize: 14,
                      color: AppTheme.textGray,
                    ),
                  ),
                ],
              ],
            ),

          if (TranslatableTextHelper.resolveDisplayTextSync(_restaurant!.description, context.read<LocaleProvider>().languageCode).trim().isNotEmpty) ...[
            const SizedBox(height: 16),
            const Divider(color: AppTheme.textGray, height: 1),
            const SizedBox(height: 16),
            TranslatableText(
              _restaurant!.description,
              locale: context.read<LocaleProvider>().languageCode,
              style: const TextStyle(
                fontSize: 14,
                color: Colors.white,
                height: 1.5,
              ),
            ),
          ],

          if (_restaurant!.capacity != null) ...[
            const SizedBox(height: 16),
            const Divider(color: AppTheme.textGray, height: 1),
            const SizedBox(height: 16),
            Row(
              children: [
                const Icon(Icons.people, size: 20, color: AppTheme.accentGold),
                const SizedBox(width: 12),
                Text(
                  'Capacité : ${_restaurant!.capacity} personnes',
                  style: const TextStyle(
                    fontSize: 14,
                    color: Colors.white,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ],
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildOpeningHours() {
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
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            AppLocalizations.of(context).openingHours,
            style: const TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.bold,
              color: AppTheme.accentGold,
            ),
          ),
          const SizedBox(height: 16),
          ..._restaurant!.openingHours!.entries.map((entry) {
            return Padding(
              padding: const EdgeInsets.only(bottom: 8.0),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    _getDayLabel(entry.key),
                    style: const TextStyle(
                      fontSize: 14,
                      color: AppTheme.textGray,
                    ),
                  ),
                  Text(
                    entry.value,
                    style: const TextStyle(
                      fontSize: 14,
                      color: Colors.white,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ],
              ),
            );
          }),
        ],
      ),
    );
  }

  Widget _buildAmenities() {
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
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            AppLocalizations.of(context).amenities,
            style: const TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.bold,
              color: AppTheme.accentGold,
            ),
          ),
          const SizedBox(height: 16),
          Wrap(
            spacing: 10,
            runSpacing: 10,
            children: _restaurant!.amenities!.map((amenity) {
              return Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 12,
                  vertical: 6,
                ),
                decoration: BoxDecoration(
                  color: AppTheme.accentGold.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(
                    color: AppTheme.accentGold.withValues(alpha: 0.5),
                  ),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(
                      Icons.check_circle,
                      size: 14,
                      color: AppTheme.accentGold,
                    ),
                    const SizedBox(width: 6),
                    Text(
                      amenity,
                      style: const TextStyle(fontSize: 12, color: Colors.white),
                    ),
                  ],
                ),
              );
            }).toList(),
          ),
        ],
      ),
    );
  }

  Widget _buildReserveButton() {
    final l10n = AppLocalizations.of(context);
    return AnimatedButton(
      text: _restaurant!.isOpen ? l10n.bookTable : l10n.closed,
      onPressed: _restaurant!.isOpen
          ? () {
              HapticHelper.confirm();
              context.navigateTo(
                ReserveRestaurantScreen(restaurant: _restaurant!),
              );
            }
          : null,
      width: double.infinity,
      height: 56,
      backgroundColor: AppTheme.accentGold,
      textColor: AppTheme.primaryDark,
      enableHaptic: false,
    );
  }

  String _getDayLabel(String day) {
    final l10n = AppLocalizations.of(context);
    final labels = {
      'monday': l10n.dayMonday,
      'tuesday': l10n.dayTuesday,
      'wednesday': l10n.dayWednesday,
      'thursday': l10n.dayThursday,
      'friday': l10n.dayFriday,
      'saturday': l10n.daySaturday,
      'sunday': l10n.daySunday,
    };
    return labels[day.toLowerCase()] ?? day;
  }
}
