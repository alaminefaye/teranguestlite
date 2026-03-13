import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../models/palace.dart';
import '../../models/vehicle.dart';
import '../../providers/auth_provider.dart';
import '../../providers/palace_provider.dart';
import '../../providers/tablet_session_provider.dart';
import '../../services/palace_api.dart';
import '../../utils/haptic_helper.dart';
import '../../utils/layout_helper.dart';

/// Formulaire direct de demande de location — affiché quand le mode admin est "form".
/// Le client ne choisit pas un véhicule précis : il choisit un type, une durée, et envoie.
class VehicleDirectFormScreen extends StatefulWidget {
  /// Liste des véhicules disponibles (fournie par VehicleListScreen).
  final List<Vehicle> vehicles;

  const VehicleDirectFormScreen({super.key, required this.vehicles});

  @override
  State<VehicleDirectFormScreen> createState() =>
      _VehicleDirectFormScreenState();
}

typedef _DurationChoice = ({String label, String value, int? days, int? hours});

class _VehicleDirectFormScreenState extends State<VehicleDirectFormScreen> {
  final TextEditingController _detailsController = TextEditingController();
  final TextEditingController _clientCodeController = TextEditingController();

  String? _selectedType; // null = tous / pas de filtre
  String _durationMode = 'half'; // 'half' | 'day' | 'multi'
  final TextEditingController _multiDaysController = TextEditingController();
  DateTime? _requestedFor;
  bool _sending = false;
  int? _serviceId;

  @override
  void initState() {
    super.initState();
    _multiDaysController.addListener(() => setState(() {}));
    WidgetsBinding.instance.addPostFrameCallback((_) async {
      await _resolveServiceId();
      if (!mounted) return;
      // Auto-remplissage du code client (même logique que create_palace_request_screen)
      context.read<AuthProvider>().loadUser();
      final tabletSession = context.read<TabletSessionProvider>();
      final auth = context.read<AuthProvider>();
      await tabletSession.load();
      if (!mounted) return;
      final authRoom = auth.user?.roomNumber?.trim() ?? '';
      if (authRoom.isNotEmpty) await tabletSession.setRoomNumber(authRoom);
      await tabletSession.tryRestoreSessionFromRoom();
      if (!mounted) return;
      final code = tabletSession.clientCodeForPreFill;
      if (code != null && code.isNotEmpty && _clientCodeController.text.isEmpty) {
        _clientCodeController.text = code;
        if (mounted) setState(() {});
      }
    });
  }

  Future<void> _resolveServiceId() async {
    try {
      final api = PalaceApi();
      final services = await api.getPalaceServices();
      PalaceService? vs;
      for (final s in services) {
        if (s.isVehicleService) {
          vs = s;
          break;
        }
      }
      if (vs != null && mounted) setState(() => _serviceId = vs!.id);
    } catch (_) {}
  }

  @override
  void dispose() {
    _detailsController.dispose();
    _clientCodeController.dispose();
    _multiDaysController.dispose();
    super.dispose();
  }

  // ── Prix estimé ──────────────────────────────────────────────────────────
  double? get _estimatedPrice {
    final (days: rd, hours: rh) = _effectiveDuration;
    // Utilise les véhicules du type sélectionné pour calculer un prix représentatif.
    final candidates = _selectedType == null
        ? widget.vehicles
        : widget.vehicles.where((v) => v.vehicleType == _selectedType).toList();
    if (candidates.isEmpty) return null;
    // Affiche la plage basse (premier véhicule trié par prix croissant).
    final sorted = List<Vehicle>.from(candidates)
      ..sort((a, b) {
        final ap = a.pricePerDay ?? a.priceHalfDay ?? double.maxFinite;
        final bp = b.pricePerDay ?? b.priceHalfDay ?? double.maxFinite;
        return ap.compareTo(bp);
      });
    return sorted.first.estimatePrice(
      rentalDays: rd,
      rentalDurationHours: rh,
    );
  }

