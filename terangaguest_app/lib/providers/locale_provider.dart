import 'dart:ui';
import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';

const String _keyLocale = 'app_locale';

/// Supported: fr, en, ar, es. Default: fr.
class LocaleProvider with ChangeNotifier {
  Locale _locale = const Locale('fr');
  bool _loaded = false;

  Locale get locale => _locale;
  bool get isLoaded => _loaded;

  Future<void> load() async {
    if (_loaded) return;
    try {
      final prefs = await SharedPreferences.getInstance();
      final code = prefs.getString(_keyLocale) ?? 'fr';
      _locale = Locale(_supported(code));
      _loaded = true;
      notifyListeners();
    } catch (e) {
      debugPrint('LocaleProvider.load error: $e');
      _loaded = true;
      notifyListeners();
    }
  }

  static String _supported(String code) {
    if (code == 'en' || code == 'ar' || code == 'es') return code;
    return 'fr';
  }

  Future<void> setLocale(Locale value) async {
    if (_locale == value) return;
    _locale = value;
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString(_keyLocale, _supported(value.languageCode));
    } catch (e) {
      debugPrint('LocaleProvider.setLocale error: $e');
    }
    notifyListeners();
  }

  String get languageCode => _locale.languageCode;
}
