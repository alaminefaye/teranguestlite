import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../config/api_config.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../models/announcement.dart';
import '../../models/user.dart';
import '../../providers/announcements_provider.dart';
import '../../services/api_service.dart';
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
  Enterprise? _enterprise;
  bool _loadingEnterprise = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (!mounted) return;
      _loadVitrineEnterprise();
      final provider = context.read<AnnouncementsProvider>();
      provider.loadAnnouncements();
    });
  }

  Future<void> _loadVitrineEnterprise() async {
    if (_loadingEnterprise) return;
    setState(() => _loadingEnterprise = true);
    try {
      final response = await ApiService().get(ApiConfig.vitrineEnterprise);
      final data = response.data;
      if (data is Map && data['success'] == true && data['data'] is Map) {
        final payload = Map<String, dynamic>.from(data['data'] as Map);
        if (!mounted) return;
        setState(() => _enterprise = Enterprise.fromJson(payload));
      }
    } catch (_) {
      // ignore
    } finally {
      if (mounted) setState(() => _loadingEnterprise = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final programUrl = _enterprise?.animationsProgramUrl?.trim() ?? '';
    final journalUrl = _enterprise?.animationsJournalUrl?.trim() ?? '';
    final hasDocs = programUrl.isNotEmpty || journalUrl.isNotEmpty;
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
                    if (provider.isLoading &&
                        !provider.hasAnnouncements &&
                        !hasDocs) {
                      return const Center(
                        child: CircularProgressIndicator(
                          valueColor: AlwaysStoppedAnimation<Color>(
                            AppTheme.accentGold,
                          ),
                        ),
                      );
                    }

                    if (provider.error != null && !provider.hasAnnouncements) {
                      if (!hasDocs) {
                        return ErrorStateWidget(
                          message: provider.error!,
                          hint: l10n.errorHint,
                          onRetry: provider.loadAnnouncements,
                        );
                      }
                    }

                    final items = provider.announcements;
                    return ListView(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 16,
                        vertical: 8,
                      ),
                      children: [
                        if (_loadingEnterprise && !hasDocs)
                          const Padding(
                            padding: EdgeInsets.only(bottom: 12),
                            child: LinearProgressIndicator(
                              color: AppTheme.accentGold,
                            ),
                          ),
                        if (hasDocs) ...[
                          _DocsSection(
                            programUrl: programUrl,
                            journalUrl: journalUrl,
                          ),
                          const SizedBox(height: 14),
                        ],
                        if (items.isEmpty)
                          const EmptyStateWidget(
                            icon: Icons.campaign_outlined,
                            title: 'Aucune animation',
                            subtitle:
                                'Les annonces et événements seront affichés ici.',
                          )
                        else
                          ...items.map(
                            (ann) => Padding(
                              padding: const EdgeInsets.only(bottom: 12),
                              child: _AnnouncementTile(
                                announcement: ann,
                                onTap: () {
                                  HapticHelper.lightImpact();
                                  AnnouncementPopup.show(context, ann);
                                },
                              ),
                            ),
                          ),
                      ],
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

class _DocsSection extends StatelessWidget {
  final String programUrl;
  final String journalUrl;

  const _DocsSection({required this.programUrl, required this.journalUrl});

  @override
  Widget build(BuildContext context) {
    final items = <_DocItem>[];
    if (programUrl.trim().isNotEmpty) {
      items.add(
        _DocItem(
          label: 'Programme',
          icon: Icons.event_note_outlined,
          url: programUrl,
        ),
      );
    }
    if (journalUrl.trim().isNotEmpty) {
      items.add(
        _DocItem(
          label: 'Journal',
          icon: Icons.article_outlined,
          url: journalUrl,
        ),
      );
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Documents',
          style: TextStyle(color: Colors.white, fontWeight: FontWeight.w800),
        ),
        const SizedBox(height: 10),
        ...items.map(
          (d) => Padding(
            padding: const EdgeInsets.only(bottom: 10),
            child: Material(
              color: AppTheme.primaryDark.withValues(alpha: 0.35),
              borderRadius: BorderRadius.circular(14),
              child: InkWell(
                borderRadius: BorderRadius.circular(14),
                onTap: () async {
                  HapticHelper.lightImpact();
                  await launchUrl(
                    Uri.parse(d.url),
                    mode: LaunchMode.externalApplication,
                  );
                },
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
                        child: Icon(d.icon, color: AppTheme.accentGold),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Text(
                          d.label,
                          style: const TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                      ),
                      const Icon(Icons.chevron_right, color: AppTheme.textGray),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ),
      ],
    );
  }
}

class _DocItem {
  final String label;
  final IconData icon;
  final String url;

  _DocItem({required this.label, required this.icon, required this.url});
}
