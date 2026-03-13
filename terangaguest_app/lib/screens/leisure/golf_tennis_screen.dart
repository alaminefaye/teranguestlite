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
import '../../main.dart'; // import for rootNavigatorKey
import '../palace/my_palace_requests_screen.dart';

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
    // 1. Try to find a service explicitly in 'sport_loisir' category
    final sportServices = services
        .where((s) => s.isAvailable && s.category == 'sport_loisir')
        .toList();
    if (sportServices.isNotEmpty) return sportServices.first;

    // 2. Fall back to 'concierge' if no 'sport_loisir' exists
    final concierge = services
        .where((s) => s.isAvailable && s.category == 'concierge')
        .toList();
    // Try to avoid "Assistance médecin" specifically by favoring others.
    if (concierge.isNotEmpty) {
      final fallback = concierge
          .where((s) => !s.name.toLowerCase().contains('médecin'))
          .toList();
      if (fallback.isNotEmpty) return fallback.first;
      return concierge.first;
    }

    // 3. Any available service as a last resort
    final available = services.where((s) => s.isAvailable).toList();
    return available.isNotEmpty ? available.first : null;
  }

  void _onOptionTap(
    BuildContext context,
    String optionKey,
    String optionLabel,
  ) {
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
              side: BorderSide(
                color: AppTheme.accentGold.withValues(alpha: 0.5),
              ),
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
                          : DateFormat(
                              'EEEE d MMMM',
                              Localizations.localeOf(context).languageCode,
                            ).format(selectedDate!),
                      style: const TextStyle(color: Colors.white),
                    ),
                    trailing: const Icon(
                      Icons.calendar_today,
                      color: AppTheme.accentGold,
                    ),
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
                    trailing: const Icon(
                      Icons.access_time,
                      color: AppTheme.accentGold,
                    ),
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
                      hintStyle: TextStyle(
                        color: AppTheme.textGray.withValues(alpha: 0.7),
                      ),
                      filled: true,
                      fillColor: AppTheme.primaryDark.withValues(alpha: 0.6),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                        borderSide: BorderSide(
                          color: AppTheme.accentGold.withValues(alpha: 0.4),
                        ),
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
                child: Text(
                  l10n.cancel,
                  style: const TextStyle(color: AppTheme.textGray),
                ),
              ),
              FilledButton(
                onPressed: () async {
                  final prefix = widget.sportType == 'tennis'
                      ? l10n.tennisPrefix
                      : l10n.golfPrefix;
                  final parts = <String>['$prefix - $optionLabel'];
                  if (selectedDate != null) {
                    parts.add(
                      '${l10n.datePrefix}${DateFormat('dd/MM/yyyy').format(selectedDate!)}',
                    );
                  }
                  if (selectedTime != null) {
                    parts.add(
                      '${l10n.timePrefix}${selectedTime!.hour.toString().padLeft(2, '0')}:${selectedTime!.minute.toString().padLeft(2, '0')}',
                    );
                  }
                  final notes = notesController.text.trim();
                  if (notes.isNotEmpty) parts.add(notes);
                  final description = parts.join('\n');

                  // Extract dependencies BEFORE popping the dialog context
                  final palaceProvider = context.read<PalaceProvider>();
                  final localScaffoldMessenger = ScaffoldMessenger.of(context);

                  // Use the global navigator key to show dialogs safely after context is popped
                  final navigator = rootNavigatorKey.currentState;

                  // Close the form dialog
                  Navigator.of(ctx).pop();

                  if (navigator == null) return;

                  // Show the loading dialog using the global navigator
                  showDialog(
                    context: navigator.context,
                    barrierDismissible: false,
                    useRootNavigator: true,
                    builder: (c) => const Center(
                      child: CircularProgressIndicator(
                        valueColor: AlwaysStoppedAnimation<Color>(
                          AppTheme.accentGold,
                        ),
                      ),
                    ),
                  );

                  try {
                    await palaceProvider
                        .createPalaceRequest(
                          serviceId: service.id,
                          details: description,
                        )
                        .timeout(
                          const Duration(seconds: 25),
                          onTimeout: () => throw Exception(l10n.timeoutError),
                        );

                    // Pop the loading dialog safely
                    navigator.pop();

                    if (!mounted) return;

                    // Show success dialog safely
                    showDialog(
                      context: navigator.context,
                      useRootNavigator: true,
                      builder: (dialogContext) => AlertDialog(
                        backgroundColor: AppTheme.primaryBlue,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(16),
                          side: const BorderSide(
                            color: AppTheme.accentGold,
                            width: 2,
                          ),
                        ),
                        title: Row(
                          children: [
                            const Icon(
                              Icons.check_circle,
                              color: Colors.green,
                              size: 32,
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Text(
                                l10n.requestSent,
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 18,
                                ),
                              ),
                            ),
                          ],
                        ),
                        content: Text(
                          l10n.requestSentMessage,
                          style: const TextStyle(color: AppTheme.textGray),
                        ),
                        actions: [
                          TextButton(
                            onPressed: () => Navigator.of(
                              dialogContext,
                              rootNavigator: true,
                            ).pop(),
                            child: Text(
                              l10n.ok,
                              style: const TextStyle(
                                color: AppTheme.textGray,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                          TextButton(
                            onPressed: () {
                              Navigator.of(
                                dialogContext,
                                rootNavigator: true,
                              ).pop();
                              final nav = rootNavigatorKey.currentState;
                              if (nav != null) {
                                nav.push(
                                  MaterialPageRoute(
                                    builder: (_) =>
                                        const MyPalaceRequestsScreen(),
                                  ),
                                );
                              }
                            },
                            child: Text(
                              l10n.viewMyRequests,
                              style: const TextStyle(
                                color: AppTheme.accentGold,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                        ],
                      ),
                    );
                  } catch (e) {
                    // Pop the loading dialog safely if an error occurs
                    navigator.pop();
                    localScaffoldMessenger.showSnackBar(
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
        decoration: const BoxDecoration(gradient: AppTheme.backgroundGradient),
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
