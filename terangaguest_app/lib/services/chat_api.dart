import 'package:dio/dio.dart';
import 'package:http_parser/http_parser.dart';
import '../models/chat_message.dart';
import 'api_service.dart';

class ChatApi {
  final ApiService _api = ApiService();

  Future<List<ChatMessage>> getMessages() async {
    try {
      final response = await _api.get('/chat/messages');
      final data = response.data as Map<String, dynamic>;
      if (data['success'] != true) {
        throw Exception(
          data['message'] ?? 'Erreur lors du chargement des messages.',
        );
      }
      final messages = data['data']['messages'] as List? ?? [];
      return messages
          .map((e) => ChatMessage.fromJson(e as Map<String, dynamic>))
          .toList();
    } on DioException catch (e) {
      final body = e.response?.data;
      final serverMessage = body is Map && body['message'] is String
          ? (body['message'] as String).trim()
          : null;
      throw Exception(
        serverMessage != null && serverMessage.isNotEmpty
            ? serverMessage
            : 'Impossible de se connecter au serveur. Vérifiez la connexion internet ou réessayez plus tard.',
      );
    } catch (_) {
      throw Exception('Erreur inattendue lors du chargement des messages.');
    }
  }

  Future<ChatMessage> sendMessage(String content, {int? replyToId}) async {
    try {
      final payload = <String, dynamic>{'content': content};
      if (replyToId != null) payload['reply_to_id'] = replyToId;
      final response = await _api.post(
        '/chat/messages',
        data: payload,
      );
      final data = response.data as Map<String, dynamic>;
      if (data['success'] != true) {
        throw Exception(
          data['message'] ?? 'Erreur lors de l’envoi du message.',
        );
      }
      return ChatMessage.fromJson(data['data'] as Map<String, dynamic>);
    } on DioException catch (e) {
      final body = e.response?.data;
      final serverMessage = body is Map && body['message'] is String
          ? (body['message'] as String).trim()
          : null;
      throw Exception(
        serverMessage != null && serverMessage.isNotEmpty
            ? serverMessage
            : 'Impossible d’envoyer le message. Vérifiez la connexion internet ou réessayez plus tard.',
      );
    } catch (_) {
      throw Exception('Erreur inattendue lors de l’envoi du message.');
    }
  }

  Future<ChatMessage> sendMediaMessage({
    required String filePath,
    required String fileName,
    required String messageType,
    String? content,
    int? durationSeconds,
  }) async {
    try {
      final Map<String, dynamic> payload = {'message_type': messageType};

      if (content != null && content.trim().isNotEmpty) {
        payload['content'] = content.trim();
      }

      if (durationSeconds != null && durationSeconds > 0) {
        payload['duration'] = durationSeconds;
      }

      MediaType? contentType;
      final lowerName = fileName.toLowerCase();
      if (messageType == 'audio') {
        if (lowerName.endsWith('.aac')) {
          contentType = MediaType('audio', 'aac');
        } else if (lowerName.endsWith('.m4a')) {
          contentType = MediaType('audio', 'mp4');
        } else if (lowerName.endsWith('.mp3')) {
          contentType = MediaType('audio', 'mpeg');
        } else if (lowerName.endsWith('.wav')) {
          contentType = MediaType('audio', 'wav');
        } else if (lowerName.endsWith('.ogg')) {
          contentType = MediaType('audio', 'ogg');
        }
      }

      payload['file'] = await MultipartFile.fromFile(
        filePath,
        filename: fileName,
        contentType: contentType,
      );

      final formData = FormData.fromMap(payload);

      final response = await _api.post('/chat/messages', data: formData);
      final data = response.data as Map<String, dynamic>;
      if (data['success'] != true) {
        throw Exception(
          data['message'] ?? 'Erreur lors de l’envoi du message.',
        );
      }
      return ChatMessage.fromJson(data['data'] as Map<String, dynamic>);
    } on DioException catch (e) {
      final body = e.response?.data;
      final serverMessage = body is Map && body['message'] is String
          ? (body['message'] as String).trim()
          : null;
      throw Exception(
        serverMessage != null && serverMessage.isNotEmpty
            ? serverMessage
            : 'Impossible d’envoyer le message. Vérifiez la connexion internet ou réessayez plus tard.',
      );
    } catch (_) {
      throw Exception('Erreur inattendue lors de l’envoi du message.');
    }
  }

