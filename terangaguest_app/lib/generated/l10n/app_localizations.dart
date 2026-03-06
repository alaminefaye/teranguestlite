import 'dart:async';

import 'package:flutter/foundation.dart';
import 'package:flutter/widgets.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:intl/intl.dart' as intl;

import 'app_localizations_ar.dart';
import 'app_localizations_en.dart';
import 'app_localizations_es.dart';
import 'app_localizations_fr.dart';

// ignore_for_file: type=lint

/// Callers can lookup localized strings with an instance of AppLocalizations
/// returned by `AppLocalizations.of(context)`.
///
/// Applications need to include `AppLocalizations.delegate()` in their app's
/// `localizationDelegates` list, and the locales they support in the app's
/// `supportedLocales` list. For example:
///
/// ```dart
/// import 'l10n/app_localizations.dart';
///
/// return MaterialApp(
///   localizationsDelegates: AppLocalizations.localizationsDelegates,
///   supportedLocales: AppLocalizations.supportedLocales,
///   home: MyApplicationHome(),
/// );
/// ```
///
/// ## Update pubspec.yaml
///
/// Please make sure to update your pubspec.yaml to include the following
/// packages:
///
/// ```yaml
/// dependencies:
///   # Internationalization support.
///   flutter_localizations:
///     sdk: flutter
///   intl: any # Use the pinned version from flutter_localizations
///
///   # Rest of dependencies
/// ```
///
/// ## iOS Applications
///
/// iOS applications define key application metadata, including supported
/// locales, in an Info.plist file that is built into the application bundle.
/// To configure the locales supported by your app, you’ll need to edit this
/// file.
///
/// First, open your project’s ios/Runner.xcworkspace Xcode workspace file.
/// Then, in the Project Navigator, open the Info.plist file under the Runner
/// project’s Runner folder.
///
/// Next, select the Information Property List item, select Add Item from the
/// Editor menu, then select Localizations from the pop-up menu.
///
/// Select and expand the newly-created Localizations item then, for each
/// locale your application supports, add a new item and select the locale
/// you wish to add from the pop-up menu in the Value field. This list should
/// be consistent with the languages listed in the AppLocalizations.supportedLocales
/// property.
abstract class AppLocalizations {
  AppLocalizations(String locale)
    : localeName = intl.Intl.canonicalizedLocale(locale.toString());

  final String localeName;

  static AppLocalizations of(BuildContext context) {
    return Localizations.of<AppLocalizations>(context, AppLocalizations)!;
  }

  static const LocalizationsDelegate<AppLocalizations> delegate =
      _AppLocalizationsDelegate();

  /// A list of this localizations delegate along with the default localizations
  /// delegates.
  ///
  /// Returns a list of localizations delegates containing this delegate along with
  /// GlobalMaterialLocalizations.delegate, GlobalCupertinoLocalizations.delegate,
  /// and GlobalWidgetsLocalizations.delegate.
  ///
  /// Additional delegates can be added by appending to this list in
  /// MaterialApp. This list does not have to be used at all if a custom list
  /// of delegates is preferred or required.
  static const List<LocalizationsDelegate<dynamic>> localizationsDelegates =
      <LocalizationsDelegate<dynamic>>[
        delegate,
        GlobalMaterialLocalizations.delegate,
        GlobalCupertinoLocalizations.delegate,
        GlobalWidgetsLocalizations.delegate,
      ];

  /// A list of this localizations delegate's supported locales.
  static const List<Locale> supportedLocales = <Locale>[
    Locale('ar'),
    Locale('en'),
    Locale('es'),
    Locale('fr'),
  ];

  /// No description provided for @appTitle.
  ///
  /// In fr, this message translates to:
  /// **'Teranga Guest'**
  String get appTitle;

  /// No description provided for @login.
  ///
  /// In fr, this message translates to:
  /// **'Connexion'**
  String get login;

  /// No description provided for @email.
  ///
  /// In fr, this message translates to:
  /// **'Email'**
  String get email;

  /// No description provided for @password.
  ///
  /// In fr, this message translates to:
  /// **'Mot de passe'**
  String get password;

  /// No description provided for @rememberMe.
  ///
  /// In fr, this message translates to:
  /// **'Se souvenir de moi'**
  String get rememberMe;

  /// No description provided for @loginButton.
  ///
  /// In fr, this message translates to:
  /// **'Se connecter'**
  String get loginButton;

  /// No description provided for @loginError.
  ///
  /// In fr, this message translates to:
  /// **'Erreur de connexion'**
  String get loginError;

  /// No description provided for @emailRequired.
  ///
  /// In fr, this message translates to:
  /// **'Veuillez entrer votre email'**
  String get emailRequired;

  /// No description provided for @emailInvalid.
  ///
  /// In fr, this message translates to:
  /// **'Email invalide'**
  String get emailInvalid;

  /// No description provided for @passwordRequired.
  ///
  /// In fr, this message translates to:
  /// **'Veuillez entrer votre mot de passe'**
  String get passwordRequired;

  /// No description provided for @passwordTooShort.
  ///
  /// In fr, this message translates to:
  /// **'Mot de passe trop court (min. 6 caractères)'**
  String get passwordTooShort;

  /// No description provided for @myProfile.
  ///
  /// In fr, this message translates to:
  /// **'Mon Profil'**
  String get myProfile;

  /// No description provided for @myHistories.
  ///
  /// In fr, this message translates to:
  /// **'Mes Historiques'**
  String get myHistories;

  /// No description provided for @myFavorites.
  ///
  /// In fr, this message translates to:
  /// **'Mes Favoris'**
  String get myFavorites;

  /// No description provided for @myOrders.
  ///
  /// In fr, this message translates to:
  /// **'Mes Commandes'**
  String get myOrders;

  /// No description provided for @myRestaurantReservations.
  ///
  /// In fr, this message translates to:
  /// **'Mes Réservations Restaurant'**
  String get myRestaurantReservations;

  /// No description provided for @mySpaReservations.
  ///
  /// In fr, this message translates to:
  /// **'Mes Réservations Spa'**
  String get mySpaReservations;

  /// No description provided for @myExcursions.
  ///
  /// In fr, this message translates to:
  /// **'Mes Excursions'**
  String get myExcursions;

  /// No description provided for @myLaundryRequests.
  ///
  /// In fr, this message translates to:
  /// **'Mes Demandes Blanchisserie'**
  String get myLaundryRequests;

  /// No description provided for @myPalaceRequests.
  ///
  /// In fr, this message translates to:
  /// **'Mes Demandes Palace'**
  String get myPalaceRequests;

  /// No description provided for @settings.
  ///
  /// In fr, this message translates to:
  /// **'Paramètres'**
  String get settings;

  /// No description provided for @changePassword.
  ///
  /// In fr, this message translates to:
  /// **'Changer le mot de passe'**
  String get changePassword;

  /// No description provided for @about.
  ///
  /// In fr, this message translates to:
  /// **'À propos'**
  String get about;

  /// No description provided for @contactSupport.
  ///
  /// In fr, this message translates to:
  /// **'Contacter le support'**
  String get contactSupport;

  /// No description provided for @logout.
  ///
  /// In fr, this message translates to:
  /// **'Déconnexion'**
  String get logout;

  /// No description provided for @logoutConfirm.
  ///
  /// In fr, this message translates to:
  /// **'Êtes-vous sûr de vouloir vous déconnecter ?'**
  String get logoutConfirm;

  /// No description provided for @cancel.
  ///
  /// In fr, this message translates to:
  /// **'Annuler'**
  String get cancel;

  /// No description provided for @version.
  ///
  /// In fr, this message translates to:
  /// **'Version'**
  String get version;

  /// No description provided for @preferences.
  ///
  /// In fr, this message translates to:
  /// **'Préférences'**
  String get preferences;

  /// No description provided for @notifications.
  ///
  /// In fr, this message translates to:
  /// **'Notifications'**
  String get notifications;

  /// No description provided for @notificationsOn.
  ///
  /// In fr, this message translates to:
  /// **'Notifications activées'**
  String get notificationsOn;

  /// No description provided for @notificationsOff.
  ///
  /// In fr, this message translates to:
  /// **'Notifications désactivées'**
  String get notificationsOff;

  /// No description provided for @application.
  ///
  /// In fr, this message translates to:
  /// **'Application'**
  String get application;

  /// No description provided for @language.
  ///
  /// In fr, this message translates to:
  /// **'Langue'**
  String get language;

  /// No description provided for @french.
  ///
  /// In fr, this message translates to:
  /// **'Français'**
  String get french;

  /// No description provided for @english.
  ///
  /// In fr, this message translates to:
  /// **'English'**
  String get english;

  /// No description provided for @welcomeTitle.
  ///
  /// In fr, this message translates to:
  /// **'Bienvenue au King Fahd Palace Hotel'**
  String get welcomeTitle;

  /// No description provided for @welcomeToEnterprise.
  ///
  /// In fr, this message translates to:
  /// **'Bienvenue au {enterpriseName}'**
  String welcomeToEnterprise(String enterpriseName);

  /// No description provided for @welcomeSubtitle.
  ///
  /// In fr, this message translates to:
  /// **'Votre assistant digital est à votre service'**
  String get welcomeSubtitle;

  /// No description provided for @roomService.
  ///
  /// In fr, this message translates to:
  /// **'Service en chambre'**
  String get roomService;

  /// No description provided for @restaurantsBars.
  ///
  /// In fr, this message translates to:
  /// **'Restaurants et Bars'**
  String get restaurantsBars;

  /// No description provided for @spaWellness.
  ///
  /// In fr, this message translates to:
  /// **'Spa et Bien-être'**
  String get spaWellness;

  /// No description provided for @wellnessSportLeisure.
  ///
  /// In fr, this message translates to:
  /// **'Bien-être, sport & loisirs'**
  String get wellnessSportLeisure;

  /// No description provided for @wellnessSportLeisureSubtitle.
  ///
  /// In fr, this message translates to:
  /// **'Spa, Golf, Tennis, Fitness'**
  String get wellnessSportLeisureSubtitle;

  /// No description provided for @golfTitle.
  ///
  /// In fr, this message translates to:
  /// **'Golf'**
  String get golfTitle;

  /// No description provided for @golfSubtitle.
  ///
  /// In fr, this message translates to:
  /// **'Réservation Tee-time et location de matériel'**
  String get golfSubtitle;

  /// No description provided for @tennisTitle.
  ///
  /// In fr, this message translates to:
  /// **'Tennis'**
  String get tennisTitle;

  /// No description provided for @tennisSubtitle.
  ///
  /// In fr, this message translates to:
  /// **'Réservation de courts et location de matériel'**
  String get tennisSubtitle;

  /// No description provided for @golfTennisTitle.
  ///
  /// In fr, this message translates to:
  /// **'Golf & Tennis'**
  String get golfTennisTitle;

  /// No description provided for @golfTennisSubtitle.
  ///
  /// In fr, this message translates to:
  /// **'Réservation Tee-time, courts et location de matériel'**
  String get golfTennisSubtitle;

  /// No description provided for @golfTennisTeetime.
  ///
  /// In fr, this message translates to:
  /// **'Réservation Tee-time'**
  String get golfTennisTeetime;

  /// No description provided for @golfTennisCourt.
  ///
  /// In fr, this message translates to:
  /// **'Court de tennis'**
  String get golfTennisCourt;

  /// No description provided for @golfTennisEquipment.
  ///
  /// In fr, this message translates to:
  /// **'Location de matériel'**
  String get golfTennisEquipment;

  /// No description provided for @sportFitnessTitle.
  ///
  /// In fr, this message translates to:
  /// **'Sport & Fitness'**
  String get sportFitnessTitle;

  /// No description provided for @sportFitnessSubtitle.
  ///
  /// In fr, this message translates to:
  /// **'Horaires de la salle et réservation de coach personnel'**
  String get sportFitnessSubtitle;

  /// No description provided for @sportFitnessGymHours.
  ///
  /// In fr, this message translates to:
  /// **'Horaires de la salle de sport'**
  String get sportFitnessGymHours;

