import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/guest_session.dart';
import '../services/tablet_session_api.dart';

const _keySession = 'tablet_guest_session';
const _keyRoomNumber = 'tablet_room_number';
const _keyValidatedCode = 'tablet_validated_code';

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
  /// Charge la session depuis le stockage sans revalidation.
  Future<void> load() async {
    await _loadFromStorage();
    notifyListeners();
  }

  /// Charge la session depuis le stockage puis vérifie auprès du serveur
  /// qu'elle est encore valide (séjour actif). Si le client a fait checkout
  /// ou le séjour est terminé, la session est supprimée et l'utilisateur
  /// devra ressaisir le code.
  Future<void> loadAndValidate() async {
    await _loadFromStorage();
    if (_session != null) {
      try {
        final s = await _api.validateSession(_session!);
        _session = s;
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString(_keySession, jsonEncode(s.toJson()));
      } catch (_) {
        await clearSession();
      }
    }
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
        _session = null;
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

  /// Valide le code et enregistre la session uniquement si le serveur répond succès.
  /// En cas d'erreur (code invalide, séjour expiré), toute session existante est effacée.
  /// [enterpriseId] optionnel : pour limiter la chambre à l'établissement de l'utilisateur connecté.
  Future<void> validateCode(String code, {int? enterpriseId}) async {
    _loading = true;
    _error = null;
    notifyListeners();
    try {
      final s = await _api.validateCode(
        code: code,
        roomNumber: _roomNumber,
        enterpriseId: enterpriseId,
      );
      _session = s;
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(_keySession, jsonEncode(s.toJson()));
      await prefs.setString(_keyValidatedCode, code.trim());
      notifyListeners();
    } catch (e) {
      await clearSession();
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
    await prefs.remove(_keyValidatedCode);
    notifyListeners();
  }

  /// Code client validé (stocké après une validation réussie). Utilisé pour les réservations.
  /// Retourne null si aucun code stocké ou session effacée.
  Future<String?> getValidatedClientCode() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString(_keyValidatedCode);
  }

  void clearError() {
    _error = null;
    notifyListeners();
  }
}
