import 'package:dio/dio.dart';
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

  Future<ChatMessage> sendMessage(String content) async {
    try {
      final response = await _api.post(
        '/chat/messages',
        data: {'content': content},
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

      payload['file'] = await MultipartFile.fromFile(
        filePath,
        filename: fileName,
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

  Future<List<StaffConversationSummary>> getStaffConversations() async {
    try {
      final response = await _api.get('/staff/chat/conversations');
      final data = response.data as Map<String, dynamic>;
      if (data['success'] != true) {
        throw Exception(
          data['message'] ??
              'Erreur lors du chargement des conversations invités.',
        );
      }
      final root = data['data'] as Map<String, dynamic>? ?? {};
      final conversations = root['conversations'] as List? ?? [];
      return conversations
          .map(
            (e) => StaffConversationSummary.fromJson(e as Map<String, dynamic>),
          )
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

  Future<ChatMessage> sendStaffTextMessage(
    int conversationId,
    String content,
  ) async {
    try {
      final response = await _api.post(
        '/staff/chat/conversations/$conversationId/messages',
        data: {'content': content},
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