  /// No description provided for @sportFitnessBookCoach.
  ///
  /// In fr, this message translates to:
  /// **'Réserver un coach personnel'**
  String get sportFitnessBookCoach;

  /// No description provided for @gymHoursDefault.
  ///
  /// In fr, this message translates to:
  /// **'Consultez la réception pour les horaires.'**
  String get gymHoursDefault;

  /// No description provided for @palaceServices.
  ///
  /// In fr, this message translates to:
  /// **'Autres services'**
  String get palaceServices;

  /// No description provided for @explorationMobility.
  ///
  /// In fr, this message translates to:
  /// **'Exploration & mobilité'**
  String get explorationMobility;

  /// No description provided for @explorationMobilitySubtitle.
  ///
  /// In fr, this message translates to:
  /// **'Location véhicule, découverte, visites guidées et transferts'**
  String get explorationMobilitySubtitle;

  /// No description provided for @vehicleRental.
  ///
  /// In fr, this message translates to:
  /// **'Location de Véhicule'**
  String get vehicleRental;

  /// No description provided for @vehicleRentalDesc.
  ///
  /// In fr, this message translates to:
  /// **'Catalogue de véhicules (berlines, 4x4, citadines) avec réservation et options de confort.'**
  String get vehicleRentalDesc;

  /// No description provided for @sitesTouristiques.
  ///
  /// In fr, this message translates to:
  /// **'Découverte & Sites Touristiques'**
  String get sitesTouristiques;

  /// No description provided for @sitesTouristiquesDesc.
  ///
  /// In fr, this message translates to:
  /// **'Lieux incontournables : Lac Rose, Île de Gorée, Plateau… Photos et descriptifs.'**
  String get sitesTouristiquesDesc;

  /// No description provided for @guidedTours.
  ///
  /// In fr, this message translates to:
  /// **'Visites Guidées Personnalisées'**
  String get guidedTours;

  /// No description provided for @guidedToursDesc.
  ///
  /// In fr, this message translates to:
  /// **'Réservation de guides certifiés pour circuits culturels, gastronomiques ou historiques.'**
  String get guidedToursDesc;

  /// No description provided for @transfersVtc.
  ///
  /// In fr, this message translates to:
  /// **'Transferts & VTC'**
  String get transfersVtc;

  /// No description provided for @transfersVtcDesc.
  ///
  /// In fr, this message translates to:
  /// **'Navettes aéroport ou chauffeurs privés pour des trajets sécurisés.'**
  String get transfersVtcDesc;

  /// No description provided for @excursions.
  ///
  /// In fr, this message translates to:
  /// **'Excursions'**
  String get excursions;

  /// No description provided for @laundry.
  ///
  /// In fr, this message translates to:
  /// **'Blanchisserie'**
  String get laundry;

  /// No description provided for @concierge.
  ///
  /// In fr, this message translates to:
  /// **'Conciergerie'**
  String get concierge;

  /// No description provided for @callCenter.
  ///
  /// In fr, this message translates to:
  /// **'Centre d\'Appels'**
  String get callCenter;

  /// No description provided for @hotelInfosSecurity.
  ///
  /// In fr, this message translates to:
  /// **'Hotel infos & sécurité'**
  String get hotelInfosSecurity;

  /// No description provided for @hotelInfosSecuritySubtitle.
  ///
  /// In fr, this message translates to:
  /// **'Livret d\'accueil, assistance urgence et chatbot'**
  String get hotelInfosSecuritySubtitle;

  /// No description provided for @hotelInfos.
  ///
  /// In fr, this message translates to:
  /// **'Hôtel Infos'**
  String get hotelInfos;

  /// No description provided for @hotelInfosDesc.
  ///
  /// In fr, this message translates to:
  /// **'Wi-Fi, plans, règlement et infos pratiques'**
  String get hotelInfosDesc;

  /// No description provided for @assistanceEmergency.
  ///
  /// In fr, this message translates to:
  /// **'Assistance & Urgence'**
  String get assistanceEmergency;

  /// No description provided for @assistanceEmergencyDesc.
  ///
  /// In fr, this message translates to:
  /// **'Médecin ou urgence sécurité (chambre identifiée)'**
  String get assistanceEmergencyDesc;

  /// No description provided for @chatbotMultilingual.
  ///
  /// In fr, this message translates to:
  /// **'Chatbot IA Multilingue'**
  String get chatbotMultilingual;

  /// No description provided for @chatbotDesc.
  ///
  /// In fr, this message translates to:
  /// **'Assistant digital 24/7'**
  String get chatbotDesc;

  /// No description provided for @gallery.
  ///
  /// In fr, this message translates to:
  /// **'Galerie'**
  String get gallery;

  /// No description provided for @galleryDesc.
  ///
  /// In fr, this message translates to:
  /// **'Photos de l\'établissement et albums'**
  String get galleryDesc;

  /// No description provided for @ourEstablishments.
  ///
  /// In fr, this message translates to:
  /// **'Nos établissements'**
  String get ourEstablishments;

  /// No description provided for @ourEstablishmentsDesc.
  ///
  /// In fr, this message translates to:
  /// **'Autres sites du groupe dans le pays'**
  String get ourEstablishmentsDesc;

  /// No description provided for @wifiCode.
  ///
  /// In fr, this message translates to:
  /// **'Code Wi-Fi'**
  String get wifiCode;

  /// No description provided for @wifiPassword.
  ///
  /// In fr, this message translates to:
  /// **'Mot de passe Wi-Fi'**
  String get wifiPassword;

  /// No description provided for @houseRules.
  ///
  /// In fr, this message translates to:
  /// **'Règlement intérieur'**
  String get houseRules;

  /// No description provided for @practicalInfo.
  ///
  /// In fr, this message translates to:
  /// **'Informations pratiques'**
  String get practicalInfo;

  /// No description provided for @requestDoctor.
  ///
  /// In fr, this message translates to:
  /// **'Solliciter un médecin'**
  String get requestDoctor;

  /// No description provided for @reportSecurityEmergency.
  ///
  /// In fr, this message translates to:
  /// **'Signaler une urgence sécurité'**
  String get reportSecurityEmergency;

  /// No description provided for @roomLabel.
  ///
  /// In fr, this message translates to:
  /// **'Chambre : {room}'**
  String roomLabel(String room);

  /// No description provided for @emergencyRequestSent.
  ///
  /// In fr, this message translates to:
  /// **'Demande envoyée. L\'équipe va vous contacter.'**
  String get emergencyRequestSent;

  /// No description provided for @noActiveStayForEmergency.
  ///
  /// In fr, this message translates to:
  /// **'Un séjour actif est requis. Connectez-vous avec le compte de la chambre ou contactez la réception.'**
  String get noActiveStayForEmergency;

  /// No description provided for @assistanceDoctorNotConfigured.
  ///
  /// In fr, this message translates to:
  /// **'Le service Assistance médecin n\'est pas configuré pour cet établissement.'**
  String get assistanceDoctorNotConfigured;

  /// No description provided for @assistanceSecurityNotConfigured.
  ///
  /// In fr, this message translates to:
  /// **'Le service Urgence sécurité n\'est pas configuré pour cet établissement.'**
  String get assistanceSecurityNotConfigured;

  /// No description provided for @confirmEmergencyAction.
  ///
  /// In fr, this message translates to:
  /// **'Voulez-vous confirmer : {action} ?'**
  String confirmEmergencyAction(String action);

  /// No description provided for @deleteConversationConfirm.
  ///
  /// In fr, this message translates to:
  /// **'Supprimer cette conversation ?'**
  String get deleteConversationConfirm;

  /// No description provided for @deleteConversation.
  ///
  /// In fr, this message translates to:
  /// **'Supprimer la conversation'**
  String get deleteConversation;

  /// No description provided for @messageDeleted.
  ///
  /// In fr, this message translates to:
  /// **'Message supprimé'**
  String get messageDeleted;

  /// No description provided for @reply.
  ///
  /// In fr, this message translates to:
  /// **'Répondre'**
  String get reply;

  /// No description provided for @deleteMessage.
  ///
  /// In fr, this message translates to:
  /// **'Supprimer le message'**
  String get deleteMessage;

  /// No description provided for @chatbotComingSoon.
  ///
  /// In fr, this message translates to:
  /// **'Bientôt disponible'**
  String get chatbotComingSoon;

  /// No description provided for @chatbotComingSoonHint.
  ///
  /// In fr, this message translates to:
  /// **'Le chatbot multilingue sera disponible prochainement.'**
  String get chatbotComingSoonHint;

  /// No description provided for @servicesChambreLogistique.
  ///
  /// In fr, this message translates to:
  /// **'Service en chambre'**
  String get servicesChambreLogistique;

  /// No description provided for @roomServiceRestauration.
  ///
  /// In fr, this message translates to:
  /// **'Room Service & Restauration'**
  String get roomServiceRestauration;

  /// No description provided for @roomServiceRestaurationDesc.
  ///
  /// In fr, this message translates to:
  /// **'Menu digital haute définition permettant de commander repas et boissons avec suivi en temps réel de la préparation.'**
  String get roomServiceRestaurationDesc;

  /// No description provided for @laundryDesc.
  ///
  /// In fr, this message translates to:
  /// **'Grille tarifaire interactive et demande de ramassage immédiate du linge.'**
  String get laundryDesc;

  /// No description provided for @amenitiesConcierge.
  ///
  /// In fr, this message translates to:
  /// **'Amenities & Conciergerie'**
  String get amenitiesConcierge;

  /// No description provided for @amenitiesConciergeDesc.
  ///
  /// In fr, this message translates to:
  /// **'Demande simplifiée d\'articles de toilette, oreillers supplémentaires, kit de rasage ou tout autre service sans passer par le téléphone.'**
  String get amenitiesConciergeDesc;

  /// No description provided for @minibarIntelligent.
  ///
  /// In fr, this message translates to:
  /// **'Mini-bar Intelligent'**
  String get minibarIntelligent;

  /// No description provided for @minibarIntelligentDesc.
  ///
  /// In fr, this message translates to:
  /// **'Inventaire digital des produits et déclaration simplifiée des consommations.'**
  String get minibarIntelligentDesc;

  /// No description provided for @comingSoon.
  ///
  /// In fr, this message translates to:
  /// **'Bientôt disponible'**
  String get comingSoon;

  /// No description provided for @amenityToiletries.
  ///
  /// In fr, this message translates to:
  /// **'Articles de toilette'**
  String get amenityToiletries;

  /// No description provided for @amenityPillows.
  ///
  /// In fr, this message translates to:
  /// **'Oreillers supplémentaires'**
  String get amenityPillows;

  /// No description provided for @amenityShavingKit.
  ///
  /// In fr, this message translates to:
  /// **'Kit de rasage'**
  String get amenityShavingKit;

  /// No description provided for @amenityOther.
  ///
  /// In fr, this message translates to:
  /// **'Autre demande'**
  String get amenityOther;

  /// No description provided for @amenityDetailsHint.
  ///
  /// In fr, this message translates to:
  /// **'Précisez si besoin (optionnel)'**
  String get amenityDetailsHint;

  /// No description provided for @amenitySelectQuantities.
  ///
  /// In fr, this message translates to:
  /// **'Sélectionnez les articles et quantités'**
  String get amenitySelectQuantities;

  /// No description provided for @amenityPillowCount.
  ///
  /// In fr, this message translates to:
  /// **'Nombre d\'oreillers'**
  String get amenityPillowCount;

  /// No description provided for @amenityOtherDetailsHint.
  ///
  /// In fr, this message translates to:
  /// **'Décrivez votre demande (optionnel)'**
  String get amenityOtherDetailsHint;

  /// No description provided for @amenityItemSoap.
  ///
  /// In fr, this message translates to:
  /// **'Savon'**
  String get amenityItemSoap;

  /// No description provided for @amenityItemShampoo.
  ///
  /// In fr, this message translates to:
  /// **'Shampooing'**
  String get amenityItemShampoo;

  /// No description provided for @amenityItemToothpaste.
  ///
  /// In fr, this message translates to:
  /// **'Dentifrice'**
  String get amenityItemToothpaste;

