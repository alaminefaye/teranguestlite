import 'package:flutter/material.dart';
import '../../config/theme.dart';
import '../../models/establishment.dart';
import '../../services/establishments_api.dart';
import '../../utils/haptic_helper.dart';

/// Fiche détail d'un établissement : présentation, adresse, galerie photos.
class EstablishmentDetailScreen extends StatefulWidget {
  const EstablishmentDetailScreen({
    super.key,
    required this.establishmentId,
    required this.name,
  });

  final int establishmentId;
  final String name;

  @override
  State<EstablishmentDetailScreen> createState() =>
      _EstablishmentDetailScreenState();
}

class _EstablishmentDetailScreenState extends State<EstablishmentDetailScreen> {
  final EstablishmentsApi _api = EstablishmentsApi();
  EstablishmentDetail? _detail;
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
      final detail =
          await _api.getEstablishmentDetail(widget.establishmentId);
      if (mounted) {
        setState(() {
          _detail = detail;
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
                            widget.name,
                            style: const TextStyle(
                              fontSize: 20,
                              fontWeight: FontWeight.bold,
                              color: AppTheme.accentGold,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              Expanded(
                child: _buildContent(),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildContent() {
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
                style: const TextStyle(
                  color: AppTheme.textGray,
                  fontSize: 14,
                ),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 16),
              TextButton(
                onPressed: _load,
                child: const Text(
                  'Réessayer',
                  style: TextStyle(color: AppTheme.accentGold),
                ),
              ),
            ],
          ),
        ),
      );
    }
    final d = _detail!;
    return SingleChildScrollView(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          if (d.coverPhoto != null && d.coverPhoto!.isNotEmpty)
            ClipRRect(
              borderRadius: BorderRadius.circular(12),
              child: Image.network(
                d.coverPhoto!,
                height: 200,
                fit: BoxFit.cover,
                loadingBuilder: (context, child, progress) =>
                    progress == null
                        ? child
                        : const SizedBox(
                            height: 200,
                            child: Center(
                              child: CircularProgressIndicator(
                                color: AppTheme.accentGold,
                              ),
                            ),
                          ),
                errorBuilder: (context, error, stackTrace) => const SizedBox.shrink(),
              ),
            ),
          if (d.coverPhoto != null && d.coverPhoto!.isNotEmpty)
            const SizedBox(height: 16),
          if (d.location != null && d.location!.isNotEmpty) ...[
            Text(
              d.location!,
              style: const TextStyle(
                fontSize: 15,
                color: AppTheme.textGray,
              ),
            ),
            const SizedBox(height: 12),
          ],
          if (d.description != null && d.description!.isNotEmpty) ...[
            const Text(
              'Présentation',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.w600,
                color: AppTheme.accentGold,
              ),
            ),
            const SizedBox(height: 6),
            Text(
              d.description!,
              style: const TextStyle(
                fontSize: 14,
                color: AppTheme.textGray,
                height: 1.4,
              ),
            ),
            const SizedBox(height: 16),
          ],
          if (d.address != null && d.address!.isNotEmpty) ...[
            const Text(
              'Adresse',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.w600,
                color: AppTheme.accentGold,
              ),
            ),
            const SizedBox(height: 6),
            Text(
              d.address!,
              style: const TextStyle(
                fontSize: 14,
                color: AppTheme.textGray,
              ),
            ),
            const SizedBox(height: 16),
          ],
          if (d.phone != null && d.phone!.isNotEmpty) ...[
            Text(
              'Tél. ${d.phone!}',
              style: const TextStyle(
                fontSize: 14,
                color: AppTheme.textGray,
              ),
            ),
            const SizedBox(height: 8),
          ],
          if (d.website != null && d.website!.isNotEmpty) ...[
            InkWell(
              onTap: () {
                // Option: url_launcher
              },
              child: Text(
                d.website!,
                style: const TextStyle(
                  fontSize: 14,
                  color: AppTheme.accentGold,
                  decoration: TextDecoration.underline,
                ),
              ),
            ),
            const SizedBox(height: 20),
          ],
          if (d.photos.isNotEmpty) ...[
            const Text(
              'Galerie',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.w600,
                color: AppTheme.accentGold,
              ),
            ),
            const SizedBox(height: 12),
            GridView.builder(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                crossAxisCount: 2,
                mainAxisSpacing: 12,
                crossAxisSpacing: 12,
                childAspectRatio: 0.85,
              ),
              itemCount: d.photos.length,
              itemBuilder: (context, index) {
                final photo = d.photos[index];
                final url = photo.url;
                if (url == null || url.isEmpty) return const SizedBox.shrink();
                return ClipRRect(
                  borderRadius: BorderRadius.circular(12),
                  child: Image.network(
                    url,
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
          ],
          const SizedBox(height: 24),
        ],
      ),
    );
  }
}
