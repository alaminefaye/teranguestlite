// ignore: unused_import
import 'package:intl/intl.dart' as intl;
import 'app_localizations.dart';

// ignore_for_file: type=lint

/// The translations for Spanish Castilian (`es`).
class AppLocalizationsEs extends AppLocalizations {
  AppLocalizationsEs([String locale = 'es']) : super(locale);

  @override
  String get appTitle => 'Teranga Guest';

  @override
  String get login => 'Iniciar sesión';

  @override
  String get email => 'Email';

  @override
  String get password => 'Contraseña';

  @override
  String get rememberMe => 'Recordarme';

  @override
  String get loginButton => 'Entrar';

  @override
  String get loginError => 'Error de conexión';

  @override
  String get emailRequired => 'Introduzca su correo electrónico';

  @override
  String get emailInvalid => 'Correo no válido';

  @override
  String get passwordRequired => 'Introduzca su contraseña';

  @override
  String get passwordTooShort =>
      'Contraseña demasiado corta (mín. 6 caracteres)';

  @override
  String get myProfile => 'Mi Perfil';

  @override
  String get myHistories => 'Mis Historiales';

  @override
  String get myFavorites => 'Mis Favoritos';

  @override
  String get myOrders => 'Mis Pedidos';

  @override
  String get myRestaurantReservations => 'Mis Reservas de Restaurante';

  @override
  String get mySpaReservations => 'Mis Reservas de Spa';

  @override
  String get myExcursions => 'Mis Excursiones';

  @override
  String get myLaundryRequests => 'Mis Solicitudes de Lavandería';

  @override
  String get myPalaceRequests => 'Mis Solicitudes Palace';

  @override
  String get settings => 'Ajustes';

  @override
  String get changePassword => 'Cambiar contraseña';

  @override
  String get about => 'Acerca de';

  @override
  String get contactSupport => 'Contactar soporte';

  @override
  String get logout => 'Cerrar sesión';

  @override
  String get logoutConfirm => '¿Está seguro de que desea cerrar sesión?';

  @override
  String get cancel => 'Cancelar';

  @override
  String get version => 'Versión';

  @override
  String get preferences => 'Preferencias';

  @override
  String get notifications => 'Notificaciones';

  @override
  String get notificationsOn => 'Notificaciones activadas';

  @override
  String get notificationsOff => 'Notificaciones desactivadas';

  @override
  String get application => 'Aplicación';

  @override
  String get language => 'Idioma';

  @override
  String get french => 'Français';

  @override
  String get english => 'English';

  @override
  String get welcomeTitle => 'Bienvenido al King Fahd Palace Hotel';

  @override
  String welcomeToEnterprise(String enterpriseName) {
    return 'Bienvenido al $enterpriseName';
  }

  @override
  String get welcomeSubtitle => 'Su asistente digital a su servicio';

  @override
  String get roomService => 'Servicio a la habitación';

  @override
  String get restaurantsBars => 'Restaurantes y Bares';

  @override
  String get spaWellness => 'Spa y Bienestar';

  @override
  String get wellnessSportLeisure => 'Bienestar, deporte y ocio';

  @override
  String get wellnessSportLeisureSubtitle => 'Spa, Golf, Tenis, Fitness';

  @override
  String get golfTitle => 'Golf';

  @override
  String get golfSubtitle => 'Reserva Tee-time y alquiler de material';

  @override
  String get tennisTitle => 'Tenis';

  @override
  String get tennisSubtitle => 'Reserva de pistas y alquiler de material';

  @override
  String get golfTennisTitle => 'Golf y Tenis';

  @override
  String get golfTennisSubtitle =>
      'Reserva Tee-time, pistas y alquiler de material';

  @override
  String get golfTennisTeetime => 'Reserva Tee-time';

  @override
  String get golfTennisCourt => 'Pista de tenis';

  @override
  String get golfTennisEquipment => 'Alquiler de material';

  @override
  String get sportFitnessTitle => 'Deporte y Fitness';

  @override
  String get sportFitnessSubtitle =>
      'Horarios del gimnasio y reserva de coach personal';

  @override
  String get sportFitnessGymHours => 'Horario del gimnasio';

  @override
  String get sportFitnessBookCoach => 'Reservar un entrenador personal';

  @override
  String get gymHoursDefault => 'Consulte recepción para horarios.';

  @override
  String get palaceServices => 'Otros servicios';

  @override
  String get explorationMobility => 'Exploration & mobilité';

  @override
  String get explorationMobilitySubtitle =>
      'Location véhicule, découverte, visites guidées et transferts';

  @override
  String get vehicleRental => 'Location de Véhicule';

  @override
  String get vehicleRentalDesc =>
      'Catalogue de véhicules (berlines, 4x4, citadines) avec réservation et options de confort.';

  @override
  String get sitesTouristiques => 'Découverte & Sites Touristiques';

  @override
  String get sitesTouristiquesDesc =>
      'Lieux incontournables : Lac Rose, Île de Gorée, Plateau… Photos et descriptifs.';

  @override
  String get guidedTours => 'Visites Guidées Personnalisées';

  @override
  String get guidedToursDesc =>
      'Réservation de guides certifiés pour circuits culturels, gastronomiques ou historiques.';

  @override
  String get transfersVtc => 'Transferts & VTC';

  @override
  String get transfersVtcDesc =>
      'Navettes aéroport ou chauffeurs privés pour des trajets sécurisés.';