  /// No description provided for @amenityItemToothbrush.
  ///
  /// In fr, this message translates to:
  /// **'Brosse à dents'**
  String get amenityItemToothbrush;

  /// No description provided for @amenityItemComb.
  ///
  /// In fr, this message translates to:
  /// **'Peigne'**
  String get amenityItemComb;

  /// No description provided for @amenityItemTowels.
  ///
  /// In fr, this message translates to:
  /// **'Serviettes'**
  String get amenityItemTowels;

  /// No description provided for @amenityItemPillow.
  ///
  /// In fr, this message translates to:
  /// **'Oreiller supplémentaire'**
  String get amenityItemPillow;

  /// No description provided for @amenityItemRazor.
  ///
  /// In fr, this message translates to:
  /// **'Rasoir'**
  String get amenityItemRazor;

  /// No description provided for @amenityItemShavingFoam.
  ///
  /// In fr, this message translates to:
  /// **'Mousse à raser'**
  String get amenityItemShavingFoam;

  /// No description provided for @amenityItemAfterShave.
  ///
  /// In fr, this message translates to:
  /// **'Après-rasage'**
  String get amenityItemAfterShave;

  /// No description provided for @amenityItemBlades.
  ///
  /// In fr, this message translates to:
  /// **'Lames de rechange'**
  String get amenityItemBlades;

  /// No description provided for @back.
  ///
  /// In fr, this message translates to:
  /// **'Retour'**
  String get back;

  /// No description provided for @retry.
  ///
  /// In fr, this message translates to:
  /// **'Réessayer'**
  String get retry;

  /// No description provided for @error.
  ///
  /// In fr, this message translates to:
  /// **'Erreur'**
  String get error;

  /// No description provided for @errorHint.
  ///
  /// In fr, this message translates to:
  /// **'Vérifiez votre connexion et réessayez.'**
  String get errorHint;

  /// No description provided for @close.
  ///
  /// In fr, this message translates to:
  /// **'Fermer'**
  String get close;

  /// No description provided for @ok.
  ///
  /// In fr, this message translates to:
  /// **'OK'**
  String get ok;

  /// No description provided for @save.
  ///
  /// In fr, this message translates to:
  /// **'Enregistrer'**
  String get save;

  /// No description provided for @noFavorites.
  ///
  /// In fr, this message translates to:
  /// **'Aucun favori'**
  String get noFavorites;

  /// No description provided for @noFavoritesHint.
  ///
  /// In fr, this message translates to:
  /// **'Ajoutez des articles, restaurants, soins ou excursions en favoris depuis leurs fiches.'**
  String get noFavoritesHint;

  /// No description provided for @contactSupportTitle.
  ///
  /// In fr, this message translates to:
  /// **'Contacter le support'**
  String get contactSupportTitle;

  /// No description provided for @chooseContact.
  ///
  /// In fr, this message translates to:
  /// **'Choisissez un moyen de contact :'**
  String get chooseContact;

  /// No description provided for @aboutDescription.
  ///
  /// In fr, this message translates to:
  /// **'Application d\'accueil et de services pour les clients du King Fahd Palace Hotel.'**
  String get aboutDescription;

  /// No description provided for @hotelName.
  ///
  /// In fr, this message translates to:
  /// **'KING FAHD PALACE HOTEL'**
  String get hotelName;

  /// No description provided for @noUser.
  ///
  /// In fr, this message translates to:
  /// **'Aucun utilisateur connecté'**
  String get noUser;

  /// No description provided for @addToCart.
  ///
  /// In fr, this message translates to:
  /// **'Ajouter au panier'**
  String get addToCart;

  /// No description provided for @viewCart.
  ///
  /// In fr, this message translates to:
  /// **'Voir le panier'**
  String get viewCart;

  /// No description provided for @cart.
  ///
  /// In fr, this message translates to:
  /// **'Panier'**
  String get cart;

  /// No description provided for @emptyCart.
  ///
  /// In fr, this message translates to:
  /// **'Votre panier est vide'**
  String get emptyCart;

  /// No description provided for @emptyCartHint.
  ///
  /// In fr, this message translates to:
  /// **'Ajoutez des articles pour commander.'**
  String get emptyCartHint;

  /// No description provided for @browseMenu.
  ///
  /// In fr, this message translates to:
  /// **'Parcourir le menu'**
  String get browseMenu;

  /// No description provided for @clearCartConfirm.
  ///
  /// In fr, this message translates to:
  /// **'Êtes-vous sûr de vouloir vider votre panier ?'**
  String get clearCartConfirm;

  /// No description provided for @clear.
  ///
  /// In fr, this message translates to:
  /// **'Vider'**
  String get clear;

  /// No description provided for @orderSuccess.
  ///
  /// In fr, this message translates to:
  /// **'Commande enregistrée'**
  String get orderSuccess;

  /// No description provided for @orderSuccessHint.
  ///
  /// In fr, this message translates to:
  /// **'Vous pouvez suivre votre commande dans Mes Commandes.'**
  String get orderSuccessHint;

  /// No description provided for @home.
  ///
  /// In fr, this message translates to:
  /// **'Accueil'**
  String get home;

  /// No description provided for @myBookings.
  ///
  /// In fr, this message translates to:
  /// **'Mes réservations'**
  String get myBookings;

  /// No description provided for @excursionNotFound.
  ///
  /// In fr, this message translates to:
  /// **'Excursion introuvable'**
  String get excursionNotFound;

  /// No description provided for @excursionNotFoundHint.
  ///
  /// In fr, this message translates to:
  /// **'Cette excursion n\'existe pas ou n\'est plus disponible.'**
  String get excursionNotFoundHint;

  /// No description provided for @serviceNotFound.
  ///
  /// In fr, this message translates to:
  /// **'Service introuvable'**
  String get serviceNotFound;

  /// No description provided for @serviceNotFoundHint.
  ///
  /// In fr, this message translates to:
  /// **'Ce service n\'existe pas ou n\'est plus disponible.'**
  String get serviceNotFoundHint;

  /// No description provided for @restaurantNotFound.
  ///
  /// In fr, this message translates to:
  /// **'Restaurant introuvable'**
  String get restaurantNotFound;

  /// No description provided for @restaurantNotFoundHint.
  ///
  /// In fr, this message translates to:
  /// **'Ce restaurant n\'existe pas ou n\'est plus disponible.'**
  String get restaurantNotFoundHint;

  /// No description provided for @orderNotFound.
  ///
  /// In fr, this message translates to:
  /// **'Commande introuvable'**
  String get orderNotFound;

  /// No description provided for @orderNotFoundHint.
  ///
  /// In fr, this message translates to:
  /// **'Cette commande n\'existe pas ou a été supprimée.'**
  String get orderNotFoundHint;

  /// No description provided for @noLaundryService.
  ///
  /// In fr, this message translates to:
  /// **'Aucun service blanchisserie disponible'**
  String get noLaundryService;

  /// No description provided for @noLaundryServiceHint.
  ///
  /// In fr, this message translates to:
  /// **'Les services de nettoyage seront listés ici.'**
  String get noLaundryServiceHint;

  /// No description provided for @noSpaService.
  ///
  /// In fr, this message translates to:
  /// **'Aucun service spa disponible'**
  String get noSpaService;

  /// No description provided for @noSpaServiceInCategory.
  ///
  /// In fr, this message translates to:
  /// **'Aucun service dans cette catégorie'**
  String get noSpaServiceInCategory;

  /// No description provided for @noSpaServiceHint.
  ///
  /// In fr, this message translates to:
  /// **'Les soins et massages seront proposés ici.'**
  String get noSpaServiceHint;

  /// No description provided for @noPalaceService.
  ///
  /// In fr, this message translates to:
  /// **'Aucun service Palace disponible'**
  String get noPalaceService;

  /// No description provided for @noPalaceServiceHint.
  ///
  /// In fr, this message translates to:
  /// **'Les services premium seront affichés ici.'**
  String get noPalaceServiceHint;

  /// No description provided for @noExcursionAvailable.
  ///
  /// In fr, this message translates to:
  /// **'Aucune excursion disponible'**
  String get noExcursionAvailable;

  /// No description provided for @noExcursionAvailableHint.
  ///
  /// In fr, this message translates to:
  /// **'Les activités et sorties seront proposées ici.'**
  String get noExcursionAvailableHint;

  /// No description provided for @noOrder.
  ///
  /// In fr, this message translates to:
  /// **'Aucune commande'**
  String get noOrder;

  /// No description provided for @noOrderForStatus.
  ///
  /// In fr, this message translates to:
  /// **'Aucune commande {status}'**
  String noOrderForStatus(String status);

  /// No description provided for @noOrderSubtitle.
  ///
  /// In fr, this message translates to:
  /// **'Vos commandes room service apparaîtront ici.'**
  String get noOrderSubtitle;

  /// No description provided for @orderStatusPending.
  ///
  /// In fr, this message translates to:
  /// **'en attente'**
  String get orderStatusPending;

  /// No description provided for @orderStatusConfirmed.
  ///
  /// In fr, this message translates to:
  /// **'confirmée'**
  String get orderStatusConfirmed;

  /// No description provided for @orderStatusPreparing.
  ///
  /// In fr, this message translates to:
  /// **'en préparation'**
  String get orderStatusPreparing;

  /// No description provided for @orderStatusDelivering.
  ///
  /// In fr, this message translates to:
  /// **'en livraison'**
  String get orderStatusDelivering;

  /// No description provided for @orderStatusDelivered.
  ///
  /// In fr, this message translates to:
  /// **'livrée'**
  String get orderStatusDelivered;

  /// No description provided for @noRestaurantAvailable.
  ///
  /// In fr, this message translates to:
  /// **'Aucun restaurant disponible'**
  String get noRestaurantAvailable;

  /// No description provided for @noRestaurantForType.
  ///
  /// In fr, this message translates to:
  /// **'Aucun {type} disponible'**
  String noRestaurantForType(String type);

  /// No description provided for @noRestaurantSubtitle.
  ///
  /// In fr, this message translates to:
  /// **'Les restaurants et bars seront listés ici.'**
  String get noRestaurantSubtitle;

  /// No description provided for @typeRestaurant.
  ///
  /// In fr, this message translates to:
  /// **'restaurant'**
  String get typeRestaurant;

  /// No description provided for @typeBar.
  ///
  /// In fr, this message translates to:
  /// **'bar'**
  String get typeBar;

  /// No description provided for @typeCafe.
  ///
  /// In fr, this message translates to:
  /// **'café'**
  String get typeCafe;

  /// No description provided for @typeLounge.
  ///
  /// In fr, this message translates to:
  /// **'lounge'**
  String get typeLounge;

  /// No description provided for @noItemAvailable.
  ///
  /// In fr, this message translates to:
  /// **'Aucun article disponible'**
  String get noItemAvailable;

  /// No description provided for @noSearchResult.
  ///
  /// In fr, this message translates to:
  /// **'Aucun résultat trouvé'**
  String get noSearchResult;

  /// No description provided for @noItemSubtitle.
  ///
  /// In fr, this message translates to:
  /// **'Les articles de cette catégorie seront listés ici.'**
  String get noItemSubtitle;

  /// No description provided for @tryAnotherSearch.
  ///
  /// In fr, this message translates to:
  /// **'Essayez un autre terme de recherche.'**
  String get tryAnotherSearch;

  /// No description provided for @noCategoryAvailable.
  ///
  /// In fr, this message translates to:
  /// **'Aucune catégorie disponible'**
  String get noCategoryAvailable;

  /// No description provided for @noCategoryHint.
  ///
  /// In fr, this message translates to:
  /// **'Le menu room service sera disponible ici.'**
  String get noCategoryHint;

  /// No description provided for @noPalaceRequest.
  ///
  /// In fr, this message translates to:
  /// **'Aucune demande Palace'**
  String get noPalaceRequest;

  /// No description provided for @noPalaceRequestHint.
  ///
  /// In fr, this message translates to:
  /// **'Envoyez une demande depuis les services Palace.'**
  String get noPalaceRequestHint;

  /// No description provided for @noLaundryRequest.
  ///
  /// In fr, this message translates to:
  /// **'Aucune demande blanchisserie'**
  String get noLaundryRequest;

