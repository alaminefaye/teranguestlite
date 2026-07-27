import 'package:flutter/material.dart';
import '../../config/api_config.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../models/guide.dart';
import '../../services/guides_api.dart';
import '../../utils/haptic_helper.dart';
import '../../utils/navigation_helper.dart';
import 'guide_items_screen.dart';
import '../../widgets/service_card.dart';
import '../../utils/layout_helper.dart';

class GuidesScreen extends StatefulWidget {
  const GuidesScreen({super.key});

  @override
  State<GuidesScreen> createState() => _GuidesScreenState();
}

class _GuidesScreenState extends State<GuidesScreen> {
  final GuidesApi _api = GuidesApi();
  List<GuideCategory>? _categories;
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadGuides();
  }

  Future<void> _loadGuides() async {
    try {
      final data = await _api.getGuides();
      setState(() {
        _categories = data;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.primaryDark,
      appBar: AppBar(
        title: Text(
          AppLocalizations.of(context).guidesScreenTitle,
          style: const TextStyle(color: AppTheme.accentGold),
        ),
        backgroundColor: Colors.transparent,
        elevation: 0,
        iconTheme: const IconThemeData(color: AppTheme.accentGold),
      ),
      body: _buildBody(),
    );
  }

  String _getCategoryImage(GuideCategory category) {
    if (category.image != null && category.image!.trim().isNotEmpty) {
      return ApiConfig.storageUrl(category.image!);
    }
    final nameLower = category.name.toLowerCase();
    final typeLower = (category.categoryType ?? '').toLowerCase();

    if (typeLower == 'useful_numbers' || (nameLower.contains('num') && nameLower.contains('util'))) {
      return 'assets/images/info_urgence.png';
    }
    if (nameLower.contains('urgence')) {
      return 'assets/images/info_urgence.png';
    }
    if (nameLower.contains('santé') || nameLower.contains('sante') || nameLower.contains('hopit') || nameLower.contains('médic')) {
      return 'assets/images/info_sante.png';
    }
    if (nameLower.contains('transport') || nameLower.contains('vol') || nameLower.contains('taxi')) {
      return 'assets/images/info_transport.png';
    }
    if (nameLower.contains('pratique') || nameLower.contains('internet') || nameLower.contains('culture')) {
      return 'assets/images/info_pratique.png';
    }
    if (nameLower.contains('découvr') || nameLower.contains('decouvr') || nameLower.contains('sénégal') || nameLower.contains('senegal')) {
      return 'assets/images/info_decouvrir.png';
    }
    return 'assets/images/info_hotel.png';
  }

  Widget _buildBody() {
    if (_isLoading) {
      return const Center(
        child: CircularProgressIndicator(color: AppTheme.accentGold),
      );
    }

    if (_error != null) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.error_outline, size: 48, color: AppTheme.errorRed),
            const SizedBox(height: 16),
            Text(_error!, style: const TextStyle(color: AppTheme.textGray)),
            const SizedBox(height: 16),
            ElevatedButton(
              onPressed: () {
                setState(() {
                  _isLoading = true;
                  _error = null;
                });
                _loadGuides();
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.accentGold,
              ),
              child: Text(AppLocalizations.of(context).retry),
            ),
          ],
        ),
      );
    }

    if (_categories == null || _categories!.isEmpty) {
      return Center(
        child: Text(
          AppLocalizations.of(context).noGuideAvailable,
          style: const TextStyle(color: AppTheme.textGray),
        ),
      );
    }

    final crossAxisCount = LayoutHelper.gridCrossAxisCount(context);
    final aspectRatio = LayoutHelper.dashboardCellAspectRatio(context);
    final spacing = LayoutHelper.gridSpacing(context);

    return GridView.builder(
      padding: const EdgeInsets.all(20),
      gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
        crossAxisCount: crossAxisCount,
        crossAxisSpacing: spacing,
        mainAxisSpacing: spacing,
        childAspectRatio: aspectRatio,
      ),
      itemCount: _categories!.length,
      itemBuilder: (context, index) {
        final category = _categories![index];
        final imagePath = _getCategoryImage(category);

        return ServiceCard(
          title: category.name,
          icon: Icons.info_outline,
          imagePath: imagePath,
          onTap: () {
            HapticHelper.lightImpact();
            context.navigateTo(GuideItemsScreen(category: category));
          },
        );
      },
    );
  }
}