  @override
  String get excursions => 'Excursiones';

  @override
  String get laundry => 'Lavandería';

  @override
  String get concierge => 'Conserjería';

  @override
  String get callCenter => 'Centro de llamadas';

  @override
  String get hotelInfosSecurity => 'Hotel infos & sécurité';

  @override
  String get hotelInfosSecuritySubtitle =>
      'Livret d\'accueil, assistance urgence et chatbot';

  @override
  String get guidesInfos => 'Guides & Infos';

  @override
  String get hotelInfos => 'Hôtel Infos';

  @override
  String get hotelInfosDesc => 'Wi-Fi, plans, règlement et infos pratiques';

  @override
  String get assistanceEmergency => 'Assistance & Urgence';

  @override
  String get assistanceEmergencyDesc =>
      'Médecin ou urgence sécurité (chambre identifiée)';

  @override
  String get chatbotMultilingual => 'Chatbot IA Multilingue';

  @override
  String get chatbotDesc => 'Assistant digital 24/7';

  @override
  String get gallery => 'Galería';

  @override
  String get galleryDesc => 'Fotos del establecimiento y álbumes';

  @override
  String get ourEstablishments => 'Nuestros establecimientos';

  @override
  String get ourEstablishmentsDesc => 'Otros sitios del grupo en el país';

  @override
  String get wifiCode => 'Code Wi-Fi';

  @override
  String get wifiPassword => 'Mot de passe Wi-Fi';

  @override
  String get houseRules => 'Règlement intérieur';

  @override
  String get practicalInfo => 'Informations pratiques';

  @override
  String get requestDoctor => 'Solliciter un médecin';

  @override
  String get reportSecurityEmergency => 'Signaler une urgence sécurité';

  @override
  String roomLabel(String room) {
    return 'Habitación: $room';
  }

  @override
  String get emergencyRequestSent =>
      'Demande envoyée. L\'équipe va vous contacter.';

  @override
  String get noActiveStayForEmergency =>
      'Un séjour actif est requis. Connectez-vous avec le compte de la chambre ou contactez la réception.';

  @override
  String get assistanceDoctorNotConfigured =>
      'Le service Assistance médecin n\'est pas configuré pour cet établissement.';

  @override
  String get assistanceSecurityNotConfigured =>
      'Le service Urgence sécurité n\'est pas configuré pour cet établissement.';

  @override
  String confirmEmergencyAction(String action) {
    return 'Voulez-vous confirmer : $action ?';
  }

  @override
  String get deleteConversationConfirm => 'Supprimer cette conversation ?';

  @override
  String get deleteConversation => 'Supprimer la conversation';

  @override
  String get messageDeleted => 'Message supprimé';

  @override
  String get reply => 'Répondre';

  @override
  String get deleteMessage => 'Supprimer le message';

  @override
  String get chatbotComingSoon => 'Bientôt disponible';

  @override
  String get chatbotComingSoonHint =>
      'Le chatbot multilingue sera disponible prochainement.';

  @override
  String get servicesChambreLogistique => 'Servicio en habitación';

  @override
  String get roomServiceRestauration => 'Room Service y Restauración';

  @override
  String get roomServiceRestaurationDesc =>
      'Menú digital en alta definición para pedir comidas y bebidas con seguimiento en tiempo real de la preparación.';

  @override
  String get laundryDesc =>
      'Tarifa interactiva y solicitud inmediata de recogida de ropa.';

  @override
  String get amenitiesConcierge => 'Amenidades y Conserjería';

  @override
  String get amenitiesConciergeDesc =>
      'Solicitud simplificada de artículos de tocador, almohadas extra, kit de afeitado o cualquier otro servicio sin llamar.';

  @override
  String get minibarIntelligent => 'Minibar Inteligente';

  @override
  String get minibarIntelligentDesc =>
      'Inventario digital de productos y declaración simplificada de consumiciones.';

  @override
  String get comingSoon => 'Próximamente';

  @override
  String get amenityToiletries => 'Artículos de tocador';

  @override
  String get amenityPillows => 'Almohadas adicionales';

  @override
  String get amenityShavingKit => 'Kit de afeitado';

  @override
  String get amenityOther => 'Otra solicitud';

  @override
  String get amenityDetailsHint => 'Especifique si desea (opcional)';

  @override
  String get amenitySelectQuantities => 'Seleccione artículos y cantidades';

  @override
  String get amenityPillowCount => 'Número de almohadas';

  @override
  String get amenityOtherDetailsHint => 'Describa su solicitud (opcional)';

  @override
  String get amenityItemSoap => 'Jabón';

  @override
  String get amenityItemShampoo => 'Champú';

  @override
  String get amenityItemToothpaste => 'Pasta de dientes';

  @override
  String get amenityItemToothbrush => 'Cepillo de dientes';

  @override
  String get amenityItemComb => 'Peine';

  @override
  String get amenityItemTowels => 'Toallas';

  @override
  String get amenityItemPillow => 'Almohada adicional';

  @override
  String get amenityItemRazor => 'Maquinilla de afeitar';

  @override
  String get amenityItemShavingFoam => 'Espuma de afeitar';

  @override
  String get amenityItemAfterShave => 'Loción aftershave';

  @override
  String get amenityItemBlades => 'Cuchillas de repuesto';

  @override
  String get back => 'Volver';

  @override
  String get retry => 'Reintentar';

