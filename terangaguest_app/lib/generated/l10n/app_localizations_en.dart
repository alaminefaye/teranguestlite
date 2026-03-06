// ignore: unused_import
import 'package:intl/intl.dart' as intl;
import 'app_localizations.dart';

// ignore_for_file: type=lint

/// The translations for English (`en`).
class AppLocalizationsEn extends AppLocalizations {
  AppLocalizationsEn([String locale = 'en']) : super(locale);

  @override
  String get appTitle => 'Teranga Guest';

  @override
  String get login => 'Login';

  @override
  String get email => 'Email';

  @override
  String get password => 'Password';

  @override
  String get rememberMe => 'Remember me';

  @override
  String get loginButton => 'Sign in';

  @override
  String get loginError => 'Login error';

  @override
  String get emailRequired => 'Please enter your email';

  @override
  String get emailInvalid => 'Invalid email';

  @override
  String get passwordRequired => 'Please enter your password';

  @override
  String get passwordTooShort => 'Password too short (min. 6 characters)';

  @override
  String get myProfile => 'My Profile';

  @override
  String get myHistories => 'My History';

  @override
  String get myFavorites => 'My Favorites';

  @override
  String get myOrders => 'My Orders';

  @override
  String get myRestaurantReservations => 'My Restaurant Reservations';

  @override
  String get mySpaReservations => 'My Spa Reservations';

  @override
  String get myExcursions => 'My Excursions';

  @override
  String get myLaundryRequests => 'My Laundry Requests';

  @override
  String get myPalaceRequests => 'My Palace Requests';

  @override
  String get settings => 'Settings';

  @override
  String get changePassword => 'Change password';

  @override
  String get about => 'About';

  @override
  String get contactSupport => 'Contact support';

  @override
  String get logout => 'Log out';

  @override
  String get logoutConfirm => 'Are you sure you want to log out?';

  @override
  String get cancel => 'Cancel';

  @override
  String get version => 'Version';

  @override
  String get preferences => 'Preferences';

  @override
  String get notifications => 'Notifications';

  @override
  String get notificationsOn => 'Notifications enabled';

  @override
  String get notificationsOff => 'Notifications disabled';

  @override
  String get application => 'Application';

  @override
  String get language => 'Language';

  @override
  String get french => 'Français';

  @override
  String get english => 'English';

  @override
  String get welcomeTitle => 'Welcome to King Fahd Palace Hotel';

  @override
  String welcomeToEnterprise(String enterpriseName) {
    return 'Welcome to $enterpriseName';
  }

  @override
  String get welcomeSubtitle => 'Your digital assistant is at your service';

  @override
  String get roomService => 'Room Service';

  @override
  String get restaurantsBars => 'Restaurants & Bars';

  @override
  String get spaWellness => 'Spa & Wellness';

  @override
  String get wellnessSportLeisure => 'WELLNESS, SPORT & LEISURE';

  @override
  String get wellnessSportLeisureSubtitle => 'Spa, Golf, Tennis, Fitness';

  @override
  String get golfTitle => 'Golf';

  @override
  String get golfSubtitle => 'Tee-time booking and equipment rental';

  @override
  String get tennisTitle => 'Tennis';

  @override
  String get tennisSubtitle => 'Court booking and equipment rental';

  @override
  String get golfTennisTitle => 'Golf & Tennis';

  @override
  String get golfTennisSubtitle => 'Tee-time, courts and equipment rental';

  @override
  String get golfTennisTeetime => 'Tee-time booking';

  @override
  String get golfTennisCourt => 'Tennis court';

  @override
  String get golfTennisEquipment => 'Equipment rental';

  @override
  String get sportFitnessTitle => 'Sport & Fitness';

  @override
  String get sportFitnessSubtitle => 'Gym hours and personal coach booking';

  @override
  String get sportFitnessGymHours => 'Gym opening hours';

  @override
  String get sportFitnessBookCoach => 'Book a personal coach';

  @override
  String get gymHoursDefault => 'Contact reception for opening hours.';

  @override
  String get palaceServices => 'Other services';

  @override
  String get explorationMobility => 'Exploration & mobility';

  @override
  String get explorationMobilitySubtitle =>
      'Vehicle rental, discovery, guided tours and transfers';

  @override
  String get vehicleRental => 'Vehicle Rental';

  @override
  String get vehicleRentalDesc =>
      'Interactive catalog of vehicles (sedans, 4x4, city cars) with direct booking and comfort options.';

  @override
  String get sitesTouristiques => 'Discovery & Tourist Sites';

  @override
  String get sitesTouristiquesDesc =>
      'Must-see places: Lac Rose, Gorée Island, Plateau… Photos and detailed descriptions.';

  @override
  String get guidedTours => 'Personalized Guided Tours';

