import 'package:flutter/material.dart';
import '../config/theme.dart';
import '../models/menu_category.dart';

class CategoryCard extends StatelessWidget {
  final MenuCategory category;
  final VoidCallback onTap;

  const CategoryCard({
    super.key,
    required this.category,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Semantics(
      button: true,
      label: category.name,
      child: GestureDetector(
        onTap: onTap,
        child: Transform(
        transform: Matrix4.identity()
          ..setEntry(3, 2, 0.001) // Perspective
          ..rotateX(-0.05) // Légère rotation pour effet 3D
          ..rotateY(0.02),
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
              width: 1.5,
            ),
            boxShadow: [
              // Ombre principale (plus prononcée)
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.4),
                blurRadius: 20,
                spreadRadius: 2,
                offset: const Offset(0, 10),
              ),
              // Ombre secondaire (effet de profondeur)
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
              // Icône de la catégorie (plus grand et centré)
              _buildPlaceholder(),
              
              const SizedBox(height: 12),

            // Nom de la catégorie
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 8.0),
              child: Text(
                category.name,
                textAlign: TextAlign.center,
                style: const TextStyle(
                  fontSize: 24,
                  fontWeight: FontWeight.w900,
                  color: AppTheme.accentGold,
                  height: 1.1,
                  letterSpacing: 0.3,
                ),
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
              ),
            ),
              
            const SizedBox(height: 8),

            // Nombre d'articles
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Icon(
                  Icons.restaurant_menu,
                  size: 16,
                  color: AppTheme.textGray,
                ),
                const SizedBox(width: 6),
                Text(
                  '${category.itemsCount} article${category.itemsCount > 1 ? 's' : ''}',
                  style: const TextStyle(
                    fontSize: 15,
                    fontWeight: FontWeight.w600,
                    color: AppTheme.textGray,
                    letterSpacing: 0.3,
                  ),
                ),
              ],
            ),
            ],
          ),
        ),
      ),
      ),
    );
  }

  Widget _buildPlaceholder() {
    return const Icon(
      Icons.restaurant,
      size: 70,
      color: AppTheme.accentGold,
    );
  }
}
