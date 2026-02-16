import 'package:flutter/foundation.dart';
import 'package:dio/dio.dart';
import '../config/api_config.dart';
import '../models/order.dart';
import 'api_service.dart';

class OrdersApi {
  final ApiService _apiService = ApiService();

  /// Récupère la liste des commandes
  Future<Map<String, dynamic>> getOrders({
    String? status,
    int page = 1,
    int perPage = 15,
  }) async {
    try {
      final queryParams = <String, dynamic>{
        'page': page,
        'per_page': perPage,
      };

      if (status != null && status.isNotEmpty) {
        queryParams['status'] = status;
      }

      final response = await _apiService.get(
        ApiConfig.orders,
        queryParameters: queryParams,
      );

      final orders = (response.data['data'] as List)
          .map((json) => Order.fromJson(json as Map<String, dynamic>))
          .toList();

      return {
        'orders': orders,
        'meta': response.data['meta'],
        'links': response.data['links'],
      };
    } on DioException catch (e) {
      debugPrint('❌ API Error: $e');
      rethrow;
    }
  }

  /// Récupère le détail d'une commande
  Future<Order> getOrderDetail(int orderId) async {
    try {
      final response = await _apiService.get(
        '${ApiConfig.orders}/$orderId',
      );

      return Order.fromJson(response.data['data'] as Map<String, dynamic>);
    } on DioException catch (e) {
      debugPrint('❌ API Error: $e');
      rethrow;
    }
  }

  /// Recommander une commande (ajoute les articles au panier)
  Future<void> reorderOrder(int orderId) async {
    try {
      await _apiService.post(
        '${ApiConfig.orders}/$orderId/reorder',
      );
    } on DioException catch (e) {
      debugPrint('❌ API Error: $e');
      rethrow;
    }
  }

  /// Annuler une commande
  Future<void> cancelOrder(int orderId) async {
    try {
      await _apiService.post(
        '${ApiConfig.orders}/$orderId/cancel',
      );
    } on DioException catch (e) {
      debugPrint('❌ API Error: $e');
      rethrow;
    }
  }

  Future<void> updateOrderStatus({
    required int orderId,
    required String action,
  }) async {
    try {
      await _apiService.post(
        '${ApiConfig.orders}/$orderId/status',
        data: {'action': action},
      );
    } on DioException catch (e) {
      debugPrint('❌ API Error: $e');
      rethrow;
    }
  }
}
