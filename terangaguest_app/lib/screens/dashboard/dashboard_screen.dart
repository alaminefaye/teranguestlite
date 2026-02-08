import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import 'package:weather/weather.dart';
import 'package:cached_network_image/cached_network_image.dart';
import '../../config/theme.dart';
import '../../config/api_config.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../providers/auth_provider.dart';
import '../../providers/orders_provider.dart';
import '../../widgets/service_card.dart';
import '../../services/weather_service.dart';
import '../../utils/navigation_helper.dart';
import '../../utils/haptic_helper.dart';
import '../../utils/layout_helper.dart';
import '../room_service/categories_screen.dart';
import '../room_service/cart_screen.dart';
import '../profile/profile_screen.dart';
import '../orders/orders_list_screen.dart';
import '../restaurants/restaurants_list_screen.dart';
import '../spa/spa_services_list_screen.dart';
import '../excursions/excursions_list_screen.dart';
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
      case '/spa':
        context.navigateTo(const SpaServicesListScreen());
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
              // Header (icônes notifications + profil)
              _buildHeader(context),
              // Bloc style 3e image : logo centré + slogan, puis bannière avec photo et nom entreprise
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

  Widget _buildHeader(BuildContext context) {
    final h = MediaQuery.sizeOf(context).height;
    final isLandscape = MediaQuery.of(context).orientation == Orientation.landscape;
    final isCompact = isLandscape ? (h < 900) : (h < 600);
    final isVeryCompact = h < 500;
    final topPad = isVeryCompact ? 4.0 : (isCompact ? 6.0 : 12.0);
    final bottomPad = isVeryCompact ? 2.0 : (isCompact ? 2.0 : 6.0);
    final iconSize = isVeryCompact ? 22.0 : (isCompact ? 24.0 : 30.0);

    return Padding(
      padding: EdgeInsets.only(left: 24.0, right: 24.0, top: topPad, bottom: bottomPad),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          const SizedBox(width: 40),
          const Spacer(),
          Row(
            children: [
              Stack(
                clipBehavior: Clip.none,
                children: [
                  IconButton(
                    onPressed: () {},
                    padding: EdgeInsets.zero,
                    constraints: const BoxConstraints(),
                    iconSize: iconSize,
                    icon: const Icon(Icons.notifications_none, color: AppTheme.accentGold),
                  ),
                  Positioned(
                    right: 2,
                    top: 2,
                    child: Container(
                      width: isVeryCompact ? 6 : 8,
                      height: isVeryCompact ? 6 : 8,
                      decoration: BoxDecoration(
                        color: Colors.red,
                        shape: BoxShape.circle,
                        border: Border.all(color: AppTheme.primaryDark, width: 1),
                      ),
                    ),
                  ),
                ],
              ),
              SizedBox(width: isCompact ? 8 : 16),
              IconButton(
                onPressed: () {
                  HapticHelper.lightImpact();
                  context.navigateTo(const ProfileScreen());
                },
                padding: EdgeInsets.zero,
                constraints: const BoxConstraints(),
                iconSize: iconSize,
                icon: const Icon(Icons.person_outline, color: AppTheme.accentGold),
              ),
            ],
          ),
        ],
      ),
    );
  }

  /// Bloc style 3e image : logo entreprise centré + slogan, puis bannière avec image et nom en overlay.
  Widget _buildEnterpriseHero(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final user = context.watch<AuthProvider>().user;
    final enterprise = user?.enterprise;
    final enterpriseName = enterprise?.name.trim() ?? '';
    final logoPath = enterprise?.logo;
    final logoUrl = (logoPath != null && logoPath.isNotEmpty)
        ? ApiConfig.storageUrl(logoPath)
        : null;

    final h = MediaQuery.sizeOf(context).height;
    final isLandscape = MediaQuery.of(context).orientation == Orientation.landscape;
    final isCompact = isLandscape ? (h < 900) : (h < 600);
    final isVeryCompact = h < 500;
    final pad = LayoutHelper.horizontalPaddingValue(context);
    final logoSize = isVeryCompact ? 48.0 : (isCompact ? 64.0 : 88.0);
    final subtitleSize = isVeryCompact ? 10.0 : (isCompact ? 12.0 : 14.0);
    final nameOnBannerSize = isVeryCompact ? 18.0 : (isCompact ? 22.0 : 28.0);
    final heroTopPad = isVeryCompact ? 4.0 : (isCompact ? 8.0 : 12.0);
    final bannerHeight = isVeryCompact ? 72.0 : (isCompact ? 96.0 : 120.0);

    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        // Zone dégradé : logo centré + slogan (comme 3e image)
        Container(
          width: double.infinity,
          padding: EdgeInsets.only(left: pad, right: pad, top: heroTopPad, bottom: 12),
          decoration: BoxDecoration(
            gradient: LinearGradient(
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
              colors: [
                AppTheme.primaryDark,
                AppTheme.primaryBlue,
                AppTheme.primaryBlue.withValues(alpha: 0.98),
              ],
            ),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              if (logoUrl != null && logoUrl.isNotEmpty)
                ClipRect(
                  child: CachedNetworkImage(
                    imageUrl: logoUrl,
                    height: logoSize,
                    fit: BoxFit.contain,
                    placeholder: (_, __) => SizedBox(
                      height: logoSize,
                      child: Center(
                        child: SizedBox(
                          width: logoSize * 0.5,
                          height: logoSize * 0.5,
                          child: const CircularProgressIndicator(
                            strokeWidth: 2,
                            color: AppTheme.accentGold,
                          ),
                        ),
                      ),
                    ),
                    errorWidget: (_, __, ___) => _buildTerangaGuestText(logoSize),
                  ),
                )
              else
                _buildTerangaGuestText(logoSize),
              SizedBox(height: isVeryCompact ? 4 : 8),
              Text(
                l10n.welcomeSubtitle,
                textAlign: TextAlign.center,
                style: TextStyle(
                  color: AppTheme.textGray,
                  fontSize: subtitleSize,
                  fontWeight: FontWeight.w400,
                ),
              ),
            ],
          ),
        ),
        // Bannière : image de fond (logo ou dégradé) + nom entreprise en gros en bas à gauche
        Container(
          height: bannerHeight,
          width: double.infinity,
          margin: EdgeInsets.only(left: pad, right: pad, top: 8, bottom: 8),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(12),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.35),
                blurRadius: 8,
                offset: const Offset(0, 2),
              ),
            ],
          ),
          child: ClipRRect(
            borderRadius: BorderRadius.circular(12),
            child: Stack(
              fit: StackFit.expand,
              children: [
                if (logoUrl != null && logoUrl.isNotEmpty)
                  CachedNetworkImage(
                    imageUrl: logoUrl,
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
                Positioned.fill(
                  child: DecoratedBox(
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        begin: Alignment.topCenter,
                        end: Alignment.bottomCenter,
                        colors: [
                          Colors.transparent,
                          Colors.black54,
                          Colors.black87,
                        ],
                      ),
                    ),
                    child: const SizedBox.shrink(),
                  ),
                ),
                Align(
                  alignment: Alignment.bottomLeft,
                  child: Padding(
                    padding: const EdgeInsets.only(left: 16, bottom: 12, right: 16),
                    child: Text(
                      enterpriseName.isNotEmpty ? enterpriseName : l10n.welcomeTitle,
                      style: TextStyle(
                        fontSize: nameOnBannerSize,
                        fontWeight: FontWeight.bold,
                        color: Colors.white,
                        shadows: [
                          Shadow(
                            color: Colors.black.withValues(alpha: 0.8),
                            offset: const Offset(0, 1),
                            blurRadius: 4,
                          ),
                        ],
                      ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildTerangaGuestText(double logoSize) {
    final fontSize = (logoSize * 0.28).clamp(14.0, 26.0);
    return RichText(
      text: TextSpan(
        children: [
          TextSpan(
            text: 'TERAN',
            style: TextStyle(
              fontSize: fontSize,
              fontWeight: FontWeight.bold,
              color: AppTheme.textWhite,
              letterSpacing: 2.0,
            ),
          ),
          TextSpan(
            text: 'GUEST',
            style: TextStyle(
              fontSize: fontSize,
              fontWeight: FontWeight.bold,
              color: AppTheme.accentGold,
              letterSpacing: 2.0,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildServicesGrid(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final services = [
      {'title': l10n.roomService, 'icon': Icons.room_service_outlined, 'route': '/room-service'},
      {'title': l10n.restaurantsBars, 'icon': Icons.restaurant_menu_outlined, 'route': '/restaurants'},
      {'title': l10n.spaWellness, 'icon': Icons.spa_outlined, 'route': '/spa'},
      {'title': l10n.palaceServices, 'icon': Icons.auto_awesome_outlined, 'route': '/palace'},
      {'title': l10n.excursions, 'icon': Icons.terrain_outlined, 'route': '/excursions'},
      {'title': l10n.laundry, 'icon': Icons.local_laundry_service_outlined, 'route': '/laundry'},
      {'title': l10n.concierge, 'icon': Icons.headset_mic_outlined, 'route': '/concierge'},
      {'title': l10n.callCenter, 'icon': Icons.phone_outlined, 'route': 'tel:'},
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
