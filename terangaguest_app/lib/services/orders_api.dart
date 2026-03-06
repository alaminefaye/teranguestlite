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
    String? period,
    int page = 1,
    int perPage = 15,
    String? clientCode,
  }) async {
    try {
      final queryParams = <String, dynamic>{'page': page, 'per_page': perPage};

      if (status != null && status.isNotEmpty) {
        queryParams['status'] = status;
      }

      if (period != null && period.isNotEmpty && period != 'all') {
        queryParams['period'] = period;
      }

      if (clientCode != null && clientCode.trim().isNotEmpty) {
        queryParams['client_code'] = clientCode.trim();
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
      final response = await _apiService.get('${ApiConfig.orders}/$orderId');
      final data = response.data;
      final Map<String, dynamic>? orderMap;
      if (data is Map<String, dynamic>) {
        if (data['data'] is Map<String, dynamic>) {
          orderMap = data['data'] as Map<String, dynamic>;
        } else if (data['data'] == null && data['id'] != null) {
          orderMap = data;
        } else {
          orderMap = null;
        }
      } else if (data is Map) {
        orderMap = Map<String, dynamic>.from(data);
      } else {
        orderMap = null;
      }
      if (orderMap == null) {
        throw Exception('Réponse détail commande invalide');
      }
      return Order.fromJson(orderMap);
    } on DioException catch (e) {
      debugPrint('❌ API Error: $e');
      rethrow;
    } catch (e, stack) {
      debugPrint('❌ Order detail parse error: $e\n$stack');
      rethrow;
    }
  }

  /// Recommander une commande (ajoute les articles au panier)
  Future<void> reorderOrder(int orderId) async {
    try {
      await _apiService.post('${ApiConfig.orders}/$orderId/reorder');
    } on DioException catch (e) {
      debugPrint('❌ API Error: $e');
      rethrow;
    }
  }

  /// Annuler une commande
  Future<void> cancelOrder(int orderId, {String? reason}) async {
    try {
      final payload = <String, dynamic>{};
      if (reason != null && reason.trim().isNotEmpty) {
        payload['reason'] = reason.trim();
      }
      await _apiService.post(
        '${ApiConfig.orders}/$orderId/cancel',
        data: payload.isEmpty ? null : payload,
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

  /// Notifie explicitement le personnel "Service en chambre" qu'une commande est prête à livrer.
  Future<void> notifyRoomService(int orderId) async {
    try {
      await _apiService.post(
        '${ApiConfig.orders}/$orderId/notify-room-service',
      );
    } on DioException catch (e) {
      debugPrint('❌ API Error notifyRoomService: $e');
      rethrow;
    }
  }

  /// Annule une commande par le staff/admin avec notification au client.
  Future<void> cancelByStaff(int orderId, String reason) async {
    try {
      await _apiService.post(
        '${ApiConfig.orders}/$orderId/cancel-by-staff',
        data: {'reason': reason},
      );
    } on DioException catch (e) {
      debugPrint('❌ API Error cancelByStaff: $e');
      rethrow;
    }
  }
}
