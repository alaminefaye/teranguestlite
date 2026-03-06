import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../providers/auth_provider.dart';
import '../../widgets/animated_button.dart';
import '../dashboard/dashboard_screen.dart';
import '../admin/admin_home_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();

  bool _obscurePassword = true;
  bool _rememberMe = false;

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _handleLogin() async {
    if (!_formKey.currentState!.validate()) return;

    final authProvider = Provider.of<AuthProvider>(context, listen: false);

    final success = await authProvider.login(
      email: _emailController.text.trim(),
      password: _passwordController.text,
      rememberMe: _rememberMe,
    );

    if (!mounted) return;

    if (success) {
      final home = _resolveHomeScreen(authProvider);
      Navigator.of(
        context,
      ).pushReplacement(MaterialPageRoute(builder: (_) => home));
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            authProvider.errorMessage ??
                AppLocalizations.of(context).loginError,
          ),
          backgroundColor: Colors.red,
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(10),
          ),
        ),
      );
    }
  }

  Widget _resolveHomeScreen(AuthProvider auth) {
    if (auth.isAdmin || auth.isStaff) return const AdminHomeScreen();
    return const DashboardScreen();
  }

  @override
  Widget build(BuildContext context) {
    final screenWidth = MediaQuery.of(context).size.width;
    // Mobile si largeur < 600 (portrait téléphone)
    final isMobile = screenWidth < 600;

    // Valeurs adaptées selon le device
    final double logoWidth = isMobile ? 160.0 : 220.0;
    final double titleFontSize = isMobile ? 14.0 : 18.0;
    final double fieldFontSize = isMobile ? 13.0 : 16.0;
    final double iconSize = isMobile ? 18.0 : 24.0;
    final double borderRadius = isMobile ? 10.0 : 12.0;
    final double buttonHeight = isMobile ? 44.0 : 56.0;
    final double buttonFontSize = isMobile ? 14.0 : 16.0;
    final double padding = isMobile ? 24.0 : 32.0;
    final double spacingLogo = isMobile ? 24.0 : 40.0;
    final double spacingFields = isMobile ? 14.0 : 20.0;
    final double spacingRemember = isMobile ? 10.0 : 16.0;
    final double spacingButton = isMobile ? 20.0 : 32.0;
    final double checkboxFontSize = isMobile ? 12.0 : 14.0;

    // Conteneur centré avec largeur max sur tablette
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
                        // Logo
                        _buildLogo(logoWidth, titleFontSize),
                        SizedBox(height: spacingLogo),

                        // Email
                        _buildEmailField(fieldFontSize, iconSize, borderRadius),
                        SizedBox(height: spacingFields),

                        // Password
                        _buildPasswordField(
                          fieldFontSize,
                          iconSize,
                          borderRadius,
                        ),
                        SizedBox(height: spacingRemember),

                        // Remember me
                        _buildRememberMe(checkboxFontSize),
                        SizedBox(height: spacingButton),

                        // Bouton
                        _buildLoginButton(buttonHeight, buttonFontSize),

                        SizedBox(height: isMobile ? 24.0 : 36.0),

                        // Mention développeur
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
      ),
    );
  }

  Widget _buildLogo(double logoWidth, double titleFontSize) {
    return Column(
      children: [
        Image.asset('assets/logo.png', width: logoWidth),
        const SizedBox(height: 12),
        Text(
          AppLocalizations.of(context).login,
          style: TextStyle(fontSize: titleFontSize, color: AppTheme.textGray),
        ),
      ],
    );
  }

  Widget _buildEmailField(
    double fontSize,
    double iconSize,
    double borderRadius,
  ) {
    return TextFormField(
      controller: _emailController,
      keyboardType: TextInputType.emailAddress,
      style: TextStyle(color: Colors.white, fontSize: fontSize),
      decoration: InputDecoration(
        labelText: AppLocalizations.of(context).email,
        labelStyle: TextStyle(color: AppTheme.textGray, fontSize: fontSize),
        prefixIcon: Icon(
          Icons.email,
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
        final l10n = AppLocalizations.of(context);
        if (value == null || value.isEmpty) return l10n.emailRequired;
        if (!RegExp(r'^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$').hasMatch(value)) {
          return l10n.emailInvalid;
        }
        return null;
      },
    );
  }

  Widget _buildPasswordField(
    double fontSize,
    double iconSize,
    double borderRadius,
  ) {
    final l10n = AppLocalizations.of(context);
    return TextFormField(
      controller: _passwordController,
      obscureText: _obscurePassword,
      style: TextStyle(color: Colors.white, fontSize: fontSize),
      decoration: InputDecoration(
        labelText: l10n.password,
        labelStyle: TextStyle(color: AppTheme.textGray, fontSize: fontSize),
        prefixIcon: Icon(
          Icons.lock,
          color: AppTheme.accentGold,
          size: iconSize,
        ),
        suffixIcon: IconButton(
          icon: Icon(
            _obscurePassword ? Icons.visibility : Icons.visibility_off,
            color: AppTheme.textGray,
            size: iconSize,
          ),
          onPressed: () {
            setState(() {
              _obscurePassword = !_obscurePassword;
            });
          },
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
        final l10n = AppLocalizations.of(context);
        if (value == null || value.isEmpty) return l10n.passwordRequired;
        if (value.length < 6) return l10n.passwordTooShort;
        return null;
      },
    );
  }

  Widget _buildRememberMe(double fontSize) {
    return Row(
      children: [
        Checkbox(
          value: _rememberMe,
          onChanged: (value) {
            setState(() {
              _rememberMe = value ?? false;
            });
          },
          activeColor: AppTheme.accentGold,
          checkColor: AppTheme.primaryDark,
          materialTapTargetSize: MaterialTapTargetSize.shrinkWrap,
          visualDensity: VisualDensity.compact,
        ),
        Text(
          AppLocalizations.of(context).rememberMe,
          style: TextStyle(color: AppTheme.textGray, fontSize: fontSize),
        ),
      ],
    );
  }

  Widget _buildLoginButton(double height, double fontSize) {
    return Consumer<AuthProvider>(
      builder: (context, authProvider, child) {
        return AnimatedButton(
          text: AppLocalizations.of(context).loginButton,
          onPressed: authProvider.isLoading ? null : _handleLogin,
          isLoading: authProvider.isLoading,
          width: double.infinity,
          height: height,
          backgroundColor: AppTheme.accentGold,
          textColor: AppTheme.primaryDark,
        );
      },
    );
  }
}
