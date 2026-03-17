// ignore: unused_import
import 'package:intl/intl.dart' as intl;
import 'app_localizations.dart';

// ignore_for_file: type=lint

/// The translations for Arabic (`ar`).
class AppLocalizationsAr extends AppLocalizations {
  AppLocalizationsAr([String locale = 'ar']) : super(locale);

  @override
  String get appTitle => 'Teranga Guest';

  @override
  String get login => 'تسجيل الدخول';

  @override
  String get email => 'البريد الإلكتروني';

  @override
  String get password => 'كلمة المرور';

  @override
  String get rememberMe => 'تذكرني';

  @override
  String get loginButton => 'دخول';

  @override
  String get loginError => 'خطأ في الاتصال';

  @override
  String get emailRequired => 'يرجى إدخال بريدك الإلكتروني';

  @override
  String get emailInvalid => 'بريد إلكتروني غير صالح';

  @override
  String get passwordRequired => 'يرجى إدخال كلمة المرور';

  @override
  String get passwordTooShort => 'كلمة المرور قصيرة جداً (6 أحرف على الأقل)';

  @override
  String get myProfile => 'ملفي';

  @override
  String get myHistories => 'سجلاتي';

  @override
  String get myFavorites => 'المفضلة';

  @override
  String get myOrders => 'طلباتي';

  @override
  String get myRestaurantReservations => 'حجوزات المطعم';

  @override
  String get mySpaReservations => 'حجوزات السبا';

  @override
  String get myExcursions => 'جولاتي';

  @override
  String get myLaundryRequests => 'طلبات الغسيل';

  @override
  String get myPalaceRequests => 'طلبات القصر';

  @override
  String get settings => 'الإعدادات';

  @override
  String get changePassword => 'تغيير كلمة المرور';

  @override
  String get about => 'حول';

  @override
  String get contactSupport => 'اتصل بالدعم';

  @override
  String get logout => 'تسجيل الخروج';

  @override
  String get logoutConfirm => 'هل أنت متأكد من تسجيل الخروج؟';

  @override
  String get cancel => 'إلغاء';

  @override
  String get version => 'الإصدار';

  @override
  String get preferences => 'التفضيلات';

  @override
  String get notifications => 'الإشعارات';

  @override
  String get notificationsOn => 'الإشعارات مفعلة';

  @override
  String get notificationsOff => 'الإشعارات معطلة';

  @override
  String get application => 'التطبيق';

  @override
  String get language => 'اللغة';

  @override
  String get french => 'Français';

  @override
  String get english => 'English';

  @override
  String get welcomeTitle => 'مرحباً بكم في فندق قصر الملك فهد';

  @override
  String welcomeToEnterprise(String enterpriseName) {
    return 'مرحباً بكم في $enterpriseName';
  }

  @override
  String get welcomeSubtitle => 'مساعدكم الرقمي في خدمتكم';

  @override
  String get roomService => 'خدمة الغرف';

  @override
  String get restaurantsBars => 'المطاعم والبارات';

  @override
  String get spaWellness => 'السبا والعافية';

  @override
  String get wellnessSportLeisure => 'العافية، الرياضة والترفيه';

  @override
  String get wellnessSportLeisureSubtitle => 'سبا، غولف، تنس، لياقة';

  @override
  String get golfTitle => 'غولف';

  @override
  String get golfSubtitle => 'حجز تي-تايم واستئجار المعدات';

  @override
  String get tennisTitle => 'تنس';

  @override
  String get tennisSubtitle => 'حجز الملاعب واستئجار المعدات';

  @override
  String get golfTennisTitle => 'غولف وتنس';

  @override
  String get golfTennisSubtitle => 'حجز تي-تايم والم courts واستئجار المعدات';

  @override
  String get golfTennisTeetime => 'حجز تي-تايم';

  @override
  String get golfTennisCourt => 'ملعب تنس';

  @override
  String get golfTennisEquipment => 'استئجار معدات';

  @override
  String get sportFitnessTitle => 'الرياضة واللياقة';

  @override
  String get sportFitnessSubtitle => 'ساعات القاعة وحجز مدرب شخصي';

  @override
  String get sportFitnessGymHours => 'ساعات القاعة الرياضية';

  @override
  String get sportFitnessBookCoach => 'حجز مدرب شخصي';

  @override
  String get gymHoursDefault => 'استفسر من الاستقبال عن الساعات.';

  @override
  String get palaceServices => 'خدمات القصر';

  @override
  String get explorationMobility => 'الاستكشاف والتنقل';

  @override
  String get explorationMobilitySubtitle =>
      'تأجير السيارات، استكشاف، جولات ونقل';

  @override
  String get vehicleRental => 'تأجير سيارات';

  @override
  String get vehicleRentalDesc =>
      'كتالوج تفاعلي للسيارات مع حجز مباشر وخيارات راحة.';

  @override
  String get sitesTouristiques => 'استكشاف ومواقع سياحية';

  @override
  String get sitesTouristiquesDesc =>
      'أماكن لا تفوت: بحيرة ريتبا، جزيرة غوريه، بلاتو... صور ووصف مفصل.';

  @override
  String get guidedTours => 'جولات سياحية مخصصة';

  @override
  String get guidedToursDesc =>
      'احجز مرشدين معتمدين لجولات ثقافية، تذوق الطعام أو تاريخية.';

  @override
  String get transfersVtc => 'نقل وسائق خاص';

  @override
  String get transfersVtcDesc => 'نقل من وإلى المطار أو سائق خاص لرحلات آمنة.';

  @override
  String get excursions => 'جولات';

  @override
  String get laundry => 'الغسيل';

  @override
  String get concierge => 'الكونسيرج';

  @override
  String get callCenter => 'مركز الاتصالات';

  @override
  String get hotelInfosSecurity => 'معلومات الفندق والأمن';

  @override
  String get hotelInfosSecuritySubtitle =>
      'كتيب الترحيب، المساعدة والطوارئ والشات بوت';

  @override
  String get guidesInfos => 'أدلة ومعلومات';

  @override
  String get hotelInfos => 'معلومات الفندق';

  @override
  String get hotelInfosDesc => 'واي فاي، خرائط، قواعد المنزل ومعلومات عملية';

  @override
  String get assistanceEmergency => 'المساعدة والطوارئ';

  @override
  String get assistanceEmergencyDesc =>
      'طلب طبيب أو الإبلاغ عن حالة طوارئ أمنية (غرفة محددة)';

  @override
  String get chatbotMultilingual => 'روبوت محادثة متعدد اللغات';

  @override
  String get chatbotDesc => 'مساعد رقمي متاح 24/7';

  @override
  String get gallery => 'المعرض';

  @override
  String get galleryDesc => 'صور المنشأة والألبومات';

  @override
  String get ourEstablishments => 'منشآتنا';

  @override
  String get ourEstablishmentsDesc => 'مواقع أخرى للمجموعة في البلاد';

  @override
  String get wifiCode => 'رمز واي فاي';

  @override
  String get wifiPassword => 'كلمة مرور واي فاي';

  @override
  String get houseRules => 'القواعد الداخلية';

  @override
  String get practicalInfo => 'معلومات عملية';

  @override
  String get requestDoctor => 'طلب طبيب';

