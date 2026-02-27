import 'package:flutter/material.dart';
import '../config/theme.dart';

class QuantitySelector extends StatelessWidget {
  final int quantity;
  final VoidCallback onIncrement;
  final VoidCallback onDecrement;
  final int minQuantity;
  final int maxQuantity;

  const QuantitySelector({
    super.key,
    required this.quantity,
    required this.onIncrement,
    required this.onDecrement,
    this.minQuantity = 1,
    this.maxQuantity = 99,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [
            AppTheme.primaryBlue.withValues(alpha: 0.6),
            AppTheme.primaryDark.withValues(alpha: 0.8),
          ],
        ),
        borderRadius: BorderRadius.circular(30),
        border: Border.all(color: AppTheme.accentGold, width: 1.5),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          // Bouton -
          _buildButton(
            icon: Icons.remove,
            onPressed: quantity > minQuantity ? onDecrement : null,
          ),

          // Quantité
          Container(
            constraints: const BoxConstraints(minWidth: 50),
            padding: const EdgeInsets.symmetric(horizontal: 12),
            child: Text(
              quantity.toString(),
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.bold,
                color: AppTheme.accentGold,
              ),
            ),
          ),

          // Bouton +
          _buildButton(
            icon: Icons.add,
            onPressed: quantity < maxQuantity ? onIncrement : null,
          ),
        ],
      ),
    );
  }

  Widget _buildButton({
    required IconData icon,
    required VoidCallback? onPressed,
  }) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onPressed,
        borderRadius: BorderRadius.circular(30),
        child: Container(
          width: 44,
          height: 44,
          alignment: Alignment.center,
          child: Icon(
            icon,
            color: onPressed != null
                ? AppTheme.accentGold
                : AppTheme.textGray.withValues(alpha: 0.5),
            size: 24,
          ),
        ),
      ),
    );
  }
}
