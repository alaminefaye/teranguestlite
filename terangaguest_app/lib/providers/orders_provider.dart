import 'package:flutter/foundation.dart';
import '../models/order.dart';
import '../services/orders_api.dart';

class OrdersProvider with ChangeNotifier {
  final OrdersApi _ordersApi = OrdersApi();

  List<Order> _orders = [];
  bool _isLoading = false;
  String? _errorMessage;
  int _currentPage = 1;
  bool _hasMorePages = true;
  String? _selectedStatus;
  String? _selectedPeriod;
  String? _clientCode;

  List<Order> get orders => _orders;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;
  bool get hasMorePages => _hasMorePages;
  String? get selectedStatus => _selectedStatus;
  String? get selectedPeriod => _selectedPeriod;
  String? get clientCode => _clientCode;

  /// Nombre de commandes en cours (pending, confirmed, preparing, delivering)
  int get inProgressOrdersCount {
    const inProgress = ['pending', 'confirmed', 'preparing', 'delivering'];
    return _orders.where((o) => inProgress.contains(o.status)).length;
  }

  /// Charge les commandes pour le footer (sans filtre) — appelé au démarrage du dashboard
  Future<void> fetchOrdersForDashboard() async {
    await fetchOrders(status: null, period: null);
  }

  /// Définit le code client pour filtrer les historiques (guest connecté sur la tablette).
  void setClientCode(String? code) {
    _clientCode = code?.trim().isEmpty == true ? null : code?.trim();
  }

  /// Récupère les commandes
  Future<void> fetchOrders({
    String? status,
    String? period,
    bool loadMore = false,
  }) async {
    if (!loadMore) {
      _isLoading = true;
      _errorMessage = null;
      _currentPage = 1;
      _selectedStatus = status;
      _selectedPeriod = period;
      _safeNotifyListeners();
    }

    try {
      final result = await _ordersApi.getOrders(
        status: status,
        period: loadMore ? _selectedPeriod : period,
        page: _currentPage,
        clientCode: _clientCode,
      );

      final newOrders = result['orders'] as List<Order>;
      final meta = result['meta'] as Map<String, dynamic>;

      if (loadMore) {
        _orders.addAll(newOrders);
      } else {
        _orders = newOrders;
      }

      _hasMorePages = meta['current_page'] < (meta['last_page'] ?? 1);
      _isLoading = false;
      _errorMessage = null;
      notifyListeners();
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
      _safeNotifyListeners();
    }
  }

  void _safeNotifyListeners() {
    try {
      notifyListeners();
    } catch (_) {
      // Évite qu'une exception dans un listener (ex. rebuild) fasse crasher l'app
    }
  }

  /// Charge plus de commandes
  Future<void> loadMoreOrders() async {
    if (_hasMorePages && !_isLoading) {
      _currentPage++;
      await fetchOrders(
        status: _selectedStatus,
        period: _selectedPeriod,
        loadMore: true,
      );
    }
  }

  /// Récupère le détail d'une commande
  Future<Order> fetchOrderDetail(int orderId) async {
    return await _ordersApi.getOrderDetail(orderId);
  }

  /// Recommander une commande
  Future<void> reorderOrder(int orderId) async {
    try {
      await _ordersApi.reorderOrder(orderId);
    } catch (e) {
      throw e.toString();
    }
  }

  /// Annuler une commande
  Future<void> cancelOrder(int orderId, {String? reason}) async {
    try {
      await _ordersApi.cancelOrder(orderId, reason: reason);
      await fetchOrders(status: _selectedStatus, period: _selectedPeriod);
    } catch (e) {
      throw e.toString();
    }
  }

  Future<void> updateOrderStatus({
    required int orderId,
    required String action,
  }) async {
    try {
      await _ordersApi.updateOrderStatus(orderId: orderId, action: action);
      await fetchOrders(status: _selectedStatus, period: _selectedPeriod);
    } catch (e) {
      throw e.toString();
    }
  }

  /// Notifie le personnel "Service en chambre" qu'une commande est prête à livrer.
  Future<void> notifyRoomService(int orderId) async {
    try {
      await _ordersApi.notifyRoomService(orderId);
    } catch (e) {
      throw e.toString();
    }
  }

  /// Annule une commande (par le staff/admin) avec notification au client.
  Future<void> cancelByStaff(int orderId, String reason) async {
    try {
      await _ordersApi.cancelByStaff(orderId, reason);
    } catch (e) {
      throw e.toString();
    }
  }

  /// Rafraîchir les commandes
  Future<void> refreshOrders() async {
    await fetchOrders(status: _selectedStatus, period: _selectedPeriod);
  }

  /// Vide la liste et met en état de chargement (pour forcer un affichage
  /// "loading" avant de recharger, ex. après création d'une commande).
  void clearOrdersAndSetLoading() {
    _orders = [];
    _isLoading = true;
    _errorMessage = null;
    _currentPage = 1;
    _selectedPeriod = null;
    _clientCode = null;
    _safeNotifyListeners();
  }
}
