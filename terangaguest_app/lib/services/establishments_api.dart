import 'package:flutter/foundation.dart';
import 'package:dio/dio.dart';
import '../config/api_config.dart';
import '../models/establishment.dart';
import 'api_service.dart';

class EstablishmentsApi {
  final ApiService _apiService = ApiService();

  /// Liste des établissements (autres sites du groupe).
  Future<List<Establishment>> getEstablishments() async {
    try {
      final response = await _apiService.get(ApiConfig.establishments);
      final data = response.data['data'];
      if (data == null || data is! List) return [];
      return data
          .map((e) => Establishment.fromJson(e as Map<String, dynamic>))
          .toList();
    } on DioException catch (e) {
      debugPrint('❌ Establishments API Error: $e');
      rethrow;
    }
  }

  /// Détail d'un établissement avec galerie.
  Future<EstablishmentDetail> getEstablishmentDetail(int id) async {
    try {
      final response = await _apiService.get('${ApiConfig.establishments}/$id');
      final data = response.data['data'];
      if (data == null || data is! Map<String, dynamic>) {
        throw Exception('Invalid establishment detail response');
      }
      return EstablishmentDetail.fromJson(data);
    } on DioException catch (e) {
      debugPrint('❌ Establishment detail API Error: $e');
      rethrow;
    }
  }
}
