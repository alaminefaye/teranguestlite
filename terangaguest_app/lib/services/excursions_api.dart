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
    String? clientCode,
  }) async {
    try {
      final data = <String, dynamic>{
        'date': date.toIso8601String().split('T')[0],
        'adults': adultsCount,
        'children': childrenCount,
        if (specialRequests != null && specialRequests.isNotEmpty) 'special_requests': specialRequests,
        if (clientCode != null && clientCode.trim().isNotEmpty) 'client_code': clientCode.trim(),
      };
      final response = await _apiService.post(
        '${ApiConfig.excursions}/$excursionId/book',
        data: data,
      );

      return ExcursionBooking.fromJson(
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
      return 'Vérifiez la date et le nombre de participants.';
    }
    return e.message ?? 'Erreur lors de la réservation.';
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