  Future<Map<String, dynamic>> getStaffConversations({
    int page = 1,
    int perPage = 20,
  }) async {
    try {
      final response = await _api.get(
        '/staff/chat/conversations',
        queryParameters: {'page': page, 'per_page': perPage},
      );
      final data = response.data as Map<String, dynamic>;
      if (data['success'] != true) {
        throw Exception(
          data['message'] ??
              'Erreur lors du chargement des conversations invités.',
        );
      }
      final root = data['data'] as Map<String, dynamic>? ?? {};
      final conversations = root['conversations'] as List? ?? [];
      final meta = root['meta'] as Map<String, dynamic>? ?? {};
      final items = conversations
          .map(
            (e) => StaffConversationSummary.fromJson(e as Map<String, dynamic>),
          )
          .toList();
      return {'conversations': items, 'meta': meta};
    } on DioException catch (e) {
      final body = e.response?.data;
      final serverMessage = body is Map && body['message'] is String
          ? (body['message'] as String).trim()
          : null;
      throw Exception(
        serverMessage != null && serverMessage.isNotEmpty
            ? serverMessage
            : 'Impossible de se connecter au serveur. Vérifiez la connexion internet ou réessayez plus tard.',
      );
    } catch (_) {
      throw Exception(
        'Erreur inattendue lors du chargement des conversations invités.',
      );
    }
  }

  Future<StaffConversationDetail> getStaffConversationDetail(
    int conversationId,
  ) async {
    try {
      final response = await _api.get(
        '/staff/chat/conversations/$conversationId',
      );
      final data = response.data as Map<String, dynamic>;
      if (data['success'] != true) {
        throw Exception(
          data['message'] ??
              'Erreur lors du chargement de la conversation invité.',
        );
      }
      final root = data['data'] as Map<String, dynamic>? ?? {};
      final messages = root['messages'] as List? ?? [];
      return StaffConversationDetail(
        conversationId: root['conversation_id'] as int,
        guestName: (root['guest_name'] as String?) ?? 'Client chambre',
        roomLabel: root['room_label'] as String?,
        messages: messages
            .map((e) => ChatMessage.fromJson(e as Map<String, dynamic>))
            .toList(),
      );
    } on DioException catch (e) {
      final body = e.response?.data;
      final serverMessage = body is Map && body['message'] is String
          ? (body['message'] as String).trim()
          : null;
      throw Exception(
        serverMessage != null && serverMessage.isNotEmpty
            ? serverMessage
            : 'Impossible de se connecter au serveur. Vérifiez la connexion internet ou réessayez plus tard.',
      );
    } catch (_) {
      throw Exception(
        'Erreur inattendue lors du chargement de la conversation invité.',
      );
    }
  }

  /// Supprimer une conversation (staff) — comme supprimer le chat sur WhatsApp.
  Future<void> deleteStaffConversation(int conversationId) async {
    try {
      final response = await _api.delete(
        '/staff/chat/conversations/$conversationId',
      );
      final data = response.data as Map<String, dynamic>;
      if (data['success'] != true) {
        throw Exception(
          data['message'] ?? 'Erreur lors de la suppression de la conversation.',
        );
      }
    } on DioException catch (e) {
      final body = e.response?.data;
      final serverMessage = body is Map && body['message'] is String
          ? (body['message'] as String).trim()
          : null;
      throw Exception(
        serverMessage != null && serverMessage.isNotEmpty
            ? serverMessage
            : 'Impossible de se connecter au serveur.',
      );
    } catch (_) {
      throw Exception('Erreur inattendue lors de la suppression de la conversation.');
    }
  }

  /// Supprimer un message (client) — soft delete, uniquement ses propres messages.
  Future<void> deleteMessage(int messageId) async {
    try {
      final response = await _api.delete('/chat/messages/$messageId');
      final data = response.data as Map<String, dynamic>;
      if (data['success'] != true) {
        throw Exception(data['message'] ?? 'Erreur lors de la suppression.');
      }
    } on DioException catch (e) {
      final body = e.response?.data;
      final msg = body is Map && body['message'] is String ? body['message'] as String : null;
      throw Exception(msg ?? 'Impossible de supprimer le message.');
    }
  }

  /// Supprimer un message (staff) — soft delete.
  Future<void> deleteStaffMessage(int conversationId, int messageId) async {
    try {
      final response = await _api.delete(
        '/staff/chat/conversations/$conversationId/messages/$messageId',
      );
      final data = response.data as Map<String, dynamic>;
      if (data['success'] != true) {
        throw Exception(data['message'] ?? 'Erreur lors de la suppression.');
      }
    } on DioException catch (e) {
      final body = e.response?.data;
      final msg = body is Map && body['message'] is String ? body['message'] as String : null;
      throw Exception(msg ?? 'Impossible de supprimer le message.');
    }
  }

