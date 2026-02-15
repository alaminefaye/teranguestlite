import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../utils/haptic_helper.dart';
import '../../utils/layout_helper.dart';
import '../../providers/palace_provider.dart';
import '../../models/leisure_category.dart';
import '../../models/palace.dart';

/// Écran générique pour toute activité loisir/sport (Squash, Piscine, Yoga, etc.) :
/// une demande de réservation ou d'info (date, heure, précisions) envoyée au concierge (Palace).
class LeisureRequestScreen extends StatefulWidget {
  const LeisureRequestScreen({super.key, required this.activity});

  final LeisureCategoryDto activity;

  @override
  State<LeisureRequestScreen> createState() => _LeisureRequestScreenState();
}

class _LeisureRequestScreenState extends State<LeisureRequestScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<PalaceProvider>().fetchPalaceServices();
    });
  }

  PalaceService? _getConciergeService(List<PalaceService> services) {
    final concierge = services
        .where((s) => s.isAvailable && (s.category == 'concierge'))
        .toList();
    if (concierge.isNotEmpty) return concierge.first;
    final available = services.where((s) => s.isAvailable).toList();
    return available.isNotEmpty ? available.first : null;
  }

  void _openRequestDialog(BuildContext context) {
    HapticHelper.lightImpact();
    final l10n = AppLocalizations.of(context);
    final provider = context.read<PalaceProvider>();
    final service = _getConciergeService(provider.services);

    if (service == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(l10n.noPalaceServiceHint),
          backgroundColor: Colors.orange,
        ),
      );
      return;
    }

    DateTime? selectedDate;
    TimeOfDay? selectedTime;
    final notesController = TextEditingController();

    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setState) {
          return AlertDialog(
            backgroundColor: AppTheme.primaryBlue,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(16),
              side: BorderSide(color: AppTheme.accentGold.withValues(alpha: 0.5)),
            ),
            title: Text(
              '${widget.activity.name} - ${l10n.book}',
              style: const TextStyle(color: AppTheme.accentGold, fontSize: 18),
            ),
            content: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  ListTile(
                    title: Text(
                      selectedDate == null
                          ? l10n.selectDate
                          : DateFormat('EEEE d MMMM', 'fr_FR').format(selectedDate!),
                      style: const TextStyle(color: Colors.white),
                    ),
                    trailing: const Icon(Icons.calendar_today, color: AppTheme.accentGold),
                    onTap: () async {
                      final date = await showDatePicker(
                        context: context,
                        initialDate: DateTime.now(),
                        firstDate: DateTime.now(),
                        lastDate: DateTime.now().add(const Duration(days: 365)),
                      );
                      if (date != null) setState(() => selectedDate = date);
                    },
                  ),
                  ListTile(
                    title: Text(
                      selectedTime == null
                          ? l10n.time
                          : '${selectedTime!.hour.toString().padLeft(2, '0')}:${selectedTime!.minute.toString().padLeft(2, '0')}',
                      style: const TextStyle(color: Colors.white),
                    ),
                    trailing: const Icon(Icons.access_time, color: AppTheme.accentGold),
                    onTap: () async {
                      final time = await showTimePicker(
                        context: context,
                        initialTime: TimeOfDay.now(),
                      );
                      if (time != null) setState(() => selectedTime = time);
                    },
                  ),
                  const SizedBox(height: 12),
                  TextField(
                    controller: notesController,
                    maxLines: 3,
                    decoration: InputDecoration(
                      hintText: l10n.describeRequest,
                      hintStyle: TextStyle(color: AppTheme.textGray.withValues(alpha: 0.7)),
                      filled: true,
                      fillColor: AppTheme.primaryDark.withValues(alpha: 0.6),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                        borderSide: BorderSide(color: AppTheme.accentGold.withValues(alpha: 0.4)),
                      ),
                    ),
                    style: const TextStyle(color: Colors.white),
                  ),
                ],
              ),
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.of(ctx).pop(),
                child: Text(l10n.cancel, style: const TextStyle(color: AppTheme.textGray)),
              ),
              FilledButton(
                onPressed: () async {
                  final parts = <String>['${widget.activity.name} - Demande'];
                  if (selectedDate != null) {
                    parts.add('Date: ${DateFormat('dd/MM/yyyy').format(selectedDate!)}');
                  }
                  if (selectedTime != null) {
                    parts.add('Heure: ${selectedTime!.hour.toString().padLeft(2, '0')}:${selectedTime!.minute.toString().padLeft(2, '0')}');
                  }
                  final notes = notesController.text.trim();
                  if (notes.isNotEmpty) parts.add(notes);
                  final description = parts.join('\n');

                  Navigator.of(ctx).pop();
                  try {
                    await context.read<PalaceProvider>().createPalaceRequest(
                          serviceId: service.id,
                          details: description,
                        );
                    if (!context.mounted) return;
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(
                        content: Text(l10n.requestSent),
                        backgroundColor: AppTheme.accentGold,
                        duration: const Duration(seconds: 2),
                      ),
                    );
                  } catch (e) {
                    if (!context.mounted) return;
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(
                        content: Text('${l10n.errorPrefix}$e'),
                        backgroundColor: Colors.red,
                      ),
                    );
                  }
                },
                style: FilledButton.styleFrom(
                  backgroundColor: AppTheme.accentGold,
                  foregroundColor: AppTheme.primaryDark,
                ),
                child: Text(l10n.sendRequest),
              ),
            ],
          );
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);

    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(
          gradient: AppTheme.backgroundGradient,
        ),
        child: SafeArea(
          child: Column(
            children: [
              _buildAppBar(context),
              Expanded(
                child: Center(
                  child: Padding(
                    padding: LayoutHelper.horizontalPadding(context),
                    child: _buildReservationCard(context, l10n),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildReservationCard(BuildContext context, AppLocalizations l10n) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: () => _openRequestDialog(context),
        borderRadius: BorderRadius.circular(16),
        child: Container(
          width: double.infinity,
          padding: const EdgeInsets.symmetric(vertical: 32, horizontal: 24),
          decoration: BoxDecoration(
            color: AppTheme.primaryDark.withValues(alpha: 0.4),
            borderRadius: BorderRadius.circular(16),
            border: Border.all(
              color: AppTheme.accentGold.withValues(alpha: 0.6),
              width: 1.5,
            ),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(
                Icons.calendar_month_outlined,
                size: 48,
                color: AppTheme.accentGold,
              ),
              const SizedBox(height: 16),
              Text(
                l10n.book,
                style: const TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.bold,
                  color: Colors.white,
                ),
              ),
              const SizedBox(height: 8),
              Text(
                l10n.requestReservationHint,
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 14,
                  color: AppTheme.textGray,
                  height: 1.3,
                ),
              ),
              const SizedBox(height: 20),
              FilledButton.icon(
                onPressed: () => _openRequestDialog(context),
                icon: const Icon(Icons.edit_calendar_outlined, size: 20),
                label: Text(l10n.sendRequest),
                style: FilledButton.styleFrom(
                  backgroundColor: AppTheme.accentGold,
                  foregroundColor: AppTheme.primaryDark,
                  padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 14),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildAppBar(BuildContext context) {
    final hasDescription = widget.activity.description != null &&
        widget.activity.description!.isNotEmpty;

    return Padding(
      padding: const EdgeInsets.all(20.0),
      child: Row(
        children: [
          IconButton(
            icon: const Icon(Icons.arrow_back, color: AppTheme.accentGold),
            onPressed: () {
              HapticHelper.lightImpact();
              Navigator.of(context).pop();
            },
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  widget.activity.name,
                  style: const TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.bold,
                    color: Colors.white,
                  ),
                ),
                if (hasDescription) ...[
                  const SizedBox(height: 4),
                  Text(
                    widget.activity.description!,
                    style: const TextStyle(
                      fontSize: 13,
                      color: AppTheme.textGray,
                    ),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }
}
