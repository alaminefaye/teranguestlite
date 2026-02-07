class ApiConfig {
  // Base URL de l'API
  // Production
  static const String baseUrl = 'https://teranguest.universaltechnologiesafrica.com/api';
  
  // Développement (localhost)
  // static const String baseUrl = 'http://localhost:8000/api';
  
  // Timeout en millisecondes
  static const int connectTimeout = 30000;
  static const int receiveTimeout = 30000;
  
  // Endpoints
  static const String login = '/auth/login';
  static const String logout = '/auth/logout';
  static const String user = '/user';
  static const String changePassword = '/auth/change-password';
  
  // Room Service
  static const String roomServiceCategories = '/room-service/categories';
  static const String roomServiceItems = '/room-service/items';
  static const String roomServiceCheckout = '/room-service/checkout';
  
  // Orders
  static const String orders = '/orders';
  
  // Restaurants
  static const String restaurants = '/restaurants';
  static const String myRestaurantReservations = '/my-restaurant-reservations';
  
  // Spa
  static const String spaServices = '/spa-services';
  static const String mySpaReservations = '/my-spa-reservations';
  
  // Excursions
  static const String excursions = '/excursions';
  static const String myExcursionBookings = '/my-excursion-bookings';
  
  // Laundry
  static const String laundryServices = '/laundry/services';
  static const String laundryRequest = '/laundry/request';
  static const String myLaundryRequests = '/my-laundry-requests';
  
  // Palace Services
  static const String palaceServices = '/palace-services';
  static const String myPalaceRequests = '/my-palace-requests';
  
  // FCM Token
  static const String fcmToken = '/fcm-token';
  
  // Tablette en chambre - code client (sans auth)
  static const String tabletValidateCode = '/tablet/validate-code';
  static const String tabletValidateSession = '/tablet/validate-session';
  static const String tabletCheckout = '/tablet/checkout';
}
