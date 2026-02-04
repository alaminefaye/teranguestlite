import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../providers/laundry_provider.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/error_state.dart';
import '../../utils/navigation_helper.dart';
import '../../utils/haptic_helper.dart';
import '../../widgets/animated_button.dart';
import 'create_laundry_request_screen.dart';

class LaundryListScreen extends StatefulWidget {
  const LaundryListScreen({super.key});

  @override
  State<LaundryListScreen> createState() => _LaundryListScreenState();
}

class _LaundryListScreenState extends State<LaundryListScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<LaundryProvider>().fetchLaundryServices();
    });
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
                            AppLocalizations.of(context).laundry,
                            style: const TextStyle(
                                fontSize: 24,
                                fontWeight: FontWeight.bold,
                                color: Colors.white),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            AppLocalizations.of(context).laundrySubtitle,
                            style: const TextStyle(
                                fontSize: 13, color: AppTheme.textGray),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              Expanded(child: _buildContent()),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildContent() {
    return Consumer<LaundryProvider>(
      builder: (context, provider, child) {
        if (provider.isLoading) {
          return const Center(
              child: CircularProgressIndicator(
                  valueColor:
                      AlwaysStoppedAnimation<Color>(AppTheme.accentGold)));
        }

        if (provider.errorMessage != null) {
          return ErrorStateWidget(
            message: provider.errorMessage!,
            hint: AppLocalizations.of(context).errorHint,
            onRetry: () => provider.refreshLaundryServices(),
          );
        }

        if (provider.services.isEmpty) {
          final l10n = AppLocalizations.of(context);
          return EmptyStateWidget(
            icon: Icons.local_laundry_service_outlined,
            title: l10n.noLaundryService,
            subtitle: l10n.noLaundryServiceHint,
          );
        }

        return Column(
          children: [
            Expanded(
              child: Padding(
                padding: const EdgeInsets.symmetric(
                    horizontal: 60.0, vertical: 20.0),
                child: GridView.builder(
                  gridDelegate:
                      const SliverGridDelegateWithFixedCrossAxisCount(
                    crossAxisCount: 4,
                    childAspectRatio: 1.2,
                    crossAxisSpacing: 20,
                    mainAxisSpacing: 20,
                  ),
                  itemCount: provider.services.length,
                  itemBuilder: (context, index) {
                    final service = provider.services[index];
                    final quantity = provider.getQuantityForService(service.id);

                    return Transform(
                      transform: Matrix4.identity()
                        ..setEntry(3, 2, 0.001)
                        ..rotateX(-0.05)
                        ..rotateY(0.02),
                      alignment: Alignment.center,
                      child: Container(
                        decoration: BoxDecoration(
                          gradient: LinearGradient(
                            begin: Alignment.topLeft,
                            end: Alignment.bottomRight,
                            colors: [
                              AppTheme.primaryBlue,
                              AppTheme.primaryDark
                            ],
                          ),
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(
                              color: AppTheme.accentGold, width: 1.5),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withValues(alpha: 0.4),
                              blurRadius: 20,
                              spreadRadius: 2,
                              offset: const Offset(0, 10),
                            ),
                            BoxShadow(
                              color: AppTheme.accentGold.withValues(alpha: 0.1),
                              blurRadius: 15,
                              spreadRadius: -2,
                              offset: const Offset(0, -4),
                            ),
                          ],
                        ),
                        child: Padding(
                          padding: const EdgeInsets.all(16.0),
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Column(
                                children: [
                                  const Icon(Icons.local_laundry_service,
                                      size: 48, color: AppTheme.accentGold),
                                  const SizedBox(height: 12),
                                  Text(service.name,
                                      style: const TextStyle(
                                          fontSize: 16,
                                          fontWeight: FontWeight.bold,
                                          color: AppTheme.accentGold),
                                      textAlign: TextAlign.center,
                                      maxLines: 2,
                                      overflow: TextOverflow.ellipsis),
                                  const SizedBox(height: 8),
                                  Text(service.formattedPrice,
                                      style: const TextStyle(
                                          fontSize: 13,
                                          color: AppTheme.textGray)),
                                ],
                              ),
                              Row(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  IconButton(
                                    onPressed: quantity > 0
                                        ? () => provider.updateQuantity(
                                            service.id, quantity - 1)
                                        : null,
                                    icon: const Icon(
                                        Icons.remove_circle_outline),
                                    color: AppTheme.accentGold,
                                    iconSize: 28,
                                  ),
                                  Container(
                                    width: 50,
                                    padding: const EdgeInsets.symmetric(
                                        vertical: 8),
                                    decoration: BoxDecoration(
                                      color:
                                          AppTheme.accentGold.withValues(alpha: 0.2),
                                      borderRadius: BorderRadius.circular(8),
                                      border: Border.all(
                                          color: AppTheme.accentGold),
                                    ),
                                    child: Text('$quantity',
                                        textAlign: TextAlign.center,
                                        style: const TextStyle(
                                            fontSize: 18,
                                            fontWeight: FontWeight.bold,
                                            color: AppTheme.accentGold)),
                                  ),
                                  IconButton(
                                    onPressed: quantity < 99
                                        ? () => provider.updateQuantity(
                                            service.id, quantity + 1)
                                        : null,
                                    icon:
                                        const Icon(Icons.add_circle_outline),
                                    color: AppTheme.accentGold,
                                    iconSize: 28,
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                      ),
                    );
                  },
                ),
              ),
            ),
            if (provider.getTotalItems() > 0)
              Container(
                padding: const EdgeInsets.all(20),
                decoration: const BoxDecoration(
                  color: AppTheme.primaryDark,
                  border: Border(
                    top: BorderSide(color: AppTheme.accentGold, width: 2),
                  ),
                ),
                child: SafeArea(
                  top: false,
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(AppLocalizations.of(context).articleCount(provider.getTotalItems()),
                              style: const TextStyle(
                                  fontSize: 14, color: AppTheme.textGray)),
                          Text(
                              '${provider.getTotalPrice().toStringAsFixed(0)} FCFA',
                              style: const TextStyle(
                                  fontSize: 20,
                                  fontWeight: FontWeight.w900,
                                  color: AppTheme.accentGold)),
                        ],
                      ),
                      AnimatedButton(
                        text: 'Confirmer',
                        onPressed: () {
                          HapticHelper.confirm();
                          context.navigateTo(const CreateLaundryRequestScreen());
                        },
                        backgroundColor: AppTheme.accentGold,
                        textColor: AppTheme.primaryDark,
                        enableHaptic: false,
                      ),
                    ],
                  ),
                ),
              ),
          ],
        );
      },
    );
  }
}
