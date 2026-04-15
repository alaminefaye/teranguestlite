import 'package:flutter/material.dart';
import '../config/theme.dart';
import '../utils/haptic_helper.dart';

class VitrineDisabledScreen extends StatelessWidget {
  final String title;
  final String? subtitle;
  final IconData icon;

  const VitrineDisabledScreen({
    super.key,
    required this.title,
    this.subtitle,
    this.icon = Icons.visibility_outlined,
  });

  @override
  Widget build(BuildContext context) {
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
                    Expanded(
                      child: Text(
                        title,
                        style: TextStyle(
                          fontSize: MediaQuery.of(context).size.width < 600
                              ? 18
                              : 28,
                          fontWeight: FontWeight.bold,
                          color: AppTheme.accentGold,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ),
              ),
              Expanded(
                child: Center(
                  child: Padding(
                    padding: const EdgeInsets.all(24.0),
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(icon, size: 54, color: AppTheme.accentGold),
                        const SizedBox(height: 14),
                        const Text(
                          'Mode vitrine',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 18,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                        const SizedBox(height: 10),
                        Text(
                          subtitle ?? 'Cette fonctionnalité est désactivée.',
                          textAlign: TextAlign.center,
                          style: const TextStyle(
                            color: AppTheme.textGray,
                            height: 1.4,
                          ),
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

