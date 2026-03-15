import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../config/theme.dart';
import '../models/menu_category.dart';
import '../generated/l10n/app_localizations.dart';
import '../providers/locale_provider.dart';
import '../utils/translatable_text_helper.dart';
import 'translatable_text.dart';

class CategoryCard extends StatelessWidget {
  final MenuCategory category;
  final VoidCallback onTap;

  const CategoryCard({super.key, required this.category, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final locale = context.read<LocaleProvider>().languageCode;
    final screenWidth = MediaQuery.of(context).size.width;
    final isMobile = screenWidth < 600;
    final double iconDisplaySize = isMobile ? 46.0 : 70.0;
    final double fontSize = isMobile ? 13.0 : 21.0;
    final double subFontSize = isMobile ? 11.0 : 14.0;
    final nameStr = TranslatableTextHelper.resolveDisplayTextSync(category.name, locale);

    return Semantics(
      button: true,
      label: nameStr,
      child: GestureDetector(
        onTap: onTap,
        child: Transform(
          transform: Matrix4.identity()
            ..setEntry(3, 2, 0.001)
            ..rotateX(-0.05)
            ..rotateY(0.02),
          alignment: Alignment.center,
          child: Container(
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
                colors: [AppTheme.primaryBlue, AppTheme.primaryDark],
              ),
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: AppTheme.accentGold, width: 2),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.4),
                  blurRadius: 20,
                  spreadRadius: 2,
                  offset: const Offset(0, 10),
                ),
                BoxShadow(
                  color: AppTheme.accentGold.withValues(alpha: 0.1),
                  blurRadius: 15,
                  spreadRadius: -2,
                  offset: const Offset(0, -4),
                ),
              ],
            ),
            child: Column(
              mainAxisSize: MainAxisSize.max,
              crossAxisAlignment: CrossAxisAlignment.center,
              children: [
                // Icône — alignée en bas de sa zone
                Expanded(
                  flex: 3,
                  child: Align(
                    alignment: Alignment.bottomCenter,
                    child: Padding(
                      padding: const EdgeInsets.only(bottom: 6),
                      child: Icon(
                        _getIconForCategory(nameStr),
                        size: iconDisplaySize,
                        color: AppTheme.accentGold,
                      ),
                    ),
                  ),
                ),
                // Texte — aligné en haut de sa zone
                Expanded(
                  flex: 2,
                  child: Align(
                    alignment: Alignment.topCenter,
                    child: Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 6.0),
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          TranslatableText(
                            category.name,
                            locale: locale,
                            textAlign: TextAlign.center,
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                            style: TextStyle(
                              fontSize: fontSize,
                              fontWeight: FontWeight.w900,
                              color: AppTheme.accentGold,
                              height: 1.1,
                            ),
                          ),
                          const SizedBox(height: 3),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(
                                Icons.restaurant_menu,
                                size: subFontSize,
                                color: AppTheme.textGray,
                              ),
                              const SizedBox(width: 4),
                              Text(
                                AppLocalizations.of(context).articleCount(category.itemsCount),
                                style: TextStyle(
                                  fontSize: subFontSize,
                                  fontWeight: FontWeight.w600,
                                  color: AppTheme.textGray,
                                ),
                              ),
                            ],
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
    );
  }

  static IconData _getIconForCategory(String name) {
    final n = name.toLowerCase().trim();
    if (n.contains('petit') && n.contains('déjeuner')) {
      return Icons.free_breakfast;
    }
    if (n.contains('breakfast')) {
      return Icons.free_breakfast;
    }
    if (n.contains('plats') && n.contains('principal')) {
      return Icons.dinner_dining;
    }
    if (n.contains('main') && (n.contains('course') || n.contains('dish'))) {
      return Icons.dinner_dining;
    }
    if (n.contains('boisson')) {
      return Icons.local_bar;
    }
    if (n.contains('drink') || n.contains('beverage')) {
      return Icons.local_bar;
    }
    if (n.contains('dessert')) {
      return Icons.cake;
    }
    if (n.contains('snack') || n.contains('collation')) {
      return Icons.cookie;
    }
    return Icons.restaurant;
  }
}
