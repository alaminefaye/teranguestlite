import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../providers/auth_provider.dart';
import '../../providers/palace_provider.dart';
import '../../services/palace_api.dart';
import '../../utils/haptic_helper.dart';

/// Assistance & Urgence : boutons Médecin et Urgence sécurité (chambre identifiée).
class AssistanceEmergencyScreen extends StatefulWidget {
  const AssistanceEmergencyScreen({super.key});

  @override
  State<AssistanceEmergencyScreen> createState() => _AssistanceEmergencyScreenState();
}

class _AssistanceEmergencyScreenState extends State<AssistanceEmergencyScreen> {
  bool _sendingDoctor = false;
  bool _sendingSecurity = false;
  String? _configError;

  Future<int?> _findDoctorServiceId() async {
    final api = PalaceApi();
    final services = await api.getPalaceServices();
    for (final s in services) {
      final name = (s.name ?? '').toLowerCase();
      final normalized = _normalize(name);
      if (name.contains('médecin') ||
          normalized.contains('medecin') ||
          name.contains('doctor') ||
          name.contains('docteur')) {
        return s.id;
      }
    }
    return null;
  }

  Future<int?> _findSecurityServiceId() async {
    final api = PalaceApi();
    final services = await api.getPalaceServices();
    for (final s in services) {
      final name = (s.name ?? '').toLowerCase();
      final normalized = _normalize(name);
      if (name.contains('urgence') ||
          normalized.contains('urgence') ||
          name.contains('sécurité') ||
          normalized.contains('securite') ||
          name.contains('security') ||
          name.contains('emergency')) {
        return s.id;
      }
    }
    return null;
  }

  String _normalize(String input) {
    return input
        .replaceAll('é', 'e')
        .replaceAll('è', 'e')
        .replaceAll('ê', 'e')
        .replaceAll('à', 'a')
        .replaceAll('â', 'a')
        .replaceAll('ù', 'u')
        .replaceAll('û', 'u');
  }

  Future<void> _sendRequest(String type) async {
    final l10n = AppLocalizations.of(context);
    final user = context.read<AuthProvider>().user;
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
      final id = await _findDoctorServiceId();
      if (id == null) {
        if (mounted) {
          setState(() {
            _configError = l10n.assistanceDoctorNotConfigured;
          });
        }
        return;
      }
      if (mounted && _configError != null) {
        setState(() {
          _configError = null;
        });
      }
      setState(() => _sendingDoctor = true);
      try {
        await context.read<PalaceProvider>().createPalaceRequest(
              serviceId: id,
              details: description,
              metadata: {'type': 'doctor', 'room': user?.roomNumber},
            );
        if (mounted) {
          _showSnack(l10n.emergencyRequestSent);
        }
      } catch (e) {
        if (mounted) _showSnack(errorMessage(e.toString()), isError: true);
      } finally {
        if (mounted) setState(() => _sendingDoctor = false);
      }
    } else {
      final id = await _findSecurityServiceId();
      if (id == null) {
        if (mounted) {
          setState(() {
            _configError = l10n.assistanceSecurityNotConfigured;
          });
        }
        return;
      }
      if (mounted && _configError != null) {
        setState(() {
          _configError = null;
        });
      }
      setState(() => _sendingSecurity = true);
      try {
        await context.read<PalaceProvider>().createPalaceRequest(
              serviceId: id,
              details: description,
              metadata: {'type': 'security', 'room': user?.roomNumber},
            );
        if (mounted) {
          _showSnack(l10n.emergencyRequestSent);
        }
      } catch (e) {
        if (mounted) _showSnack(errorMessage(e.toString()), isError: true);
      } finally {
        if (mounted) setState(() => _sendingSecurity = false);
      }
    }
  }

  void _showSnack(String msg, {bool isError = false}) {
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
                      icon: const Icon(Icons.arrow_back, color: AppTheme.accentGold),
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
                            style: const TextStyle(
                              fontSize: 24,
                              fontWeight: FontWeight.bold,
                              color: Colors.white,
                            ),
                          ),
                          Text(
                            l10n.assistanceEmergencyDesc,
                            style: const TextStyle(fontSize: 13, color: AppTheme.textGray),
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
                child: SingleChildScrollView(
                  padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
                  child: Column(
                    children: [
                      if (doctorEnabled)
                        _actionCard(
                          context,
                          icon: Icons.medical_services_outlined,
                          title: l10n.requestDoctor,
                          loading: _sendingDoctor,
                          onTap: () {
                            HapticHelper.lightImpact();
                            _sendRequest('doctor');
                          },
                        ),
                      if (doctorEnabled && securityEnabled) const SizedBox(height: 16),
                      if (securityEnabled)
                        _actionCard(
                          context,
                          icon: Icons.security_outlined,
                          title: l10n.reportSecurityEmergency,
                          loading: _sendingSecurity,
                          onTap: () {
                            HapticHelper.lightImpact();
                            _sendRequest('security');
                          },
                        ),
                      if (!doctorEnabled && !securityEnabled)
                        Center(
                          child: Padding(
                            padding: const EdgeInsets.all(24),
                            child: Text(
                              l10n.comingSoon,
                              style: const TextStyle(color: AppTheme.textGray, fontSize: 16),
                            ),
                          ),
                        ),
                      if (_configError != null)
                        Padding(
                          padding: const EdgeInsets.only(top: 24),
                          child: Container(
                            padding: const EdgeInsets.all(16),
                            decoration: BoxDecoration(
                              color: Colors.red.withValues(alpha: 0.12),
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(
                                color: Colors.red.withValues(alpha: 0.8),
                                width: 1,
                              ),
                            ),
                            child: Row(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Icon(
                                  Icons.info_outline,
                                  color: Colors.redAccent,
                                ),
                                const SizedBox(width: 12),
                                Expanded(
                                  child: Text(
                                    _configError!,
                                    style: const TextStyle(
                                      color: Colors.redAccent,
                                      fontSize: 14,
                                      fontWeight: FontWeight.w500,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _actionCard(
    BuildContext context, {
    required IconData icon,
    required String title,
    required bool loading,
    required VoidCallback onTap,
  }) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: loading ? null : onTap,
        borderRadius: BorderRadius.circular(16),
        child: Container(
          padding: const EdgeInsets.all(24),
          decoration: BoxDecoration(
            color: AppTheme.primaryBlue.withValues(alpha: 0.6),
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: AppTheme.accentGold.withValues(alpha: 0.5)),
          ),
          child: Row(
            children: [
              Icon(icon, color: AppTheme.accentGold, size: 40),
              const SizedBox(width: 20),
              Expanded(
                child: Text(
                  title,
                  style: const TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.w600,
                    color: Colors.white,
                  ),
                ),
              ),
              if (loading)
                const SizedBox(
                  width: 24,
                  height: 24,
                  child: CircularProgressIndicator(
                    strokeWidth: 2,
                    valueColor: AlwaysStoppedAnimation<Color>(AppTheme.accentGold),
                  ),
                )
              else
                const Icon(Icons.arrow_forward_ios, color: AppTheme.accentGold, size: 18),
            ],
          ),
        ),
      ),
    );
  }
}