  @override
  String get guidedToursDesc =>
      'Book certified guides for cultural, gastronomic or historical tours.';

  @override
  String get transfersVtc => 'Transfers & Chauffeur';

  @override
  String get transfersVtcDesc =>
      'Airport shuttles or private drivers for secure trips.';

  @override
  String get excursions => 'Excursions';

  @override
  String get laundry => 'Laundry';

  @override
  String get concierge => 'Concierge';

  @override
  String get callCenter => 'Call Center';

  @override
  String get hotelInfosSecurity => 'Hotel infos & security';

  @override
  String get hotelInfosSecuritySubtitle =>
      'Welcome booklet, emergency assistance and chatbot';

  @override
  String get hotelInfos => 'Hotel Infos';

  @override
  String get hotelInfosDesc => 'Wi-Fi, maps, house rules and practical info';

  @override
  String get assistanceEmergency => 'Assistance & Emergency';

  @override
  String get assistanceEmergencyDesc =>
      'Request a doctor or report a security emergency (room identified)';

  @override
  String get chatbotMultilingual => 'Multilingual AI Chatbot';

  @override
  String get chatbotDesc => '24/7 digital assistant';

  @override
  String get gallery => 'Gallery';

  @override
  String get galleryDesc => 'Establishment photos and albums';

  @override
  String get ourEstablishments => 'Our establishments';

  @override
  String get ourEstablishmentsDesc => 'Other group locations in the country';

  @override
  String get wifiCode => 'Wi-Fi network';

  @override
  String get wifiPassword => 'Wi-Fi password';

  @override
  String get houseRules => 'House rules';

  @override
  String get practicalInfo => 'Practical information';

  @override
  String get requestDoctor => 'Request a doctor';

  @override
  String get reportSecurityEmergency => 'Report security emergency';

  @override
  String roomLabel(String room) {
    return 'Room: $room';
  }

  @override
  String get emergencyRequestSent => 'Request sent. The team will contact you.';

  @override
  String get noActiveStayForEmergency =>
      'An active stay is required. Sign in with the room account or contact reception.';

  @override
  String get assistanceDoctorNotConfigured =>
      'Doctor assistance service is not configured for this property.';

  @override
  String get assistanceSecurityNotConfigured =>
      'Security emergency service is not configured for this property.';

  @override
  String confirmEmergencyAction(String action) {
    return 'Do you want to confirm: $action?';
  }

  @override
  String get deleteConversationConfirm => 'Delete this conversation?';

  @override
  String get deleteConversation => 'Delete conversation';

  @override
  String get messageDeleted => 'Message deleted';

  @override
  String get reply => 'Reply';

  @override
  String get deleteMessage => 'Delete message';

  @override
  String get chatbotComingSoon => 'Coming soon';

  @override
  String get chatbotComingSoonHint =>
      'The multilingual chatbot will be available soon.';

  @override
  String get servicesChambreLogistique => 'Room service';

  @override
  String get roomServiceRestauration => 'Room Service & Dining';

  @override
  String get roomServiceRestaurationDesc =>
      'High-definition digital menu to order meals and drinks with real-time preparation tracking.';

  @override
  String get laundryDesc =>
      'Interactive price list and immediate laundry pickup request.';

  @override
  String get amenitiesConcierge => 'Amenities & Concierge';

  @override
  String get amenitiesConciergeDesc =>
      'Simplified request for toiletries, extra pillows, shaving kit or any other service without calling.';

  @override
  String get minibarIntelligent => 'Smart Mini-bar';

  @override
  String get minibarIntelligentDesc =>
      'Digital inventory of products and simplified consumption declaration.';

  @override
  String get comingSoon => 'Coming soon';

  @override
  String get amenityToiletries => 'Toiletries';

  @override
  String get amenityPillows => 'Extra pillows';

  @override
  String get amenityShavingKit => 'Shaving kit';

  @override
  String get amenityOther => 'Other request';

  @override
  String get amenityDetailsHint => 'Specify if needed (optional)';

  @override
  String get amenitySelectQuantities => 'Select items and quantities';

  @override
  String get amenityPillowCount => 'Number of pillows';

  @override
  String get amenityOtherDetailsHint => 'Describe your request (optional)';

  @override
  String get amenityItemSoap => 'Soap';

  @override
  String get amenityItemShampoo => 'Shampoo';

  @override
  String get amenityItemToothpaste => 'Toothpaste';

  @override
  String get amenityItemToothbrush => 'Toothbrush';

  @override
  String get amenityItemComb => 'Comb';

  @override
  String get amenityItemTowels => 'Towels';

  @override
  String get amenityItemPillow => 'Extra pillow';

  @override
  String get amenityItemRazor => 'Razor';

  @override
  String get amenityItemShavingFoam => 'Shaving foam';

  @override
  String get amenityItemAfterShave => 'After-shave';

