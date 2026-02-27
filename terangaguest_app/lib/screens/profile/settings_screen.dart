import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../providers/locale_provider.dart';
import '../../utils/haptic_helper.dart';

const String _keyNotificationsEnabled = 'settings_notifications_enabled';

class SettingsScreen extends StatefulWidget {
  const SettingsScreen({super.key});

  @override
  State<SettingsScreen> createState() => _SettingsScreenState();
}

class _SettingsScreenState extends State<SettingsScreen> {
  bool _notificationsEnabled = true;
  bool _loading = true;

  @override
  void initState() {
    super.initState();
    _loadSettings();
  }

  Future<void> _loadSettings() async {
    final prefs = await SharedPreferences.getInstance();
    setState(() {
      _notificationsEnabled = prefs.getBool(_keyNotificationsEnabled) ?? true;
      _loading = false;
    });
  }

  Future<void> _setNotificationsEnabled(bool value) async {
    HapticHelper.lightImpact();
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool(_keyNotificationsEnabled, value);
    if (mounted) {
      setState(() => _notificationsEnabled = value);
      final l10n = AppLocalizations.of(context);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(value ? l10n.notificationsOn : l10n.notificationsOff),
          backgroundColor: AppTheme.primaryBlue,
          behavior: SnackBarBehavior.floating,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(gradient: AppTheme.backgroundGradient),
        child: SafeArea(
          child: Column(
            children: [
              _buildHeader(),
              Expanded(
                child: _loading
                    ? const Center(
                        child: CircularProgressIndicator(
                          valueColor: AlwaysStoppedAnimation<Color>(
                            AppTheme.accentGold,
                          ),
                        ),
                      )
                    : ListView(
                        padding: const EdgeInsets.all(20),
                        children: [
                          _buildSectionTitle(
                            AppLocalizations.of(context).preferences,
                          ),
                          const SizedBox(height: 12),
                          _buildNotificationTile(context),
                          const SizedBox(height: 24),
                          _buildSectionTitle(
                            AppLocalizations.of(context).application,
                          ),
                          const SizedBox(height: 12),
                          _buildLanguageTile(context),
                        ],
                      ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildHeader() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 12),
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
          Text(
            AppLocalizations.of(context).settings,
            style: const TextStyle(
              fontSize: 28,
              fontWeight: FontWeight.bold,
              color: Colors.white,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSectionTitle(String title) {
    return Text(
      title,
      style: const TextStyle(
        fontSize: 18,
        fontWeight: FontWeight.bold,
        color: AppTheme.accentGold,
      ),
    );
  }

  Widget _buildNotificationTile(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      decoration: BoxDecoration(
        color: AppTheme.primaryBlue.withValues(alpha: 0.5),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppTheme.accentGold.withValues(alpha: 0.3)),
      ),
      child: Row(
        children: [
          Icon(Icons.notifications_outlined, color: AppTheme.accentGold),
          const SizedBox(width: 16),
          Expanded(
            child: Text(
              l10n.notifications,
              style: const TextStyle(fontSize: 16, color: Colors.white),
            ),
          ),
          Switch(
            value: _notificationsEnabled,
            onChanged: _setNotificationsEnabled,
            activeTrackColor: AppTheme.accentGold.withValues(alpha: 0.3),
            activeThumbColor: AppTheme.accentGold,
          ),
        ],
      ),
    );
  }

  Widget _buildLanguageTile(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final localeProvider = context.watch<LocaleProvider>();
    final code = localeProvider.languageCode;
    final currentLabel = code == 'en'
        ? l10n.english
        : code == 'ar'
        ? l10n.arabic
        : code == 'es'
        ? l10n.spanish
        : l10n.french;
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: () => _showLanguageDialog(context),
        borderRadius: BorderRadius.circular(12),
        child: Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: AppTheme.primaryBlue.withValues(alpha: 0.5),
            borderRadius: BorderRadius.circular(12),
            border: Border.all(
              color: AppTheme.accentGold.withValues(alpha: 0.3),
            ),
          ),
          child: Row(
            children: [
              Icon(Icons.language, color: AppTheme.accentGold),
              const SizedBox(width: 16),
              Expanded(
                child: Text(
                  l10n.language,
                  style: const TextStyle(fontSize: 16, color: Colors.white),
                ),
              ),
              Text(
                currentLabel,
                style: TextStyle(fontSize: 14, color: AppTheme.textGray),
              ),
              const SizedBox(width: 8),
              const Icon(
                Icons.arrow_forward_ios,
                size: 14,
                color: AppTheme.textGray,
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _showLanguageDialog(BuildContext context) {
    HapticHelper.lightImpact();
    final l10n = AppLocalizations.of(context);
    final localeProvider = context.read<LocaleProvider>();
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        backgroundColor: AppTheme.primaryBlue,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(16),
          side: const BorderSide(color: AppTheme.accentGold, width: 1),
        ),
        title: Text(
          l10n.language,
          style: const TextStyle(color: AppTheme.accentGold),
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            ListTile(
              title: Text(
                l10n.french,
                style: const TextStyle(color: Colors.white),
              ),
              onTap: () {
                localeProvider.setLocale(const Locale('fr'));
                Navigator.pop(context);
              },
            ),
            ListTile(
              title: Text(
                l10n.english,
                style: const TextStyle(color: Colors.white),
              ),
              onTap: () {
                localeProvider.setLocale(const Locale('en'));
                Navigator.pop(context);
              },
            ),
            ListTile(
              title: Text(
                l10n.arabic,
                style: const TextStyle(color: Colors.white),
              ),
              onTap: () {
                localeProvider.setLocale(const Locale('ar'));
                Navigator.pop(context);
              },
            ),
            ListTile(
              title: Text(
                l10n.spanish,
                style: const TextStyle(color: Colors.white),
              ),
              onTap: () {
                localeProvider.setLocale(const Locale('es'));
                Navigator.pop(context);
              },
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text(
              l10n.close,
              style: const TextStyle(color: AppTheme.accentGold),
            ),
          ),
        ],
      ),
    );
  }
}
