import 'package:flutter/foundation.dart';
import '../models/spa.dart';
import '../services/spa_api.dart';

class SpaProvider with ChangeNotifier {
  final SpaApi _spaApi = SpaApi();

  List<SpaService> _services = [];
  List<SpaReservation> _reservations = [];
  bool _isLoading = false;
  String? _errorMessage;
  String? _selectedCategory;

  List<SpaService> get services => _services;
  List<SpaReservation> get reservations => _reservations;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;
  String? get selectedCategory => _selectedCategory;

  /// Récupère les services spa
  Future<void> fetchSpaServices({String? category}) async {
    _isLoading = true;
    _errorMessage = null;
    _selectedCategory = category;
    notifyListeners();

    try {
      _services = await _spaApi.getSpaServices(category: category);
      _isLoading = false;
      _errorMessage = null;
      notifyListeners();
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Récupère le détail d'un service spa
  Future<SpaService> fetchSpaServiceDetail(int serviceId) async {
    try {
      return await _spaApi.getSpaServiceDetail(serviceId);
    } catch (e) {
      throw e.toString();
    }
  }

  /// Réserver un service spa
  Future<SpaReservation> reserveSpaService({
    required int serviceId,
    required DateTime date,
    required String time,
    String? specialRequests,
    String? clientCode,
  }) async {
    try {
      final reservation = await _spaApi.reserveSpaService(
        serviceId: serviceId,
        date: date,
        time: time,
        specialRequests: specialRequests,
        clientCode: clientCode,
      );

      // Rafraîchir les réservations
      await fetchMySpaReservations();

      return reservation;
    } catch (e) {
      throw e.toString();
    }
  }

  /// Récupère les réservations spa de l'utilisateur
  Future<void> fetchMySpaReservations() async {
    try {
      _reservations = await _spaApi.getMySpaReservations();
      notifyListeners();
    } catch (e) {
      debugPrint('Error fetching spa reservations: $e');
    }
  }

  /// Annuler une réservation spa
  Future<void> cancelSpaReservation(int reservationId) async {
    try {
      await _spaApi.cancelSpaReservation(reservationId);
      // Rafraîchir la liste
      await fetchMySpaReservations();
    } catch (e) {
      throw e.toString();
    }
  }

  /// Rafraîchir la liste
  Future<void> refreshSpaServices() async {
    await fetchSpaServices(category: _selectedCategory);
  }
}
