import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:url_launcher/url_launcher.dart';
import 'package:webview_flutter/webview_flutter.dart';
import '../../config/theme.dart';
import '../../utils/haptic_helper.dart';
import 'web_browser_helper_stub.dart'
    if (dart.library.html) 'web_browser_helper_web.dart';
import 'web_document_view_stub.dart'
    if (dart.library.html) 'web_document_view_web.dart';

class InAppDocumentScreen extends StatefulWidget {
  final String title;
  final String url;

  const InAppDocumentScreen({
    super.key,
    required this.title,
    required this.url,
  });

  @override
  State<InAppDocumentScreen> createState() => _InAppDocumentScreenState();
}

class _InAppDocumentScreenState extends State<InAppDocumentScreen> {
  WebViewController? _controller;
  bool _isLoading = true;
  bool _hasError = false;
  bool _openDirectlyOnWeb = false;

  @override
  void initState() {
    super.initState();
    if (kIsWeb) {
      _openDirectlyOnWeb = _shouldOpenDirectlyOnWeb(widget.url);
      _isLoading = false;
      if (_openDirectlyOnWeb) {
        WidgetsBinding.instance.addPostFrameCallback((_) {
          _openExternally();
        });
      }
      return;
    }

    final loadUrl = _buildMobileViewerUrl(widget.url);
    _controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setNavigationDelegate(
        NavigationDelegate(
          onPageStarted: (_) {
            if (mounted) {
              setState(() {
                _isLoading = true;
                _hasError = false;
              });
            }
          },
          onPageFinished: (_) {
            if (mounted) setState(() => _isLoading = false);
          },
          onWebResourceError: (_) {
            if (mounted) {
              setState(() {
                _isLoading = false;
                _hasError = true;
              });
            }
          },
        ),
      )
      ..loadRequest(Uri.parse(loadUrl));
  }

  /// Sur mobile, on passe par Google Docs Viewer pour certains PDF.
  String _buildMobileViewerUrl(String url) {
    final lower = url.toLowerCase();
    if (lower.endsWith('.pdf') || lower.contains('.pdf?')) {
      return 'https://docs.google.com/viewer?embedded=true&url=${Uri.encodeComponent(url)}';
    }
    return url;
  }

  bool _shouldOpenDirectlyOnWeb(String url) {
    final lower = url.trim().toLowerCase();
    return lower.endsWith('.pdf') ||
        lower.contains('.pdf?') ||
        lower.contains('docs.google.com/viewer') ||
        lower.contains('docs.google.com/gview');
  }

  Future<void> _openExternally() async {
    final targetUrl = kIsWeb
        ? buildWebPdfLaunchUrl(widget.url, title: widget.title)
        : widget.url;
    final uri = Uri.tryParse(targetUrl);
    if (uri == null) return;
    await launchUrl(
      uri,
      mode: LaunchMode.platformDefault,
      webOnlyWindowName: kIsWeb ? '_self' : null,
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(gradient: AppTheme.backgroundGradient),
        child: SafeArea(
          child: Column(
            children: [
              Padding(
                padding: const EdgeInsets.symmetric(
                  horizontal: 8,
                  vertical: 12,
                ),
                child: Row(
                  children: [
                    IconButton(
                      icon: const Icon(
                        Icons.arrow_back,
                        color: AppTheme.accentGold,
                      ),
                      onPressed: () {
                        HapticHelper.lightImpact();
                        Navigator.of(context).pop();
                      },
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        widget.title,
                        style: const TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                          color: AppTheme.accentGold,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                    IconButton(
                      icon: const Icon(
                        Icons.open_in_new,
                        color: AppTheme.accentGold,
                      ),
                      onPressed: _openExternally,
                    ),
                    if (_isLoading)
                      const Padding(
                        padding: EdgeInsets.only(right: 12),
                        child: SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            valueColor: AlwaysStoppedAnimation<Color>(
                              AppTheme.accentGold,
                            ),
                          ),
                        ),
                      ),
                  ],
                ),
              ),
              Expanded(
                child: kIsWeb
                    ? _openDirectlyOnWeb
                        ? _WebOpenFallback(
                            title: widget.title,
                            onOpenExternally: _openExternally,
                          )
                        : WebDocumentView(url: widget.url)
                    : _hasError
                    ? _ErrorView(
                        onRetry: () {
                          setState(() {
                            _isLoading = true;
                            _hasError = false;
                          });
                          _controller?.reload();
                        },
                        onOpenExternally: _openExternally,
                      )
                    : WebViewWidget(controller: _controller!),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _WebOpenFallback extends StatelessWidget {
  final String title;
  final VoidCallback onOpenExternally;

  const _WebOpenFallback({
    required this.title,
    required this.onOpenExternally,
  });

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(
              Icons.picture_as_pdf_outlined,
              color: AppTheme.accentGold,
              size: 56,
            ),
            const SizedBox(height: 16),
            Text(
              title,
              textAlign: TextAlign.center,
              style: const TextStyle(
                color: Colors.white,
                fontSize: 18,
                fontWeight: FontWeight.w700,
              ),
            ),
            const SizedBox(height: 10),
            const Text(
              'Le document PDF est ouvert avec le mode le plus compatible pour garder un scroll normal sur le web mobile.',
              textAlign: TextAlign.center,
              style: TextStyle(
                color: AppTheme.textGray,
                fontSize: 14,
                height: 1.4,
              ),
            ),
            const SizedBox(height: 20),
            ElevatedButton(
              onPressed: onOpenExternally,
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.accentGold,
                foregroundColor: Colors.black,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
              child: const Text('Ouvrir le document'),
            ),
          ],
        ),
      ),
    );
  }
}

class _ErrorView extends StatelessWidget {
  final VoidCallback onRetry;
  final VoidCallback onOpenExternally;

  const _ErrorView({required this.onRetry, required this.onOpenExternally});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(
              Icons.error_outline,
              color: AppTheme.accentGold,
              size: 56,
            ),
            const SizedBox(height: 16),
            const Text(
              'Impossible de charger le document',
              textAlign: TextAlign.center,
              style: TextStyle(
                color: Colors.white,
                fontSize: 16,
                fontWeight: FontWeight.w600,
              ),
            ),
            const SizedBox(height: 8),
            const Text(
              'Vérifiez votre connexion et réessayez.',
              textAlign: TextAlign.center,
              style: TextStyle(color: AppTheme.textGray),
            ),
            const SizedBox(height: 24),
            ElevatedButton(
              onPressed: onRetry,
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.accentGold,
                foregroundColor: Colors.black,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
              child: const Text('Réessayer'),
            ),
            const SizedBox(height: 12),
            TextButton(
              onPressed: onOpenExternally,
              child: const Text(
                'Ouvrir le document',
                style: TextStyle(color: AppTheme.accentGold),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
