import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../config/theme.dart';
import '../../models/spa.dart';
import '../../models/favorite_item.dart';
import '../../providers/locale_provider.dart';
import '../../providers/spa_provider.dart';
import '../../providers/favorites_provider.dart';
import '../../utils/translatable_text_helper.dart';
import '../../widgets/translatable_text.dart';
import '../../utils/haptic_helper.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/error_state.dart';

class SpaServiceDetailScreen extends StatefulWidget {
  final int serviceId;

  const SpaServiceDetailScreen({super.key, required this.serviceId});

  @override
  State<SpaServiceDetailScreen> createState() => _SpaServiceDetailScreenState();
}

class _SpaServiceDetailScreenState extends State<SpaServiceDetailScreen> {
  SpaService? _service;
  bool _isLoading = true;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<FavoritesProvider>().load();
    });
    _loadServiceDetail();
  }

  Future<void> _loadServiceDetail() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final service = await context.read<SpaProvider>().fetchSpaServiceDetail(
        widget.serviceId,
      );
      setState(() {
        _service = service;
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
                  _service?.name ?? AppLocalizations.of(context).spaServiceFallback,
                  locale: context.read<LocaleProvider>().languageCode,
                  style: TextStyle(
                    fontSize: MediaQuery.of(context).size.width < 600 ? 18 : 28,
                    fontWeight: FontWeight.bold,
                    color: AppTheme.accentGold,
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
                const SizedBox(height: 4),
                Text(
                  _service?.category ?? '',
                  style: const TextStyle(
                    fontSize: 14,
                    color: AppTheme.textGray,
                  ),
                ),
              ],
            ),
          ),
          if (_service != null)
            Consumer<FavoritesProvider>(
              builder: (context, fav, _) {
                final isFav = fav.isFavorite(FavoriteType.spa, _service!.id);
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
                        type: FavoriteType.spa,
                        id: _service!.id,
                        name: TranslatableTextHelper.resolveDisplayTextSync(_service!.name, locale),
                        imageUrl: _service!.image,
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
        onRetry: _loadServiceDetail,
      );
    }

    if (_service == null) {
      final l10n = AppLocalizations.of(context);
      return EmptyStateWidget(
        icon: Icons.spa_outlined,
        title: l10n.serviceNotFound,
        subtitle: l10n.serviceNotFoundHint,
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
          _buildImage(),
          const SizedBox(height: 30),
          _buildMainInfo(),
        ],
      ),
    );
  }

  Widget _buildImage() {
    return ClipRRect(
      borderRadius: BorderRadius.circular(16),
      child: _service!.image != null
          ? CachedNetworkImage(
              imageUrl: _service!.image!,
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
        child: Icon(Icons.spa, size: 80, color: AppTheme.accentGold),
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
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  const Icon(
                    Icons.access_time,
                    size: 20,
                    color: AppTheme.accentGold,
                  ),
                  const SizedBox(width: 8),
                  Text(
                    _service!.formattedDuration,
                    style: const TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w600,
                      color: Colors.white,
                    ),
                  ),
                ],
              ),
              Text(
                _service!.formattedPrice,
                style: const TextStyle(
                  fontSize: 22,
                  fontWeight: FontWeight.w900,
                  color: AppTheme.accentGold,
                ),
              ),
            ],
          ),
          if (_service!.description != null) ...[
            const SizedBox(height: 16),
            const Divider(color: AppTheme.textGray, height: 1),
            const SizedBox(height: 16),
            TranslatableText(
              _service!.description,
              locale: context.read<LocaleProvider>().languageCode,
              style: const TextStyle(
                fontSize: 14,
                color: Colors.white,
                height: 1.5,
              ),
            ),
          ],
        ],
      ),
    );
  }

}
