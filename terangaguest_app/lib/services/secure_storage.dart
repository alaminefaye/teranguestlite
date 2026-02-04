import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/user.dart';

class SecureStorage {
  static final SecureStorage _instance = SecureStorage._internal();
  late FlutterSecureStorage _storage;
  
  // Flags pour savoir quel storage fonctionne
  bool _useSecureStorage = true;
  bool _useSharedPreferences = true;
  SharedPreferences? _prefs;
  
  // Storage en mémoire comme dernier recours (ne persiste pas)
  final Map<String, String> _memoryStorage = {};

  factory SecureStorage() {
    return _instance;
  }

  SecureStorage._internal() {
    _storage = const FlutterSecureStorage(
      aOptions: AndroidOptions(
        encryptedSharedPreferences: true,
      ),
      iOptions: IOSOptions(
        accessibility: KeychainAccessibility.first_unlock,
      ),
    );
  }

  // Clés de stockage
  static const String _keyToken = 'auth_token';
  static const String _keyUser = 'user_data';
  static const String _keyRememberMe = 'remember_me';

  /// Initialiser SharedPreferences comme fallback
  Future<void> _initPrefs() async {
    if (_prefs == null && _useSharedPreferences) {
      try {
        _prefs = await SharedPreferences.getInstance();
      } catch (e) {
        debugPrint('⚠️ SharedPreferences failed, using in-memory storage: $e');
        _useSharedPreferences = false;
      }
    }
  }

  /// Méthode générique d'écriture avec fallback automatique (3 niveaux)
  Future<void> _writeSecure(String key, String value) async {
    // Niveau 1 : flutter_secure_storage (préféré)
    if (_useSecureStorage) {
      try {
        await _storage.write(key: key, value: value);
        return;
      } catch (e) {
        debugPrint('⚠️ Secure storage failed: $e');
        _useSecureStorage = false;
      }
    }
    
    // Niveau 2 : SharedPreferences (fallback 1)
    if (_useSharedPreferences) {
      try {
        await _initPrefs();
        if (_prefs != null) {
          await _prefs!.setString(key, value);
          return;
        }
      } catch (e) {
        debugPrint('⚠️ SharedPreferences failed: $e');
        _useSharedPreferences = false;
      }
    }
    
    // Niveau 3 : Mémoire (fallback ultime - ne persiste pas)
    debugPrint('ℹ️ Using in-memory storage (non-persistent)');
    _memoryStorage[key] = value;
  }

  /// Méthode générique de lecture avec fallback automatique (3 niveaux)
  Future<String?> _readSecure(String key) async {
    // Niveau 1 : flutter_secure_storage (préféré)
    if (_useSecureStorage) {
      try {
        return await _storage.read(key: key);
      } catch (e) {
        debugPrint('⚠️ Secure storage failed: $e');
        _useSecureStorage = false;
      }
    }
    
    // Niveau 2 : SharedPreferences (fallback 1)
    if (_useSharedPreferences) {
      try {
        await _initPrefs();
        if (_prefs != null) {
          return _prefs!.getString(key);
        }
      } catch (e) {
        debugPrint('⚠️ SharedPreferences failed: $e');
        _useSharedPreferences = false;
      }
    }
    
    // Niveau 3 : Mémoire (fallback ultime)
    return _memoryStorage[key];
  }

  /// Méthode générique de suppression avec fallback automatique (3 niveaux)
  Future<void> _deleteSecure(String key) async {
    // Niveau 1 : flutter_secure_storage (préféré)
    if (_useSecureStorage) {
      try {
        await _storage.delete(key: key);
        return;
      } catch (e) {
        debugPrint('⚠️ Secure storage failed: $e');
        _useSecureStorage = false;
      }
    }
    
    // Niveau 2 : SharedPreferences (fallback 1)
    if (_useSharedPreferences) {
      try {
        await _initPrefs();
        if (_prefs != null) {
          await _prefs!.remove(key);
          return;
        }
      } catch (e) {
        debugPrint('⚠️ SharedPreferences failed: $e');
        _useSharedPreferences = false;
      }
    }
    
    // Niveau 3 : Mémoire (fallback ultime)
    _memoryStorage.remove(key);
  }

  // ========== TOKEN ==========

  /// Sauvegarder le token d'authentification
  Future<void> saveToken(String token) async {
    await _writeSecure(_keyToken, token);
  }

  /// Récupérer le token d'authentification
  Future<String?> getToken() async {
    return await _readSecure(_keyToken);
  }

  /// Supprimer le token d'authentification
  Future<void> deleteToken() async {
    await _deleteSecure(_keyToken);
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
    await _writeSecure(_keyUser, userJson);
  }

  /// Récupérer les données utilisateur
  Future<User?> getUser() async {
    try {
      final userJson = await _readSecure(_keyUser);
      if (userJson == null) return null;

      final userMap = jsonDecode(userJson) as Map<String, dynamic>;
      return User.fromJson(userMap);
    } catch (e) {
      debugPrint('❌ Error reading user from storage: $e');
      return null;
    }
  }

  /// Supprimer les données utilisateur
  Future<void> deleteUser() async {
    await _deleteSecure(_keyUser);
  }

  // ========== REMEMBER ME ==========

  /// Sauvegarder la préférence "Se souvenir de moi"
  Future<void> setRememberMe(bool value) async {
    await _writeSecure(_keyRememberMe, value.toString());
  }

  /// Récupérer la préférence "Se souvenir de moi"
  Future<bool> getRememberMe() async {
    final value = await _readSecure(_keyRememberMe);
    return value == 'true';
  }

  // ========== CLEAR ALL ==========

  /// Supprimer toutes les données stockées
  Future<void> clearAll() async {
    // Niveau 1 : flutter_secure_storage
    if (_useSecureStorage) {
      try {
        await _storage.deleteAll();
      } catch (e) {
        debugPrint('⚠️ Secure storage failed: $e');
        _useSecureStorage = false;
      }
    }
    
    // Niveau 2 : SharedPreferences
    if (_useSharedPreferences) {
      try {
        await _initPrefs();
        await _prefs?.clear();
      } catch (e) {
        debugPrint('⚠️ SharedPreferences failed: $e');
        _useSharedPreferences = false;
      }
    }
    
    // Niveau 3 : Mémoire
    _memoryStorage.clear();
  }

  /// Supprimer uniquement les données d'authentification
  Future<void> clearAuth() async {
    await deleteToken();
    await deleteUser();
  }
}
