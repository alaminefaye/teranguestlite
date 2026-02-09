import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/guest_session.dart';
import '../services/tablet_session_api.dart';
import '../services/notification_service.dart';

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
      final s = await _api.validateCode(code: code, roomNumber: _roomNumber);
      _session = s;
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(_keySession, jsonEncode(s.toJson()));
      notifyListeners();
      // Enregistrer le token FCM pour recevoir les notifications sur cette tablette
      NotificationService().registerWithBackendForTabletSession(s);
    } catch (e) {
      _error = e.toString().replaceFirst('Exception: ', '');
      notifyListeners();
      rethrow;
    } finally {
      _loading = false;
      notifyListeners();
    }
  }

  /// Vérifie que la session en cours est encore valide (séjour actif).
  /// Met à jour la session avec les données serveur si valide.
  /// Lance une exception si la session a expiré ou est invalide.
  Future<GuestSession> validateCurrentSession() async {
    if (_session == null) {
      throw StateError('Aucune session en cours');
    }
    _error = null;
    notifyListeners();
    try {
      final s = await _api.validateSession(_session!);
      _session = s;
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(_keySession, jsonEncode(s.toJson()));
      notifyListeners();
      return s;
    } catch (e) {
      _error = e.toString().replaceFirst('Exception: ', '');
      notifyListeners();
      rethrow;
    }
  }

  Future<void> clearSession() async {
    _session = null;
    _error = null;
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_keySession);
    notifyListeners();
  }

  void clearError() {
    _error = null;
    notifyListeners();
  }
}
