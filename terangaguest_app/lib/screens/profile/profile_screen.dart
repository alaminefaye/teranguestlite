import 'package:flutter/material.dart';
import 'package:package_info_plus/package_info_plus.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../providers/auth_provider.dart';
import '../../utils/navigation_helper.dart';
import '../../utils/haptic_helper.dart';
import '../auth/login_screen.dart';
import 'settings_screen.dart';
import '../../widgets/animated_button.dart';
import '../orders/orders_list_screen.dart';
import '../restaurants/my_reservations_screen.dart';
import '../spa/my_spa_reservations_screen.dart';
import '../excursions/my_excursion_bookings_screen.dart';
import '../laundry/my_laundry_requests_screen.dart';
import '../palace/my_palace_requests_screen.dart';
import '../favorites/my_favorites_screen.dart';

/// Email et téléphone du support (modifiables par l'hôtel).
const String _supportEmail = 'support@kingfahdpalace.com';
const String _supportPhone = '+221338699000';

class ProfileScreen extends StatelessWidget {
  const ProfileScreen({super.key});

  Future<void> _handleLogout(BuildContext context) async {
    final l10n = AppLocalizations.of(context);
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        backgroundColor: AppTheme.primaryBlue,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(16),
          side: const BorderSide(
            color: AppTheme.accentGold,
            width: 1,
          ),
        ),
        title: Text(
          l10n.logout,
          style: const TextStyle(color: Colors.white),
        ),
        content: Text(
          l10n.logoutConfirm,
          style: const TextStyle(color: AppTheme.textGray),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: Text(
              l10n.cancel,
              style: const TextStyle(color: AppTheme.textGray),
            ),
          ),
          AnimatedButton(
            text: l10n.logout,
            onPressed: () => Navigator.pop(context, true),
            height: 44,
            backgroundColor: Colors.red,
            textColor: Colors.white,
          ),
        ],
      ),
    );

    if (confirmed == true && context.mounted) {
      final authProvider = Provider.of<AuthProvider>(context, listen: false);
      await authProvider.logout();

      if (context.mounted) {
        NavigationHelper.navigateAndRemoveUntil(context, const LoginScreen());
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(
          gradient: AppTheme.backgroundGradient,
        ),
        child: SafeArea(
          child: Consumer<AuthProvider>(
            builder: (context, authProvider, child) {
              final user = authProvider.user;

              if (user == null) {
                return Center(
                  child: Text(
                    AppLocalizations.of(context).noUser,
                    style: const TextStyle(color: Colors.white),
                  ),
                );
              }

              return ListView(
                padding: const EdgeInsets.all(20),
                children: [
                  // Header
                  _buildHeader(context),
                  const SizedBox(height: 30),

                  // Informations utilisateur
                  _buildUserInfo(user),
                  const SizedBox(height: 30),

                  // Actions
                  _buildActions(context),
                  const SizedBox(height: 30),

                  // Bouton déconnexion
                  _buildLogoutButton(context),
                  const SizedBox(height: 24),
                  _buildVersionFooter(),
                ],
              );
            },
          ),
        ),
      ),
    );
  }

  Widget _buildHeader(BuildContext context) {
    return Row(
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
          AppLocalizations.of(context).myProfile,
          style: const TextStyle(
            fontSize: 28,
            fontWeight: FontWeight.bold,
            color: Colors.white,
          ),
        ),
      ],
    );
  }

  Widget _buildUserInfo(dynamic user) {
    return Container(
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [
            AppTheme.primaryBlue.withValues(alpha: 0.6),
            AppTheme.primaryDark.withValues(alpha: 0.8),
          ],
        ),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(
          color: AppTheme.accentGold,
          width: 1.5,
        ),
      ),
      child: Column(
        children: [
          // Avatar
          Container(
            width: 100,
            height: 100,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: AppTheme.accentGold.withValues(alpha: 0.2),
              border: Border.all(
                color: AppTheme.accentGold,
                width: 2,
              ),
            ),
            child: Center(
              child: Text(
                user.name.substring(0, 1).toUpperCase(),
                style: const TextStyle(
                  fontSize: 40,
                  fontWeight: FontWeight.bold,
                  color: AppTheme.accentGold,
                ),
              ),
            ),
          ),
          const SizedBox(height: 20),

          // Nom
          Text(
            user.name,
            style: const TextStyle(
              fontSize: 24,
              fontWeight: FontWeight.bold,
              color: Colors.white,
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 8),

          // Email
          Text(
            user.email,
            style: const TextStyle(
              fontSize: 15,
              color: AppTheme.textGray,
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 20),

          // Infos supplémentaires
          if (user.roomNumber != null && (user.roomNumber ?? '').isNotEmpty) ...[
            const Divider(color: AppTheme.textGray, height: 32),
            _buildInfoRow(Icons.bed, 'Chambre', user.roomNumber ?? ''),
          ],
          if (user.enterprise != null) ...[
            const SizedBox(height: 12),
            _buildInfoRow(Icons.business, 'Hôtel', user.enterprise?.name ?? ''),
          ],
          const SizedBox(height: 12),
          _buildInfoRow(Icons.badge, 'Rôle', user.displayRole),
        ],
      ),
    );
  }

  Widget _buildInfoRow(IconData icon, String label, String value) {
    return Row(
      children: [
        Icon(icon, size: 20, color: AppTheme.accentGold),
        const SizedBox(width: 12),
        Text(
          '$label: ',
          style: const TextStyle(
            fontSize: 14,
            color: AppTheme.textGray,
          ),
        ),
        Expanded(
          child: Text(
            value,
            style: const TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.w600,
              color: Colors.white,
            ),
            textAlign: TextAlign.right,
          ),
        ),
      ],
    );
  }

  Widget _buildActions(BuildContext context) {
    return Column(
      children: [
        // Section: Mes Historiques
        Text(
          AppLocalizations.of(context).myHistories,
          style: const TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.bold,
            color: AppTheme.accentGold,
          ),
        ),
        const SizedBox(height: 16),

        // Mes Favoris
        _buildActionTile(
          context: context,
          icon: Icons.favorite_outline,
          title: AppLocalizations.of(context).myFavorites,
          onTap: () {
            HapticHelper.lightImpact();
            context.navigateTo(const MyFavoritesScreen());
          },
        ),
        const SizedBox(height: 12),

        // Mes Commandes
        _buildActionTile(
          context: context,
          icon: Icons.receipt_long_outlined,
          title: AppLocalizations.of(context).myOrders,
          onTap: () {
            HapticHelper.lightImpact();
            context.navigateTo(const OrdersListScreen());
          },
        ),
        const SizedBox(height: 12),

        // Mes Réservations Restaurant
        _buildActionTile(
          context: context,
          icon: Icons.restaurant_outlined,
          title: AppLocalizations.of(context).myRestaurantReservations,
          onTap: () {
            HapticHelper.lightImpact();
            context.navigateTo(const MyRestaurantReservationsScreen());
          },
        ),
        const SizedBox(height: 12),

        // Mes Réservations Spa
        _buildActionTile(
          context: context,
          icon: Icons.spa_outlined,
          title: AppLocalizations.of(context).mySpaReservations,
          onTap: () {
            HapticHelper.lightImpact();
            context.navigateTo(const MySpaReservationsScreen());
          },
        ),
        const SizedBox(height: 12),

        // Mes Bookings Excursions
        _buildActionTile(
          context: context,
          icon: Icons.landscape_outlined,
          title: AppLocalizations.of(context).myExcursions,
          onTap: () {
            HapticHelper.lightImpact();
            context.navigateTo(const MyExcursionBookingsScreen());
          },
        ),
        const SizedBox(height: 12),

        // Mes Demandes Blanchisserie
        _buildActionTile(
          context: context,
          icon: Icons.local_laundry_service_outlined,
          title: AppLocalizations.of(context).myLaundryRequests,
          onTap: () {
            HapticHelper.lightImpact();
            context.navigateTo(const MyLaundryRequestsScreen());
          },
        ),
        const SizedBox(height: 12),

        // Mes Demandes Palace
        _buildActionTile(
          context: context,
          icon: Icons.star_outline,
          title: AppLocalizations.of(context).myPalaceRequests,
          onTap: () {
            HapticHelper.lightImpact();
            context.navigateTo(const MyPalaceRequestsScreen());
          },
        ),
        const SizedBox(height: 24),

        // Section: Paramètres
        Text(
          AppLocalizations.of(context).settings,
          style: const TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.bold,
            color: AppTheme.accentGold,
          ),
        ),
        const SizedBox(height: 16),

        // À propos
        _buildActionTile(
          context: context,
          icon: Icons.info_outline,
          title: AppLocalizations.of(context).about,
          onTap: () {
            HapticHelper.lightImpact();
            _showAboutDialog(context);
          },
        ),
        const SizedBox(height: 12),

        // Contacter le support
        _buildActionTile(
          context: context,
          icon: Icons.support_agent_outlined,
          title: AppLocalizations.of(context).contactSupport,
          onTap: () {
            HapticHelper.lightImpact();
            _showContactSupportDialog(context);
          },
        ),
        const SizedBox(height: 12),

        // Paramètres
        _buildActionTile(
          context: context,
          icon: Icons.settings_outlined,
          title: AppLocalizations.of(context).settings,
          onTap: () {
            HapticHelper.lightImpact();
            context.navigateTo(const SettingsScreen());
          },
        ),
      ],
    );
  }

  Widget _buildActionTile({
    required BuildContext context,
    required IconData icon,
    required String title,
    required VoidCallback onTap,
  }) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
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
              Icon(icon, color: AppTheme.accentGold),
              const SizedBox(width: 16),
              Expanded(
                child: Text(
                  title,
                  style: const TextStyle(
                    fontSize: 16,
                    color: Colors.white,
                  ),
                ),
              ),
              const Icon(
                Icons.arrow_forward_ios,
                size: 16,
                color: AppTheme.textGray,
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildLogoutButton(BuildContext context) {
    return AnimatedOutlineButton(
      text: AppLocalizations.of(context).logout,
      icon: Icons.logout,
      onPressed: () => _handleLogout(context),
      width: double.infinity,
      height: 56,
      borderColor: Colors.red,
      textColor: Colors.red,
    );
  }

  Future<void> _launchUrl(Uri uri, BuildContext context) async {
    final launched = await canLaunchUrl(uri)
        ? await launchUrl(uri, mode: LaunchMode.externalApplication)
        : false;
    if (!launched && context.mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(AppLocalizations.of(context).cannotOpenLink),
          backgroundColor: AppTheme.primaryBlue,
        ),
      );
    }
  }

  void _showContactSupportDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        backgroundColor: AppTheme.primaryBlue,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(16),
          side: const BorderSide(color: AppTheme.accentGold, width: 1),
        ),
        title: Row(
          children: [
            const Icon(Icons.support_agent, color: AppTheme.accentGold),
            const SizedBox(width: 12),
            Text(
              AppLocalizations.of(context).contactSupportTitle,
              style: const TextStyle(color: AppTheme.accentGold),
            ),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Text(
              AppLocalizations.of(context).chooseContact,
              style: TextStyle(color: AppTheme.textGray),
            ),
            const SizedBox(height: 20),
            Material(
              color: Colors.transparent,
              child: InkWell(
                onTap: () {
                  Navigator.pop(context);
                  _launchUrl(Uri.parse('mailto:$_supportEmail'), context);
                },
                borderRadius: BorderRadius.circular(12),
                child: Padding(
                  padding: const EdgeInsets.symmetric(vertical: 12),
                  child: Row(
                    children: [
                      Icon(Icons.email_outlined, color: AppTheme.accentGold),
                      const SizedBox(width: 16),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              'Email',
                              style: TextStyle(
                                color: Colors.white,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                            Text(
                              _supportEmail,
                              style: const TextStyle(
                                color: AppTheme.textGray,
                                fontSize: 13,
                              ),
                            ),
                          ],
                        ),
                      ),
                      const Icon(Icons.open_in_new, size: 18, color: AppTheme.textGray),
                    ],
                  ),
                ),
              ),
            ),
            const SizedBox(height: 12),
            Material(
              color: Colors.transparent,
              child: InkWell(
                onTap: () {
                  Navigator.pop(context);
                  _launchUrl(Uri.parse('tel:$_supportPhone'), context);
                },
                borderRadius: BorderRadius.circular(12),
                child: Padding(
                  padding: const EdgeInsets.symmetric(vertical: 12),
                  child: Row(
                    children: [
                      Icon(Icons.phone_outlined, color: AppTheme.accentGold),
                      const SizedBox(width: 16),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text(
                              'Téléphone',
                              style: TextStyle(
                                color: Colors.white,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                            Text(
                              _supportPhone,
                              style: const TextStyle(
                                color: AppTheme.textGray,
                                fontSize: 13,
                              ),
                            ),
                          ],
                        ),
                      ),
                      const Icon(Icons.open_in_new, size: 18, color: AppTheme.textGray),
                    ],
                  ),
                ),
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text(AppLocalizations.of(context).close, style: const TextStyle(color: AppTheme.accentGold)),
          ),
        ],
      ),
    );
  }

  void _showAboutDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        backgroundColor: AppTheme.primaryBlue,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(16),
          side: const BorderSide(color: AppTheme.accentGold, width: 1),
        ),
        title: Text(
          AppLocalizations.of(context).about,
          style: const TextStyle(color: AppTheme.accentGold),
        ),
        content: FutureBuilder<PackageInfo>(
          future: PackageInfo.fromPlatform(),
          builder: (context, snapshot) {
            final l10n = AppLocalizations.of(context);
            final data = snapshot.data;
            final version = data != null
                ? '${data.version}+${data.buildNumber}'
                : '—';
            return Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'TerangaGuest',
                  style: TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                    color: Colors.white,
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  '${l10n.version} $version',
                  style: const TextStyle(color: AppTheme.textGray),
                ),
                const SizedBox(height: 16),
                Text(
                  l10n.aboutDescription,
                  style: const TextStyle(
                    color: AppTheme.textGray,
                    fontSize: 14,
                  ),
                ),
              ],
            );
          },
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text(AppLocalizations.of(context).ok, style: const TextStyle(color: AppTheme.accentGold)),
          ),
        ],
      ),
    );
  }

  Widget _buildVersionFooter() {
    return FutureBuilder<PackageInfo>(
      future: PackageInfo.fromPlatform(),
      builder: (context, snapshot) {
        final l10n = AppLocalizations.of(context);
        final data = snapshot.data;
        final version = data != null
            ? '${data.version}+${data.buildNumber}'
            : '2.0.10';
        return Center(
          child: Text(
            l10n.appNameVersion(l10n.version, version),
            style: TextStyle(
              fontSize: 12,
              color: AppTheme.textGray.withValues(alpha: 0.7),
            ),
          ),
        );
      },
    );
  }
}
