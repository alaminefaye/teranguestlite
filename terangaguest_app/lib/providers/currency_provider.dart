import 'package:flutter/foundation.dart';
import 'package:intl/intl.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../services/currency_service.dart';

enum DisplayCurrency {
  fcfa,
  eur,
  usd,
}

extension DisplayCurrencyExt on DisplayCurrency {
  String get code {
    switch (this) {
      case DisplayCurrency.fcfa:
        return 'XOF';
      case DisplayCurrency.eur:
        return 'EUR';
      case DisplayCurrency.usd:
        return 'USD';
    }
  }

  String get symbol {
    switch (this) {
      case DisplayCurrency.fcfa:
        return 'FCFA';
      case DisplayCurrency.eur:
        return '€';
      case DisplayCurrency.usd:
        return '\$';
    }
  }
}

/// Gère la devise d'affichage (FCFA / EUR / USD) et le formatage des prix
/// avec conversion automatique selon les taux du jour (API gratuite).
class CurrencyProvider extends ChangeNotifier {
  CurrencyProvider({CurrencyService? currencyService})
      : _currencyService = currencyService ?? CurrencyService();

  final CurrencyService _currencyService;
  static const String _prefKey = 'display_currency';

  DisplayCurrency _displayCurrency = DisplayCurrency.fcfa;
  Map<String, double> _rates = {};
  bool _ratesLoaded = false;

  DisplayCurrency get displayCurrency => _displayCurrency;
  Map<String, double> get rates => Map.unmodifiable(_rates);
  bool get ratesLoaded => _ratesLoaded;

  /// Charge les taux et la préférence sauvegardée.
  Future<void> load() async {
    final prefs = await SharedPreferences.getInstance();
    final index = prefs.getInt(_prefKey);
    if (index != null &&
        index >= 0 &&
        index < DisplayCurrency.values.length) {
      _displayCurrency = DisplayCurrency.values[index];
    }
    await refreshRates();
  }

  /// Rafraîchit les taux depuis l'API (respecte le cache 24h du service).
  Future<void> refreshRates() async {
    _rates = await _currencyService.getRates();
    _ratesLoaded = true;
    notifyListeners();
  }

  /// Change la devise d'affichage et persiste.
  Future<void> setDisplayCurrency(DisplayCurrency currency) async {
    if (_displayCurrency == currency) return;
    _displayCurrency = currency;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setInt(
        _prefKey, DisplayCurrency.values.indexOf(currency));
    notifyListeners();
  }

  /// Convertit un montant FCFA vers la devise d'affichage.
  double convertFromFcfa(double amountFcfa) {
    if (amountFcfa.isNaN || !amountFcfa.isFinite) return 0;
    switch (_displayCurrency) {
      case DisplayCurrency.fcfa:
        return amountFcfa;
      case DisplayCurrency.eur:
        final rate = _rates['EUR'] ?? 0.00152;
        return amountFcfa * rate;
      case DisplayCurrency.usd:
        final rate = _rates['USD'] ?? 0.00165;
        return amountFcfa * rate;
    }
  }

  /// Formate un prix (montant en FCFA) dans la devise d'affichage avec symbole.
  /// Ex: 65000 FCFA → "65 000 FCFA" ou "99.12 €" ou "107.25 \$"
  String formatPrice(double amountFcfa) {
    final converted = convertFromFcfa(amountFcfa);
    if (_displayCurrency == DisplayCurrency.fcfa) {
      return '${NumberFormat('#,##0', 'fr_FR').format(converted.round())} FCFA';
    }
    final format = NumberFormat.currency(
      locale: 'fr_FR',
      symbol: _displayCurrency.symbol,
      decimalDigits: 2,
    );
    return format.format(converted);
  }

  /// Même chose pour un montant déjà en FCFA (int ou double).
  String formatPriceNum(num amountFcfa) => formatPrice(amountFcfa.toDouble());
}
