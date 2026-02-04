import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../config/theme.dart';
import '../generated/l10n/app_localizations.dart';
import '../providers/cart_provider.dart';
import '../screens/room_service/cart_screen.dart';
import '../utils/navigation_helper.dart';
import '../utils/haptic_helper.dart';

class CartBadge extends StatelessWidget {
  final Color iconColor;
  final double iconSize;

  const CartBadge({
    super.key,
    this.iconColor = AppTheme.accentGold,
    this.iconSize = 24,
  });

  @override
  Widget build(BuildContext context) {
    return Consumer<CartProvider>(
      builder: (context, cart, child) {
        return Semantics(
          button: true,
          label: cart.itemCount > 0 ? '${AppLocalizations.of(context).cart}, ${AppLocalizations.of(context).articleCount(cart.itemCount)}' : AppLocalizations.of(context).cart,
          child: Stack(
          clipBehavior: Clip.none,
          children: [
            IconButton(
              icon: Icon(
                Icons.shopping_cart,
                color: iconColor,
                size: iconSize,
              ),
              onPressed: () {
                HapticHelper.lightImpact();
                context.navigateTo(const CartScreen());
              },
            ),
            
            // Badge avec le nombre d'articles
            if (cart.itemCount > 0)
              Positioned(
                right: 6,
                top: 6,
                child: Container(
                  padding: const EdgeInsets.all(4),
                  constraints: const BoxConstraints(
                    minWidth: 18,
                    minHeight: 18,
                  ),
                  decoration: BoxDecoration(
                    color: Colors.red,
                    shape: BoxShape.circle,
                    border: Border.all(
                      color: AppTheme.primaryDark,
                      width: 1.5,
                    ),
                  ),
                  child: Center(
                    child: Text(
                      cart.itemCount > 9 ? '9+' : cart.itemCount.toString(),
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 10,
                        fontWeight: FontWeight.bold,
                        height: 1,
                      ),
                    ),
                  ),
                ),
              ),
          ],
        ),
        );
      },
    );
  }
}