  @override
  String get reportSecurityEmergency => 'الإبلاغ عن حالة طوارئ أمنية';

  @override
  String roomLabel(String room) {
    return 'الغرفة: $room';
  }

  @override
  String get emergencyRequestSent => 'تم إرسال الطلب. سيتصل بك الفريق.';

  @override
  String get noActiveStayForEmergency =>
      'يجب أن يكون لديك إقامة نشطة. سجّل الدخول بحساب الغرفة أو اتصل بالاستقبال.';

  @override
  String get assistanceDoctorNotConfigured =>
      'خدمة طلب الطبيب غير مفعلة لهذا المنشأة.';

  @override
  String get assistanceSecurityNotConfigured =>
      'خدمة طوارئ الأمن غير مفعلة لهذا المنشأة.';

  @override
  String confirmEmergencyAction(String action) {
    return 'هل تريد التأكيد: $action؟';
  }

  @override
  String get deleteConversationConfirm => 'حذف هذه المحادثة؟';

  @override
  String get deleteConversation => 'حذف المحادثة';

  @override
  String get messageDeleted => 'تم حذف الرسالة';

  @override
  String get reply => 'رد';

  @override
  String get deleteMessage => 'حذف الرسالة';

  @override
  String get chatbotComingSoon => 'قريباً';

  @override
  String get chatbotComingSoonHint =>
      'الشات بوت متعدد اللغات سيكون متاحاً قريباً.';

  @override
  String get servicesChambreLogistique => 'خدمة الغرف';

  @override
  String get roomServiceRestauration => 'خدمة الغرف والمطاعم';

  @override
  String get roomServiceRestaurationDesc =>
      'قائمة رقمية لطلب وجبات الطعام والمشروبات مع تتبع التحضير في الوقت الفعلي.';

  @override
  String get laundryDesc => 'جدول أسعار تفاعلي وطلب استلام فوري للملابس.';

  @override
  String get amenitiesConcierge => 'وسائل الراحة والكونسيرج';

  @override
  String get amenitiesConciergeDesc =>
      'طلب مبسط لمستلزمات الحمام أو وسائد إضافية أو طقم حلاقة أو أي خدمة أخرى دون الحاجة للاتصال.';

  @override
  String get minibarIntelligent => 'ميني بار ذكي';

  @override
  String get minibarIntelligentDesc =>
      'جرد رقمي للمنتجات وإعلان مبسط للاستهلاك.';

  @override
  String get comingSoon => 'قريباً';

  @override
  String get amenityToiletries => 'مستلزمات الحمام';

  @override
  String get amenityPillows => 'وسائد إضافية';

  @override
  String get amenityShavingKit => 'طقم حلاقة';

  @override
  String get amenityOther => 'طلب آخر';

  @override
  String get amenityDetailsHint => 'حدد إن لزم (اختياري)';

  @override
  String get amenitySelectQuantities => 'اختر المنتجات والكميات';

  @override
  String get amenityPillowCount => 'عدد الوسائد';

  @override
  String get amenityOtherDetailsHint => 'صف طلبك (اختياري)';

  @override
  String get amenityItemSoap => 'صابون';

  @override
  String get amenityItemShampoo => 'شامبو';

  @override
  String get amenityItemToothpaste => 'معجون أسنان';

  @override
  String get amenityItemToothbrush => 'فرشاة أسنان';

  @override
  String get amenityItemComb => 'مشط';

  @override
  String get amenityItemTowels => 'مناشف';

  @override
  String get amenityItemPillow => 'وسادة إضافية';

  @override
  String get amenityItemRazor => 'ماكينة حلاقة';

  @override
  String get amenityItemShavingFoam => 'رغوة حلاقة';

  @override
  String get amenityItemAfterShave => 'لوشن بعد الحلاقة';

  @override
  String get amenityItemBlades => 'شفرة حلاقة';

  @override
  String get back => 'رجوع';

  @override
  String get retry => 'إعادة المحاولة';

  @override
  String get error => 'خطأ';

  @override
  String get errorHint => 'تحقق من اتصالك وأعد المحاولة.';

  @override
  String get close => 'إغلاق';

  @override
  String get ok => 'موافق';

  @override
  String get save => 'حفظ';

  @override
  String get noFavorites => 'لا مفضلات';

  @override
  String get noFavoritesHint =>
      'أضف عناصر أو مطاعم أو علاجات أو جولات إلى المفضلة من صفحاتها.';

  @override
  String get contactSupportTitle => 'اتصل بالدعم';

  @override
  String get chooseContact => 'اختر وسيلة الاتصال:';

  @override
  String get aboutDescription =>
      'تطبيق الترحيب والخدمات لضيوف فندق قصر الملك فهد.';

  @override
  String get hotelName => 'فندق قصر الملك فهد';

  @override
  String get noUser => 'لا يوجد مستخدم متصل';

  @override
  String get addToCart => 'إضافة إلى السلة';

  @override
  String get viewCart => 'عرض السلة';

  @override
  String get cart => 'السلة';

  @override
  String get emptyCart => 'سلتك فارغة';

  @override
  String get emptyCartHint => 'أضف عناصر للطلب.';

  @override
  String get browseMenu => 'تصفح القائمة';

  @override
  String get clearCartConfirm => 'هل أنت متأكد من تفريغ السلة؟';

  @override
  String get clear => 'تفريغ';

  @override
  String get orderSuccess => 'تم تسجيل الطلب';

  @override
  String get orderSuccessHint => 'يمكنك متابعة طلبك في طلباتي.';

  @override
  String get home => 'الرئيسية';

  @override
  String get myBookings => 'حجوزاتي';

  @override
  String get excursionNotFound => 'الجولة غير موجودة';

  @override
  String get excursionNotFoundHint => 'هذه الجولة غير موجودة أو لم تعد متاحة.';

  @override
  String get serviceNotFound => 'الخدمة غير موجودة';

  @override
  String get serviceNotFoundHint => 'هذه الخدمة غير موجودة أو لم تعد متاحة.';

  @override
  String get restaurantNotFound => 'المطعم غير موجود';

  @override
  String get restaurantNotFoundHint => 'هذا المطعم غير موجود أو لم يعد متاحاً.';

  @override
  String get orderNotFound => 'الطلب غير موجود';

  @override
  String get orderNotFoundHint => 'هذا الطلب غير موجود أو تم حذفه.';

  @override
  String get noLaundryService => 'لا توجد خدمة غسيل متاحة';

  @override
  String get noLaundryServiceHint => 'ستظهر خدمات التنظيف هنا.';

  @override
  String get noSpaService => 'لا توجد خدمة سبا متاحة';

  @override
  String get noSpaServiceInCategory => 'لا خدمة في هذه الفئة';

  @override
  String get noSpaServiceHint => 'ستُعرض العلاجات والتدليك هنا.';

  @override
  String get noPalaceService => 'لا توجد خدمة قصر متاحة';

  @override
  String get noPalaceServiceHint => 'ستُعرض الخدمات المميزة هنا.';

  @override
  String get noExcursionAvailable => 'لا توجد جولة متاحة';

  @override
  String get noExcursionAvailableHint => 'ستُعرض الأنشطة والجولات هنا.';

  @override
  String get noOrder => 'لا طلبات';

  @override
  String noOrderForStatus(String status) {
    return 'لا طلبات $status';
  }

