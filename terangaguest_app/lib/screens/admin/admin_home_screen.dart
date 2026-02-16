import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../providers/auth_provider.dart';
import '../../services/admin_api.dart';
import '../../utils/haptic_helper.dart';
import '../../utils/layout_helper.dart';
import '../../widgets/service_card.dart';
import '../orders/orders_list_screen.dart';
import '../restaurants/my_reservations_screen.dart';
import '../spa/my_spa_reservations_screen.dart';
import '../excursions/my_excursion_bookings_screen.dart';
import '../laundry/my_laundry_requests_screen.dart';
import '../palace/my_palace_requests_screen.dart';
import '../hotel_infos/assistance_emergency_screen.dart';
import 'admin_chat_conversations_screen.dart';

/// Page d'accueil pour les administrateurs / staff dans l'app mobile.
/// Affiche des boxes pour accéder aux différents modules de gestion.
class AdminHomeScreen extends StatefulWidget {
  const AdminHomeScreen({super.key});

  @override
  State<AdminHomeScreen> createState() => _AdminHomeScreenState();
}

class _AdminHomeScreenState extends State<AdminHomeScreen> {
  final AdminApi _adminApi = AdminApi();
  AdminSummary? _summary;
  bool _isLoading = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadSummary();
  }

  Future<void> _loadSummary() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final data = await _adminApi.getSummary();
      if (!mounted) return;
      setState(() {
        _summary = data;
        _isLoading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final user = context.watch<AuthProvider>().user;
    final enterpriseName = user?.enterprise?.name ?? 'Votre établissement';

    final tiles = [
      _AdminTile(
        icon: Icons.room_service_outlined,
        label: 'Commandes Room Service',
        routeKey: 'admin-room-service',
        badge: _summary?.ordersPending ?? 0,
      ),
      _AdminTile(
        icon: Icons.restaurant_menu_outlined,
        label: 'Réservations Restaurants',
        routeKey: 'admin-restaurants',
        badge: _summary?.restaurantPending ?? 0,
      ),
      _AdminTile(
        icon: Icons.spa_outlined,
        label: 'Réservations Spa & Bien-être',
        routeKey: 'admin-spa',
        badge: _summary?.spaPending ?? 0,
      ),
      _AdminTile(
        icon: Icons.hiking_outlined,
        label: 'Excursions & Activités',
        routeKey: 'admin-excursions',
        badge: _summary?.excursionsPending ?? 0,
      ),
      _AdminTile(
        icon: Icons.local_laundry_service_outlined,
        label: 'Demandes Blanchisserie',
        routeKey: 'admin-laundry',
        badge: _summary?.laundryPending ?? 0,
      ),
      _AdminTile(
        icon: Icons.workspace_premium_outlined,
        label: 'Services Palace / Conciergerie',
        routeKey: 'admin-palace',
        badge: _summary?.palacePending ?? 0,
      ),
      _AdminTile(
        icon: Icons.health_and_safety_outlined,
        label: 'Assistance & Urgence',
        routeKey: 'admin-emergency',
        badge: _summary?.emergencyOpen ?? 0,
      ),
      _AdminTile(
        icon: Icons.chat_bubble_outline,
        label: 'Messages / Chat client',
        routeKey: 'admin-chat',
        badge: 0,
      ),
    ];

    final crossAxisCount = LayoutHelper.gridCrossAxisCount(context);
    final aspectRatio = LayoutHelper.dashboardCellAspectRatio(context);
    final spacing = LayoutHelper.gridSpacing(context);

    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(gradient: AppTheme.backgroundGradient),
        child: SafeArea(
          child: Column(
            children: [
              _buildAppBar(context, enterpriseName),
              if (_error != null)
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  child: Container(
                    width: double.infinity,
                    margin: const EdgeInsets.only(bottom: 8),
                    padding: const EdgeInsets.symmetric(
                      horizontal: 12,
                      vertical: 8,
                    ),
                    decoration: BoxDecoration(
                      color: Colors.redAccent.withValues(alpha: 0.2),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: Colors.redAccent, width: 1),
                    ),
                    child: Row(
                      children: const [
                        Icon(
                          Icons.error_outline,
                          color: Colors.redAccent,
                          size: 18,
                        ),
                        SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            'Impossible de charger les statistiques. Les données peuvent être incomplètes.',
                            style: TextStyle(color: Colors.white, fontSize: 12),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              Expanded(
                child: Padding(
                  padding: LayoutHelper.horizontalPadding(context),
                  child: GridView.builder(
                    padding: EdgeInsets.symmetric(vertical: spacing),
                    gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                      crossAxisCount: crossAxisCount,
                      crossAxisSpacing: spacing,
                      mainAxisSpacing: spacing,
                      childAspectRatio: aspectRatio,
                    ),
                    itemCount: tiles.length,
                    itemBuilder: (context, index) {
                      final tile = tiles[index];
                      return ServiceCard(
                        title: tile.label,
                        icon: tile.icon,
                        badge: tile.badge > 0 ? tile.badge.toString() : null,
                        isLoading: _isLoading && _summary == null,
                        onTap: () {
                          HapticHelper.lightImpact();
                          _handleTileTap(context, tile, l10n);
                        },
                      );
                    },
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildAppBar(BuildContext context, String enterpriseName) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
      child: Row(
        children: [
          const Icon(
            Icons.dashboard_outlined,
            color: AppTheme.accentGold,
            size: 28,
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                const Text(
                  'Espace Administrateur',
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 20,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  enterpriseName,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    color: AppTheme.textGray,
                    fontSize: 13,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  void _handleTileTap(
    BuildContext context,
    _AdminTile tile,
    AppLocalizations l10n,
  ) {
    switch (tile.routeKey) {
      case 'admin-room-service':
        Navigator.of(
          context,
        ).push(MaterialPageRoute(builder: (_) => const OrdersListScreen()));
        break;
      case 'admin-restaurants':
        Navigator.of(context).push(
          MaterialPageRoute(
            builder: (_) => const MyRestaurantReservationsScreen(),
          ),
        );
        break;
      case 'admin-spa':
        Navigator.of(context).push(
          MaterialPageRoute(builder: (_) => const MySpaReservationsScreen()),
        );
        break;
      case 'admin-excursions':
        Navigator.of(context).push(
          MaterialPageRoute(builder: (_) => const MyExcursionBookingsScreen()),
        );
        break;
      case 'admin-laundry':
        Navigator.of(context).push(
          MaterialPageRoute(builder: (_) => const MyLaundryRequestsScreen()),
        );
        break;
      case 'admin-palace':
        Navigator.of(context).push(
          MaterialPageRoute(builder: (_) => const MyPalaceRequestsScreen()),
        );
        break;
      case 'admin-emergency':
        Navigator.of(context).push(
          MaterialPageRoute(builder: (_) => const AssistanceEmergencyScreen()),
        );
        break;
      case 'admin-chat':
        Navigator.of(context).push(
          MaterialPageRoute(
            builder: (_) => const AdminChatConversationsScreen(),
          ),
        );
        break;
      default:
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              'Section "${tile.label}" en préparation pour la version staff mobile.',
            ),
            backgroundColor: AppTheme.accentGold,
            duration: const Duration(seconds: 2),
          ),
        );
    }
  }
}

class _AdminTile {
  final IconData icon;
  final String label;
  final String routeKey;
  final int badge;

  const _AdminTile({
    required this.icon,
    required this.label,
    required this.routeKey,
    this.badge = 0,
  });
}