  @override
  String get error => 'Error';

  @override
  String get errorHint => 'Compruebe su conexión e intente de nuevo.';

  @override
  String get close => 'Cerrar';

  @override
  String get ok => 'OK';

  @override
  String get save => 'Guardar';

  @override
  String get noFavorites => 'Sin favoritos';

  @override
  String get noFavoritesHint =>
      'Añada artículos, restaurantes, tratamientos o excursiones a favoritos desde sus páginas.';

  @override
  String get contactSupportTitle => 'Contactar soporte';

  @override
  String get chooseContact => 'Elija un medio de contacto:';

  @override
  String get aboutDescription =>
      'Aplicación de bienvenida y servicios para huéspedes del King Fahd Palace Hotel.';

  @override
  String get hotelName => 'KING FAHD PALACE HOTEL';

  @override
  String get noUser => 'Ningún usuario conectado';

  @override
  String get addToCart => 'Añadir al carrito';

  @override
  String get viewCart => 'Ver carrito';

  @override
  String get cart => 'Carrito';

  @override
  String get emptyCart => 'Su carrito está vacío';

  @override
  String get emptyCartHint => 'Añada artículos para pedir.';

  @override
  String get browseMenu => 'Ver menú';

  @override
  String get clearCartConfirm => '¿Está seguro de que desea vaciar su carrito?';

  @override
  String get clear => 'Vaciar';

  @override
  String get orderSuccess => 'Pedido registrado';

  @override
  String get orderSuccessHint => 'Puede seguir su pedido en Mis Pedidos.';

  @override
  String get home => 'Inicio';

  @override
  String get myBookings => 'Mis reservas';

  @override
  String get excursionNotFound => 'Excursión no encontrada';

  @override
  String get excursionNotFoundHint =>
      'Esta excursión no existe o ya no está disponible.';

  @override
  String get serviceNotFound => 'Servicio no encontrado';

  @override
  String get serviceNotFoundHint =>
      'Este servicio no existe o ya no está disponible.';

  @override
  String get restaurantNotFound => 'Restaurante no encontrado';

  @override
  String get restaurantNotFoundHint =>
      'Este restaurante no existe o ya no está disponible.';

  @override
  String get orderNotFound => 'Pedido no encontrado';

  @override
  String get orderNotFoundHint => 'Este pedido no existe o ha sido eliminado.';

  @override
  String get noLaundryService => 'Ningún servicio de lavandería disponible';

  @override
  String get noLaundryServiceHint =>
      'Los servicios de limpieza aparecerán aquí.';

  @override
  String get noSpaService => 'Ningún servicio de spa disponible';

  @override
  String get noSpaServiceInCategory => 'Ningún servicio en esta categoría';

  @override
  String get noSpaServiceHint =>
      'Los tratamientos y masajes se ofrecerán aquí.';

  @override
  String get noPalaceService => 'Ningún servicio Palace disponible';

  @override
  String get noPalaceServiceHint => 'Los servicios premium se mostrarán aquí.';

  @override
  String get noExcursionAvailable => 'Ninguna excursión disponible';

  @override
  String get noExcursionAvailableHint =>
      'Las actividades y salidas se ofrecerán aquí.';

  @override
  String get noOrder => 'Ningún pedido';

  @override
  String noOrderForStatus(String status) {
    return 'Ningún pedido $status';
  }

  @override
  String get noOrderSubtitle => 'Sus pedidos de habitación aparecerán aquí.';

  @override
  String get orderStatusPending => 'pendiente';

  @override
  String get orderStatusConfirmed => 'confirmado';

  @override
  String get orderStatusPreparing => 'en preparación';

  @override
  String get orderStatusDelivering => 'en reparto';

  @override
  String get orderStatusDelivered => 'entregado';

  @override
  String get noRestaurantAvailable => 'Ningún restaurante disponible';

  @override
  String noRestaurantForType(String type) {
    return 'Ningún $type disponible';
  }

  @override
  String get noRestaurantSubtitle =>
      'Los restaurantes y bares se listarán aquí.';

  @override
  String get typeRestaurant => 'restaurante';

  @override
  String get typeBar => 'bar';

  @override
  String get typeCafe => 'café';

  @override
  String get typeLounge => 'lounge';

  @override
  String get noItemAvailable => 'Ningún artículo disponible';

  @override
  String get noSearchResult => 'Ningún resultado';

  @override
  String get noItemSubtitle =>
      'Los artículos de esta categoría se listarán aquí.';

  @override
  String get tryAnotherSearch => 'Pruebe otro término de búsqueda.';

  @override
  String get noCategoryAvailable => 'Ninguna categoría disponible';

  @override
  String get noCategoryHint => 'El menú de habitación estará disponible aquí.';

  @override
  String get noPalaceRequest => 'Ninguna solicitud Palace';

  @override
  String get noPalaceRequestHint =>
      'Envíe una solicitud desde los servicios Palace.';

  @override
  String get noLaundryRequest => 'Ninguna solicitud de lavandería';

  @override
  String get noLaundryRequestHint =>
      'Envíe una solicitud desde el servicio de Lavandería.';

  @override
  String get noExcursionBooked => 'Ninguna excursión reservada';

  @override
  String get noExcursionBookedHint =>
      'Reserve una excursión desde la lista de actividades.';

  @override
  String get noSpaReservation => 'Ninguna reserva de spa';

