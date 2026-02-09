import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../models/excursion.dart';
import '../../providers/auth_provider.dart';
import '../../providers/excursions_provider.dart';
import '../../utils/navigation_helper.dart';
import '../../utils/haptic_helper.dart';
import '../../widgets/animated_button.dart';
import 'my_excursion_bookings_screen.dart';

class BookExcursionScreen extends StatefulWidget {
  final Excursion excursion;

  const BookExcursionScreen({super.key, required this.excursion});

  @override
  State<BookExcursionScreen> createState() => _BookExcursionScreenState();
}

class _BookExcursionScreenState extends State<BookExcursionScreen> {
  DateTime? _selectedDate;
  int _adultsCount = 1;
  int _childrenCount = 0;
  final TextEditingController _specialRequestsController =
      TextEditingController();
  final TextEditingController _clientCodeController = TextEditingController();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<AuthProvider>().loadUser();
    });
  }

  @override
  void dispose() {
    _specialRequestsController.dispose();
    _clientCodeController.dispose();
    super.dispose();
  }

  double get _totalPrice {
    return (widget.excursion.priceAdult * _adultsCount) +
        (widget.excursion.priceChild * _childrenCount);
  }

  @override
  Widget build(BuildContext context) {
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
                      onPressed: () {
                        HapticHelper.lightImpact();
                        Navigator.pop(context);
                      },
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            AppLocalizations.of(context).reserve,
                            style: const TextStyle(
                              fontSize: 24,
                              fontWeight: FontWeight.bold,
                              color: Colors.white,
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
                  padding: const EdgeInsets.symmetric(
                    horizontal: 60,
                    vertical: 20,
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      _buildCanReserveBanner(),
                      _buildDateSelector(),
                      const SizedBox(height: 24),
                      _buildParticipantsSelector(),
                      const SizedBox(height: 24),
                      _buildSpecialRequests(),
                      const SizedBox(height: 30),
                      _buildSummary(),
                      const SizedBox(height: 30),
                      _buildConfirmButton(),
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

  Widget _buildDateSelector() {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [AppTheme.primaryBlue, AppTheme.primaryDark],
        ),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppTheme.accentGold, width: 1.5),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            AppLocalizations.of(context).date,
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.bold,
              color: AppTheme.accentGold,
            ),
          ),
          const SizedBox(height: 12),
          InkWell(
            onTap: () async {
              final DateTime? picked = await showDatePicker(
                context: context,
                initialDate: _selectedDate ?? DateTime.now(),
                firstDate: DateTime.now(),
                lastDate: DateTime.now().add(const Duration(days: 90)),
                builder: (context, child) {
                  return Theme(
                    data: ThemeData.dark().copyWith(
                      colorScheme: const ColorScheme.dark(
                        primary: AppTheme.accentGold,
                        onPrimary: AppTheme.primaryDark,
                        surface: AppTheme.primaryBlue,
                        onSurface: Colors.white,
                      ),
                    ),
                    child: child!,
                  );
                },
              );
              if (picked != null) {
                setState(() {
                  _selectedDate = picked;
                });
              }
            },
            child: Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: AppTheme.primaryBlue.withValues(alpha: 0.5),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(
                  color: AppTheme.accentGold.withValues(alpha: 0.3),
                ),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    _selectedDate != null
                        ? DateFormat(
                            'dd MMMM yyyy',
                            'fr_FR',
                          ).format(_selectedDate!)
                        : AppLocalizations.of(context).selectDate,
                    style: TextStyle(
                      fontSize: 15,
                      color: _selectedDate != null
                          ? Colors.white
                          : AppTheme.textGray,
                    ),
                  ),
                  const Icon(
                    Icons.calendar_today,
                    color: AppTheme.accentGold,
                    size: 20,
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildParticipantsSelector() {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [AppTheme.primaryBlue, AppTheme.primaryDark],
        ),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppTheme.accentGold, width: 1.5),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            AppLocalizations.of(context).participants,
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.bold,
              color: AppTheme.accentGold,
            ),
          ),
          const SizedBox(height: 16),
          Row(
            children: [
              Expanded(
                child: Column(
                  children: [
                    Text(
                      AppLocalizations.of(context).adults,
                      style: TextStyle(fontSize: 14, color: AppTheme.textGray),
                    ),
                    const SizedBox(height: 8),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        IconButton(
                          onPressed: _adultsCount > 1
                              ? () => setState(() => _adultsCount--)
                              : null,
                          icon: const Icon(Icons.remove_circle_outline),
                          color: AppTheme.accentGold,
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 16,
                            vertical: 8,
                          ),
                          decoration: BoxDecoration(
                            color: AppTheme.accentGold.withValues(alpha: 0.2),
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: AppTheme.accentGold),
                          ),
                          child: Text(
                            '$_adultsCount',
                            style: const TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                              color: AppTheme.accentGold,
                            ),
                          ),
                        ),
                        IconButton(
                          onPressed: _adultsCount < 20
                              ? () => setState(() => _adultsCount++)
                              : null,
                          icon: const Icon(Icons.add_circle_outline),
                          color: AppTheme.accentGold,
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              Expanded(
                child: Column(
                  children: [
                    Text(
                      AppLocalizations.of(context).children,
                      style: TextStyle(fontSize: 14, color: AppTheme.textGray),
                    ),
                    const SizedBox(height: 8),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        IconButton(
                          onPressed: _childrenCount > 0
                              ? () => setState(() => _childrenCount--)
                              : null,
                          icon: const Icon(Icons.remove_circle_outline),
                          color: AppTheme.accentGold,
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 16,
                            vertical: 8,
                          ),
                          decoration: BoxDecoration(
                            color: AppTheme.accentGold.withValues(alpha: 0.2),
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: AppTheme.accentGold),
                          ),
                          child: Text(
                            '$_childrenCount',
                            style: const TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                              color: AppTheme.accentGold,
                            ),
                          ),
                        ),
                        IconButton(
                          onPressed: _childrenCount < 20
                              ? () => setState(() => _childrenCount++)
                              : null,
                          icon: const Icon(Icons.add_circle_outline),
                          color: AppTheme.accentGold,
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildSpecialRequests() {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [AppTheme.primaryBlue, AppTheme.primaryDark],
        ),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppTheme.accentGold, width: 1.5),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            AppLocalizations.of(context).specialRequestsOptional,
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.bold,
              color: AppTheme.accentGold,
            ),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _specialRequestsController,
            style: const TextStyle(color: Colors.white),
            maxLines: 3,
            decoration: InputDecoration(
              hintText: AppLocalizations.of(
                context,
              ).allergiesPreferencesExample,
              hintStyle: TextStyle(
                color: AppTheme.textGray.withValues(alpha: 0.6),
              ),
              filled: true,
              fillColor: AppTheme.primaryBlue.withValues(alpha: 0.5),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: BorderSide(
                  color: AppTheme.accentGold.withValues(alpha: 0.3),
                ),
              ),
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
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSummary() {
    if (_selectedDate == null) return const SizedBox.shrink();
    final l10n = AppLocalizations.of(context);
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [
            AppTheme.accentGold.withValues(alpha: 0.2),
            AppTheme.primaryDark,
          ],
        ),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppTheme.accentGold, width: 2),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            l10n.summary,
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: AppTheme.accentGold,
            ),
          ),
          const Divider(height: 24, color: AppTheme.textGray),
          _buildSummaryRow(l10n.excursion, widget.excursion.name),
          const SizedBox(height: 12),
          _buildSummaryRow(
            l10n.date,
            DateFormat('EEEE dd MMMM yyyy', 'fr_FR').format(_selectedDate!),
          ),
          const SizedBox(height: 12),
          _buildSummaryRow(l10n.adults, '$_adultsCount'),
          const SizedBox(height: 12),
          _buildSummaryRow(l10n.children, '$_childrenCount'),
          const Divider(height: 24, color: AppTheme.textGray),
          _buildSummaryRow(
            l10n.total,
            '${_totalPrice.toStringAsFixed(0)} FCFA',
          ),
        ],
      ),
    );
  }

  Widget _buildSummaryRow(String label, String value) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(
          label,
          style: const TextStyle(fontSize: 14, color: AppTheme.textGray),
        ),
        Text(
          value,
          style: const TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.bold,
            color: Colors.white,
          ),
          textAlign: TextAlign.right,
        ),
      ],
    );
  }

  Widget _buildCanReserveBanner() {
    final user = context.watch<AuthProvider>().user;
    if (user?.canReserve == true) return const SizedBox.shrink();
    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.orange.shade900.withValues(alpha: 0.4),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: Colors.orange, width: 1.5),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(Icons.info_outline, color: Colors.orange, size: 24),
              const SizedBox(width: 12),
              Expanded(
                child: Text(
                  'Les réservations sont réservées aux clients avec un séjour valide. Entrez votre code client ci-dessous (reçu à l\'enregistrement).',
                  style: const TextStyle(color: Colors.white, fontSize: 13),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _clientCodeController,
            style: const TextStyle(color: Colors.white, fontSize: 16),
            decoration: InputDecoration(
              hintText: 'Code client (ex: 123456)',
              hintStyle: TextStyle(
                color: AppTheme.textGray.withValues(alpha: 0.8),
              ),
              filled: true,
              fillColor: Colors.white.withValues(alpha: 0.15),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(10),
                borderSide: const BorderSide(color: Colors.orange),
              ),
              prefixIcon: const Icon(
                Icons.person_outline,
                color: Colors.orange,
                size: 22,
              ),
            ),
            onChanged: (_) => setState(() {}),
          ),
        ],
      ),
    );
  }

  Widget _buildConfirmButton() {
    final user = context.watch<AuthProvider>().user;
    final hasCode = _clientCodeController.text.trim().isNotEmpty;
    final canBook =
        ((user?.canReserve == true) || hasCode) && _selectedDate != null;

    return AnimatedButton(
      text: AppLocalizations.of(context).confirmReservation,
      onPressed: canBook ? _handleConfirmBooking : null,
      width: double.infinity,
      height: 56,
      backgroundColor: AppTheme.accentGold,
      textColor: AppTheme.primaryDark,
    );
  }

  Future<void> _handleConfirmBooking() async {
    if (_selectedDate == null) return;

    final auth = context.read<AuthProvider>();
    final clientCode = _clientCodeController.text.trim();
    final relyingOnCanReserve = clientCode.isEmpty && (auth.user?.canReserve == true);

    if (relyingOnCanReserve) {
      await auth.loadUser();
      if (!context.mounted) return;
      if (auth.user?.canReserve != true) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text(
                'Votre séjour n\'est plus actif. Entrez votre code client pour réserver.',
              ),
              backgroundColor: Colors.orange,
              duration: Duration(seconds: 4),
            ),
          );
        }
        return;
      }
    }

    try {
      showDialog(
        context: context,
        barrierDismissible: false,
        builder: (context) => const Center(
          child: CircularProgressIndicator(
            valueColor: AlwaysStoppedAnimation<Color>(AppTheme.accentGold),
          ),
        ),
      );

      await context.read<ExcursionsProvider>().bookExcursion(
        excursionId: widget.excursion.id,
        date: _selectedDate!,
        adultsCount: _adultsCount,
        childrenCount: _childrenCount,
        specialRequests: _specialRequestsController.text.isEmpty
            ? null
            : _specialRequestsController.text,
        clientCode: clientCode.isNotEmpty ? clientCode : null,
      );

      if (mounted) Navigator.pop(context);

      if (mounted) {
        showDialog(
          context: context,
          builder: (context) => AlertDialog(
            backgroundColor: AppTheme.primaryBlue,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(16),
              side: const BorderSide(color: AppTheme.accentGold, width: 2),
            ),
            title: Row(
              children: [
                const Icon(Icons.check_circle, color: Colors.green, size: 32),
                const SizedBox(width: 12),
                Text(
                  AppLocalizations.of(context).reservationConfirmed,
                  style: const TextStyle(color: Colors.white, fontSize: 18),
                ),
              ],
            ),
            content: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  AppLocalizations.of(
                    context,
                  ).excursionConfirmedMessage(_adultsCount + _childrenCount),
                  style: const TextStyle(color: AppTheme.textGray),
                ),
                const SizedBox(height: 16),
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: AppTheme.accentGold.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(
                      color: AppTheme.accentGold.withValues(alpha: 0.3),
                    ),
                  ),
                  child: Row(
                    children: [
                      const Icon(
                        Icons.notifications_active,
                        color: AppTheme.accentGold,
                        size: 20,
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          AppLocalizations.of(context).confirmationNotification,
                          style: const TextStyle(
                            fontSize: 12,
                            color: AppTheme.textGray,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            actions: [
              TextButton(
                onPressed: () {
                  Navigator.pop(context);
                  Navigator.pop(context);
                  Navigator.pop(context);
                },
                child: Text(
                  AppLocalizations.of(context).ok,
                  style: TextStyle(
                    color: AppTheme.textGray,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
              AnimatedButton(
                text: AppLocalizations.of(context).myExcursionsShort,
                icon: Icons.landscape,
                onPressed: () {
                  Navigator.pop(context);
                  Navigator.pop(context);
                  Navigator.pop(context);
                  HapticHelper.lightImpact();
                  context.navigateTo(const MyExcursionBookingsScreen());
                },
                height: 44,
                backgroundColor: AppTheme.accentGold,
                textColor: AppTheme.primaryDark,
                enableHaptic: false,
              ),
            ],
          ),
        );
      }
    } catch (e) {
      if (mounted) Navigator.pop(context);

      if (mounted) {
        final message = e.toString().replaceFirst('Exception: ', '');
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              '${AppLocalizations.of(context).errorPrefix}$message',
            ),
            backgroundColor: Colors.red,
            duration: const Duration(seconds: 4),
          ),
        );
      }
    }
  }
}
