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
import '../../main.dart'; // import for rootNavigatorKey
import '../palace/my_palace_requests_screen.dart';

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
              side: BorderSide(
                color: AppTheme.accentGold.withValues(alpha: 0.5),
              ),
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
                    maxLines: 3,
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
                  final parts = <String>[
                    '${widget.activity.name}${l10n.requestDemandeSuffix}',
                  ];
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
    );
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);

    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(gradient: AppTheme.backgroundGradient),
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
                  padding: const EdgeInsets.symmetric(
                    horizontal: 24,
                    vertical: 14,
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

  Widget _buildAppBar(BuildContext context) {
    final hasDescription =
        widget.activity.description != null &&
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
                  style: TextStyle(
                    fontSize: MediaQuery.of(context).size.width < 600 ? 18 : 28,
                    fontWeight: FontWeight.bold,
                    color: AppTheme.accentGold,
                  ),
                ),
                if (hasDescription) ...[
                  const SizedBox(height: 4),
                  Text(
                    widget.activity.description!,
                    style: const TextStyle(
                      fontSize: 14,
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
