import 'package:flutter/material.dart';
import '../../config/theme.dart';
import '../../models/guide.dart';
import '../../services/guides_api.dart';
import '../../utils/haptic_helper.dart';
import '../../utils/navigation_helper.dart';
import 'guide_items_screen.dart';

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

    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: _categories!.length,
      itemBuilder: (context, index) {
        final category = _categories![index];
        return Card(
          elevation: 2,
          margin: const EdgeInsets.only(bottom: 16),
          color: AppTheme.primaryBlue,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
          child: ListTile(
            contentPadding: const EdgeInsets.all(16),
            title: Text(
              category.name,
              style: const TextStyle(
                color: AppTheme.textWhite,
                fontSize: 18,
                fontWeight: FontWeight.bold,
              ),
            ),
            trailing: const Icon(
              Icons.chevron_right,
              color: AppTheme.accentGold,
            ),
            onTap: () {
              HapticHelper.lightImpact();
              context.navigateTo(GuideItemsScreen(category: category));
            },
          ),
        );
      },
    );
  }
}
