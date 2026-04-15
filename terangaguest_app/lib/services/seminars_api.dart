import 'package:flutter/foundation.dart';
import 'package:dio/dio.dart';
import '../config/api_config.dart';
import '../models/seminar_room.dart';
import 'api_service.dart';

class SeminarsApi {
  final ApiService _apiService = ApiService();

  Future<List<SeminarRoom>> getSeminarRooms() async {
    try {
      final endpoint = ApiConfig.vitrineSeminars;
      final response = await _apiService.get(endpoint);
      final data = response.data;
      final list = data is Map ? data['data'] : null;
      if (list is! List) return const [];
      return list
          .map((json) => SeminarRoom.fromJson(json as Map<String, dynamic>))
          .toList();
    } on DioException catch (e) {
      debugPrint('❌ API Error: $e');
      rethrow;
    }
  }
}

