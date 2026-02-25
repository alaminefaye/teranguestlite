import 'package:flutter/material.dart';

class AppTheme {
  // Couleurs principales
  static const Color primaryDark = Color(0xFF0A1929);
  static const Color primaryBlue = Color(0xFF1A2F44);
  static const Color accentGold = Color(0xFFD4AF37);
  static const Color accentGoldLight = Color(0xFFE5C158);
  static const Color textWhite = Color(0xFFFFFFFF);
  static const Color textGray = Color(0xFFB0B8C1);
  static const Color cardBorder = Color(0xFF2A3F54);
  static const Color errorRed = Color(0xFFE53935);

  // Gradient background
  static const LinearGradient backgroundGradient = LinearGradient(
    begin: Alignment.topCenter,
    end: Alignment.bottomCenter,
    colors: [primaryDark, primaryBlue],
  );

  // Gradient gold pour boutons
  static const LinearGradient goldGradient = LinearGradient(
    colors: [accentGold, accentGoldLight],
  );

  // Helper method to create safe text styles
  static TextStyle _createTextStyle({
    required double fontSize,
    required FontWeight fontWeight,
    required Color color,
    String fontFamily = 'Georgia',
  }) {
    return TextStyle(
      fontSize: fontSize,
      fontWeight: fontWeight,
      color: color,
      fontFamily: fontFamily,
      letterSpacing: 0.5,
    );
  }

  // Theme principal
  static ThemeData get theme {
    return ThemeData(
      useMaterial3: true,
      brightness: Brightness.dark,
      scaffoldBackgroundColor: primaryDark,
      primaryColor: accentGold,
      colorScheme: const ColorScheme.dark(
        primary: accentGold,
        secondary: accentGoldLight,
        surface: primaryBlue,
      ),

      // Typographie - Using Georgia (guaranteed iOS font)
      textTheme: TextTheme(
        displayLarge: _createTextStyle(
          fontSize: 32,
          fontWeight: FontWeight.bold,
          color: textWhite,
        ),
        displayMedium: _createTextStyle(
          fontSize: 28,
          fontWeight: FontWeight.bold,
          color: textWhite,
        ),
        headlineMedium: _createTextStyle(
          fontSize: 20,
          fontWeight: FontWeight.w600,
          color: accentGold,
        ),
        titleLarge: _createTextStyle(
          fontSize: 18,
          fontWeight: FontWeight.w600,
          color: textWhite,
        ),
        bodyLarge: _createTextStyle(
          fontSize: 16,
          fontWeight: FontWeight.normal,
          color: textGray,
        ),
        bodyMedium: _createTextStyle(
          fontSize: 14,
          fontWeight: FontWeight.normal,
          color: textGray,
        ),
      ),

      // App Bar
      appBarTheme: AppBarTheme(
        backgroundColor: Colors.transparent,
        elevation: 0,
        centerTitle: true,
        titleTextStyle: _createTextStyle(
          fontSize: 20,
          fontWeight: FontWeight.w600,
          color: textWhite,
        ),
        iconTheme: const IconThemeData(color: accentGold),
      ),
    );
  }
}
