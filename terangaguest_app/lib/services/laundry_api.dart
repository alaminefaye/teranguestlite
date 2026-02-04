import 'package:flutter/foundation.dart';
import 'package:dio/dio.dart';
import '../config/api_config.dart';
import '../models/laundry.dart';
import 'api_service.dart';

class LaundryApi {
  final ApiService _apiService = ApiService();

  /// Récupère la liste des services de blanchisserie
  Future<List<LaundryService>> getLaundryServices() async {
    try {
      final response = await _apiService.get(ApiConfig.laundryServices);

      return (response.data['data'] as List)
          .map((json) => LaundryService.fromJson(json as Map<String, dynamic>))
          .toList();
    } on DioException catch (e) {
      debugPrint('❌ API Error: $e');
      rethrow;
    }
  }

  /// Créer une demande de blanchisserie
  Future<LaundryRequest> createLaundryRequest({
    required List<Map<String, int>> items,
    String? specialInstructions,
  }) async {
    try {
      final response = await _apiService.post(
        ApiConfig.laundryRequest,
        data: {
          'items': items,
          'special_instructions': specialInstructions,
        },
      );

      return LaundryRequest.fromJson(
          response.data['data'] as Map<String, dynamic>);
    } on DioException catch (e) {
      debugPrint('❌ API Error: $e');
      rethrow;
    }
  }

  /// Récupère les demandes de l'utilisateur
  Future<List<LaundryRequest>> getMyLaundryRequests() async {
    try {
      final response = await _apiService.get(ApiConfig.myLaundryRequests);

      return (response.data['data'] as List)
          .map((json) => LaundryRequest.fromJson(json as Map<String, dynamic>))
          .toList();
    } on DioException catch (e) {
      debugPrint('❌ API Error: $e');
      rethrow;
    }
  }

  /// Annuler une demande
  Future<void> cancelLaundryRequest(int requestId) async {
    try {
      await _apiService.post('/laundry-requests/$requestId/cancel');
    } on DioException catch (e) {
      debugPrint('❌ API Error: $e');
      rethrow;
    }
  }
}
