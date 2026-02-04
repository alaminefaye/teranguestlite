import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:weather/weather.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../widgets/service_card.dart';
import '../../services/weather_service.dart';
import '../../utils/navigation_helper.dart';
import '../../utils/haptic_helper.dart';
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
              // Header
              _buildHeader(context),
              
              // Welcome Message
              _buildWelcomeSection(context),
              
              // Services Grid
              Expanded(
                child: _buildServicesGrid(context),
              ),
              
              // Footer
              _buildFooter(context),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildHeader(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(left: 24.0, right: 24.0, top: 30.0, bottom: 10.0),
      child: Column(
        children: [
          // Ligne 1 : TERANGUEST + Icônes (reste en haut)
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Gauche : TERANGUEST (TERAN blanc + GUEST or)
              RichText(
                text: const TextSpan(
                  children: [
                    TextSpan(
                      text: 'TERAN',
                      style: TextStyle(
                        fontSize: 26,
                        fontWeight: FontWeight.bold,
                        color: AppTheme.textWhite,
                        letterSpacing: 2.0,
                      ),
                    ),
                    TextSpan(
                      text: 'GUEST',
                      style: TextStyle(
                        fontSize: 26,
                        fontWeight: FontWeight.bold,
                        color: AppTheme.accentGold,
                        letterSpacing: 2.0,
                      ),
                    ),
                  ],
                ),
              ),
              // Droite : Icônes notification + profil
              Row(
                children: [
                  // Notification avec badge rouge
                  Stack(
                    clipBehavior: Clip.none,
                    children: [
                      IconButton(
                        onPressed: () {},
                        padding: EdgeInsets.zero,
                        constraints: const BoxConstraints(),
                        iconSize: 36,
                        icon: const Icon(
                          Icons.notifications_none,
                          color: AppTheme.accentGold,
                        ),
                      ),
                      Positioned(
                        right: 4,
                        top: 4,
                        child: Container(
                          width: 10,
                          height: 10,
                          decoration: BoxDecoration(
                            color: Colors.red,
                            shape: BoxShape.circle,
                            border: Border.all(
                              color: AppTheme.primaryDark,
                              width: 1.5,
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(width: 16),
                  // Profil
                  IconButton(
                    onPressed: () {
                      HapticHelper.lightImpact();
                      context.navigateTo(const ProfileScreen());
                    },
                    padding: EdgeInsets.zero,
                    constraints: const BoxConstraints(),
                    iconSize: 36,
                    icon: const Icon(
                      Icons.person_outline,
                      color: AppTheme.accentGold,
                    ),
                  ),
                ],
              ),
            ],
          ),
          
          // ESPACE pour descendre la couronne + nom
          const SizedBox(height: 40),
          
          // Ligne 2 : Couronne + Nom + Étoiles (descendu)
          Column(
            children: [
              // Couronne dorée
              const Icon(
                Icons.diamond,
                color: AppTheme.accentGold,
                size: 32,
              ),
              const SizedBox(height: 8),
              // Nom de l'hôtel
              Text(
                AppLocalizations.of(context).hotelName,
                textAlign: TextAlign.center,
                style: Theme.of(context).textTheme.titleLarge?.copyWith(
                      fontSize: 16,
                      fontWeight: FontWeight.w600,
                      letterSpacing: 1.5,
                      color: AppTheme.textWhite,
                    ),
              ),
              const SizedBox(height: 6),
              // 3 étoiles dorées
              Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: List.generate(
                  3,
                  (index) => const Padding(
                    padding: EdgeInsets.symmetric(horizontal: 3.0),
                    child: Icon(
                      Icons.star,
                      color: AppTheme.accentGold,
                      size: 14,
                    ),
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildWelcomeSection(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return Padding(
      padding: const EdgeInsets.only(left: 40.0, right: 40.0, top: 25.0, bottom: 15.0),
      child: Column(
        children: [
          Text(
            l10n.welcomeTitle,
            textAlign: TextAlign.center,
            style: Theme.of(context).textTheme.displayMedium?.copyWith(
                  fontSize: 32,
                  fontWeight: FontWeight.bold,
                  color: AppTheme.textWhite,
                  height: 1.2,
                ),
          ),
          const SizedBox(height: 10),
          Text(
            l10n.welcomeSubtitle,
            textAlign: TextAlign.center,
            style: Theme.of(context).textTheme.bodyLarge?.copyWith(
                  color: AppTheme.textGray,
                  fontSize: 15,
                  fontWeight: FontWeight.w400,
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

    // Layout pour tablette: 4 colonnes x 2 rangées
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 40.0),
      child: GridView.builder(
        padding: const EdgeInsets.symmetric(vertical: 20),
        gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
          crossAxisCount: 4, // 4 colonnes comme sur la tablette
          crossAxisSpacing: 20,
          mainAxisSpacing: 20,
          childAspectRatio: 1.35, // Un peu plus petit
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
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 20, horizontal: 28),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.end,
        children: [
          StreamBuilder(
            stream: Stream.periodic(const Duration(seconds: 1)),
            builder: (context, snapshot) {
              final now = DateTime.now();
              final temperature = _currentWeather?.temperature?.celsius?.round() ?? 25;
              
              return Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                mainAxisSize: MainAxisSize.min,
                children: [
                  // Heure + Badge météo sur la MÊME LIGNE
                  Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      // Heure
                      Text(
                        DateFormat('hh:mm a').format(now).toUpperCase(),
                        style: const TextStyle(
                          fontSize: 26,
                          fontWeight: FontWeight.bold,
                          color: AppTheme.accentGold,
                          height: 1.0,
                        ),
                      ),
                      const SizedBox(width: 16),
                      // Badge météo avec icône + température
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                        decoration: BoxDecoration(
                          color: AppTheme.accentGold.withValues(alpha: 0.15),
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(
                            color: AppTheme.accentGold,
                            width: 1.5,
                          ),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            // Icône météo
                            Text(
                              _currentWeather != null 
                                  ? WeatherService.getWeatherIcon(_currentWeather?.weatherMain)
                                  : '☀️',
                              style: const TextStyle(fontSize: 22),
                            ),
                            const SizedBox(width: 8),
                            // Température
                            Text(
                              '$temperature°',
                              style: const TextStyle(
                                fontSize: 24,
                                fontWeight: FontWeight.bold,
                                color: AppTheme.accentGold,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 6),
                  // Date en dessous
                  Text(
                    DateFormat('EEEE d MMMM', 'fr_FR').format(now),
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w400,
                      color: AppTheme.textGray,
                      height: 1.2,
                    ),
                  ),
                ],
              );
            },
          ),
        ],
      ),
    );
  }
}
