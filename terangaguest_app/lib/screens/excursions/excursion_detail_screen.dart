import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../config/theme.dart';
import '../../models/excursion.dart';
import '../../models/favorite_item.dart';
import '../../providers/excursions_provider.dart';
import '../../providers/favorites_provider.dart';
import '../../utils/navigation_helper.dart';
import '../../utils/haptic_helper.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/error_state.dart';
import '../../widgets/animated_button.dart';
import 'book_excursion_screen.dart';

class ExcursionDetailScreen extends StatefulWidget {
  final int excursionId;

  const ExcursionDetailScreen({super.key, required this.excursionId});

  @override
  State<ExcursionDetailScreen> createState() => _ExcursionDetailScreenState();
}

class _ExcursionDetailScreenState extends State<ExcursionDetailScreen> {
  Excursion? _excursion;
  bool _isLoading = true;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<FavoritesProvider>().load();
    });
    _loadExcursionDetail();
  }

  Future<void> _loadExcursionDetail() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final excursion = await context
          .read<ExcursionsProvider>()
          .fetchExcursionDetail(widget.excursionId);
      setState(() {
        _excursion = excursion;
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
    final w = MediaQuery.sizeOf(context).width;
    final isMobile = w < 600;
    final titleSize = isMobile ? 20.0 : 24.0;
    final pad = isMobile ? 12.0 : 20.0;

    return Padding(
      padding: EdgeInsets.all(pad),
      child: Row(
        children: [
          IconButton(
            icon: const Icon(Icons.arrow_back, color: AppTheme.accentGold),
            onPressed: () {
              HapticHelper.lightImpact();
              Navigator.pop(context);
            },
          ),
          SizedBox(width: isMobile ? 8 : 12),
          Expanded(
            child: Text(
              _excursion?.name ?? 'Excursion',
              style: TextStyle(
                fontSize: titleSize,
                fontWeight: FontWeight.bold,
                color: AppTheme.accentGold,
              ),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ),
          if (_excursion != null)
            Consumer<FavoritesProvider>(
              builder: (context, fav, _) {
                final isFav = fav.isFavorite(
                  FavoriteType.excursion,
                  _excursion!.id,
                );
                return IconButton(
                  icon: Icon(
                    isFav ? Icons.favorite : Icons.favorite_border,
                    color: isFav ? Colors.red : AppTheme.accentGold,
                  ),
                  onPressed: () {
                    HapticHelper.lightImpact();
                    fav.toggle(
                      FavoriteItem(
                        type: FavoriteType.excursion,
                        id: _excursion!.id,
                        name: _excursion!.name,
                        imageUrl: _excursion!.image,
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
        onRetry: _loadExcursionDetail,
      );
    }

    if (_excursion == null) {
      final l10n = AppLocalizations.of(context);
      return EmptyStateWidget(
        icon: Icons.landscape_outlined,
        title: l10n.excursionNotFound,
        subtitle: l10n.excursionNotFoundHint,
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
          ClipRRect(
            borderRadius: BorderRadius.circular(16),
            child: _excursion!.image != null
                ? CachedNetworkImage(
                    imageUrl: _excursion!.image!,
                    height: 300,
                    width: double.infinity,
                    fit: BoxFit.cover,
                    placeholder: (context, url) => Container(
                      height: 300,
                      color: AppTheme.primaryBlue.withValues(alpha: 0.3),
                      child: const Center(
                        child: Icon(
                          Icons.landscape,
                          size: 80,
                          color: AppTheme.accentGold,
                        ),
                      ),
                    ),
                    errorWidget: (context, url, error) => Container(
                      height: 300,
                      color: AppTheme.primaryBlue.withValues(alpha: 0.3),
                      child: const Center(
                        child: Icon(
                          Icons.landscape,
                          size: 80,
                          color: AppTheme.accentGold,
                        ),
                      ),
                    ),
                  )
                : Container(
                    height: 300,
                    color: AppTheme.primaryBlue.withValues(alpha: 0.3),
                    child: const Center(
                      child: Icon(
                        Icons.landscape,
                        size: 80,
                        color: AppTheme.accentGold,
                      ),
                    ),
                  ),
          ),
          const SizedBox(height: 30),
          Container(
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
                          _excursion!.formattedDuration,
                          style: const TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w600,
                            color: Colors.white,
                          ),
                        ),
                      ],
                    ),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        Text(
                          AppLocalizations.of(
                            context,
                          ).adultPrice(_excursion!.formattedPriceAdult),
                          style: const TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                            color: AppTheme.accentGold,
                          ),
                        ),
                        Text(
                          AppLocalizations.of(
                            context,
                          ).childPrice(_excursion!.formattedPriceChild),
                          style: const TextStyle(
                            fontSize: 14,
                            color: AppTheme.textGray,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
                if (_excursion!.description != null) ...[
                  const SizedBox(height: 16),
                  const Divider(color: AppTheme.textGray, height: 1),
                  const SizedBox(height: 16),
                  Text(
                    _excursion!.description!,
                    style: const TextStyle(
                      fontSize: 14,
                      color: Colors.white,
                      height: 1.5,
                    ),
                  ),
                ],
                if (_excursion!.inclusions != null &&
                    _excursion!.inclusions!.isNotEmpty) ...[
                  const SizedBox(height: 16),
                  const Divider(color: AppTheme.textGray, height: 1),
                  const SizedBox(height: 16),
                  Text(
                    AppLocalizations.of(context).included,
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                      color: AppTheme.accentGold,
                    ),
                  ),
                  const SizedBox(height: 12),
                  ...(_excursion!.inclusions!.map(
                    (item) => Padding(
                      padding: const EdgeInsets.only(bottom: 8.0),
                      child: Row(
                        children: [
                          const Icon(
                            Icons.check_circle,
                            size: 16,
                            color: AppTheme.accentGold,
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              item,
                              style: const TextStyle(
                                fontSize: 14,
                                color: Colors.white,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  )),
                ],
              ],
            ),
          ),
          const SizedBox(height: 30),
          AnimatedButton(
            text: _excursion!.isAvailable
                ? AppLocalizations.of(context).book
                : AppLocalizations.of(context).unavailable,
            onPressed: _excursion!.isAvailable
                ? () {
                    HapticHelper.confirm();
                    context.navigateTo(
                      BookExcursionScreen(excursion: _excursion!),
                    );
                  }
                : null,
            width: double.infinity,
            height: 56,
            backgroundColor: AppTheme.accentGold,
            textColor: AppTheme.primaryDark,
            enableHaptic: false,
          ),
        ],
      ),
    );
  }
}
