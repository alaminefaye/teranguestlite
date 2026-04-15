import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../config/theme.dart';
import '../../models/seminar_room.dart';
import '../../utils/haptic_helper.dart';

class SeminarRoomDetailScreen extends StatelessWidget {
  final SeminarRoom room;

  const SeminarRoomDetailScreen({super.key, required this.room});

  @override
  Widget build(BuildContext context) {
    final w = MediaQuery.sizeOf(context).width;
    final isMobile = w < 600;
    final pad = isMobile ? 16.0 : 60.0;
    final phone = room.contactPhone?.trim() ?? '';
    final email = room.contactEmail?.trim() ?? '';

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
                      child: Text(
                        room.name,
                        style: TextStyle(
                          fontSize: isMobile ? 18 : 28,
                          fontWeight: FontWeight.bold,
                          color: AppTheme.accentGold,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ),
              ),
              Expanded(
                child: SingleChildScrollView(
                  padding: EdgeInsets.symmetric(horizontal: pad, vertical: 10),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      if (room.image != null && room.image!.trim().isNotEmpty)
                        ClipRRect(
                          borderRadius: BorderRadius.circular(16),
                          child: CachedNetworkImage(
                            imageUrl: room.image!,
                            height: 240,
                            width: double.infinity,
                            fit: BoxFit.cover,
                            placeholder: (context, url) => Container(
                              height: 240,
                              color: AppTheme.primaryBlue.withValues(alpha: 0.25),
                            ),
                            errorWidget: (context, url, error) => Container(
                              height: 240,
                              color: AppTheme.primaryBlue.withValues(alpha: 0.25),
                              child: const Center(
                                child: Icon(
                                  Icons.image_not_supported_outlined,
                                  color: AppTheme.textGray,
                                ),
                              ),
                            ),
                          ),
                        ),
                      const SizedBox(height: 16),
                      _InfoRow(
                        icon: Icons.people_outline,
                        label: 'Capacité',
                        value: room.capacity != null ? '${room.capacity}' : '—',
                      ),
                      const SizedBox(height: 10),
                      if ((room.description ?? '').trim().isNotEmpty) ...[
                        const Divider(color: AppTheme.textGray, height: 24),
                        Text(
                          'Description',
                          style: const TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          room.description!,
                          style: const TextStyle(
                            color: Colors.white,
                            height: 1.5,
                          ),
                        ),
                      ],
                      const Divider(color: AppTheme.textGray, height: 24),
                      Text(
                        'Équipements',
                        style: const TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                      const SizedBox(height: 10),
                      if (room.equipments.isEmpty)
                        const Text(
                          'Aucun équipement.',
                          style: TextStyle(color: AppTheme.textGray),
                        )
                      else
                        ...room.equipments.map(
                          (e) => Padding(
                            padding: const EdgeInsets.only(bottom: 8),
                            child: Row(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Icon(
                                  Icons.check_circle_outline,
                                  size: 18,
                                  color: AppTheme.accentGold,
                                ),
                                const SizedBox(width: 10),
                                Expanded(
                                  child: Text(
                                    e,
                                    style: const TextStyle(
                                      color: Colors.white,
                                      height: 1.3,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      if (phone.isNotEmpty || email.isNotEmpty) ...[
                        const Divider(color: AppTheme.textGray, height: 24),
                        Text(
                          'Contact',
                          style: const TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                        const SizedBox(height: 10),
                        if (phone.isNotEmpty)
                          _ActionRow(
                            icon: Icons.call_outlined,
                            label: phone,
                            onTap: () async {
                              HapticHelper.lightImpact();
                              await launchUrl(
                                Uri.parse('tel:$phone'),
                                mode: LaunchMode.externalApplication,
                              );
                            },
                          ),
                        if (email.isNotEmpty)
                          _ActionRow(
                            icon: Icons.email_outlined,
                            label: email,
                            onTap: () async {
                              HapticHelper.lightImpact();
                              await launchUrl(
                                Uri.parse('mailto:$email'),
                                mode: LaunchMode.externalApplication,
                              );
                            },
                          ),
                      ],
                      SizedBox(height: MediaQuery.of(context).padding.bottom),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _InfoRow extends StatelessWidget {
  final IconData icon;
  final String label;
  final String value;

  const _InfoRow({required this.icon, required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Icon(icon, size: 20, color: AppTheme.accentGold),
        const SizedBox(width: 10),
        Text(
          '$label:',
          style: const TextStyle(
            color: AppTheme.textGray,
            fontWeight: FontWeight.w600,
          ),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: Text(
            value,
            style: const TextStyle(
              color: Colors.white,
              fontWeight: FontWeight.w700,
            ),
          ),
        ),
      ],
    );
  }
}

class _ActionRow extends StatelessWidget {
  final IconData icon;
  final String label;
  final VoidCallback onTap;

  const _ActionRow({required this.icon, required this.label, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: Material(
        color: AppTheme.primaryDark.withValues(alpha: 0.35),
        borderRadius: BorderRadius.circular(14),
        child: InkWell(
          borderRadius: BorderRadius.circular(14),
          onTap: onTap,
          child: Padding(
            padding: const EdgeInsets.all(12),
            child: Row(
              children: [
                Icon(icon, color: AppTheme.accentGold),
                const SizedBox(width: 12),
                Expanded(
                  child: Text(
                    label,
                    style: const TextStyle(color: Colors.white),
                  ),
                ),
                const Icon(Icons.chevron_right, color: AppTheme.textGray),
              ],
            ),
          ),
        ),
      ),
    );
  }
}

