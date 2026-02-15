import 'package:dio/dio.dart';
import '../config/api_config.dart';
import '../models/chat_message.dart';
import 'api_service.dart';

class ChatApi {
  final ApiService _api = ApiService();

  Future<List<ChatMessage>> getMessages() async {
    final response = await _api.get('${ApiConfig.baseUrl}/chat/messages'.replaceFirst(ApiConfig.baseUrl, ''));
    final data = response.data as Map<String, dynamic>;
    if (data['success'] != true) {
      throw Exception(data['message'] ?? 'Erreur lors du chargement des messages.');
    }
    final messages = data['data']['messages'] as List? ?? [];
    return messages
        .map((e) => ChatMessage.fromJson(e as Map<String, dynamic>))
        .toList();
  }

  Future<ChatMessage> sendMessage(String content) async {
    try {
      final response = await _api.post(
        '/chat/messages',
        data: {'content': content},
      );
      final data = response.data as Map<String, dynamic>;
      if (data['success'] != true) {
        throw Exception(data['message'] ?? 'Erreur lors de l’envoi du message.');
      }
      return ChatMessage.fromJson(data['data'] as Map<String, dynamic>);
    } on DioException catch (e) {
      throw Exception(e.message ?? 'Erreur réseau.');
    }
  }
}

