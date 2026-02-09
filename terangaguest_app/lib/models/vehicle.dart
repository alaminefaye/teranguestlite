class Vehicle {
  final int id;
  final String name;
  final String vehicleType;
  final String vehicleTypeLabel;
  final int numberOfSeats;
  final String? image;
  final double? pricePerDay;
  final double? priceHalfDay;

  Vehicle({
    required this.id,
    required this.name,
    required this.vehicleType,
    required this.vehicleTypeLabel,
    required this.numberOfSeats,
    this.image,
    this.pricePerDay,
    this.priceHalfDay,
  });

  factory Vehicle.fromJson(Map<String, dynamic> json) {
    return Vehicle(
      id: (json['id'] as num).toInt(),
      name: json['name'] as String? ?? '',
      vehicleType: json['vehicle_type'] as String? ?? 'other',
      vehicleTypeLabel: json['vehicle_type_label'] as String? ?? 'Autre',
      numberOfSeats: (json['number_of_seats'] as num?)?.toInt() ?? 0,
      image: json['image'] as String?,
      pricePerDay: json['price_per_day'] != null ? (json['price_per_day'] as num).toDouble() : null,
      priceHalfDay: json['price_half_day'] != null ? (json['price_half_day'] as num).toDouble() : null,
    );
  }

  /// Même logique que le backend : demi-journée si durée <= 5h et pas de jours, sinon prix/jour × jours.
  double? estimatePrice({int? rentalDays, int? rentalDurationHours}) {
    final useHalfDay = rentalDurationHours != null && rentalDurationHours <= 5 &&
        (rentalDays == null || rentalDays < 1);
    if (useHalfDay && priceHalfDay != null) return priceHalfDay;
    if (pricePerDay == null) return null;
    final days = (rentalDays != null && rentalDays >= 1) ? rentalDays : 1;
    return pricePerDay! * days;
  }

  /// Texte affiché pour le prix journée (depuis API ou calculé).
  String get displayPricePerDay => pricePerDay != null
      ? '${pricePerDay!.toInt()} FCFA'
      : 'Sur demande';
}
