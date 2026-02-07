import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/guest_session.dart';
import '../services/tablet_session_api.dart';

const _keySession = 'tablet_guest_session';
const _keyRoomNumber = 'tablet_room_number';

/// Gère la session client sur la tablette (code validé + chambre).
class TabletSessionProvider with ChangeNotifier {
  final TabletSessionApi _api = TabletSessionApi();
  GuestSession? _session;
  String? _roomNumber;
  bool _loading = false;
  String? _error;

  GuestSession? get session => _session;
  String? get roomNumber => _roomNumber;
  bool get hasSession => _session != null;
  bool get isLoading => _loading;
  String? get error => _error;

  /// À appeler au démarrage (ex. initState d'un écran ou main).
  Future<void> load() async {
    await _loadFromStorage();
    notifyListeners();
  }

  Future<void> _loadFromStorage() async {
    final prefs = await SharedPreferences.getInstance();
    _roomNumber = prefs.getString(_keyRoomNumber);
    final json = prefs.getString(_keySession);
    if (json != null) {
      try {
        _session = GuestSession.fromJson(
          Map<String, dynamic>.from(jsonDecode(json) as Map),
        );
      } catch (_) {
        await prefs.remove(_keySession);
      }
    }
  }

  Future<void> setRoomNumber(String? value) async {
    _roomNumber = value?.trim();
    final prefs = await SharedPreferences.getInstance();
    final r = _roomNumber;
    if (r == null || r.isEmpty) {
      await prefs.remove(_keyRoomNumber);
    } else {
      await prefs.setString(_keyRoomNumber, r);
    }
    notifyListeners();
  }

  /// Valide le code et enregistre la session si succès.
  Future<void> validateCode(String code) async {
    _loading = true;
    _error = null;
    notifyListeners();
    try {
      final s = await _api.validateCode(
        code: code,
        roomNumber: _roomNumber,
      );
      _session = s;
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(_keySession, jsonEncode(s.toJson()));
      notifyListeners();
    } catch (e) {
      _error = e.toString().replaceFirst('Exception: ', '');
      notifyListeners();
      rethrow;
    } finally {
      _loading = false;
      notifyListeners();
    }
  }

  Future<void> clearSession() async {
    _session = null;
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_keySession);
    notifyListeners();
  }

  void clearError() {
    _error = null;
    notifyListeners();
  }
}
