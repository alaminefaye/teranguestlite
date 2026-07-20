import 'dart:ui';
import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../services/api_service.dart';

const String _keyLocale = 'app_locale';

/// Supported: fr, en. Default: fr.
class LocaleProvider with ChangeNotifier {
  LocaleProvider({
    Locale initialLocale = const Locale('fr'),
    bool loaded = false,
  }) : _locale = Locale(_supported(initialLocale.languageCode)),
       _loaded = loaded;

  Locale _locale;
  bool _loaded;

  Locale get locale => _locale;
  bool get isLoaded => _loaded;

  static Future<String> loadInitialLanguageCode() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final code = prefs.getString(_keyLocale) ?? 'fr';
      return _supported(code);
    } catch (_) {
      return 'fr';
    }
  }

  Future<void> load() async {
    if (_loaded) return;
    try {
      final prefs = await SharedPreferences.getInstance();
      final code = prefs.getString(_keyLocale) ?? 'fr';
      final validCode = _supported(code);
      _locale = Locale(validCode);
      ApiService().setLanguage(validCode);
      _loaded = true;
      notifyListeners();
    } catch (e) {
      debugPrint('LocaleProvider.load error: $e');
      _loaded = true;
      notifyListeners();
    }
  }

  static String _supported(String code) {
    if (code == 'en') return code;
    return 'fr';
  }

  Future<void> setLocale(Locale value) async {
    final validCode = _supported(value.languageCode);
    if (_locale.languageCode == validCode) return;
    _locale = Locale(validCode);
    ApiService().setLanguage(validCode);
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(_keyLocale, validCode);
    } catch (e) {
      debugPrint('LocaleProvider.setLocale error: $e');
    }
    notifyListeners();
  }

  String get languageCode => _locale.languageCode;
}