  @override
  String get noOrderSubtitle => 'ستظهر طلبات خدمة الغرف هنا.';

  @override
  String get orderStatusPending => 'قيد الانتظار';

  @override
  String get orderStatusConfirmed => 'مؤكد';

  @override
  String get orderStatusPreparing => 'قيد التحضير';

  @override
  String get orderStatusDelivering => 'قيد التوصيل';

  @override
  String get orderStatusDelivered => 'تم التوصيل';

  @override
  String get noRestaurantAvailable => 'لا مطعم متاح';

  @override
  String noRestaurantForType(String type) {
    return 'لا $type متاح';
  }

  @override
  String get noRestaurantSubtitle => 'ستُعرض المطاعم والبارات هنا.';

  @override
  String get typeRestaurant => 'مطعم';

  @override
  String get typeBar => 'بار';

  @override
  String get typeCafe => 'مقهى';

  @override
  String get typeLounge => 'صالة';

  @override
  String get noItemAvailable => 'لا عنصر متاح';

  @override
  String get noSearchResult => 'لا نتائج';

  @override
  String get noItemSubtitle => 'ستُعرض عناصر هذه الفئة هنا.';

  @override
  String get tryAnotherSearch => 'جرب كلمة بحث أخرى.';

  @override
  String get noCategoryAvailable => 'لا فئة متاحة';

  @override
  String get noCategoryHint => 'قائمة خدمة الغرف ستكون متاحة هنا.';

  @override
  String get noPalaceRequest => 'لا طلب قصر';

  @override
  String get noPalaceRequestHint => 'أرسل طلباً من خدمات القصر.';

  @override
  String get noLaundryRequest => 'لا طلب غسيل';

  @override
  String get noLaundryRequestHint => 'أرسل طلباً من خدمة الغسيل.';

  @override
  String get noExcursionBooked => 'لا جولة محجوزة';

  @override
  String get noExcursionBookedHint => 'احجز جولة من قائمة الأنشطة.';

  @override
  String get noSpaReservation => 'لا حجز سبا';

  @override
  String get noSpaReservationHint => 'احجز علاجاً من قائمة خدمات السبا.';

  @override
  String get noRestaurantReservation => 'لا حجز مطعم';

  @override
  String get noRestaurantReservationHint => 'احجز طاولة من قائمة المطاعم.';

  @override
  String get itemNotFound => 'العنصر غير موجود';

  @override
  String get filterAll => 'الكل';

  @override
  String get filterPending => 'قيد الانتظار';

  @override
  String get filterConfirmed => 'مؤكدة';

  @override
  String get filterPreparing => 'قيد التحضير';

  @override
  String get filterDelivering => 'قيد التوصيل';

  @override
  String get filterDelivered => 'تم التوصيل';

  @override
  String get filterAllTypes => 'الكل';

  @override
  String get filterRestaurants => 'مطاعم';

  @override
  String get filterBars => 'بارات';

  @override
  String get filterCafes => 'مقاهي';

  @override
  String get filterLounges => 'صالات';

  @override
  String get ordersHistorySubtitle => 'السجل والمتابعة';

  @override
  String get arabic => 'العربية';

  @override
  String get spanish => 'Español';

  @override
  String get discoverRestaurants => 'اكتشف أماكننا';

  @override
  String get reservationsConfirmed => 'حجوزات مؤكدة';

  @override
  String get myRequests => 'طلباتي';

  @override
  String requestNumber(int id) {
    return 'طلب #$id';
  }

  @override
  String get palaceServicesSubtitle => 'خدمات مميزة وكونسيرج';

  @override
  String get spaWellnessSubtitle => 'العافية والاسترخاء';

  @override
  String get myReservations => 'حجوزاتي';

  @override
  String get discoverRegion => 'اكتشف منطقتنا';

  @override
  String get spaSubtitle => 'راحة واسترخاء';

  @override
  String get chooseCategory => 'اختر فئة';

  @override
  String get orderDetailTitle => 'تفاصيل الطلب';

  @override
  String get orderTrackingSubtitle => 'متابعة طلبك';

  @override
  String adultPrice(String price) {
    return 'بالغ: $price';
  }

  @override
  String childPrice(String price) {
    return 'طفل: $price';
  }

  @override
  String get book => 'حجز';

  @override
  String get unavailable => 'غير متاح';

  @override
  String get spaServiceDefaultName => 'خدمة السبا';

  @override
  String capacityPeople(int count) {
    return 'السعة: $count أشخاص';
  }

  @override
  String get openingHours => 'ساعات العمل';

  @override
  String get bookTable => 'حجز طاولة';

  @override
  String get closed => 'مغلق';

  @override
  String get search => 'بحث...';

  @override
  String get reviewOrder => 'راجع طلبك';

  @override
  String get specialInstructions => 'تعليمات خاصة';

  @override
  String get specialInstructionsHint => 'حساسية، تفضيلات، تعليمات التوصيل...';

  @override
  String get description => 'الوصف';

  @override
  String get specialInstructionsExample => 'مثلاً: بدون بصل، مطهو جيداً...';

  @override
  String get viewCartCaps => 'عرض السلة';

  @override
  String get passwordChangedSuccess => 'تم تغيير كلمة المرور بنجاح';

  @override
  String get currentPassword => 'كلمة المرور الحالية';

  @override
  String get newPassword => 'كلمة المرور الجديدة';

  @override
  String get confirmPassword => 'تأكيد كلمة المرور الجديدة';

  @override
  String get fieldRequired => 'حقل مطلوب';

  @override
  String get minChars => '8 أحرف على الأقل';

  @override
  String get needUpperCase => 'يجب أن تحتوي على حرف كبير';

  @override
  String get needDigit => 'يجب أن تحتوي على رقم';

  @override
  String get passwordsDoNotMatch => 'كلمات المرور غير متطابقة';

  @override
  String get passwordRulesHint =>
      'يجب أن تحتوي كلمة المرور الجديدة على 8 أحرف على الأقل وحرف كبير ورقم.';

  @override
  String get requestDetails => 'تفاصيل طلبك';

  @override
  String get describeRequest => 'صف طلبك بالتفصيل...';

  @override
  String get preferredTimeOptional => 'الوقت المفضل (اختياري)';

  @override
  String get selectDateAndTime => 'اختر التاريخ والوقت';

  @override
  String get sendRequest => 'إرسال الطلب';

  @override
  String get requestReservationHint =>
      'أدخل التاريخ والوقت والتفاصيل في النموذج.';

  @override
  String get requestSent => 'تم إرسال الطلب!';

  @override
  String get requestSentMessage => 'تم تسجيل طلب الخدمة.';

  @override
  String get confirmRequest => 'تأكيد الطلب';

  @override
  String get selectedItems => 'العناصر المختارة';

  @override
  String get total => 'المجموع';

  @override
  String get specialInstructionsOptional => 'تعليمات خاصة (اختياري)';

  @override
  String get laundryInstructionsExample =>
      'مثلاً: منظف عديم الرائحة، كي خفيف...';

  @override
  String get date => 'التاريخ';

  @override
  String get selectDate => 'اختر تاريخاً';

  @override
  String get participants => 'المشاركون';

  @override
  String get adults => 'بالغون';

  @override
  String get children => 'أطفال';

