import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../providers/auth_provider.dart';
import '../../utils/haptic_helper.dart';

/// Chatbot IA multilingue : ouvre l'URL du chatbot si configurée, sinon « Bientôt disponible ».
class ChatbotScreen extends StatelessWidget {
  const ChatbotScreen({super.key});

  Future<void> _openChatbot(BuildContext context, String url) async {
    HapticHelper.lightImpact();
    final uri = Uri.parse(url);
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    } else {
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Impossible d\'ouvrir le lien'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final chatbotUrl = context.watch<AuthProvider>().user?.enterprise?.chatbotUrl;

    return Scaffold(
      body: Container(
        decoration: BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [AppTheme.primaryDark, AppTheme.primaryBlue],
          ),
        ),
        child: SafeArea(
          child: Column(
            children: [
              Padding(
                padding: const EdgeInsets.all(20.0),
                child: Row(
                  children: [
                    IconButton(
                      icon: const Icon(Icons.arrow_back, color: AppTheme.accentGold),
                      onPressed: () {
                        HapticHelper.lightImpact();
                        Navigator.pop(context);
                      },
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            l10n.chatbotMultilingual,
                            style: const TextStyle(
                              fontSize: 24,
                              fontWeight: FontWeight.bold,
                              color: Colors.white,
                            ),
                          ),
                          Text(
                            l10n.chatbotDesc,
                            style: const TextStyle(fontSize: 13, color: AppTheme.textGray),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              Expanded(
                child: chatbotUrl != null && chatbotUrl.isNotEmpty
                    ? Center(
                        child: Padding(
                          padding: const EdgeInsets.all(32),
                          child: Column(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(
                                Icons.smart_toy_outlined,
                                size: 64,
                                color: AppTheme.accentGold.withValues(alpha: 0.7),
                              ),
                              const SizedBox(height: 24),
                              Text(
                                l10n.chatbotDesc,
                                style: const TextStyle(
                                  fontSize: 16,
                                  color: Colors.white70,
                                ),
                                textAlign: TextAlign.center,
                              ),
                              const SizedBox(height: 24),
                              FilledButton.icon(
                                onPressed: () => _openChatbot(context, chatbotUrl),
                                icon: const Icon(Icons.open_in_new, size: 20),
                                label: const Text('Ouvrir l\'assistant'),
                                style: FilledButton.styleFrom(
                                  backgroundColor: AppTheme.accentGold,
                                  foregroundColor: Colors.black87,
                                  padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
                                ),
                              ),
                            ],
                          ),
                        ),
                      )
                    : Center(
                        child: Padding(
                          padding: const EdgeInsets.all(32),
                          child: Column(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(
                                Icons.smart_toy_outlined,
                                size: 64,
                                color: AppTheme.accentGold.withValues(alpha: 0.7),
                              ),
                              const SizedBox(height: 16),
                              Text(
                                l10n.chatbotComingSoon,
                                style: const TextStyle(
                                  fontSize: 20,
                                  fontWeight: FontWeight.w600,
                                  color: Colors.white,
                                ),
                                textAlign: TextAlign.center,
                              ),
                              const SizedBox(height: 8),
                              Text(
                                l10n.chatbotComingSoonHint,
                                style: const TextStyle(
                                  fontSize: 14,
                                  color: AppTheme.textGray,
                                ),
                                textAlign: TextAlign.center,
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
    );
  }
}
