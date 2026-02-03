import 'package:flutter/foundation.dart';
import '../models/cart_item.dart';
import '../models/menu_item.dart';
import '../services/room_service_api.dart';

class CartProvider with ChangeNotifier {
  final RoomServiceApi _roomServiceApi = RoomServiceApi();
  
  // Liste des articles dans le panier
  final List<CartItem> _items = [];
  
  // Statut de chargement
  bool _isLoading = false;
  
  // Message d'erreur
  String? _errorMessage;

  // Getters
  List<CartItem> get items => [..._items];
  int get itemCount => _items.length;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;
  bool get isEmpty => _items.isEmpty;

  // Calculer le nombre total d'articles (avec quantités)
  int get totalItemsQuantity {
    return _items.fold(0, (sum, item) => sum + item.quantity);
  }

  // Calculer le total du panier
  double get totalAmount {
    return _items.fold(0.0, (sum, item) => sum + item.subtotal);
  }

  // Total formaté
  String get formattedTotal {
    final total = totalAmount.toStringAsFixed(0);
    return '$total FCFA';
  }

  // Ajouter un article au panier
  void addItem(MenuItem menuItem, {int quantity = 1, String? specialInstructions}) {
    // Vérifier si l'article existe déjà dans le panier
    final existingIndex = _items.indexWhere(
      (item) => item.menuItem.id == menuItem.id,
    );

    if (existingIndex >= 0) {
      // L'article existe déjà, augmenter la quantité
      _items[existingIndex].quantity += quantity;
      
      // Mettre à jour les instructions si fournies
      if (specialInstructions != null && specialInstructions.isNotEmpty) {
        _items[existingIndex].specialInstructions = specialInstructions;
      }
    } else {
      // Nouvel article, l'ajouter au panier
      _items.add(
        CartItem(
          menuItem: menuItem,
          quantity: quantity,
          specialInstructions: specialInstructions,
        ),
      );
    }

    notifyListeners();
  }

  // Retirer un article du panier
  void removeItem(int menuItemId) {
    _items.removeWhere((item) => item.menuItem.id == menuItemId);
    notifyListeners();
  }

  // Augmenter la quantité d'un article
  void incrementQuantity(int menuItemId) {
    final index = _items.indexWhere(
      (item) => item.menuItem.id == menuItemId,
    );

    if (index >= 0) {
      _items[index].quantity++;
      notifyListeners();
    }
  }

  // Diminuer la quantité d'un article
  void decrementQuantity(int menuItemId) {
    final index = _items.indexWhere(
      (item) => item.menuItem.id == menuItemId,
    );

    if (index >= 0) {
      if (_items[index].quantity > 1) {
        _items[index].quantity--;
        notifyListeners();
      } else {
        // Si quantité = 1, retirer l'article
        removeItem(menuItemId);
      }
    }
  }

  // Mettre à jour la quantité d'un article
  void updateQuantity(int menuItemId, int newQuantity) {
    if (newQuantity <= 0) {
      removeItem(menuItemId);
      return;
    }

    final index = _items.indexWhere(
      (item) => item.menuItem.id == menuItemId,
    );

    if (index >= 0) {
      _items[index].quantity = newQuantity;
      notifyListeners();
    }
  }

  // Mettre à jour les instructions spéciales d'un article
  void updateSpecialInstructions(int menuItemId, String? instructions) {
    final index = _items.indexWhere(
      (item) => item.menuItem.id == menuItemId,
    );

    if (index >= 0) {
      _items[index].specialInstructions = instructions;
      notifyListeners();
    }
  }

  // Vider le panier
  void clear() {
    _items.clear();
    _errorMessage = null;
    notifyListeners();
  }

  // Passer la commande (checkout)
  Future<Map<String, dynamic>> checkout({String? specialInstructions}) async {
    if (_items.isEmpty) {
      throw Exception('Le panier est vide');
    }

    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      // Préparer les données pour l'API
      final itemsData = _items.map((item) => item.toCheckoutJson()).toList();

      // Appeler l'API
      final result = await _roomServiceApi.checkout(
        items: itemsData,
        specialInstructions: specialInstructions,
      );

      // Vider le panier après une commande réussie
      clear();

      _isLoading = false;
      notifyListeners();

      return result;
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
      notifyListeners();
      rethrow;
    }
  }

  // Obtenir un article du panier par ID
  CartItem? getItem(int menuItemId) {
    try {
      return _items.firstWhere(
        (item) => item.menuItem.id == menuItemId,
      );
    } catch (e) {
      return null;
    }
  }

  // Vérifier si un article est dans le panier
  bool isInCart(int menuItemId) {
    return _items.any((item) => item.menuItem.id == menuItemId);
  }

  // Obtenir la quantité d'un article dans le panier
  int getItemQuantity(int menuItemId) {
    final item = getItem(menuItemId);
    return item?.quantity ?? 0;
  }
}