  /// No description provided for @noLaundryRequestHint.
  ///
  /// In fr, this message translates to:
  /// **'Passez une demande depuis le service Blanchisserie.'**
  String get noLaundryRequestHint;

  /// No description provided for @noExcursionBooked.
  ///
  /// In fr, this message translates to:
  /// **'Aucune excursion réservée'**
  String get noExcursionBooked;

  /// No description provided for @noExcursionBookedHint.
  ///
  /// In fr, this message translates to:
  /// **'Réservez une excursion depuis la liste des activités.'**
  String get noExcursionBookedHint;

  /// No description provided for @noSpaReservation.
  ///
  /// In fr, this message translates to:
  /// **'Aucune réservation spa'**
  String get noSpaReservation;

  /// No description provided for @noSpaReservationHint.
  ///
  /// In fr, this message translates to:
  /// **'Réservez un soin depuis la liste des services spa.'**
  String get noSpaReservationHint;

  /// No description provided for @noRestaurantReservation.
  ///
  /// In fr, this message translates to:
  /// **'Aucune réservation restaurant'**
  String get noRestaurantReservation;

  /// No description provided for @noRestaurantReservationHint.
  ///
  /// In fr, this message translates to:
  /// **'Réservez une table depuis la liste des restaurants.'**
  String get noRestaurantReservationHint;

  /// No description provided for @itemNotFound.
  ///
  /// In fr, this message translates to:
  /// **'Article introuvable'**
  String get itemNotFound;

  /// No description provided for @filterAll.
  ///
  /// In fr, this message translates to:
  /// **'Toutes'**
  String get filterAll;

  /// No description provided for @filterPending.
  ///
  /// In fr, this message translates to:
  /// **'En attente'**
  String get filterPending;

  /// No description provided for @filterConfirmed.
  ///
  /// In fr, this message translates to:
  /// **'Confirmées'**
  String get filterConfirmed;

  /// No description provided for @filterPreparing.
  ///
  /// In fr, this message translates to:
  /// **'En préparation'**
  String get filterPreparing;

  /// No description provided for @filterDelivering.
  ///
  /// In fr, this message translates to:
  /// **'En livraison'**
  String get filterDelivering;

  /// No description provided for @filterDelivered.
  ///
  /// In fr, this message translates to:
  /// **'Livrées'**
  String get filterDelivered;

  /// No description provided for @filterAllTypes.
  ///
  /// In fr, this message translates to:
  /// **'Tous'**
  String get filterAllTypes;

  /// No description provided for @filterRestaurants.
  ///
  /// In fr, this message translates to:
  /// **'Restaurants'**
  String get filterRestaurants;

  /// No description provided for @filterBars.
  ///
  /// In fr, this message translates to:
  /// **'Bars'**
  String get filterBars;

  /// No description provided for @filterCafes.
  ///
  /// In fr, this message translates to:
  /// **'Cafés'**
  String get filterCafes;

  /// No description provided for @filterLounges.
  ///
  /// In fr, this message translates to:
  /// **'Lounges'**
  String get filterLounges;

  /// No description provided for @ordersHistorySubtitle.
  ///
  /// In fr, this message translates to:
  /// **'Historique et suivi'**
  String get ordersHistorySubtitle;

  /// No description provided for @arabic.
  ///
  /// In fr, this message translates to:
  /// **'العربية'**
  String get arabic;

  /// No description provided for @spanish.
  ///
  /// In fr, this message translates to:
  /// **'Español'**
  String get spanish;

  /// No description provided for @discoverRestaurants.
  ///
  /// In fr, this message translates to:
  /// **'Découvrez nos établissements'**
  String get discoverRestaurants;

  /// No description provided for @reservationsConfirmed.
  ///
  /// In fr, this message translates to:
  /// **'Réservations confirmées'**
  String get reservationsConfirmed;

  /// No description provided for @myRequests.
  ///
  /// In fr, this message translates to:
  /// **'Mes Demandes'**
  String get myRequests;

  /// No description provided for @requestNumber.
  ///
  /// In fr, this message translates to:
  /// **'Demande #{id}'**
  String requestNumber(int id);

  /// No description provided for @palaceServicesSubtitle.
  ///
  /// In fr, this message translates to:
  /// **'Services premium et conciergerie'**
  String get palaceServicesSubtitle;

  /// No description provided for @spaWellnessSubtitle.
  ///
  /// In fr, this message translates to:
  /// **'Bien-être & Détente'**
  String get spaWellnessSubtitle;

  /// No description provided for @myReservations.
  ///
  /// In fr, this message translates to:
  /// **'Mes Réservations'**
  String get myReservations;

  /// No description provided for @discoverRegion.
  ///
  /// In fr, this message translates to:
  /// **'Découvrez notre région'**
  String get discoverRegion;

  /// No description provided for @spaSubtitle.
  ///
  /// In fr, this message translates to:
  /// **'Détente et relaxation'**
  String get spaSubtitle;

  /// No description provided for @chooseCategory.
  ///
  /// In fr, this message translates to:
  /// **'Choisissez une catégorie'**
  String get chooseCategory;

  /// No description provided for @orderDetailTitle.
  ///
  /// In fr, this message translates to:
  /// **'Détail Commande'**
  String get orderDetailTitle;

  /// No description provided for @orderTrackingSubtitle.
  ///
  /// In fr, this message translates to:
  /// **'Suivi de votre commande'**
  String get orderTrackingSubtitle;

  /// No description provided for @adultPrice.
  ///
  /// In fr, this message translates to:
  /// **'Adulte: {price}'**
  String adultPrice(String price);

  /// No description provided for @childPrice.
  ///
  /// In fr, this message translates to:
  /// **'Enfant: {price}'**
  String childPrice(String price);

  /// No description provided for @book.
  ///
  /// In fr, this message translates to:
  /// **'Réserver'**
  String get book;

  /// No description provided for @unavailable.
  ///
  /// In fr, this message translates to:
  /// **'Indisponible'**
  String get unavailable;

  /// No description provided for @spaServiceDefaultName.
  ///
  /// In fr, this message translates to:
  /// **'Service Spa'**
  String get spaServiceDefaultName;

  /// No description provided for @capacityPeople.
  ///
  /// In fr, this message translates to:
  /// **'Capacité : {count} personnes'**
  String capacityPeople(int count);

  /// No description provided for @openingHours.
  ///
  /// In fr, this message translates to:
  /// **'Horaires d\'ouverture'**
  String get openingHours;

  /// No description provided for @bookTable.
  ///
  /// In fr, this message translates to:
  /// **'Réserver une table'**
  String get bookTable;

  /// No description provided for @closed.
  ///
  /// In fr, this message translates to:
  /// **'Fermé'**
  String get closed;

  /// No description provided for @search.
  ///
  /// In fr, this message translates to:
  /// **'Rechercher...'**
  String get search;

  /// No description provided for @reviewOrder.
  ///
  /// In fr, this message translates to:
  /// **'Vérifiez votre commande'**
  String get reviewOrder;

  /// No description provided for @specialInstructions.
  ///
  /// In fr, this message translates to:
  /// **'Instructions spéciales'**
  String get specialInstructions;

  /// No description provided for @specialInstructionsHint.
  ///
  /// In fr, this message translates to:
  /// **'Allergies, préférences, consignes de livraison...'**
  String get specialInstructionsHint;

  /// No description provided for @description.
  ///
  /// In fr, this message translates to:
  /// **'Description'**
  String get description;

  /// No description provided for @specialInstructionsExample.
  ///
  /// In fr, this message translates to:
  /// **'Ex: Sans oignons, bien cuit...'**
  String get specialInstructionsExample;

  /// No description provided for @viewCartCaps.
  ///
  /// In fr, this message translates to:
  /// **'VOIR PANIER'**
  String get viewCartCaps;

  /// No description provided for @passwordChangedSuccess.
  ///
  /// In fr, this message translates to:
  /// **'Mot de passe modifié avec succès'**
  String get passwordChangedSuccess;

  /// No description provided for @currentPassword.
  ///
  /// In fr, this message translates to:
  /// **'Mot de passe actuel'**
  String get currentPassword;

  /// No description provided for @newPassword.
  ///
  /// In fr, this message translates to:
  /// **'Nouveau mot de passe'**
  String get newPassword;

  /// No description provided for @confirmPassword.
  ///
  /// In fr, this message translates to:
  /// **'Confirmer le nouveau mot de passe'**
  String get confirmPassword;

  /// No description provided for @fieldRequired.
  ///
  /// In fr, this message translates to:
  /// **'Champ requis'**
  String get fieldRequired;

  /// No description provided for @minChars.
  ///
  /// In fr, this message translates to:
  /// **'Minimum 8 caractères'**
  String get minChars;

  /// No description provided for @needUpperCase.
  ///
  /// In fr, this message translates to:
  /// **'Doit contenir une majuscule'**
  String get needUpperCase;

  /// No description provided for @needDigit.
  ///
  /// In fr, this message translates to:
  /// **'Doit contenir un chiffre'**
  String get needDigit;

  /// No description provided for @passwordsDoNotMatch.
  ///
  /// In fr, this message translates to:
  /// **'Les mots de passe ne correspondent pas'**
  String get passwordsDoNotMatch;

  /// No description provided for @passwordRulesHint.
  ///
  /// In fr, this message translates to:
  /// **'Le nouveau mot de passe doit contenir au moins 8 caractères, une majuscule et un chiffre.'**
  String get passwordRulesHint;

  /// No description provided for @requestDetails.
  ///
  /// In fr, this message translates to:
  /// **'Détails de votre demande'**
  String get requestDetails;

  /// No description provided for @describeRequest.
  ///
  /// In fr, this message translates to:
  /// **'Décrivez votre demande en détail...'**
  String get describeRequest;

  /// No description provided for @preferredTimeOptional.
  ///
  /// In fr, this message translates to:
  /// **'Heure souhaitée (optionnel)'**
  String get preferredTimeOptional;

  /// No description provided for @selectDateAndTime.
  ///
  /// In fr, this message translates to:
  /// **'Sélectionner date et heure'**
  String get selectDateAndTime;

  /// No description provided for @sendRequest.
  ///
  /// In fr, this message translates to:
  /// **'Envoyer la demande'**
  String get sendRequest;

  /// No description provided for @requestReservationHint.
  ///
  /// In fr, this message translates to:
  /// **'Indiquez la date, l\'heure et vos précisions dans le formulaire.'**
  String get requestReservationHint;

  /// No description provided for @requestSent.
  ///
  /// In fr, this message translates to:
  /// **'Demande envoyée !'**
  String get requestSent;

  /// No description provided for @requestSentMessage.
  ///
  /// In fr, this message translates to:
  /// **'Votre demande de service a été enregistrée.'**
  String get requestSentMessage;

  /// No description provided for @confirmRequest.
  ///
  /// In fr, this message translates to:
  /// **'Confirmer la demande'**
  String get confirmRequest;

  /// No description provided for @selectedItems.
  ///
  /// In fr, this message translates to:
  /// **'Articles sélectionnés'**
  String get selectedItems;

  /// No description provided for @total.
  ///
  /// In fr, this message translates to:
  /// **'Total'**
  String get total;

  /// No description provided for @specialInstructionsOptional.
  ///
  /// In fr, this message translates to:
  /// **'Instructions spéciales (optionnel)'**
  String get specialInstructionsOptional;

  /// No description provided for @laundryInstructionsExample.
  ///
  /// In fr, this message translates to:
  /// **'Ex: Lessive sans parfum, repassage doux...'**
  String get laundryInstructionsExample;

  /// No description provided for @date.
  ///
  /// In fr, this message translates to:
  /// **'Date'**
  String get date;

  /// No description provided for @selectDate.
  ///
  /// In fr, this message translates to:
  /// **'Sélectionner une date'**
  String get selectDate;

  /// No description provided for @participants.
  ///
  /// In fr, this message translates to:
  /// **'Participants'**
  String get participants;

  /// No description provided for @adults.
  ///
  /// In fr, this message translates to:
  /// **'Adultes'**
  String get adults;

