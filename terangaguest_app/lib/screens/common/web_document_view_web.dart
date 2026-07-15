import 'dart:html' as html;
import 'dart:ui_web' as ui_web;

import 'package:flutter/widgets.dart';

class WebDocumentView extends StatefulWidget {
  final String url;

  const WebDocumentView({super.key, required this.url});

  @override
  State<WebDocumentView> createState() => _WebDocumentViewState();
}

class _WebDocumentViewState extends State<WebDocumentView> {
  static int _nextId = 0;
  late final String _viewType;

  @override
  void initState() {
    super.initState();
    _viewType = 'web-document-view-${_nextId++}';
    final iframeUrl = _buildIframeUrl(widget.url);

    ui_web.platformViewRegistry.registerViewFactory(_viewType, (int viewId) {
      final iframe = html.IFrameElement()
        ..src = iframeUrl
        ..style.border = '0'
        ..style.display = 'block'
        ..style.width = '100%'
        ..style.height = '100%'
        ..style.overflow = 'auto'
        ..style.touchAction = 'auto'
        ..style.backgroundColor = 'transparent'
        ..setAttribute('scrolling', 'yes')
        ..allowFullscreen = true;

      final container = html.DivElement()
        ..style.width = '100%'
        ..style.height = '100%'
        ..style.overflow = 'auto'
        ..style.touchAction = 'auto'
        ..style.setProperty('-webkit-overflow-scrolling', 'touch');

      container.children.add(iframe);
      return container;
    });
  }

  String _buildIframeUrl(String url) {
    final trimmed = url.trim();
    final lower = trimmed.toLowerCase();
    if (lower.endsWith('.pdf') || lower.contains('.pdf?')) {
      return 'pdf_viewer.html?file=${Uri.encodeComponent(trimmed)}';
    }
    return trimmed;
  }

  @override
  Widget build(BuildContext context) {
    return HtmlElementView(viewType: _viewType);
  }
}
