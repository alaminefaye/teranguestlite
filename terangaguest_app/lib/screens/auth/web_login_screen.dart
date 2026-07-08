import 'package:flutter/material.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../widgets/animated_button.dart';
import '../dashboard/dashboard_screen.dart';

class WebLoginScreen extends StatefulWidget {
  const WebLoginScreen({super.key});

  @override
  State<WebLoginScreen> createState() => _WebLoginScreenState();
}

class _WebLoginScreenState extends State<WebLoginScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!mounted) return;
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(builder: (_) => const DashboardScreen()),
      );
    });
  }

  @override
  Widget build(BuildContext context) {
    final screenWidth = MediaQuery.of(context).size.width;
    final isMobile = screenWidth < 600;

    final double logoWidth = isMobile ? 160.0 : 220.0;
    final double titleFontSize = isMobile ? 14.0 : 18.0;
    final double buttonHeight = isMobile ? 44.0 : 56.0;
    final double buttonFontSize = isMobile ? 14.0 : 16.0;
    final double padding = isMobile ? 24.0 : 32.0;
    final double spacingLogo = isMobile ? 24.0 : 40.0;
    final double messageFontSize = isMobile ? 13.0 : 15.0;

    final double maxWidth = isMobile ? double.infinity : 480.0;

    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(gradient: AppTheme.backgroundGradient),
        child: SafeArea(
          child: Center(
            child: SingleChildScrollView(
              padding: EdgeInsets.all(padding),
              child: Center(
                child: ConstrainedBox(
                  constraints: BoxConstraints(maxWidth: maxWidth),
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      _buildLogo(context, logoWidth, titleFontSize),
                      SizedBox(height: spacingLogo),
                      Text(
                        'Acces direct a l application',
                        textAlign: TextAlign.center,
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: messageFontSize,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      SizedBox(height: isMobile ? 16.0 : 20.0),
                      SizedBox(
                        width: 28,
                        height: 28,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          valueColor: AlwaysStoppedAnimation<Color>(
                            AppTheme.accentGold.withValues(alpha: 0.9),
                          ),
                        ),
                      ),
                      SizedBox(height: isMobile ? 20.0 : 28.0),
                      _buildLoginButton(context, buttonHeight, buttonFontSize),
                      SizedBox(height: isMobile ? 24.0 : 36.0),
                      Text(
                        AppLocalizations.of(context).developedByUTA,
                        textAlign: TextAlign.center,
                        style: TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.bold,
                          fontSize: isMobile ? 11.0 : 13.0,
                          letterSpacing: 0.3,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildLogo(
    BuildContext context,
    double logoWidth,
    double titleFontSize,
  ) {
    return Column(
      children: [
        Image.asset('assets/logo.png', width: logoWidth),
        const SizedBox(height: 12),
        Text(
          AppLocalizations.of(context).appTitle,
          style: TextStyle(fontSize: titleFontSize, color: AppTheme.textGray),
        ),
      ],
    );
  }

  Widget _buildLoginButton(
    BuildContext context,
    double height,
    double fontSize,
  ) {
    return AnimatedButton(
      text: 'Acceder a l application',
      onPressed: () {
        Navigator.of(context).pushReplacement(
          MaterialPageRoute(builder: (_) => const DashboardScreen()),
        );
      },
      width: double.infinity,
      height: height,
      backgroundColor: AppTheme.accentGold,
      textColor: AppTheme.primaryDark,
    );
  }
}