  @override
  String get specialRequestsOptional => 'طلبات خاصة (اختياري)';

  @override
  String get summary => 'الملخص';

  @override
  String get confirmReservation => 'تأكيد الحجز';

  @override
  String get reservationConfirmed => 'تم تأكيد الحجز!';

  @override
  String excursionConfirmedMessage(int count) {
    return 'جولتك لـ $count شخص(أشخاص) مؤكدة.';
  }

  @override
  String get confirmationNotification => 'ستتلقى تأكيداً عبر الإشعار.';

  @override
  String get numberOfGuests => 'عدد الأشخاص';

  @override
  String tableReservedMessage(int count) {
    return 'طاولتك لـ $count شخص(أشخاص) محجوزة.';
  }

  @override
  String get orderConfirmed => 'تم تأكيد الطلب!';

  @override
  String get itemsAddedToCart => 'تمت إضافة العناصر إلى السلة!';

  @override
  String get errorPrefix => 'خطأ: ';

  @override
  String get cannotOpenLink => 'تعذر فتح الرابط';

  @override
  String get included => 'مشمول:';

  @override
  String get schedule => 'الجدول';

  @override
  String get childrenAgeRange => 'الأطفال (العمر)';

  @override
  String get reviewsTitle => 'الآراء';

  @override
  String get reviewsPending => 'للتقييم';

  @override
  String get reviewsMyReviews => 'تقييماتي';

  @override
  String get reviewsNoPending => 'لا توجد تقييمات معلقة';

  @override
  String get reviewsNoPendingHint =>
      'يُعرض التقييم بعد الطلب المُسلّم أو المغادرة أو الطلب المنجز أو الرحلة المنتهية.';

  @override
  String get reviewsNoReviewsYet => 'لا توجد تقييمات بعد';

  @override
  String get reviewsNoReviewsYetHint => 'ستظهر تقييماتك هنا.';

  @override
  String get rateYourExperience => 'كيف كانت تجربتك؟';

  @override
  String get optionalComment => 'تعليق (اختياري)';

  @override
  String get submit => 'إرسال';

  @override
  String get thankYouForReview => 'شكراً على تقييمك!';

  @override
  String get orderTracking => 'متابعة الطلب';

  @override
  String get orderItems => 'العناصر المطلوبة';

  @override
  String get quantity => 'الكمية';

  @override
  String get reorder => 'إعادة الطلب';

  @override
  String get laundryRequestSentMessage => 'تم تسجيل طلب الغسيل.';

  @override
  String get demand => 'طلب';

  @override
  String get reserve => 'حجز';

  @override
  String get myExcursionsShort => 'جولاتي';

  @override
  String get categoryFacial => 'العناية بالوجه';

  @override
  String get categoryBody => 'العناية بالجسم';

  @override
  String get excursionsTitle => 'الجولات';

  @override
  String articleCount(int count) {
    return '$count عنصر(عناصر)';
  }

  @override
  String appNameVersion(String version, String v) {
    return 'TerangaGuest $version $v';
  }

  @override
  String orderLabel(int id) {
    return 'طلب $id';
  }

  @override
  String navigationTo(String name) {
    return 'الانتقال إلى $name';
  }

  @override
  String get open => 'مفتوح';

  @override
  String get itemsLabel => 'العناصر';

  @override
  String get statusLabel => 'الحالة';

  @override
  String get laundrySubtitle => 'خدمة التنظيف';

  @override
  String get spaHintExample => 'مثلاً: ضغط خفيف، زيت اللافندر...';

  @override
  String get restaurantHintExample => 'مثلاً: طاولة قرب النافذة، عيد ميلاد...';

  @override
  String get allergiesPreferencesExample => 'مثلاً: حساسية، تفضيلات...';

  @override
  String get orderConfirmedMessage => 'تم تسجيل طلبك بنجاح';

  @override
  String get orderNumberLabel => 'رقم الطلب';

  @override
  String get statusPending => 'قيد الانتظار';

  @override
  String get statusConfirmed => 'مؤكدة';

  @override
  String get statusPreparing => 'قيد التحضير';

  @override
  String get statusDelivering => 'قيد التوصيل';

  @override
  String get statusInProgress => 'قيد التنفيذ';

  @override
  String get statusCompleted => 'منتهية';

  @override
  String get statusCancelled => 'ملغاة';

  @override
  String get statusPickedUp => 'تم الاستلام';

  @override
  String get statusProcessing => 'قيد المعالجة';

  @override
  String get statusReady => 'جاهزة';

  @override
  String get statusDelivered => 'تم التوصيل';

  @override
  String get myCart => 'سلتي';

  @override
  String get orderNotificationHint =>
      'ستتلقى إشعاراً عند تأكيد الطلب من المطعم.';

  @override
  String spaReservationConfirmedMessage(String name) {
    return 'حجزك لـ $name مؤكد.';
  }

  @override
  String get excursion => 'جولة';

  @override
  String get verifyOrder => 'تحقق من طلبك';

  @override
  String get personsShort => 'شخص';

  @override
  String get amenities => 'المرافق';

  @override
  String get placeOrder => 'تأكيد الطلب';

  @override
  String get restaurant => 'مطعم';

  @override
  String get time => 'الوقت';

  @override
  String get guests => 'الأشخاص';

  @override
  String guestsCount(int count) {
    return '$count ضيف/ضيوف';
  }

  @override
  String get dayMonday => 'الإثنين';

  @override
  String get dayTuesday => 'الثلاثاء';

  @override
  String get dayWednesday => 'الأربعاء';

  @override
  String get dayThursday => 'الخميس';

  @override
  String get dayFriday => 'الجمعة';

  @override
  String get daySaturday => 'السبت';

  @override
  String get daySunday => 'الأحد';

  @override
  String get service => 'الخدمة';

  @override
  String get duration => 'المدة';

  @override
  String get reservationCancelledMessage => 'تم إلغاء الحجز.';

  @override
  String get cancelReservationConfirm => 'إلغاء هذا الحجز؟';

  @override
  String get vehicleRentalTitle => 'تأجير السيارات';

  @override
  String get vehicleRentalSubtitle => 'اختر مركبة وأرسل طلب الاستئجار.';

  @override
  String get noVehicleAvailable => 'لا توجد مركبات متاحة';

  @override
  String get noVehicleAvailableHint => 'ستعرض المنشأة المركبات قريباً.';

  @override
  String get requestVehicleRental => 'طلب هذه الاستئارة';

  @override
  String get rentalDate => 'التاريخ المفضل';

  @override
  String get rentalDuration => 'المدة (ساعات)';

  @override
  String get rentalDays => 'عدد الأيام';

  @override
  String get estimatedPrice => 'السعر التقديري';

  @override
  String get guidedToursTitle => 'جولات سياحية مخصصة';

  @override
  String get guidedToursSubtitle => 'أدخل التاريخ ونوع الجولة وعدد الأشخاص.';

  @override
  String get tourType => 'نوع الجولة';

  @override
  String get tourTypeCultural => 'ثقافي';

  @override
  String get tourTypeGastronomic => 'تذوق الطعام';

  @override
  String get tourTypeHistorical => 'تاريخي';

  @override
  String get transfersVtcTitle => 'نقل وسيارات خاصة';

