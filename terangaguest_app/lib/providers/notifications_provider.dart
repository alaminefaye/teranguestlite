import 'dart:async';
import 'package:flutter/foundation.dart';
import '../../services/api_service.dart';
import '../../config/api_config.dart';

class NotificationsProvider with ChangeNotifier {
  final ApiService _apiService = ApiService();
  int _unreadCount = 0;
  Timer? _timer;

  int get unreadCount => _unreadCount;

  void startPolling() {
    _timer?.cancel();
    fetchUnreadCount();
    _timer = Timer.periodic(const Duration(seconds: 15), (_) {
      fetchUnreadCount();
    });
  }

  void stopPolling() {
    _timer?.cancel();
    _timer = null;
  }

  Future<void> fetchUnreadCount() async {
    try {
      final response = await _apiService.get(
        '${ApiConfig.notifications}/unread',
      );
      final data = response.data;
      if (data is Map && data['success'] == true) {
        final count = data['count'] ?? data['unread_count'] ?? 0;
        if (_unreadCount != count) {
          _unreadCount = count;
          notifyListeners();
        }
      }
    } catch (e) {
      debugPrint('Error polling unread notifications: $e');
    }
  }

  void markAllRead() {
    _unreadCount = 0;
    notifyListeners();
  }
}