  /// No description provided for @children.
  ///
  /// In fr, this message translates to:
  /// **'Enfants'**
  String get children;

  /// No description provided for @specialRequestsOptional.
  ///
  /// In fr, this message translates to:
  /// **'Demandes spéciales (optionnel)'**
  String get specialRequestsOptional;

  /// No description provided for @summary.
  ///
  /// In fr, this message translates to:
  /// **'Récapitulatif'**
  String get summary;

  /// No description provided for @confirmReservation.
  ///
  /// In fr, this message translates to:
  /// **'Confirmer la réservation'**
  String get confirmReservation;

  /// No description provided for @reservationConfirmed.
  ///
  /// In fr, this message translates to:
  /// **'Réservation confirmée !'**
  String get reservationConfirmed;

  /// No description provided for @excursionConfirmedMessage.
  ///
  /// In fr, this message translates to:
  /// **'Votre excursion pour {count} personne(s) est confirmée.'**
  String excursionConfirmedMessage(int count);

  /// No description provided for @confirmationNotification.
  ///
  /// In fr, this message translates to:
  /// **'Vous recevrez une confirmation par notification.'**
  String get confirmationNotification;

  /// No description provided for @numberOfGuests.
  ///
  /// In fr, this message translates to:
  /// **'Nombre de personnes'**
  String get numberOfGuests;

  /// No description provided for @tableReservedMessage.
  ///
  /// In fr, this message translates to:
  /// **'Votre table pour {count} personne(s) est réservée.'**
  String tableReservedMessage(int count);

  /// No description provided for @orderConfirmed.
  ///
  /// In fr, this message translates to:
  /// **'Commande confirmée !'**
  String get orderConfirmed;

  /// No description provided for @itemsAddedToCart.
  ///
  /// In fr, this message translates to:
  /// **'Articles ajoutés au panier !'**
  String get itemsAddedToCart;

  /// No description provided for @errorPrefix.
  ///
  /// In fr, this message translates to:
  /// **'Erreur : '**
  String get errorPrefix;

  /// No description provided for @cannotOpenLink.
  ///
  /// In fr, this message translates to:
  /// **'Impossible d\'ouvrir le lien'**
  String get cannotOpenLink;

  /// No description provided for @included.
  ///
  /// In fr, this message translates to:
  /// **'Inclus :'**
  String get included;

  /// No description provided for @schedule.
  ///
  /// In fr, this message translates to:
  /// **'Horaires'**
  String get schedule;

  /// No description provided for @childrenAgeRange.
  ///
  /// In fr, this message translates to:
  /// **'Enfants (âge)'**
  String get childrenAgeRange;

  /// No description provided for @reviewsTitle.
  ///
  /// In fr, this message translates to:
  /// **'Avis'**
  String get reviewsTitle;

  /// No description provided for @reviewsPending.
  ///
  /// In fr, this message translates to:
  /// **'À noter'**
  String get reviewsPending;

  /// No description provided for @reviewsMyReviews.
  ///
  /// In fr, this message translates to:
  /// **'Mes avis'**
  String get reviewsMyReviews;

  /// No description provided for @reviewsNoPending.
  ///
  /// In fr, this message translates to:
  /// **'Aucun avis à donner'**
  String get reviewsNoPending;

  /// No description provided for @reviewsNoPendingHint.
  ///
  /// In fr, this message translates to:
  /// **'Les avis sont proposés après une commande livrée, un check-out, une demande traitée ou une excursion terminée.'**
  String get reviewsNoPendingHint;

  /// No description provided for @reviewsNoReviewsYet.
  ///
  /// In fr, this message translates to:
  /// **'Aucun avis'**
  String get reviewsNoReviewsYet;

  /// No description provided for @reviewsNoReviewsYetHint.
  ///
  /// In fr, this message translates to:
  /// **'Vos avis apparaîtront ici.'**
  String get reviewsNoReviewsYetHint;

  /// No description provided for @rateYourExperience.
  ///
  /// In fr, this message translates to:
  /// **'Comment s\'est passée votre expérience ?'**
  String get rateYourExperience;

  /// No description provided for @optionalComment.
  ///
  /// In fr, this message translates to:
  /// **'Commentaire (optionnel)'**
  String get optionalComment;

  /// No description provided for @submit.
  ///
  /// In fr, this message translates to:
  /// **'Envoyer'**
  String get submit;

  /// No description provided for @thankYouForReview.
  ///
  /// In fr, this message translates to:
  /// **'Merci pour votre avis !'**
  String get thankYouForReview;

  /// No description provided for @orderTracking.
  ///
  /// In fr, this message translates to:
  /// **'Suivi de commande'**
  String get orderTracking;

  /// No description provided for @orderItems.
  ///
  /// In fr, this message translates to:
  /// **'Articles commandés'**
  String get orderItems;

  /// No description provided for @quantity.
  ///
  /// In fr, this message translates to:
  /// **'Quantité'**
  String get quantity;

  /// No description provided for @reorder.
  ///
  /// In fr, this message translates to:
  /// **'Recommander'**
  String get reorder;

  /// No description provided for @laundryRequestSentMessage.
  ///
  /// In fr, this message translates to:
  /// **'Votre demande de blanchisserie a été enregistrée.'**
  String get laundryRequestSentMessage;

  /// No description provided for @demand.
  ///
  /// In fr, this message translates to:
  /// **'Demander'**
  String get demand;

  /// No description provided for @reserve.
  ///
  /// In fr, this message translates to:
  /// **'Réserver'**
  String get reserve;

  /// No description provided for @myExcursionsShort.
  ///
  /// In fr, this message translates to:
  /// **'Mes Excursions'**
  String get myExcursionsShort;

  /// No description provided for @categoryFacial.
  ///
  /// In fr, this message translates to:
  /// **'Soins Visage'**
  String get categoryFacial;

  /// No description provided for @categoryBody.
  ///
  /// In fr, this message translates to:
  /// **'Soins Corps'**
  String get categoryBody;

  /// No description provided for @excursionsTitle.
  ///
  /// In fr, this message translates to:
  /// **'Excursions'**
  String get excursionsTitle;

  /// No description provided for @articleCount.
  ///
  /// In fr, this message translates to:
  /// **'{count} article(s)'**
  String articleCount(int count);

  /// No description provided for @appNameVersion.
  ///
  /// In fr, this message translates to:
  /// **'TerangaGuest {version} {v}'**
  String appNameVersion(String version, String v);

  /// No description provided for @orderLabel.
  ///
  /// In fr, this message translates to:
  /// **'Commande {id}'**
  String orderLabel(int id);

  /// No description provided for @navigationTo.
  ///
  /// In fr, this message translates to:
  /// **'Navigation vers {name}'**
  String navigationTo(String name);

  /// No description provided for @open.
  ///
  /// In fr, this message translates to:
  /// **'Ouvert'**
  String get open;

  /// No description provided for @itemsLabel.
  ///
  /// In fr, this message translates to:
  /// **'ARTICLES'**
  String get itemsLabel;

  /// No description provided for @statusLabel.
  ///
  /// In fr, this message translates to:
  /// **'Statut'**
  String get statusLabel;

  /// No description provided for @laundrySubtitle.
  ///
  /// In fr, this message translates to:
  /// **'Service de nettoyage'**
  String get laundrySubtitle;

  /// No description provided for @spaHintExample.
  ///
  /// In fr, this message translates to:
  /// **'Ex: Pression douce, huile de lavande...'**
  String get spaHintExample;

  /// No description provided for @restaurantHintExample.
  ///
  /// In fr, this message translates to:
  /// **'Ex: Table près de la fenêtre, anniversaire...'**
  String get restaurantHintExample;

  /// No description provided for @allergiesPreferencesExample.
  ///
  /// In fr, this message translates to:
  /// **'Ex: Allergies, préférences...'**
  String get allergiesPreferencesExample;

  /// No description provided for @orderConfirmedMessage.
  ///
  /// In fr, this message translates to:
  /// **'Votre commande a été enregistrée avec succès'**
  String get orderConfirmedMessage;

  /// No description provided for @orderNumberLabel.
  ///
  /// In fr, this message translates to:
  /// **'N° de commande'**
  String get orderNumberLabel;

  /// No description provided for @statusPending.
  ///
  /// In fr, this message translates to:
  /// **'En attente'**
  String get statusPending;

  /// No description provided for @statusConfirmed.
  ///
  /// In fr, this message translates to:
  /// **'Confirmée'**
  String get statusConfirmed;

  /// No description provided for @statusPreparing.
  ///
  /// In fr, this message translates to:
  /// **'En préparation'**
  String get statusPreparing;

  /// No description provided for @statusDelivering.
  ///
  /// In fr, this message translates to:
  /// **'En livraison'**
  String get statusDelivering;

  /// No description provided for @statusInProgress.
  ///
  /// In fr, this message translates to:
  /// **'En cours'**
  String get statusInProgress;

  /// No description provided for @statusCompleted.
  ///
  /// In fr, this message translates to:
  /// **'Terminée'**
  String get statusCompleted;

  /// No description provided for @statusCancelled.
  ///
  /// In fr, this message translates to:
  /// **'Annulée'**
  String get statusCancelled;

  /// No description provided for @statusPickedUp.
  ///
  /// In fr, this message translates to:
  /// **'Récupérée'**
  String get statusPickedUp;

  /// No description provided for @statusProcessing.
  ///
  /// In fr, this message translates to:
  /// **'En cours'**
  String get statusProcessing;

  /// No description provided for @statusReady.
  ///
  /// In fr, this message translates to:
  /// **'Prête'**
  String get statusReady;

  /// No description provided for @statusDelivered.
  ///
  /// In fr, this message translates to:
  /// **'Livrée'**
  String get statusDelivered;

  /// No description provided for @myCart.
  ///
  /// In fr, this message translates to:
  /// **'Mon Panier'**
  String get myCart;

  /// No description provided for @orderNotificationHint.
  ///
  /// In fr, this message translates to:
  /// **'Vous recevrez une notification dès que votre commande sera confirmée par le restaurant.'**
  String get orderNotificationHint;

  /// No description provided for @spaReservationConfirmedMessage.
  ///
  /// In fr, this message translates to:
  /// **'Votre réservation pour {name} est confirmée.'**
  String spaReservationConfirmedMessage(String name);

  /// No description provided for @excursion.
  ///
  /// In fr, this message translates to:
  /// **'Excursion'**
  String get excursion;

  /// No description provided for @verifyOrder.
  ///
  /// In fr, this message translates to:
  /// **'Vérifiez votre commande'**
  String get verifyOrder;

  /// No description provided for @personsShort.
  ///
  /// In fr, this message translates to:
  /// **'pers.'**
  String get personsShort;

  /// No description provided for @amenities.
  ///
  /// In fr, this message translates to:
  /// **'Commodités'**
  String get amenities;

  /// No description provided for @placeOrder.
  ///
  /// In fr, this message translates to:
  /// **'Commander'**
  String get placeOrder;

  /// No description provided for @restaurant.
  ///
  /// In fr, this message translates to:
  /// **'Restaurant'**
  String get restaurant;

  /// No description provided for @time.
  ///
  /// In fr, this message translates to:
  /// **'Heure'**
  String get time;

  /// No description provided for @guests.
  ///
  /// In fr, this message translates to:
  /// **'Personnes'**
  String get guests;

  /// No description provided for @guestsCount.
  ///
  /// In fr, this message translates to:
  /// **'{count} personne(s)'**
  String guestsCount(int count);

  /// No description provided for @dayMonday.
  ///
  /// In fr, this message translates to:
  /// **'Lundi'**
  String get dayMonday;

  /// No description provided for @dayTuesday.
  ///
  /// In fr, this message translates to:
  /// **'Mardi'**
  String get dayTuesday;

  /// No description provided for @dayWednesday.
  ///
  /// In fr, this message translates to:
  /// **'Mercredi'**
  String get dayWednesday;

  /// No description provided for @dayThursday.
  ///
  /// In fr, this message translates to:
  /// **'Jeudi'**
  String get dayThursday;

  /// No description provided for @dayFriday.
  ///
  /// In fr, this message translates to:
  /// **'Vendredi'**
  String get dayFriday;

