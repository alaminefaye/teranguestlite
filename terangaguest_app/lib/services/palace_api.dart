import 'package:flutter/foundation.dart';
import 'package:dio/dio.dart';
import '../config/api_config.dart';
import '../models/palace.dart';
import 'api_service.dart';

class PalaceApi {
  final ApiService _apiService = ApiService();

  /// Récupère la liste des services palace
  Future<List<PalaceService>> getPalaceServices() async {
    try {
      final endpoint = ApiConfig.vitrineMode
          ? ApiConfig.vitrinePalaceServices
          : ApiConfig.palaceServices;
      final response = await _apiService.get(endpoint);

      return (response.data['data'] as List)
          .map((json) => PalaceService.fromJson(json as Map<String, dynamic>))
          .toList();
    } on DioException catch (e) {
      debugPrint('❌ API Error: $e');
      rethrow;
    }
  }

  /// Créer une demande de service palace.
  /// Backend attend: description (optionnel si metadata véhicule), requested_for, metadata (optionnel).
  Future<PalaceRequest> createPalaceRequest({
    required int serviceId,
    String? details,
    DateTime? scheduledTime,
    Map<String, dynamic>? metadata,
    String? clientCode,
  }) async {
    if (ApiConfig.vitrineMode) {
      throw Exception('Fonction désactivée en mode vitrine.');
    }
    try {
      final description = (details?.trim() ?? '').isEmpty
          ? null
          : details!.trim();
      final requestedFor = scheduledTime == null
          ? null
          : '${scheduledTime.year.toString().padLeft(4, '0')}-${scheduledTime.month.toString().padLeft(2, '0')}-${scheduledTime.day.toString().padLeft(2, '0')} ${scheduledTime.hour.toString().padLeft(2, '0')}:${scheduledTime.minute.toString().padLeft(2, '0')}';

      final data = <String, dynamic>{
        if (clientCode?.trim().isNotEmpty == true)
          'client_code': clientCode!.trim(),
        if (description?.isNotEmpty == true) 'description': description,
        if (requestedFor?.isNotEmpty == true) 'requested_for': requestedFor,
        if (metadata?.isNotEmpty == true) 'metadata': metadata,
      };

      final response = await _apiService.post(
        '/palace-services/$serviceId/request',
        data: data,
      );

      return PalaceRequest.fromJson(
        response.data['data'] as Map<String, dynamic>,
      );
    } on DioException catch (e) {
      debugPrint('❌ API Error: $e');
      final message = _messageFromDio(e);
      throw Exception(message);
    }
  }

  static String _messageFromDio(DioException e) {
    final data = e.response?.data;
    if (data is Map && data['message'] != null) {
      final msg = data['message'];
      if (msg is String && msg.isNotEmpty) return msg;
    }
    if (e.response?.statusCode == 422) {
      final errors = data is Map ? data['errors'] : null;
      if (errors is Map && errors.isNotEmpty) {
        final first = errors.values.first;
        if (first is List && first.isNotEmpty) return first.first as String;
      }
      return 'Vérifiez les informations saisies.';
    }
    return e.message ?? 'Erreur lors de l\'envoi de la demande.';
  }

  /// Récupère les demandes de l'utilisateur (avec pagination/filtre période)
  Future<Map<String, dynamic>> getMyPalaceRequests({
    String? period,
    int page = 1,
    int perPage = 15,
    String? clientCode,
  }) async {
    if (ApiConfig.vitrineMode) {
      throw Exception('Fonction désactivée en mode vitrine.');
    }
    try {
      final queryParams = <String, dynamic>{'page': page, 'per_page': perPage};

      if (period != null && period.isNotEmpty && period != 'all') {
        queryParams['period'] = period;
      }

      if (clientCode != null && clientCode.trim().isNotEmpty) {
        queryParams['client_code'] = clientCode.trim();
      }

      final response = await _apiService.get(
        ApiConfig.myPalaceRequests,
        queryParameters: queryParams,
      );

      final data = response.data as Map<String, dynamic>;
      final requestsJson = data['data'] as List? ?? [];
      final meta = data['meta'] as Map<String, dynamic>? ?? {};

      final requests = requestsJson
          .map((json) => PalaceRequest.fromJson(json as Map<String, dynamic>))
          .toList();

      return {'requests': requests, 'meta': meta};
    } on DioException catch (e) {
      debugPrint('❌ API Error: $e');
      rethrow;
    }
  }

  Future<Map<String, dynamic>> getEmergencyPalaceRequests({
    String? period,
    int page = 1,
    int perPage = 15,
  }) async {
    if (ApiConfig.vitrineMode) {
      throw Exception('Fonction désactivée en mode vitrine.');
    }
    try {
      final queryParams = <String, dynamic>{
        'emergency': 1,
        'page': page,
        'per_page': perPage,
      };

      if (period != null && period.isNotEmpty && period != 'all') {
        queryParams['period'] = period;
      }

      final response = await _apiService.get(
        ApiConfig.myPalaceRequests,
        queryParameters: queryParams,
      );

      final data = response.data as Map<String, dynamic>;
      final requestsJson = data['data'] as List? ?? [];
      final meta = data['meta'] as Map<String, dynamic>? ?? {};

      final requests = requestsJson
          .map((json) => PalaceRequest.fromJson(json as Map<String, dynamic>))
          .toList();

      return {'requests': requests, 'meta': meta};
    } on DioException catch (e) {
      debugPrint('❌ API Error: $e');
      rethrow;
    }
  }

  /// Annuler une demande
  Future<void> cancelPalaceRequest(int requestId) async {
    if (ApiConfig.vitrineMode) {
      throw Exception('Fonction désactivée en mode vitrine.');
    }
    try {
      await _apiService.post('/palace-requests/$requestId/cancel');
    } on DioException catch (e) {
      debugPrint('❌ API Error: $e');
      rethrow;
    }
  }

  /// Mettre à jour le statut d'une demande (staff/admin).
  Future<PalaceRequest> updatePalaceRequestStatus({
    required int requestId,
    required String action,
    String? reason,
  }) async {
    if (ApiConfig.vitrineMode) {
      throw Exception('Fonction désactivée en mode vitrine.');
    }
    try {
      final data = <String, dynamic>{'action': action};
      if (reason != null && reason.trim().isNotEmpty) {
        data['reason'] = reason.trim();
      }
      final response = await _apiService.post(
        '/palace-requests/$requestId/status',
        data: data,
      );

      return PalaceRequest.fromJson(
        response.data['data'] as Map<String, dynamic>,
      );
    } on DioException catch (e) {
      debugPrint('❌ API Error: $e');
      final message = _messageFromDio(e);
      throw Exception(message);
    }
  }
}
