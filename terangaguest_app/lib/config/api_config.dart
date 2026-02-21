class ApiConfig {
  // Base URL de l'API
  // Production
  static const String baseUrl = 'https://teranguest.com/api';

  /// Base URL du site (sans /api) pour les assets stockés : storage, logos, etc.
  static String get storageBaseUrl {
    final u = baseUrl;
    if (u.endsWith('/api')) return u.substring(0, u.length - 4);
    return u;
  }

  /// URL complète pour un chemin de fichier storage (ex. enterprises/logo.png).
  static String storageUrl(String path) {
    final p = path.trim();
    if (p.isEmpty) return '';
    final normalized = p.startsWith('/') ? p.substring(1) : p;
    return '$storageBaseUrl/storage/$normalized';
  }

  // Développement (localhost)
  // static const String baseUrl = 'http://localhost:8000/api';
  // Alors storageBaseUrl = 'http://localhost:8000'

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
  static const String restaurantReservations = '/restaurant-reservations';

  // Spa
  static const String spaServices = '/spa-services';
  static const String mySpaReservations = '/my-spa-reservations';
  static const String spaReservations = '/spa-reservations';

  // Excursions
  static const String excursions = '/excursions';
  static const String myExcursionBookings = '/my-excursion-bookings';

  // Laundry
  static const String laundryServices = '/laundry/services';
  static const String laundryRequest = '/laundry/request';
  static const String myLaundryRequests = '/my-laundry-requests';
  static const String laundryRequests = '/laundry-requests';

  // Amenities & Conciergerie (dynamique depuis l'admin)
  static const String amenityCategories = '/amenity-categories';

  // Bien-être, Sport & Loisirs (dynamique)
  static const String leisureCategories = '/leisure-categories';

  // Palace Services
  static const String palaceServices = '/palace-services';
  static const String myPalaceRequests = '/my-palace-requests';

  // Admin
  static const String adminSummary = '/admin-summary';

  // FCM Token
  static const String fcmToken = '/fcm-token';

  // Tablette en chambre - code client (sans auth)
  static const String tabletValidateCode = '/tablet/validate-code';
  static const String tabletValidateSession = '/tablet/validate-session';
  static const String tabletCheckout = '/tablet/checkout';
}