  /// No description provided for @daySaturday.
  ///
  /// In fr, this message translates to:
  /// **'Samedi'**
  String get daySaturday;

  /// No description provided for @daySunday.
  ///
  /// In fr, this message translates to:
  /// **'Dimanche'**
  String get daySunday;

  /// No description provided for @service.
  ///
  /// In fr, this message translates to:
  /// **'Service'**
  String get service;

  /// No description provided for @duration.
  ///
  /// In fr, this message translates to:
  /// **'Durée'**
  String get duration;

  /// No description provided for @reservationCancelledMessage.
  ///
  /// In fr, this message translates to:
  /// **'Réservation annulée.'**
  String get reservationCancelledMessage;

  /// No description provided for @cancelReservationConfirm.
  ///
  /// In fr, this message translates to:
  /// **'Annuler cette réservation ?'**
  String get cancelReservationConfirm;

  /// No description provided for @vehicleRentalTitle.
  ///
  /// In fr, this message translates to:
  /// **'Location de Véhicule'**
  String get vehicleRentalTitle;

  /// No description provided for @vehicleRentalSubtitle.
  ///
  /// In fr, this message translates to:
  /// **'Choisissez un véhicule et envoyez votre demande de réservation.'**
  String get vehicleRentalSubtitle;

  /// No description provided for @noVehicleAvailable.
  ///
  /// In fr, this message translates to:
  /// **'Aucun véhicule disponible'**
  String get noVehicleAvailable;

  /// No description provided for @noVehicleAvailableHint.
  ///
  /// In fr, this message translates to:
  /// **'Les véhicules seront bientôt proposés par l\'établissement.'**
  String get noVehicleAvailableHint;

  /// No description provided for @requestVehicleRental.
  ///
  /// In fr, this message translates to:
  /// **'Demander cette location'**
  String get requestVehicleRental;

  /// No description provided for @rentalDate.
  ///
  /// In fr, this message translates to:
  /// **'Date souhaitée'**
  String get rentalDate;

  /// No description provided for @rentalDuration.
  ///
  /// In fr, this message translates to:
  /// **'Durée (heures)'**
  String get rentalDuration;

  /// No description provided for @rentalDays.
  ///
  /// In fr, this message translates to:
  /// **'Nombre de jours'**
  String get rentalDays;

  /// No description provided for @estimatedPrice.
  ///
  /// In fr, this message translates to:
  /// **'Prix estimé'**
  String get estimatedPrice;

  /// No description provided for @guidedToursTitle.
  ///
  /// In fr, this message translates to:
  /// **'Visites Guidées Personnalisées'**
  String get guidedToursTitle;

  /// No description provided for @guidedToursSubtitle.
  ///
  /// In fr, this message translates to:
  /// **'Indiquez la date, le type de circuit et le nombre de personnes.'**
  String get guidedToursSubtitle;

  /// No description provided for @tourType.
  ///
  /// In fr, this message translates to:
  /// **'Type de circuit'**
  String get tourType;

  /// No description provided for @tourTypeCultural.
  ///
  /// In fr, this message translates to:
  /// **'Culturel'**
  String get tourTypeCultural;

  /// No description provided for @tourTypeGastronomic.
  ///
  /// In fr, this message translates to:
  /// **'Gastronomique'**
  String get tourTypeGastronomic;

  /// No description provided for @tourTypeHistorical.
  ///
  /// In fr, this message translates to:
  /// **'Historique'**
  String get tourTypeHistorical;

  /// No description provided for @transfersVtcTitle.
  ///
  /// In fr, this message translates to:
  /// **'Transferts & VTC'**
  String get transfersVtcTitle;

  /// No description provided for @transfersVtcSubtitle.
  ///
  /// In fr, this message translates to:
  /// **'Navette aéroport ou chauffeur privé. Indiquez lieu de prise en charge et destination.'**
  String get transfersVtcSubtitle;

  /// No description provided for @pickupPlace.
  ///
  /// In fr, this message translates to:
  /// **'Lieu de prise en charge'**
  String get pickupPlace;

  /// No description provided for @destinationPlace.
  ///
  /// In fr, this message translates to:
  /// **'Destination'**
  String get destinationPlace;

  /// No description provided for @sitesTouristiquesTitle.
  ///
  /// In fr, this message translates to:
  /// **'Découverte & Sites Touristiques'**
  String get sitesTouristiquesTitle;

  /// No description provided for @sitesTouristiquesSubtitle.
  ///
  /// In fr, this message translates to:
  /// **'Vitrine des lieux incontournables avec photos et descriptifs.'**
  String get sitesTouristiquesSubtitle;

  /// No description provided for @filterVehicleType.
  ///
  /// In fr, this message translates to:
  /// **'Type'**
  String get filterVehicleType;

  /// No description provided for @filterMinSeats.
  ///
  /// In fr, this message translates to:
  /// **'Places min.'**
  String get filterMinSeats;

  /// No description provided for @vehicleTypeBerline.
  ///
  /// In fr, this message translates to:
  /// **'Berline'**
  String get vehicleTypeBerline;

  /// No description provided for @vehicleTypeSuv.
  ///
  /// In fr, this message translates to:
  /// **'SUV'**
  String get vehicleTypeSuv;

  /// No description provided for @vehicleTypeMinibus.
  ///
  /// In fr, this message translates to:
  /// **'Minibus'**
  String get vehicleTypeMinibus;

  /// No description provided for @vehicleTypeVan.
  ///
  /// In fr, this message translates to:
  /// **'Van'**
  String get vehicleTypeVan;

  /// No description provided for @vehicleTypeOther.
  ///
  /// In fr, this message translates to:
  /// **'Autre'**
  String get vehicleTypeOther;

  /// No description provided for @validate.
  ///
  /// In fr, this message translates to:
  /// **'Valider'**
  String get validate;

  /// No description provided for @clientCode.
  ///
  /// In fr, this message translates to:
  /// **'Code client'**
  String get clientCode;

  /// No description provided for @clientCodeHint.
  ///
  /// In fr, this message translates to:
  /// **'Code client (ex: 123456)'**
  String get clientCodeHint;

  /// No description provided for @specialRequests.
  ///
  /// In fr, this message translates to:
  /// **'Demandes spéciales'**
  String get specialRequests;

  /// No description provided for @developedByUTA.
  ///
  /// In fr, this message translates to:
  /// **'Développé par Universal Technologies Africa'**
  String get developedByUTA;

  /// No description provided for @tapToContinue.
  ///
  /// In fr, this message translates to:
  /// **'Appuyez pour continuer'**
  String get tapToContinue;

  /// No description provided for @changeLanguage.
  ///
  /// In fr, this message translates to:
  /// **'Changer la langue'**
  String get changeLanguage;

  /// No description provided for @monthJanuary.
  ///
  /// In fr, this message translates to:
  /// **'janvier'**
  String get monthJanuary;

  /// No description provided for @monthFebruary.
  ///
  /// In fr, this message translates to:
  /// **'février'**
  String get monthFebruary;

  /// No description provided for @monthMarch.
  ///
  /// In fr, this message translates to:
  /// **'mars'**
  String get monthMarch;

  /// No description provided for @monthApril.
  ///
  /// In fr, this message translates to:
  /// **'avril'**
  String get monthApril;

  /// No description provided for @monthMay.
  ///
  /// In fr, this message translates to:
  /// **'mai'**
  String get monthMay;

  /// No description provided for @monthJune.
  ///
  /// In fr, this message translates to:
  /// **'juin'**
  String get monthJune;

  /// No description provided for @monthJuly.
  ///
  /// In fr, this message translates to:
  /// **'juillet'**
  String get monthJuly;

  /// No description provided for @monthAugust.
  ///
  /// In fr, this message translates to:
  /// **'août'**
  String get monthAugust;

  /// No description provided for @monthSeptember.
  ///
  /// In fr, this message translates to:
  /// **'septembre'**
  String get monthSeptember;

  /// No description provided for @monthOctober.
  ///
  /// In fr, this message translates to:
  /// **'octobre'**
  String get monthOctober;

  /// No description provided for @monthNovember.
  ///
  /// In fr, this message translates to:
  /// **'novembre'**
  String get monthNovember;

  /// No description provided for @monthDecember.
  ///
  /// In fr, this message translates to:
  /// **'décembre'**
  String get monthDecember;

  /// No description provided for @reservationClientCodeBanner.
  ///
  /// In fr, this message translates to:
  /// **'Les réservations sont réservées aux clients avec un séjour valide. Entrez votre code client ci-dessous (reçu à l\'enregistrement).'**
  String get reservationClientCodeBanner;

  /// No description provided for @sessionExpiredNeedClientCode.
  ///
  /// In fr, this message translates to:
  /// **'Votre séjour n\'est plus actif. Entrez votre code client pour réserver.'**
  String get sessionExpiredNeedClientCode;

  /// No description provided for @periodAllDates.
  ///
  /// In fr, this message translates to:
  /// **'Toutes les dates'**
  String get periodAllDates;

  /// No description provided for @periodToday.
  ///
  /// In fr, this message translates to:
  /// **'Aujourd\'hui'**
  String get periodToday;

  /// No description provided for @periodThisWeek.
  ///
  /// In fr, this message translates to:
  /// **'Cette semaine'**
  String get periodThisWeek;

  /// No description provided for @periodThisMonth.
  ///
  /// In fr, this message translates to:
  /// **'Ce mois'**
  String get periodThisMonth;

  /// No description provided for @staffOrdersTitle.
  ///
  /// In fr, this message translates to:
  /// **'Commandes Room Service'**
  String get staffOrdersTitle;

  /// No description provided for @staffOrdersSubtitle.
  ///
  /// In fr, this message translates to:
  /// **'Suivi et traitement des commandes room service'**
  String get staffOrdersSubtitle;

  /// No description provided for @addedToCart.
  ///
  /// In fr, this message translates to:
  /// **'{itemName} ajouté au panier'**
  String addedToCart(String itemName);

  /// No description provided for @invalidSessionRetry.
  ///
  /// In fr, this message translates to:
  /// **'Séjour invalide ou expiré. Entrez à nouveau votre code.'**
  String get invalidSessionRetry;

  /// No description provided for @orderValidationError.
  ///
  /// In fr, this message translates to:
  /// **'Impossible de valider la commande. Vérifiez votre code client ou contactez la réception.'**
  String get orderValidationError;

  /// No description provided for @enterCodeToValidate.
  ///
  /// In fr, this message translates to:
  /// **'Entrez le code reçu à l\'enregistrement pour valider la commande.'**
  String get enterCodeToValidate;

  /// No description provided for @roomNumberTablet.
  ///
  /// In fr, this message translates to:
  /// **'Numéro de chambre (cette tablette)'**
  String get roomNumberTablet;

  /// No description provided for @roomIdRecommended.
  ///
  /// In fr, this message translates to:
  /// **'ID chambre (recommandé — voir tableau de bord > Accès tablettes)'**
  String get roomIdRecommended;

  /// No description provided for @multiHotelWarning.
  ///
  /// In fr, this message translates to:
  /// **'En multi-établissement, renseignez l\'ID chambre pour n\'afficher que les données de votre hôtel.'**
  String get multiHotelWarning;

  /// No description provided for @code6Digits.
  ///
  /// In fr, this message translates to:
  /// **'Code à 6 chiffres'**
  String get code6Digits;

  /// No description provided for @enter6Digits.
  ///
  /// In fr, this message translates to:
  /// **'Entrez le code à 6 chiffres.'**
  String get enter6Digits;

  /// No description provided for @defineRoomTablet.
  ///
  /// In fr, this message translates to:
  /// **'Définissez le numéro de chambre de cette tablette.'**
  String get defineRoomTablet;

  /// No description provided for @confirmIdentity.
  ///
  /// In fr, this message translates to:
  /// **'Confirmer que c\'est bien vous'**
  String get confirmIdentity;

  /// No description provided for @verifyInfoBeforeOrder.
  ///
  /// In fr, this message translates to:
  /// **'Vérifiez vos informations avant d\'envoyer la commande :'**
  String get verifyInfoBeforeOrder;