  Future<ChatMessage> sendStaffTextMessage(
    int conversationId,
    String content, {
    int? replyToId,
  }) async {
    try {
      final payload = <String, dynamic>{'content': content};
      if (replyToId != null) payload['reply_to_id'] = replyToId;
      final response = await _api.post(
        '/staff/chat/conversations/$conversationId/messages',
        data: payload,
      );
      final data = response.data as Map<String, dynamic>;
      if (data['success'] != true) {
        throw Exception(
          data['message'] ?? 'Erreur lors de l’envoi du message.',
        );
      }
      return ChatMessage.fromJson(data['data'] as Map<String, dynamic>);
    } on DioException catch (e) {
      final body = e.response?.data;
      final serverMessage = body is Map && body['message'] is String
          ? (body['message'] as String).trim()
          : null;
      throw Exception(
        serverMessage != null && serverMessage.isNotEmpty
            ? serverMessage
            : 'Impossible d’envoyer le message. Vérifiez la connexion internet ou réessayez plus tard.',
      );
    } catch (_) {
      throw Exception('Erreur inattendue lors de l’envoi du message.');
    }
  }

  /// Envoyer un média (image ou note vocale) dans la conversation staff ↔ client (pas dans le chat perso).
  Future<ChatMessage> sendStaffMediaMessage(
    int conversationId, {
    required String filePath,
    required String fileName,
    required String messageType,
    String? content,
    int? durationSeconds,
  }) async {
    try {
      final Map<String, dynamic> payload = {'message_type': messageType};

      if (content != null && content.trim().isNotEmpty) {
        payload['content'] = content.trim();
      }

      if (durationSeconds != null && durationSeconds > 0) {
        payload['duration'] = durationSeconds;
      }

      MediaType? contentType;
      final lowerName = fileName.toLowerCase();
      if (messageType == 'audio') {
        if (lowerName.endsWith('.aac')) {
          contentType = MediaType('audio', 'aac');
        } else if (lowerName.endsWith('.m4a')) {
          contentType = MediaType('audio', 'mp4');
        } else if (lowerName.endsWith('.mp3')) {
          contentType = MediaType('audio', 'mpeg');
        } else if (lowerName.endsWith('.wav')) {
          contentType = MediaType('audio', 'wav');
        } else if (lowerName.endsWith('.ogg')) {
          contentType = MediaType('audio', 'ogg');
        }
      }

      payload['file'] = await MultipartFile.fromFile(
        filePath,
        filename: fileName,
        contentType: contentType,
      );

      final formData = FormData.fromMap(payload);

      final response = await _api.post(
        '/staff/chat/conversations/$conversationId/messages',
        data: formData,
      );
      final data = response.data as Map<String, dynamic>;
      if (data['success'] != true) {
        throw Exception(
          data['message'] ?? 'Erreur lors de l’envoi du message.',
        );
      }
      return ChatMessage.fromJson(data['data'] as Map<String, dynamic>);
    } on DioException catch (e) {
      final body = e.response?.data;
      final serverMessage = body is Map && body['message'] is String
          ? (body['message'] as String).trim()
          : null;
      throw Exception(
        serverMessage != null && serverMessage.isNotEmpty
            ? serverMessage
            : 'Impossible d’envoyer le message. Vérifiez la connexion internet ou réessayez plus tard.',
      );
    } catch (_) {
      throw Exception('Erreur inattendue lors de l’envoi du message.');
    }
  }
}

class StaffConversationSummary {
  final int id;
  final String guestName;
  final String? roomLabel;
  final String? lastMessagePreview;
  final DateTime? lastMessageAt;
  final int unreadCount;

  StaffConversationSummary({
    required this.id,
    required this.guestName,
    required this.roomLabel,
    required this.lastMessagePreview,
    required this.lastMessageAt,
    required this.unreadCount,
  });

  factory StaffConversationSummary.fromJson(Map<String, dynamic> json) {
    final lastAtRaw = json['last_message_at'] as String?;
    return StaffConversationSummary(
      id: json['id'] as int,
      guestName: (json['guest_name'] as String?) ?? 'Client chambre',
      roomLabel: json['room_label'] as String?,
      lastMessagePreview: json['last_message_preview'] as String?,
      lastMessageAt: lastAtRaw != null ? DateTime.tryParse(lastAtRaw) : null,
      unreadCount: (json['unread_count'] as num?)?.toInt() ?? 0,
    );
  }
}

class StaffConversationDetail {
  final int conversationId;
  final String guestName;
  final String? roomLabel;
  final List<ChatMessage> messages;

  StaffConversationDetail({
    required this.conversationId,
    required this.guestName,
    required this.roomLabel,
    required this.messages,
  });
}
