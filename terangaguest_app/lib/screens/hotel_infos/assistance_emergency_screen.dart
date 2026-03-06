import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../providers/auth_provider.dart';
import '../../providers/palace_provider.dart';
import '../../services/palace_api.dart';
import '../../utils/haptic_helper.dart';
import '../../utils/layout_helper.dart';
import '../../widgets/service_card.dart';

/// Assistance & Urgence : boutons Médecin et Urgence sécurité (chambre identifiée).
class AssistanceEmergencyScreen extends StatefulWidget {
  const AssistanceEmergencyScreen({super.key});

  @override
  State<AssistanceEmergencyScreen> createState() =>
      _AssistanceEmergencyScreenState();
}

class _AssistanceEmergencyScreenState extends State<AssistanceEmergencyScreen> {
  bool _sendingDoctor = false;
  bool _sendingSecurity = false;

  Future<int?> _findServiceId(String keyword) async {
    final api = PalaceApi();
    final services = await api.getPalaceServices();
    for (final s in services) {
      if (s.name.toLowerCase().contains(keyword)) return s.id;
    }
    return null;
  }

  /// Affiche une boîte de dialogue de confirmation avant d'envoyer la demande.
  Future<void> _confirmAndSendRequest(String type, String actionTitle) async {
    final l10n = AppLocalizations.of(context);
    final confirmed = await showDialog<bool>(
      context: context,
      barrierDismissible: false,
      builder: (ctx) {
        return AlertDialog(
          backgroundColor: AppTheme.primaryBlue,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
            side: const BorderSide(color: AppTheme.accentGold, width: 1.5),
          ),
          title: Text(
            l10n.confirmRequest,
            style: const TextStyle(
              color: AppTheme.accentGold,
              fontWeight: FontWeight.bold,
              fontSize: 18,
            ),
          ),
          content: Text(
            l10n.confirmEmergencyAction(actionTitle),
            style: const TextStyle(color: Colors.white, fontSize: 16),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(ctx).pop(false),
              child: Text(
                l10n.cancel,
                style: const TextStyle(color: AppTheme.textGray),
              ),
            ),
            FilledButton(
              onPressed: () => Navigator.of(ctx).pop(true),
              style: FilledButton.styleFrom(
                backgroundColor: AppTheme.accentGold,
                foregroundColor: AppTheme.primaryDark,
              ),
              child: Text(l10n.validate),
            ),
          ],
        );
      },
    );
    if (confirmed == true && mounted) _sendRequest(type);
  }

  Future<void> _sendRequest(String type) async {
    final l10n = AppLocalizations.of(context);
    final user = context.read<AuthProvider>().user;
    final palaceProvider = context.read<PalaceProvider>();
    final roomInfo = user?.roomNumber != null && user!.roomNumber!.isNotEmpty
        ? '${l10n.roomLabel} ${user.roomNumber}'
        : 'Chambre non identifiée';
    final description = 'Demande depuis $roomInfo';

    String errorMessage(String raw) {
      if (raw.contains('séjour') ||
          raw.contains('Réservation possible') ||
          raw.contains('active stay') ||
          raw.contains('code client')) {
        return l10n.noActiveStayForEmergency;
      }
      return raw.replaceAll('Exception: ', '');
    }

    if (type == 'doctor') {
      final id = await _findServiceId('médecin');
      if (id == null) {
        _showSnack(l10n.assistanceDoctorNotConfigured, isError: true);
        return;
      }
      setState(() => _sendingDoctor = true);
      try {
        await palaceProvider.createPalaceRequest(
          serviceId: id,
          details: description,
          metadata: {'type': 'doctor', 'room': user?.roomNumber},
        );
        _showSnack(l10n.emergencyRequestSent);
      } catch (e) {
        _showSnack(errorMessage(e.toString()), isError: true);
      } finally {
        if (mounted) setState(() => _sendingDoctor = false);
      }
    } else {
      final id = await _findServiceId('urgence');
      if (id == null) {
        _showSnack(l10n.assistanceSecurityNotConfigured, isError: true);
        return;
      }
      setState(() => _sendingSecurity = true);
      try {
        await palaceProvider.createPalaceRequest(
          serviceId: id,
          details: description,
          metadata: {'type': 'security', 'room': user?.roomNumber},
        );
        _showSnack(l10n.emergencyRequestSent);
      } catch (e) {
        _showSnack(errorMessage(e.toString()), isError: true);
      } finally {
        if (mounted) setState(() => _sendingSecurity = false);
      }
    }
  }

  void _showSnack(String msg, {bool isError = false}) {
    if (!context.mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(msg),
        backgroundColor: isError ? Colors.red : AppTheme.accentGold,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final enterprise = context.watch<AuthProvider>().user?.enterprise;
    final emergency = enterprise?.emergency;
    final doctorEnabled = emergency?.doctorEnabled ?? true;
    final securityEnabled = emergency?.securityEnabled ?? true;
    final roomNumber = context.watch<AuthProvider>().user?.roomNumber;

    return Scaffold(
      body: Container(
        decoration: BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [AppTheme.primaryDark, AppTheme.primaryBlue],
          ),
        ),
        child: SafeArea(
          child: Column(
            children: [
              Padding(
                padding: const EdgeInsets.all(20.0),
                child: Row(
                  children: [
                    IconButton(
                      icon: const Icon(
                        Icons.arrow_back,
                        color: AppTheme.accentGold,
                      ),
                      onPressed: () => Navigator.pop(context),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            l10n.assistanceEmergency,
                            style: TextStyle(
                              fontSize: MediaQuery.of(context).size.width < 600
                                  ? 18
                                  : 28,
                              fontWeight: FontWeight.bold,
                              color: AppTheme.accentGold,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            l10n.assistanceEmergencyDesc,
                            style: const TextStyle(
                              fontSize: 14,
                              color: AppTheme.textGray,
                            ),
                          ),
                          if (roomNumber != null && roomNumber.isNotEmpty)
                            Padding(
                              padding: const EdgeInsets.only(top: 6),
                              child: Text(
                                '${l10n.roomLabel} $roomNumber',
                                style: const TextStyle(
                                  fontSize: 14,
                                  color: AppTheme.accentGold,
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                            ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              Expanded(
                child: doctorEnabled || securityEnabled
                    ? Padding(
                        padding: LayoutHelper.horizontalPadding(context),
                        child: GridView.count(
                          crossAxisCount: LayoutHelper.gridCrossAxisCount(
                            context,
                          ),
                          mainAxisSpacing: LayoutHelper.gridSpacing(context),
                          crossAxisSpacing: LayoutHelper.gridSpacing(context),
                          childAspectRatio:
                              LayoutHelper.dashboardCellAspectRatio(context),
                          padding: EdgeInsets.symmetric(
                            vertical: LayoutHelper.gridSpacing(context),
                          ),
                          children: [
                            if (doctorEnabled)
                              ServiceCard(
                                title: l10n.requestDoctor,
                                icon: Icons.medical_services_outlined,
                                imagePath:
                                    'assets/images/assistance_medecin.png',
                                isLoading: _sendingDoctor,
                                onTap: () {
                                  HapticHelper.lightImpact();
                                  _confirmAndSendRequest(
                                    'doctor',
                                    l10n.requestDoctor,
                                  );
                                },
                              ),
                            if (securityEnabled)
                              ServiceCard(
                                title: l10n.reportSecurityEmergency,
                                icon: Icons.security_outlined,
                                imagePath:
                                    'assets/images/assistance_securite.png',
                                isLoading: _sendingSecurity,
                                onTap: () {
                                  HapticHelper.lightImpact();
                                  _confirmAndSendRequest(
                                    'security',
                                    l10n.reportSecurityEmergency,
                                  );
                                },
                              ),
                          ],
                        ),
                      )
                    : Center(
                        child: Padding(
                          padding: const EdgeInsets.all(24),
                          child: Text(
                            l10n.comingSoon,
                            style: const TextStyle(
                              color: AppTheme.textGray,
                              fontSize: 16,
                            ),
                          ),
                        ),
                      ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
