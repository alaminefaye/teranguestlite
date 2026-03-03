import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import 'package:geolocator/geolocator.dart';
import '../../config/theme.dart';
import '../../generated/l10n/app_localizations.dart';
import '../../models/palace.dart';
import '../../models/vehicle.dart';
import '../../providers/auth_provider.dart';
import '../../providers/palace_provider.dart';
import '../../providers/tablet_session_provider.dart';
import '../../services/vehicle_api.dart';
import '../../widgets/animated_button.dart';

/// Type de demande véhicule : taxi ou location.
enum VehicleRequestType { taxi, rental }

class CreatePalaceRequestScreen extends StatefulWidget {
  final PalaceService service;

  const CreatePalaceRequestScreen({super.key, required this.service});

  @override
  State<CreatePalaceRequestScreen> createState() =>
      _CreatePalaceRequestScreenState();
}

class _CreatePalaceRequestScreenState extends State<CreatePalaceRequestScreen> {
  final TextEditingController _detailsController = TextEditingController();
  final TextEditingController _clientCodeController = TextEditingController();
  DateTime? _scheduledTime;

  // Véhicule : type choisi (null si service non véhicule ou pas encore choisi)
  VehicleRequestType? _vehicleType;

  // Taxi
  final TextEditingController _pickupController = TextEditingController();
  final TextEditingController _destinationController = TextEditingController();
  final TextEditingController _distanceController = TextEditingController();
  double? _pickupLat;
  double? _pickupLng;
  bool _loadingLocation = false;

  // Location
  final TextEditingController _rentalDaysController = TextEditingController();
  final TextEditingController _rentalDurationController =
      TextEditingController();
  final VehicleApi _vehicleApi = VehicleApi();
  List<Vehicle> _vehicles = [];
  bool _loadingVehicles = false;
  Vehicle? _selectedVehicle;
  String? _filterVehicleType;
  int? _filterMinSeats;

  bool get _isVehicleService => widget.service.isVehicleService;

  @override
  void initState() {
    super.initState();
    _rentalDaysController.addListener(_onRentalFieldsChanged);
    _rentalDurationController.addListener(_onRentalFieldsChanged);
    _clientCodeController.addListener(_onRentalFieldsChanged);
    WidgetsBinding.instance.addPostFrameCallback((_) async {
      context.read<AuthProvider>().loadUser();
      if (!mounted) return;
      final tabletSession = context.read<TabletSessionProvider>();
      final auth = context.read<AuthProvider>();
      await tabletSession.load();
      if (!mounted) return;
      final authRoom = auth.user?.roomNumber?.trim() ?? '';
      if (authRoom.isNotEmpty) await tabletSession.setRoomNumber(authRoom);
      await tabletSession.tryRestoreSessionFromRoom();
      if (!mounted) return;
      final code = tabletSession.clientCodeForPreFill;
      if (code != null && code.isNotEmpty && _clientCodeController.text.isEmpty) {
        _clientCodeController.text = code;
        setState(() {});
      }
    });
  }

  void _onRentalFieldsChanged() {
    if (mounted) setState(() {});
  }

  @override
  void dispose() {
    _rentalDaysController.removeListener(_onRentalFieldsChanged);
    _rentalDurationController.removeListener(_onRentalFieldsChanged);
    _clientCodeController.removeListener(_onRentalFieldsChanged);
    _detailsController.dispose();
    _clientCodeController.dispose();
    _pickupController.dispose();
    _destinationController.dispose();
    _distanceController.dispose();
    _rentalDaysController.dispose();
    _rentalDurationController.dispose();
    super.dispose();
  }

  Future<void> _loadVehicles() async {
    if (!_isVehicleService || _vehicleType != VehicleRequestType.rental) return;
    setState(() => _loadingVehicles = true);
    try {
      final list = await _vehicleApi.getVehicles(
        vehicleType: _filterVehicleType,
        minSeats: _filterMinSeats,
      );
      if (mounted) {
        setState(() {
          _vehicles = list;
          _loadingVehicles = false;
        });
      }
    } catch (_) {
      if (mounted) {
        setState(() {
          _vehicles = [];
          _loadingVehicles = false;
        });
      }
    }
  }