  @override
  String get amenityItemBlades => 'Razor blades';

  @override
  String get back => 'Back';

  @override
  String get retry => 'Retry';

  @override
  String get error => 'Error';

  @override
  String get errorHint => 'Check your connection and try again.';

  @override
  String get close => 'Close';

  @override
  String get ok => 'OK';

  @override
  String get save => 'Save';

  @override
  String get noFavorites => 'No favorites';

  @override
  String get noFavoritesHint =>
      'Add items, restaurants, treatments or excursions to favorites from their pages.';

  @override
  String get contactSupportTitle => 'Contact support';

  @override
  String get chooseContact => 'Choose a contact method:';

  @override
  String get aboutDescription =>
      'Welcome and services app for King Fahd Palace Hotel guests.';

  @override
  String get hotelName => 'KING FAHD PALACE HOTEL';

  @override
  String get noUser => 'No user logged in';

  @override
  String get addToCart => 'Add to cart';

  @override
  String get viewCart => 'View cart';

  @override
  String get cart => 'Cart';

  @override
  String get emptyCart => 'Your cart is empty';

  @override
  String get emptyCartHint => 'Add items to place an order.';

  @override
  String get browseMenu => 'Browse menu';

  @override
  String get clearCartConfirm => 'Are you sure you want to clear your cart?';

  @override
  String get clear => 'Clear';

  @override
  String get orderSuccess => 'Order placed';

  @override
  String get orderSuccessHint => 'You can track your order in My Orders.';

  @override
  String get home => 'Home';

  @override
  String get myBookings => 'My bookings';

  @override
  String get excursionNotFound => 'Excursion not found';

  @override
  String get excursionNotFoundHint =>
      'This excursion does not exist or is no longer available.';

  @override
  String get serviceNotFound => 'Service not found';

  @override
  String get serviceNotFoundHint =>
      'This service does not exist or is no longer available.';

  @override
  String get restaurantNotFound => 'Restaurant not found';

  @override
  String get restaurantNotFoundHint =>
      'This restaurant does not exist or is no longer available.';

  @override
  String get orderNotFound => 'Order not found';

  @override
  String get orderNotFoundHint =>
      'This order does not exist or has been removed.';

  @override
  String get noLaundryService => 'No laundry service available';

  @override
  String get noLaundryServiceHint => 'Cleaning services will be listed here.';

  @override
  String get noSpaService => 'No spa service available';

  @override
  String get noSpaServiceInCategory => 'No service in this category';

  @override
  String get noSpaServiceHint =>
      'Treatments and massages will be offered here.';

  @override
  String get noPalaceService => 'No Palace service available';

  @override
  String get noPalaceServiceHint => 'Premium services will be displayed here.';

  @override
  String get noExcursionAvailable => 'No excursion available';

  @override
  String get noExcursionAvailableHint =>
      'Activities and outings will be offered here.';

  @override
  String get noOrder => 'No orders';

  @override
  String noOrderForStatus(String status) {
    return 'No orders $status';
  }

  @override
  String get noOrderSubtitle => 'Your room service orders will appear here.';

  @override
  String get orderStatusPending => 'pending';

  @override
  String get orderStatusConfirmed => 'confirmed';

  @override
  String get orderStatusPreparing => 'preparing';

  @override
  String get orderStatusDelivering => 'delivering';

  @override
  String get orderStatusDelivered => 'delivered';

  @override
  String get noRestaurantAvailable => 'No restaurant available';

  @override
  String noRestaurantForType(String type) {
    return 'No $type available';
  }

  @override
  String get noRestaurantSubtitle =>
      'Restaurants and bars will be listed here.';

  @override
  String get typeRestaurant => 'restaurant';

  @override
  String get typeBar => 'bar';

  @override
  String get typeCafe => 'café';

  @override
  String get typeLounge => 'lounge';

  @override
  String get noItemAvailable => 'No item available';

  @override
  String get noSearchResult => 'No results found';

  @override
  String get noItemSubtitle => 'Items in this category will be listed here.';

  @override
  String get tryAnotherSearch => 'Try another search term.';

  @override
  String get noCategoryAvailable => 'No category available';

  @override
  String get noCategoryHint => 'The room service menu will be available here.';

  @override
  String get noPalaceRequest => 'No Palace request';

  @override
  String get noPalaceRequestHint => 'Submit a request from Palace services.';

  @override
  String get noLaundryRequest => 'No laundry request';

  @override
  String get noLaundryRequestHint =>
      'Submit a request from the Laundry service.';

  @override
  String get noExcursionBooked => 'No excursion booked';

  @override
  String get noExcursionBookedHint =>
      'Book an excursion from the activities list.';

  @override
  String get noSpaReservation => 'No spa reservation';

  @override
  String get noSpaReservationHint =>
      'Book a treatment from the spa services list.';

