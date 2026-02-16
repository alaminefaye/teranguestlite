import 'package:flutter/foundation.dart';
import 'package:dio/dio.dart';
import '../config/api_config.dart';
import 'api_service.dart';

class AdminSummary {
  final int ordersPending;
  final int ordersInProgress;
  final int ordersDelivered;
  final int restaurantPending;
  final int restaurantToday;
  final int spaPending;
  final int spaToday;
  final int excursionsPending;
  final int excursionsToday;
  final int laundryPending;
  final int laundryInProgress;
  final int laundryDelivered;
  final int palacePending;
  final int palaceInProgress;
  final int palaceCompleted;
  final int emergencyOpen;

  const AdminSummary({
    required this.ordersPending,
    required this.ordersInProgress,
    required this.ordersDelivered,
    required this.restaurantPending,
    required this.restaurantToday,
    required this.spaPending,
    required this.spaToday,
    required this.excursionsPending,
    required this.excursionsToday,
    required this.laundryPending,
    required this.laundryInProgress,
    required this.laundryDelivered,
    required this.palacePending,
    required this.palaceInProgress,
    required this.palaceCompleted,
    required this.emergencyOpen,
  });

  factory AdminSummary.fromJson(Map<String, dynamic> json) {
    final orders = (json['orders'] as Map?) ?? {};
    final restaurants = (json['restaurants'] as Map?) ?? {};
    final spa = (json['spa'] as Map?) ?? {};
    final excursions = (json['excursions'] as Map?) ?? {};
    final laundry = (json['laundry'] as Map?) ?? {};
    final palace = (json['palace'] as Map?) ?? {};
    final emergency = (json['emergency'] as Map?) ?? {};

    int _int(Map data, String key) {
      final value = data[key];
      if (value is int) return value;
      if (value is num) return value.toInt();
      return 0;
    }

    return AdminSummary(
      ordersPending: _int(orders, 'pending'),
      ordersInProgress: _int(orders, 'in_progress'),
      ordersDelivered: _int(orders, 'delivered'),
      restaurantPending: _int(restaurants, 'pending'),
      restaurantToday: _int(restaurants, 'today'),
      spaPending: _int(spa, 'pending'),
      spaToday: _int(spa, 'today'),
      excursionsPending: _int(excursions, 'pending'),
      excursionsToday: _int(excursions, 'today'),
      laundryPending: _int(laundry, 'pending'),
      laundryInProgress: _int(laundry, 'in_progress'),
      laundryDelivered: _int(laundry, 'delivered'),
      palacePending: _int(palace, 'pending'),
      palaceInProgress: _int(palace, 'in_progress'),
      palaceCompleted: _int(palace, 'completed'),
      emergencyOpen: _int(emergency, 'open'),
    );
  }
}

class AdminApi {
  final ApiService _apiService = ApiService();

  Future<AdminSummary> getSummary() async {
    try {
      final response = await _apiService.get(ApiConfig.adminSummary);
      return AdminSummary.fromJson(
        response.data['data'] as Map<String, dynamic>,
      );
    } on DioException catch (e) {
      debugPrint('❌ API Error (admin summary): $e');
      rethrow;
    }
  }
}
