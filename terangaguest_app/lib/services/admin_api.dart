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
  final int chatUnreadConversations;
  final int chatOpen;

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
    required this.chatUnreadConversations,
    required this.chatOpen,
  });

  factory AdminSummary.fromJson(Map<String, dynamic> json) {
    final orders = (json['orders'] as Map?) ?? {};
    final restaurants = (json['restaurants'] as Map?) ?? {};
    final spa = (json['spa'] as Map?) ?? {};
    final excursions = (json['excursions'] as Map?) ?? {};
    final laundry = (json['laundry'] as Map?) ?? {};
    final palace = (json['palace'] as Map?) ?? {};
    final emergency = (json['emergency'] as Map?) ?? {};
    final chat = (json['chat'] as Map?) ?? {};

    int toInt(Map data, String key) {
      final value = data[key];
      if (value is int) return value;
      if (value is num) return value.toInt();
      return 0;
    }

    return AdminSummary(
      ordersPending: toInt(orders, 'pending'),
      ordersInProgress: toInt(orders, 'in_progress'),
      ordersDelivered: toInt(orders, 'delivered'),
      restaurantPending: toInt(restaurants, 'pending'),
      restaurantToday: toInt(restaurants, 'today'),
      spaPending: toInt(spa, 'pending'),
      spaToday: toInt(spa, 'today'),
      excursionsPending: toInt(excursions, 'pending'),
      excursionsToday: toInt(excursions, 'today'),
      laundryPending: toInt(laundry, 'pending'),
      laundryInProgress: toInt(laundry, 'in_progress'),
      laundryDelivered: toInt(laundry, 'delivered'),
      palacePending: toInt(palace, 'pending'),
      palaceInProgress: toInt(palace, 'in_progress'),
      palaceCompleted: toInt(palace, 'completed'),
      emergencyOpen: toInt(emergency, 'open'),
      chatUnreadConversations: toInt(chat, 'unread_conversations'),
      chatOpen: toInt(chat, 'open'),
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
