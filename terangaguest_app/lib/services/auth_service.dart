import 'package:flutter/foundation.dart';
import 'package:dio/dio.dart';
import '../config/api_config.dart';
import '../models/user.dart';
import 'api_service.dart';
import 'secure_storage.dart';

class AuthService {
  final ApiService _apiService = ApiService();
  final SecureStorage _secureStorage = SecureStorage();

  /// Login avec email et mot de passe
  Future<Map<String, dynamic>> login({
    required String email,
    required String password,
    bool rememberMe = false,
  }) async {
    try {
      final response = await _apiService.post(
        ApiConfig.login,
        data: {'email': email, 'password': password},
      );

      if (response.statusCode == 200) {
        final data = response.data;

        if (data['success'] == true) {
          final userData = data['data']['user'];
          final token = data['data']['token'] as String;

          // Créer l'objet User
          final user = User.fromJson(userData);

          // Sauvegarder le token et l'utilisateur
          await _secureStorage.saveToken(token);
          await _secureStorage.saveUser(user);
          await _secureStorage.setRememberMe(rememberMe);

          // Définir le token dans ApiService pour les futures requêtes
          _apiService.setAuthToken(token);

          return {'user': user, 'token': token};
        } else {
          throw Exception(data['message'] ?? 'Erreur de connexion');
        }
      } else {
        throw Exception('Erreur serveur: ${response.statusCode}');
      }
    } on DioException catch (e) {
      if (e.response != null) {
        final errorData = e.response?.data;
        if (errorData is Map && errorData.containsKey('message')) {
          throw Exception(errorData['message']);
        } else if (errorData is Map && errorData.containsKey('errors')) {
          // Erreurs de validation
          final errors = errorData['errors'] as Map<String, dynamic>;
          final firstError = errors.values.first;
          if (firstError is List && firstError.isNotEmpty) {
            throw Exception(firstError.first);
          }
        }
        throw Exception('Identifiants invalides');
      } else {
        throw Exception('Impossible de se connecter au serveur');
      }
    }
  }

  /// Login Web avec Code Client
  Future<Map<String, dynamic>> webLogin({required String clientCode}) async {
    try {
      final response = await _apiService.post(
        ApiConfig.webLogin,
        data: {'client_code': clientCode},
      );

      if (response.statusCode == 200) {
        final data = response.data;

        if (data['success'] == true) {
          final userData = data['data']['user'];
          final token = data['data']['token'] as String;

          final user = User.fromJson(userData);

          await _secureStorage.saveToken(token);
          await _secureStorage.saveUser(user);
          await _secureStorage.setRememberMe(
            true,
          ); // Toujours se souvenir sur web client

          _apiService.setAuthToken(token);

          return {'user': user, 'token': token};
        } else {
          throw Exception(data['message'] ?? 'Erreur de connexion');
        }
      } else {
        throw Exception('Erreur serveur: ${response.statusCode}');
      }
    } on DioException catch (e) {
      if (e.response != null) {
        final errorData = e.response?.data;
        if (errorData is Map && errorData.containsKey('message')) {
          throw Exception(errorData['message']);
        }
        throw Exception('Code client invalide');
      } else {
        throw Exception('Impossible de se connecter au serveur');
      }
    }
  }

  /// Logout
  Future<void> logout() async {
    try {
      // Appeler l'API de déconnexion (optionnel)
      await _apiService.post(ApiConfig.logout);
    } catch (e) {
      // Continuer même en cas d'erreur API
      debugPrint('⚠️ Logout API error (non-blocking): $e');
    } finally {
      // Supprimer les données locales
      await _secureStorage.clearAuth();
      _apiService.removeAuthToken();
    }
  }

  /// Récupérer l'utilisateur actuel depuis l'API
  Future<User> getCurrentUser() async {
    try {
      final response = await _apiService.get(ApiConfig.user);

      if (response.statusCode == 200) {
        final data = response.data;
        // API renvoie { "success": true, "data": { ... user ... } }
        final Map<String, dynamic> userData =
            data is Map && data['data'] != null
            ? data['data'] as Map<String, dynamic>
            : data as Map<String, dynamic>;
        final user = User.fromJson(userData);

        // Mettre à jour le stockage local
        await _secureStorage.saveUser(user);

        return user;
      } else {
        throw Exception('Erreur serveur: ${response.statusCode}');
      }
    } on DioException catch (e) {
      if (e.response?.statusCode == 401) {
        // Token invalide ou expiré
        throw Exception('Session expirée. Veuillez vous reconnecter.');
      }
      throw Exception('Impossible de récupérer les informations utilisateur');
    }
  }

  /// Changer le mot de passe
  Future<void> changePassword({
    required String currentPassword,
    required String newPassword,
    required String newPasswordConfirmation,
  }) async {
    try {
      final response = await _apiService.post(
        ApiConfig.changePassword,
        data: {
          'current_password': currentPassword,
          'password': newPassword,
          'password_confirmation': newPasswordConfirmation,
        },
      );

      if (response.statusCode == 200) {
        final data = response.data;
        if (data['success'] != true) {
          throw Exception(
            data['message'] ?? 'Erreur lors du changement de mot de passe',
          );
        }
      } else {
        throw Exception('Erreur serveur: ${response.statusCode}');
      }
    } on DioException catch (e) {
      if (e.response != null) {
        final errorData = e.response?.data;
        if (errorData is Map && errorData.containsKey('message')) {
          throw Exception(errorData['message']);
        } else if (errorData is Map && errorData.containsKey('errors')) {
          final errors = errorData['errors'] as Map<String, dynamic>;
          final firstError = errors.values.first;
          if (firstError is List && firstError.isNotEmpty) {
            throw Exception(firstError.first);
          }
        }
      }
      throw Exception('Impossible de changer le mot de passe');
    }
  }

  /// Vérifier si l'utilisateur est connecté
  Future<bool> isLoggedIn() async {
    return await _secureStorage.hasToken();
  }

  /// Récupérer l'utilisateur stocké localement
  Future<User?> getStoredUser() async {
    return await _secureStorage.getUser();
  }

  /// Initialiser l'authentification au démarrage de l'app
  Future<User?> initAuth() async {
    try {
      final token = await _secureStorage.getToken();

      if (token == null || token.isEmpty) {
        return null;
      }

      // Définir le token dans ApiService pour les requêtes
      _apiService.setAuthToken(token);

      // Vérifier si le token est toujours valide via l'API
      try {
        final user = await getCurrentUser();
        return user;
      } on DioException catch (e) {
        // 401 = token invalide ou expiré → déconnecter
        if (e.response?.statusCode == 401) {
          await _secureStorage.clearAuth();
          _apiService.removeAuthToken();
          return null;
        }
        // Autre erreur (réseau, timeout, 5xx) → garder la session, restaurer l'utilisateur depuis le stockage
        final storedUser = await _secureStorage.getUser();
        if (storedUser != null) {
          return storedUser;
        }
        await _secureStorage.clearAuth();
        _apiService.removeAuthToken();
        return null;
      } catch (e) {
        // Erreur de parsing ou autre → tenter de restaurer depuis le stockage
        final storedUser = await _secureStorage.getUser();
        if (storedUser != null) {
          return storedUser;
        }
        await _secureStorage.clearAuth();
        _apiService.removeAuthToken();
        return null;
      }
    } catch (e) {
      debugPrint('❌ Error initializing auth: $e');
      return null;
    }
  }
}
