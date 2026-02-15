import 'package:flutter/foundation.dart';
import 'package:dio/dio.dart';
import '../config/api_config.dart';
import '../models/leisure_category.dart';
import 'api_service.dart';

class LeisureApi {
  final ApiService _apiService = ApiService();

  /// Arbre Sport / Loisirs : catégories principales avec leurs activités (config admin).
  Future<List<LeisureMainCategoryDto>> getCategories() async {
    try {
      final response = await _apiService.get(ApiConfig.leisureCategories);
      final list = response.data['data'] as List? ?? [];
      return list
          .map((e) => LeisureMainCategoryDto.fromJson(e as Map<String, dynamic>))
          .toList();
    } on DioException catch (e) {
      debugPrint('❌ LeisureApi Error: $e');
      rethrow;
    }
  }
}
