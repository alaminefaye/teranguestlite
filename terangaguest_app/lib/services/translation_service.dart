// Traduction on-device (ML Kit) en fallback quand l'API n'a pas la langue.
// Impl sur Android/iOS ; stub sur web (retourne le texte source).
import 'translation_service_impl.dart' if (dart.library.html) 'translation_service_stub.dart' as impl;

Future<String> translateText(String text, String targetLang) =>
    impl.translateText(text, targetLang);
