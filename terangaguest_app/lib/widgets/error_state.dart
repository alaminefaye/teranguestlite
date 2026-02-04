import 'package:flutter/material.dart';
import '../config/theme.dart';
import '../generated/l10n/app_localizations.dart';
import 'animated_button.dart';

/// Widget réutilisable pour afficher un état d'erreur (échec chargement, réseau, etc.).
/// Affiche une icône, un titre, le message d'erreur, un conseil optionnel et un bouton Réessayer.
class ErrorStateWidget extends StatelessWidget {
  const ErrorStateWidget({
    super.key,
    required this.message,
    this.title,
    this.hint,
    this.onRetry,
    this.icon = Icons.error_outline,
    this.iconSize = 64,
  });

  final String message;
  final String? title;
  final String? hint;
  final VoidCallback? onRetry;
  final IconData icon;
  final double iconSize;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final effectiveTitle = title ?? l10n.error;
    return Center(
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon, size: iconSize, color: AppTheme.accentGold),
            const SizedBox(height: 20),
            Text(
              effectiveTitle,
              style: const TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.bold,
                color: Colors.white,
              ),
            ),
            const SizedBox(height: 12),
            Text(
              message,
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontSize: 14,
                color: AppTheme.textGray,
              ),
            ),
            if (hint != null && hint!.isNotEmpty) ...[
              const SizedBox(height: 12),
              Text(
                hint!,
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 13,
                  color: AppTheme.textGray.withValues(alpha: 0.9),
                  fontStyle: FontStyle.italic,
                ),
              ),
            ],
            if (onRetry != null) ...[
              const SizedBox(height: 24),
              AnimatedButton(
                text: l10n.retry,
                onPressed: onRetry!,
                backgroundColor: AppTheme.accentGold,
                textColor: AppTheme.primaryDark,
              ),
            ],
          ],
        ),
      ),
    );
  }
}
