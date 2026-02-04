import 'package:flutter/foundation.dart';
import 'package:dio/dio.dart';
import '../config/api_config.dart';
import '../models/spa.dart';
import 'api_service.dart';

class SpaApi {
  final ApiService _apiService = ApiService();

  /// Récupère la liste des services spa
  Future<List<SpaService>> getSpaServices({
    String? category,
    int page = 1,
  }) async {
    try {
      final queryParams = <String, dynamic>{
        'page': page,
      };

      if (category != null && category.isNotEmpty) {
        queryParams['category'] = category;
      }

      final response = await _apiService.get(
        ApiConfig.spaServices,
        queryParameters: queryParams,
      );

      return (response.data['data'] as List)
          .map((json) => SpaService.fromJson(json as Map<String, dynamic>))
          .toList();
    } on DioException catch (e) {
      debugPrint('❌ API Error: $e');
      rethrow;
    }
  }

  /// Récupère le détail d'un service spa
  Future<SpaService> getSpaServiceDetail(int serviceId) async {
    try {
      final response = await _apiService.get(
        '${ApiConfig.spaServices}/$serviceId',
      );

      return SpaService.fromJson(response.data['data'] as Map<String, dynamic>);
    } on DioException catch (e) {
      debugPrint('❌ API Error: $e');
      rethrow;
    }
  }

  /// Réserver un service spa
  Future<SpaReservation> reserveSpaService({
    required int serviceId,
    required DateTime date,
    required String time,
    String? specialRequests,
  }) async {
    try {
      final response = await _apiService.post(
        '${ApiConfig.spaServices}/$serviceId/reserve',
        data: {
          'date': date.toIso8601String().split('T')[0],
          'time': time,
          'special_requests': specialRequests,
        },
      );

      return SpaReservation.fromJson(
        response.data['data'] as Map<String, dynamic>,
      );
    } on DioException catch (e) {
      debugPrint('❌ API Error: $e');
      rethrow;
    }
  }

  /// Récupère les réservations spa de l'utilisateur
  Future<List<SpaReservation>> getMySpaReservations() async {
    try {
      final response = await _apiService.get(
        ApiConfig.mySpaReservations,
      );

      return (response.data['data'] as List)
          .map((json) => SpaReservation.fromJson(json as Map<String, dynamic>))
          .toList();
    } on DioException catch (e) {
      debugPrint('❌ API Error: $e');
      rethrow;
    }
  }

  /// Annuler une réservation spa (si > 24h avant)
  Future<void> cancelSpaReservation(int reservationId) async {
    try {
      await _apiService.post(
        '${ApiConfig.mySpaReservations}/$reservationId/cancel',
      );
    } on DioException catch (e) {
      debugPrint('❌ API Error: $e');
      rethrow;
    }
  }
}
