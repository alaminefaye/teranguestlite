import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../services/chat_api.dart';

const String _keyLastSeenMessageId = 'chat_last_seen_message_id';

/// Compteur de messages non lus (côté client) pour le chat Hotel Infos.
/// Les messages "non lus" sont les messages du staff dont l'id est supérieur
/// au dernier id vu par le client (en ouvrant le chat).
class ChatUnreadProvider with ChangeNotifier {
  final ChatApi _chatApi = ChatApi();

  int _unreadCount = 0;

  int get unreadCount => _unreadCount;

  /// Charge le nombre de messages non lus (staff, id > lastSeenMessageId).
  /// À appeler au chargement du dashboard.
  Future<void> loadUnreadCount() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final lastSeenId = prefs.getInt(_keyLastSeenMessageId) ?? 0;
      final messages = await _chatApi.getMessages();
      final count = messages
          .where((m) => m.senderType == 'staff' && m.id > lastSeenId)
          .length;
      if (_unreadCount != count) {
        _unreadCount = count;
        notifyListeners();
      }
    } catch (_) {
      // Ne pas bloquer l'UI ; on garde l'ancien compteur
    }
  }

  /// Marque la conversation comme lue (appelé à l'ouverture du ChatbotScreen).
  /// [lastMessageId] = max id des messages actuellement affichés.
  Future<void> markAsRead(int lastMessageId) async {
    if (lastMessageId <= 0 && _unreadCount == 0) return;
    try {
      final prefs = await SharedPreferences.getInstance();
      final previous = prefs.getInt(_keyLastSeenMessageId) ?? 0;
      if (lastMessageId > previous) {
        await prefs.setInt(_keyLastSeenMessageId, lastMessageId);
      }
      if (_unreadCount != 0) {
        _unreadCount = 0;
        notifyListeners();
      }
    } catch (_) {}
  }
}
