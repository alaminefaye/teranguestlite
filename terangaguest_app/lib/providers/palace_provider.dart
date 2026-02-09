import 'package:flutter/foundation.dart';
import '../models/palace.dart';
import '../services/palace_api.dart';

class PalaceProvider with ChangeNotifier {
  final PalaceApi _palaceApi = PalaceApi();

  List<PalaceService> _services = [];
  List<PalaceRequest> _requests = [];
  bool _isLoading = false;
  String? _errorMessage;

  List<PalaceService> get services => _services;
  List<PalaceRequest> get requests => _requests;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

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
      await fetchMyPalaceRequests();

      return request;
    } catch (e) {
      throw e.toString();
    }
  }

  /// Récupère les demandes de l'utilisateur
  Future<void> fetchMyPalaceRequests() async {
    try {
      _requests = await _palaceApi.getMyPalaceRequests();
      notifyListeners();
    } catch (e) {
      debugPrint('Error fetching palace requests: $e');
    }
  }

  /// Annuler une demande
  Future<void> cancelPalaceRequest(int requestId) async {
    try {
      await _palaceApi.cancelPalaceRequest(requestId);
      await fetchMyPalaceRequests();
    } catch (e) {
      throw e.toString();
    }
  }

  /// Rafraîchir
  Future<void> refreshPalaceServices() async {
    await fetchPalaceServices();
  }
}
