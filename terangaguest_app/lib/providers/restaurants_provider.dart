import 'package:flutter/foundation.dart';
import '../models/restaurant.dart';
import '../services/restaurants_api.dart';

class RestaurantsProvider with ChangeNotifier {
  final RestaurantsApi _restaurantsApi = RestaurantsApi();

  List<Restaurant> _restaurants = [];
  List<RestaurantReservation> _reservations = [];
  bool _isLoading = false;
  String? _errorMessage;
  String? _selectedType;

  List<Restaurant> get restaurants => _restaurants;
  List<RestaurantReservation> get reservations => _reservations;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;
  String? get selectedType => _selectedType;

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
      await fetchMyReservations();

      return reservation;
    } catch (e) {
      throw e.toString();
    }
  }

  /// Récupère les réservations de l'utilisateur
  Future<void> fetchMyReservations() async {
    try {
      _reservations = await _restaurantsApi.getMyReservations();
      notifyListeners();
    } catch (e) {
      debugPrint('Error fetching reservations: $e');
    }
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
      await fetchMyReservations();
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
      await fetchMyReservations();
    } catch (e) {
      throw e.toString();
    }
  }

  /// Rafraîchir la liste
  Future<void> refreshRestaurants() async {
    await fetchRestaurants(type: _selectedType);
  }
}
