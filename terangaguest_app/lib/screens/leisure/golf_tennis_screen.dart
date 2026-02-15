import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../utils/haptic_helper.dart';
import '../../utils/layout_helper.dart';
import '../../widgets/service_card.dart';
import '../../providers/palace_provider.dart';
import '../../models/palace.dart';

/// Golf & Tennis : Tee-time, Court de tennis, Location de matériel.
/// Chaque option ouvre un formulaire (date/heure + précisions) → demande Palace (concierge).
class GolfTennisScreen extends StatefulWidget {
  const GolfTennisScreen({super.key});

  @override
  State<GolfTennisScreen> createState() => _GolfTennisScreenState();
}

class _GolfTennisScreenState extends State<GolfTennisScreen> {
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

  void _onOptionTap(BuildContext context, String optionKey, String optionLabel) {
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
              optionLabel,
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
                  final parts = <String>['Golf & Tennis - $optionLabel'];
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
    final crossAxisCount = LayoutHelper.gridCrossAxisCount(context);
    final aspectRatio = LayoutHelper.dashboardCellAspectRatio(context);
    final spacing = LayoutHelper.gridSpacing(context);

    final options = [
      (l10n.golfTennisTeetime, Icons.sports_golf_outlined, 'tee_time'),
      (l10n.golfTennisCourt, Icons.sports_tennis_outlined, 'court'),
      (l10n.golfTennisEquipment, Icons.sports_outlined, 'equipment'),
    ];

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
                child: Padding(
                  padding: LayoutHelper.horizontalPadding(context),
                  child: GridView.builder(
                    padding: EdgeInsets.symmetric(vertical: spacing),
                    gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                      crossAxisCount: crossAxisCount,
                      crossAxisSpacing: spacing,
                      mainAxisSpacing: spacing,
                      childAspectRatio: aspectRatio,
                    ),
                    itemCount: options.length,
                    itemBuilder: (context, index) {
                      final (title, icon, key) = options[index];
                      return ServiceCard(
                        title: title,
                        icon: icon,
                        onTap: () => _onOptionTap(context, key, title),
                      );
                    },
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
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 12),
      child: Row(
        children: [
          IconButton(
            onPressed: () {
              HapticHelper.lightImpact();
              Navigator.of(context).pop();
            },
            icon: const Icon(Icons.arrow_back_ios_new, color: AppTheme.accentGold),
          ),
          Expanded(
            child: Text(
              l10n.golfTennisTitle,
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.w800,
                color: AppTheme.accentGold,
                letterSpacing: 0.5,
              ),
            ),
          ),
          const SizedBox(width: 48),
        ],
      ),
    );
  }
}
