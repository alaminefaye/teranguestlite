import 'package:flutter/material.dart';

/// Utilitaires pour un layout responsive adapté aux tablettes.
/// Évite que les box soient cachées ou débordent selon la taille d'écran.
class LayoutHelper {
  LayoutHelper._();

  /// Largeur courte de l'écran (pour détecter tablette vs téléphone)
  static double shortestSide(BuildContext context) {
    return MediaQuery.sizeOf(context).shortestSide;
  }

  /// Largeur totale (en paysage = width > height)
  static double width(BuildContext context) => MediaQuery.sizeOf(context).width;
  static double height(BuildContext context) =>
      MediaQuery.sizeOf(context).height;

  /// True si on considère l'appareil comme une tablette
  static bool isTablet(BuildContext context) {
    return shortestSide(context) >= 600;
  }

  /// Nombre de colonnes pour les grilles (dashboard, listes) selon la largeur
  /// pour que les cartes ne soient pas trop serrées ni coupées
  static int gridCrossAxisCount(BuildContext context) {
    final w = width(context);
    if (w >= 1200) return 4;
    if (w >= 900) return 4;
    if (w >= 600) return 3;
    if (w >= 400) return 2;
    return 2;
  }

  /// Ratio largeur/hauteur des cellules pour le dashboard (8 services).
  /// Légèrement plus grand = cartes un peu plus plates (moins hautes).
  static double dashboardCellAspectRatio(BuildContext context) {
    final cols = gridCrossAxisCount(context);
    switch (cols) {
      case 4:
        return 1.48;
      case 3:
        return 1.38;
      case 2:
        return 1.25;
      default:
        return 1.4;
    }
  }

  /// Ratio pour les grilles de listes (cartes articles, restaurants, etc.)
  static double listCellAspectRatio(BuildContext context) {
    final cols = gridCrossAxisCount(context);
    switch (cols) {
      case 4:
        return 0.9;
      case 3:
        return 0.85;
      case 2:
        return 0.8;
      default:
        return 0.9;
    }
  }

  /// Padding horizontal pour le contenu (grilles, listes) en pixels
  static double horizontalPaddingValue(BuildContext context) {
    final w = width(context);
    if (w >= 1200) return 60;
    if (w >= 900) return 48;
    if (w >= 600) return 40;
    return 24;
  }

  /// Padding horizontal pour le contenu principal (grilles, listes)
  static EdgeInsets horizontalPadding(BuildContext context) {
    final p = horizontalPaddingValue(context);
    return EdgeInsets.symmetric(horizontal: p);
  }

  /// Espacement entre les cellules de grille
  static double gridSpacing(BuildContext context) {
    return width(context) >= 900 ? 20 : 16;
  }
}
