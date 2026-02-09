import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../models/palace.dart';
import '../../utils/layout_helper.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../providers/palace_provider.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/error_state.dart';
import '../../utils/navigation_helper.dart';
import '../../utils/haptic_helper.dart';
import 'create_palace_request_screen.dart';

class PalaceListScreen extends StatefulWidget {
  const PalaceListScreen({super.key});

  @override
  State<PalaceListScreen> createState() => _PalaceListScreenState();
}

class _PalaceListScreenState extends State<PalaceListScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<PalaceProvider>().fetchPalaceServices();
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
              Padding(
                padding: const EdgeInsets.all(20.0),
                child: Row(
                  children: [
                    IconButton(
                      icon: const Icon(
                        Icons.arrow_back,
                        color: AppTheme.accentGold,
                      ),
                      onPressed: () => Navigator.pop(context),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            AppLocalizations.of(context).palaceServices,
                            style: const TextStyle(
                              fontSize: 24,
                              fontWeight: FontWeight.bold,
                              color: Colors.white,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            AppLocalizations.of(context).palaceServicesSubtitle,
                            style: const TextStyle(
                              fontSize: 13,
                              color: AppTheme.textGray,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              Expanded(child: _buildContent()),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildContent() {
    return Consumer<PalaceProvider>(
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
            onRetry: () => provider.refreshPalaceServices(),
          );
        }

        if (provider.services.isEmpty) {
          final l10n = AppLocalizations.of(context);
          return EmptyStateWidget(
            icon: Icons.star_outline,
            title: l10n.noPalaceService,
            subtitle: l10n.noPalaceServiceHint,
          );
        }

        return RefreshIndicator(
          color: AppTheme.accentGold,
          onRefresh: provider.refreshPalaceServices,
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
                itemCount: provider.services.length,
                itemBuilder: (context, index) {
                  final service = provider.services[index];
                  return GestureDetector(
                    onTap: service.isAvailable
                        ? () {
                            HapticHelper.lightImpact();
                            context.navigateTo(
                              CreatePalaceRequestScreen(service: service),
                            );
                          }
                        : null,
                    child: Transform(
                      transform: Matrix4.identity()
                        ..setEntry(3, 2, 0.001)
                        ..rotateX(-0.05)
                        ..rotateY(0.02),
                      alignment: Alignment.center,
                      child: Container(
                        decoration: BoxDecoration(
                          gradient: LinearGradient(
                            begin: Alignment.topLeft,
                            end: Alignment.bottomRight,
                            colors: [
                              AppTheme.primaryBlue,
                              AppTheme.primaryDark,
                            ],
                          ),
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(
                            color: AppTheme.accentGold,
                            width: 1.5,
                          ),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withValues(alpha: 0.4),
                              blurRadius: 20,
                              spreadRadius: 2,
                              offset: const Offset(0, 10),
                            ),
                            BoxShadow(
                              color: AppTheme.accentGold.withValues(alpha: 0.1),
                              blurRadius: 15,
                              spreadRadius: -2,
                              offset: const Offset(0, -4),
                            ),
                          ],
                        ),
                        child: Padding(
                          padding: const EdgeInsets.all(12.0),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.stretch,
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Expanded(
                                child: ClipRRect(
                                  borderRadius: BorderRadius.circular(10),
                                  child: _buildServiceImage(service),
                                ),
                              ),
                              const SizedBox(height: 12),
                              Text(
                                service.name,
                                style: const TextStyle(
                                  fontSize: 14,
                                  fontWeight: FontWeight.bold,
                                  color: AppTheme.accentGold,
                                ),
                                textAlign: TextAlign.center,
                                maxLines: 2,
                                overflow: TextOverflow.ellipsis,
                              ),
                              if (service.category != null) ...[
                                const SizedBox(height: 4),
                                Text(
                                  service.category!,
                                  style: const TextStyle(
                                    fontSize: 11,
                                    color: AppTheme.textGray,
                                  ),
                                  textAlign: TextAlign.center,
                                ),
                              ],
                            ],
                          ),
                        ),
                      ),
                    ),
                  );
                },
              ),
            ),
          ),
        );
      },
    );
  }

  /// Image du serveur qui occupe tout l'espace, ou icône par défaut centrée
  Widget _buildServiceImage(PalaceService service) {
    final fallbackIcon = Center(
      child: Icon(
        _iconForPalaceService(service),
        size: 48,
        color: AppTheme.accentGold,
      ),
    );
    final imageUrl = service.image?.trim();
    if (imageUrl == null || imageUrl.isEmpty) {
      return Container(
        color: AppTheme.primaryDark.withValues(alpha: 0.5),
        child: fallbackIcon,
      );
    }

    return Image.network(
      imageUrl,
      fit: BoxFit.cover,
      width: double.infinity,
      height: double.infinity,
      errorBuilder: (_, __, ___) => Container(
        color: AppTheme.primaryDark.withValues(alpha: 0.5),
        child: fallbackIcon,
      ),
      loadingBuilder: (context, child, loadingProgress) {
        if (loadingProgress == null) return child;
        return Container(
          color: AppTheme.primaryDark.withValues(alpha: 0.5),
          child: fallbackIcon,
        );
      },
    );
  }

  /// Icône spécifique selon le nom ou la catégorie du service Palace
  static IconData _iconForPalaceService(PalaceService service) {
    final name = service.name.toLowerCase();
    final cat = (service.category ?? '').toLowerCase();
    if (name.contains('baby') ||
        name.contains('enfant') ||
        name.contains('garderie'))
      return Icons.child_care;
    if (name.contains('billetterie') ||
        name.contains('spectacle') ||
        name.contains('ticket'))
      return Icons.confirmation_number;
    if (name.contains('voiture') ||
        name.contains('chauffeur') ||
        name.contains('location'))
      return Icons.directions_car;
    if (name.contains('événement') ||
        name.contains('event') ||
        name.contains('organisation'))
      return Icons.event;
    if (name.contains('pressing') ||
        name.contains('repassage') ||
        name.contains('laundry'))
      return Icons.local_laundry_service;
    if (name.contains('majordome') || name.contains('butler'))
      return Icons.support_agent;
    if (name.contains('transfert') ||
        name.contains('aéroport') ||
        name.contains('airport'))
      return Icons.flight_takeoff;
    if (name.contains('conciergerie') || name.contains('vip'))
      return Icons.luggage;
    if (cat == 'transport') return Icons.directions_car;
    if (cat == 'butler') return Icons.support_agent;
    if (cat == 'vip') return Icons.star;
    return Icons.auto_awesome;
  }
}