  @override
  String get transfersVtcSubtitle =>
      'نقل من المطار أو سائق خاص. أدخل مكان الانطلاق والوجهة.';

  @override
  String get pickupPlace => 'مكان الانطلاق';

  @override
  String get destinationPlace => 'الوجهة';

  @override
  String get sitesTouristiquesTitle => 'اكتشاف والمواقع السياحية';

  @override
  String get sitesTouristiquesSubtitle => 'أهم الأماكن مع صور ووصف.';

  @override
  String get filterVehicleType => 'النوع';

  @override
  String get filterMinSeats => 'الحد الأدنى للمقاعد';

  @override
  String get vehicleTypeBerline => 'سيدان';

  @override
  String get vehicleTypeSuv => 'دفع رباعي';

  @override
  String get vehicleTypeMinibus => 'ميني باص';

  @override
  String get vehicleTypeVan => 'فان';

  @override
  String get vehicleTypeOther => 'أخرى';

  @override
  String get validate => 'تحقق';

  @override
  String get clientCode => 'رمز العميل';

  @override
  String get clientCodeHint => 'رمز العميل (مثال: 123456)';

  @override
  String get invalidClientCode => 'رمز عميل غير صالح';

  @override
  String get clientLoginTitle => 'تسجيل دخول العميل';

  @override
  String get pleaseEnterClientCode => 'يرجى إدخال رمز العميل';

  @override
  String get accessMyRoom => 'الدخول إلى غرفتي';

  @override
  String get specialRequests => 'طلبات خاصة';

  @override
  String get developedByUTA =>
      'تم التطوير بواسطة Universal Technologies Africa';

  @override
  String get tapToContinue => 'اضغط للمتابعة';

  @override
  String get changeLanguage => 'تغيير اللغة';

  @override
  String get monthJanuary => 'يناير';

  @override
  String get monthFebruary => 'فبراير';

  @override
  String get monthMarch => 'مارس';

  @override
  String get monthApril => 'أبريل';

  @override
  String get monthMay => 'مايو';

  @override
  String get monthJune => 'يونيو';

  @override
  String get monthJuly => 'يوليو';

  @override
  String get monthAugust => 'أغسطس';

  @override
  String get monthSeptember => 'سبتمبر';

  @override
  String get monthOctober => 'أكتوبر';

  @override
  String get monthNovember => 'نوفمبر';

  @override
  String get monthDecember => 'ديسمبر';

  @override
  String get reservationClientCodeBanner =>
      'الحجوزات مقتصرة على الضيوف الذين لديهم إقامة صالحة. يرجى إدخال رمز العميل الخاص بك أدناه (تم استلامه عند تسجيل الدخول).';

  @override
  String get sessionExpiredNeedClientCode =>
      'إقامتك لم تعد نشطة. أدخل رمز العميل الخاص بك للحجز.';

  @override
  String get periodAllDates => 'كل التواريخ';

  @override
  String get periodToday => 'اليوم';

  @override
  String get periodThisWeek => 'هذا الأسبوع';

  @override
  String get periodThisMonth => 'هذا الشهر';

  @override
  String get staffOrdersTitle => 'طلبات خدمة الغرف';

  @override
  String get staffOrdersSubtitle => 'تتبع ومعالجة طلبات خدمة الغرف';

  @override
  String addedToCart(String itemName) {
    return 'تمت إضافة $itemName إلى سلة التسوق';
  }

  @override
  String get invalidSessionRetry =>
      'جلسة غير صالحة أو منتهية الصلاحية. الرجاء إدخال الرمز الخاص بك مرة أخرى.';

  @override
  String get orderValidationError =>
      'تعذر التحقق من صحة الطلب. يرجى التحقق من رمز العميل الخاص بك أو الاتصال بمكتب الاستقبال.';

  @override
  String get enterCodeToValidate =>
      'أدخل الرمز المستلم عند تسجيل الوصول للتحقق من صحة الطلب.';

  @override
  String get roomNumberTablet => 'رقم الغرفة (هذا الجهاز اللوحي)';

  @override
  String get roomIdRecommended =>
      'معرّف الغرفة (موصى به - راجع لوحة القيادة > وصول الجهاز اللوحي)';

  @override
  String get multiHotelWarning =>
      'في إعدادات الفنادق المتعددة، أدخل معرّف الغرفة لعرض بيانات فندقك فقط.';

  @override
  String get code6Digits => 'رمز مكون من 6 أرقام';

  @override
  String get enter6Digits => 'أدخل الرمز المكون من 6 أرقام.';

  @override
  String get defineRoomTablet => 'حدد رقم الغرفة لهذا الجهاز اللوحي.';

  @override
  String get confirmIdentity => 'قم بتأكيد هويتك';

  @override
  String get verifyInfoBeforeOrder => 'تحقق من معلوماتك قبل إرسال الطلب:';

  @override
  String get identityName => 'الاسم';

  @override
  String get identityRoom => 'الغرفة';

  @override
  String get identityPhone => 'الهاتف';

  @override
  String get identityEmail => 'البريد الإلكتروني';

  @override
  String get paymentMethodText => 'طريقة الدفع';

  @override
  String get paymentCash => 'نقدًا';

  @override
  String get paymentRoomBill => 'أضف إلى فاتورة الغرفة';

  @override
  String get paymentWave => 'Wave';

  @override
  String get paymentOrangeMoney => 'Orange Money';

  @override
  String get confirmOrder => 'تأكيد الطلب';

  @override
  String get statusUpdated => 'تم تحديث الحالة';

  @override
  String get orderStatusUpdated => 'تم تحديث حالة الطلب';

  @override
  String cannotSendNotification(String error) {
    return 'تعذر إرسال الإشعار: $error';
  }

  @override
  String get orderCancelledNotified => 'تم إلغاء الطلب - تم إخطار العميل.';

  @override
  String get orderCancelled => 'تم إلغاء الطلب';

  @override
  String get cancelOrder => 'إلغاء الطلب';

  @override
  String get yesCancel => 'نعم، إلغاء';

  @override
  String get spaCategoryAll => 'الكل';

  @override
  String get spaCategoryMassage => 'تدليك';

  @override
  String get spaCategoryFacial => 'علاجات الوجه';

  @override
  String get spaCategoryBody => 'علاجات الجسم';

  @override
  String get spaCategoryHammam => 'حمام';

  @override
  String get spaAndWellness => 'سبا وعافية';

  @override
  String get spaServiceFallback => 'خدمة سبا';

  @override
  String get timeLabel => 'الوقت';

  @override
  String get excursionFallback => 'جولة';

  @override
  String get locationEnableSettings => 'تفعيل الموقع في الإعدادات.';

  @override
  String get locationAccessDenied => 'تم رفض الوصول إلى الموقع.';

  @override
  String get locationCurrentPos => 'الموقع الحالي';

  @override
  String get locationError => 'لا يمكن الحصول على الموقع';

  @override
  String get vehicleRequestType => 'نوع الطلب';

  @override
  String get vehicleTypeTaxi => 'سيارة أجرة';

  @override
  String get vehicleTypeRental => 'تأجير';

  @override
  String get taxiPickup => 'نصطحبكم من';

  @override
  String get taxiPickupHint => 'العنوان أو الموقع';

  @override
  String get taxiMyLocation => 'موقعي';

  @override
  String get taxiDestination => 'الوجهة';

