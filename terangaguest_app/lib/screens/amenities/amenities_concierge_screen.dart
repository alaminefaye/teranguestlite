import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../utils/haptic_helper.dart';
import '../../utils/layout_helper.dart';
import '../../widgets/service_card.dart';
import '../../widgets/quantity_selector.dart';
import '../../providers/palace_provider.dart';
import '../../models/palace.dart';
import '../../models/amenity_category.dart';
import '../../services/amenity_api.dart';

/// Écran dédié « Amenities & Conciergerie » : demande simplifiée d'articles de toilette,
/// oreillers supplémentaires, kit de rasage ou tout autre service sans passer par le téléphone.
/// Utilise un service Palace de catégorie concierge en backend.
class AmenitiesConciergeScreen extends StatefulWidget {
  const AmenitiesConciergeScreen({super.key});

  @override
  State<AmenitiesConciergeScreen> createState() =>
      _AmenitiesConciergeScreenState();
}

class _AmenitiesConciergeScreenState extends State<AmenitiesConciergeScreen> {
  List<AmenityCategoryDto>? _dynamicCategories;
  bool _loadingCategories = true;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<PalaceProvider>().fetchPalaceServices();
      _loadAmenityCategories();
    });
  }

  Future<void> _loadAmenityCategories() async {
    try {
      final list = await AmenityApi().getCategories();
      if (mounted) {
        setState(() {
          _dynamicCategories = list;
          _loadingCategories = false;
        });
      }
    } catch (_) {
      if (mounted) {
        setState(() {
          _dynamicCategories = null;
          _loadingCategories = false;
        });
      }
    }
  }

  /// Retourne le premier service Palace de catégorie concierge, sinon le premier disponible.
  PalaceService? _getConciergeService(List<PalaceService> services) {
    final concierge = services
        .where((s) => s.isAvailable && (s.category == 'concierge'))
        .toList();
    if (concierge.isNotEmpty) return concierge.first;
    final available = services.where((s) => s.isAvailable).toList();
    return available.isNotEmpty ? available.first : null;
  }

  /// Retourne la liste des articles (libellés) selon la catégorie d'amenity.
  List<String> _getItemsForCategory(
    AppLocalizations l10n,
    String categoryLabel,
  ) {
    if (categoryLabel == l10n.amenityToiletries) {
      return [
        l10n.amenityItemSoap,
        l10n.amenityItemShampoo,
        l10n.amenityItemToothpaste,
        l10n.amenityItemToothbrush,
        l10n.amenityItemComb,
        l10n.amenityItemTowels,
      ];
    }
    if (categoryLabel == l10n.amenityPillows) {
      return [l10n.amenityItemPillow];
    }
    if (categoryLabel == l10n.amenityShavingKit) {
      return [
        l10n.amenityItemRazor,
        l10n.amenityItemShavingFoam,
        l10n.amenityItemAfterShave,
        l10n.amenityItemBlades,
      ];
    }
    return [];
  }

  static const List<IconData> _categoryIcons = [
    Icons.soap_outlined,
    Icons.bed_outlined,
    Icons.content_cut_outlined,
    Icons.more_horiz,
    Icons.list_alt_outlined,
  ];

  void _onAmenityTap(
    BuildContext context, {
    required String label,
    required IconData icon,
    List<String>? itemLabelsOverride,
  }) {
    HapticHelper.lightImpact();
    final l10n = AppLocalizations.of(context);
    final provider = context.read<PalaceProvider>();
    final service = _getConciergeService(provider.services);

    if (service == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(l10n.noPalaceServiceHint),
          backgroundColor: Colors.orange,
        ),
      );
      return;
    }

    final itemLabels = itemLabelsOverride ?? _getItemsForCategory(l10n, label);
    final isOther = itemLabels.isEmpty;

    if (isOther) {
      _showOtherRequestDialog(context, service.id, label, l10n);
      return;
    }

    final quantities = <String, int>{};
    for (final item in itemLabels) {
      quantities[item] = 0;
    }

    final screenContext = context;
    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (dialogContext, setState) {
          return AlertDialog(
            backgroundColor: AppTheme.primaryBlue,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(16),
              side: BorderSide(
                color: AppTheme.accentGold.withValues(alpha: 0.5),
              ),
            ),
            title: Text(
              label,
              style: const TextStyle(color: AppTheme.accentGold),
            ),
            content: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Text(
                    l10n.amenitySelectQuantities,
                    style: const TextStyle(
                      color: AppTheme.textGray,
                      fontSize: 13,
                    ),
                  ),
                  const SizedBox(height: 16),
                  ...itemLabels.map((itemLabel) {
                    final qty = quantities[itemLabel] ?? 0;
                    final maxQty = itemLabels.length == 1 ? 10 : 9;
                    return Padding(
                      padding: const EdgeInsets.only(bottom: 12),
                      child: Row(
                        children: [
                          Expanded(
                            child: Text(
                              itemLabel,
                              style: const TextStyle(
                                color: Colors.white,
                                fontSize: 15,
                              ),
                            ),
                          ),
                          QuantitySelector(
                            quantity: qty,
                            minQuantity: 0,
                            maxQuantity: maxQty,
                            onIncrement: () {
                              setState(
                                () => quantities[itemLabel] =
                                    (quantities[itemLabel] ?? 0) + 1,
                              );
                            },
                            onDecrement: () {
                              final v = quantities[itemLabel] ?? 0;
                              if (v > 0) {
                                setState(() => quantities[itemLabel] = v - 1);
                              }
                            },
                          ),
                        ],
                      ),
                    );
                  }),
                ],
              ),
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.of(ctx).pop(),
                child: Text(
                  l10n.cancel,
                  style: const TextStyle(color: AppTheme.textGray),
                ),
              ),
              FilledButton(
                onPressed: () {
                  final parts = <String>[];
                  for (final entry in quantities.entries) {
                    if (entry.value > 0) {
                      parts.add('${entry.key} x${entry.value}');
                    }
                  }
                  if (parts.isEmpty) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(
                        content: Text(l10n.amenitySelectQuantities),
                        backgroundColor: Colors.orange,
                      ),
                    );
                    return;
                  }
                  final details = parts.join(', ');
                  Navigator.of(ctx).pop();
                  _sendAmenityRequest(
                    context: screenContext,
                    serviceId: service.id,
                    label: label,
                    details: details,
                    l10n: l10n,
                  );
                },
                style: FilledButton.styleFrom(
                  backgroundColor: AppTheme.accentGold,
                  foregroundColor: AppTheme.primaryDark,
                ),
                child: Text(l10n.sendRequest),
              ),
            ],
          );
        },
      ),
    );
  }

  void _showOtherRequestDialog(
    BuildContext context,
    int serviceId,
    String label,
    AppLocalizations l10n,
  ) {
    final detailsController = TextEditingController();
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: AppTheme.primaryBlue,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(16),
          side: BorderSide(color: AppTheme.accentGold.withValues(alpha: 0.5)),
        ),
        title: Text(label, style: const TextStyle(color: AppTheme.accentGold)),
        content: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Text(
                l10n.amenityOtherDetailsHint,
                style: const TextStyle(color: AppTheme.textGray, fontSize: 13),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: detailsController,
                maxLines: 3,
                decoration: InputDecoration(
                  hintText: l10n.describeRequest,
                  hintStyle: TextStyle(
                    color: AppTheme.textGray.withValues(alpha: 0.7),
                  ),
                  filled: true,
                  fillColor: AppTheme.primaryDark.withValues(alpha: 0.6),
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: BorderSide(
                      color: AppTheme.accentGold.withValues(alpha: 0.4),
                    ),
                  ),
                ),
                style: const TextStyle(color: Colors.white),
              ),
            ],
          ),
        ),
        actions: [
          TextButton(
            onPressed: () async {
              FocusManager.instance.primaryFocus?.unfocus();
              await Future.delayed(const Duration(milliseconds: 50));
              if (ctx.mounted) {
                Navigator.of(ctx).pop();
              }
            },
            child: Text(
              l10n.cancel,
              style: const TextStyle(color: AppTheme.textGray),
            ),
          ),
          FilledButton(
            onPressed: () async {
              final details = detailsController.text.trim();
              FocusManager.instance.primaryFocus?.unfocus();
              await Future.delayed(const Duration(milliseconds: 50));
              if (ctx.mounted) {
                Navigator.of(ctx).pop();
              }
              if (context.mounted) {
                _sendAmenityRequest(
                  context: context,
                  serviceId: serviceId,
                  label: label,
                  details: details.isEmpty ? label : details,
                  l10n: l10n,
                );
              }
            },
            style: FilledButton.styleFrom(
              backgroundColor: AppTheme.accentGold,
              foregroundColor: AppTheme.primaryDark,
            ),
            child: Text(l10n.sendRequest),
          ),
        ],
      ),
    ).then((_) => detailsController.dispose());
  }

  Future<void> _sendAmenityRequest({
    required BuildContext context,
    required int serviceId,
    required String label,
    required String details,
    required AppLocalizations l10n,
  }) async {
    final description = details.isEmpty ? label : '$label\n$details';

    try {
      await context.read<PalaceProvider>().createPalaceRequest(
        serviceId: serviceId,
        details: description,
      );
      if (!context.mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(l10n.requestSent),
          backgroundColor: AppTheme.accentGold,
          duration: const Duration(seconds: 2),
        ),
      );
    } catch (e) {
      if (!context.mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('${l10n.errorPrefix}$e'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final crossAxisCount = LayoutHelper.gridCrossAxisCount(context);
    final aspectRatio = LayoutHelper.dashboardCellAspectRatio(context);
    final spacing = LayoutHelper.gridSpacing(context);

    final useDynamic =
        _dynamicCategories != null && _dynamicCategories!.isNotEmpty;
    final List<
      (String title, IconData icon, String? image, List<String>? itemLabels)
    >
    options;

    String? imageForAmenity(String name) {
      final n = name.toLowerCase();
      if (n.contains('toilette') ||
          n.contains('soap') ||
          n.contains('shampoo') ||
          n.contains('rasage') ||
          n.contains('shav')) {
        return n.contains('rasage') || n.contains('shav')
            ? 'assets/images/amenity_shaving.png'
            : 'assets/images/amenity_toiletries.png';
      }
      if (n.contains('oreiller') ||
          n.contains('pillow') ||
          n.contains('lit') ||
          n.contains('bed')) {
        return 'assets/images/amenity_pillows.png';
      }
      return 'assets/images/amenity_other.png';
    }

    if (useDynamic) {
      options = _dynamicCategories!.asMap().entries.map((e) {
        final i = e.key;
        final cat = e.value;
        final icon = i < _categoryIcons.length
            ? _categoryIcons[i]
            : _categoryIcons.last;
        final itemLabels = cat.items.map((item) => item.name).toList();
        return (cat.name, icon, imageForAmenity(cat.name), itemLabels);
      }).toList();
    } else {
      options = [
        (
          l10n.amenityToiletries,
          Icons.soap_outlined,
          'assets/images/amenity_toiletries.png',
          null,
        ),
        (
          l10n.amenityPillows,
          Icons.bed_outlined,
          'assets/images/amenity_pillows.png',
          null,
        ),
        (
          l10n.amenityShavingKit,
          Icons.content_cut_outlined,
          'assets/images/amenity_shaving.png',
          null,
        ),
        (
          l10n.amenityOther,
          Icons.more_horiz,
          'assets/images/amenity_other.png',
          null,
        ),
      ];
    }

    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(gradient: AppTheme.backgroundGradient),
        child: SafeArea(
          child: Column(
            children: [
              _buildAppBar(context, l10n),
              Expanded(
                child: _loadingCategories
                    ? const Center(
                        child: CircularProgressIndicator(
                          valueColor: AlwaysStoppedAnimation<Color>(
                            AppTheme.accentGold,
                          ),
                        ),
                      )
                    : Padding(
                        padding: LayoutHelper.horizontalPadding(context),
                        child: GridView.builder(
                          padding: EdgeInsets.symmetric(vertical: spacing),
                          gridDelegate:
                              SliverGridDelegateWithFixedCrossAxisCount(
                                crossAxisCount: crossAxisCount,
                                crossAxisSpacing: spacing,
                                mainAxisSpacing: spacing,
                                childAspectRatio: aspectRatio,
                              ),
                          itemCount: options.length,
                          itemBuilder: (context, index) {
                            final (title, icon, image, itemLabels) =
                                options[index];
                            return ServiceCard(
                              title: title,
                              icon: icon,
                              imagePath: image,
                              onTap: () => _onAmenityTap(
                                context,
                                label: title,
                                icon: icon,
                                itemLabelsOverride: itemLabels,
                              ),
                            );
                          },
                        ),
                      ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildAppBar(BuildContext context, AppLocalizations l10n) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 12),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          Row(
            children: [
              IconButton(
                onPressed: () {
                  HapticHelper.lightImpact();
                  Navigator.of(context).pop();
                },
                icon: const Icon(
                  Icons.arrow_back_ios_new,
                  color: AppTheme.accentGold,
                ),
              ),
              Expanded(
                child: Text(
                  l10n.amenitiesConcierge,
                  textAlign: TextAlign.center,
                  style: const TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.w800,
                    color: AppTheme.accentGold,
                    letterSpacing: 0.5,
                  ),
                ),
              ),
              const SizedBox(width: 48),
            ],
          ),
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 0, 16, 12),
            child: Text(
              l10n.amenitiesConciergeDesc,
              textAlign: TextAlign.center,
              style: TextStyle(
                fontSize: 13,
                color: AppTheme.textGray,
                height: 1.3,
              ),
            ),
          ),
        ],
      ),
    );
  }
}
