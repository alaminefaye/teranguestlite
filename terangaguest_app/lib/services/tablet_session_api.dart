import 'package:dio/dio.dart';
import '../config/api_config.dart';
import '../models/guest_session.dart';
import 'api_service.dart';

/// API tablette en chambre (sans auth) : validation code et checkout.
class TabletSessionApi {
  final ApiService _api = ApiService();

  /// Valide le code client. Envoie [roomId] ou [roomNumber] pour lier à la chambre.
  Future<GuestSession> validateCode({
    required String code,
    int? roomId,
    String? roomNumber,
  }) async {
    if (roomId == null && (roomNumber == null || roomNumber.isEmpty)) {
      throw Exception('Indiquez le numéro de chambre ou l\'identifiant de la chambre.');
    }
    final response = await _api.post(
      ApiConfig.tabletValidateCode,
      data: {
        'code': code.trim(),
        if (roomId != null) 'room_id': roomId,
        if (roomNumber != null && roomNumber.isNotEmpty) 'room_number': roomNumber,
      },
    );
    final data = response.data;
    if (data['success'] != true || data['data'] == null) {
      throw Exception(data['message'] ?? 'Code invalide ou séjour expiré.');
    }
    return GuestSession.fromJson(data['data'] as Map<String, dynamic>);
  }

  /// Checkout room service avec session client (tablette).
  Future<Map<String, dynamic>> checkout({
    required GuestSession session,
    required List<Map<String, dynamic>> items,
    String? specialInstructions,
  }) async {
    final response = await _api.post(
      ApiConfig.tabletCheckout,
      data: {
        'guest_id': session.guestId,
        'room_id': session.roomId,
        'reservation_id': session.reservationId,
        'items': items,
        if (specialInstructions != null && specialInstructions.isNotEmpty)
          'special_instructions': specialInstructions,
      },
    );
    final data = response.data;
    if (data['success'] != true) {
      throw Exception(data['message'] ?? 'Erreur lors de la commande.');
    }
    return data['data'] as Map<String, dynamic>;
  }
}
