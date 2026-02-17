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
      final queryParams = <String, dynamic>{'page': page};

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
    String? clientCode,
  }) async {
    try {
      final data = <String, dynamic>{
        'date': date.toIso8601String().split('T')[0],
        'time': time,
        if (specialRequests != null && specialRequests.isNotEmpty)
          'special_requests': specialRequests,
        if (clientCode != null && clientCode.trim().isNotEmpty)
          'client_code': clientCode.trim(),
      };
      final response = await _apiService.post(
        '${ApiConfig.spaServices}/$serviceId/reserve',
        data: data,
      );

      return SpaReservation.fromJson(
        response.data['data'] as Map<String, dynamic>,
      );
    } on DioException catch (e) {
      debugPrint('❌ API Error: $e');
      final message = _messageFromDioException(e);
      throw Exception(message);
    }
  }

  static String _messageFromDioException(DioException e) {
    final data = e.response?.data;
    if (data is Map && data['message'] != null) {
      final msg = data['message'];
      if (msg is String && msg.isNotEmpty) return msg;
    }
    if (e.response?.statusCode == 400) {
      return 'Requête invalide. Vérifiez la date et l\'heure.';
    }
    if (e.response?.statusCode == 403) {
      return (data is Map &&
              data['message'] != null &&
              (data['message'] as String).isNotEmpty)
          ? (data['message'] as String)
          : 'Code client invalide ou séjour non actif. Vérifiez votre code.';
    }
    if (e.response?.statusCode == 422) {
      final errors = data is Map ? data['errors'] : null;
      if (errors is Map && errors.isNotEmpty) {
        final first = errors.values.first;
        if (first is List && first.isNotEmpty) return first.first as String;
      }
      if (data is Map && data['message'] != null) {
        return data['message'] as String;
      }
      return 'Vérifiez les informations saisies (date, heure).';
    }
    return e.message ?? 'Erreur lors de la réservation.';
  }

  /// Récupère les réservations spa de l'utilisateur
  Future<List<SpaReservation>> getMySpaReservations() async {
    try {
      final response = await _apiService.get(ApiConfig.mySpaReservations);

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

  Future<SpaReservation> updateSpaReservationStatus({
    required int reservationId,
    required String action,
    DateTime? date,
    String? time,
  }) async {
    try {
      final data = <String, dynamic>{'action': action};

      if (date != null) {
        data['date'] = date.toIso8601String().split('T')[0];
      }

      if (time != null && time.isNotEmpty) {
        data['time'] = time;
      }

      final response = await _apiService.post(
        '${ApiConfig.spaReservations}/$reservationId/status',
        data: data,
      );

      return SpaReservation.fromJson(
        response.data['data'] as Map<String, dynamic>,
      );
    } on DioException catch (e) {
      debugPrint('❌ API Error: $e');
      final message = _messageFromDioException(e);
      throw Exception(message);
    }
  }
}
