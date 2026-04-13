import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../utils/layout_helper.dart';
import '../../utils/translatable_text_helper.dart';
import '../../models/menu_category.dart';
import '../../models/menu_item.dart';
import '../../providers/locale_provider.dart';
import '../../services/room_service_api.dart';
import '../../widgets/menu_item_card.dart';
import '../../widgets/translatable_text.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/error_state.dart';
import '../../utils/navigation_helper.dart';
import '../../utils/haptic_helper.dart';
import 'item_detail_screen.dart';

class ItemsScreen extends StatefulWidget {
  final MenuCategory category;

  const ItemsScreen({super.key, required this.category});

  @override
  State<ItemsScreen> createState() => _ItemsScreenState();
}

class _ItemsScreenState extends State<ItemsScreen> {
  final RoomServiceApi _roomServiceApi = RoomServiceApi();
  final TextEditingController _searchController = TextEditingController();

  List<MenuItem> _items = [];
  bool _isLoading = true;
  String? _errorMessage;
  int _currentPage = 1;
  bool _hasMorePages = true;
  String _searchQuery = '';

  @override
  void initState() {
    super.initState();
    _loadItems();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadItems({bool loadMore = false}) async {
    if (!loadMore) {
      setState(() {
        _isLoading = true;
        _errorMessage = null;
        _currentPage = 1;
      });
    }

    try {
      final result = await _roomServiceApi.getItems(
        categoryId: widget.category.id,
        available: true,
        search: _searchQuery.isEmpty ? null : _searchQuery,
        page: _currentPage,
      );

      final items = result['items'] as List<MenuItem>;
      final meta = result['meta'] as Map<String, dynamic>? ?? {};
      final currentPage = meta['current_page'] as int? ?? 1;
      final lastPage = meta['last_page'] as int? ?? 1;

      setState(() {
        if (loadMore) {
          _items.addAll(items);
        } else {
          _items = items;
        }
        _isLoading = false;
        _hasMorePages = currentPage < lastPage;
      });
    } catch (e) {
      setState(() {
        _errorMessage = e.toString();
        _isLoading = false;
      });
    }
  }

  void _loadMoreItems() {
    if (_hasMorePages && !_isLoading) {
      setState(() {
        _currentPage++;
      });
      _loadItems(loadMore: true);
    }
  }

  void _performSearch(String query) {
    setState(() {
      _searchQuery = query;
    });
    _loadItems();
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

              // Barre de recherche
              _buildSearchBar(),

              // Liste des articles
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
              children: [
                TranslatableText(
                  widget.category.name,
                  locale: context.read<LocaleProvider>().languageCode,
                  style: TextStyle(
                    fontSize: MediaQuery.of(context).size.width < 600 ? 16 : 24,
                    fontWeight: FontWeight.bold,
                    color: AppTheme.accentGold,
                  ),
                ),
                if (TranslatableTextHelper.resolveDisplayTextSync(widget.category.description, context.read<LocaleProvider>().languageCode).trim().isNotEmpty) ...[
                  const SizedBox(height: 4),
                  TranslatableText(
                    widget.category.description,
                    locale: context.read<LocaleProvider>().languageCode,
                    style: const TextStyle(
                      fontSize: 13,
                      color: AppTheme.textGray,
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSearchBar() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 20.0),
      child: Container(
        decoration: BoxDecoration(
          color: AppTheme.primaryBlue.withValues(alpha: 0.5),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: AppTheme.accentGold.withValues(alpha: 0.3)),
        ),
        child: TextField(
          controller: _searchController,
          style: const TextStyle(color: Colors.white),
          decoration: InputDecoration(
            hintText: AppLocalizations.of(context).search,
            hintStyle: TextStyle(
              color: AppTheme.textGray.withValues(alpha: 0.6),
            ),
            prefixIcon: const Icon(Icons.search, color: AppTheme.accentGold),
            suffixIcon: _searchQuery.isNotEmpty
                ? IconButton(
                    icon: const Icon(Icons.clear, color: AppTheme.textGray),
                    onPressed: () {
                      _searchController.clear();
                      _performSearch('');
                    },
                  )
                : null,
            border: InputBorder.none,
            contentPadding: const EdgeInsets.symmetric(
              horizontal: 16,
              vertical: 14,
            ),
          ),
          onSubmitted: _performSearch,
        ),
      ),
    );
  }

  Widget _buildContent() {
    if (_isLoading && _items.isEmpty) {
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
        onRetry: _loadItems,
      );
    }

    if (_items.isEmpty) {
      final l10n = AppLocalizations.of(context);
      return EmptyStateWidget(
        icon: Icons.restaurant_outlined,
        title: _searchQuery.isEmpty
            ? l10n.noItemAvailable
            : l10n.noSearchResult,
        subtitle: _searchQuery.isEmpty
            ? l10n.noItemSubtitle
            : l10n.tryAnotherSearch,
      );
    }

    return RefreshIndicator(
      color: AppTheme.accentGold,
      onRefresh: _loadItems,
      child: NotificationListener<ScrollNotification>(
        onNotification: (ScrollNotification scrollInfo) {
          if (scrollInfo.metrics.pixels == scrollInfo.metrics.maxScrollExtent) {
            _loadMoreItems();
          }
          return false;
        },
        child: Padding(
          padding: EdgeInsets.only(
            left: LayoutHelper.horizontalPaddingValue(context),
            right: LayoutHelper.horizontalPaddingValue(context),
            top: 8,
            bottom: 24,
          ),
          child: GridView.builder(
            physics: const AlwaysScrollableScrollPhysics(),
            cacheExtent: 150,
            gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: LayoutHelper.gridCrossAxisCount(context),
              childAspectRatio: LayoutHelper.listCellAspectRatio(context),
              crossAxisSpacing: LayoutHelper.gridSpacing(context),
              mainAxisSpacing: LayoutHelper.gridSpacing(context),
            ),
            itemCount: _items.length + (_hasMorePages ? 1 : 0),
            itemBuilder: (context, index) {
              if (index == _items.length) {
                return const Center(
                  child: CircularProgressIndicator(
                    valueColor: AlwaysStoppedAnimation<Color>(
                      AppTheme.accentGold,
                    ),
                  ),
                );
              }

              final item = _items[index];
              return MenuItemCard(
                item: item,
                onTap: () {
                  HapticHelper.lightImpact();
                  context.navigateTo(ItemDetailScreen(item: item));
                },
              );
            },
          ),
        ),
      ),
    );
  }
}
