/// Session client (tablette en chambre) après validation du code.
class GuestSession {
  final int guestId;
  final String guestName;
  final String? guestPhone;
  final String? guestEmail;
  final int roomId;
  final String roomNumber;
  final int reservationId;
  final String reservationNumber;

  const GuestSession({
    required this.guestId,
    required this.guestName,
    this.guestPhone,
    this.guestEmail,
    required this.roomId,
    required this.roomNumber,
    required this.reservationId,
    required this.reservationNumber,
  });

  factory GuestSession.fromJson(Map<String, dynamic> json) {
    return GuestSession(
      guestId: json['guest_id'] as int,
      guestName: json['guest_name'] as String? ?? '',
      guestPhone: json['guest_phone'] as String?,
      guestEmail: json['guest_email'] as String?,
      roomId: json['room_id'] as int,
      roomNumber: json['room_number'] as String? ?? '',
      reservationId: json['reservation_id'] as int,
      reservationNumber: json['reservation_number'] as String? ?? '',
    );
  }

  Map<String, dynamic> toJson() => {
        'guest_id': guestId,
        'guest_name': guestName,
        'guest_phone': guestPhone,
        'guest_email': guestEmail,
        'room_id': roomId,
        'room_number': roomNumber,
        'reservation_id': reservationId,
        'reservation_number': reservationNumber,
      };
}
