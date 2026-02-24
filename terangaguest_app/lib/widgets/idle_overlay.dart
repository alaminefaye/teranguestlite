import 'dart:async';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../config/theme.dart';
import '../providers/tablet_session_provider.dart';
import '../providers/cart_provider.dart';
import '../providers/favorites_provider.dart';
import '../providers/orders_provider.dart';
import '../providers/restaurants_provider.dart';
import '../providers/spa_provider.dart';
import '../providers/excursions_provider.dart';
import '../providers/laundry_provider.dart';
import '../providers/palace_provider.dart';

/// Overlay de veille pour tablette en chambre.
///
/// Détecte l'inactivité et affiche un écran de veille premium.
/// Au tap, vérifie silencieusement la session invité :
///   - Valide  → ferme l'overlay
///   - Expirée → vide panier + session, ferme l'overlay
class IdleOverlay extends StatefulWidget {
  final Widget child;

  /// Délai avant affichage de l'overlay (1 min pour tests, 1h en prod)
  final Duration idleDuration;

  /// Appelé quand la session est expirée, après nettoyage des données.
  /// Utiliser pour naviguer vers un écran neutre (ex. DashboardScreen).
  final VoidCallback? onSessionExpired;

  const IdleOverlay({
    super.key,
    required this.child,
    this.idleDuration = const Duration(minutes: 1),
    this.onSessionExpired,
  });

  @override
  State<IdleOverlay> createState() => _IdleOverlayState();
}

