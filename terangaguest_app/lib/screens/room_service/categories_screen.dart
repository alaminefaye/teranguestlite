import 'package:flutter/material.dart';
import '../../config/theme.dart';
import '../../models/menu_category.dart';
import '../../services/room_service_api.dart';
import '../../widgets/category_card.dart';
import '../../widgets/cart_badge.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/error_state.dart';
import '../../utils/navigation_helper.dart';
import '../../utils/haptic_helper.dart';
import '../../utils/layout_helper.dart';
import 'items_screen.dart';

class CategoriesScreen extends StatefulWidget {
  const CategoriesScreen({super.key});

  @override
  State<CategoriesScreen> createState() => _CategoriesScreenState();
}

class _CategoriesScreenState extends State<CategoriesScreen> {
  final RoomServiceApi _roomServiceApi = RoomServiceApi();
  List<MenuCategory> _categories = [];
  bool _isLoading = true;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _loadCategories();
  }

  Future<void> _loadCategories() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final categories = await _roomServiceApi.getCategories(available: true);
      setState(() {
        _categories = categories;
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
              children: [
                Text(
                  AppLocalizations.of(context).roomService,
                  style: const TextStyle(
                    fontSize: 28,
                    fontWeight: FontWeight.bold,
                    color: Colors.white,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  AppLocalizations.of(context).chooseCategory,
                  style: TextStyle(fontSize: 14, color: AppTheme.textGray),
                ),
              ],
            ),
          ),

          // Badge panier
          const CartBadge(),
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
        onRetry: _loadCategories,
      );
    }

    if (_categories.isEmpty) {
      final l10n = AppLocalizations.of(context);
      return EmptyStateWidget(
        icon: Icons.restaurant_menu_outlined,
        title: l10n.noCategoryAvailable,
        subtitle: l10n.noCategoryHint,
      );
    }

    return RefreshIndicator(
      color: AppTheme.accentGold,
      onRefresh: _loadCategories,
      child: Center(
        child: Padding(
          padding: EdgeInsets.only(
            left: LayoutHelper.horizontalPaddingValue(context),
            right: LayoutHelper.horizontalPaddingValue(context),
            top: 24,
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
            itemCount: _categories.length,
            itemBuilder: (context, index) {
              final category = _categories[index];
              return CategoryCard(
                category: category,
                onTap: () {
                  HapticHelper.lightImpact();
                  context.navigateTo(ItemsScreen(category: category));
                },
              );
            },
          ),
        ),
      ),
    );
  }
}
