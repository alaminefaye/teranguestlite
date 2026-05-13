import 'package:flutter/foundation.dart';
import 'package:dio/dio.dart';
import '../config/api_config.dart';
import '../models/restaurant.dart';
import 'api_service.dart';

class RestaurantsApi {
  final ApiService _apiService = ApiService();

  /// Récupère la liste des restaurants.
  ///
  /// Si [allowedTypes] est renseigné (boîte Restaurants ou Bars seule), les
  /// résultats sont toujours limités à ces types — y compris quand [type] est
  /// vide (« Tous » dans cette boîte).
  Future<List<Restaurant>> getRestaurants({
    String? type,
    List<String>? allowedTypes,
    int page = 1,
  }) async {
    try {
      final queryParams = <String, dynamic>{'page': page};

      final allowed = allowedTypes == null || allowedTypes.isEmpty
          ? null
          : allowedTypes.toSet();

      String? apiType = (type != null && type.isNotEmpty) ? type : null;
      if (apiType == null && allowed != null && allowed.length == 1) {
        apiType = allowed.first;
      }

      if (apiType != null && apiType.isNotEmpty) {
        queryParams['type'] = apiType;
      }

      final endpoint = ApiConfig.vitrineMode
          ? ApiConfig.vitrineRestaurants
          : ApiConfig.restaurants;
      final response = await _apiService.get(
        endpoint,
        queryParameters: queryParams,
      );

      var list = (response.data['data'] as List)
          .map((json) => Restaurant.fromJson(json as Map<String, dynamic>))
          .toList();

      if (allowed != null) {
        list = list.where((r) {
          final raw = r.type;
          final t = raw is String ? raw : raw?.toString();
          return t != null && allowed.contains(t);
        }).toList();
      }

      return list;
    } on DioException catch (e) {
      debugPrint('❌ API Error: $e');
      rethrow;
    }
  }

  /// Récupère le détail d'un restaurant
  Future<Restaurant> getRestaurantDetail(int restaurantId) async {
    try {
      final endpoint = ApiConfig.vitrineMode
          ? ApiConfig.vitrineRestaurants
          : ApiConfig.restaurants;
      final response = await _apiService.get('$endpoint/$restaurantId');

      return Restaurant.fromJson(response.data['data'] as Map<String, dynamic>);
    } on DioException catch (e) {
      debugPrint('❌ API Error: $e');
      rethrow;
    }
  }

  /// Réserver une table
  Future<RestaurantReservation> reserveTable({
    required int restaurantId,
    required DateTime date,
    required String time,
    required int guests,
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
        'guests': guests,
        if (specialRequests != null && specialRequests.isNotEmpty)
          'special_requests': specialRequests,
        if (clientCode != null && clientCode.trim().isNotEmpty)
          'client_code': clientCode.trim(),
      };
      final response = await _apiService.post(
        '${ApiConfig.restaurants}/$restaurantId/reserve',
        data: data,
      );

      return RestaurantReservation.fromJson(
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
    return e.message ?? 'Erreur lors de la réservation.';
  }

  /// Récupère les réservations de l'utilisateur (avec pagination/filtre période)
  Future<Map<String, dynamic>> getMyReservations({
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
        ApiConfig.myRestaurantReservations,
        queryParameters: queryParams,
      );

      final data = response.data as Map<String, dynamic>;
      final reservationsJson = data['data'] as List? ?? [];
      final meta = data['meta'] as Map<String, dynamic>? ?? {};

      final reservations = reservationsJson
          .map(
            (json) =>
                RestaurantReservation.fromJson(json as Map<String, dynamic>),
          )
          .toList();

      return {'reservations': reservations, 'meta': meta};
    } on DioException catch (e) {
      debugPrint('❌ API Error: $e');
      rethrow;
    }
  }

  Future<RestaurantReservation> updateReservationStatus({
    required int reservationId,
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
        '${ApiConfig.restaurantReservations}/$reservationId/status',
        data: data,
      );

      return RestaurantReservation.fromJson(
        response.data['data'] as Map<String, dynamic>,
      );
    } on DioException catch (e) {
      debugPrint('❌ API Error: $e');
      final message = _messageFromDio(e);
      throw Exception(message);
    }
  }

  /// Annuler une réservation (si > 24h avant)
  Future<void> cancelReservation({
    required int reservationId,
    required String reason,
  }) async {
    if (ApiConfig.vitrineMode) {
      throw Exception('Fonction désactivée en mode vitrine.');
    }
    try {
      await _apiService.post(
        '${ApiConfig.myRestaurantReservations}/$reservationId/cancel',
        data: {'reason': reason},
      );
    } on DioException catch (e) {
      debugPrint('❌ API Error: $e');
      rethrow;
    }
  }
}
