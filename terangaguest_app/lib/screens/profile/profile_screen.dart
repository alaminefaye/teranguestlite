import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../providers/auth_provider.dart';
import '../auth/login_screen.dart';
import 'change_password_screen.dart';

class ProfileScreen extends StatelessWidget {
  const ProfileScreen({super.key});

  Future<void> _handleLogout(BuildContext context) async {
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
        title: const Text(
          'Déconnexion',
          style: TextStyle(color: Colors.white),
        ),
        content: const Text(
          'Êtes-vous sûr de vouloir vous déconnecter ?',
          style: TextStyle(color: AppTheme.textGray),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text(
              'Annuler',
              style: TextStyle(color: AppTheme.textGray),
            ),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.red,
            ),
            child: const Text(
              'Déconnexion',
              style: TextStyle(color: Colors.white),
            ),
          ),
        ],
      ),
    );

    if (confirmed == true && context.mounted) {
      final authProvider = Provider.of<AuthProvider>(context, listen: false);
      await authProvider.logout();

      if (context.mounted) {
        Navigator.of(context).pushAndRemoveUntil(
          MaterialPageRoute(builder: (_) => const LoginScreen()),
          (route) => false,
        );
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
                return const Center(
                  child: Text(
                    'Aucun utilisateur connecté',
                    style: TextStyle(color: Colors.white),
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
          onPressed: () => Navigator.pop(context),
        ),
        const SizedBox(width: 12),
        const Text(
          'Mon Profil',
          style: TextStyle(
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
            AppTheme.primaryBlue.withOpacity(0.6),
            AppTheme.primaryDark.withOpacity(0.8),
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
              color: AppTheme.accentGold.withOpacity(0.2),
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
          if (user.roomNumber != null) ...[
            const Divider(color: AppTheme.textGray, height: 32),
            _buildInfoRow(Icons.bed, 'Chambre', user.roomNumber!),
          ],
          if (user.enterprise != null) ...[
            const SizedBox(height: 12),
            _buildInfoRow(Icons.business, 'Hôtel', user.enterprise!.name),
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
        // Changer mot de passe
        _buildActionTile(
          context: context,
          icon: Icons.lock_outline,
          title: 'Changer le mot de passe',
          onTap: () {
            Navigator.push(
              context,
              MaterialPageRoute(
                builder: (_) => const ChangePasswordScreen(),
              ),
            );
          },
        ),
        const SizedBox(height: 12),

        // Paramètres (à venir)
        _buildActionTile(
          context: context,
          icon: Icons.settings_outlined,
          title: 'Paramètres',
          onTap: () {
            ScaffoldMessenger.of(context).showSnackBar(
              const SnackBar(
                content: Text('Écran "Paramètres" à venir'),
                backgroundColor: AppTheme.primaryBlue,
              ),
            );
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
            color: AppTheme.primaryBlue.withOpacity(0.5),
            borderRadius: BorderRadius.circular(12),
            border: Border.all(
              color: AppTheme.accentGold.withOpacity(0.3),
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
    return SizedBox(
      width: double.infinity,
      height: 56,
      child: OutlinedButton(
        onPressed: () => _handleLogout(context),
        style: OutlinedButton.styleFrom(
          side: const BorderSide(
            color: Colors.red,
            width: 2,
          ),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
        ),
        child: const Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.logout, color: Colors.red),
            SizedBox(width: 12),
            Text(
              'Déconnexion',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
                color: Colors.red,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
