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

  /// Créer une demande de service palace
  Future<PalaceRequest> createPalaceRequest({
    required int serviceId,
    String? details,
    DateTime? scheduledTime,
  }) async {
    try {
      final response = await _apiService.post(
        '/palace-services/$serviceId/request',
        data: {
          'details': details,
          'scheduled_time': scheduledTime?.toIso8601String(),
        },
      );

      return PalaceRequest.fromJson(
          response.data['data'] as Map<String, dynamic>);
    } on DioException catch (e) {
      debugPrint('❌ API Error: $e');
      rethrow;
    }
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

  /// Annuler une demande
  Future<void> cancelPalaceRequest(int requestId) async {
    try {
      await _apiService.post('/palace-requests/$requestId/cancel');
    } on DioException catch (e) {
      debugPrint('❌ API Error: $e');
      rethrow;
    }
  }
}
