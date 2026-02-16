import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../providers/auth_provider.dart';
import '../../utils/haptic_helper.dart';

/// Livret d'accueil : Wi-Fi, plans, règlement, infos pratiques.
class HotelInfosScreen extends StatelessWidget {
  const HotelInfosScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final enterprise = context.watch<AuthProvider>().user?.enterprise;
    final infos = enterprise?.hotelInfos;

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
                            l10n.hotelInfos,
                            style: const TextStyle(
                              fontSize: 24,
                              fontWeight: FontWeight.bold,
                              color: Colors.white,
                            ),
                          ),
                          Text(
                            l10n.hotelInfosDesc,
                            style: const TextStyle(fontSize: 13, color: AppTheme.textGray),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              Expanded(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      if (infos != null) ...[
                        if (infos.wifiNetwork.isNotEmpty || infos.wifiPassword.isNotEmpty)
                          _section(
                            context,
                            l10n.wifiCode,
                            [
                              if (infos.wifiNetwork.isNotEmpty)
                                _row(l10n.wifiCode, infos.wifiNetwork),
                              if (infos.wifiPassword.isNotEmpty)
                                _row(l10n.wifiPassword, infos.wifiPassword),
                            ],
                          ),
                        if (infos.houseRules.trim().isNotEmpty)
                          _section(context, l10n.houseRules, [
                            Padding(
                              padding: const EdgeInsets.only(top: 8),
                              child: Text(
                                infos.houseRules,
                                style: const TextStyle(
                                  color: Colors.white70,
                                  fontSize: 14,
                                  height: 1.4,
                                ),
                              ),
                            ),
                          ]),
                        if (infos.mapUrl != null && infos.mapUrl!.isNotEmpty)
                          _section(
                            context,
                            'Plan',
                            [
                              const SizedBox(height: 8),
                              ClipRRect(
                                borderRadius: BorderRadius.circular(12),
                                child: Image.network(
                                  infos.mapUrl!,
                                  fit: BoxFit.contain,
                                  width: double.infinity,
                                  loadingBuilder: (context, child, progress) =>
                                      progress == null
                                          ? child
                                          : const Center(
                                              child: CircularProgressIndicator(
                                                color: AppTheme.accentGold,
                                              ),
                                            ),
                                  errorBuilder: (context, error, stackTrace) =>
                                      const Icon(
                                        Icons.map_outlined,
                                        color: AppTheme.textGray,
                                        size: 48,
                                      ),
                                ),
                              ),
                            ],
                          ),
                        if (infos.practicalInfo.trim().isNotEmpty)
                          _section(context, l10n.practicalInfo, [
                            Padding(
                              padding: const EdgeInsets.only(top: 8),
                              child: Text(
                                infos.practicalInfo,
                                style: const TextStyle(
                                  color: Colors.white70,
                                  fontSize: 14,
                                  height: 1.4,
                                ),
                              ),
                            ),
                          ]),
                        if (infos.wifiNetwork.isEmpty &&
                            infos.wifiPassword.isEmpty &&
                            infos.houseRules.trim().isEmpty &&
                            (infos.mapUrl == null || infos.mapUrl!.isEmpty) &&
                            infos.practicalInfo.trim().isEmpty)
                          Center(
                            child: Padding(
                              padding: const EdgeInsets.all(24),
                              child: Text(
                                l10n.comingSoon,
                                style: const TextStyle(color: AppTheme.textGray, fontSize: 16),
                              ),
                            ),
                          ),
                      ] else
                        Center(
                          child: Padding(
                            padding: const EdgeInsets.all(24),
                            child: Text(
                              l10n.comingSoon,
                              style: const TextStyle(color: AppTheme.textGray, fontSize: 16),
                            ),
                          ),
                        ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _section(BuildContext context, String title, List<Widget> children) {
    return Container(
      margin: const EdgeInsets.only(bottom: 20),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppTheme.primaryBlue.withValues(alpha: 0.5),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppTheme.accentGold.withValues(alpha: 0.4)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            title,
            style: const TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.w600,
              color: AppTheme.accentGold,
            ),
          ),
          ...children,
        ],
      ),
    );
  }

  Widget _row(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(top: 6),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 120,
            child: Text(
              '$label :',
              style: const TextStyle(color: AppTheme.textGray, fontSize: 14),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: const TextStyle(color: Colors.white, fontSize: 14, fontWeight: FontWeight.w500),
            ),
          ),
        ],
      ),
    );
  }
}
