import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../models/palace.dart';
import '../../providers/palace_provider.dart';
import '../../services/palace_api.dart';
import '../../utils/haptic_helper.dart';

/// Demande de transfert ou VTC (navette aéroport, chauffeur privé).
class TransfersRequestScreen extends StatefulWidget {
  const TransfersRequestScreen({super.key});

  @override
  State<TransfersRequestScreen> createState() => _TransfersRequestScreenState();
}

class _TransfersRequestScreenState extends State<TransfersRequestScreen> {
  final TextEditingController _pickupController = TextEditingController();
  final TextEditingController _destinationController = TextEditingController();
  final TextEditingController _detailsController = TextEditingController();
  DateTime? _requestedFor;
  bool _sending = false;
  int? _serviceId;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _resolveServiceId());
  }

  Future<void> _resolveServiceId() async {
    final api = PalaceApi();
    try {
      final services = await api.getPalaceServices();
      PalaceService? found;
      for (final s in services) {
        final n = s.name.toLowerCase();
        if (n.contains('transfert') ||
            n.contains('vtc') ||
            n.contains('navette') ||
            n.contains('chauffeur')) {
          found = s;
          break;
        }
      }
      if (found != null && mounted) setState(() => _serviceId = found!.id);
    } catch (_) {}
  }

  @override
  void dispose() {
    _pickupController.dispose();
    _destinationController.dispose();
    _detailsController.dispose();
    super.dispose();
  }

  void _showSnack(String msg, {bool isError = false}) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(msg),
        backgroundColor: isError ? Colors.red : AppTheme.accentGold,
      ),
    );
  }

  Future<void> _submit() async {
    final l10n = AppLocalizations.of(context);
    if (_serviceId == null) {
      _showSnack(
        'Service Transferts & VTC non configuré. Contactez l\'établissement.',
        isError: true,
      );
      return;
    }
    final pickup = _pickupController.text.trim();
    final destination = _destinationController.text.trim();
    if (pickup.isEmpty || destination.isEmpty) {
      _showSnack(
        'Indiquez le lieu de prise en charge et la destination.',
        isError: true,
      );
      return;
    }
    setState(() => _sending = true);
    try {
      final metadata = <String, dynamic>{
        'vehicle_request_type': 'taxi',
        'pickup_address': pickup,
        'destination_address': destination,
      };
      await context.read<PalaceProvider>().createPalaceRequest(
        serviceId: _serviceId!,
        details: _detailsController.text.trim().isEmpty
            ? null
            : _detailsController.text.trim(),
        scheduledTime: _requestedFor,
        metadata: metadata,
      );
      if (mounted) {
        _showSnack(l10n.requestSentMessage);
        Navigator.of(context).popUntil((route) => route.isFirst);
      }
    } catch (e) {
      if (mounted) _showSnack(e.toString(), isError: true);
    } finally {
      if (mounted) setState(() => _sending = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);

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
                            l10n.transfersVtcTitle,
                            style: const TextStyle(
                              fontSize: 22,
                              fontWeight: FontWeight.bold,
                              color: Colors.white,
                            ),
                          ),
                          Text(
                            l10n.transfersVtcSubtitle,
                            style: const TextStyle(
                              fontSize: 13,
                              color: AppTheme.textGray,
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
                  padding: const EdgeInsets.symmetric(
                    horizontal: 20,
                    vertical: 8,
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      TextField(
                        controller: _pickupController,
                        style: const TextStyle(color: Colors.white),
                        decoration: InputDecoration(
                          labelText: l10n.pickupPlace,
                          hintText: 'Ex: Aéroport, Hôtel…',
                          labelStyle: const TextStyle(
                            color: AppTheme.accentGold,
                          ),
                          hintStyle: TextStyle(
                            color: AppTheme.textGray.withValues(alpha: 0.7),
                          ),
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                          enabledBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                            borderSide: BorderSide(
                              color: AppTheme.accentGold.withValues(alpha: 0.4),
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(height: 16),
                      TextField(
                        controller: _destinationController,
                        style: const TextStyle(color: Colors.white),
                        decoration: InputDecoration(
                          labelText: l10n.destinationPlace,
                          hintText: 'Ex: Centre-ville, Adresse…',
                          labelStyle: const TextStyle(
                            color: AppTheme.accentGold,
                          ),
                          hintStyle: TextStyle(
                            color: AppTheme.textGray.withValues(alpha: 0.7),
                          ),
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                          enabledBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                            borderSide: BorderSide(
                              color: AppTheme.accentGold.withValues(alpha: 0.4),
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(height: 16),
                      InkWell(
                        onTap: () async {
                          final date = await showDatePicker(
                            context: context,
                            initialDate: DateTime.now(),
                            firstDate: DateTime.now(),
                            lastDate: DateTime.now().add(
                              const Duration(days: 365),
                            ),
                          );
                          if (!context.mounted || date == null) return;
                          final time = await showTimePicker(
                            context: context,
                            initialTime: TimeOfDay.now(),
                          );
                          if (!context.mounted || time == null) return;
                          if (!mounted) return;
                          setState(
                            () => _requestedFor = DateTime(
                              date.year,
                              date.month,
                              date.day,
                              time.hour,
                              time.minute,
                            ),
                          );
                        },
                        borderRadius: BorderRadius.circular(12),
                        child: InputDecorator(
                          decoration: InputDecoration(
                            labelText: l10n.date,
                            labelStyle: const TextStyle(
                              color: AppTheme.accentGold,
                            ),
                            border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                            enabledBorder: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12),
                              borderSide: BorderSide(
                                color: AppTheme.accentGold.withValues(
                                  alpha: 0.4,
                                ),
                              ),
                            ),
                          ),
                          child: Text(
                            _requestedFor != null
                                ? DateFormat(
                                    'dd/MM/yyyy HH:mm',
                                  ).format(_requestedFor!)
                                : '—',
                            style: TextStyle(
                              color: _requestedFor != null
                                  ? Colors.white
                                  : AppTheme.textGray,
                              fontSize: 16,
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(height: 16),
                      TextField(
                        controller: _detailsController,
                        maxLines: 2,
                        style: const TextStyle(color: Colors.white),
                        decoration: InputDecoration(
                          labelText: l10n.description,
                          hintText: l10n.describeRequest,
                          labelStyle: const TextStyle(
                            color: AppTheme.accentGold,
                          ),
                          hintStyle: TextStyle(
                            color: AppTheme.textGray.withValues(alpha: 0.7),
                          ),
                          border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                          enabledBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                            borderSide: BorderSide(
                              color: AppTheme.accentGold.withValues(alpha: 0.4),
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(height: 24),
                      FilledButton.icon(
                        onPressed: _sending
                            ? null
                            : () {
                                HapticHelper.lightImpact();
                                _submit();
                              },
                        icon: _sending
                            ? const SizedBox(
                                width: 20,
                                height: 20,
                                child: CircularProgressIndicator(
                                  strokeWidth: 2,
                                  valueColor: AlwaysStoppedAnimation<Color>(
                                    Colors.black54,
                                  ),
                                ),
                              )
                            : const Icon(Icons.send_outlined, size: 20),
                        label: Text(_sending ? '...' : l10n.sendRequest),
                        style: FilledButton.styleFrom(
                          backgroundColor: AppTheme.accentGold,
                          foregroundColor: Colors.black87,
                          padding: const EdgeInsets.symmetric(vertical: 14),
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
}