  @override
  String get noSpaReservationHint =>
      'Reserve un tratamiento desde la lista de servicios de spa.';

  @override
  String get noRestaurantReservation => 'Ninguna reserva de restaurante';

  @override
  String get noRestaurantReservationHint =>
      'Reserve una mesa desde la lista de restaurantes.';

  @override
  String get itemNotFound => 'Artículo no encontrado';

  @override
  String get filterAll => 'Todas';

  @override
  String get filterPending => 'Pendiente';

  @override
  String get filterConfirmed => 'Confirmadas';

  @override
  String get filterPreparing => 'En preparación';

  @override
  String get filterDelivering => 'En reparto';

  @override
  String get filterDelivered => 'Entregadas';

  @override
  String get filterAllTypes => 'Todos';

  @override
  String get filterRestaurants => 'Restaurantes';

  @override
  String get filterBars => 'Bares';

  @override
  String get filterCafes => 'Cafés';

  @override
  String get filterLounges => 'Lounges';

  @override
  String get ordersHistorySubtitle => 'Historial y seguimiento';

  @override
  String get arabic => 'العربية';

  @override
  String get spanish => 'Español';

  @override
  String get discoverRestaurants => 'Descubra nuestros establecimientos';

  @override
  String get reservationsConfirmed => 'Reservas confirmadas';

  @override
  String get myRequests => 'Mis Solicitudes';

  @override
  String requestNumber(int id) {
    return 'Solicitud #$id';
  }

  @override
  String get palaceServicesSubtitle => 'Servicios premium y conserjería';

  @override
  String get spaWellnessSubtitle => 'Bienestar y relax';

  @override
  String get myReservations => 'Mis Reservas';

  @override
  String get discoverRegion => 'Descubra nuestra región';

  @override
  String get spaSubtitle => 'Descanso y relajación';

  @override
  String get chooseCategory => 'Elija una categoría';

  @override
  String get orderDetailTitle => 'Detalle del pedido';

  @override
  String get orderTrackingSubtitle => 'Seguimiento de su pedido';

  @override
  String adultPrice(String price) {
    return 'Adulto: $price';
  }

  @override
  String childPrice(String price) {
    return 'Niño: $price';
  }

  @override
  String get book => 'Reservar';

  @override
  String get unavailable => 'No disponible';

  @override
  String get spaServiceDefaultName => 'Servicio Spa';

  @override
  String capacityPeople(int count) {
    return 'Capacidad: $count personas';
  }

  @override
  String get openingHours => 'Horario de apertura';

  @override
  String get bookTable => 'Reservar mesa';

  @override
  String get closed => 'Cerrado';

  @override
  String get search => 'Buscar...';

  @override
  String get reviewOrder => 'Revise su pedido';

  @override
  String get specialInstructions => 'Instrucciones especiales';

  @override
  String get specialInstructionsHint =>
      'Alergias, preferencias, instrucciones de entrega...';

  @override
  String get description => 'Descripción';

  @override
  String get specialInstructionsExample => 'Ej: Sin cebolla, bien hecho...';

  @override
  String get viewCartCaps => 'VER CARRITO';

  @override
  String get passwordChangedSuccess => 'Contraseña modificada correctamente';

  @override
  String get currentPassword => 'Contraseña actual';

  @override
  String get newPassword => 'Nueva contraseña';

  @override
  String get confirmPassword => 'Confirmar nueva contraseña';

  @override
  String get fieldRequired => 'Campo obligatorio';

  @override
  String get minChars => 'Mínimo 8 caracteres';

  @override
  String get needUpperCase => 'Debe contener una mayúscula';

  @override
  String get needDigit => 'Debe contener un número';

  @override
  String get passwordsDoNotMatch => 'Las contraseñas no coinciden';

  @override
  String get passwordRulesHint =>
      'La nueva contraseña debe tener al menos 8 caracteres, una mayúscula y un número.';

  @override
  String get requestDetails => 'Detalles de su solicitud';

  @override
  String get describeRequest => 'Describa su solicitud en detalle...';

  @override
  String get preferredTimeOptional => 'Hora preferida (opcional)';

  @override
  String get selectDateAndTime => 'Seleccionar fecha y hora';

  @override
  String get sendRequest => 'Enviar solicitud';

  @override
  String get requestReservationHint =>
      'Indique fecha, hora y detalles en el formulario.';

  @override
  String get requestSent => '¡Solicitud enviada!';

  @override
  String get requestSentMessage =>
      'Su solicitud de servicio ha sido registrada.';

  @override
  String get confirmRequest => 'Confirmar solicitud';

  @override
  String get selectedItems => 'Artículos seleccionados';

  @override
  String get total => 'Total';

  @override
  String get specialInstructionsOptional =>
      'Instrucciones especiales (opcional)';

  @override
  String get laundryInstructionsExample =>
      'Ej: Detergente sin perfume, planchado suave...';

  @override
  String get date => 'Fecha';

  @override
  String get selectDate => 'Seleccionar fecha';

  @override
  String get participants => 'Participantes';

  @override
  String get adults => 'Adultos';

  @override
  String get children => 'Niños';

  @override
  String get specialRequestsOptional => 'Solicitudes especiales (opcional)';

  @override
  String get summary => 'Resumen';

  @override
  String get confirmReservation => 'Confirmar reserva';

  @override
  String get reservationConfirmed => '¡Reserva confirmada!';

