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
import '../../main.dart'; // import for rootNavigatorKey
import '../palace/my_palace_requests_screen.dart';

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
              side: BorderSide(
                color: AppTheme.accentGold.withValues(alpha: 0.5),
              ),
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
                          : DateFormat(
                              'EEEE d MMMM',
                              'fr_FR',
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
                  final parts = <String>[l10n.sportFitnessCoachBooking];
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
    final user = context.watch<AuthProvider>().user;
    final gymHours = user?.enterprise?.gymHours?.trim();
    final gymHoursDisplay = (gymHours != null && gymHours.isNotEmpty)
        ? gymHours
        : l10n.gymHoursDefault;

    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(gradient: AppTheme.backgroundGradient),
        child: SafeArea(
          child: Column(
            children: [
              _buildAppBar(context, l10n),
              Expanded(
                child: SingleChildScrollView(
                  padding: LayoutHelper.horizontalPadding(
                    context,
                  ).copyWith(top: 20, bottom: 32),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      _buildGymHoursCard(
                        l10n.sportFitnessGymHours,
                        gymHoursDisplay,
                      ),
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
              Icon(
                Icons.schedule_outlined,
                color: AppTheme.accentGold,
                size: 24,
              ),
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
                  padding: const EdgeInsets.symmetric(
                    horizontal: 24,
                    vertical: 12,
                  ),
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
