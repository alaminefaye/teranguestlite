import 'package:flutter/foundation.dart';
import 'package:dio/dio.dart';
import '../config/api_config.dart';
import '../models/amenity_category.dart';
import 'api_service.dart';

class AmenityApi {
  final ApiService _apiService = ApiService();

  /// Récupère les catégories Amenities & Conciergerie avec leurs articles (config admin).
  Future<List<AmenityCategoryDto>> getCategories() async {
    try {
      final endpoint = ApiConfig.vitrineMode
          ? ApiConfig.vitrineAmenityCategories
          : ApiConfig.amenityCategories;
      final response = await _apiService.get(endpoint);
      final list = response.data['data'] as List? ?? [];
      return list
          .map((e) => AmenityCategoryDto.fromJson(e as Map<String, dynamic>))
          .toList();
    } on DioException catch (e) {
      debugPrint('❌ AmenityApi Error: $e');
      rethrow;
    }
  }
}