  @override
  String get noRestaurantReservation => 'No restaurant reservation';

  @override
  String get noRestaurantReservationHint =>
      'Book a table from the restaurants list.';

  @override
  String get itemNotFound => 'Item not found';

  @override
  String get filterAll => 'All';

  @override
  String get filterPending => 'Pending';

  @override
  String get filterConfirmed => 'Confirmed';

  @override
  String get filterPreparing => 'Preparing';

  @override
  String get filterDelivering => 'Delivering';

  @override
  String get filterDelivered => 'Delivered';

  @override
  String get filterAllTypes => 'All';

  @override
  String get filterRestaurants => 'Restaurants';

  @override
  String get filterBars => 'Bars';

  @override
  String get filterCafes => 'Cafés';

  @override
  String get filterLounges => 'Lounges';

  @override
  String get ordersHistorySubtitle => 'History and tracking';

  @override
  String get arabic => 'العربية';

  @override
  String get spanish => 'Español';

  @override
  String get discoverRestaurants => 'Discover our venues';

  @override
  String get reservationsConfirmed => 'Confirmed reservations';

  @override
  String get myRequests => 'My Requests';

  @override
  String requestNumber(int id) {
    return 'Request #$id';
  }

  @override
  String get palaceServicesSubtitle => 'Premium services and concierge';

  @override
  String get spaWellnessSubtitle => 'Wellness & Relaxation';

  @override
  String get myReservations => 'My Reservations';

  @override
  String get discoverRegion => 'Discover our region';

  @override
  String get spaSubtitle => 'Rest and relaxation';

  @override
  String get chooseCategory => 'Choose a category';

  @override
  String get orderDetailTitle => 'Order Detail';

  @override
  String get orderTrackingSubtitle => 'Track your order';

  @override
  String adultPrice(String price) {
    return 'Adult: $price';
  }

  @override
  String childPrice(String price) {
    return 'Child: $price';
  }

  @override
  String get book => 'Book';

  @override
  String get unavailable => 'Unavailable';

  @override
  String get spaServiceDefaultName => 'Spa Service';

  @override
  String capacityPeople(int count) {
    return 'Capacity: $count people';
  }

  @override
  String get openingHours => 'Opening hours';

  @override
  String get bookTable => 'Book a table';

  @override
  String get closed => 'Closed';

  @override
  String get search => 'Search...';

  @override
  String get reviewOrder => 'Review your order';

  @override
  String get specialInstructions => 'Special instructions';

  @override
  String get specialInstructionsHint =>
      'Allergies, preferences, delivery instructions...';

  @override
  String get description => 'Description';

  @override
  String get specialInstructionsExample => 'E.g. No onions, well done...';

  @override
  String get viewCartCaps => 'VIEW CART';

  @override
  String get passwordChangedSuccess => 'Password changed successfully';

  @override
  String get currentPassword => 'Current password';

  @override
  String get newPassword => 'New password';

  @override
  String get confirmPassword => 'Confirm new password';

  @override
  String get fieldRequired => 'Required field';

  @override
  String get minChars => 'Minimum 8 characters';

  @override
  String get needUpperCase => 'Must contain an uppercase letter';

  @override
  String get needDigit => 'Must contain a number';

  @override
  String get passwordsDoNotMatch => 'Passwords do not match';

  @override
  String get passwordRulesHint =>
      'The new password must contain at least 8 characters, one uppercase letter and one number.';

  @override
  String get requestDetails => 'Request details';

  @override
  String get describeRequest => 'Describe your request in detail...';

  @override
  String get preferredTimeOptional => 'Preferred time (optional)';

  @override
  String get selectDateAndTime => 'Select date and time';

  @override
  String get sendRequest => 'Send request';

  @override
  String get requestReservationHint =>
      'Enter date, time and details in the form.';

  @override
  String get requestSent => 'Request sent!';

  @override
  String get requestSentMessage => 'Your service request has been recorded.';

  @override
  String get confirmRequest => 'Confirm request';

  @override
  String get selectedItems => 'Selected items';

  @override
  String get total => 'Total';

  @override
  String get specialInstructionsOptional => 'Special instructions (optional)';

  @override
  String get laundryInstructionsExample =>
      'E.g. Fragrance-free detergent, light ironing...';

  @override
  String get date => 'Date';

  @override
  String get selectDate => 'Select a date';

  @override
  String get participants => 'Participants';

  @override
  String get adults => 'Adults';

  @override
  String get children => 'Children';

  @override
  String get specialRequestsOptional => 'Special requests (optional)';

  @override
  String get summary => 'Summary';

  @override
  String get confirmReservation => 'Confirm reservation';

  @override
  String get reservationConfirmed => 'Reservation confirmed!';

  @override
  String excursionConfirmedMessage(int count) {
    return 'Your excursion for $count person(s) is confirmed.';
  }

