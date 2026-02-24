import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../providers/auth_provider.dart';
import '../../providers/palace_provider.dart';
import '../../services/palace_api.dart';
import '../../utils/haptic_helper.dart';
import '../../utils/layout_helper.dart';

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
                              fontSize: MediaQuery.of(context).size.width < 600 ? 18 : 28,
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
                          crossAxisCount: LayoutHelper.gridCrossAxisCount(context),
                          mainAxisSpacing: LayoutHelper.gridSpacing(context),
                          crossAxisSpacing: LayoutHelper.gridSpacing(context),
                          childAspectRatio: LayoutHelper.dashboardCellAspectRatio(context),
                          padding: EdgeInsets.symmetric(
                            vertical: LayoutHelper.gridSpacing(context),
                          ),
                          children: [
                            if (doctorEnabled)
                              _buildBoxCard(
                                context,
                                icon: Icons.medical_services_outlined,
                                title: l10n.requestDoctor,
                                loading: _sendingDoctor,
                                onTap: () {
                                  HapticHelper.lightImpact();
                                  _sendRequest('doctor');
                                },
                              ),
                            if (securityEnabled)
                              _buildBoxCard(
                                context,
                                icon: Icons.security_outlined,
                                title: l10n.reportSecurityEmergency,
                                loading: _sendingSecurity,
                                onTap: () {
                                  HapticHelper.lightImpact();
                                  _sendRequest('security');
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

  /// Carte style « boîte » comme sur Hotel Infos & Sécurité : icône au-dessus, titre en dessous.
  Widget _buildBoxCard(
    BuildContext context, {
    required IconData icon,
    required String title,
    required bool loading,
    required VoidCallback onTap,
  }) {
    final isMobile = MediaQuery.sizeOf(context).width < 600;
    final iconSize = isMobile ? 46.0 : 70.0;
    final fontSize = isMobile ? 13.0 : 21.0;

    return GestureDetector(
      onTap: loading ? null : onTap,
      child: Container(
        decoration: BoxDecoration(
          gradient: const LinearGradient(
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
            colors: [AppTheme.primaryBlue, AppTheme.primaryDark],
          ),
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: AppTheme.accentGold, width: 2),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.4),
              blurRadius: 20,
              spreadRadius: 2,
              offset: const Offset(0, 10),
            ),
            BoxShadow(
              color: AppTheme.accentGold.withValues(alpha: 0.1),
              blurRadius: 15,
              spreadRadius: -2,
              offset: const Offset(0, -4),
            ),
          ],
        ),
        child: Stack(
          alignment: Alignment.center,
          children: [
            Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                if (loading)
                  const Padding(
                    padding: EdgeInsets.only(bottom: 8),
                    child: SizedBox(
                      width: 24,
                      height: 24,
                      child: CircularProgressIndicator(
                        strokeWidth: 2,
                        valueColor: AlwaysStoppedAnimation<Color>(
                          AppTheme.accentGold,
                        ),
                      ),
                    ),
                  )
                else ...[
                  Icon(icon, size: iconSize, color: AppTheme.accentGold),
                  const SizedBox(height: 8),
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 8),
                    child: Text(
                      title,
                      textAlign: TextAlign.center,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: TextStyle(
                        fontSize: fontSize,
                        fontWeight: FontWeight.w600,
                        color: AppTheme.accentGold,
                      ),
                    ),
                  ),
                ],
              ],
            ),
          ],
        ),
      ),
    );
  }
}
