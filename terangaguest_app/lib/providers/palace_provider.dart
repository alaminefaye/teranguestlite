import 'package:flutter/foundation.dart';
import '../models/palace.dart';
import '../services/palace_api.dart';

class PalaceProvider with ChangeNotifier {
  final PalaceApi _palaceApi = PalaceApi();

  List<PalaceService> _services = [];
  List<PalaceRequest> _requests = [];
  List<PalaceRequest> _emergencyRequests = [];
  bool _isLoading = false;
  bool _hasMoreRequestPages = true;
  bool _hasMoreEmergencyPages = true;
  String? _errorMessage;
  String? _selectedRequestsPeriod;
  String? _selectedEmergencyPeriod;
  int _currentRequestsPage = 1;
  int _currentEmergencyPage = 1;

  List<PalaceService> get services => _services;
  List<PalaceRequest> get requests => _requests;
  List<PalaceRequest> get emergencyRequests => _emergencyRequests;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;
  bool get hasMoreRequestPages => _hasMoreRequestPages;
  bool get hasMoreEmergencyPages => _hasMoreEmergencyPages;
  String? get selectedRequestsPeriod => _selectedRequestsPeriod;
  String? get selectedEmergencyPeriod => _selectedEmergencyPeriod;

  /// Récupère les services palace
  Future<void> fetchPalaceServices() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      _services = await _palaceApi.getPalaceServices();
      _isLoading = false;
      _errorMessage = null;
      notifyListeners();
    } catch (e) {
      _errorMessage = e.toString();
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Créer une demande de service palace
  Future<PalaceRequest> createPalaceRequest({
    required int serviceId,
    String? details,
    DateTime? scheduledTime,
    Map<String, dynamic>? metadata,
    String? clientCode,
  }) async {
    try {
      final request = await _palaceApi.createPalaceRequest(
        serviceId: serviceId,
        details: details,
        scheduledTime: scheduledTime,
        metadata: metadata,
        clientCode: clientCode,
      );

      // Rafraîchir les demandes
      await fetchMyPalaceRequests(period: _selectedRequestsPeriod);

      return request;
    } catch (e) {
      throw e.toString();
    }
  }

  /// Récupère les demandes de l'utilisateur
  Future<void> fetchMyPalaceRequests({
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
      final effectivePeriod =
          loadMore ? _selectedRequestsPeriod : (period ?? _selectedRequestsPeriod);

      final result = await _palaceApi.getMyPalaceRequests(
        period: effectivePeriod,
        page: _currentRequestsPage,
      );

      final newRequests =
          result['requests'] as List<PalaceRequest>? ?? [];
      final meta = result['meta'] as Map<String, dynamic>? ?? {};

      if (loadMore) {
        _requests.addAll(newRequests);
      } else {
        _requests = newRequests;
      }

      final currentPage = meta['current_page'] is int
          ? meta['current_page'] as int
          : _currentRequestsPage;
      final lastPage = meta['last_page'] is int ? meta['last_page'] as int : currentPage;

      _hasMoreRequestPages = currentPage < lastPage;
      _currentRequestsPage = currentPage + 1;

      _isLoading = false;
      _errorMessage = null;
      notifyListeners();
    } catch (e) {
      debugPrint('Error fetching palace requests: $e');
      _errorMessage = e.toString();
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> fetchEmergencyPalaceRequests({
    String? period,
    bool loadMore = false,
  }) async {
    if (!loadMore) {
      _isLoading = true;
      _errorMessage = null;
      _currentEmergencyPage = 1;
      _selectedEmergencyPeriod = period;
      _hasMoreEmergencyPages = true;
      notifyListeners();
    } else {
      if (!_hasMoreEmergencyPages || _isLoading) {
        return;
      }
      _isLoading = true;
      notifyListeners();
    }

    try {
      final effectivePeriod =
          loadMore ? _selectedEmergencyPeriod : (period ?? _selectedEmergencyPeriod);

      final result = await _palaceApi.getEmergencyPalaceRequests(
        period: effectivePeriod,
        page: _currentEmergencyPage,
      );

      final newRequests =
          result['requests'] as List<PalaceRequest>? ?? [];
      final meta = result['meta'] as Map<String, dynamic>? ?? {};

      if (loadMore) {
        _emergencyRequests.addAll(newRequests);
      } else {
        _emergencyRequests = newRequests;
      }

      final currentPage = meta['current_page'] is int
          ? meta['current_page'] as int
          : _currentEmergencyPage;
      final lastPage = meta['last_page'] is int ? meta['last_page'] as int : currentPage;

      _hasMoreEmergencyPages = currentPage < lastPage;
      _currentEmergencyPage = currentPage + 1;

      _isLoading = false;
      _errorMessage = null;
      notifyListeners();
    } catch (e) {
      debugPrint('Error fetching emergency palace requests: $e');
      _errorMessage = e.toString();
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> loadMorePalaceRequests() async {
    await fetchMyPalaceRequests(loadMore: true);
  }

  Future<void> loadMoreEmergencyPalaceRequests() async {
    await fetchEmergencyPalaceRequests(loadMore: true);
  }

  /// Annuler une demande
  Future<void> cancelPalaceRequest(int requestId) async {
    try {
      await _palaceApi.cancelPalaceRequest(requestId);
      await fetchMyPalaceRequests(period: _selectedRequestsPeriod);
    } catch (e) {
      throw e.toString();
    }
  }

  /// Mettre à jour le statut d'une demande (staff/admin).
  Future<void> updatePalaceRequestStatus({
    required int requestId,
    required String action,
    String? reason,
  }) async {
    try {
      await _palaceApi.updatePalaceRequestStatus(
        requestId: requestId,
        action: action,
        reason: reason,
      );
      await fetchMyPalaceRequests(period: _selectedRequestsPeriod);
    } catch (e) {
      throw e.toString();
    }
  }

  /// Rafraîchir
  Future<void> refreshPalaceServices() async {
    await fetchPalaceServices();
  }
}
