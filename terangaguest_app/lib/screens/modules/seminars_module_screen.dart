import 'package:flutter/material.dart';
import '../../config/theme.dart';
import '../../utils/haptic_helper.dart';
import '../../utils/layout_helper.dart';
import '../../services/seminars_api.dart';
import '../../models/seminar_room.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/error_state.dart';
import '../../widgets/seminar_room_card.dart';
import '../../utils/navigation_helper.dart';
import 'seminar_room_detail_screen.dart';

class SeminarsModuleScreen extends StatefulWidget {
  const SeminarsModuleScreen({super.key});

  @override
  State<SeminarsModuleScreen> createState() => _SeminarsModuleScreenState();
}

class _SeminarsModuleScreenState extends State<SeminarsModuleScreen> {
  late Future<List<SeminarRoom>> _future;

  Future<List<SeminarRoom>> _loadRooms() => SeminarsApi().getSeminarRooms();

  @override
  void initState() {
    super.initState();
    _future = _loadRooms();
  }

  Future<void> _refresh() async {
    setState(() {
      _future = _loadRooms();
    });
    await _future;
  }

  @override
  Widget build(BuildContext context) {
    final titleSize =
        MediaQuery.of(context).size.width < 600 ? 18.0 : 28.0;

    return Scaffold(
      body: Container(
        decoration: BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: const [AppTheme.primaryDark, AppTheme.primaryBlue],
          ),
        ),
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
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            'Séminaires',
                            style: TextStyle(
                              fontSize: titleSize,
                              fontWeight: FontWeight.bold,
                              color: AppTheme.accentGold,
                            ),
                          ),
                          const SizedBox(height: 4),
                          const Text(
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
                          _refresh();
                        },
                      );
                    }

                    final rooms = snapshot.data ?? const [];
                    if (rooms.isEmpty) {
                      return RefreshIndicator(
                        color: AppTheme.accentGold,
                        onRefresh: _refresh,
                        child: SingleChildScrollView(
                          physics: const AlwaysScrollableScrollPhysics(),
                          child: SizedBox(
                            height: MediaQuery.sizeOf(context).height * 0.65,
                            child: const EmptyStateWidget(
                              icon: Icons.meeting_room_outlined,
                              title: 'Aucune salle',
                              subtitle:
                                  'Aucune salle de séminaire n’est disponible.',
                            ),
                          ),
                        ),
                      );
                    }

                    return RefreshIndicator(
                      color: AppTheme.accentGold,
                      onRefresh: _refresh,
                      child: Align(
                        alignment: Alignment.topCenter,
                        child: Padding(
                          padding: EdgeInsets.only(
                            left: LayoutHelper.horizontalPaddingValue(context),
                            right: LayoutHelper.horizontalPaddingValue(context),
                            top: 12,
                            bottom: 24,
                          ),
                          child: GridView.builder(
                            shrinkWrap: true,
                            physics: const AlwaysScrollableScrollPhysics(),
                            gridDelegate:
                                SliverGridDelegateWithFixedCrossAxisCount(
                              crossAxisCount:
                                  LayoutHelper.gridCrossAxisCount(context),
                              childAspectRatio:
                                  LayoutHelper.listCellAspectRatio(context),
                              crossAxisSpacing:
                                  LayoutHelper.gridSpacing(context),
                              mainAxisSpacing:
                                  LayoutHelper.gridSpacing(context),
                            ),
                            itemCount: rooms.length,
                            itemBuilder: (context, index) {
                              final room = rooms[index];
                              return SeminarRoomCard(
                                room: room,
                                onTap: () {
                                  HapticHelper.lightImpact();
                                  context.navigateTo(
                                    SeminarRoomDetailScreen(room: room),
                                  );
                                },
                              );
                            },
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
