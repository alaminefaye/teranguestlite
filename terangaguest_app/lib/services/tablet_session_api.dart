import 'package:dio/dio.dart';
import '../config/api_config.dart';
import '../models/guest_session.dart';
import '../models/user.dart';
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
    final hasRoomNumber = (roomNumber?.isNotEmpty ?? false);
    if (roomId == null && !hasRoomNumber) {
      throw Exception(
        'Indiquez le numéro de chambre ou l\'identifiant de la chambre.',
      );
    }
    try {
      final payload = <String, dynamic>{'code': code.trim()};
      if (roomId != null) {
        payload['room_id'] = roomId;
      }
      if (hasRoomNumber) {
        payload['room_number'] = roomNumber;
      }
      final response = await _api.post(
        ApiConfig.tabletValidateCode,
        data: payload,
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

  /// Récupère le livret d'accueil (Wi‑Fi, règlement, etc.) pour la chambre de la session.
  /// Envoie la session (guest_id, room_id, reservation_id) pour que le serveur ne renvoie
  /// les infos que pour ce séjour → isolation entre entreprises.
  Future<HotelInfos> getHotelInfos(GuestSession session) async {
    try {
      final response = await _api.post(
        ApiConfig.tabletHotelInfos,
        data: {
          'guest_id': session.guestId,
          'room_id': session.roomId,
          'reservation_id': session.reservationId,
          if (session.validatedAt != null && session.validatedAt!.isNotEmpty)
            'validated_at': session.validatedAt,
        },
      );
      final data = response.data as Map<String, dynamic>?;
      if (data == null || data['success'] != true) {
        throw Exception(data?['message'] ?? 'Impossible de charger les infos.');
      }
      final inner = data['data'] as Map<String, dynamic>?;
      final raw = inner?['hotel_infos'] as Map<String, dynamic>?;
      return HotelInfos.fromJson(raw);
    } on DioException catch (e) {
      final body = e.response?.data;
      final message = body is Map && body['message'] != null
          ? (body['message'] as String?)
          : null;
      throw Exception(
        message?.trim().isNotEmpty == true
            ? message!
            : 'Impossible de charger les infos de l\'hôtel.',
      );
    }
  }

  /// Vérifie que la session (séjour) est encore valide avant d'afficher la confirmation de commande.
  /// Envoie [validatedAt] pour que le serveur rejette si le code a été régénéré depuis.
  /// Lance une Exception si la session a expiré ou est invalide (403).
  Future<GuestSession> validateSession(GuestSession session) async {
    try {
      final response = await _api.post(
        ApiConfig.tabletValidateSession,
        data: {
          'guest_id': session.guestId,
          'room_id': session.roomId,
          'reservation_id': session.reservationId,
          if (session.validatedAt != null && session.validatedAt!.isNotEmpty)
            'validated_at': session.validatedAt,
        },
      );
      final data = response.data as Map<String, dynamic>?;
      if (data == null || data['success'] != true || data['data'] == null) {
        throw Exception(
          data?['message'] ??
              'Séjour invalide ou expiré. Entrez à nouveau votre code.',
        );
      }
      return GuestSession.fromJson(data['data'] as Map<String, dynamic>);
    } on DioException catch (e) {
      final body = e.response?.data;
      final message = body is Map && body['message'] != null
          ? (body['message'] as String?)
          : null;
      throw Exception(
        message?.trim().isNotEmpty == true
            ? message!
            : 'Séjour invalide ou expiré. Entrez à nouveau votre code.',
      );
    }
  }

  /// Message explicite pour les erreurs HTTP courantes (panier / checkout).
  static String _messageFromDio(DioException e) {
    final body = e.response?.data;
    if (body is Map && body['message'] != null) {
      final msg = body['message'];
      if (msg is String && msg.trim().isNotEmpty) return msg;
    }
    final code = e.response?.statusCode;
    if (code == 403) {
      return 'Accès refusé. Vérifiez que votre code client est encore valide et que vous êtes bien enregistré pour cette chambre. Si le problème continue, contactez la réception.';
    }
    if (code == 401) {
      return 'Session expirée. Entrez à nouveau votre code client pour valider la commande.';
    }
    if (code == 422) {
      if (body is Map && body['errors'] != null) {
        final errors = body['errors'] as Map<String, dynamic>?;
        if (errors != null && errors.isNotEmpty) {
          final first = errors.values.first;
          if (first is List && first.isNotEmpty) return first.first.toString();
        }
      }
      return 'Données invalides. Vérifiez votre panier et réessayez.';
    }
    if (code != null && code >= 500) {
      return 'Le serveur est temporairement indisponible. Réessayez dans quelques instants.';
    }
    if (e.type == DioExceptionType.connectionError ||
        e.type == DioExceptionType.connectionTimeout) {
      return 'Impossible de joindre le serveur. Vérifiez votre connexion.';
    }
    return 'Impossible de valider la commande. Réessayez ou contactez la réception.';
  }

  /// Checkout room service avec session client (tablette).
  Future<Map<String, dynamic>> checkout({
    required GuestSession session,
    required List<Map<String, dynamic>> items,
    String? specialInstructions,
    required String paymentMethod,
  }) async {
    try {
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
      final data = response.data as Map<String, dynamic>?;
      if (data == null || data['success'] != true) {
        throw Exception(data?['message'] ?? 'Erreur lors de la commande.');
      }
      return data['data'] as Map<String, dynamic>;
    } on DioException catch (e) {
      throw Exception(_messageFromDio(e));
    }
  }
}
