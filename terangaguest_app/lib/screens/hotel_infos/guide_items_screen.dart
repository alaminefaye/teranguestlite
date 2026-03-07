import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../config/theme.dart';
import '../../models/guide.dart';

class GuideItemsScreen extends StatelessWidget {
  final GuideCategory category;

  const GuideItemsScreen({super.key, required this.category});

  Future<void> _makePhoneCall(String phoneNumber) async {
    final Uri launchUri = Uri(scheme: 'tel', path: phoneNumber);
    if (await canLaunchUrl(launchUri)) {
      await launchUrl(launchUri);
    }
  }

  Future<void> _openMap(double lat, double lng) async {
    final Uri launchUri = Uri.parse(
      'https://www.google.com/maps/search/?api=1&query=$lat,$lng',
    );
    if (await canLaunchUrl(launchUri)) {
      await launchUrl(launchUri, mode: LaunchMode.externalApplication);
    }
  }

  @override
  Widget build(BuildContext context) {
    final items = category.items ?? [];

    return Scaffold(
      backgroundColor: AppTheme.primaryDark,
      body: Container(
        decoration: const BoxDecoration(gradient: AppTheme.backgroundGradient),
        child: SafeArea(
          child: Column(
            children: [
              _buildAppBar(context),
              Expanded(
                child: items.isEmpty
                    ? const Center(
                        child: Text(
                          'Aucun élément',
                          style: TextStyle(color: AppTheme.textGray),
                        ),
                      )
                    : ListView.builder(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 20,
                          vertical: 12,
                        ),
                        itemCount: items.length,
                        itemBuilder: (context, index) {
                          final item = items[index];
                          return _buildItemCard(context, item);
                        },
                      ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildAppBar(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.all(20.0),
      child: Row(
        children: [
          IconButton(
            icon: const Icon(Icons.arrow_back, color: AppTheme.accentGold),
            onPressed: () => Navigator.of(context).pop(),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              category.name,
              style: TextStyle(
                fontSize: MediaQuery.of(context).size.width < 600 ? 18 : 28,
                fontWeight: FontWeight.bold,
                color: AppTheme.accentGold,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildItemCard(BuildContext context, GuideItem item) {
    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      decoration: BoxDecoration(
        color: AppTheme.primaryBlue.withValues(alpha: 0.5),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: AppTheme.accentGold.withValues(alpha: 0.3),
          width: 1,
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.2),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            if (item.image != null)
              Image.network(
                'https://teranguest.com/storage/${item.image}',
                height: 160,
                fit: BoxFit.cover,
                errorBuilder: (context, error, stackTrace) =>
                    const SizedBox.shrink(),
              ),
            Padding(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    item.title,
                    style: const TextStyle(
                      color: AppTheme.accentGold,
                      fontSize: 20,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  if (item.description != null) ...[
                    const SizedBox(height: 12),
                    Text(
                      item.description!,
                      style: const TextStyle(
                        color: AppTheme.textWhite,
                        height: 1.5,
                      ),
                    ),
                  ],
                  if (item.address != null) ...[
                    const SizedBox(height: 16),
                    Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Icon(
                          Icons.location_on,
                          size: 20,
                          color: AppTheme.textGray,
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Text(
                            item.address!,
                            style: const TextStyle(
                              color: AppTheme.textGray,
                              height: 1.4,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ],
                  const SizedBox(height: 20),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.end,
                    children: [
                      if (item.phone != null)
                        Expanded(
                          child: ElevatedButton.icon(
                            onPressed: () => _makePhoneCall(item.phone!),
                            icon: const Icon(Icons.phone, size: 20),
                            label: const Text("Appeler"),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: Colors.green.withValues(
                                alpha: 0.2,
                              ),
                              foregroundColor: Colors.green,
                              elevation: 0,
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(12),
                                side: const BorderSide(color: Colors.green),
                              ),
                              padding: const EdgeInsets.symmetric(vertical: 12),
                            ),
                          ),
                        ),
                      if (item.phone != null &&
                          item.latitude != null &&
                          item.longitude != null)
                        const SizedBox(width: 12),
                      if (item.latitude != null && item.longitude != null)
                        Expanded(
                          child: ElevatedButton.icon(
                            onPressed: () =>
                                _openMap(item.latitude!, item.longitude!),
                            icon: const Icon(Icons.map, size: 20),
                            label: const Text("Y aller"),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: AppTheme.accentGold.withValues(
                                alpha: 0.1,
                              ),
                              foregroundColor: AppTheme.accentGold,
                              elevation: 0,
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(12),
                                side: const BorderSide(
                                  color: AppTheme.accentGold,
                                ),
                              ),
                              padding: const EdgeInsets.symmetric(vertical: 12),
                            ),
                          ),
                        ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
