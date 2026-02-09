import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../config/theme.dart';
import '../providers/auth_provider.dart';
import '../providers/tablet_session_provider.dart';

/// Affiche le dialogue "Entrez votre code client" comme pour le Room service.
/// Retourne le code validé, ou null si annulé.
/// À appeler avant toute réservation / demande si l'utilisateur n'a pas de séjour valide.
Future<String?> showGuestCodeDialog(BuildContext context) async {
  final tabletSession = context.read<TabletSessionProvider>();
  final authUser = context.read<AuthProvider>().user;
  tabletSession.clearError();

  if ((tabletSession.roomNumber ?? '').trim().isEmpty &&
      authUser?.roomNumber != null &&
      authUser!.roomNumber!.trim().isNotEmpty) {
    await tabletSession.setRoomNumber(authUser.roomNumber!.trim());
  }
  if (!context.mounted) return null;

  final codeController = TextEditingController();
  final roomController = TextEditingController(text: tabletSession.roomNumber);
  String? code;

  final result = await showDialog<String?>(
    context: context,
    barrierDismissible: false,
    builder: (ctx) {
      return Consumer<TabletSessionProvider>(
        builder: (ctx, ts, _) {
          final loading = ts.isLoading;
          final error = ts.error;
          final currentRoom = (ts.roomNumber ?? '').trim();
          final showRoomSetup = currentRoom.isEmpty;

          return AlertDialog(
            backgroundColor: AppTheme.primaryBlue,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(16),
              side: const BorderSide(color: AppTheme.accentGold, width: 1),
            ),
            title: const Text(
              'Code client',
              style: TextStyle(color: AppTheme.accentGold),
            ),
            content: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  const Text(
                    'Entrez le code reçu à l\'enregistrement pour continuer.',
                    style: TextStyle(color: Colors.white70, fontSize: 14),
                  ),
                  const SizedBox(height: 16),
                  if (showRoomSetup) ...[
                    TextField(
                      controller: roomController,
                      decoration: InputDecoration(
                        labelText: 'Numéro de chambre',
                        labelStyle: const TextStyle(color: AppTheme.textGray),
                        filled: true,
                        fillColor: Colors.white.withValues(alpha: 0.1),
                        border: const OutlineInputBorder(),
                        hintText: 'ex: 101',
                      ),
                      keyboardType: TextInputType.number,
                      style: const TextStyle(color: Colors.white),
                    ),
                    const SizedBox(height: 12),
                  ] else ...[
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 14),
                      decoration: BoxDecoration(
                        color: Colors.white.withValues(alpha: 0.08),
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: AppTheme.accentGold.withValues(alpha: 0.3)),
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.bed, color: AppTheme.accentGold, size: 20),
                          const SizedBox(width: 10),
                          Text(
                            'Chambre : ${ts.roomNumber ?? "—"}',
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 16,
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 12),
                  ],
                  TextField(
                    controller: codeController,
                    decoration: const InputDecoration(
                      labelText: 'Code à 6 chiffres',
                      labelStyle: TextStyle(color: AppTheme.textGray),
                      filled: true,
                      fillColor: Colors.white10,
                      border: OutlineInputBorder(),
                    ),
                    keyboardType: TextInputType.number,
                    maxLength: 6,
                    style: const TextStyle(color: Colors.white, letterSpacing: 8),
                    onChanged: (v) => code = v.trim(),
                  ),
                  if (error != null) ...[
                    const SizedBox(height: 8),
                    Text(error, style: const TextStyle(color: Colors.red, fontSize: 12)),
                  ],
                ],
              ),
            ),
            actions: [
              TextButton(
                onPressed: loading ? null : () => Navigator.of(ctx).pop(null),
                child: const Text('Annuler', style: TextStyle(color: Colors.white70)),
              ),
              FilledButton(
                onPressed: loading
                    ? null
                    : () async {
                        final c = (code ?? codeController.text.trim());
                        if (c.isEmpty) {
                          ScaffoldMessenger.of(ctx).showSnackBar(
                            const SnackBar(
                              content: Text('Entrez le code à 6 chiffres.'),
                              backgroundColor: Colors.orange,
                            ),
                          );
                          return;
                        }
                        final r = showRoomSetup
                            ? roomController.text.trim()
                            : (ts.roomNumber ?? '').trim();
                        if (r.isEmpty) {
                          ScaffoldMessenger.of(ctx).showSnackBar(
                            const SnackBar(
                              content: Text('Indiquez le numéro de chambre.'),
                              backgroundColor: Colors.orange,
                            ),
                          );
                          return;
                        }
                        if (showRoomSetup) await ts.setRoomNumber(r);
                        try {
                          final enterpriseId = Provider.of<AuthProvider>(ctx, listen: false).user?.enterpriseId;
                          await ts.validateCode(c, enterpriseId: enterpriseId);
                          if (!ctx.mounted) return;
                          Navigator.of(ctx).pop(c);
                        } catch (e) {
                          final message = e.toString().replaceFirst('Exception: ', '');
                          if (ctx.mounted) {
                            ScaffoldMessenger.of(ctx).showSnackBar(
                              SnackBar(
                                content: Text(message),
                                backgroundColor: Colors.red,
                                duration: const Duration(seconds: 5),
                              ),
                            );
                          }
                        }
                      },
                style: FilledButton.styleFrom(backgroundColor: AppTheme.accentGold),
                child: loading
                    ? const SizedBox(
                        width: 20,
                        height: 20,
                        child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                      )
                    : const Text('Valider'),
              ),
            ],
          );
        },
      );
    },
  );
  tabletSession.clearError();
  return result;
}
