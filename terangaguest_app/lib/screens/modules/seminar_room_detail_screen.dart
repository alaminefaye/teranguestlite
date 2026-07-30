import 'package:cached_network_image/cached_network_image.dart';
import 'package:cached_network_image_platform_interface/cached_network_image_platform_interface.dart';
import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../config/theme.dart';
import '../../models/seminar_room.dart';
import '../../utils/haptic_helper.dart';

class SeminarRoomDetailScreen extends StatefulWidget {
  final SeminarRoom room;

  const SeminarRoomDetailScreen({super.key, required this.room});

  @override
  State<SeminarRoomDetailScreen> createState() =>
      _SeminarRoomDetailScreenState();
}

class _SeminarRoomDetailScreenState extends State<SeminarRoomDetailScreen> {
  late final PageController _pageController;
  int _currentImageIndex = 0;

  @override
  void initState() {
    super.initState();
    _pageController = PageController();
  }

  @override
  void dispose() {
    _pageController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final room = widget.room;
    final width = MediaQuery.sizeOf(context).width;
    final isMobile = width < 600;
    final horizontalPadding = isMobile ? 16.0 : 60.0;
    final phone = room.contactPhone?.trim() ?? '';
    final email = room.contactEmail?.trim() ?? '';
    final gallery = <String>[
      if (room.image != null && room.image!.trim().isNotEmpty)
        room.image!.trim(),
      ...room.galleryImages.where((image) {
        final trimmed = image.trim();
        return trimmed.isNotEmpty && trimmed != (room.image?.trim() ?? '');
      }),
    ];
    final hasImage = gallery.isNotEmpty;
    final heroHeight = isMobile ? 205.0 : 255.0;
    final thumbHeight = isMobile ? 68.0 : 82.0;

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
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ),
              ),
              Expanded(
                child: SingleChildScrollView(
                  padding: EdgeInsets.fromLTRB(
                    horizontalPadding,
                    6,
                    horizontalPadding,
                    24,
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      ClipRRect(
                        borderRadius: BorderRadius.circular(18),
                        child: hasImage
                            ? SizedBox(
                                height: heroHeight,
                                child: PageView.builder(
                                  controller: _pageController,
                                  itemCount: gallery.length,
                                  onPageChanged: (index) {
                                    setState(() => _currentImageIndex = index);
                                  },
                                  itemBuilder: (context, index) {
                                    return CachedNetworkImage(
                                      imageRenderMethodForWeb:
                                          ImageRenderMethodForWeb.HtmlImage,
                                      imageUrl: gallery[index],
                                      width: double.infinity,
                                      height: heroHeight,
                                      fit: BoxFit.cover,
                                      placeholder: (context, url) =>
                                          _buildPlaceholder(height: heroHeight),
                                      errorWidget: (context, url, error) =>
                                          _buildPlaceholder(height: heroHeight),
                                    );
                                  },
                                ),
                              )
                            : _buildPlaceholder(height: heroHeight),
                      ),
                      if (gallery.length > 1) ...[
                        const SizedBox(height: 10),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: List.generate(gallery.length, (index) {
                            final isActive = index == _currentImageIndex;
                            return AnimatedContainer(
                              duration: const Duration(milliseconds: 180),
                              margin: const EdgeInsets.symmetric(horizontal: 3),
                              width: isActive ? 16 : 7,
                              height: 7,
                              decoration: BoxDecoration(
                                color: isActive
                                    ? AppTheme.accentGold
                                    : Colors.white24,
                                borderRadius: BorderRadius.circular(999),
                              ),
                            );
                          }),
                        ),
                        const SizedBox(height: 10),
                        SizedBox(
                          height: thumbHeight,
                          child: ListView.separated(
                            scrollDirection: Axis.horizontal,
                            itemCount: gallery.length,
                            separatorBuilder: (_, _) =>
                                const SizedBox(width: 10),
                            itemBuilder: (context, index) => GestureDetector(
                              onTap: () {
                                _pageController.animateToPage(
                                  index,
                                  duration: const Duration(milliseconds: 220),
                                  curve: Curves.easeInOut,
                                );
                              },
                              child: Container(
                                width: isMobile ? 92 : 108,
                                decoration: BoxDecoration(
                                  borderRadius: BorderRadius.circular(12),
                                  border: Border.all(
                                    color: index == _currentImageIndex
                                        ? AppTheme.accentGold
                                        : Colors.white24,
                                    width: 1.3,
                                  ),
                                ),
                                child: ClipRRect(
                                  borderRadius: BorderRadius.circular(10),
                                  child: CachedNetworkImage(
                                    imageRenderMethodForWeb:
                                        ImageRenderMethodForWeb.HtmlImage,
                                    imageUrl: gallery[index],
                                    width: isMobile ? 92 : 108,
                                    height: thumbHeight,
                                    fit: BoxFit.cover,
                                    placeholder: (context, url) => Container(
                                      width: isMobile ? 92 : 108,
                                      height: thumbHeight,
                                      color: AppTheme.primaryBlue.withValues(
                                        alpha: 0.3,
                                      ),
                                    ),
                                    errorWidget: (context, url, error) =>
                                        Container(
                                          width: isMobile ? 92 : 108,
                                          height: thumbHeight,
                                          color: AppTheme.primaryBlue
                                              .withValues(alpha: 0.3),
                                        ),
                                  ),
                                ),
                              ),
                            ),
                          ),
                        ),
                      ],
                      const SizedBox(height: 16),
                      _InfoRow(
                        icon: Icons.people_outline,
                        label: 'Capacité',
                        value: room.capacity != null ? '${room.capacity}' : '—',
                      ),
                      const SizedBox(height: 10),
                      if ((room.description ?? '').trim().isNotEmpty) ...[
                        const Divider(color: AppTheme.textGray, height: 24),
                        const Text(
                          'Description',
                          style: TextStyle(
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
                      const Text(
                        'Équipements',
                        style: TextStyle(
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
                          (equipment) => Padding(
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
                                    equipment,
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
                        const Text(
                          'Contact',
                          style: TextStyle(
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

  Widget _buildPlaceholder({required double height}) {
    return Container(
      width: double.infinity,
      height: height,
      color: AppTheme.primaryBlue.withValues(alpha: 0.25),
      child: const Center(
        child: Icon(
          Icons.image_not_supported_outlined,
          color: AppTheme.textGray,
        ),
      ),
    );
  }
}

class _InfoRow extends StatelessWidget {
  final IconData icon;
  final String label;
  final String value;

  const _InfoRow({
    required this.icon,
    required this.label,
    required this.value,
  });

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

  const _ActionRow({
    required this.icon,
    required this.label,
    required this.onTap,
  });

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
