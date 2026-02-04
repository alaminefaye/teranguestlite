import 'package:flutter/services.dart';

/// Helper pour le feedback haptique (vibrations subtiles)
class HapticHelper {
  /// Feedback léger (tap sur un bouton)
  static Future<void> lightImpact() async {
    await HapticFeedback.lightImpact();
  }

  /// Feedback moyen (sélection)
  static Future<void> mediumImpact() async {
    await HapticFeedback.mediumImpact();
  }

  /// Feedback fort (action importante)
  static Future<void> heavyImpact() async {
    await HapticFeedback.heavyImpact();
  }

  /// Feedback de sélection (scroll, picker)
  static Future<void> selectionClick() async {
    await HapticFeedback.selectionClick();
  }

  /// Feedback de vibration (notification)
  static Future<void> vibrate() async {
    await HapticFeedback.vibrate();
  }

  /// Feedback pour succès (commande validée, etc.)
  static Future<void> success() async {
    await HapticFeedback.mediumImpact();
    await Future.delayed(const Duration(milliseconds: 50));
    await HapticFeedback.lightImpact();
  }

  /// Feedback pour erreur
  static Future<void> error() async {
    await HapticFeedback.heavyImpact();
    await Future.delayed(const Duration(milliseconds: 100));
    await HapticFeedback.heavyImpact();
  }

  /// Feedback pour action importante (réservation, checkout)
  static Future<void> confirm() async {
    await HapticFeedback.mediumImpact();
  }

  /// Feedback pour ajout au panier
  static Future<void> addToCart() async {
    await HapticFeedback.lightImpact();
  }
}
