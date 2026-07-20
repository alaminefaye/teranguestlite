import 'package:flutter/material.dart';
import 'package:cached_network_image/cached_network_image.dart';
import 'package:cached_network_image_platform_interface/cached_network_image_platform_interface.dart';
import '../../config/theme.dart';
import '../../config/api_config.dart';
import '../../models/room.dart';
import '../../services/api_service.dart';
import '../../utils/haptic_helper.dart';
import '../../utils/layout_helper.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/error_state.dart';
import 'room_price_detail_screen.dart';

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
    final spacing = LayoutHelper.gridSpacing(context);
    final isMobile = MediaQuery.of(context).size.width < 600;

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
        GridView.builder(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          itemCount: roomType.rooms.length,
          gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: 2,
            crossAxisSpacing: spacing,
            mainAxisSpacing: spacing,
            childAspectRatio: isMobile ? 0.68 : 0.82,
          ),
          itemBuilder: (context, index) => _buildRoomCard(
            roomType.rooms[index],
            roomType.typeLabel.isNotEmpty
                ? roomType.typeLabel
                : roomType.typeName,
          ),
        ),
        const SizedBox(height: 32),
      ],
    );
  }

  Widget _buildRoomCard(Room room, String sectionTitle) {
    final hasImage = room.image != null && room.image!.isNotEmpty;
    final cardTitle = (room.description != null && room.description!.isNotEmpty)
        ? room.description!
        : room.typeName;
    final isMobile = MediaQuery.of(context).size.width < 600;
    final imageHeight = isMobile ? 84.0 : 118.0;
    final titleSize = isMobile ? 14.0 : 17.0;
    final priceSize = isMobile ? 12.0 : 15.0;
    final metaSize = isMobile ? 12.0 : 14.0;

    return Material(
      color: Colors.transparent,
      child: InkWell(
        borderRadius: BorderRadius.circular(16),
        onTap: () {
          HapticHelper.lightImpact();
          Navigator.of(context).push(
            MaterialPageRoute(
              builder: (_) =>
                  RoomPriceDetailScreen(room: room, sectionTitle: sectionTitle),
            ),
          );
        },
        child: Container(
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
                borderRadius: const BorderRadius.vertical(
                  top: Radius.circular(14),
                ),
                child: hasImage
                    ? CachedNetworkImage(
                        imageRenderMethodForWeb:
                            ImageRenderMethodForWeb.HtmlImage,
                        imageUrl: room.image!,
                        height: imageHeight,
                        width: double.infinity,
                        fit: BoxFit.cover,
                        placeholder: (context, url) => Container(
                          height: imageHeight,
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
                          height: imageHeight,
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
                        height: imageHeight,
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
                padding: EdgeInsets.all(isMobile ? 12 : 16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      cardTitle,
                      style: TextStyle(
                        fontSize: titleSize,
                        fontWeight: FontWeight.bold,
                        color: Colors.white,
                      ),
                      maxLines: 3,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 10),
                    if (room.capacity != null)
                      Row(
                        children: [
                          Icon(
                            Icons.people_outline,
                            size: isMobile ? 15 : 18,
                            color: AppTheme.textGray,
                          ),
                          const SizedBox(width: 8),
                          Text(
                            '${room.capacity} personne${room.capacity! > 1 ? 's' : ''}',
                            style: TextStyle(
                              fontSize: metaSize,
                              color: AppTheme.textGray,
                            ),
                          ),
                        ],
                      ),
                    const SizedBox(height: 10),
                    if (room.pricePerNight != null)
                      Container(
                        width: double.infinity,
                        padding: EdgeInsets.symmetric(
                          horizontal: isMobile ? 8 : 12,
                          vertical: isMobile ? 6 : 8,
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
                          style: TextStyle(
                            fontSize: priceSize,
                            fontWeight: FontWeight.bold,
                            color: AppTheme.accentGold,
                          ),
                          textAlign: TextAlign.center,
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
