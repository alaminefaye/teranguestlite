import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';

import 'package:cached_network_image/cached_network_image.dart';
import '../../config/theme.dart';
import '../../config/api_config.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../models/user.dart';
import '../../providers/locale_provider.dart';
import '../../services/api_service.dart';
import '../../src/platform_check_stub.dart'
    if (dart.library.io) '../../src/platform_check_io.dart'
    as platform_check;
import '../../widgets/service_card.dart';

import '../../utils/navigation_helper.dart';
import '../../utils/haptic_helper.dart';
import '../../utils/layout_helper.dart';
import '../room_service/categories_screen.dart';
import '../services_chambre/room_and_logistics_screen.dart';
import '../restaurants/restaurants_list_screen.dart';
import '../leisure/wellness_sport_leisure_screen.dart';
import '../palace/palace_list_screen.dart';
import '../exploration/exploration_mobility_screen.dart';
import '../hotel_infos/hotel_infos_security_screen.dart';
import '../hotel_infos/assistance_emergency_screen.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  Enterprise? _enterprise;

  @override
  void initState() {
    super.initState();
    _loadEnterprise();
  }

  Future<void> _loadEnterprise() async {
    if (platform_check.isFlutterTest) return;
    try {
      final response = await ApiService().get(ApiConfig.vitrineEnterprise);
      final data = response.data;
      if (data is Map && data['success'] == true) {
        final payload = data['data'];
        if (payload is Map) {
          if (!mounted) return;
          setState(() {
            _enterprise = Enterprise.fromJson(
              Map<String, dynamic>.from(payload),
            );
          });
        }
      }
    } catch (_) {
      // ignore
    }
  }

  void _handleServiceTap(
    BuildContext context,
    String route,
    String serviceName,
  ) {
    // Feedback haptique au tap
    HapticHelper.lightImpact();

    switch (route) {
      case '/services-chambre-logistique':
        context.navigateTo(const RoomAndLogisticsScreen());
        break;
      case '/room-service':
        context.navigateTo(const CategoriesScreen());
        break;
      case '/restaurants':
        context.navigateTo(const RestaurantsListScreen());
        break;
      case '/wellness-sport-leisure':
        context.navigateTo(const WellnessSportLeisureScreen());
        break;
      case '/palace':
        context.navigateTo(const PalaceListScreen());
        break;
      case '/exploration-mobility':
        context.navigateTo(const ExplorationMobilityScreen());
        break;
      case '/hotel-infos-security':
        context.navigateTo(const HotelInfosSecurityScreen());
        break;
      case '/assistance-emergency':
        context.navigateTo(const AssistanceEmergencyScreen());
        break;
      case '/chatbot':
        final url = _enterprise?.chatbotUrl?.trim() ?? '';
        if (url.isNotEmpty) {
          launchUrl(Uri.parse(url), mode: LaunchMode.externalApplication);
          break;
        }
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: const Text('Fonction indisponible.'),
            backgroundColor: AppTheme.accentGold,
            duration: const Duration(seconds: 1),
          ),
        );
        break;
      default:
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              AppLocalizations.of(context).navigationTo(serviceName),
            ),
            backgroundColor: AppTheme.accentGold,
            duration: const Duration(seconds: 1),
          ),
        );
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
              // Bloc hero : isolé en RepaintBoundary pour éviter les repaints lors du scroll de la grille
              RepaintBoundary(child: _buildEnterpriseHero(context)),
              // Grille des services
              Expanded(child: _buildServicesGrid(context)),
              RepaintBoundary(child: _buildFooter(context)),
            ],
          ),
        ),
      ),
    );
  }

  /// Hero : une seule grande image en fond, logo + slogan + nom entreprise dessus,
  /// bouton langue en overlay sur l'image.
  Widget _buildEnterpriseHero(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final enterprise = _enterprise;
    final enterpriseName = enterprise?.name.trim() ?? '';
    final coverPath = enterprise?.coverPhoto;
    final logoPath = enterprise?.logo;
    final backgroundImageUrl = () {
      String? p = coverPath;
      if (p != null && p.trim().isNotEmpty) {
        final trimmed = p.trim();
        if (trimmed.startsWith('http')) return trimmed;
        return ApiConfig.storageUrl(trimmed);
      }
      p = logoPath;
      if (p != null && p.trim().isNotEmpty) {
        final trimmed = p.trim();
        if (trimmed.startsWith('http')) return trimmed;
        return ApiConfig.storageUrl(trimmed);
      }
      return null;
    }();

    final h = MediaQuery.sizeOf(context).height;
    final isLandscape =
        MediaQuery.of(context).orientation == Orientation.landscape;
    final isCompact = isLandscape ? (h < 900) : (h < 600);
    final isVeryCompact = h < 500;
    final pad = LayoutHelper.horizontalPaddingValue(context);
    final w = MediaQuery.sizeOf(context).width;
    final isMobileWidth = w < 600;
    final logoSize = isVeryCompact
        ? 76.0
        : (isCompact ? 88.0 : (isMobileWidth ? 78.0 : 140.0));
    final subtitleSize = isVeryCompact
        ? 10.0
        : (isCompact ? 12.0 : (isMobileWidth ? 11.0 : 14.0));
    final nameOnBannerSize = isVeryCompact
        ? 16.0
        : (isCompact ? 20.0 : (isMobileWidth ? 18.0 : 30.0));
    final iconSize = isVeryCompact
        ? 20.0
        : (isCompact ? 24.0 : (isMobileWidth ? 22.0 : 32.0));
    final logoUrl = () {
      final p = logoPath;
      if (p == null || p.trim().isEmpty) return null;
      final trimmed = p.trim();
      if (trimmed.startsWith('http')) return trimmed;
      return ApiConfig.storageUrl(trimmed);
    }();
    // Hauteur du hero pour que l'image s'affiche bien (une seule grande zone image)
    final heroHeight = isVeryCompact ? 180.0 : (isCompact ? 220.0 : 260.0);

    return SizedBox(
      height: heroHeight,
      width: double.infinity,
      child: Stack(
        fit: StackFit.expand,
        children: [
          // 1) Image en plein fond : couverture (photo) ou logo en repli
          if (backgroundImageUrl != null && backgroundImageUrl.isNotEmpty)
            CachedNetworkImage(
              imageUrl: backgroundImageUrl,
              fit: BoxFit.cover,
              placeholder: (context, url) => Container(
                decoration: const BoxDecoration(
                  gradient: AppTheme.backgroundGradient,
                ),
              ),
              errorWidget: (context, url, error) => Container(
                decoration: const BoxDecoration(
                  gradient: AppTheme.backgroundGradient,
                ),
              ),
            )
          else
            Container(
              decoration: const BoxDecoration(
                gradient: AppTheme.backgroundGradient,
              ),
            ),
          // 2) Dégradé pour lisibilité des textes
          Positioned.fill(
            child: DecoratedBox(
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                  colors: [
                    Colors.black26,
                    Colors.transparent,
                    Colors.black45,
                    Colors.black.withValues(alpha: 0.75),
                  ],
                  stops: const [0.0, 0.25, 0.6, 1.0],
                ),
              ),
              child: const SizedBox.shrink(),
            ),
          ),
          // 3) Logo + slogan sur le cover (remontés pour que "Votre assistant digital" soit plus haut)
          Align(
            alignment: const Alignment(0, -0.72),
            child: Padding(
              padding: EdgeInsets.only(
                left: pad,
                right: pad,
                top: 8,
                bottom: 6,
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  if (logoUrl != null && logoUrl.isNotEmpty)
                    SizedBox(
                      width: logoSize,
                      height: logoSize,
                      child: DecoratedBox(
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          border: Border.all(
                            color: AppTheme.accentGold.withValues(alpha: 0.6),
                            width: 2,
                          ),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withValues(alpha: 0.35),
                              blurRadius: 14,
                              offset: const Offset(0, 6),
                            ),
                          ],
                        ),
                        child: ClipOval(
                          child: CachedNetworkImage(
                            imageUrl: logoUrl,
                            fit: BoxFit.cover,
                            placeholder: (context, url) => Center(
                              child: SizedBox(
                                width: logoSize * 0.28,
                                height: logoSize * 0.28,
                                child: const CircularProgressIndicator(
                                  strokeWidth: 2,
                                  color: AppTheme.accentGold,
                                ),
                              ),
                            ),
                            errorWidget: (context, url, error) => const Center(
                              child: Icon(
                                Icons.business_outlined,
                                color: AppTheme.textGray,
                              ),
                            ),
                          ),
                        ),
                      ),
                    ),
                  if (logoUrl != null && logoUrl.isNotEmpty)
                    SizedBox(height: isVeryCompact ? 6 : 8),
                  Text(
                    l10n.welcomeSubtitle,
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      color: AppTheme.textWhite,
                      fontSize: subtitleSize,
                      fontWeight: FontWeight.w400,
                      shadows: [
                        Shadow(
                          color: Colors.black.withValues(alpha: 0.8),
                          offset: const Offset(0, 1),
                          blurRadius: 3,
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
          // 4) "Bienvenue au [nom de l'hôtel]" au centre en bas, bien en gras
          Align(
            alignment: Alignment.bottomCenter,
            child: Padding(
              padding: EdgeInsets.only(
                left: pad + 8,
                top: 0,
                bottom: 8,
                right: pad + 8,
              ),
              child: Text(
                enterpriseName.isNotEmpty
                    ? l10n.welcomeToEnterprise(enterpriseName)
                    : l10n.welcomeTitle,
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: nameOnBannerSize,
                  fontWeight: FontWeight.w800,
                  color: Colors.white,
                  shadows: [
                    Shadow(
                      color: Colors.black.withValues(alpha: 0.9),
                      offset: const Offset(0, 1),
                      blurRadius: 6,
                    ),
                    Shadow(
                      color: Colors.black.withValues(alpha: 0.6),
                      offset: const Offset(0, 2),
                      blurRadius: 8,
                    ),
                  ],
                ),
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
              ),
            ),
          ),
          // 4b) Logo TerangaGuest en haut à gauche
          Positioned(
            top: 8,
            left: 8,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
              decoration: BoxDecoration(
                color: AppTheme.primaryDark.withValues(alpha: 0.65),
                borderRadius: BorderRadius.circular(28),
                border: Border.all(
                  color: AppTheme.accentGold.withValues(alpha: 0.4),
                  width: 1,
                ),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.5),
                    blurRadius: 12,
                    offset: const Offset(0, 2),
                  ),
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.3),
                    blurRadius: 6,
                    offset: const Offset(0, 1),
                  ),
                ],
              ),
              child: Image.asset(
                'assets/logo.png',
                height: isVeryCompact ? 18 : (isCompact ? 22 : 24),
                fit: BoxFit.contain,
              ),
            ),
          ),
          // 5) Boutons notification et profil sur l'image (fond + ombre pour la visibilité)
          Positioned(
            top: 8,
            right: 8,
            child: Container(
              decoration: BoxDecoration(
                color: AppTheme.primaryDark.withValues(alpha: 0.65),
                borderRadius: BorderRadius.circular(28),
                border: Border.all(
                  color: AppTheme.accentGold.withValues(alpha: 0.4),
                  width: 1,
                ),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.5),
                    blurRadius: 12,
                    offset: const Offset(0, 2),
                  ),
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.3),
                    blurRadius: 6,
                    offset: const Offset(0, 1),
                  ),
                ],
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  IconButton(
                    onPressed: () {
                      HapticHelper.lightImpact();
                      _showLanguageDialog(context);
                    },
                    padding: EdgeInsets.all(isMobileWidth ? 4.0 : 8.0),
                    constraints: isMobileWidth ? const BoxConstraints() : null,
                    iconSize: iconSize,
                    icon: Icon(
                      Icons.translate,
                      color: AppTheme.accentGold,
                      shadows: [
                        Shadow(
                          color: Colors.black.withValues(alpha: 0.8),
                          blurRadius: 4,
                          offset: const Offset(0, 1),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  void _showLanguageDialog(BuildContext context) {
    final localeProvider = context.read<LocaleProvider>();
    final currentCode = localeProvider.languageCode;
    const languages = [
      ('fr', 'Français'),
      ('en', 'English'),
      ('ar', 'العربية'),
      ('es', 'Español'),
    ];
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: AppTheme.primaryBlue,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(16),
          side: BorderSide(color: AppTheme.accentGold.withValues(alpha: 0.5)),
        ),
        title: Text(
          AppLocalizations.of(context).changeLanguage,
          style: const TextStyle(color: AppTheme.accentGold),
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: languages.map((e) {
            final selected = currentCode == e.$1;
            return ListTile(
              title: Text(
                e.$2,
                style: TextStyle(
                  color: selected ? AppTheme.accentGold : Colors.white,
                  fontWeight: selected ? FontWeight.bold : FontWeight.normal,
                ),
              ),
              trailing: selected
                  ? const Icon(Icons.check, color: AppTheme.accentGold)
                  : null,
              onTap: () async {
                await localeProvider.setLocale(Locale(e.$1));
                if (ctx.mounted) Navigator.of(ctx).pop();
              },
            );
          }).toList(),
        ),
      ),
    );
  }

  Widget _buildServicesGrid(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final services = [
      {
        'title': l10n.servicesChambreLogistique,
        'icon': Icons.room_service_outlined,
        'route': '/services-chambre-logistique',
        'image': 'assets/images/box_room_service.png',
      },
      {
        'title': l10n.restaurantsBars,
        'icon': Icons.restaurant_menu_outlined,
        'route': '/restaurants',
        'image': 'assets/images/box_restaurant.png',
      },
      {
        'title': l10n.wellnessSportLeisure,
        'icon': Icons.spa_outlined,
        'route': '/wellness-sport-leisure',
        'image': 'assets/images/box_wellness.png',
      },
      {
        'title': l10n.palaceServices,
        'icon': Icons.auto_awesome_outlined,
        'route': '/palace',
        'image': 'assets/images/box_autres_services.png',
      },
      {
        'title': l10n.explorationMobility,
        'icon': Icons.explore_outlined,
        'route': '/exploration-mobility',
        'image': 'assets/images/box_exploration.png',
      },
      {
        'title': l10n.hotelInfosSecurity,
        'icon': Icons.info_outline_rounded,
        'route': '/hotel-infos-security',
        'image': 'assets/images/box_hotel_infos.png',
      },
      {
        'title': l10n.assistanceEmergency,
        'icon': Icons.emergency_outlined,
        'route': '/assistance-emergency',
        'image': 'assets/images/box_assistance.png',
      },
      {
        'title': l10n.chatbotMultilingual,
        'icon': Icons.smart_toy_outlined,
        'route': '/chatbot',
        'image': 'assets/images/box_chatbot.png',
      },
    ];

    final filteredServices = services.where((s) {
      final route = (s['route'] as String?) ?? '';
      return route != '/assistance-emergency' && route != '/chatbot';
    }).toList();

    final crossAxisCount = LayoutHelper.gridCrossAxisCount(context);
    final aspectRatio = LayoutHelper.dashboardCellAspectRatio(context);
    final spacing = LayoutHelper.gridSpacing(context);

    return Padding(
      padding: LayoutHelper.horizontalPadding(context),
      child: GridView.builder(
        padding: EdgeInsets.symmetric(vertical: spacing),
        cacheExtent: 200,
        gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
          crossAxisCount: crossAxisCount,
          crossAxisSpacing: spacing,
          mainAxisSpacing: spacing,
          childAspectRatio: aspectRatio,
        ),
        itemCount: filteredServices.length,
        itemBuilder: (context, index) {
          final service = filteredServices[index];
          return ServiceCard(
            title: service['title'] as String,
            icon: service['icon'] as IconData,
            imagePath: service['image'] as String?,
            onTap: () {
              _handleServiceTap(
                context,
                service['route'] as String,
                service['title'] as String,
              );
            },
          );
        },
      ),
    );
  }

  Widget _buildFooter(BuildContext context) {
    final size = MediaQuery.sizeOf(context);
    final h = size.height;
    final isLandscape =
        MediaQuery.of(context).orientation == Orientation.landscape;
    final isCompact = isLandscape ? (h < 900) : (h < 600);
    final isVeryCompact = h < 500;
    final padV = isVeryCompact ? 4.0 : (isCompact ? 6.0 : 12.0);
    final padH = isVeryCompact ? 10.0 : (isCompact ? 12.0 : 20.0);
    final timeSize = isVeryCompact ? 14.0 : (isCompact ? 16.0 : 20.0);
    final dateSize = isVeryCompact ? 9.0 : (isCompact ? 10.0 : 12.0);

    return Container(
      padding: EdgeInsets.symmetric(vertical: padV, horizontal: padH),
      child: StreamBuilder(
        stream: Stream.periodic(const Duration(seconds: 1)),
        builder: (context, snapshot) {
          final now = DateTime.now();
          return Row(
            mainAxisAlignment: MainAxisAlignment.center,
            crossAxisAlignment: CrossAxisAlignment.center,
            mainAxisSize: MainAxisSize.max,
            children: [
              Text(
                DateFormat('hh:mm a').format(now).toUpperCase(),
                style: TextStyle(
                  fontSize: timeSize,
                  fontWeight: FontWeight.bold,
                  color: AppTheme.accentGold,
                  height: 1.0,
                ),
              ),
              SizedBox(width: isCompact ? 10 : 14),
              Flexible(
                child: Text(
                  DateFormat(
                    'EEEE d MMMM',
                    Localizations.localeOf(context).languageCode,
                  ).format(now),
                  style: TextStyle(
                    fontSize: dateSize,
                    fontWeight: FontWeight.w400,
                    color: AppTheme.textGray,
                    height: 1.0,
                  ),
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            ],
          );
        },
      ),
    );
  }
}