  @override
  String get confirmationNotification =>
      'You will receive a confirmation by notification.';

  @override
  String get numberOfGuests => 'Number of guests';

  @override
  String tableReservedMessage(int count) {
    return 'Your table for $count person(s) is reserved.';
  }

  @override
  String get orderConfirmed => 'Order confirmed!';

  @override
  String get itemsAddedToCart => 'Items added to cart!';

  @override
  String get errorPrefix => 'Error: ';

  @override
  String get cannotOpenLink => 'Cannot open link';

  @override
  String get included => 'Included:';

  @override
  String get schedule => 'Schedule';

  @override
  String get childrenAgeRange => 'Children (age)';

  @override
  String get reviewsTitle => 'Reviews';

  @override
  String get reviewsPending => 'To rate';

  @override
  String get reviewsMyReviews => 'My reviews';

  @override
  String get reviewsNoPending => 'No reviews to submit';

  @override
  String get reviewsNoPendingHint =>
      'Reviews are offered after a delivered order, checkout, completed request or finished excursion.';

  @override
  String get reviewsNoReviewsYet => 'No reviews yet';

  @override
  String get reviewsNoReviewsYetHint => 'Your reviews will appear here.';

  @override
  String get rateYourExperience => 'How was your experience?';

  @override
  String get optionalComment => 'Comment (optional)';

  @override
  String get submit => 'Submit';

  @override
  String get thankYouForReview => 'Thank you for your review!';

  @override
  String get orderTracking => 'Order tracking';

  @override
  String get orderItems => 'Ordered items';

  @override
  String get quantity => 'Quantity';

  @override
  String get reorder => 'Reorder';

  @override
  String get laundryRequestSentMessage =>
      'Your laundry request has been recorded.';

  @override
  String get demand => 'Request';

  @override
  String get reserve => 'Book';

  @override
  String get myExcursionsShort => 'My Excursions';

  @override
  String get categoryFacial => 'Facial Care';

  @override
  String get categoryBody => 'Body Care';

  @override
  String get excursionsTitle => 'Excursions';

  @override
  String articleCount(int count) {
    return '$count item(s)';
  }

  @override
  String appNameVersion(String version, String v) {
    return 'TerangaGuest $version $v';
  }

  @override
  String orderLabel(int id) {
    return 'Order $id';
  }

  @override
  String navigationTo(String name) {
    return 'Navigate to $name';
  }

  @override
  String get open => 'Open';

  @override
  String get itemsLabel => 'ITEMS';

  @override
  String get statusLabel => 'Status';

  @override
  String get laundrySubtitle => 'Cleaning service';

  @override
  String get spaHintExample => 'E.g. Light pressure, lavender oil...';

  @override
  String get restaurantHintExample => 'E.g. Window table, birthday...';

  @override
  String get allergiesPreferencesExample => 'E.g. Allergies, preferences...';

  @override
  String get orderConfirmedMessage =>
      'Your order has been successfully recorded';

  @override
  String get orderNumberLabel => 'Order no.';

  @override
  String get statusPending => 'Pending';

  @override
  String get statusConfirmed => 'Confirmed';

  @override
  String get statusPreparing => 'Preparing';

  @override
  String get statusDelivering => 'Delivering';

  @override
  String get statusInProgress => 'In progress';

  @override
  String get statusCompleted => 'Completed';

  @override
  String get statusCancelled => 'Cancelled';

  @override
  String get statusPickedUp => 'Picked up';

  @override
  String get statusProcessing => 'Processing';

  @override
  String get statusReady => 'Ready';

  @override
  String get statusDelivered => 'Delivered';

  @override
  String get myCart => 'My Cart';

  @override
  String get orderNotificationHint =>
      'You will receive a notification once your order is confirmed by the restaurant.';

  @override
  String spaReservationConfirmedMessage(String name) {
    return 'Your reservation for $name is confirmed.';
  }

  @override
  String get excursion => 'Excursion';

  @override
  String get verifyOrder => 'Check your order';

  @override
  String get personsShort => 'pax';

  @override
  String get amenities => 'Amenities';

  @override
  String get placeOrder => 'Place order';

  @override
  String get restaurant => 'Restaurant';

  @override
  String get time => 'Time';

  @override
  String get guests => 'Guests';

  @override
  String guestsCount(int count) {
    return '$count guest(s)';
  }

  @override
  String get dayMonday => 'Monday';

  @override
  String get dayTuesday => 'Tuesday';

  @override
  String get dayWednesday => 'Wednesday';

  @override
  String get dayThursday => 'Thursday';

  @override
  String get dayFriday => 'Friday';

  @override
  String get daySaturday => 'Saturday';

  @override
  String get daySunday => 'Sunday';

  @override
  String get service => 'Service';