  ({int? days, int? hours}) get _effectiveDuration {
    return switch (_durationMode) {
      'half' => (days: null, hours: 4),
      'day' => (days: 1, hours: null),
      'multi' => (
          days: int.tryParse(_multiDaysController.text.trim()),
          hours: null,
        ),
      _ => (days: null, hours: null),
    };
  }

  // ── Types de véhicules disponibles dans les données ──────────────────────
  List<({String value, String label})> get _types {
    final l10n = AppLocalizations.of(context);
    final labelOf = {
      'berline': l10n.vehicleTypeBerline,
      'suv': l10n.vehicleTypeSuv,
      'minibus': l10n.vehicleTypeMinibus,
      'van': l10n.vehicleTypeVan,
      'other': l10n.vehicleTypeOther,
    };
    // Retourne uniquement les types présents dans la liste des véhicules.
    final seen = <String>{};
    final result = <({String value, String label})>[];
    for (final v in widget.vehicles) {
      if (seen.add(v.vehicleType)) {
        result.add((value: v.vehicleType, label: labelOf[v.vehicleType] ?? v.vehicleTypeLabel));
      }
    }
    return result;
  }

  void _showSnack(String msg, {bool isError = false}) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(msg),
      backgroundColor: isError ? Colors.red : AppTheme.accentGold,
    ));
  }

  Future<void> _submit() async {
    final l10n = AppLocalizations.of(context);
    if (_serviceId == null) {
      _showSnack(l10n.vehicleRentalNotConfigured, isError: true);
      return;
    }
    final (days: rd, hours: rh) = _effectiveDuration;
    if (_durationMode == 'multi' && (rd == null || rd < 1)) {
      _showSnack(l10n.durationOrDaysRequired, isError: true);
      return;
    }
    final clientCode = _clientCodeController.text.trim().isEmpty
        ? null
        : _clientCodeController.text.trim();

    setState(() => _sending = true);
    try {
      final meta = <String, dynamic>{
        'vehicle_request_type': 'rental',
        if (_selectedType != null) 'vehicle_type': _selectedType,
        'duration_mode': _durationMode,
        if (rd != null && rd > 0) 'rental_days': rd,
        if (rh != null && rh > 0) 'rental_duration_hours': rh,
      };

      await context.read<PalaceProvider>().createPalaceRequest(
            serviceId: _serviceId!,
            details: _detailsController.text.trim().isEmpty
                ? null
                : _detailsController.text.trim(),
            scheduledTime: _requestedFor,
            metadata: meta,
            clientCode: clientCode,
          );

      if (mounted) {
        _showSnack(l10n.requestSentMessage);
        Navigator.of(context).popUntil((r) => r.isFirst);
      }
    } catch (e) {
      if (mounted) _showSnack(e.toString(), isError: true);
    } finally {
      if (mounted) setState(() => _sending = false);
    }
  }

  // ── Build ─────────────────────────────────────────────────────────────────
  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final w = MediaQuery.sizeOf(context).width;
    final isMobile = w < 600;
    final pad = isMobile ? 12.0 : 20.0;

    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [AppTheme.primaryDark, AppTheme.primaryBlue],
          ),
        ),
        child: SafeArea(
          child: Column(
            children: [
              // ─── En-tête ──────────────────────────────────────────────
              Padding(
                padding: EdgeInsets.all(pad),
                child: Row(
                  children: [
                    IconButton(
                      icon: const Icon(Icons.arrow_back, color: AppTheme.accentGold),
                      onPressed: () {
                        HapticHelper.lightImpact();
                        Navigator.pop(context);
                      },
                    ),
                    SizedBox(width: isMobile ? 8 : 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            l10n.vehicleRentalTitle,
                            style: TextStyle(
                              fontSize: isMobile ? 20 : 22,
                              fontWeight: FontWeight.bold,
                              color: AppTheme.accentGold,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            l10n.requestVehicleRental,
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
              // ─── Contenu ──────────────────────────────────────────────
              Expanded(
                child: SingleChildScrollView(
                  padding: EdgeInsets.symmetric(
                    horizontal: LayoutHelper.horizontalPaddingValue(context),
                    vertical: 8,
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      // ── Type de véhicule ──────────────────────────────
                      _label(l10n.filterVehicleType),
                      const SizedBox(height: 8),
                      _typeSelector(),
                      const SizedBox(height: 20),

                      // ── Durée ─────────────────────────────────────────
                      _label(l10n.rentalDuration),
                      const SizedBox(height: 8),
                      _durationSelector(l10n),
                      if (_durationMode == 'multi') ...[
                        const SizedBox(height: 12),
                        TextField(
                          controller: _multiDaysController,
                          keyboardType: TextInputType.number,
                          style: const TextStyle(color: Colors.white),
                          decoration: InputDecoration(
                            labelText: l10n.rentalDays,
                            hintText: l10n.rentalDaysHint,
                            labelStyle: const TextStyle(color: AppTheme.accentGold),
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
                      ],
                      const SizedBox(height: 20),

                      // ── Prix estimé ───────────────────────────────────
                      if (_estimatedPrice != null) ...[
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 16,
                            vertical: 12,
                          ),
                          decoration: BoxDecoration(
                            color: AppTheme.accentGold.withValues(alpha: 0.15),
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(
                              color: AppTheme.accentGold.withValues(alpha: 0.5),
                            ),
                          ),
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text(
                                l10n.estimatedPrice,
                                style: const TextStyle(
                                  color: AppTheme.textGray,
                                  fontSize: 14,
                                ),
                              ),
                              Text(
                                '${_estimatedPrice!.toInt()} FCFA',
                                style: const TextStyle(
                                  color: AppTheme.accentGold,
                                  fontSize: 18,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(height: 20),
                      ],

                      // ── Code client ───────────────────────────────────
                      TextField(
                        controller: _clientCodeController,
                        style: const TextStyle(color: Colors.white),
                        decoration: InputDecoration(
                          labelText: 'Code client',
                          hintText: 'Ex: ABC-123',
                          labelStyle: const TextStyle(color: AppTheme.accentGold),
                          hintStyle: TextStyle(
                            color: AppTheme.textGray.withValues(alpha: 0.7),
                          ),
                          prefixIcon: const Icon(
                            Icons.badge_outlined,
                            color: AppTheme.accentGold,
                            size: 20,
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

                      // ── Date souhaitée ────────────────────────────────
                      _dateField(l10n),
                      const SizedBox(height: 16),

                      // ── Commentaire ───────────────────────────────────
                      TextField(
                        controller: _detailsController,
                        maxLines: 3,
                        style: const TextStyle(color: Colors.white),
                        decoration: InputDecoration(
                          labelText: l10n.description,
                          hintText: l10n.describeRequest,
                          labelStyle: const TextStyle(color: AppTheme.accentGold),
                          hintStyle: TextStyle(
                            color: AppTheme.textGray.withValues(alpha: 0.7),
                          ),
                          border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12)),
                          enabledBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12),
                            borderSide: BorderSide(
                              color: AppTheme.accentGold.withValues(alpha: 0.4),
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(height: 24),

                      // ── Bouton envoyer ────────────────────────────────
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
                      const SizedBox(height: 16),
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

  // ── Widgets helpers ────────────────────────────────────────────────────────

  Widget _label(String text) => Text(
        text,
        style: const TextStyle(
          color: AppTheme.accentGold,
          fontSize: 14,
          fontWeight: FontWeight.w500,
        ),
      );

  Widget _typeSelector() {
    final l10n = AppLocalizations.of(context);
    final items = _types;
    return Wrap(
      spacing: 8,
      runSpacing: 8,
      children: [
        // Chip "Tous"
        _typeChip(null, l10n.allOption ?? 'Tous'),
        for (final t in items) _typeChip(t.value, t.label),
      ],
    );
  }

  Widget _typeChip(String? value, String label) {
    final selected = _selectedType == value;
    return GestureDetector(
      onTap: () => setState(() => _selectedType = value),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
        decoration: BoxDecoration(
          gradient: selected
              ? LinearGradient(
                  colors: [
                    AppTheme.accentGold,
                    AppTheme.accentGold.withValues(alpha: 0.8),
                  ],
                )
              : null,
          color: selected ? null : AppTheme.primaryBlue.withValues(alpha: 0.5),
          borderRadius: BorderRadius.circular(25),
          border: Border.all(
            color: selected
                ? AppTheme.accentGold
                : AppTheme.accentGold.withValues(alpha: 0.3),
          ),
        ),
        child: Text(
          label,
          style: TextStyle(
            color: selected ? AppTheme.primaryDark : AppTheme.textGray,
            fontWeight: selected ? FontWeight.bold : FontWeight.normal,
            fontSize: 13,
          ),
        ),
      ),
    );
  }

  Widget _durationSelector(AppLocalizations l10n) {
    final options = [
      (value: 'half', label: l10n.halfDayOption ?? 'Demi-journée'),
      (value: 'day', label: l10n.fullDayOption ?? '1 Journée'),
      (value: 'multi', label: l10n.multipleDaysOption ?? 'Plusieurs jours'),
    ];
    return Row(
      children: options
          .map(
            (o) => Expanded(
              child: Padding(
                padding: const EdgeInsets.only(right: 8),
                child: GestureDetector(
                  onTap: () => setState(() => _durationMode = o.value),
                  child: AnimatedContainer(
                    duration: const Duration(milliseconds: 200),
                    padding: const EdgeInsets.symmetric(vertical: 12),
                    decoration: BoxDecoration(
                      gradient: _durationMode == o.value
                          ? LinearGradient(
                              colors: [
                                AppTheme.accentGold,
                                AppTheme.accentGold.withValues(alpha: 0.8),
                              ],
                            )
                          : null,
                      color: _durationMode == o.value
                          ? null
                          : AppTheme.primaryBlue.withValues(alpha: 0.5),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(
                        color: _durationMode == o.value
                            ? AppTheme.accentGold
                            : AppTheme.accentGold.withValues(alpha: 0.3),
                      ),
                    ),
                    child: Text(
                      o.label,
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        color: _durationMode == o.value
                            ? AppTheme.primaryDark
                            : AppTheme.textGray,
                        fontWeight: _durationMode == o.value
                            ? FontWeight.bold
                            : FontWeight.normal,
                        fontSize: 12,
                      ),
                    ),
                  ),
                ),
              ),
            ),
          )
          .toList(),
    );
  }

  Widget _dateField(AppLocalizations l10n) {
    return InkWell(
      onTap: () async {
        final ctx = context;
        final date = await showDatePicker(
          context: ctx,
          initialDate: DateTime.now(),
          firstDate: DateTime.now(),
          lastDate: DateTime.now().add(const Duration(days: 365)),
        );
        if (date == null || !ctx.mounted) return;
        final time = await showTimePicker(
          context: ctx,
          initialTime: TimeOfDay.now(),
        );
        if (time == null || !ctx.mounted) return;
        setState(() => _requestedFor = DateTime(
              date.year,
              date.month,
              date.day,
              time.hour,
              time.minute,
            ));
      },
      borderRadius: BorderRadius.circular(12),
      child: InputDecorator(
        decoration: InputDecoration(
          labelText: l10n.rentalDate,
          labelStyle: const TextStyle(color: AppTheme.accentGold),
          border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
          enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide:
                BorderSide(color: AppTheme.accentGold.withValues(alpha: 0.4)),
          ),
          suffixIcon:
              const Icon(Icons.calendar_today, color: AppTheme.accentGold, size: 18),
        ),
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
}
