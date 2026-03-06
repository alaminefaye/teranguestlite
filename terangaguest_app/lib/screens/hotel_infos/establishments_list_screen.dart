import 'package:flutter/material.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../models/establishment.dart';
import '../../services/establishments_api.dart';
import '../../utils/haptic_helper.dart';
import '../../utils/layout_helper.dart';
import 'establishment_detail_screen.dart';

class EstablishmentsListScreen extends StatefulWidget {
  const EstablishmentsListScreen({super.key});

  @override
  State<EstablishmentsListScreen> createState() =>
      _EstablishmentsListScreenState();
}

class _EstablishmentsListScreenState extends State<EstablishmentsListScreen> {
  final EstablishmentsApi _api = EstablishmentsApi();
  List<Establishment>? _list;
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final list = await _api.getEstablishments();
      if (mounted) {
        setState(() {
          _list = list;
          _loading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _error = e.toString().replaceFirst('Exception: ', '');
          _loading = false;
        });
      }
    }
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
                            l10n.ourEstablishments,
                            style: TextStyle(
                              fontSize: MediaQuery.of(context).size.width < 600
                                  ? 18
                                  : 28,
                              fontWeight: FontWeight.bold,
                              color: AppTheme.accentGold,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            l10n.ourEstablishmentsDesc,
                            style: const TextStyle(
                              fontSize: 14,
                              color: AppTheme.textGray,
                            ),
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              Expanded(child: _buildContent(context, l10n)),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildContent(BuildContext context, AppLocalizations l10n) {
    if (_loading) {
      return const Center(
        child: CircularProgressIndicator(color: AppTheme.accentGold),
      );
    }
    if (_error != null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(
                _error!,
                style: const TextStyle(color: AppTheme.textGray, fontSize: 14),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 16),
              TextButton(
                onPressed: _load,
                child: Text(
                  l10n.retry,
                  style: const TextStyle(color: AppTheme.accentGold),
                ),
              ),
            ],
          ),
        ),
      );
    }
    final list = _list ?? [];
    if (list.isEmpty) {
      return Center(
        child: Text(
          l10n.comingSoon,
          style: const TextStyle(color: AppTheme.textGray, fontSize: 16),
        ),
      );
    }
    final padding = LayoutHelper.horizontalPaddingValue(context);
    return ListView.builder(
      padding: EdgeInsets.symmetric(horizontal: padding, vertical: 12),
      itemCount: list.length,
      itemBuilder: (context, index) {
        final e = list[index];
        return Padding(
          padding: const EdgeInsets.only(bottom: 12),
          child: Material(
            color: Colors.transparent,
            child: InkWell(
              onTap: () {
                HapticHelper.lightImpact();
                Navigator.of(context).push(
                  MaterialPageRoute<void>(
                    builder: (ctx) => EstablishmentDetailScreen(
                      establishmentId: e.id,
                      name: e.name,
                    ),
                  ),
                );
              },
              borderRadius: BorderRadius.circular(12),
              child: Container(
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(
                    color: AppTheme.accentGold.withValues(alpha: 0.5),
                    width: 1.5,
                  ),
                ),
                child: ClipRRect(
                  borderRadius: BorderRadius.circular(12),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      if (e.coverPhoto != null && e.coverPhoto!.isNotEmpty)
                        Image.network(
                          e.coverPhoto!,
                          height: 160,
                          fit: BoxFit.cover,
                          loadingBuilder: (context, child, progress) =>
                              progress == null
                              ? child
                              : SizedBox(
                                  height: 160,
                                  child: Center(
                                    child: CircularProgressIndicator(
                                      color: AppTheme.accentGold,
                                    ),
                                  ),
                                ),
                          errorBuilder: (context, error, stackTrace) =>
                              SizedBox(
                                height: 160,
                                child: Center(
                                  child: Icon(
                                    Icons.business_outlined,
                                    color: AppTheme.accentGold.withValues(
                                      alpha: 0.6,
                                    ),
                                    size: 48,
                                  ),
                                ),
                              ),
                        )
                      else
                        Container(
                          height: 160,
                          color: AppTheme.primaryBlue.withValues(alpha: 0.5),
                          child: Center(
                            child: Icon(
                              Icons.business_outlined,
                              color: AppTheme.accentGold.withValues(alpha: 0.6),
                              size: 48,
                            ),
                          ),
                        ),
                      Padding(
                        padding: const EdgeInsets.all(14),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              e.name,
                              style: const TextStyle(
                                fontSize: 17,
                                fontWeight: FontWeight.w600,
                                color: AppTheme.accentGold,
                              ),
                            ),
                            if (e.location != null &&
                                e.location!.isNotEmpty) ...[
                              const SizedBox(height: 4),
                              Text(
                                e.location!,
                                style: const TextStyle(
                                  fontSize: 14,
                                  color: AppTheme.textGray,
                                ),
                              ),
                            ],
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
        );
      },
    );
  }
}