  @override
  String get duration => 'Duration';

  @override
  String get reservationCancelledMessage => 'Reservation cancelled.';

  @override
  String get cancelReservationConfirm => 'Cancel this reservation?';

  @override
  String get vehicleRentalTitle => 'Vehicle Rental';

  @override
  String get vehicleRentalSubtitle =>
      'Choose a vehicle and submit your rental request.';

  @override
  String get noVehicleAvailable => 'No vehicles available';

  @override
  String get noVehicleAvailableHint =>
      'Vehicles will soon be offered by the establishment.';

  @override
  String get requestVehicleRental => 'Request this rental';

  @override
  String get rentalDate => 'Preferred date';

  @override
  String get rentalDuration => 'Duration (hours)';

  @override
  String get rentalDays => 'Number of days';

  @override
  String get estimatedPrice => 'Estimated price';

  @override
  String get guidedToursTitle => 'Personalized Guided Tours';

  @override
  String get guidedToursSubtitle =>
      'Enter date, tour type and number of people.';

  @override
  String get tourType => 'Tour type';

  @override
  String get tourTypeCultural => 'Cultural';

  @override
  String get tourTypeGastronomic => 'Gastronomic';

  @override
  String get tourTypeHistorical => 'Historical';

  @override
  String get transfersVtcTitle => 'Transfers & Chauffeur';

  @override
  String get transfersVtcSubtitle =>
      'Airport shuttle or private driver. Enter pickup and destination.';

  @override
  String get pickupPlace => 'Pickup location';

  @override
  String get destinationPlace => 'Destination';

  @override
  String get sitesTouristiquesTitle => 'Discovery & Tourist Sites';

  @override
  String get sitesTouristiquesSubtitle =>
      'Showcase of must-see places with photos and descriptions.';

  @override
  String get filterVehicleType => 'Type';

  @override
  String get filterMinSeats => 'Min. seats';

  @override
  String get vehicleTypeBerline => 'Sedan';

  @override
  String get vehicleTypeSuv => 'SUV';

  @override
  String get vehicleTypeMinibus => 'Minibus';

  @override
  String get vehicleTypeVan => 'Van';

  @override
  String get vehicleTypeOther => 'Other';

  @override
  String get validate => 'Validate';

  @override
  String get clientCode => 'Client code';

  @override
  String get clientCodeHint => 'Client code (e.g. 123456)';

  @override
  String get specialRequests => 'Special requests';

  @override
  String get developedByUTA => 'Developed by Universal Technologies Africa';

  @override
  String get tapToContinue => 'Tap to continue';

  @override
  String get changeLanguage => 'Change language';

  @override
  String get monthJanuary => 'January';

  @override
  String get monthFebruary => 'February';

  @override
  String get monthMarch => 'March';

  @override
  String get monthApril => 'April';

  @override
  String get monthMay => 'May';

  @override
  String get monthJune => 'June';

  @override
  String get monthJuly => 'July';

  @override
  String get monthAugust => 'August';

  @override
  String get monthSeptember => 'September';

  @override
  String get monthOctober => 'October';

  @override
  String get monthNovember => 'November';

  @override
  String get monthDecember => 'December';

  @override
  String get reservationClientCodeBanner =>
      'Reservations are restricted to guests with a valid stay. Please enter your client code below (received at check-in).';

  @override
  String get sessionExpiredNeedClientCode =>
      'Your stay is no longer active. Enter your client code to book.';

  @override
  String get periodAllDates => 'All dates';

  @override
  String get periodToday => 'Today';

  @override
  String get periodThisWeek => 'This week';

  @override
  String get periodThisMonth => 'This month';

  @override
  String get staffOrdersTitle => 'Room Service Orders';

  @override
  String get staffOrdersSubtitle =>
      'Tracking and processing room service orders';

  @override
  String addedToCart(String itemName) {
    return '$itemName added to cart';
  }

  @override
  String get invalidSessionRetry =>
      'Invalid or expired session. Please enter your code again.';

  @override
  String get orderValidationError =>
      'Unable to validate the order. Please check your client code or contact the reception.';

  @override
  String get enterCodeToValidate =>
      'Enter the code received at check-in to validate the order.';

  @override
  String get roomNumberTablet => 'Room number (this tablet)';

  @override
  String get roomIdRecommended =>
      'Room ID (recommended — see dashboard > Tablet access)';

  @override
  String get multiHotelWarning =>
      'In multi-property setups, enter the Room ID to only display your hotel\'s data.';

  @override
  String get code6Digits => '6-digit code';

  @override
  String get enter6Digits => 'Enter the 6-digit code.';

  @override
  String get defineRoomTablet => 'Provide the room number for this tablet.';

  @override
  String get confirmIdentity => 'Verify your identity';

  @override
  String get verifyInfoBeforeOrder =>
      'Verify your information before sending the order:';