  @override
  String get taxiDestinationHint => 'عنوان الوجهة';

  @override
  String get taxiDistanceOption => 'المسافة (كم، اختياري)';

  @override
  String get taxiDistanceHint => 'مثال: 5.2';

  @override
  String get rentalAllTypes => 'كل الأنواع';

  @override
  String get rentalSedan => 'سيدان';

  @override
  String get rentalSuv => 'دفع رباعي';

  @override
  String get rentalMinibus => 'حافلة صغيرة';

  @override
  String get rentalVan => 'شاحنة صغيرة';

  @override
  String get rentalOther => 'آخر';

  @override
  String get rentalChooseVehicle => 'اختر سيارة';

  @override
  String get rentalTypeLabel => 'النوع';

  @override
  String get rentalSeatsMin => 'الحد الأدنى للمقاعد';

  @override
  String get rentalSeatsAll => 'الكل';

  @override
  String rentalSeatsCount(String count) {
    return '$count مقعد';
  }

  @override
  String get rentalNoVehicleFound => 'لا توجد مركبة لهذه المعايير.';

  @override
  String rentalSeatsPl(String count) {
    return '$count مقاعد';
  }

  @override
  String get rentalDaysHint => 'مثال: 2';

  @override
  String get rentalDurationHours => 'المدة (ساعات)';

  @override
  String get rentalDurationHint => 'مثال: 8 (نصف يوم اذا كان اقل من 5 ساعات)';

  @override
  String rentalEstimate(String price) {
    return 'التقدير: $price فرنك أفريقي';
  }

  @override
  String get rentalErrorDestination => 'أدخل عنوان الوجهة.';

  @override
  String get rentalErrorChooseVehicle => 'اختر مركبة من القائمة.';

  @override
  String get rentalErrorVehicleOrDetails =>
      'اختر سيارة أجرة أو إيجار، أو صف طلبك.';

  @override
  String get sessionExpiredNeedClientCodeRequest =>
      'إقامتك لم تعد نشطة. أدخل رمز العميل لإرسال الطلب.';

  @override
  String get palaceConciergeServices => 'خدمات القصر / الكونسيرج';

  @override
  String get palaceConciergeTracking => 'تتبع طلبات القصر والكونسيرج';

  @override
  String get palaceConciergeServiceSingle => 'خدمة القصر / الكونسيرج';

  @override
  String scheduledForDate(String date) {
    return 'مقرر لـ $date';
  }

  @override
  String get requestDetailsOnly => 'تفاصيل الطلب';

  @override
  String get cancellationReason => 'سبب الإلغاء';

  @override
  String get acceptRequestTitle => 'قبول الطلب';

  @override
  String get acceptRequestMessage => 'قبول طلب خدمة القصر / الكونسيرج؟';

  @override
  String get completeRequestTitle => 'إكمال الطلب';

  @override
  String get completeRequestMessage => 'إكمال هذا الطلب؟';

  @override
  String get rejectRequestTitle => 'رفض الطلب';

  @override
  String get rejectRequestMessage => 'رفض طلب خدمة القصر؟';

  @override
  String get cancelRequestMessage => 'إلغاء طلب خدمة القصر هذا؟';

  @override
  String get cancellationReasonHint => 'سبب الإلغاء';

  @override
  String get validationReasonRequired => 'يرجى تقديم سبب.';

  @override
  String get requestDetailTitle => 'تفاصيل طلب القصر / الكونسيرج';

  @override
  String get laundryRequestDetailTitle => 'تفاصيل طلب غسيل الملابس';

  @override
  String get laundryNoItemsInRequest => 'لم يتم العثور على عناصر لهذا الطلب.';

  @override
  String get hotelMap => 'خريطة';

  @override
  String get albumsTitle => 'ألبومات';

  @override
  String photoCount(int count) {
    return '$count صورة';
  }

  @override
  String get noPhoto => 'لا توجد صورة';

  @override
  String get presentationTitle => 'عرض';

  @override
  String get addressTitle => 'عنوان';

  @override
  String get phoneAbbr => 'هاتف';

  @override
  String get staffEmergencySubtitle => 'تنبيهات الطبيب / الأمن النشطة';

  @override
  String get guestEmergencySubtitle => 'طلبات المساعدة والطوارئ الخاصة بك';

  @override
  String get noEmergencyAlerts => 'لا توجد تنبيهات مساعدة وطوارئ نشطة.';

  @override
  String get acceptEmergencyAlertMessage =>
      'هل تقبل تنبيه المساعدة والطوارئ هذا؟';

  @override
  String get cancelEmergencyAlertMessage => 'هل تريد إلغاء هذا التنبيه؟';

  @override
  String get reasonOptional => 'السبب (اختياري)';

  @override
  String get reasonRequired => 'يرجى تحديد السبب.';

  @override
  String get accept => 'قبول';

  @override
  String get unidentifiedRoom => 'غرفة غير محددة';

  @override
  String requestFromRoom(String roomInfo) {
    return 'طلب من $roomInfo';
  }

  @override
  String get newStaffMessage => 'رسالة جديدة من الموظفين';

  @override
  String get startConversation => 'ابدأ المحادثة مع الاستقبال.';

  @override
  String get newMessageSingular => 'رسالة جديدة واحدة';

  @override
  String get newMessagesPlural => 'رسائل جديدة';

  @override
  String get yesterday => 'أمس';

  @override
  String get imageUnavailable => 'الصورة غير متوفرة';

  @override
  String get voiceMessage => 'رسالة صوتية';

  @override
  String get microphonePermission =>
      'اسمح بالوصول إلى الميكروفون لإرسال ملاحظة صوتية.';

  @override
  String get sportCategory => 'الرياضة';

  @override
  String get leisureCategory => 'الترفيه';

  @override
  String get timeoutError => 'انتهت مهلة الطلب. يرجى التحقق من اتصالك.';

  @override
  String get viewMyRequests => 'عرض طلباتي';

  @override
  String get datePrefix => 'التاريخ: ';

  @override
  String get timePrefix => 'الوقت: ';

  @override
  String get requestDemandeSuffix => ' - طلب';

  @override
  String get sportFitnessCoachBooking => 'الرياضة واللياقة - حجز مدرب شخصي';

  @override
  String get golfPrefix => 'جولف';

  @override
  String get tennisPrefix => 'تنس';

  @override
  String get guidedToursNotConfigured =>
      'الجولات المصحوبة بمرشدين غير مهيأة. يجب أن تضيف المؤسسة خدمة الجولات المصحوبة بمرشدين مخصصة في لوحة القيادة.';

  @override
  String get transfersNotConfigured => 'خدمة النقل غير مهيأة. اتصل بالمؤسسة.';

  @override
  String get pickupDestinationRequired => 'يرجى تحديد مكان الالتقاء والوجهة.';

  @override
  String get exAirportHotel => 'مثال: المطار، الفندق…';

  @override
  String get exDowntownAddress => 'مثال: وسط المدينة، العنوان…';

  @override
  String get vehicleRentalNotConfigured =>
      'خدمة تأجير السيارات غير مهيأة. اتصل بالمؤسسة.';

  @override
  String get durationOrDaysRequired =>
      'يرجى تحديد عدد الأيام أو المدة بالساعات.';

