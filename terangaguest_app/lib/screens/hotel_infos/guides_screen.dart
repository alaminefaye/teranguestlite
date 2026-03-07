import 'package:flutter/material.dart';
import '../../config/theme.dart';
import '../../config/api_config.dart';
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

  String _getFallbackImagePath(String categoryName) {
    if (categoryName.contains('urgence') || categoryName.contains('Urgences')) {
      return 'assets/images/assistance_securite.png';
    } else if (categoryName.contains('Santé') ||
        categoryName.contains('Hôpitaux')) {
      return 'assets/images/assistance_medecin.png';
    } else if (categoryName.contains('Transport') ||
        categoryName.contains('Déplacements')) {
      return 'assets/images/explor_transfert.png';
    } else if (categoryName.contains('Découvrir') ||
        categoryName.contains('Tourisme')) {
      return 'assets/images/explor_decouverte.png';
    }
    return 'assets/images/box_hotel_infos.png';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.primaryDark,
      appBar: AppBar(
        title: const Text(
          'Guides & Infos',
          style: TextStyle(color: AppTheme.accentGold),
        ),
        backgroundColor: Colors.transparent,
        elevation: 0,
        iconTheme: const IconThemeData(color: AppTheme.accentGold),
      ),
      body: _buildBody(),
    );
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
              child: const Text('Réessayer'),
            ),
          ],
        ),
      );
    }

    if (_categories == null || _categories!.isEmpty) {
      return const Center(
        child: Text(
          'Aucun guide disponible',
          style: TextStyle(color: AppTheme.textGray),
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

        final imagePath = category.image != null
            ? (category.image!.startsWith('http')
                  ? category.image!
                  : ApiConfig.storageUrl(category.image!))
            : _getFallbackImagePath(category.name);

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
