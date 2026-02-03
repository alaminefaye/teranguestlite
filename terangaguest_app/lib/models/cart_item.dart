import 'menu_item.dart';

class CartItem {
  final MenuItem menuItem;
  int quantity;
  String? specialInstructions;

  CartItem({
    required this.menuItem,
    this.quantity = 1,
    this.specialInstructions,
  });

  // Calculer le sous-total pour cet article
  double get subtotal => menuItem.price * quantity;

  // Sous-total formaté
  String get formattedSubtotal {
    final total = subtotal.toStringAsFixed(0);
    return '$total FCFA';
  }

  // Factory constructor pour créer une instance depuis JSON
  factory CartItem.fromJson(Map<String, dynamic> json) {
    return CartItem(
      menuItem: MenuItem.fromJson(json['menu_item'] as Map<String, dynamic>),
      quantity: json['quantity'] as int? ?? 1,
      specialInstructions: json['special_instructions'] as String?,
    );
  }

  // Convertir en JSON
  Map<String, dynamic> toJson() {
    return {
      'menu_item': menuItem.toJson(),
      'quantity': quantity,
      'special_instructions': specialInstructions,
    };
  }

  // Pour l'API checkout
  Map<String, dynamic> toCheckoutJson() {
    return {
      'menu_item_id': menuItem.id,
      'quantity': quantity,
      'special_instructions': specialInstructions,
    };
  }

  // Copier avec des modifications
  CartItem copyWith({
    MenuItem? menuItem,
    int? quantity,
    String? specialInstructions,
  }) {
    return CartItem(
      menuItem: menuItem ?? this.menuItem,
      quantity: quantity ?? this.quantity,
      specialInstructions: specialInstructions ?? this.specialInstructions,
    );
  }
}
