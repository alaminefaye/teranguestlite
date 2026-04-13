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

      final endpoint =
          ApiConfig.vitrineMode ? ApiConfig.vitrineSpaServices : ApiConfig.spaServices;
      final response =
          await _apiService.get(endpoint, queryParameters: queryParams);

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
      final endpoint =
          ApiConfig.vitrineMode ? ApiConfig.vitrineSpaServices : ApiConfig.spaServices;
      final response = await _apiService.get(
        '$endpoint/$serviceId',
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
    if (ApiConfig.vitrineMode) {
      throw Exception('Fonction désactivée en mode vitrine.');
    }
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

  /// Récupère les réservations spa de l'utilisateur (avec pagination/filtre période)
  Future<Map<String, dynamic>> getMySpaReservations({
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
        ApiConfig.mySpaReservations,
        queryParameters: queryParams,
      );

      final data = response.data as Map<String, dynamic>;
      final reservationsJson = data['data'] as List? ?? [];
      final meta = data['meta'] as Map<String, dynamic>? ?? {};

      final reservations = reservationsJson
          .map((json) => SpaReservation.fromJson(json as Map<String, dynamic>))
          .toList();

      return {'reservations': reservations, 'meta': meta};
    } on DioException catch (e) {
      debugPrint('❌ API Error: $e');
      rethrow;
    }
  }

  /// Annuler une réservation spa (si > 24h avant)
  Future<void> cancelSpaReservation(int reservationId, {String? reason}) async {
    if (ApiConfig.vitrineMode) {
      throw Exception('Fonction désactivée en mode vitrine.');
    }
    try {
      final payload = <String, dynamic>{};
      if (reason != null && reason.trim().isNotEmpty) {
        payload['reason'] = reason.trim();
      }
      await _apiService.post(
        '${ApiConfig.mySpaReservations}/$reservationId/cancel',
        data: payload.isEmpty ? null : payload,
      );
    } on DioException catch (e) {
      debugPrint('❌ API Error: $e');
      rethrow;
    }
  }

  Future<SpaReservation> acceptRescheduledSpaReservation(
    int reservationId,
  ) async {
    if (ApiConfig.vitrineMode) {
      throw Exception('Fonction désactivée en mode vitrine.');
    }
    try {
      final response = await _apiService.post(
        '${ApiConfig.mySpaReservations}/$reservationId/accept-reschedule',
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

  Future<SpaReservation> updateSpaReservationStatus({
    required int reservationId,
    required String action,
    DateTime? date,
    String? time,
    String? reason,
  }) async {
    if (ApiConfig.vitrineMode) {
      throw Exception('Fonction désactivée en mode vitrine.');
    }
    try {
      final data = <String, dynamic>{'action': action};

      if (date != null) {
        data['date'] = date.toIso8601String().split('T')[0];
      }

      if (time != null && time.isNotEmpty) {
        data['time'] = time;
      }

      if (reason != null && reason.trim().isNotEmpty) {
        data['reason'] = reason.trim();
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