  @override
  String get exDays => 'مثال: 2';

  @override
  String get exHours => 'مثال: 5 (نصف يوم)';

  @override
  String get profileRoom => 'الغرفة';

  @override
  String get profileHotel => 'الفندق';

  @override
  String get profileRole => 'الدور';

  @override
  String get myInvoices => 'فواتيري';

  @override
  String get phone => 'رقم الهاتف';

  @override
  String get noInvoicesTitle => 'لا توجد فواتير';

  @override
  String get noInvoicesSubtitle => 'ليس لديك أي طلبات مكتملة بعد.';

  @override
  String get generalError => 'خطأ';

  @override
  String displayError(String message) {
    return 'خطأ في العرض: $message';
  }

  @override
  String get orderReceipt => 'إيصال الطلب';

  @override
  String get hotelLabel => 'الفندق:';

  @override
  String get orderDateLabel => 'تاريخ الطلب:';

  @override
  String get deliveryLabel => 'التوصيل:';

  @override
  String get noItems => 'لا توجد عناصر';

  @override
  String get totalToPay => 'الإجمالي للدفع';

  @override
  String get taxesIncluded => 'شامل جميع الضرائب ورسوم الخدمة';

  @override
  String get thankYouForOrder => 'شكرا لطلبك!';

  @override
  String get invoiceRoomLabel => 'الغرفة:';

  @override
  String get notificationsMarkedAsRead => 'تم وضع علامة مقروءة على الإشعارات';

  @override
  String get notificationsDeleted => 'تم حذف الإشعارات';

  @override
  String get halfDayOption => 'نصف يوم';

  @override
  String get fullDayOption => 'يوم واحد';

  @override
  String get multipleDaysOption => 'أيام متعددة';

  @override
  String get allOption => 'الكل';

  @override
  String get currencyFcfa => 'فرنك CFA';

  @override
  String get currencyFcfaPerPiece => 'فرنك CFA/قطعة';

  @override
  String get scrollToTop => 'العودة للأعلى';

  @override
  String get staffLaundryRequestsTitle => 'طلبات الغسيل';

  @override
  String get staffLaundryRequestsSubtitle => 'متابعة طلبات الغسيل';

  @override
  String get call => 'اتصال';

  @override
  String get getDirections => 'كيفية الوصول';

  @override
  String get splashRoomAccommodation => 'الغرفة والإقامة';

  @override
  String get splashRoomSubtitle => 'إقامتك، أولويتنا';

  @override
  String get splashRestaurant => 'المطعم والمأكولات';

  @override
  String get splashRestaurantSubtitle => 'النكهات والتميز في المطبخ';

  @override
  String get splashRoomService => 'خدمة الغرف والطلبات';

  @override
  String get splashRoomServiceSubtitle => 'التوصيل مباشرة إلى غرفتك';

  @override
  String get splashSpa => 'السبا والعافية';

  @override
  String get splashSpaSubtitle => 'استرخاء وعناية بالجسم';

  @override
  String get splashExcursions => 'الجولات والاستكشاف';

  @override
  String get splashExcursionsSubtitle => 'استكشف المنطقة بوتيرتك';

  @override
  String get splashLaundry => 'الغسيل';

  @override
  String get splashLaundrySubtitle => 'ملابس نظيفة بكل بساطة';

  @override
  String get notificationOrdersPending => 'طلبات خدمة الغرف قيد الانتظار';

  @override
  String get notificationRestaurantsPending => 'حجوزات المطاعم قيد الانتظار';

  @override
  String get notificationSpaPending => 'حجوزات السبا والعافية قيد الانتظار';

  @override
  String get notificationExcursionsPending => 'الجولات والأنشطة قيد الانتظار';

  @override
  String get notificationLaundryPending => 'طلبات الغسيل قيد الانتظار';

  @override
  String get notificationPalacePending => 'خدمات القصر والكونسيرج قيد الانتظار';

  @override
  String get notificationEmergency => 'المساعدة والطوارئ';

  @override
  String get notificationChat => 'الرسائل / دردشة العميل';

  @override
  String get acceptNewSchedule => 'قبول الموعد الجديد';

  @override
  String get hotelFallback => 'الفندق';

  @override
  String get roomNumberHint => 'مثال: 101';

  @override
  String get tableNumberHint => 'مثال: 42';

  @override
  String get clientCodeExample => 'مثال: ABC-123';

  @override
  String get takeCharge => 'التكفل';

  @override
  String get markReady => 'تحديد جاهزة';

  @override
  String get markDelivered => 'تحديد مُسلّمة';

  @override
  String get completeRequestLabel => 'إغلاق';

  @override
  String get rejectRequestLabel => 'رفض';

  @override
  String get noGuideAvailable => 'لا دليل متاح';

  @override
  String get openAction => 'فتح';

  @override
  String get adminNewEmergencyAlertTitle => 'تنبيه جديد للمساعدة والطوارئ';

  @override
  String get adminNewEmergencyAlertMessage => 'تنبيه مساعدة/طوارئ جديد مفتوح.';

  @override
  String get adminNewChatMessageTitle => 'رسالة عميل جديدة';

  @override
  String get adminNewChatMessageMessage => 'لديك رسالة عميل جديدة في الدردشة.';

  @override
  String get reservationDetailTitle => 'تفاصيل الحجز';

  @override
  String get roomLabelShort => 'الغرفة';

  @override
  String get laundryTakeChargeConfirm => 'التكفل بهذا الطلب من الغسيل؟';

  @override
  String get laundryMarkReadyConfirm => 'تحديد هذا الطلب كجاهز؟';

  @override
  String get laundryMarkDeliveredConfirm => 'تحديد هذا الطلب كمسلّم؟';

  @override
  String get laundryCancelConfirm => 'إلغاء هذا الطلب من الغسيل؟';

  @override
  String get spaRescheduleConfirmMessage =>
      'تأكيد هذا الموعد الجديد لحجز السبا؟';

  @override
  String get spaCancellationPolicy => 'الإلغاء ممكن حتى 24 ساعة قبل الموعد.';

  @override
  String get excursionConfirmReservationTitle => 'تأكيد الحجز';

  @override
  String get excursionConfirmReservationMessage => 'تأكيد حجز هذه الجولة؟';

  @override
  String get excursionMarkCompletedTitle => 'تحديد منفذة';

  @override
  String get excursionMarkCompletedMessage => 'تحديد هذه الجولة كمنفذة؟';

  @override
  String get excursionStaffTitle => 'حجوزات الجولات والأنشطة';

  @override
  String get excursionStaffSubtitle => 'متابعة الجولات والأنشطة';

  @override
  String get excursionConfirmAction => 'تأكيد';

  @override
  String get excursionMarkCompletedAction => 'تحديد منفذة';

  @override
  String get guidesScreenTitle => 'أدلة ومعلومات';

  @override
  String get adminRoomServiceDepartment => 'خدمة الغرف';

  @override
  String get adminNewRoomServiceToProcess => 'طلب خدمة غرف جديد للمعالجة';

  @override
  String get adminNewRestaurantToProcess => 'حجز مطعم جديد للمعالجة';

  @override
  String get adminNewSpaToProcess => 'حجز سبا وعافية جديد للمعالجة';

  @override
  String get adminNewExcursionToProcess => 'طلب جولات وأنشطة جديد للمعالجة';

