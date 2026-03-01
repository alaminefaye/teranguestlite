import 'package:flutter/foundation.dart';
import '../models/user.dart';
import '../services/auth_service.dart';
import '../services/fcm_service.dart';

class AuthProvider with ChangeNotifier {
  final AuthService _authService = AuthService();
  final FcmService _fcmService = FcmService();

  User? _user;
  bool _isAuthenticated = false;
  bool _isLoading = true;
  String? _errorMessage;

  // Getters
  User? get user => _user;
  bool get isAuthenticated => _isAuthenticated;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  bool get isGuest => _user?.isGuest ?? false;
  bool get isStaff => _user?.isStaff ?? false;
  bool get isAdmin => _user?.isAdmin ?? false;

  /// Initialiser l'authentification au démarrage
  Future<void> initAuth() async {
    _isLoading = true;
    notifyListeners();

    try {
      final user = await _authService.initAuth();

      if (user != null) {
        _user = user;
        _isAuthenticated = true;
        _fcmService.registerTokenIfNeeded();
      } else {
        _user = null;
        _isAuthenticated = false;
      }
    } catch (e) {
      debugPrint('❌ Error init auth: $e');
      _user = null;
      _isAuthenticated = false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  /// Login avec email et mot de passe
  Future<bool> login({
    required String email,
    required String password,
    bool rememberMe = false,
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final result = await _authService.login(
        email: email,
        password: password,
        rememberMe: rememberMe,
      );

      _user = result['user'] as User;
      _isAuthenticated = true;
      _isLoading = false;
      notifyListeners();
      _fcmService.registerTokenIfNeeded();

      return true;
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
      _isLoading = false;
      _isAuthenticated = false;
      notifyListeners();

      return false;
    }
  }

  /// Login Web avec Code Client (QR Code)
  Future<bool> webLogin({required String clientCode}) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final result = await _authService.webLogin(clientCode: clientCode);

      _user = result['user'] as User;
      _isAuthenticated = true;
      _isLoading = false;
      notifyListeners();

      // On web we might not strictly need FCM but the method is safe
      _fcmService.registerTokenIfNeeded();

      return true;
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
      _isLoading = false;
      _isAuthenticated = false;
      notifyListeners();

      return false;
    }
  }

  /// Logout
  Future<void> logout() async {
    _isLoading = true;
    notifyListeners();

    try {
      await _fcmService.unregisterToken();
      await _authService.logout();
    } catch (e) {
      debugPrint('⚠️ Logout error: $e');
    } finally {
      _user = null;
      _isAuthenticated = false;
      _isLoading = false;
      _errorMessage = null;
      notifyListeners();
    }
  }

  /// Recharger les informations utilisateur
  Future<void> loadUser() async {
    if (!_isAuthenticated) return;

    try {
      final user = await _authService.getCurrentUser();
      _user = user;
      notifyListeners();
    } catch (e) {
      debugPrint('❌ Error loading user: $e');

      // Si erreur 401, déconnecter
      if (e.toString().contains('Session expirée')) {
        await logout();
      }
    }
  }

  /// Changer le mot de passe
  Future<bool> changePassword({
    required String currentPassword,
    required String newPassword,
    required String newPasswordConfirmation,
  }) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      await _authService.changePassword(
        currentPassword: currentPassword,
        newPassword: newPassword,
        newPasswordConfirmation: newPasswordConfirmation,
      );

      _isLoading = false;
      notifyListeners();

      return true;
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
      _isLoading = false;
      notifyListeners();

      return false;
    }
  }

  /// Mettre à jour l'utilisateur
  void updateUser(User user) {
    _user = user;
    notifyListeners();
  }

  /// Effacer le message d'erreur
  void clearError() {
    _errorMessage = null;
    notifyListeners();
  }

  /// Vider les données (pour cleanup)
  void clear() {
    _user = null;
    _isAuthenticated = false;
    _isLoading = false;
    _errorMessage = null;
    notifyListeners();
  }
}