class _IdleOverlayState extends State<IdleOverlay>
    with SingleTickerProviderStateMixin {
  Timer? _idleTimer;
  Timer? _clockTimer;
  bool _showOverlay = false;
  bool _isVerifying = false;
  DateTime _now = DateTime.now();

  late AnimationController _fadeController;
  late Animation<double> _fadeAnimation;

  @override
  void initState() {
    super.initState();

    _fadeController = AnimationController(
      duration: const Duration(milliseconds: 600),
      vsync: this,
    );
    _fadeAnimation = CurvedAnimation(
      parent: _fadeController,
      curve: Curves.easeInOut,
    );

    _resetIdleTimer();
    _startClock();
  }

  @override
  void dispose() {
    _idleTimer?.cancel();
    _clockTimer?.cancel();
    _fadeController.dispose();
    super.dispose();
  }

  void _startClock() {
    _clockTimer = Timer.periodic(const Duration(seconds: 1), (_) {
      if (mounted) setState(() => _now = DateTime.now());
    });
  }

  void _resetIdleTimer() {
    _idleTimer?.cancel();
    _idleTimer = Timer(widget.idleDuration, _showIdleOverlay);
  }

  void _onUserInteraction() {
    if (_showOverlay) return; // Ignore pendant que l'overlay est affiché
    _resetIdleTimer();
  }

  void _showIdleOverlay() {
    if (!mounted) return;
    setState(() => _showOverlay = true);
    _fadeController.forward();
  }

  Future<void> _onOverlayTap() async {
    if (_isVerifying) return;

    final tabletSession = context.read<TabletSessionProvider>();
    final cartProvider = context.read<CartProvider>();
    final ordersProvider = context.read<OrdersProvider>();
    final favoritesProvider = context.read<FavoritesProvider>();
    final restaurantsProvider = context.read<RestaurantsProvider>();
    final spaProvider = context.read<SpaProvider>();
    final excursionsProvider = context.read<ExcursionsProvider>();
    final laundryProvider = context.read<LaundryProvider>();
    final palaceProvider = context.read<PalaceProvider>();

    // Si pas de session sauvegardée → fermer directement
    if (!tabletSession.hasSession) {
      _dismissOverlay();
      return;
    }

    // Vérification silencieuse de la session
    setState(() => _isVerifying = true);

    try {
      await tabletSession.validateCurrentSession();
      // Session valide → fermer l'overlay
      if (mounted) _dismissOverlay();
    } catch (_) {
      // Session expirée (checkout) → nettoyer toutes les données utilisateur
      await tabletSession.clearSession();

      // Vider toutes les données utilisateur
      cartProvider.clear();
      ordersProvider.clearOrdersAndSetLoading();
      await favoritesProvider.clearAll();
      restaurantsProvider.clearUserData();
      spaProvider.clearUserData();
      excursionsProvider.clearUserData();
      laundryProvider.clearUserData();
      palaceProvider.clearUserData();

      if (mounted) _dismissOverlay();
      // Notifier le parent pour naviguer vers un écran neutre
      widget.onSessionExpired?.call();
    } finally {
      if (mounted) setState(() => _isVerifying = false);
    }
  }

  void _dismissOverlay() {
    _fadeController.reverse().then((_) {
      if (mounted) {
        setState(() => _showOverlay = false);
        _resetIdleTimer();
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Listener(
      behavior: HitTestBehavior.translucent,
      onPointerDown: (_) => _onUserInteraction(),
      child: Stack(
        children: [
          widget.child,
          if (_showOverlay)
            FadeTransition(
              opacity: _fadeAnimation,
              child: _IdleScreen(
                now: _now,
                isVerifying: _isVerifying,
                onTap: _onOverlayTap,
              ),
            ),
        ],
      ),
    );
  }
}

// ─────────────────────────────────────────────
// Écran de veille premium
// ─────────────────────────────────────────────
class _IdleScreen extends StatefulWidget {
  final DateTime now;
  final bool isVerifying;
  final VoidCallback onTap;

  const _IdleScreen({
    required this.now,
    required this.isVerifying,
    required this.onTap,
  });

  @override
  State<_IdleScreen> createState() => _IdleScreenState();
}

class _IdleScreenState extends State<_IdleScreen>
    with SingleTickerProviderStateMixin {
  late AnimationController _pulseController;
  late Animation<double> _pulseAnimation;

  @override
  void initState() {
    super.initState();
    _pulseController = AnimationController(
      duration: const Duration(milliseconds: 1800),
      vsync: this,
    )..repeat(reverse: true);
    _pulseAnimation = Tween<double>(begin: 0.6, end: 1.0).animate(
      CurvedAnimation(parent: _pulseController, curve: Curves.easeInOut),
    );
  }

  @override
  void dispose() {
    _pulseController.dispose();
    super.dispose();
  }

  String _formatTime(DateTime dt) {
    final h = dt.hour.toString().padLeft(2, '0');
    final m = dt.minute.toString().padLeft(2, '0');
    final s = dt.second.toString().padLeft(2, '0');
    return '$h:$m:$s';
  }

  String _formatDate(DateTime dt) {
    const jours = [
      'Lundi',
      'Mardi',
      'Mercredi',
      'Jeudi',
      'Vendredi',
      'Samedi',
      'Dimanche',
    ];
    const mois = [
      'janvier',
      'février',
      'mars',
      'avril',
      'mai',
      'juin',
      'juillet',
      'août',
      'septembre',
      'octobre',
      'novembre',
      'décembre',
    ];
    final jour = jours[dt.weekday - 1];
    final moisNom = mois[dt.month - 1];
    return '$jour ${dt.day} $moisNom ${dt.year}';
  }

  @override
  Widget build(BuildContext context) {
    final size = MediaQuery.of(context).size;
    final w = size.width;
    final isLandscape = w > size.height;
    // Taille de l'heure adaptée au mobile pour éviter le débordement sur Android
    final double timeSize;
    final double timeLetterSpacing;
    if (w < 380) {
      timeSize = 42.0;
      timeLetterSpacing = 2.0;
    } else if (w < 450 || !isLandscape) {
      timeSize = 52.0;
      timeLetterSpacing = 4.0;
    } else {
      timeSize = 72.0;
      timeLetterSpacing = 8.0;
    }
    final logoWidth = isLandscape ? 200.0 : (w < 380 ? 140.0 : 160.0);

    return GestureDetector(
      onTap: widget.onTap,
      child: Container(
        width: double.infinity,
        height: double.infinity,
        decoration: const BoxDecoration(gradient: AppTheme.backgroundGradient),
        child: Stack(
          children: [
            // Cercles décoratifs
            Positioned(
              top: -80,
              right: -80,
              child: _GlowCircle(size: 300, opacity: 0.07),
            ),
            Positioned(
              bottom: -60,
              left: -60,
              child: _GlowCircle(size: 250, opacity: 0.05),
            ),
            Positioned(
              top: 220,
              left: -40,
              child: _GlowCircle(size: 140, opacity: 0.04),
            ),

            // Contenu centré (prend toute la surface)
            SizedBox.expand(
              child: SafeArea(
                child: Center(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    crossAxisAlignment: CrossAxisAlignment.center,
                    children: [
                      // Logo hôtel
                      Image.asset('assets/logo.png', width: logoWidth),

                      SizedBox(height: w < 380 ? 24 : 40),

                      // Heure (FittedBox évite tout débordement)
                      FittedBox(
                        fit: BoxFit.scaleDown,
                        child: Padding(
                          padding: const EdgeInsets.symmetric(horizontal: 16),
                          child: Text(
                            _formatTime(widget.now),
                            style: TextStyle(
                              fontSize: timeSize,
                              fontWeight: FontWeight.w200,
                              color: Colors.white,
                              letterSpacing: timeLetterSpacing,
                              height: 1,
                              decoration: TextDecoration.none,
                            ),
                          ),
                        ),
                      ),

                      SizedBox(height: w < 380 ? 8 : 12),

                      // Date
                      Text(
                        _formatDate(widget.now),
                        style: TextStyle(
                          fontSize: w < 380 ? 13 : 16,
                          color: Colors.white,
                          letterSpacing: 1.5,
                          fontWeight: FontWeight.w300,
                          decoration: TextDecoration.none,
                        ),
                        textAlign: TextAlign.center,
                      ),

                      SizedBox(height: w < 380 ? 32 : 52),

                      // "Appuyez pour continuer" ou loading
                      if (widget.isVerifying)
                        SizedBox(
                          width: 32,
                          height: 32,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            valueColor: AlwaysStoppedAnimation<Color>(
                              AppTheme.accentGold.withValues(alpha: 0.8),
                            ),
                          ),
                        )
                      else
                        AnimatedBuilder(
                          animation: _pulseAnimation,
                          builder: (context, child) => Opacity(
                            opacity: _pulseAnimation.value,
                            child: Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 36,
                                vertical: 16,
                              ),
                              decoration: BoxDecoration(
                                borderRadius: BorderRadius.circular(50),
                                border: Border.all(
                                  color: AppTheme.accentGold,
                                  width: 1.5,
                                ),
                                color: AppTheme.accentGold.withValues(
                                  alpha: 0.18,
                                ),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  const Icon(
                                    Icons.touch_app_outlined,
                                    color: Colors.white,
                                    size: 20,
                                  ),
                                  const SizedBox(width: 10),
                                  const Text(
                                    'Appuyez pour continuer',
                                    style: TextStyle(
                                      fontSize: 16,
                                      color: Colors.white,
                                      letterSpacing: 1.2,
                                      fontWeight: FontWeight.w400,
                                      decoration: TextDecoration.none,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ),
                    ],
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

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
