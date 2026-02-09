import 'package:flutter/foundation.dart';
import '../models/excursion.dart';
import '../services/excursions_api.dart';

class ExcursionsProvider with ChangeNotifier {
  final ExcursionsApi _excursionsApi = ExcursionsApi();

  List<Excursion> _excursions = [];
  List<ExcursionBooking> _bookings = [];
  bool _isLoading = false;
  String? _errorMessage;

  List<Excursion> get excursions => _excursions;
  List<ExcursionBooking> get bookings => _bookings;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  /// Récupère les excursions
  Future<void> fetchExcursions() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      _excursions = await _excursionsApi.getExcursions();
      _isLoading = false;
      _errorMessage = null;
      notifyListeners();
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Récupère le détail d'une excursion
  Future<Excursion> fetchExcursionDetail(int excursionId) async {
    try {
      return await _excursionsApi.getExcursionDetail(excursionId);
    } catch (e) {
      throw e.toString();
    }
  }

  /// Réserver une excursion
  Future<ExcursionBooking> bookExcursion({
    required int excursionId,
    required DateTime date,
    required int adultsCount,
    required int childrenCount,
    String? specialRequests,
    String? clientCode,
  }) async {
    try {
      final booking = await _excursionsApi.bookExcursion(
        excursionId: excursionId,
        date: date,
        adultsCount: adultsCount,
        childrenCount: childrenCount,
        specialRequests: specialRequests,
        clientCode: clientCode,
      );

      // Rafraîchir les bookings
      await fetchMyExcursionBookings();

      return booking;
    } catch (e) {
      throw e.toString();
    }
  }

  /// Récupère les bookings de l'utilisateur
  Future<void> fetchMyExcursionBookings() async {
    try {
      _bookings = await _excursionsApi.getMyExcursionBookings();
      notifyListeners();
    } catch (e) {
      debugPrint('Error fetching excursion bookings: $e');
    }
  }

  /// Annuler un booking
  Future<void> cancelExcursionBooking(int bookingId) async {
    try {
      await _excursionsApi.cancelExcursionBooking(bookingId);
      // Rafraîchir la liste
      await fetchMyExcursionBookings();
    } catch (e) {
      throw e.toString();
    }
  }

  /// Rafraîchir la liste
  Future<void> refreshExcursions() async {
    await fetchExcursions();
  }
}
