import 'package:flutter/foundation.dart';
import 'package:dio/dio.dart';
import '../models/vehicle.dart';
import 'api_service.dart';

class VehicleApi {
  final ApiService _apiService = ApiService();

  /// Retourne le mode de location et la liste des véhicules.
  /// [rentalMode] : 'catalogue' (défaut) ou 'form' — défini côté admin web.
  Future<({String rentalMode, List<Vehicle> vehicles})> getVehicles({
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
      final rentalMode =
          (response.data['rental_mode'] as String?)?.trim() == 'form'
              ? 'form'
              : 'catalogue';
      final list = response.data['data'] as List? ?? [];
      final vehicles = list
          .map((e) => Vehicle.fromJson(e as Map<String, dynamic>))
          .toList();
      return (rentalMode: rentalMode, vehicles: vehicles);
    } on DioException catch (e) {
      debugPrint('❌ VehicleApi Error: $e');
      rethrow;
    }
  }
}
