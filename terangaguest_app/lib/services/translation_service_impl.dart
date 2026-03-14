import 'package:google_mlkit_translation/google_mlkit_translation.dart';

final Map<String, String> _cache = {};
const String _sourceLang = 'fr';

TranslateLanguage _targetLanguage(String lang) {
  switch (lang) {
    case 'en': return TranslateLanguage.english;
    case 'es': return TranslateLanguage.spanish;
    case 'ar': return TranslateLanguage.arabic;
    default: return TranslateLanguage.english;
  }
}

Future<String> translateText(String text, String targetLang) async {
  if (text.isEmpty || targetLang == _sourceLang) return text;
  final trimmed = text.trim();
  if (trimmed.isEmpty) return text;

  final cacheKey = '$_sourceLang|$targetLang|$trimmed';
  if (_cache.containsKey(cacheKey)) return _cache[cacheKey]!;

  try {
    final target = _targetLanguage(targetLang);
    final modelManager = OnDeviceTranslatorModelManager();
    await modelManager.downloadModel(TranslateLanguage.french.bcpCode);
    await modelManager.downloadModel(target.bcpCode);
    final translator = OnDeviceTranslator(
      sourceLanguage: TranslateLanguage.french,
      targetLanguage: target,
    );
    final result = await translator.translateText(trimmed);
    await translator.close();
    if (result.isNotEmpty) {
      _cache[cacheKey] = result;
      return result;
    }
  } catch (_) {}
  return trimmed;
}
