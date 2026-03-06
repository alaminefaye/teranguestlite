import 'package:flutter/material.dart';
import '../../config/theme.dart';
import '../../models/user.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../utils/haptic_helper.dart';

/// Affiche les photos d'un album de la galerie (depuis Hotel Infos).
class GalleryAlbumDetailScreen extends StatelessWidget {
  const GalleryAlbumDetailScreen({super.key, required this.album});

  final GalleryAlbum album;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [AppTheme.primaryDark, AppTheme.primaryBlue],
          ),
        ),
        child: SafeArea(
          child: Column(
            children: [
              Padding(
                padding: const EdgeInsets.all(12.0),
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
                    const SizedBox(width: 8),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            album.name,
                            style: const TextStyle(
                              fontSize: 20,
                              fontWeight: FontWeight.bold,
                              color: AppTheme.accentGold,
                            ),
                          ),
                          if (album.description != null &&
                              album.description!.isNotEmpty)
                            Text(
                              album.description!,
                              style: const TextStyle(
                                fontSize: 13,
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
              Expanded(
                child: album.photos.isEmpty
                    ? Center(
                        child: Text(
                          l10n.noPhoto,
                          style: const TextStyle(color: AppTheme.textGray),
                        ),
                      )
                    : GridView.builder(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 16,
                          vertical: 8,
                        ),
                        gridDelegate:
                            const SliverGridDelegateWithFixedCrossAxisCount(
                              crossAxisCount: 2,
                              mainAxisSpacing: 12,
                              crossAxisSpacing: 12,
                              childAspectRatio: 0.85,
                            ),
                        itemCount: album.photos.length,
                        itemBuilder: (context, index) {
                          final photo = album.photos[index];
                          return ClipRRect(
                            borderRadius: BorderRadius.circular(12),
                            child: Image.network(
                              photo.url,
                              fit: BoxFit.cover,
                              loadingBuilder: (context, child, progress) =>
                                  progress == null
                                  ? child
                                  : const Center(
                                      child: CircularProgressIndicator(
                                        color: AppTheme.accentGold,
                                      ),
                                    ),
                              errorBuilder: (context, error, stackTrace) =>
                                  const ColoredBox(
                                    color: Colors.white12,
                                    child: Center(
                                      child: Icon(
                                        Icons.broken_image_outlined,
                                        color: AppTheme.textGray,
                                        size: 40,
                                      ),
                                    ),
                                  ),
                            ),
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
