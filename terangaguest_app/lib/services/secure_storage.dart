import 'dart:convert';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import '../models/user.dart';

class SecureStorage {
  static final SecureStorage _instance = SecureStorage._internal();
  late FlutterSecureStorage _storage;

  factory SecureStorage() {
    return _instance;
  }

  SecureStorage._internal() {
    _storage = const FlutterSecureStorage(
      aOptions: AndroidOptions(
        encryptedSharedPreferences: true,
      ),
    );
  }

  // Clés de stockage
  static const String _keyToken = 'auth_token';
  static const String _keyUser = 'user_data';
  static const String _keyRememberMe = 'remember_me';

  // ========== TOKEN ==========

  /// Sauvegarder le token d'authentification
  Future<void> saveToken(String token) async {
    await _storage.write(key: _keyToken, value: token);
  }

  /// Récupérer le token d'authentification
  Future<String?> getToken() async {
    return await _storage.read(key: _keyToken);
  }

  /// Supprimer le token d'authentification
  Future<void> deleteToken() async {
    await _storage.delete(key: _keyToken);
  }

  /// Vérifier si un token existe
  Future<bool> hasToken() async {
    final token = await getToken();
    return token != null && token.isNotEmpty;
  }

  // ========== USER ==========

  /// Sauvegarder les données utilisateur
  Future<void> saveUser(User user) async {
    final userJson = jsonEncode(user.toJson());
    await _storage.write(key: _keyUser, value: userJson);
  }

  /// Récupérer les données utilisateur
  Future<User?> getUser() async {
    try {
      final userJson = await _storage.read(key: _keyUser);
      if (userJson == null) return null;

      final userMap = jsonDecode(userJson) as Map<String, dynamic>;
      return User.fromJson(userMap);
    } catch (e) {
      print('❌ Error reading user from storage: $e');
      return null;
    }
  }

  /// Supprimer les données utilisateur
  Future<void> deleteUser() async {
    await _storage.delete(key: _keyUser);
  }

  // ========== REMEMBER ME ==========

  /// Sauvegarder la préférence "Se souvenir de moi"
  Future<void> setRememberMe(bool value) async {
    await _storage.write(key: _keyRememberMe, value: value.toString());
  }

  /// Récupérer la préférence "Se souvenir de moi"
  Future<bool> getRememberMe() async {
    final value = await _storage.read(key: _keyRememberMe);
    return value == 'true';
  }

  // ========== CLEAR ALL ==========

  /// Supprimer toutes les données stockées
  Future<void> clearAll() async {
    await _storage.deleteAll();
  }

  /// Supprimer uniquement les données d'authentification
  Future<void> clearAuth() async {
    await deleteToken();
    await deleteUser();
  }
}
