import 'package:dio/dio.dart';
import '../config/api_config.dart';
import '../models/guest_session.dart';
import 'api_service.dart';

/// API tablette en chambre (sans auth) : validation code et checkout.
class TabletSessionApi {
  final ApiService _api = ApiService();

  /// Valide le code client. Envoie [roomId] ou [roomNumber] pour lier à la chambre.
  /// En cas d'erreur (401/403), lance une Exception avec le message clair du serveur.
  Future<GuestSession> validateCode({
    required String code,
    int? roomId,
    String? roomNumber,
  }) async {
    if (roomId == null && (roomNumber == null || roomNumber.isEmpty)) {
      throw Exception('Indiquez le numéro de chambre ou l\'identifiant de la chambre.');
    }
    try {
      final response = await _api.post(
        ApiConfig.tabletValidateCode,
        data: {
          'code': code.trim(),
          if (roomId != null) 'room_id': roomId,
          if (roomNumber != null && roomNumber.isNotEmpty) 'room_number': roomNumber,
        },
      );
      final data = response.data as Map<String, dynamic>?;
      if (data == null || data['success'] != true || data['data'] == null) {
        throw Exception(data?['message'] ?? 'Code invalide ou séjour expiré.');
      }
      return GuestSession.fromJson(data['data'] as Map<String, dynamic>);
    } on DioException catch (e) {
      final body = e.response?.data;
      final message = body is Map && body.containsKey('message')
          ? (body['message'] as String?)
          : null;
      throw Exception(
        message?.trim().isNotEmpty == true
            ? message!
            : (e.response?.statusCode == 401
                ? 'Le code saisi est incorrect. Vérifiez le code à 6 chiffres reçu à l\'enregistrement.'
                : e.response?.statusCode == 403
                    ? 'Votre réservation est terminée ou n\'est pas encore active. Vérifiez vos dates de séjour avec la réception.'
                    : 'Impossible de valider le code. Réessayez ou contactez la réception.'),
      );
    }
  }

  /// Checkout room service avec session client (tablette).
  Future<Map<String, dynamic>> checkout({
    required GuestSession session,
    required List<Map<String, dynamic>> items,
    String? specialInstructions,
    required String paymentMethod,
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
        'payment_method': paymentMethod,
      },
    );
    final data = response.data;
    if (data['success'] != true) {
      throw Exception(data['message'] ?? 'Erreur lors de la commande.');
    }
    return data['data'] as Map<String, dynamic>;
  }
}
