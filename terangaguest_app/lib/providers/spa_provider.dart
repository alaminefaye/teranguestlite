import 'package:flutter/foundation.dart';
import '../models/spa.dart';
import '../services/spa_api.dart';

class SpaProvider with ChangeNotifier {
  final SpaApi _spaApi = SpaApi();

  List<SpaService> _services = [];
  List<SpaReservation> _reservations = [];
  bool _isLoading = false;
  bool _hasMoreReservationPages = true;
  String? _errorMessage;
  String? _selectedCategory;
  String? _selectedReservationsPeriod;
  int _currentReservationsPage = 1;
  String? _clientCode;

  List<SpaService> get services => _services;
  List<SpaReservation> get reservations => _reservations;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;
  String? get selectedCategory => _selectedCategory;
  bool get hasMoreReservationPages => _hasMoreReservationPages;
  String? get selectedReservationsPeriod => _selectedReservationsPeriod;
  String? get clientCode => _clientCode;

  /// Définit le code client pour filtrer les historiques (guest connecté sur la tablette).
  void setClientCode(String? code) {
    _clientCode = code?.trim().isEmpty == true ? null : code?.trim();
  }

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
      await fetchMySpaReservations(period: _selectedReservationsPeriod);

      return reservation;
    } catch (e) {
      throw e.toString();
    }
  }

  /// Récupère les réservations spa de l'utilisateur
  Future<void> fetchMySpaReservations({
    String? period,
    bool loadMore = false,
  }) async {
    if (!loadMore) {
      _isLoading = true;
      _errorMessage = null;
      _currentReservationsPage = 1;
      _selectedReservationsPeriod = period;
      _hasMoreReservationPages = true;
      notifyListeners();
    } else {
      if (!_hasMoreReservationPages || _isLoading) {
        return;
      }
      _isLoading = true;
      notifyListeners();
    }

    try {
      final effectivePeriod = loadMore
          ? _selectedReservationsPeriod
          : (period ?? _selectedReservationsPeriod);

      final result = await _spaApi.getMySpaReservations(
        period: effectivePeriod,
        page: _currentReservationsPage,
        clientCode: _clientCode,
      );

      final newReservations =
          result['reservations'] as List<SpaReservation>? ?? [];
      final meta = result['meta'] as Map<String, dynamic>? ?? {};

      if (loadMore) {
        _reservations.addAll(newReservations);
      } else {
        _reservations = newReservations;
      }

      final currentPage = meta['current_page'] is int
          ? meta['current_page'] as int
          : _currentReservationsPage;
      final lastPage = meta['last_page'] is int
          ? meta['last_page'] as int
          : currentPage;

      _hasMoreReservationPages = currentPage < lastPage;
      _currentReservationsPage = currentPage + 1;

      _isLoading = false;
      _errorMessage = null;
      notifyListeners();
    } catch (e) {
      debugPrint('Error fetching spa reservations: $e');
      _errorMessage = e.toString();
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> loadMoreSpaReservations() async {
    await fetchMySpaReservations(loadMore: true);
  }

  /// Annuler une réservation spa
  Future<void> cancelSpaReservation(int reservationId, {String? reason}) async {
    try {
      await _spaApi.cancelSpaReservation(reservationId, reason: reason);
      // Rafraîchir la liste
      await fetchMySpaReservations(period: _selectedReservationsPeriod);
    } catch (e) {
      throw e.toString();
    }
  }

  Future<void> acceptRescheduledSpaReservation(int reservationId) async {
    try {
      await _spaApi.acceptRescheduledSpaReservation(reservationId);
      await fetchMySpaReservations(period: _selectedReservationsPeriod);
    } catch (e) {
      throw e.toString();
    }
  }

  Future<void> updateSpaReservationStatus({
    required int reservationId,
    required String action,
    DateTime? date,
    String? time,
    String? reason,
  }) async {
    try {
      await _spaApi.updateSpaReservationStatus(
        reservationId: reservationId,
        action: action,
        date: date,
        time: time,
        reason: reason,
      );
      await fetchMySpaReservations(period: _selectedReservationsPeriod);
    } catch (e) {
      throw e.toString();
    }
  }

  /// Vide les données utilisateur (appelé lors d'un changement de session).
  void clearUserData() {
    _reservations = [];
    _currentReservationsPage = 1;
    _hasMoreReservationPages = true;
    _selectedReservationsPeriod = null;
    _clientCode = null;
    _errorMessage = null;
    notifyListeners();
  }

  /// Rafraîchir la liste
  Future<void> refreshSpaServices() async {
    await fetchSpaServices(category: _selectedCategory);
  }
}