  /// No description provided for @identityName.
  ///
  /// In fr, this message translates to:
  /// **'Nom'**
  String get identityName;

  /// No description provided for @identityRoom.
  ///
  /// In fr, this message translates to:
  /// **'Chambre'**
  String get identityRoom;

  /// No description provided for @identityPhone.
  ///
  /// In fr, this message translates to:
  /// **'Téléphone'**
  String get identityPhone;

  /// No description provided for @identityEmail.
  ///
  /// In fr, this message translates to:
  /// **'Email'**
  String get identityEmail;

  /// No description provided for @paymentMethodText.
  ///
  /// In fr, this message translates to:
  /// **'Moyen de paiement'**
  String get paymentMethodText;

  /// No description provided for @paymentCash.
  ///
  /// In fr, this message translates to:
  /// **'Espèce'**
  String get paymentCash;

  /// No description provided for @paymentRoomBill.
  ///
  /// In fr, this message translates to:
  /// **'Mettre sur la note de la chambre'**
  String get paymentRoomBill;

  /// No description provided for @paymentWave.
  ///
  /// In fr, this message translates to:
  /// **'Wave'**
  String get paymentWave;

  /// No description provided for @paymentOrangeMoney.
  ///
  /// In fr, this message translates to:
  /// **'Orange Money'**
  String get paymentOrangeMoney;

  /// No description provided for @confirmOrder.
  ///
  /// In fr, this message translates to:
  /// **'Confirmer la commande'**
  String get confirmOrder;

  /// No description provided for @statusUpdated.
  ///
  /// In fr, this message translates to:
  /// **'Statut mis à jour'**
  String get statusUpdated;

  /// No description provided for @orderStatusUpdated.
  ///
  /// In fr, this message translates to:
  /// **'Statut de la commande mis à jour'**
  String get orderStatusUpdated;

  /// No description provided for @cannotSendNotification.
  ///
  /// In fr, this message translates to:
  /// **'Impossible d\'envoyer la notification : {error}'**
  String cannotSendNotification(String error);

  /// No description provided for @orderCancelledNotified.
  ///
  /// In fr, this message translates to:
  /// **'Commande annulée — le client a été notifié.'**
  String get orderCancelledNotified;

  /// No description provided for @orderCancelled.
  ///
  /// In fr, this message translates to:
  /// **'Commande annulée'**
  String get orderCancelled;

  /// No description provided for @cancelOrder.
  ///
  /// In fr, this message translates to:
  /// **'Annuler la commande'**
  String get cancelOrder;

  /// No description provided for @yesCancel.
  ///
  /// In fr, this message translates to:
  /// **'Oui, annuler'**
  String get yesCancel;

  /// No description provided for @spaCategoryAll.
  ///
  /// In fr, this message translates to:
  /// **'Tous'**
  String get spaCategoryAll;

  /// No description provided for @spaCategoryMassage.
  ///
  /// In fr, this message translates to:
  /// **'Massages'**
  String get spaCategoryMassage;

  /// No description provided for @spaCategoryFacial.
  ///
  /// In fr, this message translates to:
  /// **'Soins Visage'**
  String get spaCategoryFacial;

  /// No description provided for @spaCategoryBody.
  ///
  /// In fr, this message translates to:
  /// **'Soins Corps'**
  String get spaCategoryBody;

  /// No description provided for @spaCategoryHammam.
  ///
  /// In fr, this message translates to:
  /// **'Hammam'**
  String get spaCategoryHammam;

  /// No description provided for @spaAndWellness.
  ///
  /// In fr, this message translates to:
  /// **'Spa & Bien-être'**
  String get spaAndWellness;

  /// No description provided for @spaServiceFallback.
  ///
  /// In fr, this message translates to:
  /// **'Service Spa'**
  String get spaServiceFallback;

  /// No description provided for @timeLabel.
  ///
  /// In fr, this message translates to:
  /// **'Heure'**
  String get timeLabel;

  /// No description provided for @excursionFallback.
  ///
  /// In fr, this message translates to:
  /// **'Excursion'**
  String get excursionFallback;

  /// No description provided for @locationEnableSettings.
  ///
  /// In fr, this message translates to:
  /// **'Activez la localisation dans les paramètres.'**
  String get locationEnableSettings;

  /// No description provided for @locationAccessDenied.
  ///
  /// In fr, this message translates to:
  /// **'Accès à la position refusé.'**
  String get locationAccessDenied;

  /// No description provided for @locationCurrentPos.
  ///
  /// In fr, this message translates to:
  /// **'Position actuelle'**
  String get locationCurrentPos;

  /// No description provided for @locationError.
  ///
  /// In fr, this message translates to:
  /// **'Impossible d\'obtenir la position'**
  String get locationError;

  /// No description provided for @vehicleRequestType.
  ///
  /// In fr, this message translates to:
  /// **'Type de demande'**
  String get vehicleRequestType;

  /// No description provided for @vehicleTypeTaxi.
  ///
  /// In fr, this message translates to:
  /// **'Taxi'**
  String get vehicleTypeTaxi;

  /// No description provided for @vehicleTypeRental.
  ///
  /// In fr, this message translates to:
  /// **'Location'**
  String get vehicleTypeRental;

  /// No description provided for @taxiPickup.
  ///
  /// In fr, this message translates to:
  /// **'Prise en charge'**
  String get taxiPickup;

  /// No description provided for @taxiPickupHint.
  ///
  /// In fr, this message translates to:
  /// **'Adresse ou position'**
  String get taxiPickupHint;

  /// No description provided for @taxiMyLocation.
  ///
  /// In fr, this message translates to:
  /// **'Ma position'**
  String get taxiMyLocation;

  /// No description provided for @taxiDestination.
  ///
  /// In fr, this message translates to:
  /// **'Destination'**
  String get taxiDestination;

  /// No description provided for @taxiDestinationHint.
  ///
  /// In fr, this message translates to:
  /// **'Adresse de destination'**
  String get taxiDestinationHint;

  /// No description provided for @taxiDistanceOption.
  ///
  /// In fr, this message translates to:
  /// **'Distance (km, optionnel)'**
  String get taxiDistanceOption;

  /// No description provided for @taxiDistanceHint.
  ///
  /// In fr, this message translates to:
  /// **'Ex: 5.2'**
  String get taxiDistanceHint;

  /// No description provided for @rentalAllTypes.
  ///
  /// In fr, this message translates to:
  /// **'Tous les types'**
  String get rentalAllTypes;

  /// No description provided for @rentalSedan.
  ///
  /// In fr, this message translates to:
  /// **'Berline'**
  String get rentalSedan;

  /// No description provided for @rentalSuv.
  ///
  /// In fr, this message translates to:
  /// **'SUV'**
  String get rentalSuv;

  /// No description provided for @rentalMinibus.
  ///
  /// In fr, this message translates to:
  /// **'Minibus'**
  String get rentalMinibus;

  /// No description provided for @rentalVan.
  ///
  /// In fr, this message translates to:
  /// **'Van'**
  String get rentalVan;

  /// No description provided for @rentalOther.
  ///
  /// In fr, this message translates to:
  /// **'Autre'**
  String get rentalOther;

  /// No description provided for @rentalChooseVehicle.
  ///
  /// In fr, this message translates to:
  /// **'Choisir un véhicule'**
  String get rentalChooseVehicle;

  /// No description provided for @rentalTypeLabel.
  ///
  /// In fr, this message translates to:
  /// **'Type'**
  String get rentalTypeLabel;

  /// No description provided for @rentalSeatsMin.
  ///
  /// In fr, this message translates to:
  /// **'Places min.'**
  String get rentalSeatsMin;

  /// No description provided for @rentalSeatsAll.
  ///
  /// In fr, this message translates to:
  /// **'Toutes'**
  String get rentalSeatsAll;

  /// No description provided for @rentalSeatsCount.
  ///
  /// In fr, this message translates to:
  /// **'{count} place(s)'**
  String rentalSeatsCount(String count);

  /// No description provided for @rentalNoVehicleFound.
  ///
  /// In fr, this message translates to:
  /// **'Aucun véhicule pour ces critères.'**
  String get rentalNoVehicleFound;

  /// No description provided for @rentalSeatsPl.
  ///
  /// In fr, this message translates to:
  /// **'{count} pl.'**
  String rentalSeatsPl(String count);

  /// No description provided for @rentalDaysHint.
  ///
  /// In fr, this message translates to:
  /// **'Ex: 2'**
  String get rentalDaysHint;

  /// No description provided for @rentalDurationHours.
  ///
  /// In fr, this message translates to:
  /// **'Durée (heures)'**
  String get rentalDurationHours;

  /// No description provided for @rentalDurationHint.
  ///
  /// In fr, this message translates to:
  /// **'Ex: 8 (demi-journée si ≤ 5 h)'**
  String get rentalDurationHint;

  /// No description provided for @rentalEstimate.
  ///
  /// In fr, this message translates to:
  /// **'Estimation : {price} FCFA'**
  String rentalEstimate(String price);

  /// No description provided for @rentalErrorDestination.
  ///
  /// In fr, this message translates to:
  /// **'Indiquez l\'adresse de destination.'**
  String get rentalErrorDestination;

  /// No description provided for @rentalErrorChooseVehicle.
  ///
  /// In fr, this message translates to:
  /// **'Choisissez un véhicule dans la liste.'**
  String get rentalErrorChooseVehicle;

  /// No description provided for @rentalErrorVehicleOrDetails.
  ///
  /// In fr, this message translates to:
  /// **'Choisissez Taxi ou Location, ou décrivez votre demande.'**
  String get rentalErrorVehicleOrDetails;

  /// No description provided for @sessionExpiredNeedClientCodeRequest.
  ///
  /// In fr, this message translates to:
  /// **'Votre séjour n\'est plus actif. Entrez votre code client pour effectuer la demande.'**
  String get sessionExpiredNeedClientCodeRequest;

  /// No description provided for @palaceConciergeServices.
  ///
  /// In fr, this message translates to:
  /// **'Services Palace / Conciergerie'**
  String get palaceConciergeServices;

  /// No description provided for @palaceConciergeTracking.
  ///
  /// In fr, this message translates to:
  /// **'Suivi des demandes palace & conciergerie'**
  String get palaceConciergeTracking;

  /// No description provided for @palaceConciergeServiceSingle.
  ///
  /// In fr, this message translates to:
  /// **'Service palace / conciergerie'**
  String get palaceConciergeServiceSingle;

  /// No description provided for @scheduledForDate.
  ///
  /// In fr, this message translates to:
  /// **'Prévue pour {date}'**
  String scheduledForDate(String date);

  /// No description provided for @requestDetailsOnly.
  ///
  /// In fr, this message translates to:
  /// **'Détails de la demande'**
  String get requestDetailsOnly;

  /// No description provided for @cancellationReason.
  ///
  /// In fr, this message translates to:
  /// **'Motif d\'annulation'**
  String get cancellationReason;

  /// No description provided for @acceptRequestTitle.
  ///
  /// In fr, this message translates to:
  /// **'Accepter la demande'**
  String get acceptRequestTitle;

  /// No description provided for @acceptRequestMessage.
  ///
  /// In fr, this message translates to:
  /// **'Accepter cette demande de service palace / conciergerie ?'**
  String get acceptRequestMessage;

  /// No description provided for @completeRequestTitle.
  ///
  /// In fr, this message translates to:
  /// **'Clôturer la demande'**
  String get completeRequestTitle;

  /// No description provided for @completeRequestMessage.
  ///
  /// In fr, this message translates to:
  /// **'Clôturer cette demande ?'**
  String get completeRequestMessage;

  /// No description provided for @rejectRequestTitle.
  ///
  /// In fr, this message translates to:
  /// **'Refuser la demande'**
  String get rejectRequestTitle;

  /// No description provided for @rejectRequestMessage.
  ///
  /// In fr, this message translates to:
  /// **'Refuser cette demande de service palace ?'**
  String get rejectRequestMessage;

  /// No description provided for @cancelRequestMessage.
  ///
  /// In fr, this message translates to:
  /// **'Annuler cette demande de service palace ?'**
  String get cancelRequestMessage;

