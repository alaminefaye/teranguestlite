import 'package:flutter/foundation.dart';
import 'package:dio/dio.dart';
import '../config/api_config.dart';
import 'api_service.dart';

class NotificationsApi {
  final ApiService _apiService = ApiService();

  Future<void> markAllAsRead() async {
    try {
      await _apiService.post('${ApiConfig.notifications}/mark-all-read');
    } on DioException catch (e) {
      debugPrint('❌ API Error (notifications mark-all-read): $e');
      rethrow;
    }
  }

  Future<void> cleanupRead() async {
    try {
      await _apiService.delete('${ApiConfig.notifications}/cleanup');
    } on DioException catch (e) {
      debugPrint('❌ API Error (notifications cleanup): $e');
      rethrow;
    }
  }
}
