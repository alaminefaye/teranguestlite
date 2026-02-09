import 'package:flutter/foundation.dart';
import 'package:dio/dio.dart';
import '../config/api_config.dart';
import '../models/restaurant.dart';
import 'api_service.dart';

class RestaurantsApi {
  final ApiService _apiService = ApiService();

  /// Récupère la liste des restaurants
  Future<List<Restaurant>> getRestaurants({
    String? type,
    int page = 1,
  }) async {
    try {
      final queryParams = <String, dynamic>{
        'page': page,
      };

      if (type != null && type.isNotEmpty) {
        queryParams['type'] = type;
      }

      final response = await _apiService.get(
        ApiConfig.restaurants,
        queryParameters: queryParams,
      );

      return (response.data['data'] as List)
          .map((json) => Restaurant.fromJson(json as Map<String, dynamic>))
          .toList();
    } on DioException catch (e) {
      debugPrint('❌ API Error: $e');
      rethrow;
    }
  }

  /// Récupère le détail d'un restaurant
  Future<Restaurant> getRestaurantDetail(int restaurantId) async {
    try {
      final response = await _apiService.get(
        '${ApiConfig.restaurants}/$restaurantId',
      );

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
  }) async {
    try {
      final response = await _apiService.post(
        '${ApiConfig.restaurants}/$restaurantId/reserve',
        data: {
          'date': date.toIso8601String().split('T')[0],
          'time': time,
          'guests': guests,
          'special_requests': specialRequests,
        },
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

  /// Récupère les réservations de l'utilisateur
  Future<List<RestaurantReservation>> getMyReservations() async {
    try {
      final response = await _apiService.get(
        ApiConfig.myRestaurantReservations,
      );

      return (response.data['data'] as List)
          .map((json) => RestaurantReservation.fromJson(json as Map<String, dynamic>))
          .toList();
    } on DioException catch (e) {
      debugPrint('❌ API Error: $e');
      rethrow;
    }
  }

  /// Annuler une réservation (si > 24h avant)
  Future<void> cancelReservation(int reservationId) async {
    try {
      await _apiService.post(
        '${ApiConfig.myRestaurantReservations}/$reservationId/cancel',
      );
    } on DioException catch (e) {
      debugPrint('❌ API Error: $e');
      rethrow;
    }
  }
}
