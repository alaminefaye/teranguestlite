import 'dart:async';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../providers/auth_provider.dart';
import '../dashboard/dashboard_screen.dart';
import 'package:flutter/foundation.dart' show kIsWeb;
import '../admin/admin_home_screen.dart';
import 'login_screen.dart';
import 'web_login_screen.dart';

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen>
    with TickerProviderStateMixin {
  late AnimationController _logoController;
  late AnimationController _contentController;
  late AnimationController _cardController;

  late Animation<double> _logoFade;
  late Animation<double> _logoScale;
  late Animation<double> _contentFade;
  late Animation<double> _cardFade;

  int _currentServiceIndex = 0;
  Timer? _serviceTimer;

  List<_ServiceItem> _services(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return [
      _ServiceItem(
        icon: Icons.hotel,
        label: l10n.splashRoomAccommodation,
        subtitle: l10n.splashRoomSubtitle,
        color: const Color(0xFFD4AF37),
      ),
      _ServiceItem(
        icon: Icons.restaurant,
        label: l10n.splashRestaurant,
        subtitle: l10n.splashRestaurantSubtitle,
        color: const Color(0xFFE8C96A),
      ),
      _ServiceItem(
        icon: Icons.room_service,
        label: l10n.splashRoomService,
        subtitle: l10n.splashRoomServiceSubtitle,
        color: const Color(0xFFD4AF37),
      ),
      _ServiceItem(
        icon: Icons.spa,
        label: l10n.splashSpa,
        subtitle: l10n.splashSpaSubtitle,
        color: const Color(0xFFE8C96A),
      ),
      _ServiceItem(
        icon: Icons.explore,
        label: l10n.splashExcursions,
        subtitle: l10n.splashExcursionsSubtitle,
        color: const Color(0xFFD4AF37),
      ),
      _ServiceItem(
        icon: Icons.local_laundry_service,
        label: l10n.splashLaundry,
        subtitle: l10n.splashLaundrySubtitle,
        color: const Color(0xFFE8C96A),
      ),
    ];
  }

  @override
  void initState() {
    super.initState();
    _setupAnimations();
    _checkAuth();
  }

  void _setupAnimations() {
    // Logo animation
    _logoController = AnimationController(
      duration: const Duration(milliseconds: 1200),
      vsync: this,
    );
    _logoFade = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(
        parent: _logoController,
        curve: const Interval(0.0, 0.6, curve: Curves.easeIn),
      ),
    );
    _logoScale = Tween<double>(begin: 0.6, end: 1.0).animate(
      CurvedAnimation(
        parent: _logoController,
        curve: const Interval(0.1, 0.8, curve: Curves.easeOutBack),
      ),
    );

    // Content animation (bienvenue + cards)
    _contentController = AnimationController(
      duration: const Duration(milliseconds: 800),
      vsync: this,
    );
    _contentFade = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(parent: _contentController, curve: Curves.easeIn),
    );

    // Card transition animation
    _cardController = AnimationController(
      duration: const Duration(milliseconds: 400),
      vsync: this,
    );
    _cardFade = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(parent: _cardController, curve: Curves.easeInOut),
    );

    _logoController.forward().then((_) {
      _contentController.forward();
      _cardController.forward();
      _startServiceCycle();
    });
  }

  static const int _servicesCount = 6;

  void _startServiceCycle() {
    _serviceTimer = Timer.periodic(const Duration(seconds: 2), (_) {
      if (!mounted) return;
      _cardController.reverse().then((_) {
        if (!mounted) return;
        setState(() {
          _currentServiceIndex = (_currentServiceIndex + 1) % _servicesCount;
        });
        _cardController.forward();
      });
    });
  }

  Future<void> _checkAuth() async {
    await Future.delayed(const Duration(milliseconds: 5000));
    if (!mounted) return;

    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    await authProvider.initAuth();

    if (!mounted) return;

    if (authProvider.isAuthenticated) {
      final home = _resolveHomeScreen(authProvider);
      Navigator.of(
        context,
      ).pushReplacement(MaterialPageRoute(builder: (_) => home));
    } else {
      // Si on est sur le Web, on redirige vers WebLoginScreen
      if (kIsWeb) {
        String? initialCode;
        // Tenter de récupérer le code depuis l'URL si dispo
        if (Uri.base.queryParameters.containsKey('code')) {
          initialCode = Uri.base.queryParameters['code'];
        }

        Navigator.of(context).pushReplacement(
          MaterialPageRoute(
            builder: (_) => WebLoginScreen(initialCode: initialCode),
          ),
        );
      } else {
        Navigator.of(context).pushReplacement(
          MaterialPageRoute(builder: (_) => const LoginScreen()),
        );
      }
    }
  }

  Widget _resolveHomeScreen(AuthProvider auth) {
    if (auth.isAdmin || auth.isStaff) return const AdminHomeScreen();
    return const DashboardScreen();
  }

  @override
  void dispose() {
    _serviceTimer?.cancel();
    _logoController.dispose();
    _contentController.dispose();
    _cardController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final servicesList = _services(context);
    final service = servicesList[_currentServiceIndex];

    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(gradient: AppTheme.backgroundGradient),
        child: Stack(
          children: [
            // Cercles décoratifs en arrière-plan
            Positioned(
              top: -80,
              right: -80,
              child: _GlowCircle(size: 260, opacity: 0.06),
            ),
            Positioned(
              bottom: -60,
              left: -60,
              child: _GlowCircle(size: 220, opacity: 0.05),
            ),
            Positioned(
              top: 180,
              left: -40,
              child: _GlowCircle(size: 120, opacity: 0.04),
            ),

            // Contenu principal
            SafeArea(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Spacer(flex: 2),

                  // Logo
                  AnimatedBuilder(
                    animation: _logoController,
                    builder: (context, _) {
                      return FadeTransition(
                        opacity: _logoFade,
                        child: ScaleTransition(
                          scale: _logoScale,
                          child: Image.asset('assets/logo.png', width: 230),
                        ),
                      );
                    },
                  ),

                  const SizedBox(height: 12),

                  // Bienvenue
                  FadeTransition(
                    opacity: _contentFade,
                    child: const Text(
                      'Bienvenue',
                      style: TextStyle(
                        fontSize: 20,
                        color: AppTheme.textGray,
                        letterSpacing: 2,
                        fontWeight: FontWeight.w300,
                      ),
                    ),
                  ),

                  const Spacer(flex: 1),

                  // Carte service animée
                  FadeTransition(
                    opacity: _contentFade,
                    child: AnimatedBuilder(
                      animation: _cardFade,
                      builder: (context, _) {
                        return Opacity(
                          opacity: _cardFade.value,
                          child: _ServiceCard(service: service),
                        );
                      },
                    ),
                  ),

                  const SizedBox(height: 32),

                  // Indicateurs de points
                  FadeTransition(
                    opacity: _contentFade,
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: List.generate(servicesList.length, (i) {
                        return AnimatedContainer(
                          duration: const Duration(milliseconds: 300),
                          margin: const EdgeInsets.symmetric(horizontal: 4),
                          width: i == _currentServiceIndex ? 20 : 6,
                          height: 6,
                          decoration: BoxDecoration(
                            color: i == _currentServiceIndex
                                ? AppTheme.accentGold
                                : AppTheme.accentGold.withValues(alpha: 0.3),
                            borderRadius: BorderRadius.circular(3),
                          ),
                        );
                      }),
                    ),
                  ),

                  const SizedBox(height: 40),

                  // Loading indicator
                  FadeTransition(
                    opacity: _contentFade,
                    child: SizedBox(
                      width: 28,
                      height: 28,
                      child: CircularProgressIndicator(
                        strokeWidth: 2,
                        valueColor: AlwaysStoppedAnimation<Color>(
                          AppTheme.accentGold.withValues(alpha: 0.8),
                        ),
                      ),
                    ),
                  ),

                  const Spacer(flex: 1),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// --- Carte service ---
class _ServiceCard extends StatelessWidget {
  final _ServiceItem service;
  const _ServiceCard({required this.service});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 36),
      padding: const EdgeInsets.symmetric(vertical: 20, horizontal: 24),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(20),
        color: Colors.white.withValues(alpha: 0.04),
        border: Border.all(
          color: AppTheme.accentGold.withValues(alpha: 0.25),
          width: 1,
        ),
        boxShadow: [
          BoxShadow(
            color: AppTheme.accentGold.withValues(alpha: 0.08),
            blurRadius: 20,
            spreadRadius: 2,
          ),
        ],
      ),
      child: Row(
        children: [
          Container(
            width: 52,
            height: 52,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: AppTheme.accentGold.withValues(alpha: 0.12),
              border: Border.all(
                color: AppTheme.accentGold.withValues(alpha: 0.4),
                width: 1,
              ),
            ),
            child: Icon(service.icon, color: service.color, size: 26),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  service.label,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 14,
                    fontWeight: FontWeight.w600,
                    letterSpacing: 0.3,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  service.subtitle,
                  style: TextStyle(
                    color: AppTheme.textGray.withValues(alpha: 0.8),
                    fontSize: 12,
                  ),
                ),
              ],
            ),
          ),
          Icon(
            Icons.arrow_forward_ios,
            color: AppTheme.accentGold.withValues(alpha: 0.5),
            size: 14,
          ),
        ],
      ),
    );
  }
}

// --- Cercle décoratif ---
class _GlowCircle extends StatelessWidget {
  final double size;
  final double opacity;
  const _GlowCircle({required this.size, required this.opacity});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        color: AppTheme.accentGold.withValues(alpha: opacity),
      ),
    );
  }
}

// --- Modèle de service ---
class _ServiceItem {
  final IconData icon;
  final String label;
  final String subtitle;
  final Color color;

  const _ServiceItem({
    required this.icon,
    required this.label,
    required this.subtitle,
    required this.color,
  });
}
