import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../models/palace.dart';
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
  String? _configError;
  final PalaceApi _palaceApi = PalaceApi();
  List<PalaceRequest> _emergencyRequests = [];
  bool _loadingEmergency = false;
  String? _emergencyError;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final auth = context.read<AuthProvider>();
      if (auth.isAdmin || auth.isStaff) {
        _fetchEmergencyRequests();
      }
    });
  }

  Future<void> _fetchEmergencyRequests() async {
    setState(() {
      _loadingEmergency = true;
      _emergencyError = null;
    });
    try {
      final requests = await _palaceApi.getEmergencyPalaceRequests();
      if (!mounted) return;
      setState(() {
        _emergencyRequests = requests;
        _loadingEmergency = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _emergencyError = e.toString().replaceAll('Exception: ', '');
        _loadingEmergency = false;
      });
    }
  }

  Future<int?> _findDoctorServiceId() async {
    final services = await _palaceApi.getPalaceServices();
    for (final s in services) {
      final name = s.name.toLowerCase();
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
    final services = await _palaceApi.getPalaceServices();
    for (final s in services) {
      final name = s.name.toLowerCase();
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
    final palaceProvider = context.read<PalaceProvider>();
    final roomNumber = user?.roomNumber;
    final emergency = user?.enterprise?.emergency;
    final roomInfo = roomNumber != null && roomNumber.isNotEmpty
        ? '${l10n.roomLabel} $roomNumber'
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
      final configuredId = emergency?.doctorServiceId;
      final id = configuredId ?? await _findDoctorServiceId();
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
        await palaceProvider.createPalaceRequest(
          serviceId: id,
          details: description,
          metadata: {'type': 'doctor', 'room': roomNumber},
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
      final configuredId = emergency?.securityServiceId;
      final id = configuredId ?? await _findSecurityServiceId();
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
        await palaceProvider.createPalaceRequest(
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
    final auth = context.watch<AuthProvider>();
    final isStaffOrAdmin = auth.isAdmin || auth.isStaff;

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
                            style: const TextStyle(
                              fontSize: 24,
                              fontWeight: FontWeight.bold,
                              color: Colors.white,
                            ),
                          ),
                          Text(
                            l10n.assistanceEmergencyDesc,
                            style: const TextStyle(
                              fontSize: 13,
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
                child: isStaffOrAdmin
                    ? _buildStaffContent()
                    : SingleChildScrollView(
                        padding: LayoutHelper.horizontalPadding(
                          context,
                        ).copyWith(top: 16, bottom: 24),
                        child: Column(
                          children: [
                            if (doctorEnabled)
                              SizedBox(
                                width: double.infinity,
                                height: 150,
                                child: ServiceCard(
                                  title: l10n.requestDoctor,
                                  icon: Icons.medical_services_outlined,
                                  onTap: _sendingDoctor
                                      ? () {}
                                      : () {
                                          _confirmAndSend('doctor');
                                        },
                                ),
                              ),
                            if (doctorEnabled && securityEnabled)
                              const SizedBox(height: 24),
                            if (securityEnabled)
                              SizedBox(
                                width: double.infinity,
                                height: 150,
                                child: ServiceCard(
                                  title: l10n.reportSecurityEmergency,
                                  icon: Icons.security_outlined,
                                  onTap: _sendingSecurity
                                      ? () {}
                                      : () {
                                          _confirmAndSend('security');
                                        },
                                ),
                              ),
                            if (!doctorEnabled && !securityEnabled)
                              Center(
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
                                    crossAxisAlignment:
                                        CrossAxisAlignment.start,
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

  Widget _buildStaffContent() {
    if (_loadingEmergency) {
      return const Center(
        child: CircularProgressIndicator(
          valueColor: AlwaysStoppedAnimation<Color>(AppTheme.accentGold),
        ),
      );
    }

    if (_emergencyError != null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Text(
            _emergencyError!,
            textAlign: TextAlign.center,
            style: const TextStyle(
              color: Colors.redAccent,
              fontSize: 14,
              fontWeight: FontWeight.w500,
            ),
          ),
        ),
      );
    }

    if (_emergencyRequests.isEmpty) {
      return const Center(
        child: Padding(
          padding: EdgeInsets.all(24),
          child: Text(
            'Aucune demande d\'assistance en cours.',
            textAlign: TextAlign.center,
            style: TextStyle(color: AppTheme.textGray, fontSize: 15),
          ),
        ),
      );
    }

    return RefreshIndicator(
      color: AppTheme.accentGold,
      onRefresh: _fetchEmergencyRequests,
      child: ListView.builder(
        padding: LayoutHelper.horizontalPadding(
          context,
        ).copyWith(top: 16, bottom: 24),
        itemCount: _emergencyRequests.length,
        itemBuilder: (context, index) {
          final request = _emergencyRequests[index];
          final hasRoomOrGuest =
              request.roomNumber != null || request.guestName != null;
          final roomGuestText = () {
            final parts = <String>[];
            if (request.roomNumber != null && request.roomNumber!.isNotEmpty) {
              parts.add('Chambre ${request.roomNumber}');
            }
            if (request.guestName != null && request.guestName!.isNotEmpty) {
              if (parts.isNotEmpty) {
                parts.add('– ${request.guestName}');
              } else {
                parts.add(request.guestName!);
              }
            }
            return parts.join(' ');
          }();
          final lowerName = request.serviceName.toLowerCase();
          final isDoctor =
              request.emergencyType == 'doctor' ||
              lowerName.contains('médecin') ||
              lowerName.contains('medecin') ||
              lowerName.contains('doctor') ||
              lowerName.contains('docteur');
          final icon = isDoctor
              ? Icons.medical_services_outlined
              : Icons.security_outlined;
          final typeLabel = isDoctor
              ? AppLocalizations.of(context).requestDoctor
              : AppLocalizations.of(context).reportSecurityEmergency;

          return Padding(
            padding: const EdgeInsets.only(bottom: 16),
            child: Container(
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                  colors: [AppTheme.primaryBlue, AppTheme.primaryDark],
                ),
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: AppTheme.accentGold, width: 1.5),
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
              child: Padding(
                padding: const EdgeInsets.all(16.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Icon(icon, color: AppTheme.accentGold),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                typeLabel,
                                style: const TextStyle(
                                  fontSize: 16,
                                  fontWeight: FontWeight.bold,
                                  color: AppTheme.accentGold,
                                ),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                request.serviceName,
                                style: const TextStyle(
                                  fontSize: 13,
                                  color: AppTheme.textGray,
                                ),
                                maxLines: 2,
                                overflow: TextOverflow.ellipsis,
                              ),
                              const SizedBox(height: 8),
                              _buildStatusBadge(context, request.status),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            const Icon(
                              Icons.calendar_today,
                              size: 14,
                              color: AppTheme.textGray,
                            ),
                            const SizedBox(width: 6),
                            Expanded(
                              child: Text(
                                request.createdAt
                                    .toLocal()
                                    .toString()
                                    .substring(0, 16),
                                style: const TextStyle(
                                  fontSize: 12,
                                  color: AppTheme.textGray,
                                ),
                              ),
                            ),
                          ],
                        ),
                        if (hasRoomOrGuest) const SizedBox(height: 6),
                        if (hasRoomOrGuest)
                          Row(
                            children: [
                              const Icon(
                                Icons.meeting_room,
                                size: 14,
                                color: AppTheme.textGray,
                              ),
                              const SizedBox(width: 6),
                              Expanded(
                                child: Text(
                                  roomGuestText,
                                  style: const TextStyle(
                                    fontSize: 12,
                                    color: AppTheme.textGray,
                                  ),
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                            ],
                          ),
                        if (request.details != null &&
                            request.details!.isNotEmpty) ...[
                          const SizedBox(height: 6),
                          Text(
                            request.details!,
                            style: const TextStyle(
                              fontSize: 12,
                              color: AppTheme.textGray,
                            ),
                            maxLines: 3,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ],
                      ],
                    ),
                  ],
                ),
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _buildStatusBadge(BuildContext context, String status) {
    final statusColors = _getStatusColor(status);

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: statusColors['bg'],
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: statusColors['border']!, width: 1),
      ),
      child: Text(
        _getStatusLabel(context, status),
        style: TextStyle(
          fontSize: 11,
          fontWeight: FontWeight.bold,
          color: statusColors['text'],
        ),
      ),
    );
  }

  Map<String, Color> _getStatusColor(String status) {
    switch (status) {
      case 'pending':
        return {
          'bg': Colors.orange.withValues(alpha: 0.2),
          'border': Colors.orange,
          'text': Colors.orange,
        };
      case 'in_progress':
        return {
          'bg': Colors.blue.withValues(alpha: 0.2),
          'border': Colors.blue,
          'text': Colors.blue,
        };
      case 'completed':
        return {
          'bg': Colors.green.withValues(alpha: 0.2),
          'border': Colors.green,
          'text': Colors.green,
        };
      case 'cancelled':
        return {
          'bg': Colors.red.withValues(alpha: 0.2),
          'border': Colors.red,
          'text': Colors.red,
        };
      default:
        return {
          'bg': AppTheme.textGray.withValues(alpha: 0.2),
          'border': AppTheme.textGray,
          'text': AppTheme.textGray,
        };
    }
  }

  String _getStatusLabel(BuildContext context, String status) {
    final l10n = AppLocalizations.of(context);
    switch (status) {
      case 'pending':
        return l10n.statusPending;
      case 'in_progress':
        return l10n.statusInProgress;
      case 'completed':
        return l10n.statusCompleted;
      case 'cancelled':
        return l10n.statusCancelled;
      default:
        return status;
    }
  }

  Future<void> _confirmAndSend(String type) async {
    final l10n = AppLocalizations.of(context);
    final user = context.read<AuthProvider>().user;
    final room = user?.roomNumber;
    final actionLabel = type == 'doctor'
        ? l10n.requestDoctor
        : l10n.reportSecurityEmergency;

    final rawName = user?.name.trim();
    String displayTarget;
    if (room != null && room.isNotEmpty) {
      final lowerName = rawName?.toLowerCase();
      final looksLikeRoomAlias =
          lowerName != null &&
          lowerName.contains('chambre') &&
          lowerName.contains(room.toLowerCase());
      if (rawName != null && rawName.isNotEmpty && !looksLikeRoomAlias) {
        displayTarget = '$rawName (chambre $room)';
      } else {
        displayTarget = 'la chambre $room';
      }
    } else {
      if (rawName != null && rawName.isNotEmpty) {
        displayTarget = rawName;
      } else {
        displayTarget = 'ce client';
      }
    }

    final buffer = StringBuffer()
      ..write('Vous êtes sur le point de ')
      ..write(actionLabel.toLowerCase())
      ..write(' pour ')
      ..write(displayTarget);
    buffer.write(
      '. Cette action enverra immédiatement une alerte au personnel de l’hôtel.',
    );

    final confirmed = await showDialog<bool>(
      context: context,
      barrierDismissible: true,
      builder: (dialogContext) {
        return AlertDialog(
          backgroundColor: AppTheme.primaryDark,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(24),
          ),
          title: const Text(
            'Confirmer la demande ?',
            style: TextStyle(
              color: Colors.white,
              fontSize: 20,
              fontWeight: FontWeight.w700,
            ),
          ),
          content: Text(
            buffer.toString(),
            style: const TextStyle(
              color: Colors.white70,
              fontSize: 15,
              height: 1.4,
            ),
          ),
          actionsPadding: const EdgeInsets.only(
            left: 16,
            right: 16,
            bottom: 12,
            top: 4,
          ),
          actions: [
            TextButton(
              onPressed: () {
                Navigator.of(dialogContext).pop(false);
              },
              child: const Text(
                'Annuler',
                style: TextStyle(
                  color: AppTheme.textGray,
                  fontSize: 15,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ),
            const SizedBox(width: 8),
            ElevatedButton(
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.accentGold,
                foregroundColor: Colors.black,
                padding: const EdgeInsets.symmetric(
                  horizontal: 20,
                  vertical: 10,
                ),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(18),
                ),
              ),
              onPressed: () {
                Navigator.of(dialogContext).pop(true);
              },
              child: const Text(
                'Confirmer',
                style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700),
              ),
            ),
          ],
        );
      },
    );

    if (confirmed == true && mounted) {
      HapticHelper.lightImpact();
      await _sendRequest(type);
    }
  }
}
