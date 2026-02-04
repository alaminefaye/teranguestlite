import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../providers/laundry_provider.dart';
import '../../widgets/animated_button.dart';

class CreateLaundryRequestScreen extends StatefulWidget {
  const CreateLaundryRequestScreen({super.key});

  @override
  State<CreateLaundryRequestScreen> createState() =>
      _CreateLaundryRequestScreenState();
}

class _CreateLaundryRequestScreenState
    extends State<CreateLaundryRequestScreen> {
  final TextEditingController _instructionsController =
      TextEditingController();

  @override
  void dispose() {
    _instructionsController.dispose();
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
                      icon: const Icon(Icons.arrow_back,
                          color: AppTheme.accentGold),
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
                            style: const TextStyle(
                                fontSize: 24,
                                fontWeight: FontWeight.bold,
                                color: Colors.white),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            AppLocalizations.of(context).laundry,
                            style: const TextStyle(
                                fontSize: 13, color: AppTheme.textGray),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              Expanded(
                child: SingleChildScrollView(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 60, vertical: 20),
                  child: Column(
                    children: [
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
                colors: [AppTheme.primaryBlue, AppTheme.primaryDark]),
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: AppTheme.accentGold, width: 1.5),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(AppLocalizations.of(context).selectedItems,
                  style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: AppTheme.accentGold)),
              const SizedBox(height: 16),
              ...provider.selectedItems.entries.map((entry) {
                final service = provider.services
                    .firstWhere((s) => s.id == entry.key);
                final quantity = entry.value;
                final subtotal = service.pricePerItem * quantity;

                return Padding(
                  padding: const EdgeInsets.only(bottom: 12.0),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Expanded(
                        child: Text('${service.name} × $quantity',
                            style: const TextStyle(
                                fontSize: 14, color: Colors.white)),
                      ),
                      Text('${subtotal.toStringAsFixed(0)} FCFA',
                          style: const TextStyle(
                              fontSize: 14,
                              fontWeight: FontWeight.bold,
                              color: AppTheme.accentGold)),
                    ],
                  ),
                );
              }),
              const Divider(height: 24, color: AppTheme.textGray),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(AppLocalizations.of(context).total,
                      style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                          color: Colors.white)),
                  Text('${provider.getTotalPrice().toStringAsFixed(0)} FCFA',
                      style: const TextStyle(
                          fontSize: 22,
                          fontWeight: FontWeight.w900,
                          color: AppTheme.accentGold)),
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
        gradient:
            LinearGradient(colors: [AppTheme.primaryBlue, AppTheme.primaryDark]),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppTheme.accentGold, width: 1.5),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(AppLocalizations.of(context).specialInstructionsOptional,
              style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                  color: AppTheme.accentGold)),
          const SizedBox(height: 12),
          TextField(
            controller: _instructionsController,
            style: const TextStyle(color: Colors.white),
            maxLines: 3,
            decoration: InputDecoration(
              hintText: AppLocalizations.of(context).laundryInstructionsExample,
              hintStyle: TextStyle(color: AppTheme.textGray.withValues(alpha: 0.6)),
              filled: true,
              fillColor: AppTheme.primaryBlue.withValues(alpha: 0.5),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide:
                    BorderSide(color: AppTheme.accentGold.withValues(alpha: 0.3)),
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide:
                    BorderSide(color: AppTheme.accentGold.withValues(alpha: 0.3)),
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

  Widget _buildConfirmButton() {
    return Consumer<LaundryProvider>(
      builder: (context, provider, child) {
        return AnimatedButton(
          text: AppLocalizations.of(context).confirmRequest,
          onPressed: _handleConfirmRequest,
          width: double.infinity,
          height: 56,
          backgroundColor: AppTheme.accentGold,
          textColor: AppTheme.primaryDark,
        );
      },
    );
  }

  Future<void> _handleConfirmRequest() async {
    try {
      showDialog(
        context: context,
        barrierDismissible: false,
        builder: (context) => const Center(
          child: CircularProgressIndicator(
              valueColor: AlwaysStoppedAnimation<Color>(AppTheme.accentGold)),
        ),
      );

      await context.read<LaundryProvider>().createLaundryRequest(
          specialInstructions: _instructionsController.text.isEmpty
              ? null
              : _instructionsController.text);

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
                Text(AppLocalizations.of(context).requestSent,
                    style: const TextStyle(color: Colors.white, fontSize: 18)),
              ],
            ),
            content: Text(
              AppLocalizations.of(context).laundryRequestSentMessage,
              style: const TextStyle(color: AppTheme.textGray),
            ),
            actions: [
              TextButton(
                onPressed: () {
                  Navigator.pop(context);
                  Navigator.pop(context);
                  Navigator.pop(context);
                },
                child: Text(AppLocalizations.of(context).ok,
                    style: TextStyle(
                        color: AppTheme.accentGold,
                        fontWeight: FontWeight.bold)),
              ),
            ],
          ),
        );
      }
    } catch (e) {
      if (mounted) Navigator.pop(context);

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('${AppLocalizations.of(context).errorPrefix}$e'),
            backgroundColor: Colors.red,
            duration: const Duration(seconds: 3),
          ),
        );
      }
    }
  }
}