  @override
  String excursionConfirmedMessage(int count) {
    return 'Su excursión para $count persona(s) está confirmada.';
  }

  @override
  String get confirmationNotification =>
      'Recibirá una confirmación por notificación.';

  @override
  String get numberOfGuests => 'Número de personas';

  @override
  String tableReservedMessage(int count) {
    return 'Su mesa para $count persona(s) está reservada.';
  }

  @override
  String get orderConfirmed => '¡Pedido confirmado!';

  @override
  String get itemsAddedToCart => '¡Artículos añadidos al carrito!';

  @override
  String get errorPrefix => 'Error: ';

  @override
  String get cannotOpenLink => 'No se puede abrir el enlace';

  @override
  String get included => 'Incluido:';

  @override
  String get schedule => 'Horarios';

  @override
  String get childrenAgeRange => 'Niños (edad)';

  @override
  String get reviewsTitle => 'Opiniones';

  @override
  String get reviewsPending => 'Por valorar';

  @override
  String get reviewsMyReviews => 'Mis opiniones';

  @override
  String get reviewsNoPending => 'Ninguna opinión pendiente';

  @override
  String get reviewsNoPendingHint =>
      'Las opiniones se ofrecen tras un pedido entregado, salida, solicitud completada o excursión terminada.';

  @override
  String get reviewsNoReviewsYet => 'Aún no hay opiniones';

  @override
  String get reviewsNoReviewsYetHint => 'Tus opiniones aparecerán aquí.';

  @override
  String get rateYourExperience => '¿Cómo fue tu experiencia?';

  @override
  String get optionalComment => 'Comentario (opcional)';

  @override
  String get submit => 'Enviar';

  @override
  String get thankYouForReview => '¡Gracias por tu opinión!';

  @override
  String get orderTracking => 'Seguimiento del pedido';

  @override
  String get orderItems => 'Artículos pedidos';

  @override
  String get quantity => 'Cantidad';

  @override
  String get reorder => 'Volver a pedir';

  @override
  String get laundryRequestSentMessage =>
      'Su solicitud de lavandería ha sido registrada.';

  @override
  String get demand => 'Solicitar';

  @override
  String get reserve => 'Reservar';

  @override
  String get myExcursionsShort => 'Mis Excursiones';

  @override
  String get categoryFacial => 'Cuidado facial';

  @override
  String get categoryBody => 'Cuidado corporal';

  @override
  String get excursionsTitle => 'Excursiones';

  @override
  String articleCount(int count) {
    return '$count artículo(s)';
  }

  @override
  String appNameVersion(String version, String v) {
    return 'TerangaGuest $version $v';
  }

  @override
  String orderLabel(int id) {
    return 'Pedido $id';
  }

  @override
  String navigationTo(String name) {
    return 'Ir a $name';
  }

  @override
  String get open => 'Abierto';

  @override
  String get itemsLabel => 'ARTÍCULOS';

  @override
  String get statusLabel => 'Estado';

  @override
  String get laundrySubtitle => 'Servicio de limpieza';

  @override
  String get spaHintExample => 'Ej: Presión suave, aceite de lavanda...';

  @override
  String get restaurantHintExample =>
      'Ej: Mesa junto a la ventana, cumpleaños...';

  @override
  String get allergiesPreferencesExample => 'Ej: Alergias, preferencias...';

  @override
  String get orderConfirmedMessage =>
      'Su pedido ha sido registrado correctamente';

  @override
  String get orderNumberLabel => 'N.º de pedido';

  @override
  String get statusPending => 'Pendiente';

  @override
  String get statusConfirmed => 'Confirmada';

  @override
  String get statusPreparing => 'En preparación';

  @override
  String get statusDelivering => 'En entrega';

  @override
  String get statusInProgress => 'En curso';

  @override
  String get statusCompleted => 'Completada';

  @override
  String get statusCancelled => 'Cancelada';

  @override
  String get statusPickedUp => 'Recogida';

  @override
  String get statusProcessing => 'En proceso';

  @override
  String get statusReady => 'Lista';

  @override
  String get statusDelivered => 'Entregada';

  @override
  String get myCart => 'Mi Carrito';

  @override
  String get orderNotificationHint =>
      'Recibirá una notificación cuando el restaurante confirme su pedido.';

  @override
  String spaReservationConfirmedMessage(String name) {
    return 'Su reserva para $name está confirmada.';
  }

  @override
  String get excursion => 'Excursión';

  @override
  String get verifyOrder => 'Verifique su pedido';

  @override
  String get personsShort => 'pax';

  @override
  String get amenities => 'Servicios';

  @override
  String get placeOrder => 'Realizar pedido';

  @override
  String get restaurant => 'Restaurante';

  @override
  String get time => 'Hora';

  @override
  String get guests => 'Invitados';

  @override
  String guestsCount(int count) {
    return '$count persona(s)';
  }

  @override
  String get dayMonday => 'Lunes';

  @override
  String get dayTuesday => 'Martes';

  @override
  String get dayWednesday => 'Miércoles';

  @override
  String get dayThursday => 'Jueves';

  @override
  String get dayFriday => 'Viernes';

  @override
  String get daySaturday => 'Sábado';

  @override
  String get daySunday => 'Domingo';

  @override
  String get service => 'Servicio';

  @override
  String get duration => 'Duración';

  @override
  String get reservationCancelledMessage => 'Reserva cancelada.';

