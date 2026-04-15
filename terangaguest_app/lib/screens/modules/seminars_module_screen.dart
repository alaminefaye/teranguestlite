import 'package:flutter/material.dart';
import '../../config/theme.dart';
import '../../utils/haptic_helper.dart';
import '../../services/seminars_api.dart';
import '../../models/seminar_room.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/error_state.dart';
import '../../utils/navigation_helper.dart';
import 'seminar_room_detail_screen.dart';

class SeminarsModuleScreen extends StatefulWidget {
  const SeminarsModuleScreen({super.key});

  @override
  State<SeminarsModuleScreen> createState() => _SeminarsModuleScreenState();
}

class _SeminarsModuleScreenState extends State<SeminarsModuleScreen> {
  late Future<List<SeminarRoom>> _future;

  @override
  void initState() {
    super.initState();
    _future = SeminarsApi().getSeminarRooms();
  }

  @override
  Widget build(BuildContext context) {
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
                    const Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            'Séminaires',
                            style: TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                              color: AppTheme.accentGold,
                            ),
                          ),
                          SizedBox(height: 4),
                          Text(
                            'Salles, capacités & équipements',
                            style: TextStyle(
                              fontSize: 14,
                              color: AppTheme.textGray,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              Expanded(
                child: FutureBuilder<List<SeminarRoom>>(
                  future: _future,
                  builder: (context, snapshot) {
                    if (snapshot.connectionState == ConnectionState.waiting) {
                      return const Center(
                        child: CircularProgressIndicator(
                          valueColor: AlwaysStoppedAnimation<Color>(
                            AppTheme.accentGold,
                          ),
                        ),
                      );
                    }

                    if (snapshot.hasError) {
                      return ErrorStateWidget(
                        message: 'Erreur lors du chargement.',
                        hint: 'Vérifiez votre connexion et réessayez.',
                        onRetry: () {
                          HapticHelper.lightImpact();
                          setState(() {
                            _future = SeminarsApi().getSeminarRooms();
                          });
                        },
                      );
                    }

                    final rooms = snapshot.data ?? const [];
                    if (rooms.isEmpty) {
                      return EmptyStateWidget(
                        icon: Icons.meeting_room_outlined,
                        title: 'Aucune salle',
                        subtitle: 'Aucune salle de séminaire n’est disponible.',
                      );
                    }

                    return ListView.separated(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 16,
                        vertical: 8,
                      ),
                      itemCount: rooms.length,
                      separatorBuilder: (_, _) => const SizedBox(height: 12),
                      itemBuilder: (context, index) {
                        final room = rooms[index];
                        return _RoomTile(
                          room: room,
                          onTap: () {
                            HapticHelper.lightImpact();
                            context.navigateTo(
                              SeminarRoomDetailScreen(room: room),
                            );
                          },
                        );
                      },
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

class _RoomTile extends StatelessWidget {
  final SeminarRoom room;
  final VoidCallback onTap;

  const _RoomTile({required this.room, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return Material(
      color: AppTheme.primaryDark.withValues(alpha: 0.35),
      borderRadius: BorderRadius.circular(14),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(14),
        child: Padding(
          padding: const EdgeInsets.all(14),
          child: Row(
            children: [
              Container(
                width: 44,
                height: 44,
                decoration: BoxDecoration(
                  color: AppTheme.primaryBlue.withValues(alpha: 0.35),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(
                    color: AppTheme.accentGold.withValues(alpha: 0.25),
                  ),
                ),
                child: const Icon(
                  Icons.meeting_room_outlined,
                  color: AppTheme.accentGold,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      room.name,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      room.capacity != null
                          ? 'Capacité: ${room.capacity}'
                          : 'Capacité: —',
                      style: const TextStyle(color: AppTheme.textGray),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 10),
              const Icon(Icons.chevron_right, color: AppTheme.textGray),
            ],
          ),
        ),
      ),
    );
  }
}
