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

    String lower(String n) => n.toLowerCase();

    try {
      final services = await api.getPalaceServices();
      PalaceService? found;
      for (final s in services) {
        final n = lower(s.name);
        if (n.contains('transfert') ||
            n.contains('vtc') ||
            n.contains('navette') ||
            n.contains('chauffeur')) {
          found = s;
          break;
        }
      }
      if (found != null && mounted) {
        setState(() => _serviceId = found!.id);
      }
    } catch (_) {}
  }

  @override
  void dispose() {
    _pickupController.dispose();
    _destinationController.dispose();
    _detailsController.dispose();
    super.dispose();
  }

  void _showSnack(BuildContext ctx, String msg, {bool isError = false}) {
    if (!ctx.mounted) return;
    ScaffoldMessenger.of(ctx).showSnackBar(
      SnackBar(
        content: Text(msg),
        backgroundColor: isError ? Colors.red : AppTheme.accentGold,
      ),
    );
  }

  Future<void> _submit() async {
    final l10n = AppLocalizations.of(context);
    final ctx = context;
    if (_serviceId == null) {
      _showSnack(ctx, l10n.transfersNotConfigured, isError: true);
      return;
    }
    final pickup = _pickupController.text.trim();
    final destination = _destinationController.text.trim();
    if (pickup.isEmpty || destination.isEmpty) {
      _showSnack(ctx, l10n.pickupDestinationRequired, isError: true);
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
      if (!ctx.mounted) return;
      _showSnack(ctx, l10n.requestSentMessage);
      Navigator.of(ctx).popUntil((route) => route.isFirst);
    } catch (e) {
      _showSnack(ctx, e.toString(), isError: true);
    } finally {
      if (mounted) {
        setState(() => _sending = false);
      }
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
                            l10n.transfersVtcSubtitle,
                            style: const TextStyle(
                              fontSize: 14,
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
                  padding: EdgeInsets.symmetric(
                    horizontal: MediaQuery.of(context).size.width < 600
                        ? 16
                        : 60,
                    vertical: 20,
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      _buildField(
                        label: l10n.pickupPlace,
                        child: TextField(
                          controller: _pickupController,
                          style: const TextStyle(color: Colors.white),
                          decoration: _inputDecoration(
                            hint: l10n.exAirportHotel,
                          ),
                        ),
                      ),
                      const SizedBox(height: 24),
                      _buildField(
                        label: l10n.destinationPlace,
                        child: TextField(
                          controller: _destinationController,
                          style: const TextStyle(color: Colors.white),
                          decoration: _inputDecoration(
                            hint: l10n.exDowntownAddress,
                          ),
                        ),
                      ),
                      const SizedBox(height: 24),
                      _buildField(
                        label: l10n.date,
                        child: InkWell(
                          onTap: () async {
                            final ctx = context;
                            final date = await showDatePicker(
                              context: ctx,
                              initialDate: DateTime.now(),
                              firstDate: DateTime.now(),
                              lastDate: DateTime.now().add(
                                const Duration(days: 365),
                              ),
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
                          borderRadius: BorderRadius.circular(12),
                          child: InputDecorator(
                            decoration: _inputDecoration(),
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
                      ),
                      const SizedBox(height: 24),
                      _buildField(
                        label: l10n.description,
                        child: TextField(
                          controller: _detailsController,
                          maxLines: 2,
                          style: const TextStyle(color: Colors.white),
                          decoration: _inputDecoration(
                            hint: l10n.describeRequest,
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

  InputDecoration _inputDecoration({String? hint}) {
    return InputDecoration(
      hintText: hint,
      hintStyle: TextStyle(color: AppTheme.textGray.withValues(alpha: 0.8)),
      filled: true,
      fillColor: AppTheme.primaryBlue.withValues(alpha: 0.5),
      border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: BorderSide(
          color: AppTheme.accentGold.withValues(alpha: 0.3),
        ),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: AppTheme.accentGold),
      ),
    );
  }

  Widget _buildField({required String label, required Widget child}) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [AppTheme.primaryBlue, AppTheme.primaryDark],
        ),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppTheme.accentGold, width: 1.5),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            label,
            style: const TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.bold,
              color: AppTheme.accentGold,
            ),
          ),
          const SizedBox(height: 12),
          child,
        ],
      ),
    );
  }
}
