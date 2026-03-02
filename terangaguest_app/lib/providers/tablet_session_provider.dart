import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/guest_session.dart';
import '../services/tablet_session_api.dart';

const _keySession = 'tablet_guest_session';
const _keyRoomNumber = 'tablet_room_number';
const _keyRoomId = 'tablet_room_id';

/// Gère la session client sur la tablette (code validé + chambre).
/// En multi-établissement (SaaS), on doit envoyer room_id (pas seulement room_number)
/// pour ne jamais recevoir les données d'un autre hôtel.
class TabletSessionProvider with ChangeNotifier {
  final TabletSessionApi _api = TabletSessionApi();
  GuestSession? _session;
  String? _roomNumber;
  int? _roomId;
  String? _clientCodeForPreFill;
  bool _loading = false;
  String? _error;

  GuestSession? get session => _session;
  String? get roomNumber => _roomNumber;
  /// ID chambre (prioritaire pour les API — évite les données d'un autre établissement).
  int? get roomId => _roomId ?? _session?.roomId;
  /// Code client pour pré-remplissage sur les écrans de réservation (spa, restaurant, etc.).
  String? get clientCodeForPreFill => _clientCodeForPreFill;
  bool get hasSession => _session != null;
  bool get isLoading => _loading;
  String? get error => _error;

  /// À appeler au démarrage (ex. initState d'un écran ou main).
  Future<void> load() async {
    await _loadFromStorage();
    notifyListeners();
  }

  /// Tente de récupérer automatiquement la session (et le code pour pré-remplissage) à partir
  /// de la chambre configurée. En SaaS on envoie room_id en priorité pour ne pas recevoir
  /// les données d'un autre établissement.
  Future<bool> tryRestoreSessionFromRoom() async {
    final id = roomId;
    final room = (_roomNumber ?? '').trim();
    if (id == null && room.isEmpty) return false;
    try {
      final result = await _api.getSessionByRoom(
        roomId: id,
        roomNumber: room.isNotEmpty ? room : null,
      );
      if (result == null) return false;
      _session = result.session;
      _clientCodeForPreFill = result.clientCode;
      _roomId = result.session.roomId;
      _error = null;
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(_keySession, jsonEncode(result.session.toJson()));
      await prefs.setInt(_keyRoomId, result.session.roomId);
      notifyListeners();
      return true;
    } catch (_) {
      return false;
    }
  }

  Future<void> _loadFromStorage() async {
    final prefs = await SharedPreferences.getInstance();
    _roomNumber = prefs.getString(_keyRoomNumber);
    _roomId = prefs.getInt(_keyRoomId);
    final json = prefs.getString(_keySession);
    if (json != null) {
      try {
        _session = GuestSession.fromJson(
          Map<String, dynamic>.from(jsonDecode(json) as Map),
        );
        if (_roomId == null) _roomId = _session?.roomId;
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

  /// Définit l'ID chambre (depuis le tableau de bord). Prioritaire en SaaS pour
  /// ne jamais charger les données d'un autre établissement.
  Future<void> setRoomId(int? value) async {
    _roomId = value;
    final prefs = await SharedPreferences.getInstance();
    if (value == null) {
      await prefs.remove(_keyRoomId);
    } else {
      await prefs.setInt(_keyRoomId, value);
    }
    notifyListeners();
  }

  /// Valide le code et enregistre la session si succès.
  /// Envoie room_id en priorité pour cibler le bon établissement.
  Future<void> validateCode(String code) async {
    _loading = true;
    _error = null;
    notifyListeners();
    try {
      final s = await _api.validateCode(
        code: code,
        roomId: roomId,
        roomNumber: (_roomNumber?.trim().isNotEmpty == true) ? _roomNumber : null,
      );
      _session = s;
      _roomId = s.roomId;
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(_keySession, jsonEncode(s.toJson()));
      await prefs.setInt(_keyRoomId, s.roomId);
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
    _clientCodeForPreFill = null;
    _error = null;
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_keySession);
    // Ne pas supprimer _roomId / _keyRoomId : la tablette reste liée à cette chambre.
    notifyListeners();
  }

  void clearError() {
    _error = null;
    notifyListeners();
  }
}
