import 'package:flutter/foundation.dart';
import 'package:dio/dio.dart';
import '../models/vehicle.dart';
import 'api_service.dart';

class VehicleApi {
  final ApiService _apiService = ApiService();

  Future<List<Vehicle>> getVehicles({
    String? vehicleType,
    int? minSeats,
  }) async {
    try {
      final queryParams = <String, dynamic>{};
      if (vehicleType != null && vehicleType.isNotEmpty) {
        queryParams['vehicle_type'] = vehicleType;
      }
      if (minSeats != null && minSeats > 0) {
        queryParams['seats'] = minSeats;
      }
      final response = await _apiService.get(
        '/vehicles',
        queryParameters: queryParams.isEmpty ? null : queryParams,
      );
      final list = response.data['data'] as List?;
      if (list == null) return [];
      return list
          .map((e) => Vehicle.fromJson(e as Map<String, dynamic>))
          .toList();
    } on DioException catch (e) {
      debugPrint('❌ VehicleApi Error: $e');
      rethrow;
    }
  }
}
