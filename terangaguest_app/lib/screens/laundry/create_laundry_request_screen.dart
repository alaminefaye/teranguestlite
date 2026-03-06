import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../providers/auth_provider.dart';
import '../../providers/laundry_provider.dart';
import '../../providers/tablet_session_provider.dart';
import '../../widgets/animated_button.dart';

class CreateLaundryRequestScreen extends StatefulWidget {
  const CreateLaundryRequestScreen({super.key});

  @override
  State<CreateLaundryRequestScreen> createState() =>
      _CreateLaundryRequestScreenState();
}

class _CreateLaundryRequestScreenState
    extends State<CreateLaundryRequestScreen> {
  final TextEditingController _instructionsController = TextEditingController();
  final TextEditingController _clientCodeController = TextEditingController();

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) async {
      context.read<AuthProvider>().loadUser();
      if (!mounted) return;
      final tabletSession = context.read<TabletSessionProvider>();
      final auth = context.read<AuthProvider>();
      await tabletSession.load();
      if (!mounted) return;
      final authRoom = auth.user?.roomNumber?.trim() ?? '';
      if (authRoom.isNotEmpty) await tabletSession.setRoomNumber(authRoom);
      await tabletSession.tryRestoreSessionFromRoom();
      if (!mounted) return;
      final code = tabletSession.clientCodeForPreFill;
      if (code != null &&
          code.isNotEmpty &&
          _clientCodeController.text.isEmpty) {
        _clientCodeController.text = code;
        setState(() {});
      }
    });
  }

  @override
  void dispose() {
    _instructionsController.dispose();
    _clientCodeController.dispose();
    super.dispose();
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
                      onPressed: () => Navigator.pop(context),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            AppLocalizations.of(context).confirmRequest,
                            style: TextStyle(
                              fontSize: MediaQuery.of(context).size.width < 600
                                  ? 16
                                  : 24,
                              fontWeight: FontWeight.bold,
                              color: AppTheme.accentGold,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            AppLocalizations.of(context).laundry,
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
              Expanded(
                child: SingleChildScrollView(
                  padding: EdgeInsets.symmetric(
                    horizontal: MediaQuery.of(context).size.width < 600
                        ? 16
                        : 60,
                    vertical: 20,
                  ),
                  child: Column(
                    children: [
                      _buildCanReserveBanner(),
                      _buildItemsSummary(),
                      const SizedBox(height: 24),
                      _buildSpecialInstructions(),
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

  Widget _buildItemsSummary() {
    return Consumer<LaundryProvider>(
      builder: (context, provider, child) {
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
                AppLocalizations.of(context).selectedItems,
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: AppTheme.accentGold,
                ),
              ),
              const SizedBox(height: 16),
              ...provider.selectedItems.entries.map((entry) {
                final service = provider.services.firstWhere(
                  (s) => s.id == entry.key,
                );
                final quantity = entry.value;
                final subtotal = service.pricePerItem * quantity;

                return Padding(
                  padding: const EdgeInsets.only(bottom: 12.0),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Expanded(
                        child: Text(
                          '${service.name} × $quantity',
                          style: const TextStyle(
                            fontSize: 14,
                            color: Colors.white,
                          ),
                        ),
                      ),
                      Text(
                        '${subtotal.toStringAsFixed(0)} FCFA',
                        style: const TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.bold,
                          color: AppTheme.accentGold,
                        ),
                      ),
                    ],
                  ),
                );
              }),
              const Divider(height: 24, color: AppTheme.textGray),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    AppLocalizations.of(context).total,
                    style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: Colors.white,
                    ),
                  ),
                  Text(
                    '${provider.getTotalPrice().toStringAsFixed(0)} FCFA',
                    style: const TextStyle(
                      fontSize: 22,
                      fontWeight: FontWeight.w900,
                      color: AppTheme.accentGold,
                    ),
                  ),
                ],
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildSpecialInstructions() {
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
            AppLocalizations.of(context).specialInstructionsOptional,
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.bold,
              color: AppTheme.accentGold,
            ),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _instructionsController,
            style: const TextStyle(color: Colors.white),
            maxLines: 3,
            decoration: InputDecoration(
              hintText: AppLocalizations.of(context).laundryInstructionsExample,
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

  Widget _buildCanReserveBanner() {
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
                  AppLocalizations.of(context).reservationClientCodeBanner,
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
              hintText: AppLocalizations.of(context).clientCodeHint,
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
    return Consumer<LaundryProvider>(
      builder: (context, provider, child) {
        final hasCode = _clientCodeController.text.trim().isNotEmpty;
        final canSubmit = hasCode;
        return AnimatedButton(
          text: AppLocalizations.of(context).confirmRequest,
          onPressed: canSubmit ? _handleConfirmRequest : null,
          width: double.infinity,
          height: 56,
          backgroundColor: AppTheme.accentGold,
          textColor: AppTheme.primaryDark,
        );
      },
    );
  }

  Future<void> _handleConfirmRequest() async {
    final auth = context.read<AuthProvider>();
    final clientCode = _clientCodeController.text.trim();
    final relyingOnCanReserve =
        clientCode.isEmpty && (auth.user?.canReserve == true);

    if (relyingOnCanReserve) {
      await auth.loadUser();
      if (!mounted) return;
      if (auth.user?.canReserve != true) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(
                AppLocalizations.of(
                  context,
                ).sessionExpiredNeedClientCodeRequest,
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
        builder: (dialogContext) => const Center(
          child: CircularProgressIndicator(
            valueColor: AlwaysStoppedAnimation<Color>(AppTheme.accentGold),
          ),
        ),
      );

      await context.read<LaundryProvider>().createLaundryRequest(
        specialInstructions: _instructionsController.text.isEmpty
            ? null
            : _instructionsController.text,
        clientCode: clientCode.isNotEmpty ? clientCode : null,
      );

      if (!mounted) return;
      Navigator.pop(context);

      if (mounted) {
        showDialog(
          context: context,
          builder: (dialogContext) => AlertDialog(
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
                  AppLocalizations.of(context).requestSent,
                  style: const TextStyle(color: Colors.white, fontSize: 18),
                ),
              ],
            ),
            content: Text(
              AppLocalizations.of(context).laundryRequestSentMessage,
              style: const TextStyle(color: AppTheme.textGray),
            ),
            actions: [
              TextButton(
                onPressed: () {
                  Navigator.pop(dialogContext);
                  Navigator.pop(context);
                  Navigator.pop(context);
                },
                child: Text(
                  AppLocalizations.of(context).ok,
                  style: TextStyle(
                    color: AppTheme.accentGold,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
            ],
          ),
        );
      }
    } catch (e) {
      if (!mounted) return;
      Navigator.pop(context);

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
