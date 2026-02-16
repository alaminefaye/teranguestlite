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
      final response = await _apiService.get(ApiConfig.palaceServices);

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
    try {
      final description = (details?.trim() ?? '').isEmpty
          ? null
          : details!.trim();
      final requestedFor = scheduledTime != null
          ? '${scheduledTime.year.toString().padLeft(4, '0')}-${scheduledTime.month.toString().padLeft(2, '0')}-${scheduledTime.day.toString().padLeft(2, '0')} ${scheduledTime.hour.toString().padLeft(2, '0')}:${scheduledTime.minute.toString().padLeft(2, '0')}'
          : null;

      final data = <String, dynamic>{
        if (clientCode != null && clientCode.trim().isNotEmpty)
          'client_code': clientCode.trim(),
        if (description != null && description.isNotEmpty)
          'description': description,
        if (requestedFor != null) 'requested_for': requestedFor,
        if (metadata != null && metadata.isNotEmpty) 'metadata': metadata,
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

  /// Récupère les demandes de l'utilisateur
  Future<List<PalaceRequest>> getMyPalaceRequests() async {
    try {
      final response = await _apiService.get(ApiConfig.myPalaceRequests);

      return (response.data['data'] as List)
          .map((json) => PalaceRequest.fromJson(json as Map<String, dynamic>))
          .toList();
    } on DioException catch (e) {
      debugPrint('❌ API Error: $e');
      rethrow;
    }
  }

  Future<List<PalaceRequest>> getEmergencyPalaceRequests() async {
    try {
      final response = await _apiService.get(
        '${ApiConfig.myPalaceRequests}?emergency=1',
      );

      return (response.data['data'] as List)
          .map((json) => PalaceRequest.fromJson(json as Map<String, dynamic>))
          .toList();
    } on DioException catch (e) {
      debugPrint('❌ API Error: $e');
      rethrow;
    }
  }

  /// Annuler une demande
  Future<void> cancelPalaceRequest(int requestId) async {
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
  }) async {
    try {
      final response = await _apiService.post(
        '/palace-requests/$requestId/status',
        data: {'action': action},
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
