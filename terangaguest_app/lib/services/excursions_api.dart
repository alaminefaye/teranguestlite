import 'package:flutter/foundation.dart';
import 'package:dio/dio.dart';
import '../config/api_config.dart';
import '../models/excursion.dart';
import 'api_service.dart';

class ExcursionsApi {
  final ApiService _apiService = ApiService();

  /// Récupère la liste des excursions
  Future<List<Excursion>> getExcursions({int page = 1}) async {
    try {
      final response = await _apiService.get(
        ApiConfig.excursions,
        queryParameters: {'page': page},
      );

      return (response.data['data'] as List)
          .map((json) => Excursion.fromJson(json as Map<String, dynamic>))
          .toList();
    } on DioException catch (e) {
      debugPrint('❌ API Error: $e');
      rethrow;
    }
  }

  /// Récupère le détail d'une excursion
  Future<Excursion> getExcursionDetail(int excursionId) async {
    try {
      final response = await _apiService.get(
        '${ApiConfig.excursions}/$excursionId',
      );

      return Excursion.fromJson(response.data['data'] as Map<String, dynamic>);
    } on DioException catch (e) {
      debugPrint('❌ API Error: $e');
      rethrow;
    }
  }

  /// Réserver une excursion
  Future<ExcursionBooking> bookExcursion({
    required int excursionId,
    required DateTime date,
    required int adultsCount,
    required int childrenCount,
    String? specialRequests,
  }) async {
    try {
      final response = await _apiService.post(
        '${ApiConfig.excursions}/$excursionId/book',
        data: {
          'date': date.toIso8601String().split('T')[0],
          'adults_count': adultsCount,
          'children_count': childrenCount,
          'special_requests': specialRequests,
        },
      );

      return ExcursionBooking.fromJson(
        response.data['data'] as Map<String, dynamic>,
      );
    } on DioException catch (e) {
      debugPrint('❌ API Error: $e');
      rethrow;
    }
  }

  /// Récupère les bookings de l'utilisateur
  Future<List<ExcursionBooking>> getMyExcursionBookings() async {
    try {
      final response = await _apiService.get(
        ApiConfig.myExcursionBookings,
      );

      return (response.data['data'] as List)
          .map((json) => ExcursionBooking.fromJson(json as Map<String, dynamic>))
          .toList();
    } on DioException catch (e) {
      debugPrint('❌ API Error: $e');
      rethrow;
    }
  }

  /// Annuler un booking
  Future<void> cancelExcursionBooking(int bookingId) async {
    try {
      await _apiService.post(
        '/excursion-bookings/$bookingId/cancel',
      );
    } on DioException catch (e) {
      debugPrint('❌ API Error: $e');
      rethrow;
    }
  }
}
