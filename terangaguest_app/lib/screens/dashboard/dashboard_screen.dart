import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import 'package:weather/weather.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../config/theme.dart';
import '../../config/api_config.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../providers/auth_provider.dart';
import '../../providers/locale_provider.dart';
import '../../providers/orders_provider.dart';
import '../../widgets/service_card.dart';
import '../../services/weather_service.dart';
import '../../utils/navigation_helper.dart';
import '../../utils/haptic_helper.dart';
import '../../utils/layout_helper.dart';
import '../notifications/notifications_screen.dart';
import '../room_service/categories_screen.dart';
import '../room_service/cart_screen.dart';
import '../services_chambre/room_and_logistics_screen.dart';
import '../leisure/wellness_sport_leisure_screen.dart';
import '../profile/profile_screen.dart';
import '../orders/orders_list_screen.dart';
import '../restaurants/restaurants_list_screen.dart';
import '../spa/spa_services_list_screen.dart';
import '../exploration/exploration_mobility_screen.dart';
import '../excursions/excursions_list_screen.dart';
import '../hotel_infos/hotel_infos_security_screen.dart';
import '../laundry/laundry_list_screen.dart';
import '../palace/palace_list_screen.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  final WeatherService _weatherService = WeatherService();
  Weather? _currentWeather;

  @override
  void initState() {
    super.initState();
    _loadWeather();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<OrdersProvider>().fetchOrdersForDashboard();
    });
  }

  Future<void> _loadWeather() async {
    try {
      final weather = await _weatherService.getCurrentWeather();
      if (mounted) {
        setState(() {
          _currentWeather = weather;
        });
      }
    } catch (e) {
      // Silently fail, keep default weather
    }
  }

  void _handleServiceTap(BuildContext context, String route, String serviceName) {
    // Feedback haptique au tap
    HapticHelper.lightImpact();
    
    switch (route) {
      case '/services-chambre-logistique':
        context.navigateTo(const RoomAndLogisticsScreen());
        break;
      case '/room-service':
        context.navigateTo(const CategoriesScreen());
        break;
      case '/cart':
        context.navigateTo(const CartScreen());
        break;
      case '/orders':
        context.navigateTo(const OrdersListScreen());
        break;
      case '/restaurants':
        context.navigateTo(const RestaurantsListScreen());
        break;
      case '/wellness-sport-leisure':
        context.navigateTo(const WellnessSportLeisureScreen());
        break;
      case '/spa':
        context.navigateTo(const SpaServicesListScreen());
        break;
      case '/exploration-mobility':
        context.navigateTo(const ExplorationMobilityScreen());
        break;
      case '/excursions':
        context.navigateTo(const ExcursionsListScreen());
        break;
      case '/laundry':
        context.navigateTo(const LaundryListScreen());
        break;
      case '/palace':
        context.navigateTo(const PalaceListScreen());
        break;
      case '/hotel-infos-security':
        context.navigateTo(const HotelInfosSecurityScreen());
        break;
      default:
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(AppLocalizations.of(context).navigationTo(serviceName)),
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
        decoration: const BoxDecoration(
          gradient: AppTheme.backgroundGradient,
        ),
        child: SafeArea(
          child: Column(
            children: [
              // Bloc hero : grande image en fond, logo + slogan + nom dessus, boutons notification/profil en overlay sur l'image
              _buildEnterpriseHero(context),
              // Grille des services
              Expanded(
                child: _buildServicesGrid(context),
              ),
              _buildFooter(context),
            ],
          ),
        ),
      ),
    );
  }

  /// Hero : une seule grande image en fond, logo + slogan + nom entreprise dessus,
  /// boutons notification et profil en overlay sur l'image (comme SEDIMA).
  Widget _buildEnterpriseHero(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final user = context.watch<AuthProvider>().user;
    final enterprise = user?.enterprise;
    final enterpriseName = enterprise?.name.trim() ?? '';
    // Image de fond : priorité à la photo de couverture, sinon logo
    final coverPath = enterprise?.coverPhoto;
    final logoPath = enterprise?.logo;
    final backgroundImageUrl = (coverPath != null && coverPath.isNotEmpty)
        ? ApiConfig.storageUrl(coverPath)
        : (logoPath != null && logoPath.isNotEmpty)
            ? ApiConfig.storageUrl(logoPath)
            : null;

    final h = MediaQuery.sizeOf(context).height;
    final isLandscape = MediaQuery.of(context).orientation == Orientation.landscape;
    final isCompact = isLandscape ? (h < 900) : (h < 600);
    final isVeryCompact = h < 500;
    final pad = LayoutHelper.horizontalPaddingValue(context);
    final logoSize = isVeryCompact ? 88.0 : (isCompact ? 120.0 : 160.0);
    final subtitleSize = isVeryCompact ? 10.0 : (isCompact ? 12.0 : 14.0);
    final nameOnBannerSize = isVeryCompact ? 18.0 : (isCompact ? 24.0 : 30.0);
    final iconSize = isVeryCompact ? 22.0 : (isCompact ? 26.0 : 32.0);
    final logoUrl = (logoPath != null && logoPath.isNotEmpty)
        ? ApiConfig.storageUrl(logoPath)
        : null;
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
              placeholder: (_, __) => Container(
                decoration: const BoxDecoration(
                  gradient: AppTheme.backgroundGradient,
                ),
              ),
              errorWidget: (_, __, ___) => Container(
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
            alignment: const Alignment(0, -0.95),
            child: Padding(
              padding: EdgeInsets.only(left: pad, right: pad, top: 12, bottom: 12),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  if (logoUrl != null && logoUrl.isNotEmpty)
                    CachedNetworkImage(
                      imageUrl: logoUrl,
                      height: logoSize,
                      fit: BoxFit.contain,
                      placeholder: (_, __) => SizedBox(
                        height: logoSize,
                        child: Center(
                          child: SizedBox(
                            width: logoSize * 0.4,
                            height: logoSize * 0.4,
                            child: const CircularProgressIndicator(
                              strokeWidth: 2,
                              color: AppTheme.accentGold,
                            ),
                          ),
                        ),
                      ),
                      errorWidget: (_, __, ___) => const SizedBox.shrink(),
                    ),
                  if (logoUrl != null && logoUrl.isNotEmpty) SizedBox(height: isVeryCompact ? 12 : 18),
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
              padding: EdgeInsets.only(left: pad + 8, top: 20, bottom: 16, right: pad + 8),
              child: Text(
                'Bienvenue au ${enterpriseName.isNotEmpty ? enterpriseName : l10n.welcomeTitle}',
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
          // 4b) TerangaGuest en haut à gauche (même fond que les icônes)
          Positioned(
            top: 8,
            left: 8,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
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
              child: RichText(
                text: TextSpan(
                  children: [
                    TextSpan(
                      text: 'TERAN',
                      style: TextStyle(
                        fontSize: isVeryCompact ? 14 : (isCompact ? 16 : 18),
                        fontWeight: FontWeight.bold,
                        color: AppTheme.textWhite,
                        letterSpacing: 1.5,
                        shadows: [
                          Shadow(
                            color: Colors.black.withValues(alpha: 0.8),
                            blurRadius: 4,
                            offset: const Offset(0, 1),
                          ),
                        ],
                      ),
                    ),
                    TextSpan(
                      text: 'GUEST',
                      style: TextStyle(
                        fontSize: isVeryCompact ? 14 : (isCompact ? 16 : 18),
                        fontWeight: FontWeight.bold,
                        color: AppTheme.accentGold,
                        letterSpacing: 1.5,
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
                  Stack(
                    clipBehavior: Clip.none,
                    children: [
                      IconButton(
                        onPressed: () {
                          HapticHelper.lightImpact();
                          context.navigateTo(const NotificationsScreen());
                        },
                        padding: const EdgeInsets.all(8),
                        iconSize: iconSize,
                        icon: Icon(
                          Icons.notifications_none,
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
                      Positioned(
                        right: 6,
                        top: 6,
                        child: Container(
                          width: 8,
                          height: 8,
                          decoration: BoxDecoration(
                            color: Colors.red,
                            shape: BoxShape.circle,
                            border: Border.all(color: AppTheme.primaryDark, width: 1),
                          ),
                        ),
                      ),
                    ],
                  ),
                  IconButton(
                    onPressed: () {
                      HapticHelper.lightImpact();
                      _showLanguageDialog(context);
                    },
                    padding: const EdgeInsets.all(8),
                    iconSize: iconSize,
                    icon: Icon(
                      Icons.language,
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
                  IconButton(
                    onPressed: () {
                      HapticHelper.lightImpact();
                      context.navigateTo(const ProfileScreen());
                    },
                    padding: const EdgeInsets.all(8),
                    iconSize: iconSize,
                    icon: Icon(
                      Icons.person_outline,
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
          'Changer la langue',
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
              trailing: selected ? const Icon(Icons.check, color: AppTheme.accentGold) : null,
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
      {'title': l10n.servicesChambreLogistique, 'icon': Icons.room_service_outlined, 'route': '/services-chambre-logistique'},
      {'title': l10n.restaurantsBars, 'icon': Icons.restaurant_menu_outlined, 'route': '/restaurants'},
      {'title': l10n.wellnessSportLeisure, 'icon': Icons.spa_outlined, 'route': '/wellness-sport-leisure'},
      {'title': l10n.palaceServices, 'icon': Icons.auto_awesome_outlined, 'route': '/palace'},
      {'title': l10n.explorationMobility, 'icon': Icons.explore_outlined, 'route': '/exploration-mobility'},
      {'title': l10n.hotelInfosSecurity, 'icon': Icons.info_outline_rounded, 'route': '/hotel-infos-security'},
    ];

    final crossAxisCount = LayoutHelper.gridCrossAxisCount(context);
    final aspectRatio = LayoutHelper.dashboardCellAspectRatio(context);
    final spacing = LayoutHelper.gridSpacing(context);

    return Padding(
      padding: LayoutHelper.horizontalPadding(context),
      child: GridView.builder(
        padding: EdgeInsets.symmetric(vertical: spacing),
        gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
          crossAxisCount: crossAxisCount,
          crossAxisSpacing: spacing,
          mainAxisSpacing: spacing,
          childAspectRatio: aspectRatio,
        ),
        itemCount: services.length,
        itemBuilder: (context, index) {
          final service = services[index];
          return ServiceCard(
            title: service['title'] as String,
            icon: service['icon'] as IconData,
            onTap: () {
              _handleServiceTap(context, service['route'] as String, service['title'] as String);
            },
          );
        },
      ),
    );
  }

  Widget _buildFooter(BuildContext context) {
    final h = MediaQuery.sizeOf(context).height;
    final isLandscape = MediaQuery.of(context).orientation == Orientation.landscape;
    final isCompact = isLandscape ? (h < 900) : (h < 600);
    final isVeryCompact = h < 500;
    final padV = isVeryCompact ? 4.0 : (isCompact ? 6.0 : 12.0);
    final padH = isVeryCompact ? 10.0 : (isCompact ? 12.0 : 20.0);
    final timeSize = isVeryCompact ? 14.0 : (isCompact ? 16.0 : 20.0);
    final weatherIconSize = isVeryCompact ? 12.0 : (isCompact ? 14.0 : 18.0);
    final tempSize = isVeryCompact ? 14.0 : (isCompact ? 16.0 : 20.0);
    final dateSize = isVeryCompact ? 9.0 : (isCompact ? 10.0 : 12.0);
    final badgePadH = isVeryCompact ? 6.0 : (isCompact ? 8.0 : 10.0);
    final badgePadV = isVeryCompact ? 3.0 : (isCompact ? 4.0 : 5.0);

    return Container(
      padding: EdgeInsets.symmetric(vertical: padV, horizontal: padH),
      child: Consumer<OrdersProvider>(
        builder: (context, ordersProvider, _) {
          final inProgressCount = ordersProvider.inProgressOrdersCount;
          return Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              // Commande(s) en cours — affiché seulement s'il y en a au moins une (animé, clignotant)
              if (inProgressCount > 0)
                _BlinkingOrdersBadge(
                  child: GestureDetector(
                    onTap: () {
                      HapticHelper.lightImpact();
                      context.navigateTo(const OrdersListScreen());
                    },
                    child: Container(
                      padding: EdgeInsets.symmetric(horizontal: badgePadH + 4, vertical: badgePadV + 2),
                      decoration: BoxDecoration(
                        color: Colors.orange.withValues(alpha: 0.2),
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(color: Colors.orange, width: 1.5),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(Icons.receipt_long, size: weatherIconSize + 2, color: Colors.orange),
                          SizedBox(width: isCompact ? 6 : 8),
                          Text(
                            inProgressCount == 1
                                ? '1 commande en cours'
                                : '$inProgressCount commandes en cours',
                            style: TextStyle(
                              fontSize: isVeryCompact ? 11.0 : (isCompact ? 12.0 : 14.0),
                              fontWeight: FontWeight.w600,
                              color: Colors.orange,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                )
              else
                const SizedBox.shrink(),
              // Heure, date, météo
              StreamBuilder(
                stream: Stream.periodic(const Duration(seconds: 1)),
                builder: (context, snapshot) {
                  final now = DateTime.now();
                  final temperature = _currentWeather?.temperature?.celsius?.round() ?? 25;
                  return Row(
                    mainAxisSize: MainAxisSize.min,
                    crossAxisAlignment: CrossAxisAlignment.center,
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
                      Text(
                        DateFormat('EEEE d MMMM', 'fr_FR').format(now),
                        style: TextStyle(
                          fontSize: dateSize,
                          fontWeight: FontWeight.w400,
                          color: AppTheme.textGray,
                          height: 1.0,
                        ),
                      ),
                      SizedBox(width: isCompact ? 10 : 14),
                      Container(
                        padding: EdgeInsets.symmetric(horizontal: badgePadH, vertical: badgePadV),
                        decoration: BoxDecoration(
                          color: AppTheme.accentGold.withValues(alpha: 0.15),
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(color: AppTheme.accentGold, width: 1.5),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Text(
                              _currentWeather != null
                                  ? WeatherService.getWeatherIcon(_currentWeather?.weatherMain)
                                  : '☀️',
                              style: TextStyle(fontSize: weatherIconSize),
                            ),
                            SizedBox(width: isCompact ? 6 : 8),
                            Text(
                              '$temperature°',
                              style: TextStyle(
                                fontSize: tempSize,
                                fontWeight: FontWeight.bold,
                                color: AppTheme.accentGold,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  );
                },
              ),
            ],
          );
        },
      ),
    );
  }
}

/// Badge « commande(s) en cours » avec animation de clignotement (opacité).
class _BlinkingOrdersBadge extends StatefulWidget {
  const _BlinkingOrdersBadge({required this.child});
  final Widget child;

  @override
  State<_BlinkingOrdersBadge> createState() => _BlinkingOrdersBadgeState();
}

class _BlinkingOrdersBadgeState extends State<_BlinkingOrdersBadge>
    with SingleTickerProviderStateMixin {
  late AnimationController _controller;
  late Animation<double> _animation;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      duration: const Duration(milliseconds: 900),
      vsync: this,
    )..repeat(reverse: true);
    _animation = Tween<double>(begin: 0.45, end: 1.0).animate(
      CurvedAnimation(parent: _controller, curve: Curves.easeInOut),
    );
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return FadeTransition(
      opacity: _animation,
      child: widget.child,
    );
  }
}
