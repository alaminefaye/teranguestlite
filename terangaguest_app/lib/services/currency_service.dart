import 'dart:convert';

import 'package:dio/dio.dart';
import 'package:shared_preferences/shared_preferences.dart';

/// Taux de change XOF (FCFA) vers USD, EUR, etc.
/// Utilise l'API gratuite open.er-api.com (sans clé, pas d'inscription).
/// Données mises à jour environ 1x/jour ; cache local 24h pour limiter les appels.
class CurrencyService {
  CurrencyService({Dio? dio}) : _dio = dio ?? Dio();

  static const String _baseUrl = 'https://open.er-api.com/v6/latest/XOF';
  static const String _cacheKey = 'currency_rates_cache';
  static const String _cacheTimeKey = 'currency_rates_cache_time';
  static const Duration _cacheDuration = Duration(hours: 24);

  final Dio _dio;

  /// Retourne les taux depuis XOF vers d'autres devises (ex: {"USD": 0.001745, "EUR": 0.001524}).
  /// Utilise le cache si encore valide.
  Future<Map<String, double>> getRates() async {
    final prefs = await SharedPreferences.getInstance();
    final cachedJson = prefs.getString(_cacheKey);
    final cacheTime = prefs.getInt(_cacheTimeKey);

    if (cachedJson != null &&
        cacheTime != null &&
        DateTime.now().millisecondsSinceEpoch - cacheTime <
            _cacheDuration.inMilliseconds) {
      try {
        final map = jsonDecode(cachedJson) as Map<String, dynamic>;
        return map.map((k, v) => MapEntry(k, (v as num).toDouble()));
      } catch (_) {
        // cache invalide, on refetch
      }
    }

    try {
      final response = await _dio.get<Map<String, dynamic>>(_baseUrl);
      final data = response.data;
      if (data == null || data['result'] != 'success') {
        return _fallbackRates();
      }
      final rates = data['rates'] as Map<String, dynamic>?;
      if (rates == null || rates.isEmpty) return _fallbackRates();

      final out = <String, double>{};
      for (final e in rates.entries) {
        final v = e.value;
        if (v is num) out[e.key] = v.toDouble();
      }

      await prefs.setString(_cacheKey, jsonEncode(out));
      await prefs.setInt(
          _cacheTimeKey, DateTime.now().millisecondsSinceEpoch);
      return out;
    } catch (e) {
      // Offline ou API down : utiliser cache périmé ou fallback
      if (cachedJson != null) {
        try {
          final map = jsonDecode(cachedJson) as Map<String, dynamic>;
          return map.map((k, v) => MapEntry(k, (v as num).toDouble()));
        } catch (_) {}
      }
      return _fallbackRates();
    }
  }

  /// Taux de secours approximatifs (XOF → USD/EUR) si API indisponible.
  static Map<String, double> _fallbackRates() {
    return {
      'XOF': 1.0,
      'USD': 0.00165,
      'EUR': 0.00152,
    };
  }
}