  @override
  String get identityName => 'Name';

  @override
  String get identityRoom => 'Room';

  @override
  String get identityPhone => 'Phone';

  @override
  String get identityEmail => 'Email';

  @override
  String get paymentMethodText => 'Payment method';

  @override
  String get paymentCash => 'Cash';

  @override
  String get paymentRoomBill => 'Charge to room';

  @override
  String get paymentWave => 'Wave';

  @override
  String get paymentOrangeMoney => 'Orange Money';

  @override
  String get confirmOrder => 'Confirm order';

  @override
  String get statusUpdated => 'Status updated';

  @override
  String get orderStatusUpdated => 'Order status updated';

  @override
  String cannotSendNotification(String error) {
    return 'Unable to send notification: $error';
  }

  @override
  String get orderCancelledNotified => 'Order cancelled — customer notified.';

  @override
  String get orderCancelled => 'Order cancelled';

  @override
  String get cancelOrder => 'Cancel order';

  @override
  String get yesCancel => 'Yes, cancel';

  @override
  String get spaCategoryAll => 'All';

  @override
  String get spaCategoryMassage => 'Massages';

  @override
  String get spaCategoryFacial => 'Facials';

  @override
  String get spaCategoryBody => 'Body Treatments';

  @override
  String get spaCategoryHammam => 'Hammam';

  @override
  String get spaAndWellness => 'Spa & Wellness';

  @override
  String get spaServiceFallback => 'Spa Service';

  @override
  String get timeLabel => 'Time';

  @override
  String get excursionFallback => 'Tour';

  @override
  String get locationEnableSettings => 'Enable location in settings.';

  @override
  String get locationAccessDenied => 'Location access denied.';

  @override
  String get locationCurrentPos => 'Current position';

  @override
  String get locationError => 'Cannot get location';

  @override
  String get vehicleRequestType => 'Request type';

  @override
  String get vehicleTypeTaxi => 'Taxi';

  @override
  String get vehicleTypeRental => 'Rental';

  @override
  String get taxiPickup => 'Pickup';

  @override
  String get taxiPickupHint => 'Address or location';

  @override
  String get taxiMyLocation => 'My location';

  @override
  String get taxiDestination => 'Destination';

  @override
  String get taxiDestinationHint => 'Destination address';

  @override
  String get taxiDistanceOption => 'Distance (km, optional)';

  @override
  String get taxiDistanceHint => 'E.g., 5.2';

  @override
  String get rentalAllTypes => 'All types';

  @override
  String get rentalSedan => 'Sedan';

  @override
  String get rentalSuv => 'SUV';

  @override
  String get rentalMinibus => 'Minibus';

  @override
  String get rentalVan => 'Van';

  @override
  String get rentalOther => 'Other';

  @override
  String get rentalChooseVehicle => 'Choose a vehicle';

  @override
  String get rentalTypeLabel => 'Type';

  @override
  String get rentalSeatsMin => 'Min spaces.';

  @override
  String get rentalSeatsAll => 'All';

  @override
  String rentalSeatsCount(String count) {
    return '$count space(s)';
  }

  @override
  String get rentalNoVehicleFound => 'No vehicle for these criteria.';

  @override
  String rentalSeatsPl(String count) {
    return '$count sp.';
  }

  @override
  String get rentalDaysHint => 'E.g., 2';

  @override
  String get rentalDurationHours => 'Duration (hours)';

  @override
  String get rentalDurationHint => 'E.g., 8 (half-day if ≤ 5 h)';

  @override
  String rentalEstimate(String price) {
    return 'Estimate: $price FCFA';
  }

  @override
  String get rentalErrorDestination => 'Provide the destination address.';

  @override
  String get rentalErrorChooseVehicle => 'Choose a vehicle from the list.';

  @override
  String get rentalErrorVehicleOrDetails =>
      'Choose Taxi or Rental, or describe your request.';

  @override
  String get sessionExpiredNeedClientCodeRequest =>
      'Your stay is no longer active. Enter your client code to send request.';

  @override
  String get palaceConciergeServices => 'Palace / Concierge Services';

  @override
  String get palaceConciergeTracking => 'Palace & Concierge requests tracking';

  @override
  String get palaceConciergeServiceSingle => 'Palace / Concierge service';

  @override
  String scheduledForDate(String date) {
    return 'Scheduled for $date';
  }

  @override
  String get requestDetailsOnly => 'Request details';

  @override
  String get cancellationReason => 'Cancellation reason';

  @override
  String get acceptRequestTitle => 'Accept request';

  @override
  String get acceptRequestMessage =>
      'Accept this palace / concierge service request?';

  @override
  String get completeRequestTitle => 'Complete request';

  @override
  String get completeRequestMessage => 'Complete this request?';

