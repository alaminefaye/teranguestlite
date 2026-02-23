import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../utils/haptic_helper.dart';
import '../../utils/layout_helper.dart';
import '../../providers/auth_provider.dart';
import '../../providers/palace_provider.dart';
import '../../models/palace.dart';
/// Sport & Fitness : affichage des horaires de la salle + réservation d'un coach personnel.
class SportFitnessScreen extends StatefulWidget {
  const SportFitnessScreen({super.key});

  @override
  State<SportFitnessScreen> createState() => _SportFitnessScreenState();
}

class _SportFitnessScreenState extends State<SportFitnessScreen> {
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

  void _onBookCoachTap(BuildContext context) {
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
              l10n.sportFitnessBookCoach,
              style: const TextStyle(color: AppTheme.accentGold),
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
                    maxLines: 2,
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
                  final parts = <String>['Sport & Fitness - Réservation coach personnel'];
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
    ).then((_) => notesController.dispose());
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final user = context.watch<AuthProvider>().user;
    final gymHours = user?.enterprise?.gymHours?.trim();
    final gymHoursDisplay = (gymHours != null && gymHours.isNotEmpty)
        ? gymHours
        : l10n.gymHoursDefault;

    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(
          gradient: AppTheme.backgroundGradient,
        ),
        child: SafeArea(
          child: Column(
            children: [
              _buildAppBar(context, l10n),
              Expanded(
                child: SingleChildScrollView(
                  padding: LayoutHelper.horizontalPadding(context).copyWith(top: 20, bottom: 32),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      _buildGymHoursCard(l10n.sportFitnessGymHours, gymHoursDisplay),
                      const SizedBox(height: 20),
                      _buildCoachCard(context, l10n),
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

  Widget _buildGymHoursCard(String title, String hoursText) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: AppTheme.primaryDark.withValues(alpha: 0.4),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: AppTheme.accentGold.withValues(alpha: 0.6),
          width: 1.5,
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(Icons.schedule_outlined, color: AppTheme.accentGold, size: 24),
              const SizedBox(width: 10),
              Text(
                title,
                style: const TextStyle(
                  fontSize: 17,
                  fontWeight: FontWeight.bold,
                  color: AppTheme.accentGold,
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          Text(
            hoursText,
            style: const TextStyle(
              fontSize: 15,
              height: 1.45,
              color: Colors.white,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCoachCard(BuildContext context, AppLocalizations l10n) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: () => _onBookCoachTap(context),
        borderRadius: BorderRadius.circular(16),
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 28, horizontal: 20),
          decoration: BoxDecoration(
            color: AppTheme.primaryDark.withValues(alpha: 0.4),
            borderRadius: BorderRadius.circular(16),
            border: Border.all(
              color: AppTheme.accentGold.withValues(alpha: 0.6),
              width: 1.5,
            ),
          ),
          child: Column(
            children: [
              Icon(
                Icons.fitness_center_outlined,
                size: 44,
                color: AppTheme.accentGold,
              ),
              const SizedBox(height: 14),
              Text(
                l10n.sportFitnessBookCoach,
                textAlign: TextAlign.center,
                style: const TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: Colors.white,
                ),
              ),
              const SizedBox(height: 18),
              FilledButton.icon(
                onPressed: () => _onBookCoachTap(context),
                icon: const Icon(Icons.calendar_today_outlined, size: 18),
                label: Text(l10n.book),
                style: FilledButton.styleFrom(
                  backgroundColor: AppTheme.accentGold,
                  foregroundColor: AppTheme.primaryDark,
                  padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
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

  Widget _buildAppBar(BuildContext context, AppLocalizations l10n) {
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
                  l10n.sportFitnessTitle,
                  style: TextStyle(
                    fontSize: MediaQuery.of(context).size.width < 600 ? 18 : 28,
                    fontWeight: FontWeight.bold,
                    color: AppTheme.accentGold,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  l10n.sportFitnessSubtitle,
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
    );
  }
}
