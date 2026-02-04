import 'package:flutter/material.dart';
import '../config/theme.dart';

class ServiceCard extends StatelessWidget {
  final String title;
  final IconData icon;
  final VoidCallback onTap;

  const ServiceCard({
    super.key,
    required this.title,
    required this.icon,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Semantics(
      button: true,
      label: title,
      child: GestureDetector(
        onTap: onTap,
        child: Transform(
        transform: Matrix4.identity()
          ..setEntry(3, 2, 0.001) // Perspective 3D
          ..rotateX(-0.05) // Légère rotation X
          ..rotateY(0.02), // Légère rotation Y
        alignment: Alignment.center,
        child: Container(
          decoration: BoxDecoration(
            gradient: LinearGradient(
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
              colors: [
                AppTheme.primaryBlue,
                AppTheme.primaryDark,
              ],
            ),
            borderRadius: BorderRadius.circular(16),
            border: Border.all(
              color: AppTheme.accentGold,
              width: 2,
            ),
            boxShadow: [
              // Ombre principale (effet de profondeur)
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.4),
                blurRadius: 20,
                spreadRadius: 2,
                offset: const Offset(0, 10),
              ),
              // Ombre secondaire (lueur dorée)
              BoxShadow(
                color: AppTheme.accentGold.withValues(alpha: 0.1),
                blurRadius: 15,
                spreadRadius: -2,
                offset: const Offset(0, -4),
              ),
            ],
          ),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              // Icon
              Icon(icon, size: 70, color: AppTheme.accentGold),
              const SizedBox(height: 10),
              // Title
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 8.0),
                child: Text(
                  title,
                  textAlign: TextAlign.center,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.w900,
                    color: AppTheme.accentGold,
                    height: 1.1,
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
      ),
    );
  }
}
