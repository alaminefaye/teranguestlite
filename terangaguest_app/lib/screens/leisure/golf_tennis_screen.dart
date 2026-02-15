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

/// Golf ou Tennis : chaque sport a sa propre page (Tee-time + matériel pour Golf, Court + matériel pour Tennis).
class GolfTennisScreen extends StatefulWidget {
  const GolfTennisScreen({super.key, required this.sportType});

  /// 'golf' = options Golf (Tee-time, Matériel). 'tennis' = options Tennis (Court, Matériel).
  final String sportType;

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
                  final prefix = widget.sportType == 'tennis' ? 'Tennis' : 'Golf';
                  final parts = <String>['$prefix - $optionLabel'];
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

    final isGolf = widget.sportType == 'golf';
    final options = isGolf
        ? [
            (l10n.golfTennisTeetime, Icons.sports_golf_outlined, 'tee_time'),
            (l10n.golfTennisEquipment, Icons.sports_outlined, 'equipment'),
          ]
        : [
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
    final isGolf = widget.sportType == 'golf';
    final title = isGolf ? l10n.golfTitle : l10n.tennisTitle;
    final subtitle = isGolf ? l10n.golfSubtitle : l10n.tennisSubtitle;
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
                  title,
                  style: const TextStyle(
                    fontSize: 24,
                    fontWeight: FontWeight.bold,
                    color: Colors.white,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  subtitle,
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
    );
  }
}
