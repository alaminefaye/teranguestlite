import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:weather/weather.dart';
import '../../config/theme.dart';
import '../../widgets/service_card.dart';
import '../../services/weather_service.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  final WeatherService _weatherService = WeatherService();
  Weather? _currentWeather;
  bool _isLoadingWeather = true;

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
          _isLoadingWeather = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _isLoadingWeather = false;
        });
      }
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
                    onPressed: () {},
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
                'KING FAHD PALACE HOTEL',
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
    return Padding(
      padding: const EdgeInsets.only(left: 40.0, right: 40.0, top: 25.0, bottom: 15.0),
      child: Column(
        children: [
          Text(
            'Bienvenue au King Fahd Palace Hotel',
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
            'Votre assistant digital est à votre service',
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
    // 8 services selon la documentation MOBILE-APP-FONCTIONNALITES.md
    final services = [
      {
        'title': 'Room Service',
        'icon': Icons.room_service_outlined,
        'route': '/room-service',
      },
      {
        'title': 'Restaurants & Bars',
        'icon': Icons.restaurant_menu_outlined,
        'route': '/restaurants',
      },
      {
        'title': 'Spa & Bien-être',
        'icon': Icons.spa_outlined,
        'route': '/spa',
      },
      {
        'title': 'Services Palace',
        'icon': Icons.auto_awesome_outlined,
        'route': '/palace',
      },
      {
        'title': 'Excursions',
        'icon': Icons.terrain_outlined,
        'route': '/excursions',
      },
      {
        'title': 'Blanchisserie',
        'icon': Icons.local_laundry_service_outlined,
        'route': '/laundry',
      },
      {
        'title': 'Conciergerie',
        'icon': Icons.headset_mic_outlined,
        'route': '/concierge',
      },
      {
        'title': 'Centre d\'Appels',
        'icon': Icons.phone_outlined,
        'route': 'tel:',
      },
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
              // TODO: Navigate to service screen
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text('Navigation vers ${service['title']}'),
                  backgroundColor: AppTheme.accentGold,
                  duration: const Duration(seconds: 1),
                ),
              );
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
                          color: AppTheme.accentGold.withOpacity(0.15),
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