  /// No description provided for @cancellationReasonHint.
  ///
  /// In fr, this message translates to:
  /// **'Motif de l\'annulation'**
  String get cancellationReasonHint;

  /// No description provided for @validationReasonRequired.
  ///
  /// In fr, this message translates to:
  /// **'Veuillez préciser un motif.'**
  String get validationReasonRequired;

  /// No description provided for @requestDetailTitle.
  ///
  /// In fr, this message translates to:
  /// **'Détail de la demande palace / conciergerie'**
  String get requestDetailTitle;

  /// No description provided for @laundryRequestDetailTitle.
  ///
  /// In fr, this message translates to:
  /// **'Détail de la demande de blanchisserie'**
  String get laundryRequestDetailTitle;

  /// No description provided for @laundryNoItemsInRequest.
  ///
  /// In fr, this message translates to:
  /// **'Aucun article trouvé pour cette demande.'**
  String get laundryNoItemsInRequest;

  /// No description provided for @hotelMap.
  ///
  /// In fr, this message translates to:
  /// **'Plan'**
  String get hotelMap;

  /// No description provided for @albumsTitle.
  ///
  /// In fr, this message translates to:
  /// **'Albums'**
  String get albumsTitle;

  /// No description provided for @photoCount.
  ///
  /// In fr, this message translates to:
  /// **'{count} photo(s)'**
  String photoCount(int count);

  /// No description provided for @noPhoto.
  ///
  /// In fr, this message translates to:
  /// **'Aucune photo'**
  String get noPhoto;

  /// No description provided for @presentationTitle.
  ///
  /// In fr, this message translates to:
  /// **'Présentation'**
  String get presentationTitle;

  /// No description provided for @addressTitle.
  ///
  /// In fr, this message translates to:
  /// **'Adresse'**
  String get addressTitle;

  /// No description provided for @phoneAbbr.
  ///
  /// In fr, this message translates to:
  /// **'Tél.'**
  String get phoneAbbr;

  /// No description provided for @staffEmergencySubtitle.
  ///
  /// In fr, this message translates to:
  /// **'Alertes médecin / sécurité en cours'**
  String get staffEmergencySubtitle;

  /// No description provided for @guestEmergencySubtitle.
  ///
  /// In fr, this message translates to:
  /// **'Vos demandes Assistance & Urgence'**
  String get guestEmergencySubtitle;

  /// No description provided for @noEmergencyAlerts.
  ///
  /// In fr, this message translates to:
  /// **'Aucune alerte Assistance & Urgence en cours.'**
  String get noEmergencyAlerts;

  /// No description provided for @acceptEmergencyAlertMessage.
  ///
  /// In fr, this message translates to:
  /// **'Accepter cette alerte Assistance & Urgence ?'**
  String get acceptEmergencyAlertMessage;

  /// No description provided for @cancelEmergencyAlertMessage.
  ///
  /// In fr, this message translates to:
  /// **'Annuler cette alerte ?'**
  String get cancelEmergencyAlertMessage;

  /// No description provided for @reasonOptional.
  ///
  /// In fr, this message translates to:
  /// **'Motif (optionnel)'**
  String get reasonOptional;

  /// No description provided for @reasonRequired.
  ///
  /// In fr, this message translates to:
  /// **'Veuillez préciser un motif.'**
  String get reasonRequired;

  /// No description provided for @accept.
  ///
  /// In fr, this message translates to:
  /// **'Accepter'**
  String get accept;

  /// No description provided for @unidentifiedRoom.
  ///
  /// In fr, this message translates to:
  /// **'Chambre non identifiée'**
  String get unidentifiedRoom;

  /// No description provided for @requestFromRoom.
  ///
  /// In fr, this message translates to:
  /// **'Demande depuis {roomInfo}'**
  String requestFromRoom(String roomInfo);

  /// No description provided for @newStaffMessage.
  ///
  /// In fr, this message translates to:
  /// **'Nouveau message du staff'**
  String get newStaffMessage;

  /// No description provided for @startConversation.
  ///
  /// In fr, this message translates to:
  /// **'Commencez la conversation avec la réception.'**
  String get startConversation;

  /// No description provided for @newMessageSingular.
  ///
  /// In fr, this message translates to:
  /// **'1 nouveau message'**
  String get newMessageSingular;

  /// No description provided for @newMessagesPlural.
  ///
  /// In fr, this message translates to:
  /// **'nouveaux messages'**
  String get newMessagesPlural;

  /// No description provided for @yesterday.
  ///
  /// In fr, this message translates to:
  /// **'Hier'**
  String get yesterday;

  /// No description provided for @imageUnavailable.
  ///
  /// In fr, this message translates to:
  /// **'Image indisponible'**
  String get imageUnavailable;

  /// No description provided for @voiceMessage.
  ///
  /// In fr, this message translates to:
  /// **'Message vocal'**
  String get voiceMessage;

  /// No description provided for @microphonePermission.
  ///
  /// In fr, this message translates to:
  /// **'Autorisez l’accès au micro pour envoyer une note vocale.'**
  String get microphonePermission;

  /// No description provided for @sportCategory.
  ///
  /// In fr, this message translates to:
  /// **'Sport'**
  String get sportCategory;

  /// No description provided for @leisureCategory.
  ///
  /// In fr, this message translates to:
  /// **'Loisirs'**
  String get leisureCategory;

  /// No description provided for @timeoutError.
  ///
  /// In fr, this message translates to:
  /// **'Délai dépassé. Vérifiez votre connexion.'**
  String get timeoutError;

  /// No description provided for @viewMyRequests.
  ///
  /// In fr, this message translates to:
  /// **'Voir mes demandes'**
  String get viewMyRequests;

  /// No description provided for @datePrefix.
  ///
  /// In fr, this message translates to:
  /// **'Date: '**
  String get datePrefix;

  /// No description provided for @timePrefix.
  ///
  /// In fr, this message translates to:
  /// **'Heure: '**
  String get timePrefix;

  /// No description provided for @requestDemandeSuffix.
  ///
  /// In fr, this message translates to:
  /// **' - Demande'**
  String get requestDemandeSuffix;

  /// No description provided for @sportFitnessCoachBooking.
  ///
  /// In fr, this message translates to:
  /// **'Sport & Fitness - Réservation coach personnel'**
  String get sportFitnessCoachBooking;

  /// No description provided for @golfPrefix.
  ///
  /// In fr, this message translates to:
  /// **'Golf'**
  String get golfPrefix;

  /// No description provided for @tennisPrefix.
  ///
  /// In fr, this message translates to:
  /// **'Tennis'**
  String get tennisPrefix;

  /// No description provided for @guidedToursNotConfigured.
  ///
  /// In fr, this message translates to:
  /// **'Visites guidées non configurées. L\'établissement doit ajouter le service « Visites guidées personnalisées » dans le tableau de bord (Services Palace).'**
  String get guidedToursNotConfigured;

  /// No description provided for @transfersNotConfigured.
  ///
  /// In fr, this message translates to:
  /// **'Service Transferts & VTC non configuré. Contactez l\'établissement.'**
  String get transfersNotConfigured;

  /// No description provided for @pickupDestinationRequired.
  ///
  /// In fr, this message translates to:
  /// **'Indiquez le lieu de prise en charge et la destination.'**
  String get pickupDestinationRequired;

  /// No description provided for @exAirportHotel.
  ///
  /// In fr, this message translates to:
  /// **'Ex: Aéroport, Hôtel…'**
  String get exAirportHotel;

  /// No description provided for @exDowntownAddress.
  ///
  /// In fr, this message translates to:
  /// **'Ex: Centre-ville, Adresse…'**
  String get exDowntownAddress;

  /// No description provided for @vehicleRentalNotConfigured.
  ///
  /// In fr, this message translates to:
  /// **'Service Location de véhicule non configuré. Contactez l\'établissement.'**
  String get vehicleRentalNotConfigured;

  /// No description provided for @durationOrDaysRequired.
  ///
  /// In fr, this message translates to:
  /// **'Indiquez le nombre de jours ou la durée en heures.'**
  String get durationOrDaysRequired;

  /// No description provided for @exDays.
  ///
  /// In fr, this message translates to:
  /// **'Ex: 2'**
  String get exDays;

  /// No description provided for @exHours.
  ///
  /// In fr, this message translates to:
  /// **'Ex: 5 (demi-journée)'**
  String get exHours;

  /// No description provided for @profileRoom.
  ///
  /// In fr, this message translates to:
  /// **'Chambre'**
  String get profileRoom;

  /// No description provided for @profileHotel.
  ///
  /// In fr, this message translates to:
  /// **'Hôtel'**
  String get profileHotel;

  /// No description provided for @profileRole.
  ///
  /// In fr, this message translates to:
  /// **'Rôle'**
  String get profileRole;

  /// No description provided for @myInvoices.
  ///
  /// In fr, this message translates to:
  /// **'Mes Factures'**
  String get myInvoices;

  /// No description provided for @phone.
  ///
  /// In fr, this message translates to:
  /// **'Téléphone'**
  String get phone;

  /// No description provided for @noInvoicesTitle.
  ///
  /// In fr, this message translates to:
  /// **'Aucune facture'**
  String get noInvoicesTitle;

  /// No description provided for @noInvoicesSubtitle.
  ///
  /// In fr, this message translates to:
  /// **'Vous n\'avez pas encore de commandes terminées.'**
  String get noInvoicesSubtitle;

  /// No description provided for @generalError.
  ///
  /// In fr, this message translates to:
  /// **'Erreur'**
  String get generalError;

  /// No description provided for @orderReceipt.
  ///
  /// In fr, this message translates to:
  /// **'REÇU DE COMMANDE'**
  String get orderReceipt;

  /// No description provided for @hotelLabel.
  ///
  /// In fr, this message translates to:
  /// **'HÔTEL:'**
  String get hotelLabel;

  /// No description provided for @orderDateLabel.
  ///
  /// In fr, this message translates to:
  /// **'DATE COMMANDE:'**
  String get orderDateLabel;

  /// No description provided for @deliveryLabel.
  ///
  /// In fr, this message translates to:
  /// **'LIVRAISON:'**
  String get deliveryLabel;

  /// No description provided for @noItems.
  ///
  /// In fr, this message translates to:
  /// **'Aucun article'**
  String get noItems;

  /// No description provided for @totalToPay.
  ///
  /// In fr, this message translates to:
  /// **'TOTAL À PAYER'**
  String get totalToPay;

  /// No description provided for @taxesIncluded.
  ///
  /// In fr, this message translates to:
  /// **'Toute taxe et frais de service inclus'**
  String get taxesIncluded;

  /// No description provided for @thankYouForOrder.
  ///
  /// In fr, this message translates to:
  /// **'Merci pour votre commande !'**
  String get thankYouForOrder;

  /// No description provided for @invoiceRoomLabel.
  ///
  /// In fr, this message translates to:
  /// **'CHAMBRE:'**
  String get invoiceRoomLabel;
}

class _AppLocalizationsDelegate
    extends LocalizationsDelegate<AppLocalizations> {
  const _AppLocalizationsDelegate();

  @override
  Future<AppLocalizations> load(Locale locale) {
    return SynchronousFuture<AppLocalizations>(lookupAppLocalizations(locale));
  }

  @override
  bool isSupported(Locale locale) =>
      <String>['ar', 'en', 'es', 'fr'].contains(locale.languageCode);

  @override
  bool shouldReload(_AppLocalizationsDelegate old) => false;
}

AppLocalizations lookupAppLocalizations(Locale locale) {
  // Lookup logic when only language code is specified.
  switch (locale.languageCode) {
    case 'ar':
      return AppLocalizationsAr();
    case 'en':
      return AppLocalizationsEn();
    case 'es':
      return AppLocalizationsEs();
    case 'fr':
      return AppLocalizationsFr();
  }

  throw FlutterError(
    'AppLocalizations.delegate failed to load unsupported locale "$locale". This is likely '
    'an issue with the localizations generation tool. Please file an issue '
    'on GitHub with a reproducible sample app and the gen-l10n configuration '
    'that was used.',
  );
}
