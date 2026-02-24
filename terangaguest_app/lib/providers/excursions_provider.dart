import 'package:flutter/foundation.dart';
import '../models/excursion.dart';
import '../services/excursions_api.dart';

class ExcursionsProvider with ChangeNotifier {
  final ExcursionsApi _excursionsApi = ExcursionsApi();

  List<Excursion> _excursions = [];
  List<ExcursionBooking> _bookings = [];
  bool _isLoading = false;
  bool _hasMoreBookingPages = true;
  String? _errorMessage;
  String? _selectedBookingsPeriod;
  int _currentBookingsPage = 1;

  List<Excursion> get excursions => _excursions;
  List<ExcursionBooking> get bookings => _bookings;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;
  bool get hasMoreBookingPages => _hasMoreBookingPages;
  String? get selectedBookingsPeriod => _selectedBookingsPeriod;

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
      await fetchMyExcursionBookings(period: _selectedBookingsPeriod);

      return booking;
    } catch (e) {
      throw e.toString();
    }
  }

  /// Récupère les bookings de l'utilisateur
  Future<void> fetchMyExcursionBookings({
    String? period,
    bool loadMore = false,
  }) async {
    if (!loadMore) {
      _isLoading = true;
      _errorMessage = null;
      _currentBookingsPage = 1;
      _selectedBookingsPeriod = period;
      _hasMoreBookingPages = true;
      notifyListeners();
    } else {
      if (!_hasMoreBookingPages || _isLoading) {
        return;
      }
      _isLoading = true;
      notifyListeners();
    }

    try {
      final effectivePeriod = loadMore
          ? _selectedBookingsPeriod
          : (period ?? _selectedBookingsPeriod);

      final result = await _excursionsApi.getMyExcursionBookings(
        period: effectivePeriod,
        page: _currentBookingsPage,
      );

      final newBookings = result['bookings'] as List<ExcursionBooking>? ?? [];
      final meta = result['meta'] as Map<String, dynamic>? ?? {};

      if (loadMore) {
        _bookings.addAll(newBookings);
      } else {
        _bookings = newBookings;
      }

      final currentPage = meta['current_page'] is int
          ? meta['current_page'] as int
          : _currentBookingsPage;
      final lastPage = meta['last_page'] is int
          ? meta['last_page'] as int
          : currentPage;

      _hasMoreBookingPages = currentPage < lastPage;
      _currentBookingsPage = currentPage + 1;

      _isLoading = false;
      _errorMessage = null;
      notifyListeners();
    } catch (e) {
      debugPrint('Error fetching excursion bookings: $e');
      _errorMessage = e.toString();
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> loadMoreExcursionBookings() async {
    await fetchMyExcursionBookings(loadMore: true);
  }

  /// Annuler un booking
  Future<void> cancelExcursionBooking(int bookingId, {String? reason}) async {
    try {
      await _excursionsApi.cancelExcursionBooking(bookingId, reason: reason);
      // Rafraîchir la liste
      await fetchMyExcursionBookings(period: _selectedBookingsPeriod);
    } catch (e) {
      throw e.toString();
    }
  }

  Future<void> updateExcursionBookingStatus({
    required int bookingId,
    required String action,
  }) async {
    try {
      await _excursionsApi.updateExcursionBookingStatus(
        bookingId: bookingId,
        action: action,
      );
      await fetchMyExcursionBookings(period: _selectedBookingsPeriod);
    } catch (e) {
      throw e.toString();
    }
  }

  /// Vide les données utilisateur (appelé lors d'un changement de session).
  void clearUserData() {
    _bookings = [];
    _currentBookingsPage = 1;
    _hasMoreBookingPages = true;
    _selectedBookingsPeriod = null;
    _errorMessage = null;
    notifyListeners();
  }

  /// Rafraîchir la liste
  Future<void> refreshExcursions() async {
    await fetchExcursions();
  }
}
