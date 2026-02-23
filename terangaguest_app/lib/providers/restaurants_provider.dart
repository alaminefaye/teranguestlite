import 'package:flutter/foundation.dart';
import '../models/restaurant.dart';
import '../services/restaurants_api.dart';

class RestaurantsProvider with ChangeNotifier {
  final RestaurantsApi _restaurantsApi = RestaurantsApi();

  List<Restaurant> _restaurants = [];
  List<RestaurantReservation> _reservations = [];
  bool _isLoading = false;
  bool _hasMoreReservationPages = true;
  String? _errorMessage;
  String? _selectedType;
  String? _selectedReservationsPeriod;
  int _currentReservationsPage = 1;

  List<Restaurant> get restaurants => _restaurants;
  List<RestaurantReservation> get reservations => _reservations;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;
  String? get selectedType => _selectedType;
  bool get hasMoreReservationPages => _hasMoreReservationPages;
  String? get selectedReservationsPeriod => _selectedReservationsPeriod;

  /// Récupère les restaurants
  Future<void> fetchRestaurants({String? type}) async {
    _isLoading = true;
    _errorMessage = null;
    _selectedType = type;
    notifyListeners();

    try {
      _restaurants = await _restaurantsApi.getRestaurants(type: type);
      _isLoading = false;
      _errorMessage = null;
      notifyListeners();
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Récupère le détail d'un restaurant
  Future<Restaurant> fetchRestaurantDetail(int restaurantId) async {
    try {
      return await _restaurantsApi.getRestaurantDetail(restaurantId);
    } catch (e) {
      throw e.toString();
    }
  }

  /// Réserver une table
  Future<RestaurantReservation> reserveTable({
    required int restaurantId,
    required DateTime date,
    required String time,
    required int guests,
    String? specialRequests,
    String? clientCode,
  }) async {
    try {
      final reservation = await _restaurantsApi.reserveTable(
        restaurantId: restaurantId,
        date: date,
        time: time,
        guests: guests,
        specialRequests: specialRequests,
        clientCode: clientCode,
      );

      // Rafraîchir les réservations
      await fetchMyReservations(period: _selectedReservationsPeriod);

      return reservation;
    } catch (e) {
      throw e.toString();
    }
  }

  /// Récupère les réservations de l'utilisateur
  Future<void> fetchMyReservations({
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

      final result = await _restaurantsApi.getMyReservations(
        period: effectivePeriod,
        page: _currentReservationsPage,
      );

      final newReservations =
          result['reservations'] as List<RestaurantReservation>? ?? [];
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
      debugPrint('Error fetching reservations: $e');
      _errorMessage = e.toString();
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> loadMoreReservations() async {
    await fetchMyReservations(loadMore: true);
  }

  /// Annuler une réservation
  Future<void> cancelReservation({
    required int reservationId,
    required String reason,
  }) async {
    try {
      await _restaurantsApi.cancelReservation(
        reservationId: reservationId,
        reason: reason,
      );
      // Rafraîchir la liste
      await fetchMyReservations(period: _selectedReservationsPeriod);
    } catch (e) {
      throw e.toString();
    }
  }

  Future<void> updateReservationStatus({
    required int reservationId,
    required String action,
    String? reason,
  }) async {
    try {
      await _restaurantsApi.updateReservationStatus(
        reservationId: reservationId,
        action: action,
        reason: reason,
      );
      await fetchMyReservations(period: _selectedReservationsPeriod);
    } catch (e) {
      throw e.toString();
    }
  }

  /// Rafraîchir la liste
  Future<void> refreshRestaurants() async {
    await fetchRestaurants(type: _selectedType);
  }
}