  @override
  String get adminNewLaundryToProcess => 'طلب غسيل جديد للمعالجة';

  @override
  String get adminNewPalaceToProcess => 'طلب قصر/كونسيرج جديد للمعالجة';

  @override
  String get adminNoSectionAssigned =>
      'لا يوجد قسم معين. تواصل مع المسؤول لإدارة صلاحياتك.';

  @override
  String get adminSpaceTitle => 'مساحة الإدارة';

  @override
  String get yourEstablishment => 'منشأتك';

  @override
  String get adminAlertDismissMinute =>
      'ستختفي هذه التنبيه تلقائياً خلال دقيقة.';

  @override
  String get adminAlertDismissAuto => 'ستختفي هذه التنبيه تلقائياً.';

  @override
  String get openOrder => 'فتح الطلب';

  @override
  String get openRequest => 'فتح الطلب';

  @override
  String get viewDetails => 'عرض التفاصيل';

  @override
  String get clientUnknown => 'عميل غير معروف';

  @override
  String get roomUnspecified => 'غرفة غير محددة';

  @override
  String get newRoomServiceOrder => 'طلب خدمة غرف جديد';

  @override
  String orderNumberShort(String number) {
    return 'طلب $number';
  }

  @override
  String get newLaundryRequest => 'طلب غسيل جديد';

  @override
  String requestIdShort(String id) {
    return 'طلب #$id';
  }

  @override
  String get newPalaceRequest => 'طلب قصر/كونسيرج جديد';

  @override
  String get newRestaurantReservation => 'حجز مطعم جديد';

  @override
  String reservationIdShort(String id) {
    return 'حجز #$id';
  }

  @override
  String get newExcursionRequest => 'طلب جولات وأنشطة جديد';

  @override
  String get newSpaReservation => 'حجز سبا وعافية جديد';

  @override
  String get spaReservationCancelledTitle => 'تم إلغاء حجز السبا';

  @override
  String get spaServiceLabel => 'خدمة السبا';

  @override
  String reasonPrefix(String reason) {
    return 'السبب: $reason';
  }

  @override
  String get spaScheduleAcceptedTitle => 'السبا: تم قبول الموعد';

  @override
  String get newScheduleConfirmed => 'تم تأكيد الموعد الجديد';

  @override
  String adminSectionInPreparation(String label) {
    return 'القسم \"$label\" قيد التحضير لإصدار الموظفين على الجوال.';
  }

  @override
  String get clientWillBeNotifiedWithReason =>
      'سيتم إخطار العميل بالسبب المقدم.';

  @override
  String get confirmCancellation => 'تأكيد الإلغاء';

  @override
  String get actionConfirm => 'تأكيد';

  @override
  String get actionHonor => 'تحديد كمشرفة';

  @override
  String get statusHonored => 'مشرفة';

  @override
  String itemCount(num count) {
    String _temp0 = intl.Intl.pluralLogic(
      count,
      locale: localeName,
      other: 'عناصر',
      one: 'عنصر',
    );
    return '$_temp0';
  }

  @override
  String get actionNotifyRoomService => 'نقل إلى خدمة الغرف';

  @override
  String get clientRoom => 'غرفة العميل';

  @override
  String roomLabelLong(Object number) {
    return 'غرفة $number';
  }

  @override
  String get laundryRequestCancelledByClient =>
      'تم إلغاء طلب الغسيل من قبل العميل';

  @override
  String laundryRequestCancelledByClientMessage(Object number, Object details) {
    return 'قام العميل بإلغاء طلب الغسيل رقم $number$details.';
  }

  @override
  String get laundryRequestUpdated => 'تم تحديث طلب الغسيل';

  @override
  String laundryRequestUpdatedMessage(
    Object number,
    Object status,
    Object details,
  ) {
    return 'تم تحديث حالة طلب الغسيل رقم $number: $status$details.';
  }

  @override
  String get laundryRequest => 'طلب غسيل';

  @override
  String laundryItemsLabel(Object items) {
    return 'الغسيل: $items';
  }

  @override
  String specialInstructionsLong(Object instructions) {
    return 'التعليمات: $instructions';
  }

  @override
  String get viewRequests => 'عرض الطلبات';

  @override
  String newRestaurantReservationMessage(
    Object restaurant,
    Object date,
    Object time,
    Object details,
  ) {
    return 'حجز جديد في مطعم $restaurant مجدول في $date في $time$details.';
  }

  @override
  String get restaurantReservationCancelledByClient =>
      'تم إلغاء حجز المطعم من قبل العميل';

  @override
  String restaurantReservationCancelledByClientMessage(
    Object restaurant,
    Object date,
    Object time,
    Object details,
  ) {
    return 'قام العميل بإلغاء الحجز في مطعم $restaurant المجدول في $date في $time$details.';
  }

  @override
  String get restaurantReservationUpdated => 'تم تحديث حجز المطعم';

  @override
  String get restaurantReservation => 'حجز مطعم';

  @override
  String restaurantReservationConfirmedMessage(
    Object restaurant,
    Object date,
    Object time,
  ) {
    return 'تم تأكيد حجزك في مطعم $restaurant لليوم $date الساعة $time.';
  }

  @override
  String restaurantReservationHonoredMessage(Object restaurant) {
    return 'شكراً لك، تم استخدام حجزك في مطعم $restaurant.';
  }

  @override
  String get palaceRequestCancelledByClient =>
      'تم إلغاء طلب Palace من قبل العميل';

  @override
  String palaceRequestCancelledByClientMessage(Object number, Object details) {
    return 'قام العميل بإلغاء طلب Palace رقم $number$details.';
  }

  @override
  String get palaceRequestUpdated => 'تم تحديث طلب Palace';

  @override
  String palaceRequestUpdatedMessage(
    Object number,
    Object status,
    Object details,
  ) {
    return 'تم تحديث حالة طلب Palace رقم $number: $status$details.';
  }

  @override
  String get palaceRequestDetailed => 'طلب Palace / كونسيرج';

  @override
  String palaceRequestInProgressMessage(Object item, Object number) {
    return 'طلبك \"$item\" (رقم $number) قيد المعالجة.';
  }

  @override
  String palaceRequestCompletedMessage(Object item, Object number) {
    return 'تم اكتمال طلبك \"$item\" (رقم $number).';
  }

  @override
  String palaceRequestRefusedMessage(Object item, Object number) {
    return 'تم رفض أو إلغاء طلبك \"$item\" (رقم $number).';
  }

  @override
  String palaceRequestUpdatedStatusMessage(Object item, Object number) {
    return 'تم تحديث حالة طلبك \"$item\" (رقم $number).';
  }

  @override
  String get closeButton => 'إغلاق';

  @override
  String laundryStatusPickedUpMessage(Object item, Object number) {
    return 'تم استلام $item للطلب رقم $number.';
  }

  @override
  String laundryStatusReadyMessage(Object item, Object number) {
    return '$item للطلب رقم $number جاهز.';
  }

  @override
  String laundryStatusDeliveredMessage(Object item, Object number) {
    return 'تم تسليم $item للطلب رقم $number.';
  }

  @override
  String laundryStatusCancelledMessage(Object number) {
    return 'تم إلغاء طلب الغسيل رقم $number.';
  }
}
