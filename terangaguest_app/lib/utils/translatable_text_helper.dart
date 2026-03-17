import '../services/translation_service.dart';

/// Contenu traduisible : soit une String (rétrocompat), soit un Map fr/en/es/ar.
/// Utiliser [resolveDisplayText] pour obtenir le texte à afficher, avec traduction
/// on-device si la langue choisie manque (backfill non exécuté).
class TranslatableTextHelper {
  TranslatableTextHelper._();

  /// Retourne le texte à afficher pour [locale].
  /// [content] peut être :
  /// - String : affiché tel quel (ancienne API).
  /// - Map (fr, en, es, ar) : utilise content[locale] si présent, sinon traduit content['fr'] vers locale.
  static Future<String> resolveDisplayText(dynamic content, String locale) async {
    if (content == null) return '';
    if (content is String) {
      // Pour les chaînes "en dur" (anciennes ou labels server simples)
      final lower = content.toLowerCase().trim();
      // On normalise les termes connus pour faciliter la traduction par le widget TranslatableText
      if (lower == 'restaurant' || lower == 'restaurants') return 'restaurant';
      if (lower == 'bar' || lower == 'bars') return 'bar';
      if (lower == 'cafe' || lower == 'café' || lower == 'cafes' || lower == 'cafés' || lower == 'caffe' || lower == 'caffes') return 'cafe';
      if (lower == 'lounge' || lower == 'lounges') return 'lounge';
      return content;
    }
    if (content is! Map) return content.toString();

    final map = content;
    final String? forLocale = _stringOrNull(map[locale]);
    if (forLocale != null && forLocale.trim().isNotEmpty) return forLocale;

    final String? fr = _stringOrNull(map['fr']);
    if (fr == null || fr.trim().isEmpty) {
      final en = _stringOrNull(map['en']);
      final es = _stringOrNull(map['es']);
      final ar = _stringOrNull(map['ar']);
      final any = en ?? es ?? ar ?? '';
      return any;
    }

    if (locale == 'fr') return fr;
    final translated = await translateText(fr, locale);
    return translated;
  }

  /// Synchrone : retourne le texte pour [locale] si disponible, sinon le français (sans traduire).
  static String resolveDisplayTextSync(dynamic content, String locale) {
    if (content == null) return '';
    if (content is String) {
      final lower = content.toLowerCase().trim();
      if (lower == 'restaurant' || lower == 'restaurants') return 'restaurant';
      if (lower == 'bar' || lower == 'bars') return 'bar';
      if (lower == 'cafe' || lower == 'café' || lower == 'cafes' || lower == 'cafés' || lower == 'caffe' || lower == 'caffes') return 'cafe';
      if (lower == 'lounge' || lower == 'lounges') return 'lounge';
      return content;
    }
    if (content is! Map) return content.toString();

    final map = content;
    final String? forLocale = _stringOrNull(map[locale]);
    if (forLocale != null && forLocale.trim().isNotEmpty) return forLocale;
    final String? fr = _stringOrNull(map['fr']);
    if (fr != null && fr.trim().isNotEmpty) return fr;
    final en = _stringOrNull(map['en']);
    final es = _stringOrNull(map['es']);
    final ar = _stringOrNull(map['ar']);
    return en ?? es ?? ar ?? '';
  }

  static String? _stringOrNull(dynamic v) {
    if (v == null) return null;
    if (v is String) return v;
    return v.toString();
  }
}
