class Vehicle {
  final int id;
  final String name;
  final String vehicleType;
  final String vehicleTypeLabel;
  final int numberOfSeats;
  final String? image;

  Vehicle({
    required this.id,
    required this.name,
    required this.vehicleType,
    required this.vehicleTypeLabel,
    required this.numberOfSeats,
    this.image,
  });

  factory Vehicle.fromJson(Map<String, dynamic> json) {
    return Vehicle(
      id: (json['id'] as num).toInt(),
      name: json['name'] as String? ?? '',
      vehicleType: json['vehicle_type'] as String? ?? 'other',
      vehicleTypeLabel: json['vehicle_type_label'] as String? ?? 'Autre',
      numberOfSeats: (json['number_of_seats'] as num?)?.toInt() ?? 0,
      image: json['image'] as String?,
    );
  }
}
