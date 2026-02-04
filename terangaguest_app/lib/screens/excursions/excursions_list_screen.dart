import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../providers/excursions_provider.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/error_state.dart';
import '../../widgets/excursion_card.dart';
import '../../utils/navigation_helper.dart';
import '../../utils/haptic_helper.dart';
import 'excursion_detail_screen.dart';

class ExcursionsListScreen extends StatefulWidget {
  const ExcursionsListScreen({super.key});

  @override
  State<ExcursionsListScreen> createState() => _ExcursionsListScreenState();
}

class _ExcursionsListScreenState extends State<ExcursionsListScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<ExcursionsProvider>().fetchExcursions();
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
              _buildHeader(),
              Expanded(child: _buildContent()),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildHeader() {
    return Padding(
      padding: const EdgeInsets.all(20.0),
      child: Row(
        children: [
          IconButton(
            icon: const Icon(Icons.arrow_back, color: AppTheme.accentGold),
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
                  AppLocalizations.of(context).excursionsTitle,
                  style: const TextStyle(
                      fontSize: 24,
                      fontWeight: FontWeight.bold,
                      color: Colors.white),
                ),
                const SizedBox(height: 4),
                Text(
                  AppLocalizations.of(context).discoverRegion,
                  style: const TextStyle(fontSize: 13, color: AppTheme.textGray),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildContent() {
    return Consumer<ExcursionsProvider>(
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
            onRetry: () => provider.refreshExcursions(),
          );
        }

        if (provider.excursions.isEmpty) {
          final l10n = AppLocalizations.of(context);
          return EmptyStateWidget(
            icon: Icons.landscape_outlined,
            title: l10n.noExcursionAvailable,
            subtitle: l10n.noExcursionAvailableHint,
          );
        }

        return RefreshIndicator(
          color: AppTheme.accentGold,
          onRefresh: provider.refreshExcursions,
          child: Center(
            child: Padding(
              padding:
                  const EdgeInsets.symmetric(horizontal: 60.0, vertical: 20.0),
              child: GridView.builder(
                shrinkWrap: true,
                physics: const AlwaysScrollableScrollPhysics(),
                gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                  crossAxisCount: 4,
                  childAspectRatio: 0.8,
                  crossAxisSpacing: 20,
                  mainAxisSpacing: 20,
                ),
                itemCount: provider.excursions.length,
                itemBuilder: (context, index) {
                  final excursion = provider.excursions[index];
                  return ExcursionCard(
                    excursion: excursion,
                    onTap: () {
                      HapticHelper.lightImpact();
                      context.navigateTo(ExcursionDetailScreen(
                        excursionId: excursion.id,
                      ));
                    },
                  );
                },
              ),
            ),
          ),
        );
      },
    );
  }
}
