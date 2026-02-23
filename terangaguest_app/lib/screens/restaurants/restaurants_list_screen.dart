import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../providers/restaurants_provider.dart';
import '../../widgets/restaurant_card.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/error_state.dart';
import '../../utils/navigation_helper.dart';
import '../../utils/haptic_helper.dart';
import '../../utils/layout_helper.dart';
import 'restaurant_detail_screen.dart';

class RestaurantsListScreen extends StatefulWidget {
  const RestaurantsListScreen({super.key});

  @override
  State<RestaurantsListScreen> createState() => _RestaurantsListScreenState();
}

class _RestaurantsListScreenState extends State<RestaurantsListScreen> {
  String? _selectedType;

  List<Map<String, String>> _typeFilters(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return [
      {'value': '', 'label': l10n.filterAllTypes},
      {'value': 'restaurant', 'label': l10n.filterRestaurants},
      {'value': 'bar', 'label': l10n.filterBars},
      {'value': 'cafe', 'label': l10n.filterCafes},
      {'value': 'lounge', 'label': l10n.filterLounges},
    ];
  }

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<RestaurantsProvider>().fetchRestaurants();
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
              _buildHeader(),
              _buildFilters(),
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
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  AppLocalizations.of(context).restaurantsBars,
                  style: TextStyle(
                    fontSize: MediaQuery.of(context).size.width < 600 ? 18 : 28,
                    fontWeight: FontWeight.bold,
                    color: AppTheme.accentGold,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  AppLocalizations.of(context).discoverRestaurants,
                  style: const TextStyle(
                    fontSize: 14,
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
        itemCount: _typeFilters(context).length,
        itemBuilder: (context, index) {
          final filter = _typeFilters(context)[index];
          final isSelected =
              _selectedType == filter['value'] ||
              (_selectedType == null && filter['value'] == '');

          return Padding(
            padding: const EdgeInsets.only(right: 10),
            child: GestureDetector(
              onTap: () {
                setState(() {
                  _selectedType = filter['value']!.isEmpty
                      ? null
                      : filter['value'];
                });
                context.read<RestaurantsProvider>().fetchRestaurants(
                  type: _selectedType,
                );
              },
              child: Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 20,
                  vertical: 12,
                ),
                decoration: BoxDecoration(
                  gradient: isSelected
                      ? LinearGradient(
                          colors: [
                            AppTheme.accentGold,
                            AppTheme.accentGold.withValues(alpha: 0.8),
                          ],
                        )
                      : null,
                  color: isSelected
                      ? null
                      : AppTheme.primaryBlue.withValues(alpha: 0.5),
                  borderRadius: BorderRadius.circular(25),
                  border: Border.all(
                    color: isSelected
                        ? AppTheme.accentGold
                        : AppTheme.accentGold.withValues(alpha: 0.3),
                  ),
                ),
                child: Text(
                  filter['label']!,
                  style: TextStyle(
                    color: isSelected
                        ? AppTheme.primaryDark
                        : AppTheme.textGray,
                    fontWeight: isSelected
                        ? FontWeight.bold
                        : FontWeight.normal,
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
    return Consumer<RestaurantsProvider>(
      builder: (context, provider, child) {
        if (provider.isLoading) {
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
            onRetry: () => provider.refreshRestaurants(),
          );
        }

        if (provider.restaurants.isEmpty) {
          final l10n = AppLocalizations.of(context);
          return EmptyStateWidget(
            icon: Icons.restaurant_outlined,
            title: _selectedType == null
                ? l10n.noRestaurantAvailable
                : l10n.noRestaurantForType(
                    _getTypeLabel(context, _selectedType!),
                  ),
            subtitle: l10n.noRestaurantSubtitle,
          );
        }

        return RefreshIndicator(
          color: AppTheme.accentGold,
          onRefresh: provider.refreshRestaurants,
          child: Align(
            alignment: Alignment.topCenter,
            child: Padding(
              padding: EdgeInsets.only(
                left: LayoutHelper.horizontalPaddingValue(context),
                right: LayoutHelper.horizontalPaddingValue(context),
                top: 12,
                bottom: 24,
              ),
              child: GridView.builder(
                shrinkWrap: true,
                physics: const AlwaysScrollableScrollPhysics(),
                gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                  crossAxisCount: LayoutHelper.gridCrossAxisCount(context),
                  childAspectRatio: LayoutHelper.listCellAspectRatio(context),
                  crossAxisSpacing: LayoutHelper.gridSpacing(context),
                  mainAxisSpacing: LayoutHelper.gridSpacing(context),
                ),
                itemCount: provider.restaurants.length,
                itemBuilder: (context, index) {
                  final restaurant = provider.restaurants[index];
                  return RestaurantCard(
                    restaurant: restaurant,
                    onTap: () {
                      HapticHelper.lightImpact();
                      context.navigateTo(
                        RestaurantDetailScreen(restaurantId: restaurant.id),
                      );
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

  String _getTypeLabel(BuildContext context, String type) {
    final l10n = AppLocalizations.of(context);
    switch (type) {
      case 'restaurant':
        return l10n.typeRestaurant;
      case 'bar':
        return l10n.typeBar;
      case 'cafe':
        return l10n.typeCafe;
      case 'lounge':
        return l10n.typeLounge;
      default:
        return type;
    }
  }
}
