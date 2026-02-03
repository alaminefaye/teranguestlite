import 'package:flutter/material.dart';
import '../../config/theme.dart';
import '../dashboard/dashboard_screen.dart';

class OrderConfirmationScreen extends StatelessWidget {
  final Map<String, dynamic> orderData;

  const OrderConfirmationScreen({
    super.key,
    required this.orderData,
  });

  @override
  Widget build(BuildContext context) {
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
          child: Padding(
            padding: const EdgeInsets.all(24.0),
            child: Column(
              children: [
                Expanded(
                  child: Center(
                    child: SingleChildScrollView(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          // Animation de succès
                          _buildSuccessAnimation(),
                          const SizedBox(height: 32),

                          // Message de confirmation
                          const Text(
                            'Commande confirmée !',
                            style: TextStyle(
                              fontSize: 32,
                              fontWeight: FontWeight.bold,
                              color: Colors.white,
                            ),
                            textAlign: TextAlign.center,
                          ),
                          const SizedBox(height: 16),

                          const Text(
                            'Votre commande a été enregistrée avec succès',
                            style: TextStyle(
                              fontSize: 16,
                              color: AppTheme.textGray,
                            ),
                            textAlign: TextAlign.center,
                          ),
                          const SizedBox(height: 40),

                          // Carte avec les détails de la commande
                          _buildOrderDetails(),

                          const SizedBox(height: 32),

                          // Message informatif
                          _buildInfoMessage(),
                        ],
                      ),
                    ),
                  ),
                ),

                // Boutons d'action
                _buildActionButtons(context),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildSuccessAnimation() {
    return Container(
      width: 120,
      height: 120,
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        color: Colors.green.withOpacity(0.2),
        border: Border.all(
          color: Colors.green,
          width: 3,
        ),
      ),
      child: const Icon(
        Icons.check_circle,
        size: 80,
        color: Colors.green,
      ),
    );
  }

  Widget _buildOrderDetails() {
    final orderNumber = orderData['order_number'] ?? 'N/A';
    final total = orderData['formatted_total'] ?? orderData['total_amount']?.toString() ?? '0';
    final itemsCount = (orderData['items'] as List?)?.length ?? 0;

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
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.3),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        children: [
          // Numéro de commande
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                'N° de commande',
                style: TextStyle(
                  fontSize: 14,
                  color: AppTheme.textGray,
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 12,
                  vertical: 6,
                ),
                decoration: BoxDecoration(
                  color: AppTheme.accentGold.withOpacity(0.2),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(
                    color: AppTheme.accentGold.withOpacity(0.3),
                  ),
                ),
                child: Text(
                  orderNumber,
                  style: const TextStyle(
                    fontSize: 15,
                    fontWeight: FontWeight.bold,
                    color: AppTheme.accentGold,
                    letterSpacing: 0.5,
                  ),
                ),
              ),
            ],
          ),

          const SizedBox(height: 20),
          const Divider(color: AppTheme.textGray, height: 1),
          const SizedBox(height: 20),

          // Nombre d'articles
          _buildDetailRow(
            icon: Icons.restaurant_menu,
            label: 'Articles',
            value: '$itemsCount article${itemsCount > 1 ? 's' : ''}',
          ),

          const SizedBox(height: 16),

          // Montant total
          _buildDetailRow(
            icon: Icons.payments,
            label: 'Total',
            value: total is String ? total : '$total FCFA',
            valueColor: AppTheme.accentGold,
            valueFontSize: 20,
            valueFontWeight: FontWeight.bold,
          ),

          const SizedBox(height: 16),

          // Statut
          _buildDetailRow(
            icon: Icons.info,
            label: 'Statut',
            value: 'En attente',
            valueColor: Colors.orange,
          ),
        ],
      ),
    );
  }

  Widget _buildDetailRow({
    required IconData icon,
    required String label,
    required String value,
    Color? valueColor,
    double? valueFontSize,
    FontWeight? valueFontWeight,
  }) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Row(
          children: [
            Icon(
              icon,
              size: 20,
              color: AppTheme.textGray,
            ),
            const SizedBox(width: 8),
            Text(
              label,
              style: const TextStyle(
                fontSize: 15,
                color: AppTheme.textGray,
              ),
            ),
          ],
        ),
        Text(
          value,
          style: TextStyle(
            fontSize: valueFontSize ?? 15,
            fontWeight: valueFontWeight ?? FontWeight.w600,
            color: valueColor ?? Colors.white,
          ),
        ),
      ],
    );
  }

  Widget _buildInfoMessage() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppTheme.accentGold.withOpacity(0.1),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: AppTheme.accentGold.withOpacity(0.3),
        ),
      ),
      child: const Row(
        children: [
          Icon(
            Icons.notifications_active,
            color: AppTheme.accentGold,
            size: 24,
          ),
          SizedBox(width: 12),
          Expanded(
            child: Text(
              'Vous recevrez une notification dès que votre commande sera confirmée par le restaurant.',
              style: TextStyle(
                fontSize: 13,
                color: AppTheme.textGray,
                height: 1.4,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildActionButtons(BuildContext context) {
    return Column(
      children: [
        // Bouton principal : Retour à l'accueil
        SizedBox(
          width: double.infinity,
          height: 56,
          child: ElevatedButton(
            onPressed: () {
              Navigator.of(context).pushAndRemoveUntil(
                MaterialPageRoute(
                  builder: (context) => const DashboardScreen(),
                ),
                (route) => false,
              );
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: AppTheme.accentGold,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(16),
              ),
              elevation: 0,
            ),
            child: const Text(
              'Retour à l\'accueil',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
                color: AppTheme.primaryDark,
              ),
            ),
          ),
        ),

        const SizedBox(height: 12),

        // Bouton secondaire : Voir mes commandes
        SizedBox(
          width: double.infinity,
          height: 56,
          child: OutlinedButton(
            onPressed: () {
              // TODO: Naviguer vers l'écran de mes commandes
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(
                  content: Text('Écran "Mes commandes" à venir'),
                  backgroundColor: AppTheme.primaryBlue,
                ),
              );
            },
            style: OutlinedButton.styleFrom(
              side: const BorderSide(
                color: AppTheme.accentGold,
                width: 1.5,
              ),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(16),
              ),
            ),
            child: const Text(
              'Suivre ma commande',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
                color: AppTheme.accentGold,
              ),
            ),
          ),
        ),
      ],
    );
  }
}
