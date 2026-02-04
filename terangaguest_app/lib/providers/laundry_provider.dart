import 'package:flutter/foundation.dart';
import '../models/laundry.dart';
import '../services/laundry_api.dart';

class LaundryProvider with ChangeNotifier {
  final LaundryApi _laundryApi = LaundryApi();

  List<LaundryService> _services = [];
  List<LaundryRequest> _requests = [];
  bool _isLoading = false;
  String? _errorMessage;

  // État pour le formulaire de demande
  final Map<int, int> _selectedItems = {}; // serviceId: quantity

  List<LaundryService> get services => _services;
  List<LaundryRequest> get requests => _requests;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;
  Map<int, int> get selectedItems => _selectedItems;

  int getQuantityForService(int serviceId) => _selectedItems[serviceId] ?? 0;

  double getTotalPrice() {
    double total = 0;
    for (var entry in _selectedItems.entries) {
      final service = _services.firstWhere((s) => s.id == entry.key);
      total += service.pricePerItem * entry.value;
    }
    return total;
  }

  int getTotalItems() {
    return _selectedItems.values.fold(0, (sum, qty) => sum + qty);
  }

  void updateQuantity(int serviceId, int quantity) {
    if (quantity > 0) {
      _selectedItems[serviceId] = quantity;
    } else {
      _selectedItems.remove(serviceId);
    }
    notifyListeners();
  }

  void clearSelection() {
    _selectedItems.clear();
    notifyListeners();
  }

  /// Récupère les services de blanchisserie
  Future<void> fetchLaundryServices() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      _services = await _laundryApi.getLaundryServices();
      _isLoading = false;
      _errorMessage = null;
      notifyListeners();
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Créer une demande de blanchisserie
  Future<LaundryRequest> createLaundryRequest({
    String? specialInstructions,
  }) async {
    if (_selectedItems.isEmpty) {
      throw 'Veuillez sélectionner au moins un article';
    }

    try {
      final items = _selectedItems.entries
          .map((entry) => {
                'service_id': entry.key,
                'quantity': entry.value,
              })
          .toList();

      final request = await _laundryApi.createLaundryRequest(
        items: items,
        specialInstructions: specialInstructions,
      );

      // Rafraîchir les demandes
      await fetchMyLaundryRequests();

      // Vider la sélection
      clearSelection();

      return request;
    } catch (e) {
      throw e.toString();
    }
  }

  /// Récupère les demandes de l'utilisateur
  Future<void> fetchMyLaundryRequests() async {
    try {
      _requests = await _laundryApi.getMyLaundryRequests();
      notifyListeners();
    } catch (e) {
      debugPrint('Error fetching laundry requests: $e');
    }
  }

  /// Annuler une demande
  Future<void> cancelLaundryRequest(int requestId) async {
    try {
      await _laundryApi.cancelLaundryRequest(requestId);
      await fetchMyLaundryRequests();
    } catch (e) {
      throw e.toString();
    }
  }

  /// Rafraîchir
  Future<void> refreshLaundryServices() async {
    await fetchLaundryServices();
  }
}
