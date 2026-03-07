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
      appBar: AppBar(
        title: Text(
          category.name,
          style: const TextStyle(color: AppTheme.accentGold),
        ),
        backgroundColor: Colors.transparent,
        elevation: 0,
        iconTheme: const IconThemeData(color: AppTheme.accentGold),
      ),
      body: items.isEmpty
          ? const Center(
              child: Text(
                'Aucun élément',
                style: TextStyle(color: AppTheme.textGray),
              ),
            )
          : ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: items.length,
              itemBuilder: (context, index) {
                final item = items[index];
                return Card(
                  elevation: 2,
                  margin: const EdgeInsets.only(bottom: 16),
                  color: AppTheme.primaryBlue,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          item.title,
                          style: const TextStyle(
                            color: AppTheme.textWhite,
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        if (item.description != null) ...[
                          const SizedBox(height: 8),
                          Text(
                            item.description!,
                            style: const TextStyle(color: AppTheme.textGray),
                          ),
                        ],
                        if (item.address != null) ...[
                          const SizedBox(height: 8),
                          Row(
                            children: [
                              const Icon(
                                Icons.location_on,
                                size: 16,
                                color: AppTheme.textGray,
                              ),
                              const SizedBox(width: 8),
                              Expanded(
                                child: Text(
                                  item.address!,
                                  style: const TextStyle(
                                    color: AppTheme.textGray,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ],
                        const SizedBox(height: 16),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.end,
                          children: [
                            if (item.phone != null)
                              ElevatedButton.icon(
                                onPressed: () => _makePhoneCall(item.phone!),
                                icon: const Icon(Icons.phone),
                                label: const Text("Appeler"),
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: Colors.green,
                                  foregroundColor: Colors.white,
                                ),
                              ),
                            if (item.latitude != null &&
                                item.longitude != null) ...[
                              const SizedBox(width: 8),
                              ElevatedButton.icon(
                                onPressed: () =>
                                    _openMap(item.latitude!, item.longitude!),
                                icon: const Icon(Icons.map),
                                label: const Text("Y aller"),
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: AppTheme.accentGold,
                                  foregroundColor: Colors.black,
                                ),
                              ),
                            ],
                          ],
                        ),
                      ],
                    ),
                  ),
                );
              },
            ),
    );
  }
}
