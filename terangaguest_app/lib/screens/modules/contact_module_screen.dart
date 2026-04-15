import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../config/theme.dart';
import '../../models/user.dart';
import '../../utils/haptic_helper.dart';

class ContactModuleScreen extends StatelessWidget {
  final Enterprise? enterprise;

  const ContactModuleScreen({super.key, this.enterprise});

  @override
  Widget build(BuildContext context) {
    final e = enterprise;
    final phone = e?.phone?.trim() ?? '';
    final email = e?.email?.trim() ?? '';
    final address = e?.address?.trim() ?? '';

    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(gradient: AppTheme.backgroundGradient),
        child: SafeArea(
          child: Column(
            children: [
              Padding(
                padding: const EdgeInsets.all(20.0),
                child: Row(
                  children: [
                    IconButton(
                      icon: const Icon(
                        Icons.arrow_back,
                        color: AppTheme.accentGold,
                      ),
                      onPressed: () {
                        HapticHelper.lightImpact();
                        Navigator.of(context).pop();
                      },
                    ),
                    const SizedBox(width: 12),
                    const Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            'Contact',
                            style: TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                              color: AppTheme.accentGold,
                            ),
                          ),
                          SizedBox(height: 4),
                          Text(
                            'Coordonnées utiles',
                            style: TextStyle(
                              fontSize: 14,
                              color: AppTheme.textGray,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              Expanded(
                child: ListView(
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  children: [
                    if (phone.isNotEmpty)
                      _ContactTile(
                        icon: Icons.call_outlined,
                        title: 'Téléphone',
                        value: phone,
                        onTap: () async {
                          HapticHelper.lightImpact();
                          await launchUrl(
                            Uri.parse('tel:$phone'),
                            mode: LaunchMode.externalApplication,
                          );
                        },
                      ),
                    if (email.isNotEmpty)
                      _ContactTile(
                        icon: Icons.email_outlined,
                        title: 'Email',
                        value: email,
                        onTap: () async {
                          HapticHelper.lightImpact();
                          await launchUrl(
                            Uri.parse('mailto:$email'),
                            mode: LaunchMode.externalApplication,
                          );
                        },
                      ),
                    if (address.isNotEmpty)
                      _ContactTile(
                        icon: Icons.location_on_outlined,
                        title: 'Adresse',
                        value: address,
                        onTap: null,
                      ),
                    if (phone.isEmpty && email.isEmpty && address.isEmpty)
                      const Padding(
                        padding: EdgeInsets.all(24.0),
                        child: Text(
                          'Aucune information de contact disponible.',
                          textAlign: TextAlign.center,
                          style: TextStyle(
                            color: AppTheme.textGray,
                            height: 1.4,
                          ),
                        ),
                      ),
                    const SizedBox(height: 12),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _ContactTile extends StatelessWidget {
  final IconData icon;
  final String title;
  final String value;
  final VoidCallback? onTap;

  const _ContactTile({
    required this.icon,
    required this.title,
    required this.value,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Material(
        color: AppTheme.primaryDark.withValues(alpha: 0.35),
        borderRadius: BorderRadius.circular(14),
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(14),
          child: Padding(
            padding: const EdgeInsets.all(14),
            child: Row(
              children: [
                Container(
                  width: 44,
                  height: 44,
                  decoration: BoxDecoration(
                    color: AppTheme.primaryBlue.withValues(alpha: 0.35),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(
                      color: AppTheme.accentGold.withValues(alpha: 0.25),
                    ),
                  ),
                  child: Icon(icon, color: AppTheme.accentGold),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        title,
                        style: const TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                      const SizedBox(height: 6),
                      Text(
                        value,
                        style: const TextStyle(color: AppTheme.textGray),
                      ),
                    ],
                  ),
                ),
                if (onTap != null)
                  const Icon(Icons.chevron_right, color: AppTheme.textGray),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

