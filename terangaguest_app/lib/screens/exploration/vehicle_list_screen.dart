import 'package:flutter/material.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../models/vehicle.dart';
import '../../services/vehicle_api.dart';
import '../../utils/layout_helper.dart';
import '../../utils/navigation_helper.dart';
import '../../utils/haptic_helper.dart';
import '../../widgets/empty_state.dart';
import '../../widgets/error_state.dart';
import 'vehicle_rental_request_screen.dart';

/// Catalogue des véhicules disponibles (Location de Véhicule).
class VehicleListScreen extends StatefulWidget {
  const VehicleListScreen({super.key});

  @override
  State<VehicleListScreen> createState() => _VehicleListScreenState();
}

class _VehicleListScreenState extends State<VehicleListScreen> {
  final VehicleApi _vehicleApi = VehicleApi();
  List<Vehicle> _vehicles = [];
  bool _loading = true;
  String? _error;
  /// Filtres : null = tous
  String? _filterType;
  int? _filterMinSeats;

  @override
  void initState() {
    super.initState();
    _loadVehicles();
  }

  Future<void> _loadVehicles() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final list = await _vehicleApi.getVehicles(
        vehicleType: _filterType,
        minSeats: _filterMinSeats,
      );
      if (mounted) {
        setState(() {
          _vehicles = list;
          _loading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _error = e.toString();
          _loading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);

    return Scaffold(
      body: Container(
        decoration: BoxDecoration(
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
                padding: const EdgeInsets.all(20.0),
                child: Row(
                  children: [
                    IconButton(
                      icon: const Icon(Icons.arrow_back, color: AppTheme.accentGold),
                      onPressed: () {
                        HapticHelper.lightImpact();
                        Navigator.pop(context);
                      },
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(
                            l10n.vehicleRentalTitle,
                            style: const TextStyle(
                              fontSize: 24,
                              fontWeight: FontWeight.bold,
                              color: Colors.white,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            l10n.vehicleRentalSubtitle,
                            style: const TextStyle(
                              fontSize: 13,
                              color: AppTheme.textGray,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              _buildFilters(context),
              Expanded(
                child: _loading
                    ? const Center(
                        child: CircularProgressIndicator(
                          valueColor: AlwaysStoppedAnimation<Color>(AppTheme.accentGold),
                        ),
                      )
                    : _error != null
                        ? ErrorStateWidget(
                            message: _error!,
                            hint: l10n.errorHint,
                            onRetry: _loadVehicles,
                          )
                        : _vehicles.isEmpty
                            ? EmptyStateWidget(
                                icon: Icons.directions_car_outlined,
                                title: l10n.noVehicleAvailable,
                                subtitle: l10n.noVehicleAvailableHint,
                              )
                            : RefreshIndicator(
                                color: AppTheme.accentGold,
                                onRefresh: _loadVehicles,
                                child: Padding(
                                  padding: EdgeInsets.symmetric(
                                    horizontal: LayoutHelper.horizontalPaddingValue(context),
                                    vertical: 8,
                                  ),
                                  child: GridView.builder(
                                    gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                                      crossAxisCount: LayoutHelper.gridCrossAxisCount(context),
                                      childAspectRatio: 0.82,
                                      crossAxisSpacing: LayoutHelper.gridSpacing(context),
                                      mainAxisSpacing: LayoutHelper.gridSpacing(context),
                                    ),
                                    itemCount: _vehicles.length,
                                    itemBuilder: (context, index) {
                                      final v = _vehicles[index];
                                      return _VehicleCard(
                                        vehicle: v,
                                        onTap: () {
                                          HapticHelper.lightImpact();
                                          context.navigateTo(VehicleRentalRequestScreen(vehicle: v));
                                        },
                                      );
                                    },
                                  ),
                                ),
                              ),
            ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildFilters(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final types = [
      (null, l10n.filterAllTypes),
      ('berline', l10n.vehicleTypeBerline),
      ('suv', l10n.vehicleTypeSuv),
      ('minibus', l10n.vehicleTypeMinibus),
      ('van', l10n.vehicleTypeVan),
      ('other', l10n.vehicleTypeOther),
    ];
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
      child: Row(
        children: [
          Expanded(
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
              decoration: BoxDecoration(
                color: AppTheme.primaryBlue.withValues(alpha: 0.6),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: AppTheme.accentGold.withValues(alpha: 0.4)),
              ),
              child: DropdownButtonHideUnderline(
                child: DropdownButton<String?>(
                  value: _filterType,
                  isExpanded: true,
                  hint: Text(
                    l10n.filterVehicleType,
                    style: const TextStyle(color: AppTheme.textGray, fontSize: 14),
                  ),
                  dropdownColor: AppTheme.primaryBlue,
                  icon: const Icon(Icons.arrow_drop_down, color: AppTheme.accentGold),
                  items: types.map((t) => DropdownMenuItem<String?>(
                    value: t.$1,
                    child: Text(
                      t.$2,
                      style: const TextStyle(color: Colors.white, fontSize: 14),
                    ),
                  )).toList(),
                  onChanged: (v) {
                    setState(() {
                      _filterType = v;
                      _loadVehicles();
                    });
                  },
                ),
              ),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
              decoration: BoxDecoration(
                color: AppTheme.primaryBlue.withValues(alpha: 0.6),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: AppTheme.accentGold.withValues(alpha: 0.4)),
              ),
              child: DropdownButtonHideUnderline(
                child: DropdownButton<int?>(
                  value: _filterMinSeats,
                  isExpanded: true,
                  hint: Text(
                    l10n.filterMinSeats,
                    style: const TextStyle(color: AppTheme.textGray, fontSize: 14),
                  ),
                  dropdownColor: AppTheme.primaryBlue,
                  icon: const Icon(Icons.arrow_drop_down, color: AppTheme.accentGold),
                  items: [
                    DropdownMenuItem<int?>(value: null, child: Text(l10n.filterAllTypes, style: const TextStyle(color: Colors.white, fontSize: 14))),
                    ...List.generate(19, (i) => i + 2).map((s) => DropdownMenuItem<int?>(
                      value: s,
                      child: Text('$s', style: const TextStyle(color: Colors.white, fontSize: 14)),
                    )),
                  ],
                  onChanged: (v) {
                    setState(() {
                      _filterMinSeats = v;
                      _loadVehicles();
                    });
                  },
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _VehicleCard extends StatelessWidget {
  final Vehicle vehicle;
  final VoidCallback onTap;

  const _VehicleCard({required this.vehicle, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        decoration: BoxDecoration(
          color: AppTheme.primaryBlue.withValues(alpha: 0.6),
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: AppTheme.accentGold.withValues(alpha: 0.5)),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Expanded(
              flex: 3,
              child: ClipRRect(
                borderRadius: const BorderRadius.vertical(top: Radius.circular(15)),
                child: vehicle.image != null && vehicle.image!.isNotEmpty
                    ? Image.network(
                        vehicle.image!,
                        fit: BoxFit.cover,
                        width: double.infinity,
                        loadingBuilder: (context, child, progress) {
                          if (progress == null) return child;
                          return const Center(
                            child: CircularProgressIndicator(
                              valueColor: AlwaysStoppedAnimation<Color>(AppTheme.accentGold),
                              strokeWidth: 2,
                            ),
                          );
                        },
                        errorBuilder: (context, error, stackTrace) => const Center(
                          child: Icon(Icons.directions_car, color: AppTheme.textGray, size: 48),
                        ),
                      )
                    : const Center(
                        child: Icon(Icons.directions_car, color: AppTheme.textGray, size: 48),
                      ),
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    vehicle.name,
                    style: const TextStyle(
                      color: Colors.white,
                      fontWeight: FontWeight.w600,
                      fontSize: 15,
                    ),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 4),
                  Text(
                    '${vehicle.vehicleTypeLabel} · ${vehicle.numberOfSeats} pl.',
                    style: const TextStyle(
                      color: AppTheme.textGray,
                      fontSize: 12,
                    ),
                  ),
                  if (vehicle.pricePerDay != null || vehicle.priceHalfDay != null) ...[
                    const SizedBox(height: 6),
                    Text(
                      vehicle.displayPricePerDay,
                      style: const TextStyle(
                        color: AppTheme.accentGold,
                        fontSize: 13,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ],
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
