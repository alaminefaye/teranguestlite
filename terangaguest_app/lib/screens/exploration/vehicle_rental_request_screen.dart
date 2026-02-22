import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../models/palace.dart';
import '../../models/vehicle.dart';
import '../../providers/palace_provider.dart';
import '../../services/palace_api.dart';
import '../../utils/haptic_helper.dart';

/// Formulaire de demande de location pour un véhicule choisi.
class VehicleRentalRequestScreen extends StatefulWidget {
  final Vehicle vehicle;

  const VehicleRentalRequestScreen({super.key, required this.vehicle});

  @override
  State<VehicleRentalRequestScreen> createState() => _VehicleRentalRequestScreenState();
}

class _VehicleRentalRequestScreenState extends State<VehicleRentalRequestScreen> {
  final TextEditingController _detailsController = TextEditingController();
  final TextEditingController _rentalDaysController = TextEditingController();
  final TextEditingController _rentalDurationController = TextEditingController();
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
      PalaceService? vehicleService;
      for (final s in services) {
        if (s.isVehicleService) {
          vehicleService = s;
          break;
        }
      }
      if (vehicleService != null && mounted) {
        setState(() => _serviceId = vehicleService!.id);
      }
    } catch (_) {}
  }

  @override
  void dispose() {
    _detailsController.dispose();
    _rentalDaysController.dispose();
    _rentalDurationController.dispose();
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
      _showSnack('Service Location de véhicule non configuré. Contactez l\'établissement.', isError: true);
      return;
    }
    final days = int.tryParse(_rentalDaysController.text.trim());
    final hours = int.tryParse(_rentalDurationController.text.trim());
    if ((days == null || days < 1) && (hours == null || hours < 1)) {
      _showSnack('Indiquez le nombre de jours ou la durée en heures.', isError: true);
      return;
    }

    setState(() => _sending = true);
    try {
      final meta = <String, dynamic>{
        'vehicle_request_type': 'rental',
        'vehicle_id': widget.vehicle.id,
        'vehicle_type': widget.vehicle.vehicleType,
        'number_of_seats': widget.vehicle.numberOfSeats,
      };
      if (days != null && days > 0) meta['rental_days'] = days;
      if (hours != null && hours > 0) meta['rental_duration_hours'] = hours;

      await context.read<PalaceProvider>().createPalaceRequest(
            serviceId: _serviceId!,
            details: _detailsController.text.trim().isEmpty ? null : _detailsController.text.trim(),
            scheduledTime: _requestedFor,
            metadata: meta,
          );
      if (mounted) {
        _showSnack(l10n.requestSentMessage);
        Navigator.of(context).popUntil((route) => route.isFirst);
      }
    } catch (e) {
      if (mounted) {
        _showSnack(e.toString(), isError: true);
      }
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
                            widget.vehicle.name,
                            style: const TextStyle(
                              fontSize: 22,
                              fontWeight: FontWeight.bold,
                              color: Colors.white,
                            ),
                          ),
                          Text(
                            l10n.requestVehicleRental,
                            style: const TextStyle(fontSize: 13, color: AppTheme.textGray),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              Expanded(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      _field(
                        label: l10n.rentalDate,
                        value: _requestedFor != null
                            ? DateFormat('dd/MM/yyyy HH:mm').format(_requestedFor!)
                            : null,
                        onTap: () async {
                          final ctx = context;
                          final date = await showDatePicker(
                            context: ctx,
                            initialDate: DateTime.now(),
                            firstDate: DateTime.now(),
                            lastDate: DateTime.now()
                                .add(const Duration(days: 365)),
                          );
                          if (date == null || !ctx.mounted) return;
                          final time = await showTimePicker(
                            context: ctx,
                            initialTime: TimeOfDay.now(),
                          );
                          if (time == null || !ctx.mounted) return;
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
                      ),
                      const SizedBox(height: 16),
                      TextField(
                        controller: _rentalDaysController,
                        keyboardType: TextInputType.number,
                        style: const TextStyle(color: Colors.white),
                        decoration: InputDecoration(
                          labelText: l10n.rentalDays,
                          hintText: 'Ex: 2',
                          labelStyle: const TextStyle(color: AppTheme.accentGold),
                          hintStyle: TextStyle(color: AppTheme.textGray.withValues(alpha: 0.7)),
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                          enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: AppTheme.accentGold.withValues(alpha: 0.4))),
                        ),
                      ),
                      const SizedBox(height: 16),
                      TextField(
                        controller: _rentalDurationController,
                        keyboardType: TextInputType.number,
                        style: const TextStyle(color: Colors.white),
                        decoration: InputDecoration(
                          labelText: l10n.rentalDuration,
                          hintText: 'Ex: 5 (demi-journée)',
                          labelStyle: const TextStyle(color: AppTheme.accentGold),
                          hintStyle: TextStyle(color: AppTheme.textGray.withValues(alpha: 0.7)),
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                          enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: AppTheme.accentGold.withValues(alpha: 0.4))),
                        ),
                      ),
                      const SizedBox(height: 16),
                      TextField(
                        controller: _detailsController,
                        maxLines: 3,
                        style: const TextStyle(color: Colors.white),
                        decoration: InputDecoration(
                          labelText: l10n.description,
                          hintText: l10n.describeRequest,
                          labelStyle: const TextStyle(color: AppTheme.accentGold),
                          hintStyle: TextStyle(color: AppTheme.textGray.withValues(alpha: 0.7)),
                          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                          enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: AppTheme.accentGold.withValues(alpha: 0.4))),
                        ),
                      ),
                      const SizedBox(height: 24),
                      FilledButton.icon(
                        onPressed: _sending ? null : () { HapticHelper.lightImpact(); _submit(); },
                        icon: _sending
                            ? const SizedBox(
                                width: 20,
                                height: 20,
                                child: CircularProgressIndicator(strokeWidth: 2, valueColor: AlwaysStoppedAnimation<Color>(Colors.black54)),
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

  Widget _field({required String label, String? value, required VoidCallback onTap}) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(12),
      child: InputDecorator(
        decoration: InputDecoration(
          labelText: label,
          labelStyle: const TextStyle(color: AppTheme.accentGold),
          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
          enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide(color: AppTheme.accentGold.withValues(alpha: 0.4))),
        ),
        child: Text(
          value ?? '—',
          style: TextStyle(
            color: value != null ? Colors.white : AppTheme.textGray,
            fontSize: 16,
          ),
        ),
      ),
    );
  }
}
