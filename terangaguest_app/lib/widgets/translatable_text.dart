import 'package:flutter/material.dart';
import '../generated/l10n/app_localizations.dart';
import '../utils/translatable_text_helper.dart';

/// Affiche un contenu traduisible (String ou Map fr/en/es/ar).
/// Si la traduction pour la locale manque, affiche d'abord le fallback (fr) puis
/// la traduction on-device une fois disponible.
class TranslatableText extends StatefulWidget {
  const TranslatableText(
    this.content, {
    super.key,
    required this.locale,
    this.style,
    this.textAlign,
    this.maxLines,
    this.overflow,
  });

  final dynamic content;
  final String locale;
  final TextStyle? style;
  final TextAlign? textAlign;
  final int? maxLines;
  final TextOverflow? overflow;

  @override
  State<TranslatableText> createState() => _TranslatableTextState();
}

class _TranslatableTextState extends State<TranslatableText> {
  String? _resolved;

  @override
  void initState() {
    super.initState();
    _resolve();
  }

  @override
  void didUpdateWidget(TranslatableText oldWidget) {
    if (oldWidget.content != widget.content || oldWidget.locale != widget.locale) {
      _resolve();
    }
    super.didUpdateWidget(oldWidget);
  }

  Future<void> _resolve() async {
    final sync = TranslatableTextHelper.resolveDisplayTextSync(widget.content, widget.locale);
    setState(() => _resolved = sync);
    if (sync.isEmpty || (widget.content is Map && (widget.content as Map)[widget.locale] == null)) {
      final async = await TranslatableTextHelper.resolveDisplayText(widget.content, widget.locale);
      if (mounted && async != _resolved) {
        setState(() => _resolved = async);
      }
    }
  }

  String _localizeIfKeyword(BuildContext context, String text) {
    if (text.isEmpty) return text;
    final lower = text.toLowerCase().trim();
    // On essaye de voir si c'est un type de restaurant connu
    try {
      final l10n = AppLocalizations.of(context);
      if (lower == 'restaurant' || lower == 'restaurants') return l10n.typeRestaurant;
      if (lower == 'bar' || lower == 'bars') return l10n.typeBar;
      if (lower == 'cafe' || lower == 'café' || lower == 'cafes' || lower == 'cafés' || lower == 'caffe' || lower == 'caffes') {
        return l10n.typeCafe;
      }
      if (lower == 'lounge' || lower == 'lounges') return l10n.typeLounge;
    } catch (_) {
      // Si AppLocalizations n'est pas dispo dans ce context
    }
    return text;
  }

  @override
  Widget build(BuildContext context) {
    final displayText = _resolved ?? '';
    final finalVisibleText = _localizeIfKeyword(context, displayText);

    return Text(
      finalVisibleText,
      style: widget.style,
      textAlign: widget.textAlign,
      maxLines: widget.maxLines,
      overflow: widget.overflow,
    );
  }
}
