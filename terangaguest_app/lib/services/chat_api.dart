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
    } on DioException {
      throw Exception(
        'Impossible de se connecter au serveur. Vérifiez la connexion internet ou réessayez plus tard.',
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
    } on DioException {
      throw Exception(
        'Impossible d’envoyer le message. Vérifiez la connexion internet ou réessayez plus tard.',
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
    } on DioException {
      throw Exception(
        'Impossible d’envoyer le message. Vérifiez la connexion internet ou réessayez plus tard.',
      );
    } catch (_) {
      throw Exception('Erreur inattendue lors de l’envoi du message.');
    }
  }
}
