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
      return html.IFrameElement()
        ..src = iframeUrl
        ..style.border = '0'
        ..style.width = '100%'
        ..style.height = '100%'
        ..style.backgroundColor = 'transparent'
        ..allowFullscreen = true;
    });
  }

  String _buildIframeUrl(String url) {
    final trimmed = url.trim();
    final lower = trimmed.toLowerCase();
    if (lower.endsWith('.pdf') || lower.contains('.pdf?')) {
      final uri = Uri.parse(trimmed);
      if (uri.fragment.isNotEmpty) return trimmed;
      return '$trimmed#toolbar=1&navpanes=0&scrollbar=1&view=FitH';
    }
    return trimmed;
  }

  @override
  Widget build(BuildContext context) {
    return HtmlElementView(viewType: _viewType);
  }
}
