import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:cached_network_image_platform_interface/cached_network_image_platform_interface.dart';
import '../../config/theme.dart';
import '../../config/api_config.dart';
import '../../models/room.dart';
import '../../services/api_service.dart';
import '../../utils/haptic_helper.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/error_state.dart';

class RoomPricesScreen extends StatefulWidget {
  const RoomPricesScreen({super.key});

  @override
  State<RoomPricesScreen> createState() => _RoomPricesScreenState();
}

class _RoomPricesScreenState extends State<RoomPricesScreen> {
  List<RoomType>? _roomTypes;
  bool _isLoading = true;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    _loadRooms();
  }

  Future<void> _loadRooms() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final response = await ApiService().get(ApiConfig.vitrineRooms);
      final data = response.data;

      if (data is Map && data['success'] == true && data['data'] is List) {
        final roomTypesJson = data['data'] as List;
        final roomTypes = roomTypesJson
            .map((e) => RoomType.fromJson(e as Map<String, dynamic>))
            .toList();

        if (!mounted) return;
        setState(() {
          _roomTypes = roomTypes;
          _isLoading = false;
        });
      } else {
        if (!mounted) return;
        setState(() {
          _isLoading = false;
        });
      }
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _errorMessage = e.toString();
        _isLoading = false;
      });
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
              _buildHeader(),
              Expanded(child: _buildContent()),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildHeader() {
    final w = MediaQuery.sizeOf(context).width;
    final isMobile = w < 600;
    final titleSize = isMobile ? 20.0 : 24.0;
    final pad = isMobile ? 12.0 : 20.0;

    return Padding(
      padding: EdgeInsets.all(pad),
      child: Row(
        children: [
          IconButton(
            icon: const Icon(Icons.arrow_back, color: AppTheme.accentGold),
            onPressed: () {
              HapticHelper.lightImpact();
              Navigator.pop(context);
            },
          ),
          SizedBox(width: isMobile ? 8 : 12),
          Expanded(
            child: Text(
              AppLocalizations.of(context).roomPrices,
              style: TextStyle(
                fontSize: titleSize,
                fontWeight: FontWeight.bold,
                color: AppTheme.accentGold,
              ),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildContent() {
    if (_isLoading) {
      return const Center(
        child: CircularProgressIndicator(
          valueColor: AlwaysStoppedAnimation<Color>(AppTheme.accentGold),
        ),
      );
    }

    if (_errorMessage != null) {
      return ErrorStateWidget(
        message: _errorMessage!,
        hint: AppLocalizations.of(context).errorHint,
        onRetry: _loadRooms,
      );
    }

    if (_roomTypes == null || _roomTypes!.isEmpty) {
      final l10n = AppLocalizations.of(context);
      return EmptyStateWidget(
        icon: Icons.hotel_outlined,
        title: l10n.noRoomsAvailable,
        subtitle: l10n.checkBackLater,
      );
    }

    return SingleChildScrollView(
      padding: EdgeInsets.symmetric(
        horizontal: MediaQuery.of(context).size.width < 600 ? 16 : 60,
        vertical: 20,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: _roomTypes!.map((roomType) {
          return _buildRoomTypeSection(roomType);
        }).toList(),
      ),
    );
  }

  Widget _buildRoomTypeSection(RoomType roomType) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          roomType.typeLabel.isNotEmpty
              ? roomType.typeLabel
              : roomType.typeName,
          style: const TextStyle(
            fontSize: 22,
            fontWeight: FontWeight.bold,
            color: AppTheme.accentGold,
          ),
        ),
        const SizedBox(height: 16),
        ...roomType.rooms.map((room) => _buildRoomCard(room)),
        const SizedBox(height: 32),
      ],
    );
  }

  Widget _buildRoomCard(Room room) {
    final hasImage = room.image != null && room.image!.isNotEmpty;
    final cardTitle = (room.description != null && room.description!.isNotEmpty)
        ? room.description!
        : room.typeName;

    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [AppTheme.primaryBlue, AppTheme.primaryDark],
        ),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppTheme.accentGold, width: 1.5),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Image section
          ClipRRect(
            borderRadius: const BorderRadius.vertical(top: Radius.circular(14)),
            child: hasImage
                ? CachedNetworkImage(
                    imageRenderMethodForWeb: ImageRenderMethodForWeb.HtmlImage,
                    imageUrl: room.image!,
                    height: 200,
                    width: double.infinity,
                    fit: BoxFit.cover,
                    placeholder: (context, url) => Container(
                      height: 200,
                      color: AppTheme.primaryBlue.withValues(alpha: 0.3),
                      child: const Center(
                        child: Icon(
                          Icons.hotel,
                          size: 60,
                          color: AppTheme.accentGold,
                        ),
                      ),
                    ),
                    errorWidget: (context, url, error) => Container(
                      height: 200,
                      color: AppTheme.primaryBlue.withValues(alpha: 0.3),
                      child: const Center(
                        child: Icon(
                          Icons.hotel,
                          size: 60,
                          color: AppTheme.accentGold,
                        ),
                      ),
                    ),
                  )
                : Container(
                    height: 200,
                    color: AppTheme.primaryBlue.withValues(alpha: 0.3),
                    child: const Center(
                      child: Icon(
                        Icons.hotel,
                        size: 60,
                        color: AppTheme.accentGold,
                      ),
                    ),
                  ),
          ),
          // Info section
          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Expanded(
                      child: Text(
                        cardTitle,
                        style: const TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                          color: Colors.white,
                        ),
                      ),
                    ),
                    if (room.pricePerNight != null)
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 12,
                          vertical: 6,
                        ),
                        decoration: BoxDecoration(
                          color: AppTheme.accentGold.withValues(alpha: 0.2),
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(color: AppTheme.accentGold),
                        ),
                        child: Text(
                          room.formattedPricePerNight != null
                              ? '${room.formattedPricePerNight} / nuit'
                              : '${room.pricePerNight!.toStringAsFixed(0)} FCFA / nuit',
                          style: const TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                            color: AppTheme.accentGold,
                          ),
                        ),
                      ),
                  ],
                ),
                const SizedBox(height: 12),
                if (room.capacity != null)
                  Row(
                    children: [
                      const Icon(
                        Icons.people_outline,
                        size: 18,
                        color: AppTheme.textGray,
                      ),
                      const SizedBox(width: 8),
                      Text(
                        '${room.capacity} personne${room.capacity! > 1 ? 's' : ''}',
                        style: const TextStyle(
                          fontSize: 14,
                          color: AppTheme.textGray,
                        ),
                      ),
                    ],
                  ),
                if (room.floor != null && room.floor!.isNotEmpty) ...[
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      const Icon(
                        Icons.layers_outlined,
                        size: 18,
                        color: AppTheme.textGray,
                      ),
                      const SizedBox(width: 8),
                      Text(
                        'Étage ${room.floor}',
                        style: const TextStyle(
                          fontSize: 14,
                          color: AppTheme.textGray,
                        ),
                      ),
                    ],
                  ),
                ],
                if (room.description != null &&
                    room.description!.isNotEmpty &&
                    room.description != cardTitle) ...[
                  const SizedBox(height: 16),
                  const Divider(color: AppTheme.textGray, height: 1),
                  const SizedBox(height: 16),
                  Text(
                    room.description!,
                    style: const TextStyle(
                      fontSize: 14,
                      color: Colors.white,
                      height: 1.5,
                    ),
                  ),
                ],
                if (room.amenities != null && room.amenities!.isNotEmpty) ...[
                  const SizedBox(height: 16),
                  const Divider(color: AppTheme.textGray, height: 1),
                  const SizedBox(height: 16),
                  Wrap(
                    spacing: 8,
                    runSpacing: 8,
                    children: room.amenities!.map((amenity) {
                      return Chip(
                        label: Text(
                          amenity,
                          style: const TextStyle(
                            fontSize: 12,
                            color: Colors.white,
                          ),
                        ),
                        backgroundColor: AppTheme.accentGold.withValues(
                          alpha: 0.1,
                        ),
                        side: const BorderSide(color: AppTheme.accentGold),
                      );
                    }).toList(),
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }
}
