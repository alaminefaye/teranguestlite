import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../../widgets/animated_button.dart';
import '../dashboard/dashboard_screen.dart';

class WebLoginScreen extends StatefulWidget {
  final String? initialCode;

  const WebLoginScreen({super.key, this.initialCode});

  @override
  State<WebLoginScreen> createState() => _WebLoginScreenState();
}

class _WebLoginScreenState extends State<WebLoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _codeController = TextEditingController();
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    if (widget.initialCode != null && widget.initialCode!.isNotEmpty) {
      _codeController.text = widget.initialCode!;
      // Auto-login si le code est passé en paramètre
      WidgetsBinding.instance.addPostFrameCallback((_) {
        _handleLogin();
      });
    }
  }

  @override
  void dispose() {
    _codeController.dispose();
    super.dispose();
  }

  Future<void> _handleLogin() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _isLoading = true;
    });

    final authProvider = Provider.of<AuthProvider>(context, listen: false);

    final success = await authProvider.webLogin(
      clientCode: _codeController.text.trim(),
    );

    if (!mounted) return;

    setState(() {
      _isLoading = false;
    });

    if (success) {
      Navigator.of(context).pushReplacement(
        MaterialPageRoute(builder: (_) => const DashboardScreen()),
      );
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(authProvider.errorMessage ?? "Code client invalide"),
          backgroundColor: Colors.red,
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(10),
          ),
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final screenWidth = MediaQuery.of(context).size.width;
    final isMobile = screenWidth < 600;

    final double logoWidth = isMobile ? 160.0 : 220.0;
    final double titleFontSize = isMobile ? 14.0 : 18.0;
    final double fieldFontSize = isMobile ? 13.0 : 16.0;
    final double iconSize = isMobile ? 18.0 : 24.0;
    final double borderRadius = isMobile ? 10.0 : 12.0;
    final double buttonHeight = isMobile ? 44.0 : 56.0;
    final double buttonFontSize = isMobile ? 14.0 : 16.0;
    final double padding = isMobile ? 24.0 : 32.0;
    final double spacingLogo = isMobile ? 24.0 : 40.0;
    final double spacingButton = isMobile ? 20.0 : 32.0;

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
                  child: Form(
                    key: _formKey,
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        _buildLogo(logoWidth, titleFontSize),
                        SizedBox(height: spacingLogo),

                        _buildCodeField(fieldFontSize, iconSize, borderRadius),
                        SizedBox(height: spacingButton),

                        _buildLoginButton(buttonHeight, buttonFontSize),

                        SizedBox(height: isMobile ? 24.0 : 36.0),

                        Text(
                          'Développé par Universal Technologies Africa',
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
      ),
    );
  }

  Widget _buildLogo(double logoWidth, double titleFontSize) {
    return Column(
      children: [
        Image.asset('assets/logo.png', width: logoWidth),
        const SizedBox(height: 12),
        Text(
          "Connexion Client",
          style: TextStyle(fontSize: titleFontSize, color: AppTheme.textGray),
        ),
      ],
    );
  }

  Widget _buildCodeField(
    double fontSize,
    double iconSize,
    double borderRadius,
  ) {
    return TextFormField(
      controller: _codeController,
      keyboardType: TextInputType.text,
      textCapitalization: TextCapitalization.characters,
      style: TextStyle(
        color: Colors.white,
        fontSize: fontSize,
        letterSpacing: 2.0,
        fontWeight: FontWeight.bold,
      ),
      textAlign: TextAlign.center,
      decoration: InputDecoration(
        labelText: "Code Client",
        labelStyle: TextStyle(
          color: AppTheme.textGray,
          fontSize: fontSize,
          letterSpacing: 0,
          fontWeight: FontWeight.normal,
        ),
        prefixIcon: Icon(
          Icons.qr_code_2,
          color: AppTheme.accentGold,
          size: iconSize,
        ),
        filled: true,
        fillColor: AppTheme.primaryBlue.withValues(alpha: 0.5),
        contentPadding: EdgeInsets.symmetric(
          vertical: fontSize * 0.9,
          horizontal: 12,
        ),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(borderRadius),
          borderSide: BorderSide(
            color: AppTheme.accentGold.withValues(alpha: 0.3),
          ),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(borderRadius),
          borderSide: BorderSide(
            color: AppTheme.accentGold.withValues(alpha: 0.3),
          ),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(borderRadius),
          borderSide: const BorderSide(color: AppTheme.accentGold, width: 2),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(borderRadius),
          borderSide: const BorderSide(color: Colors.red),
        ),
      ),
      validator: (value) {
        if (value == null || value.isEmpty)
          return "Veuillez entrer votre code client";
        return null;
      },
      onFieldSubmitted: (_) => _handleLogin(),
    );
  }

  Widget _buildLoginButton(double height, double fontSize) {
    return AnimatedButton(
      text: "Accéder à ma chambre",
      onPressed: _isLoading ? null : _handleLogin,
      isLoading: _isLoading,
      width: double.infinity,
      height: height,
      backgroundColor: AppTheme.accentGold,
      textColor: AppTheme.primaryDark,
    );
  }
}
