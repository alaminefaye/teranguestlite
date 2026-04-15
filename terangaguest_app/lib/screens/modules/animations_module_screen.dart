import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../models/announcement.dart';
import '../../providers/announcements_provider.dart';
import '../../utils/haptic_helper.dart';
import '../../widgets/announcement_popup.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/error_state.dart';

class AnimationsModuleScreen extends StatefulWidget {
  const AnimationsModuleScreen({super.key});

  @override
  State<AnimationsModuleScreen> createState() => _AnimationsModuleScreenState();
}

class _AnimationsModuleScreenState extends State<AnimationsModuleScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!mounted) return;
      final provider = context.read<AnnouncementsProvider>();
      provider.loadAnnouncements();
    });
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
                        Navigator.of(context).pop();
                      },
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisSize: MainAxisSize.min,
                        children: const [
                          Text(
                            'Animations',
                            style: TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                              color: AppTheme.accentGold,
                            ),
                          ),
                          SizedBox(height: 4),
                          Text(
                            'Programme & événements',
                            style: TextStyle(
                              fontSize: 14,
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
                child: Consumer<AnnouncementsProvider>(
                  builder: (context, provider, _) {
                    if (provider.isLoading && !provider.hasAnnouncements) {
                      return const Center(
                        child: CircularProgressIndicator(
                          valueColor: AlwaysStoppedAnimation<Color>(
                            AppTheme.accentGold,
                          ),
                        ),
                      );
                    }

                    if (provider.error != null && !provider.hasAnnouncements) {
                      return ErrorStateWidget(
                        message: provider.error!,
                        hint: l10n.errorHint,
                        onRetry: provider.loadAnnouncements,
                      );
                    }

                    final items = provider.announcements;
                    if (items.isEmpty) {
                      return EmptyStateWidget(
                        icon: Icons.campaign_outlined,
                        title: 'Aucune animation',
                        subtitle:
                            'Les annonces et événements seront affichés ici.',
                      );
                    }

                    return ListView.separated(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 16,
                        vertical: 8,
                      ),
                      itemCount: items.length,
                      separatorBuilder: (_, _) => const SizedBox(height: 12),
                      itemBuilder: (context, index) {
                        final ann = items[index];
                        return _AnnouncementTile(
                          announcement: ann,
                          onTap: () {
                            HapticHelper.lightImpact();
                            AnnouncementPopup.show(context, ann);
                          },
                        );
                      },
                    );
                  },
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _AnnouncementTile extends StatelessWidget {
  final Announcement announcement;
  final VoidCallback onTap;

  const _AnnouncementTile({required this.announcement, required this.onTap});

  @override
  Widget build(BuildContext context) {
    final ann = announcement;
    return Material(
      color: AppTheme.primaryDark.withValues(alpha: 0.35),
      borderRadius: BorderRadius.circular(14),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(14),
        child: Padding(
          padding: const EdgeInsets.all(14),
          child: Row(
            children: [
              Container(
                width: 44,
                height: 44,
                decoration: BoxDecoration(
                  color: AppTheme.primaryBlue.withValues(alpha: 0.35),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(
                    color: AppTheme.accentGold.withValues(alpha: 0.25),
                  ),
                ),
                child: Icon(
                  ann.hasVideo ? Icons.play_circle_outline : Icons.campaign,
                  color: AppTheme.accentGold,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      ann.title?.trim().isNotEmpty == true
                          ? ann.title!
                          : 'Annonce',
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      ann.hasVideo ? 'Vidéo' : 'Affiche',
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(color: AppTheme.textGray),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 10),
              const Icon(Icons.chevron_right, color: AppTheme.textGray),
            ],
          ),
        ),
      ),
    );
  }
}
