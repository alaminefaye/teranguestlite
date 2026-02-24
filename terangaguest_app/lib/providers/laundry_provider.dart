import 'package:flutter/foundation.dart';
import '../models/laundry.dart';
import '../services/laundry_api.dart';

class LaundryProvider with ChangeNotifier {
  final LaundryApi _laundryApi = LaundryApi();

  List<LaundryService> _services = [];
  List<LaundryRequest> _requests = [];
  bool _isLoading = false;
  bool _hasMoreRequestPages = true;
  String? _errorMessage;
  String? _selectedRequestsPeriod;
  int _currentRequestsPage = 1;

  // État pour le formulaire de demande
  final Map<int, int> _selectedItems = {}; // serviceId: quantity

  List<LaundryService> get services => _services;
  List<LaundryRequest> get requests => _requests;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;
  Map<int, int> get selectedItems => _selectedItems;
  bool get hasMoreRequestPages => _hasMoreRequestPages;
  String? get selectedRequestsPeriod => _selectedRequestsPeriod;

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
    String? clientCode,
  }) async {
    if (_selectedItems.isEmpty) {
      throw 'Veuillez sélectionner au moins un article';
    }

    try {
      final items = _selectedItems.entries
          .map((entry) => {'service_id': entry.key, 'quantity': entry.value})
          .toList();

      final request = await _laundryApi.createLaundryRequest(
        items: items,
        specialInstructions: specialInstructions,
        clientCode: clientCode,
      );

      // Rafraîchir les demandes
      await fetchMyLaundryRequests(period: _selectedRequestsPeriod);

      // Vider la sélection
      clearSelection();

      return request;
    } catch (e) {
      throw e.toString();
    }
  }

  /// Récupère les demandes de l'utilisateur
  Future<void> fetchMyLaundryRequests({
    String? period,
    bool loadMore = false,
  }) async {
    if (!loadMore) {
      _isLoading = true;
      _errorMessage = null;
      _currentRequestsPage = 1;
      _selectedRequestsPeriod = period;
      _hasMoreRequestPages = true;
      notifyListeners();
    } else {
      if (!_hasMoreRequestPages || _isLoading) {
        return;
      }
      _isLoading = true;
      notifyListeners();
    }

    try {
      final effectivePeriod = loadMore
          ? _selectedRequestsPeriod
          : (period ?? _selectedRequestsPeriod);

      final result = await _laundryApi.getMyLaundryRequests(
        period: effectivePeriod,
        page: _currentRequestsPage,
      );

      final newRequests = result['requests'] as List<LaundryRequest>? ?? [];
      final meta = result['meta'] as Map<String, dynamic>? ?? {};

      if (loadMore) {
        _requests.addAll(newRequests);
      } else {
        _requests = newRequests;
      }

      final currentPage = meta['current_page'] is int
          ? meta['current_page'] as int
          : _currentRequestsPage;
      final lastPage = meta['last_page'] is int
          ? meta['last_page'] as int
          : currentPage;

      _hasMoreRequestPages = currentPage < lastPage;
      _currentRequestsPage = currentPage + 1;

      _isLoading = false;
      _errorMessage = null;
      notifyListeners();
    } catch (e) {
      debugPrint('Error fetching laundry requests: $e');
      _errorMessage = e.toString();
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> loadMoreLaundryRequests() async {
    await fetchMyLaundryRequests(loadMore: true);
  }

  /// Annuler une demande
  Future<void> cancelLaundryRequest(int requestId) async {
    try {
      await _laundryApi.cancelLaundryRequest(requestId);
      await fetchMyLaundryRequests(period: _selectedRequestsPeriod);
    } catch (e) {
      throw e.toString();
    }
  }

  Future<void> updateLaundryRequestStatus({
    required int requestId,
    required String action,
    String? reason,
  }) async {
    try {
      await _laundryApi.updateLaundryRequestStatus(
        requestId: requestId,
        action: action,
        reason: reason,
      );
      await fetchMyLaundryRequests(period: _selectedRequestsPeriod);
    } catch (e) {
      rethrow;
    }
  }

  /// Vide les données utilisateur (appelé lors d'un changement de session).
  void clearUserData() {
    _requests = [];
    _selectedItems.clear();
    _currentRequestsPage = 1;
    _hasMoreRequestPages = true;
    _selectedRequestsPeriod = null;
    _errorMessage = null;
    notifyListeners();
  }

  /// Rafraîchir
  Future<void> refreshLaundryServices() async {
    await fetchLaundryServices();
  }
}
