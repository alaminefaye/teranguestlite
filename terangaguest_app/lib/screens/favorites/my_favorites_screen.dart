import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../config/theme.dart';
import '../../models/favorite_item.dart';
import '../../providers/favorites_provider.dart';
import '../../services/room_service_api.dart';
import '../../utils/navigation_helper.dart';
import '../../utils/haptic_helper.dart';
import '../room_service/item_detail_screen.dart';
import '../restaurants/restaurant_detail_screen.dart';
import '../spa/spa_service_detail_screen.dart';
import '../excursions/excursion_detail_screen.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../widgets/empty_state.dart';

class MyFavoritesScreen extends StatefulWidget {
  const MyFavoritesScreen({super.key});

  @override
  State<MyFavoritesScreen> createState() => _MyFavoritesScreenState();
}

class _MyFavoritesScreenState extends State<MyFavoritesScreen> {
  final RoomServiceApi _roomServiceApi = RoomServiceApi();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<FavoritesProvider>().load();
    });
  }

  Future<void> _openFavorite(BuildContext context, FavoriteItem fav) async {
    HapticHelper.lightImpact();
    switch (fav.type) {
      case FavoriteType.menuItem:
        try {
          final item = await _roomServiceApi.getItemDetails(fav.id);
          if (!context.mounted) return;
          context.navigateTo(ItemDetailScreen(item: item));
        } catch (e) {
          if (!context.mounted) return;
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(AppLocalizations.of(context).itemNotFound),
              backgroundColor: AppTheme.primaryBlue,
            ),
          );
        }
        break;
      case FavoriteType.restaurant:
        context.navigateTo(RestaurantDetailScreen(restaurantId: fav.id));
        break;
      case FavoriteType.spa:
        context.navigateTo(SpaServiceDetailScreen(serviceId: fav.id));
        break;
      case FavoriteType.excursion:
        context.navigateTo(ExcursionDetailScreen(excursionId: fav.id));
        break;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(gradient: AppTheme.backgroundGradient),
        child: SafeArea(
          child: Column(
            children: [
              _buildHeader(),
              Expanded(
                child: Consumer<FavoritesProvider>(
                  builder: (context, provider, _) {
                    if (!provider.isLoaded) {
                      return const Center(
                        child: CircularProgressIndicator(
                          valueColor: AlwaysStoppedAnimation<Color>(
                            AppTheme.accentGold,
                          ),
                        ),
                      );
                    }
                    if (provider.items.isEmpty) {
                      final l10n = AppLocalizations.of(context);
                      return EmptyStateWidget(
                        icon: Icons.favorite_border,
                        title: l10n.noFavorites,
                        subtitle: l10n.noFavoritesHint,
                      );
                    }
                    return ListView.builder(
                      padding: const EdgeInsets.all(20),
                      itemCount: provider.items.length,
                      itemBuilder: (context, index) {
                        final fav = provider.items[index];
                        return _buildFavoriteTile(context, fav, provider);
                      },
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

  Widget _buildHeader() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 12),
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
          Text(
            AppLocalizations.of(context).myFavorites,
            style: const TextStyle(
              fontSize: 28,
              fontWeight: FontWeight.bold,
              color: Colors.white,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFavoriteTile(
    BuildContext context,
    FavoriteItem fav,
    FavoritesProvider provider,
  ) {
    final IconData icon;
    switch (fav.type) {
      case FavoriteType.menuItem:
        icon = Icons.restaurant;
        break;
      case FavoriteType.restaurant:
        icon = Icons.restaurant_menu;
        break;
      case FavoriteType.spa:
        icon = Icons.spa;
        break;
      case FavoriteType.excursion:
        icon = Icons.landscape;
        break;
    }
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: () => _openFavorite(context, fav),
          borderRadius: BorderRadius.circular(12),
          child: Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: AppTheme.primaryBlue.withValues(alpha: 0.5),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(
                color: AppTheme.accentGold.withValues(alpha: 0.3),
              ),
            ),
            child: Row(
              children: [
                ClipRRect(
                  borderRadius: BorderRadius.circular(10),
                  child: fav.imageUrl != null && fav.imageUrl!.isNotEmpty
                      ? CachedNetworkImage(
                          imageUrl: fav.imageUrl!,
                          width: 56,
                          height: 56,
                          fit: BoxFit.cover,
                          placeholder: (context, url) => _placeholder(56, icon),
                          errorWidget: (context, url, error) =>
                              _placeholder(56, icon),
                        )
                      : _placeholder(56, icon),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Text(
                    fav.name,
                    style: const TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w600,
                      color: Colors.white,
                    ),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
                IconButton(
                  icon: const Icon(Icons.favorite, color: Colors.red, size: 28),
                  onPressed: () {
                    HapticHelper.lightImpact();
                    provider.remove(fav.type, fav.id);
                  },
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _placeholder(double size, IconData icon) {
    return Container(
      width: size,
      height: size,
      color: AppTheme.primaryBlue.withValues(alpha: 0.5),
      child: Icon(icon, color: AppTheme.accentGold, size: size * 0.5),
    );
  }
}
