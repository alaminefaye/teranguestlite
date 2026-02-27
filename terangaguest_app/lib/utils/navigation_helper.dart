import 'package:flutter/material.dart';

/// Helper pour des transitions de navigation fluides et élégantes
class NavigationHelper {
  /// Transition slide de droite vers gauche (standard iOS/Android)
  static Route<T> slideRoute<T>(Widget page) {
    return PageRouteBuilder<T>(
      pageBuilder: (context, animation, secondaryAnimation) => page,
      transitionsBuilder: (context, animation, secondaryAnimation, child) {
        const begin = Offset(1.0, 0.0);
        const end = Offset.zero;
        const curve = Curves.easeInOut;

        var tween = Tween(
          begin: begin,
          end: end,
        ).chain(CurveTween(curve: curve));

        return SlideTransition(position: animation.drive(tween), child: child);
      },
      transitionDuration: const Duration(milliseconds: 300),
    );
  }

  /// Transition fade élégante
  static Route<T> fadeRoute<T>(Widget page) {
    return PageRouteBuilder<T>(
      pageBuilder: (context, animation, secondaryAnimation) => page,
      transitionsBuilder: (context, animation, secondaryAnimation, child) {
        return FadeTransition(opacity: animation, child: child);
      },
      transitionDuration: const Duration(milliseconds: 250),
    );
  }

  /// Transition scale pour les dialogues/modals
  static Route<T> scaleRoute<T>(Widget page) {
    return PageRouteBuilder<T>(
      pageBuilder: (context, animation, secondaryAnimation) => page,
      transitionsBuilder: (context, animation, secondaryAnimation, child) {
        const curve = Curves.easeOutCubic;
        var curvedAnimation = CurvedAnimation(parent: animation, curve: curve);

        return ScaleTransition(
          scale: Tween<double>(begin: 0.8, end: 1.0).animate(curvedAnimation),
          child: FadeTransition(opacity: curvedAnimation, child: child),
        );
      },
      transitionDuration: const Duration(milliseconds: 300),
    );
  }

  /// Transition slide + fade combinée (ultra fluide)
  static Route<T> slideFadeRoute<T>(Widget page) {
    return PageRouteBuilder<T>(
      pageBuilder: (context, animation, secondaryAnimation) => page,
      transitionsBuilder: (context, animation, secondaryAnimation, child) {
        const begin = Offset(0.3, 0.0);
        const end = Offset.zero;
        const curve = Curves.easeOutCubic;

        var slideTween = Tween(
          begin: begin,
          end: end,
        ).chain(CurveTween(curve: curve));

        var fadeTween = Tween<double>(
          begin: 0.0,
          end: 1.0,
        ).chain(CurveTween(curve: curve));

        return SlideTransition(
          position: animation.drive(slideTween),
          child: FadeTransition(
            opacity: animation.drive(fadeTween),
            child: child,
          ),
        );
      },
      transitionDuration: const Duration(milliseconds: 350),
    );
  }

  /// Récupère le Navigator de façon sûre (évite "Null check operator" si le contexte
  /// est désactivé, ex. bouton SnackBar après un pop, ou pas de Navigator).
  static NavigatorState? _navigator(BuildContext context) {
    try {
      return Navigator.maybeOf(context);
    } catch (_) {
      return null;
    }
  }

  /// Navigation avec animation slide standard
  static Future<T?> navigateToSlide<T>(BuildContext context, Widget page) {
    return _navigator(context)?.push<T>(slideRoute(page)) ??
        Future<T?>.value(null);
  }

  /// Navigation avec animation fade
  static Future<T?> navigateToFade<T>(BuildContext context, Widget page) {
    return _navigator(context)?.push<T>(fadeRoute(page)) ??
        Future<T?>.value(null);
  }

  /// Navigation avec animation scale
  static Future<T?> navigateToScale<T>(BuildContext context, Widget page) {
    return _navigator(context)?.push<T>(scaleRoute(page)) ??
        Future<T?>.value(null);
  }

  /// Navigation avec animation slide+fade combinée (recommandé)
  static Future<T?> navigateTo<T>(BuildContext context, Widget page) {
    return _navigator(context)?.push<T>(slideFadeRoute(page)) ??
        Future<T?>.value(null);
  }

  /// Remplacer l'écran actuel avec animation
  static Future<T?> replaceWith<T>(BuildContext context, Widget page) {
    return _navigator(
          context,
        )?.pushReplacement<T, void>(slideFadeRoute(page)) ??
        Future<T?>.value(null);
  }

  /// Navigation vers écran et supprimer tous les précédents
  static Future<T?> navigateAndRemoveUntil<T>(
    BuildContext context,
    Widget page, {
    bool Function(Route<dynamic>)? predicate,
  }) {
    return _navigator(context)?.pushAndRemoveUntil<T>(
          slideFadeRoute(page),
          predicate ?? (route) => false,
        ) ??
        Future<T?>.value(null);
  }
}

/// Extension pour simplifier l'utilisation
extension NavigationExtension on BuildContext {
  /// Navigation simple avec animation par défaut
  Future<T?> navigateTo<T>(Widget page) {
    return NavigationHelper.navigateTo<T>(this, page);
  }

  /// Navigation avec slide
  Future<T?> navigateToSlide<T>(Widget page) {
    return NavigationHelper.navigateToSlide<T>(this, page);
  }

  /// Navigation avec fade
  Future<T?> navigateToFade<T>(Widget page) {
    return NavigationHelper.navigateToFade<T>(this, page);
  }

  /// Navigation avec scale
  Future<T?> navigateToScale<T>(Widget page) {
    return NavigationHelper.navigateToScale<T>(this, page);
  }
}
