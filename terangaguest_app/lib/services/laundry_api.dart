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
  /// Backend attend: items[].laundry_service_id, items[].quantity, special_instructions, pickup_time (optionnel)
  Future<LaundryRequest> createLaundryRequest({
    required List<Map<String, int>> items,
    String? specialInstructions,
    String? clientCode,
  }) async {
    try {
      final payloadItems = items
          .map(
            (e) => {
              'laundry_service_id': e['service_id'] ?? e['laundry_service_id']!,
              'quantity': e['quantity']!,
            },
          )
          .toList();

      final data = <String, dynamic>{
        'items': payloadItems,
        if (specialInstructions != null && specialInstructions.isNotEmpty)
          'special_instructions': specialInstructions,
        if (clientCode != null && clientCode.trim().isNotEmpty)
          'client_code': clientCode.trim(),
      };
      final response = await _apiService.post(
        ApiConfig.laundryRequest,
        data: data,
      );

      return LaundryRequest.fromJson(
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
      return 'Vérifiez les articles et quantités.';
    }
    return e.message ?? 'Erreur lors de la demande de blanchisserie.';
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

  Future<LaundryRequest> updateLaundryRequestStatus({
    required int requestId,
    required String action,
  }) async {
    try {
      final response = await _apiService.post(
        '${ApiConfig.laundryRequests}/$requestId/status',
        data: {'action': action},
      );

      return LaundryRequest.fromJson(
        response.data['data'] as Map<String, dynamic>,
      );
    } on DioException catch (e) {
      debugPrint('❌ API Error: $e');
      final message = _messageFromDio(e);
      throw Exception(message);
    }
  }

  /// Annuler une demande
  Future<void> cancelLaundryRequest(int requestId) async {
    try {
      await _apiService.post(
        '${ApiConfig.laundryRequests}/$requestId/status',
        data: {'action': 'cancel'},
      );
    } on DioException catch (e) {
      debugPrint('❌ API Error: $e');
      rethrow;
    }
  }
}