  @override
  String get cancelReservationConfirm => '¿Cancelar esta reserva?';

  @override
  String get vehicleRentalTitle => 'Location de Véhicule';

  @override
  String get vehicleRentalSubtitle =>
      'Choisissez un véhicule et envoyez votre demande de réservation.';

  @override
  String get noVehicleAvailable => 'Aucun véhicule disponible';

  @override
  String get noVehicleAvailableHint =>
      'Les véhicules seront bientôt proposés par l\'établissement.';

  @override
  String get requestVehicleRental => 'Demander cette location';

  @override
  String get rentalDate => 'Date souhaitée';

  @override
  String get rentalDuration => 'Durée (heures)';

  @override
  String get rentalDays => 'Número de días';

  @override
  String get estimatedPrice => 'Prix estimé';

  @override
  String get guidedToursTitle => 'Visites Guidées Personnalisées';

  @override
  String get guidedToursSubtitle =>
      'Indiquez la date, le type de circuit et le nombre de personnes.';

  @override
  String get tourType => 'Type de circuit';

  @override
  String get tourTypeCultural => 'Culturel';

  @override
  String get tourTypeGastronomic => 'Gastronomique';

  @override
  String get tourTypeHistorical => 'Historique';

  @override
  String get transfersVtcTitle => 'Transferts & VTC';

  @override
  String get transfersVtcSubtitle =>
      'Navette aéroport ou chauffeur privé. Indiquez lieu de prise en charge et destination.';

  @override
  String get pickupPlace => 'Lieu de prise en charge';

  @override
  String get destinationPlace => 'Destination';

  @override
  String get sitesTouristiquesTitle => 'Découverte & Sites Touristiques';

  @override
  String get sitesTouristiquesSubtitle =>
      'Vitrine des lieux incontournables avec photos et descriptifs.';

  @override
  String get filterVehicleType => 'Type';

  @override
  String get filterMinSeats => 'Places min.';

  @override
  String get vehicleTypeBerline => 'Berline';

  @override
  String get vehicleTypeSuv => 'SUV';

  @override
  String get vehicleTypeMinibus => 'Minibus';

  @override
  String get vehicleTypeVan => 'Van';

  @override
  String get vehicleTypeOther => 'Autre';

  @override
  String get validate => 'Validar';

  @override
  String get clientCode => 'Código de cliente';

  @override
  String get clientCodeHint => 'Código de cliente (ej: 123456)';

  @override
  String get specialRequests => 'Solicitudes especiales';

  @override
  String get developedByUTA => 'Desarrollado por Universal Technologies Africa';

  @override
  String get tapToContinue => 'Toca para continuar';

  @override
  String get changeLanguage => 'Cambiar idioma';

  @override
  String get monthJanuary => 'enero';

  @override
  String get monthFebruary => 'febrero';

  @override
  String get monthMarch => 'marzo';

  @override
  String get monthApril => 'abril';

  @override
  String get monthMay => 'mayo';

  @override
  String get monthJune => 'junio';

  @override
  String get monthJuly => 'julio';

  @override
  String get monthAugust => 'agosto';

  @override
  String get monthSeptember => 'septiembre';

  @override
  String get monthOctober => 'octubre';

  @override
  String get monthNovember => 'noviembre';

  @override
  String get monthDecember => 'diciembre';

  @override
  String get reservationClientCodeBanner =>
      'Las reservas están restringidas a clientes con una estancia válida. Ingrese su código de cliente a continuación (recibido al registrarse).';

  @override
  String get sessionExpiredNeedClientCode =>
      'Su estancia ya no está activa. Ingrese su código de cliente para reservar.';

  @override
  String get periodAllDates => 'Todas las fechas';

  @override
  String get periodToday => 'Hoy';

  @override
  String get periodThisWeek => 'Esta semana';

  @override
  String get periodThisMonth => 'Este mes';

  @override
  String get staffOrdersTitle => 'Pedidos de Servicio a la Habitación';

  @override
  String get staffOrdersSubtitle =>
      'Seguimiento y procesamiento de pedidos de servicio a la habitación';

  @override
  String addedToCart(String itemName) {
    return '$itemName añadido al carrito';
  }

  @override
  String get invalidSessionRetry =>
      'Sesión inválida o expirada. Por favor, introduzca su código nuevamente.';

  @override
  String get orderValidationError =>
      'No se puede validar el pedido. Verifique su código de cliente o contacte a la recepción.';

  @override
  String get enterCodeToValidate =>
      'Ingrese el código recibido en el registro para validar el pedido.';

  @override
  String get roomNumberTablet => 'Número de habitación (esta tableta)';

  @override
  String get roomIdRecommended =>
      'ID de habitación (recomendado — ver panel de control > Acceso de tabletas)';

  @override
  String get multiHotelWarning =>
      'Para múltiples propiedades, introduzca el ID de Habitación para mostrar únicamente los datos de su hotel.';

  @override
  String get code6Digits => 'Código de 6 dígitos';

  @override
  String get enter6Digits => 'Introduzca el código de 6 dígitos.';

  @override
  String get defineRoomTablet =>
      'Defina el número de habitación de esta tableta.';

  @override
  String get confirmIdentity => 'Confirme su identidad';

  @override
  String get verifyInfoBeforeOrder =>
      'Verifique su información antes de enviar el pedido:';

  @override
  String get identityName => 'Nombre';

  @override
  String get identityRoom => 'Habitación';

