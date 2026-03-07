import 'package:flutter/foundation.dart';
import 'package:dio/dio.dart';
import '../models/guide.dart';
import 'api_service.dart';

class GuidesApi {
  final ApiService _apiService = ApiService();

  Future<List<GuideCategory>> getGuides() async {
    try {
      final response = await _apiService.get('/guides');
      final list = response.data as List? ?? [];
      return list
          .map((e) => GuideCategory.fromJson(e as Map<String, dynamic>))
          .toList();
    } on DioException catch (e) {
      debugPrint('❌ GuidesApi Error: $e');
      rethrow;
    }
  }
}
