import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../models/laundry.dart';
import '../../utils/layout_helper.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../providers/laundry_provider.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/error_state.dart';
import '../../utils/navigation_helper.dart';
import '../../utils/haptic_helper.dart';
import '../../widgets/animated_button.dart';
import 'create_laundry_request_screen.dart';
import '../../widgets/translatable_text.dart';
import '../../utils/translatable_text_helper.dart';

class LaundryListScreen extends StatefulWidget {
  const LaundryListScreen({super.key});

  @override
  State<LaundryListScreen> createState() => _LaundryListScreenState();
}

class _LaundryListScreenState extends State<LaundryListScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<LaundryProvider>().fetchLaundryServices();
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
                            AppLocalizations.of(context).laundry,
                            style: TextStyle(
                              fontSize: MediaQuery.of(context).size.width < 600
                                  ? 16
                                  : 24,
                              fontWeight: FontWeight.bold,
                              color: AppTheme.accentGold,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            AppLocalizations.of(context).laundrySubtitle,
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
    return Consumer<LaundryProvider>(
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
            onRetry: () => provider.refreshLaundryServices(),
          );
        }

        if (provider.services.isEmpty) {
          final l10n = AppLocalizations.of(context);
          return EmptyStateWidget(
            icon: Icons.local_laundry_service_outlined,
            title: l10n.noLaundryService,
            subtitle: l10n.noLaundryServiceHint,
          );
        }

        return Column(
          children: [
            Expanded(
              child: Padding(
                padding: EdgeInsets.only(
                  left: LayoutHelper.horizontalPaddingValue(context),
                  right: LayoutHelper.horizontalPaddingValue(context),
                  top: 24,
                  bottom: 24,
                ),
                child: GridView.builder(
                  gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                    crossAxisCount: LayoutHelper.gridCrossAxisCount(context),
                    childAspectRatio: _laundryCardAspectRatio(context),
                    crossAxisSpacing: LayoutHelper.gridSpacing(context),
                    mainAxisSpacing: LayoutHelper.gridSpacing(context),
                  ),
                  itemCount: provider.services.length,
                  itemBuilder: (context, index) {
                    final service = provider.services[index];
                    final quantity = provider.getQuantityForService(service.id);

                    return Transform(
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
                          padding: const EdgeInsets.symmetric(
                            horizontal: 10,
                            vertical: 12,
                          ),
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Column(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Icon(
                                    _iconForLaundryService(service),
                                    size: 40,
                                    color: AppTheme.accentGold,
                                  ),
                                  const SizedBox(height: 6),
                                  TranslatableText(
                                    service.name,
                                    locale: Localizations.localeOf(context).languageCode,
                                    style: const TextStyle(
                                      fontSize: 14,
                                      fontWeight: FontWeight.bold,
                                      color: AppTheme.accentGold,
                                    ),
                                    textAlign: TextAlign.center,
                                    maxLines: 2,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    '${service.pricePerItem.toStringAsFixed(0)} ${AppLocalizations.of(context).currencyFcfaPerPiece}',
                                    style: const TextStyle(
                                      fontSize: 11,
                                      color: AppTheme.textGray,
                                    ),
                                    textAlign: TextAlign.center,
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                ],
                              ),
                              const SizedBox(height: 8),
                              Row(
                                mainAxisAlignment: MainAxisAlignment.center,
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  IconButton(
                                    onPressed: quantity > 0
                                        ? () => provider.updateQuantity(
                                            service.id,
                                            quantity - 1,
                                          )
                                        : null,
                                    icon: const Icon(
                                      Icons.remove_circle_outline,
                                    ),
                                    color: AppTheme.accentGold,
                                    iconSize: 24,
                                    style: IconButton.styleFrom(
                                      minimumSize: const Size(36, 36),
                                      padding: EdgeInsets.zero,
                                    ),
                                  ),
                                  Container(
                                    width: 44,
                                    padding: const EdgeInsets.symmetric(
                                      vertical: 6,
                                    ),
                                    decoration: BoxDecoration(
                                      color: AppTheme.accentGold.withValues(
                                        alpha: 0.2,
                                      ),
                                      borderRadius: BorderRadius.circular(8),
                                      border: Border.all(
                                        color: AppTheme.accentGold,
                                      ),
                                    ),
                                    child: Text(
                                      '$quantity',
                                      textAlign: TextAlign.center,
                                      style: const TextStyle(
                                        fontSize: 16,
                                        fontWeight: FontWeight.bold,
                                        color: AppTheme.accentGold,
                                      ),
                                    ),
                                  ),
                                  IconButton(
                                    onPressed: quantity < 99
                                        ? () => provider.updateQuantity(
                                            service.id,
                                            quantity + 1,
                                          )
                                        : null,
                                    icon: const Icon(Icons.add_circle_outline),
                                    color: AppTheme.accentGold,
                                    iconSize: 24,
                                    style: IconButton.styleFrom(
                                      minimumSize: const Size(36, 36),
                                      padding: EdgeInsets.zero,
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                      ),
                    );
                  },
                ),
              ),
            ),
            if (provider.getTotalItems() > 0)
              Container(
                padding: const EdgeInsets.all(20),
                decoration: const BoxDecoration(
                  color: AppTheme.primaryDark,
                  border: Border(
                    top: BorderSide(color: AppTheme.accentGold, width: 2),
                  ),
                ),
                child: SafeArea(
                  top: false,
                  child: Row(
                    children: [
                      // Total — prend l'espace restant
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Text(
                              AppLocalizations.of(
                                context,
                              ).articleCount(provider.getTotalItems()),
                              style: const TextStyle(
                                fontSize: 13,
                                color: AppTheme.textGray,
                              ),
                            ),
                            Text(
                              '${provider.getTotalPrice().toStringAsFixed(0)} ${AppLocalizations.of(context).currencyFcfa}',
                              style: const TextStyle(
                                fontSize: 18,
                                fontWeight: FontWeight.w900,
                                color: AppTheme.accentGold,
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(width: 8),
                      SizedBox(
                        width: 80,
                        height: 44,
                        child: AnimatedOutlineButton(
                          text: AppLocalizations.of(context).cancel,
                          onPressed: () {
                            HapticHelper.lightImpact();
                            provider.clearSelection();
                          },
                          borderColor: AppTheme.accentGold,
                          textColor: AppTheme.accentGold,
                          enableHaptic: false,
                          fontSize: 12,
                        ),
                      ),
                      const SizedBox(width: 8),
                      SizedBox(
                        width: 150,
                        height: 44,
                        child: AnimatedButton(
                          text: AppLocalizations.of(context).validate,
                          onPressed: () {
                            HapticHelper.confirm();
                            context.navigateTo(
                              const CreateLaundryRequestScreen(),
                            );
                          },
                          backgroundColor: AppTheme.accentGold,
                          textColor: AppTheme.primaryDark,
                          enableHaptic: false,
                          fontSize: 14,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
          ],
        );
      },
    );
  }

  /// Ratio largeur/hauteur des cartes blanchisserie (cellules un peu plus hautes pour éviter le bottom overflow).
  static double _laundryCardAspectRatio(BuildContext context) {
    final cols = LayoutHelper.gridCrossAxisCount(context);
    switch (cols) {
      case 4:
        return 0.82;
      case 3:
        return 0.78;
      case 2:
        return 0.72;
      default:
        return 0.75;
    }
  }

  /// Icône spécifique selon le type de service blanchisserie (vêtements et linge)
  static IconData _iconForLaundryService(LaundryService service) {
    final nameStr = TranslatableTextHelper.resolveDisplayTextSync(service.name, 'fr');
    final n = nameStr.toLowerCase();
    // Vêtements : icône nettoyage à sec / vêtement
    if (n.contains('chemise') || n.contains('shirt')) {
      return Icons.dry_cleaning;
    }
    if (n.contains('costume') || n.contains('suit')) {
      return Icons.dry_cleaning;
    }
    if (n.contains('pantalon') ||
        n.contains('pants') ||
        n.contains('trousers')) {
      return Icons.dry_cleaning;
    }
    if (n.contains('robe') || n.contains('dress')) {
      return Icons.dry_cleaning;
    }
    // Linge de maison
    if (n.contains('draps') ||
        n.contains('sheet') ||
        (n.contains('linge') && !n.contains('serviette'))) {
      return Icons.bed;
    }
    if (n.contains('serviette') || n.contains('towel')) {
      return Icons.local_laundry_service;
    }
    // Services
    if (n.contains('nettoyage') &&
        (n.contains('sec') || n.contains('délicat'))) {
      return Icons.dry_cleaning;
    }
    if (n.contains('repassage') || n.contains('iron')) {
      return Icons.cleaning_services;
    }
    return Icons.local_laundry_service;
  }
}