  @override
  String get identityPhone => 'Teléfono';

  @override
  String get identityEmail => 'Correo electrónico';

  @override
  String get paymentMethodText => 'Método de pago';

  @override
  String get paymentCash => 'Efectivo';

  @override
  String get paymentRoomBill => 'Cargar a la habitación';

  @override
  String get paymentWave => 'Wave';

  @override
  String get paymentOrangeMoney => 'Orange Money';

  @override
  String get confirmOrder => 'Confirmar pedido';

  @override
  String get statusUpdated => 'Estado actualizado';

  @override
  String get orderStatusUpdated => 'Estado del pedido actualizado';

  @override
  String cannotSendNotification(String error) {
    return 'No se puede enviar la notificación: $error';
  }

  @override
  String get orderCancelledNotified => 'Pedido cancelado — cliente notificado.';

  @override
  String get orderCancelled => 'Pedido cancelado';

  @override
  String get cancelOrder => 'Cancelar pedido';

  @override
  String get yesCancel => 'Sí, cancelar';

  @override
  String get spaCategoryAll => 'Todos';

  @override
  String get spaCategoryMassage => 'Masajes';

  @override
  String get spaCategoryFacial => 'Tratamientos faciales';

  @override
  String get spaCategoryBody => 'Tratamientos corporales';

  @override
  String get spaCategoryHammam => 'Hammam';

  @override
  String get spaAndWellness => 'Spa y Bienestar';

  @override
  String get spaServiceFallback => 'Servicio de Spa';

  @override
  String get timeLabel => 'Hora';

  @override
  String get excursionFallback => 'Excursión';

  @override
  String get locationEnableSettings =>
      'Habilitar la ubicación en la configuración.';

  @override
  String get locationAccessDenied => 'Acceso a la ubicación denegado.';

  @override
  String get locationCurrentPos => 'Posición actual';

  @override
  String get locationError => 'No se puede obtener la ubicación';

  @override
  String get vehicleRequestType => 'Tipo de solicitud';

  @override
  String get vehicleTypeTaxi => 'Taxi';

  @override
  String get vehicleTypeRental => 'Alquiler';

  @override
  String get taxiPickup => 'Recogida';

  @override
  String get taxiPickupHint => 'Dirección o ubicación';

  @override
  String get taxiMyLocation => 'Mi ubicación';

  @override
  String get taxiDestination => 'Destino';

  @override
  String get taxiDestinationHint => 'Dirección de destino';

  @override
  String get taxiDistanceOption => 'Distancia (km, opcional)';

  @override
  String get taxiDistanceHint => 'Ej: 5.2';

  @override
  String get rentalAllTypes => 'Todos los tipos';

  @override
  String get rentalSedan => 'Sedán';

  @override
  String get rentalSuv => 'SUV';

  @override
  String get rentalMinibus => 'Minibús';

  @override
  String get rentalVan => 'Furgoneta';

  @override
  String get rentalOther => 'Otro';

  @override
  String get rentalChooseVehicle => 'Elige un vehículo';

  @override
  String get rentalTypeLabel => 'Tipo';

  @override
  String get rentalSeatsMin => 'Plazas min.';

  @override
  String get rentalSeatsAll => 'Todas';

  @override
  String rentalSeatsCount(String count) {
    return '$count plaza(s)';
  }

  @override
  String get rentalNoVehicleFound => 'Ningún vehículo para estos criterios.';

  @override
  String rentalSeatsPl(String count) {
    return '$count pl.';
  }

  @override
  String get rentalDaysHint => 'Ej: 2';

  @override
  String get rentalDurationHours => 'Duración (horas)';

  @override
  String get rentalDurationHint => 'Ej: 8 (medio día si ≤ 5 h)';

  @override
  String rentalEstimate(String price) {
    return 'Estimación: $price FCFA';
  }

  @override
  String get rentalErrorDestination => 'Indique la dirección de destino.';

  @override
  String get rentalErrorChooseVehicle => 'Elige un vehículo de la lista.';

  @override
  String get rentalErrorVehicleOrDetails =>
      'Elija Taxi o Alquiler, o describa su solicitud.';

  @override
  String get sessionExpiredNeedClientCodeRequest =>
      'Su estadía ya no está activa. Ingrese su código de cliente para la solicitud.';

  @override
  String get palaceConciergeServices => 'Servicios de Palacio / Conserjería';

  @override
  String get palaceConciergeTracking =>
      'Seguimiento de solicitudes de palacio y conserjería';

  @override
  String get palaceConciergeServiceSingle =>
      'Servicio de Palacio / Conserjería';

  @override
  String scheduledForDate(String date) {
    return 'Programado para $date';
  }

  @override
  String get requestDetailsOnly => 'Detalles de la solicitud';

  @override
  String get cancellationReason => 'Motivo de cancelación';

  @override
  String get acceptRequestTitle => 'Aceptar solicitud';

  @override
  String get acceptRequestMessage =>
      '¿Aceptar esta solicitud de servicio de palacio / conserjería?';

  @override
  String get completeRequestTitle => 'Completar solicitud';

  @override
  String get completeRequestMessage => '¿Completar esta solicitud?';

  @override
  String get rejectRequestTitle => 'Rechazar solicitud';

  @override
  String get rejectRequestMessage =>
      '¿Rechazar esta solicitud de servicio de palacio?';