  @override
  String get rejectRequestTitle => 'Reject request';

  @override
  String get rejectRequestMessage => 'Reject this palace service request?';

  @override
  String get cancelRequestMessage => 'Cancel this palace service request?';

  @override
  String get cancellationReasonHint => 'Reason for cancellation';

  @override
  String get validationReasonRequired => 'Please provide a reason.';

  @override
  String get requestDetailTitle => 'Palace / Concierge request detail';

  @override
  String get laundryRequestDetailTitle => 'Laundry Request Detail';

  @override
  String get laundryNoItemsInRequest => 'No items found for this request.';

  @override
  String get hotelMap => 'Map';

  @override
  String get albumsTitle => 'Albums';

  @override
  String photoCount(int count) {
    return '$count photo(s)';
  }

  @override
  String get noPhoto => 'No photo';

  @override
  String get presentationTitle => 'Presentation';

  @override
  String get addressTitle => 'Address';

  @override
  String get phoneAbbr => 'Tel.';

  @override
  String get staffEmergencySubtitle => 'Active doctor / security alerts';

  @override
  String get guestEmergencySubtitle => 'Your Assistance & Emergency requests';

  @override
  String get noEmergencyAlerts => 'No active Assistance & Emergency alerts.';

  @override
  String get acceptEmergencyAlertMessage =>
      'Accept this Assistance & Emergency alert?';

  @override
  String get cancelEmergencyAlertMessage => 'Cancel this alert?';

  @override
  String get reasonOptional => 'Reason (optional)';

  @override
  String get reasonRequired => 'Please provide a reason.';

  @override
  String get accept => 'Accept';

  @override
  String get unidentifiedRoom => 'Unidentified room';

  @override
  String requestFromRoom(String roomInfo) {
    return 'Request from $roomInfo';
  }

  @override
  String get newStaffMessage => 'New message from staff';

  @override
  String get startConversation => 'Start the conversation with the reception.';

  @override
  String get newMessageSingular => '1 new message';

  @override
  String get newMessagesPlural => 'new messages';

  @override
  String get yesterday => 'Yesterday';

  @override
  String get imageUnavailable => 'Image unavailable';

  @override
  String get voiceMessage => 'Voice message';

  @override
  String get microphonePermission =>
      'Allow microphone access to send a voice note.';

  @override
  String get sportCategory => 'Sport';

  @override
  String get leisureCategory => 'Leisure';

  @override
  String get timeoutError => 'Request timeout. Please check your connection.';

  @override
  String get viewMyRequests => 'View my requests';

  @override
  String get datePrefix => 'Date: ';

  @override
  String get timePrefix => 'Time: ';

  @override
  String get requestDemandeSuffix => ' - Request';

  @override
  String get sportFitnessCoachBooking =>
      'Sport & Fitness - Personal coach booking';

  @override
  String get golfPrefix => 'Golf';

  @override
  String get tennisPrefix => 'Tennis';

  @override
  String get guidedToursNotConfigured =>
      'Guided tours not configured. The establishment must add the service \'Custom Guided Tours\' in the dashboard (Palace Services).';

  @override
  String get transfersNotConfigured =>
      'Transfers & VTC service not configured. Contact the establishment.';

  @override
  String get pickupDestinationRequired =>
      'Please indicate the pick-up location and destination.';

  @override
  String get exAirportHotel => 'Ex: Airport, Hotel…';

  @override
  String get exDowntownAddress => 'Ex: Downtown, Address…';

  @override
  String get vehicleRentalNotConfigured =>
      'Vehicle rental service not configured. Contact the establishment.';

  @override
  String get durationOrDaysRequired =>
      'Please indicate the number of days or duration in hours.';

  @override
  String get exDays => 'Ex: 2';

  @override
  String get exHours => 'Ex: 5 (half-day)';

  @override
  String get profileRoom => 'Room';

  @override
  String get profileHotel => 'Hotel';

  @override
  String get profileRole => 'Role';

  @override
  String get myInvoices => 'My Invoices';

  @override
  String get phone => 'Phone';

  @override
  String get noInvoicesTitle => 'No invoices';

  @override
  String get noInvoicesSubtitle => 'You don\'t have any completed orders yet.';

  @override
  String get generalError => 'Error';

  @override
  String get orderReceipt => 'ORDER RECEIPT';

  @override
  String get hotelLabel => 'HOTEL:';

  @override
  String get orderDateLabel => 'ORDER DATE:';

  @override
  String get deliveryLabel => 'DELIVERY:';

  @override
  String get noItems => 'No items';

  @override
  String get totalToPay => 'TOTAL TO PAY';

  @override
  String get taxesIncluded => 'All taxes and service fees included';

  @override
  String get thankYouForOrder => 'Thank you for your order!';

  @override
  String get invoiceRoomLabel => 'ROOM:';
}
