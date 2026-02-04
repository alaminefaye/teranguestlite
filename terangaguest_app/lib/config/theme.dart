import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

class AppTheme {
  // Couleurs principales
  static const Color primaryDark = Color(0xFF0A1929);
  static const Color primaryBlue = Color(0xFF1A2F44);
  static const Color accentGold = Color(0xFFD4AF37);
  static const Color accentGoldLight = Color(0xFFE5C158);
  static const Color textWhite = Color(0xFFFFFFFF);
  static const Color textGray = Color(0xFFB0B8C1);
  static const Color cardBorder = Color(0xFF2A3F54);

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
      
      // Typographie
      textTheme: TextTheme(
        displayLarge: GoogleFonts.playfairDisplay(
          fontSize: 32,
          fontWeight: FontWeight.bold,
          color: textWhite,
        ),
        displayMedium: GoogleFonts.playfairDisplay(
          fontSize: 28,
          fontWeight: FontWeight.bold,
          color: textWhite,
        ),
        headlineMedium: GoogleFonts.montserrat(
          fontSize: 20,
          fontWeight: FontWeight.w600,
          color: accentGold,
        ),
        titleLarge: GoogleFonts.montserrat(
          fontSize: 18,
          fontWeight: FontWeight.w600,
          color: textWhite,
        ),
        bodyLarge: GoogleFonts.montserrat(
          fontSize: 16,
          color: textGray,
        ),
        bodyMedium: GoogleFonts.montserrat(
          fontSize: 14,
          color: textGray,
        ),
      ),
      
      // App Bar
      appBarTheme: AppBarTheme(
        backgroundColor: Colors.transparent,
        elevation: 0,
        centerTitle: true,
        titleTextStyle: GoogleFonts.playfairDisplay(
          fontSize: 20,
          fontWeight: FontWeight.w600,
          color: textWhite,
        ),
        iconTheme: const IconThemeData(color: accentGold),
      ),
    );
  }
}