  @override
  String get cancelRequestMessage =>
      '¿Cancelar esta solicitud de servicio de palacio?';

  @override
  String get cancellationReasonHint => 'Añade un motivo para la cancelación';

  @override
  String get validationReasonRequired => 'Por favor, indique un motivo.';

  @override
  String get requestDetailTitle =>
      'Detalle de solicitud de Palacio / Conserjería';

  @override
  String get laundryRequestDetailTitle =>
      'Detalle de la solicitud de lavandería';

  @override
  String get laundryNoItemsInRequest =>
      'No se encontraron artículos para esta solicitud.';

  @override
  String get hotelMap => 'Mapa';

  @override
  String get albumsTitle => 'Álbumes';

  @override
  String photoCount(int count) {
    return '$count foto(s)';
  }

  @override
  String get noPhoto => 'Ninguna foto';

  @override
  String get presentationTitle => 'Presentación';

  @override
  String get addressTitle => 'Dirección';

  @override
  String get phoneAbbr => 'Tel.';

  @override
  String get staffEmergencySubtitle => 'Alertas de médico / seguridad activas';

  @override
  String get guestEmergencySubtitle =>
      'Sus solicitudes de Asistencia y Emergencia';

  @override
  String get noEmergencyAlerts =>
      'No hay alertas de Asistencia y Emergencia activas.';

  @override
  String get acceptEmergencyAlertMessage =>
      '¿Aceptar esta alerta de Asistencia y Emergencia?';

  @override
  String get cancelEmergencyAlertMessage => '¿Cancelar esta alerta?';

  @override
  String get reasonOptional => 'Motivo (opcional)';

  @override
  String get reasonRequired => 'Proporcione un motivo.';

  @override
  String get accept => 'Aceptar';

  @override
  String get unidentifiedRoom => 'Habitación no identificada';

  @override
  String requestFromRoom(String roomInfo) {
    return 'Solicitud desde $roomInfo';
  }

  @override
  String get newStaffMessage => 'Nuevo mensaje del personal';

  @override
  String get startConversation => 'Inicie la conversación con la recepción.';

  @override
  String get newMessageSingular => '1 mensaje nuevo';

  @override
  String get newMessagesPlural => 'nuevos mensajes';

  @override
  String get yesterday => 'Ayer';

  @override
  String get imageUnavailable => 'Imagen no disponible';

  @override
  String get voiceMessage => 'Mensaje de voz';

  @override
  String get microphonePermission =>
      'Permita el acceso al micrófono para enviar una nota de voz.';

  @override
  String get sportCategory => 'Deporte';

  @override
  String get leisureCategory => 'Ocio';

  @override
  String get timeoutError => 'Tiempo de espera agotado. Compruebe su conexión.';

  @override
  String get viewMyRequests => 'Ver mis solicitudes';

  @override
  String get datePrefix => 'Fecha: ';

  @override
  String get timePrefix => 'Hora: ';

  @override
  String get requestDemandeSuffix => ' - Petición';

  @override
  String get sportFitnessCoachBooking => 'Sport & Fitness - Reserva entrenador';

  @override
  String get golfPrefix => 'Golf';

  @override
  String get tennisPrefix => 'Tenis';

  @override
  String get guidedToursNotConfigured =>
      'Visitas guiadas no configuradas. El establecimiento debe añadir el servicio \'Visitas guiadas personalizadas\' en el panel de control (Servicios Palace).';

  @override
  String get transfersNotConfigured =>
      'Servicio de Traslados no configurado. Contacte con el establecimiento.';

  @override
  String get pickupDestinationRequired =>
      'Indique el lugar de recogida y el destino.';

  @override
  String get exAirportHotel => 'Ej: Aeropuerto, Hotel…';

  @override
  String get exDowntownAddress => 'Ej: Centro, Dirección…';

  @override
  String get vehicleRentalNotConfigured =>
      'Servicio de alquiler de vehículos no configurado. Contacte con el establecimiento.';

  @override
  String get durationOrDaysRequired =>
      'Indique el número de días o la duración en horas.';

  @override
  String get exDays => 'Ej: 2';

  @override
  String get exHours => 'Ej: 5 (medio día)';

  @override
  String get profileRoom => 'Habitación';

  @override
  String get profileHotel => 'Hotel';

  @override
  String get profileRole => 'Rol';

  @override
  String get myInvoices => 'Mis Facturas';

  @override
  String get phone => 'Teléfono';

  @override
  String get noInvoicesTitle => 'No hay facturas';

  @override
  String get noInvoicesSubtitle => 'Aún no tienes pedidos completados.';

  @override
  String get generalError => 'Error';

  @override
  String get orderReceipt => 'RECIBO DEL PEDIDO';

  @override
  String get hotelLabel => 'HOTEL:';

  @override
  String get orderDateLabel => 'FECHA PEDIDO:';

  @override
  String get deliveryLabel => 'ENTREGA:';

  @override
  String get noItems => 'Sin artículos';

  @override
  String get totalToPay => 'TOTAL A PAGAR';

  @override
  String get taxesIncluded => 'Impuestos y cargos por servicio incluidos';

  @override
  String get thankYouForOrder => '¡Gracias por su pedido!';

  @override
  String get invoiceRoomLabel => 'HABITACIÓN:';

  @override
  String get notificationsMarkedAsRead => 'Notificaciones marcadas como leídas';

  @override
  String get notificationsDeleted => 'Notificaciones eliminadas';
}
