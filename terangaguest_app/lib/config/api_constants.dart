/// Configuration de l'API Teranga Guest
class ApiConstants {
  // Base URL
  static const String baseUrl = 'http://localhost:8000/api';
  
  // Pour tester sur device physique, utiliser l'IP de votre machine
  // static const String baseUrl = 'http://192.168.1.XXX:8000/api';
  
  // Timeout
  static const Duration connectionTimeout = Duration(seconds: 30);
  static const Duration receiveTimeout = Duration(seconds: 30);
  
  // Headers
  static const Map<String, String> headers = {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  };
  
  // Endpoints - Auth
  static const String login = '/auth/login';
  static const String logout = '/auth/logout';
  static const String user = '/user';
  static const String changePassword = '/auth/change-password';
  
  // Endpoints - FCM
  static const String fcmToken = '/fcm-token';
  
  // Endpoints - Room Service
  static const String roomServiceCategories = '/room-service/categories';
  static const String roomServiceItems = '/room-service/items';
  static String roomServiceItemDetail(int id) => '/room-service/items/$id';
  static const String roomServiceCheckout = '/room-service/checkout';
  
  // Endpoints - Orders
  static const String orders = '/orders';
  static String orderDetail(int id) => '/orders/$id';
  static String orderReorder(int id) => '/orders/$id/reorder';
  
  // Endpoints - Restaurants
  static const String restaurants = '/restaurants';
  static String restaurantDetail(int id) => '/restaurants/$id';
  static String restaurantReserve(int id) => '/restaurants/$id/reserve';
  static const String myRestaurantReservations = '/my-restaurant-reservations';
  
  // Endpoints - Spa
  static const String spaServices = '/spa-services';
  static String spaServiceDetail(int id) => '/spa-services/$id';
  static String spaServiceReserve(int id) => '/spa-services/$id/reserve';
  static const String mySpaReservations = '/my-spa-reservations';
  
  // Endpoints - Excursions
  static const String excursions = '/excursions';
  static String excursionDetail(int id) => '/excursions/$id';
  static String excursionBook(int id) => '/excursions/$id/book';
  static const String myExcursionBookings = '/my-excursion-bookings';
  
  // Endpoints - Laundry
  static const String laundryServices = '/laundry/services';
  static const String laundryRequest = '/laundry/request';
  static const String myLaundryRequests = '/my-laundry-requests';
  
  // Endpoints - Palace Services
  static const String palaceServices = '/palace-services';
  static String palaceServiceDetail(int id) => '/palace-services/$id';
  static String palaceServiceRequest(int id) => '/palace-services/$id/request';
  static const String myPalaceRequests = '/my-palace-requests';

  /// Préfixe d'exception quand le code client est invalide ou le séjour expiré (pour redemander le code).
  static const String errorInvalidClientCode = 'INVALID_CLIENT_CODE';
}
