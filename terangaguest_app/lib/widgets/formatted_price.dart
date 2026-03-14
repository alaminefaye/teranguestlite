import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../providers/currency_provider.dart';

/// Affiche un montant FCFA dans la devise choisie par l'utilisateur (FCFA / EUR / USD).
/// Utiliser partout où un prix est affiché pour que le changement de devise soit pris en compte.
class FormattedPrice extends StatelessWidget {
  final double amountFcfa;
  final TextStyle? style;

  const FormattedPrice({
    super.key,
    required this.amountFcfa,
    this.style,
  });

  @override
  Widget build(BuildContext context) {
    final text = context.watch<CurrencyProvider>().formatPrice(amountFcfa);
    return Text(text, style: style);
  }
}
