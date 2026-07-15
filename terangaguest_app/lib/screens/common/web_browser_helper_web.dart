import 'dart:html' as html;

bool isSafariWebBrowser() {
  final ua = html.window.navigator.userAgent.toLowerCase();
  final hasSafari = ua.contains('safari');
  final hasOtherBrowserToken =
      ua.contains('crios') ||
      ua.contains('chrome') ||
      ua.contains('edgios') ||
      ua.contains('fxios') ||
      ua.contains('opr/') ||
      ua.contains('opios') ||
      ua.contains('samsungbrowser');
  return hasSafari && !hasOtherBrowserToken;
}

String buildWebPdfLaunchUrl(String url) {
  final trimmed = url.trim();
  final lower = trimmed.toLowerCase();
  final isPdf =
      lower.endsWith('.pdf') ||
      lower.contains('.pdf?') ||
      lower.contains('docs.google.com/viewer') ||
      lower.contains('docs.google.com/gview');

  if (!isPdf) return trimmed;
  if (isSafariWebBrowser()) return _extractOriginalPdfUrl(trimmed);

  final originalUrl = _extractOriginalPdfUrl(trimmed);
  return 'https://docs.google.com/viewer?embedded=true&url=${Uri.encodeComponent(originalUrl)}';
}

String _extractOriginalPdfUrl(String url) {
  final uri = Uri.tryParse(url);
  if (uri == null) return url;

  final host = uri.host.toLowerCase();
  final isGoogleViewer =
      host.contains('docs.google.com') &&
      (uri.path.contains('/viewer') || uri.path.contains('/gview'));
  if (!isGoogleViewer) return url;

  final nestedUrl = uri.queryParameters['url'];
  return nestedUrl == null || nestedUrl.isEmpty ? url : nestedUrl;
}
