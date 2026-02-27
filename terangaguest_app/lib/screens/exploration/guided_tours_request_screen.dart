import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../models/palace.dart';
import '../../providers/palace_provider.dart';
import '../../services/palace_api.dart';
import '../../utils/haptic_helper.dart';

/// Demande de visites guidées personnalisées (guides certifiés).
class GuidedToursRequestScreen extends StatefulWidget {
  const GuidedToursRequestScreen({super.key});

  @override
  State<GuidedToursRequestScreen> createState() =>
      _GuidedToursRequestScreenState();
}

class _GuidedToursRequestScreenState extends State<GuidedToursRequestScreen> {
  final TextEditingController _detailsController = TextEditingController();
  final TextEditingController _guestsController = TextEditingController();
  DateTime? _requestedFor;
  String _tourType = 'cultural';
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
        if (!s.isAvailable) continue;
        if (s.isGuidedTours) {
          found = s;
          break;
        }
      }
      if (found == null) {
        for (final s in services) {
          if (!s.isAvailable) continue;
          final n = s.name.toLowerCase();
          if (n.contains('visites guidées') || n.contains('visite guidée')) {
            found = s;
            break;
          }
        }
      }
      if (found != null && mounted) setState(() => _serviceId = found!.id);
    } catch (_) {}
  }

  @override
  void dispose() {
    _detailsController.dispose();
    _guestsController.dispose();
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
        'Visites guidées non configurées. Demandez à l\'établissement d\'ajouter le service « Visites guidées » dans le tableau de bord (Services Palace).',
        isError: true,
      );
      return;
    }
    setState(() => _sending = true);
    try {
      final metadata = <String, dynamic>{
        'tour_type': _tourType,
        'guests_count': int.tryParse(_guestsController.text.trim()) ?? 1,
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
                            l10n.guidedToursTitle,
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
                            l10n.guidedToursSubtitle,
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
                      _buildSection(l10n.date, _dateField(l10n)),
                      const SizedBox(height: 24),
                      _buildSection(l10n.tourType, _tourTypeField(l10n)),
                      const SizedBox(height: 24),
                      _buildSection(
                        l10n.numberOfGuests,
                        TextField(
                          controller: _guestsController,
                          keyboardType: TextInputType.number,
                          style: const TextStyle(color: Colors.white),
                          decoration: _inputDecoration(hint: 'Ex: 4'),
                        ),
                      ),
                      const SizedBox(height: 24),
                      _buildSection(
                        l10n.description,
                        TextField(
                          controller: _detailsController,
                          maxLines: 3,
                          style: const TextStyle(color: Colors.white),
                          decoration: _inputDecoration(
                            hint: l10n.describeRequest,
                          ),
                        ),
                      ),
                      if (_serviceId == null) ...[
                        const SizedBox(height: 16),
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 16,
                            vertical: 12,
                          ),
                          decoration: BoxDecoration(
                            color: Colors.orange.withValues(alpha: 0.2),
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: Colors.orange, width: 1),
                          ),
                          child: Row(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Icon(
                                Icons.info_outline,
                                color: Colors.orange,
                                size: 22,
                              ),
                              const SizedBox(width: 10),
                              Expanded(
                                child: Text(
                                  'Visites guidées non configurées. L\'établissement doit ajouter le service « Visites guidées personnalisées » dans le tableau de bord (Services Palace).',
                                  style: TextStyle(
                                    color: Colors.orange.shade100,
                                    fontSize: 13,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
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

  Widget _buildSection(String title, Widget child) {
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
            title,
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

  Widget _dateField(AppLocalizations l10n) {
    return InkWell(
      onTap: () async {
        final date = await showDatePicker(
          context: context,
          initialDate: DateTime.now(),
          firstDate: DateTime.now(),
          lastDate: DateTime.now().add(const Duration(days: 365)),
        );
        if (date == null || !mounted) return;
        final time = await showTimePicker(
          context: context,
          initialTime: TimeOfDay.now(),
        );
        if (time == null || !mounted) return;
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
              ? DateFormat('dd/MM/yyyy HH:mm').format(_requestedFor!)
              : '—',
          style: TextStyle(
            color: _requestedFor != null ? Colors.white : AppTheme.textGray,
            fontSize: 16,
          ),
        ),
      ),
    );
  }

  Widget _tourTypeField(AppLocalizations l10n) {
    final types = [
      ('cultural', l10n.tourTypeCultural),
      ('gastronomic', l10n.tourTypeGastronomic),
      ('historical', l10n.tourTypeHistorical),
    ];
    return Wrap(
      spacing: 8,
      runSpacing: 8,
      children: types.map((t) {
        final selected = _tourType == t.$1;
        return ChoiceChip(
          label: Text(t.$2),
          selected: selected,
          onSelected: (v) => setState(() => _tourType = t.$1),
          selectedColor: AppTheme.accentGold.withValues(alpha: 0.3),
          side: BorderSide(
            color: AppTheme.accentGold.withValues(alpha: selected ? 1 : 0.5),
          ),
          labelStyle: TextStyle(
            color: selected ? AppTheme.accentGold : Colors.white70,
          ),
        );
      }).toList(),
    );
  }
}