  Future<void> _useMyLocation() async {
    setState(() => _loadingLocation = true);
    try {
      bool serviceEnabled = await Geolocator.isLocationServiceEnabled();
      if (!serviceEnabled) {
        if (mounted) _showSnack('Activez la localisation dans les paramètres.');
        return;
      }
      LocationPermission permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
      }
      if (permission == LocationPermission.deniedForever) {
        if (mounted) _showSnack('Accès à la position refusé.');
        return;
      }
      final pos = await Geolocator.getCurrentPosition();
      if (mounted) {
        setState(() {
          _pickupLat = pos.latitude;
          _pickupLng = pos.longitude;
          _pickupController.text =
              'Position actuelle (${pos.latitude.toStringAsFixed(5)}, ${pos.longitude.toStringAsFixed(5)})';
          _loadingLocation = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() => _loadingLocation = false);
        _showSnack('Impossible d\'obtenir la position: $e');
      }
    }
  }

  void _showSnack(String msg) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(msg), backgroundColor: Colors.orange),
    );
  }

  Widget _buildVehicleTypeChoice() {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [AppTheme.primaryBlue, AppTheme.primaryDark],
        ),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppTheme.accentGold, width: 1.5),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Type de demande',
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.bold,
              color: AppTheme.accentGold,
            ),
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                child: _vehicleChip(
                  label: 'Taxi',
                  selected: _vehicleType == VehicleRequestType.taxi,
                  onTap: () =>
                      setState(() => _vehicleType = VehicleRequestType.taxi),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: _vehicleChip(
                  label: 'Location',
                  selected: _vehicleType == VehicleRequestType.rental,
                  onTap: () {
                    setState(() => _vehicleType = VehicleRequestType.rental);
                    _loadVehicles();
                  },
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _vehicleChip({
    required String label,
    required bool selected,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(12),
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 14),
        decoration: BoxDecoration(
          color: selected
              ? AppTheme.accentGold.withValues(alpha: 0.25)
              : AppTheme.primaryBlue.withValues(alpha: 0.5),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: selected
                ? AppTheme.accentGold
                : AppTheme.accentGold.withValues(alpha: 0.3),
          ),
        ),
        child: Center(
          child: Text(
            label,
            style: TextStyle(
              fontWeight: FontWeight.w600,
              color: selected ? AppTheme.accentGold : AppTheme.textGray,
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildTaxiFields() {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [AppTheme.primaryBlue, AppTheme.primaryDark],
        ),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppTheme.accentGold, width: 1.5),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Prise en charge',
            style: TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.bold,
              color: AppTheme.accentGold,
            ),
          ),
          const SizedBox(height: 8),
          Row(
            children: [
              Expanded(
                child: TextField(
                  controller: _pickupController,
                  readOnly: true,
                  style: const TextStyle(color: Colors.white, fontSize: 14),
                  decoration: InputDecoration(
                    hintText: 'Adresse ou position',
                    hintStyle: TextStyle(
                      color: AppTheme.textGray.withValues(alpha: 0.6),
                      fontSize: 14,
                    ),
                    filled: true,
                    fillColor: AppTheme.primaryBlue.withValues(alpha: 0.5),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: BorderSide(
                        color: AppTheme.accentGold.withValues(alpha: 0.3),
                      ),
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 8),
              SizedBox(
                height: 48,
                child: TextButton.icon(
                  onPressed: _loadingLocation ? null : _useMyLocation,
                  icon: _loadingLocation
                      ? const SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(
                            strokeWidth: 2,
                            valueColor: AlwaysStoppedAnimation<Color>(
                              AppTheme.accentGold,
                            ),
                          ),
                        )
                      : const Icon(
                          Icons.my_location,
                          color: AppTheme.accentGold,
                        ),
                  label: Text(
                    'Ma position',
                    style: TextStyle(
                      color: AppTheme.accentGold,
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          Text(
            'Destination',
            style: TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.bold,
              color: AppTheme.accentGold,
            ),
          ),
          const SizedBox(height: 8),
          TextField(
            controller: _destinationController,
            style: const TextStyle(color: Colors.white),
            decoration: InputDecoration(
              hintText: 'Adresse de destination',
              hintStyle: TextStyle(
                color: AppTheme.textGray.withValues(alpha: 0.6),
              ),
              filled: true,
              fillColor: AppTheme.primaryBlue.withValues(alpha: 0.5),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: BorderSide(
                  color: AppTheme.accentGold.withValues(alpha: 0.3),
                ),
              ),
            ),
          ),
          const SizedBox(height: 12),
          Text(
            'Distance (km, optionnel)',
            style: TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.bold,
              color: AppTheme.accentGold,
            ),
          ),
          const SizedBox(height: 8),
          TextField(
            controller: _distanceController,
            keyboardType: const TextInputType.numberWithOptions(decimal: true),
            style: const TextStyle(color: Colors.white),
            decoration: InputDecoration(
              hintText: 'Ex: 5.2',
              hintStyle: TextStyle(
                color: AppTheme.textGray.withValues(alpha: 0.6),
              ),
              filled: true,
              fillColor: AppTheme.primaryBlue.withValues(alpha: 0.5),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: BorderSide(
                  color: AppTheme.accentGold.withValues(alpha: 0.3),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  static const List<MapEntry<String, String>> _vehicleTypeFilters = [
    MapEntry('', 'Tous les types'),
    MapEntry('berline', 'Berline'),
    MapEntry('suv', 'SUV'),
    MapEntry('minibus', 'Minibus'),
    MapEntry('van', 'Van'),
    MapEntry('other', 'Autre'),
  ];

  Widget _buildRentalFields() {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [AppTheme.primaryBlue, AppTheme.primaryDark],
        ),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppTheme.accentGold, width: 1.5),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Choisir un véhicule',
            style: TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.bold,
              color: AppTheme.accentGold,
            ),
          ),
          const SizedBox(height: 8),
          Row(
            children: [
              Expanded(
                child: DropdownButtonHideUnderline(
                  child: DropdownButton<String>(
                    value: _filterVehicleType ?? '',
                    isExpanded: true,
                    dropdownColor: AppTheme.primaryBlue,
                    hint: Text(
                      'Type',
                      style: TextStyle(color: AppTheme.textGray, fontSize: 14),
                    ),
                    items: _vehicleTypeFilters
                        .map(
                          (e) => DropdownMenuItem(
                            value: e.key,
                            child: Text(
                              e.value,
                              style: const TextStyle(
                                color: Colors.white,
                                fontSize: 13,
                              ),
                            ),
                          ),
                        )
                        .toList(),
                    onChanged: (v) {
                      setState(() {
                        _filterVehicleType = (v != null && v.isNotEmpty)
                            ? v
                            : null;
                        _loadVehicles();
                      });
                    },
                  ),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: DropdownButtonHideUnderline(
                  child: DropdownButton<int?>(
                    value: _filterMinSeats,
                    isExpanded: true,
                    dropdownColor: AppTheme.primaryBlue,
                    hint: Text(
                      'Places min.',
                      style: TextStyle(color: AppTheme.textGray, fontSize: 14),
                    ),
                    items: [
                      const DropdownMenuItem<int?>(
                        value: null,
                        child: Text(
                          'Toutes',
                          style: TextStyle(color: Colors.white, fontSize: 13),
                        ),
                      ),
                      ...List.generate(20, (i) => i + 1).map(
                        (s) => DropdownMenuItem<int?>(
                          value: s,
                          child: Text(
                            '$s place(s)',
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 13,
                            ),
                          ),
                        ),
                      ),
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
            ],
          ),
          const SizedBox(height: 12),
          if (_loadingVehicles)
            const Center(
              child: Padding(
                padding: EdgeInsets.all(16),
                child: CircularProgressIndicator(
                  valueColor: AlwaysStoppedAnimation<Color>(
                    AppTheme.accentGold,
                  ),
                ),
              ),
            )
          else if (_vehicles.isEmpty)
            Padding(
              padding: const EdgeInsets.symmetric(vertical: 8),
              child: Text(
                'Aucun véhicule pour ces critères.',
                style: TextStyle(color: AppTheme.textGray, fontSize: 13),
              ),
            )
          else
            SizedBox(
              height: 158,
              child: ListView.builder(
                scrollDirection: Axis.horizontal,
                itemCount: _vehicles.length,
                itemBuilder: (context, index) {
                  final v = _vehicles[index];
                  final selected = _selectedVehicle?.id == v.id;
                  return Padding(
                    padding: const EdgeInsets.only(right: 12),
                    child: InkWell(
                      onTap: () => setState(() => _selectedVehicle = v),
                      borderRadius: BorderRadius.circular(12),
                      child: Container(
                        width: 120,
                        padding: const EdgeInsets.all(8),
                        decoration: BoxDecoration(
                          color: selected
                              ? AppTheme.accentGold.withValues(alpha: 0.2)
                              : AppTheme.primaryBlue.withValues(alpha: 0.5),
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(
                            color: selected
                                ? AppTheme.accentGold
                                : AppTheme.accentGold.withValues(alpha: 0.3),
                          ),
                        ),
                        child: Column(
                          mainAxisSize: MainAxisSize.min,
                          crossAxisAlignment: CrossAxisAlignment.stretch,
                          children: [
                            ClipRRect(
                              borderRadius: BorderRadius.circular(8),
                              child: v.image != null && v.image!.isNotEmpty
                                  ? Image.network(
                                      v.image!,
                                      height: 52,
                                      width: double.infinity,
                                      fit: BoxFit.cover,
                                      loadingBuilder:
                                          (context, child, loadingProgress) {
                                            if (loadingProgress == null) {
                                              return child;
                                            }
                                            return const SizedBox(
                                              height: 52,
                                              child: Center(
                                                child: CircularProgressIndicator(
                                                  strokeWidth: 2,
                                                  valueColor:
                                                      AlwaysStoppedAnimation<
                                                        Color
                                                      >(AppTheme.accentGold),
                                                ),
                                              ),
                                            );
                                          },
                                      errorBuilder:
                                          (context, error, stackTrace) =>
                                              const SizedBox(
                                                height: 52,
                                                child: Center(
                                                  child: Icon(
                                                    Icons.directions_car,
                                                    color: AppTheme.textGray,
                                                    size: 28,
                                                  ),
                                                ),
                                              ),
                                    )
                                  : const SizedBox(
                                      height: 52,
                                      child: Center(
                                        child: Icon(
                                          Icons.directions_car,
                                          color: AppTheme.textGray,
                                          size: 28,
                                        ),
                                      ),
                                    ),
                            ),
                            const SizedBox(height: 6),
                            Text(
                              v.name,
                              style: const TextStyle(
                                color: Colors.white,
                                fontSize: 11,
                                fontWeight: FontWeight.w600,
                              ),
                              maxLines: 2,
                              overflow: TextOverflow.ellipsis,
                            ),
                            const SizedBox(height: 2),
                            Text(
                              '${v.vehicleTypeLabel} · ${v.numberOfSeats} pl.',
                              style: TextStyle(
                                color: AppTheme.textGray,
                                fontSize: 10,
                              ),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                            if (v.pricePerDay != null || v.priceHalfDay != null)
                              Padding(
                                padding: const EdgeInsets.only(top: 4),
                                child: Text(
                                  v.displayPricePerDay,
                                  style: TextStyle(
                                    color: AppTheme.accentGold,
                                    fontSize: 10,
                                    fontWeight: FontWeight.w600,
                                  ),
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                          ],
                        ),
                      ),
                    ),
                  );
                },
              ),
            ),
          const SizedBox(height: 16),
          _labeledField(
            label: 'Nombre de jours',
            controller: _rentalDaysController,
            hint: 'Ex: 2',
            keyboardType: TextInputType.number,
          ),
          const SizedBox(height: 12),
          _labeledField(
            label: 'Durée (heures)',
            controller: _rentalDurationController,
            hint: 'Ex: 8 (demi-journée si ≤ 5 h)',
            keyboardType: TextInputType.number,
          ),
          if (_selectedVehicle != null) ...[
            const SizedBox(height: 12),
            _buildRentalPriceEstimate(),
          ],
        ],
      ),
    );
  }

  Widget _buildRentalPriceEstimate() {
    final days = int.tryParse(_rentalDaysController.text.trim());
    final hours = int.tryParse(_rentalDurationController.text.trim());
    final estimate = _selectedVehicle?.estimatePrice(
      rentalDays: days,
      rentalDurationHours: hours,
    );
    if (estimate == null) return const SizedBox.shrink();
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 12),
      decoration: BoxDecoration(
        color: AppTheme.accentGold.withValues(alpha: 0.15),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: AppTheme.accentGold.withValues(alpha: 0.4)),
      ),
      child: Row(
        children: [
          Icon(Icons.receipt_long, color: AppTheme.accentGold, size: 20),
          const SizedBox(width: 10),
          Text(
            'Estimation : ${estimate.toInt()} FCFA',
            style: TextStyle(
              color: AppTheme.accentGold,
              fontSize: 14,
              fontWeight: FontWeight.w600,
            ),
          ),
        ],
      ),
    );
  }

  Widget _labeledField({
    required String label,
    required TextEditingController controller,
    required String hint,
    TextInputType? keyboardType,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.bold,
            color: AppTheme.accentGold,
          ),
        ),
        const SizedBox(height: 8),
        TextField(
          controller: controller,
          keyboardType: keyboardType,
          style: const TextStyle(color: Colors.white),
          decoration: InputDecoration(
            hintText: hint,
            hintStyle: TextStyle(
              color: AppTheme.textGray.withValues(alpha: 0.6),
            ),
            filled: true,
            fillColor: AppTheme.primaryBlue.withValues(alpha: 0.5),
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: BorderSide(
                color: AppTheme.accentGold.withValues(alpha: 0.3),
              ),
            ),
          ),
        ),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
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
                      icon: const Icon(
                        Icons.arrow_back,
                        color: AppTheme.accentGold,
                      ),
                      onPressed: () => Navigator.pop(context),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            AppLocalizations.of(context).demand,
                            style: TextStyle(
                              fontSize: MediaQuery.of(context).size.width < 600
                                  ? 18
                                  : 28,
                              fontWeight: FontWeight.bold,
                              color: AppTheme.accentGold,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            widget.service.name,
                            style: const TextStyle(
                              fontSize: 14,
                              color: AppTheme.textGray,
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              Expanded(
                child: SingleChildScrollView(
                  padding: EdgeInsets.symmetric(
                    horizontal: MediaQuery.of(context).size.width < 600
                        ? 16
                        : 60,
                    vertical: 20,
                  ),
                  child: Column(
                    children: [
                      _buildCanReserveBanner(),
                      if (_isVehicleService) ...[
                        _buildVehicleTypeChoice(),
                        const SizedBox(height: 20),
                        if (_vehicleType == VehicleRequestType.taxi) ...[
                          _buildTaxiFields(),
                          const SizedBox(height: 24),
                        ] else if (_vehicleType ==
                            VehicleRequestType.rental) ...[
                          _buildRentalFields(),
                          const SizedBox(height: 24),
                        ],
                      ],
                      _buildDetails(),
                      const SizedBox(height: 24),
                      _buildScheduledTime(),
                      const SizedBox(height: 30),
                      _buildConfirmButton(),
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

  Widget _buildDetails() {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [AppTheme.primaryBlue, AppTheme.primaryDark],
        ),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppTheme.accentGold, width: 1.5),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            AppLocalizations.of(context).requestDetails,
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.bold,
              color: AppTheme.accentGold,
            ),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _detailsController,
            style: const TextStyle(color: Colors.white),
            maxLines: 4,
            decoration: InputDecoration(
              hintText: AppLocalizations.of(context).describeRequest,
              hintStyle: TextStyle(
                color: AppTheme.textGray.withValues(alpha: 0.6),
              ),
              filled: true,
              fillColor: AppTheme.primaryBlue.withValues(alpha: 0.5),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: BorderSide(
                  color: AppTheme.accentGold.withValues(alpha: 0.3),
                ),
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: BorderSide(
                  color: AppTheme.accentGold.withValues(alpha: 0.3),
                ),
              ),
              focusedBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: const BorderSide(color: AppTheme.accentGold),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildScheduledTime() {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [AppTheme.primaryBlue, AppTheme.primaryDark],
        ),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppTheme.accentGold, width: 1.5),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            AppLocalizations.of(context).preferredTimeOptional,
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.bold,
              color: AppTheme.accentGold,
            ),
          ),
          const SizedBox(height: 12),
          InkWell(
            onTap: () async {
              final DateTime? pickedDate = await showDatePicker(
                context: context,
                initialDate: _scheduledTime ?? DateTime.now(),
                firstDate: DateTime.now(),
                lastDate: DateTime.now().add(const Duration(days: 30)),
                builder: (context, child) {
                  return Theme(
                    data: ThemeData.dark().copyWith(
                      colorScheme: const ColorScheme.dark(
                        primary: AppTheme.accentGold,
                        onPrimary: AppTheme.primaryDark,
                        surface: AppTheme.primaryBlue,
                        onSurface: Colors.white,
                      ),
                    ),
                    child: child!,
                  );
                },
              );

              if (pickedDate != null && mounted) {
                final TimeOfDay? pickedTime = await showTimePicker(
                  context: context,
                  initialTime: TimeOfDay.now(),
                  builder: (context, child) {
                    return Theme(
                      data: ThemeData.dark().copyWith(
                        colorScheme: const ColorScheme.dark(
                          primary: AppTheme.accentGold,
                          onPrimary: AppTheme.primaryDark,
                          surface: AppTheme.primaryBlue,
                          onSurface: Colors.white,
                        ),
                      ),
                      child: child!,
                    );
                  },
                );

                if (pickedTime != null) {
                  setState(() {
                    _scheduledTime = DateTime(
                      pickedDate.year,
                      pickedDate.month,
                      pickedDate.day,
                      pickedTime.hour,
                      pickedTime.minute,
                    );
                  });
                }
              }
            },
            child: Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: AppTheme.primaryBlue.withValues(alpha: 0.5),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(
                  color: AppTheme.accentGold.withValues(alpha: 0.3),
                ),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    _scheduledTime != null
                        ? DateFormat(
                            'dd/MM/yyyy HH:mm',
                            'fr_FR',
                          ).format(_scheduledTime!)
                        : AppLocalizations.of(context).selectDateAndTime,
                    style: TextStyle(
                      fontSize: 15,
                      color: _scheduledTime != null
                          ? Colors.white
                          : AppTheme.textGray,
                    ),
                  ),
                  const Icon(
                    Icons.schedule,
                    color: AppTheme.accentGold,
                    size: 20,
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCanReserveBanner() {
    return Container(
      margin: const EdgeInsets.only(bottom: 24),
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [AppTheme.primaryBlue, AppTheme.primaryDark],
        ),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppTheme.accentGold, width: 1.5),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Icon(
                Icons.info_outline,
                color: AppTheme.accentGold,
                size: 20,
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Text(
                  'Les réservations sont réservées aux clients avec un séjour valide. Entrez votre code client ci-dessous (reçu à l\'enregistrement).',
                  style: const TextStyle(
                    color: AppTheme.textGray,
                    fontSize: 14,
                    height: 1.4,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          const Divider(color: AppTheme.textGray, height: 1),
          const SizedBox(height: 16),
          TextField(
            controller: _clientCodeController,
            style: const TextStyle(color: Colors.white, fontSize: 16),
            decoration: InputDecoration(
              hintText: AppLocalizations.of(context).clientCodeHint,
              hintStyle: TextStyle(
                color: AppTheme.textGray.withValues(alpha: 0.8),
              ),
              filled: true,
              fillColor: AppTheme.primaryBlue.withValues(alpha: 0.5),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: BorderSide(
                  color: AppTheme.accentGold.withValues(alpha: 0.3),
                ),
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: BorderSide(
                  color: AppTheme.accentGold.withValues(alpha: 0.3),
                ),
              ),
              focusedBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: const BorderSide(color: AppTheme.accentGold),
              ),
              prefixIcon: const Icon(
                Icons.person_outline,
                color: AppTheme.accentGold,
                size: 22,
              ),
            ),
            onChanged: (_) => setState(() {}),
          ),
        ],
      ),
    );
  }

  Widget _buildConfirmButton() {
    final hasCode = _clientCodeController.text.trim().isNotEmpty;
    final canSubmit = hasCode;
    return AnimatedButton(
      text: AppLocalizations.of(context).sendRequest,
      onPressed: canSubmit ? _handleConfirmRequest : null,
      width: double.infinity,
      height: 56,
      backgroundColor: AppTheme.accentGold,
      textColor: AppTheme.primaryDark,
    );
  }

  Map<String, dynamic>? _buildVehicleMetadata() {
    if (!_isVehicleService || _vehicleType == null) return null;
    if (_vehicleType == VehicleRequestType.taxi) {
      final dest = _destinationController.text.trim();
      if (dest.isEmpty) {
        _showSnack('Indiquez l\'adresse de destination.');
        return null;
      }
      final meta = <String, dynamic>{
        'vehicle_request_type': 'taxi',
        'destination_address': dest,
      };
      final pickup = _pickupController.text.trim();
      if (pickup.isNotEmpty) meta['pickup_address'] = pickup;
      if (_pickupLat != null) meta['pickup_lat'] = _pickupLat;
      if (_pickupLng != null) meta['pickup_lng'] = _pickupLng;
      final distStr = _distanceController.text.trim();
      if (distStr.isNotEmpty) {
        final d = double.tryParse(distStr.replaceAll(',', '.'));
        if (d != null && d > 0) meta['distance_km'] = d;
      }
      return meta;
    }
    if (_vehicleType == VehicleRequestType.rental) {
      if (_selectedVehicle == null) {
        _showSnack('Choisissez un véhicule dans la liste.');
        return null;
      }
      final meta = <String, dynamic>{
        'vehicle_request_type': 'rental',
        'vehicle_id': _selectedVehicle!.id,
        'vehicle_type': _selectedVehicle!.vehicleType,
        'number_of_seats': _selectedVehicle!.numberOfSeats,
      };
      final days = int.tryParse(_rentalDaysController.text.trim());
      if (days != null && days > 0) meta['rental_days'] = days;
      final hours = int.tryParse(_rentalDurationController.text.trim());
      if (hours != null && hours > 0) meta['rental_duration_hours'] = hours;
      return meta;
    }
    return null;
  }

  Future<void> _handleConfirmRequest() async {
    final detailsText = _detailsController.text.trim();
    final hasDetails = detailsText.isNotEmpty;
    if (_isVehicleService && _vehicleType == null && !hasDetails) {
      _showSnack('Choisissez Taxi ou Location, ou décrivez votre demande.');
      return;
    }
    final metadata = _buildVehicleMetadata();
    if (_isVehicleService && _vehicleType != null && metadata == null) return;
    if (!_isVehicleService && !hasDetails) {
      _showSnack(AppLocalizations.of(context).describeRequest);
      return;
    }

    final auth = context.read<AuthProvider>();
    final clientCode = _clientCodeController.text.trim();
    final relyingOnCanReserve =
        clientCode.isEmpty && (auth.user?.canReserve == true);

    if (relyingOnCanReserve) {
      await auth.loadUser();
      if (!mounted) return;
      if (auth.user?.canReserve != true) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text(
                'Votre séjour n\'est plus actif. Entrez votre code client pour effectuer la demande.',
              ),
              backgroundColor: Colors.orange,
              duration: Duration(seconds: 4),
            ),
          );
        }
        return;
      }
    }

    try {
      showDialog(
        context: context,
        barrierDismissible: false,
        builder: (dialogContext) => const Center(
          child: CircularProgressIndicator(
            valueColor: AlwaysStoppedAnimation<Color>(AppTheme.accentGold),
          ),
        ),
      );

      final description = detailsText.isEmpty ? null : detailsText;

      await context.read<PalaceProvider>().createPalaceRequest(
        serviceId: widget.service.id,
        details: description,
        scheduledTime: _scheduledTime,
        metadata: metadata,
        clientCode: clientCode.isNotEmpty ? clientCode : null,
      );

      if (!mounted) return;
      Navigator.pop(context);

      if (mounted) {
        showDialog(
          context: context,
          builder: (dialogContext) => AlertDialog(
            backgroundColor: AppTheme.primaryBlue,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(16),
              side: const BorderSide(color: AppTheme.accentGold, width: 2),
            ),
            title: Row(
              children: [
                const Icon(Icons.check_circle, color: Colors.green, size: 32),
                const SizedBox(width: 12),
                Text(
                  AppLocalizations.of(context).requestSent,
                  style: const TextStyle(color: Colors.white, fontSize: 18),
                ),
              ],
            ),
            content: Text(
              AppLocalizations.of(context).requestSentMessage,
              style: const TextStyle(color: AppTheme.textGray),
            ),
            actions: [
              TextButton(
                onPressed: () {
                  Navigator.pop(dialogContext);
                  Navigator.pop(context);
                  Navigator.pop(context);
                },
                child: Text(
                  AppLocalizations.of(context).ok,
                  style: TextStyle(
                    color: AppTheme.accentGold,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
            ],
          ),
        );
      }
    } catch (e) {
      if (!mounted) return;
      Navigator.pop(context);

      if (mounted) {
        final message = e.toString().replaceFirst('Exception: ', '');
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              '${AppLocalizations.of(context).errorPrefix}$message',
            ),
            backgroundColor: Colors.red,
            duration: const Duration(seconds: 4),
          ),
        );
      }
    }
  }
}
