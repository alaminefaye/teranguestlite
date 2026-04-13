<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FcmTokenController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\RoomServiceController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\RestaurantController;
use App\Http\Controllers\Api\SpaServiceController;
use App\Http\Controllers\Api\ExcursionController;
use App\Http\Controllers\Api\LaundryServiceController;
use App\Http\Controllers\Api\PalaceServiceController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\AdminSummaryController;
use App\Http\Controllers\Api\ChatController as ApiChatController;
use App\Http\Controllers\Api\VitrineController;

/*
|--------------------------------------------------------------------------
| API Routes - Teranga Guest
|--------------------------------------------------------------------------
*/

// ==========================================
// VITRINE (Public) — lecture seule, une seule entreprise
// ==========================================
Route::prefix('vitrine')->group(function () {
    Route::get('/enterprise', [VitrineController::class, 'enterprise']);

    Route::get('/restaurants', [VitrineController::class, 'restaurants']);
    Route::get('/restaurants/{id}', [VitrineController::class, 'restaurantShow'])->whereNumber('id');

    Route::get('/room-service/categories', [VitrineController::class, 'roomServiceCategories']);
    Route::get('/room-service/items', [VitrineController::class, 'roomServiceItems']);
    Route::get('/room-service/items/{id}', [VitrineController::class, 'roomServiceItemShow'])->whereNumber('id');

    Route::get('/spa-services', [VitrineController::class, 'spaServices']);
    Route::get('/spa-services/{id}', [VitrineController::class, 'spaServiceShow'])->whereNumber('id');

    Route::get('/excursions', [VitrineController::class, 'excursions']);
    Route::get('/excursions/{id}', [VitrineController::class, 'excursionShow'])->whereNumber('id');

    Route::get('/laundry/services', [VitrineController::class, 'laundryServices']);
    Route::get('/palace-services', [VitrineController::class, 'palaceServices']);
    Route::get('/vehicles', [VitrineController::class, 'vehicles']);

    Route::get('/leisure-categories', [VitrineController::class, 'leisureCategories']);
    Route::get('/amenity-categories', [VitrineController::class, 'amenityCategories']);

    Route::get('/establishments', [VitrineController::class, 'establishments']);
    Route::get('/establishments/{id}', [VitrineController::class, 'establishmentShow'])->whereNumber('id');

    Route::get('/announcements', [VitrineController::class, 'announcements']);

    Route::get('/guides', [VitrineController::class, 'guides']);
});

// ==========================================
// AUTHENTIFICATION (Public) — limité pour limiter le brute-force
// ==========================================
Route::prefix('auth')->middleware('throttle:api-auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/web-login', [AuthController::class, 'webLogin']);
});

// ==========================================
// TABLETTE EN CHAMBRE - Validation code client (sans auth) — limité par IP
// ==========================================
Route::prefix('tablet')->middleware('throttle:api-tablet')->group(function () {
    Route::post('/hotel-infos', [\App\Http\Controllers\Api\TabletSessionController::class, 'hotelInfos']);
    Route::post('/session-by-room', [\App\Http\Controllers\Api\TabletSessionController::class, 'sessionByRoom']);
    Route::post('/validate-code', [\App\Http\Controllers\Api\TabletSessionController::class, 'validateCode']);
    Route::post('/validate-session', [\App\Http\Controllers\Api\TabletSessionController::class, 'validateSession']);
    Route::post('/checkout', [\App\Http\Controllers\Api\TabletSessionController::class, 'checkout']);
});

// ==========================================
// ROUTES PROTÉGÉES (Authentification requise)
// ==========================================
Route::middleware('auth:sanctum')->group(function () {

    // ==========================================
    // AUTHENTIFICATION
    // ==========================================
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::get('/profile', [AuthController::class, 'profile']);
    });

    // Profile alternatif
    Route::get('/user', [AuthController::class, 'profile']);

    // Session + code client par room_number (utilisateur connecté) — pour pré-remplissage code sur résas, commandes, etc.
    Route::post('/me/session-by-room', [\App\Http\Controllers\Api\TabletSessionController::class, 'sessionByRoomAuthenticated']);

    // Récapitulatif admin (badges & compteurs)
    Route::get('/admin-summary', [AdminSummaryController::class, 'index']);

    // ==========================================
    // FCM TOKEN MANAGEMENT
    // ==========================================
    Route::post('/fcm-token', [FcmTokenController::class, 'store']);
    Route::delete('/fcm-token', [FcmTokenController::class, 'destroy']);

    // ==========================================
    // ROOM SERVICE (Menu & Articles)
    // ==========================================
    Route::prefix('room-service')->group(function () {
        Route::get('/categories', [RoomServiceController::class, 'categories']);
        Route::get('/items', [RoomServiceController::class, 'items']);
        Route::get('/items/{id}', [RoomServiceController::class, 'show']);
        Route::post('/checkout', [RoomServiceController::class, 'checkout']);
    });

    // ==========================================
    // COMMANDES (Orders)
    // ==========================================
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::get('/{id}', [OrderController::class, 'show']);
        Route::post('/{id}/reorder', [OrderController::class, 'reorder']);
        Route::post('/{id}/cancel', [OrderController::class, 'cancel']);
        Route::post('/{id}/cancel-by-staff', [OrderController::class, 'cancelByStaff']);
        Route::post('/{id}/status', [OrderController::class, 'updateStatus']);
        Route::post('/{id}/notify-room-service', [OrderController::class, 'notifyRoomService']);
    });

    // ==========================================
    // RESTAURANTS & BARS
    // ==========================================
    Route::prefix('restaurants')->group(function () {
        Route::get('/', [RestaurantController::class, 'index']);
        Route::get('/{id}', [RestaurantController::class, 'show']);
        Route::post('/{id}/reserve', [RestaurantController::class, 'reserve']);
    });
    Route::get('/my-restaurant-reservations', [RestaurantController::class, 'myReservations']);
    Route::post('/my-restaurant-reservations/{id}/cancel', [RestaurantController::class, 'cancelReservation']);
    Route::post('/restaurant-reservations/{id}/status', [RestaurantController::class, 'updateReservationStatus']);

    // ==========================================
    // SPA & BIEN-ÊTRE
    // ==========================================
    Route::prefix('spa-services')->group(function () {
        Route::get('/', [SpaServiceController::class, 'index']);
        Route::get('/{id}', [SpaServiceController::class, 'show']);
        Route::post('/{id}/reserve', [SpaServiceController::class, 'reserve']);
    });
    Route::get('/my-spa-reservations', [SpaServiceController::class, 'myReservations']);
    Route::post('/my-spa-reservations/{id}/cancel', [SpaServiceController::class, 'cancelReservation']);
    Route::post('/spa-reservations/{id}/status', [SpaServiceController::class, 'updateReservationStatus']);
    Route::post('/my-spa-reservations/{id}/accept-reschedule', [SpaServiceController::class, 'acceptRescheduledReservation']);

    // ==========================================
    // EXCURSIONS
    // ==========================================
    Route::prefix('excursions')->group(function () {
        Route::get('/', [ExcursionController::class, 'index']);
        Route::get('/{id}', [ExcursionController::class, 'show']);
        Route::post('/{id}/book', [ExcursionController::class, 'book']);
    });
    Route::get('/my-excursion-bookings', [ExcursionController::class, 'myBookings']);
    Route::post('/excursion-bookings/{id}/status', [ExcursionController::class, 'updateBookingStatus']);

    // ==========================================
    // BLANCHISSERIE (Laundry)
    // ==========================================
    Route::prefix('laundry')->group(function () {
        Route::get('/services', [LaundryServiceController::class, 'index']);
        Route::post('/request', [LaundryServiceController::class, 'request']);
    });
    Route::get('/my-laundry-requests', [LaundryServiceController::class, 'myRequests']);
    Route::post('/laundry-requests/{id}/status', [LaundryServiceController::class, 'updateRequestStatus']);

    // ==========================================
    // AMENITIES & CONCIERGERIE (catégories + articles dynamiques)
    // ==========================================
    Route::get('/amenity-categories', [\App\Http\Controllers\Api\AmenityCategoryController::class, 'index']);

    // ==========================================
    // GUIDES & INFOS (Numéros urgence, lieux, etc.)
    // ==========================================
    Route::get('/guides', [\App\Http\Controllers\GuideCategoryController::class, 'index']);

    // ==========================================
    // BIEN-ÊTRE, SPORT & LOISIRS (Spa, Golf/Tennis, Fitness - dynamique)
    // ==========================================
    Route::get('/leisure-categories', [\App\Http\Controllers\Api\LeisureCategoryController::class, 'index']);

    // ==========================================
    // SERVICES PALACE
    // ==========================================
    Route::prefix('palace-services')->group(function () {
        Route::get('/', [PalaceServiceController::class, 'index']);
        Route::get('/{id}', [PalaceServiceController::class, 'show']);
        Route::post('/{id}/request', [PalaceServiceController::class, 'request']);
    });
    Route::get('/my-palace-requests', [PalaceServiceController::class, 'myRequests']);
    Route::post('/palace-requests/{id}/cancel', [PalaceServiceController::class, 'cancel']);
    Route::post('/palace-requests/{id}/status', [PalaceServiceController::class, 'updateRequestStatus']);

    // Véhicules (pour formulaire Location)
    Route::get('/vehicles', [VehicleController::class, 'index']);

    // ==========================================
    // NOTIFICATIONS (In-App Polling Fallback)
    // ==========================================
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread', [NotificationController::class, 'unread']);
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/cleanup', [NotificationController::class, 'cleanup']);
    });

    // ==========================================
    // CHAT INVITÉ / MESSAGES HÔTEL
    // ==========================================
    Route::prefix('chat')->group(function () {
        Route::get('/messages', [ApiChatController::class, 'index']);
        Route::post('/messages', [ApiChatController::class, 'store']);
        Route::delete('/messages/{message}', [ApiChatController::class, 'destroyMessage']);
    });

    // ==========================================
    // CHAT STAFF (Conversations invités)
    // ==========================================
    Route::prefix('staff/chat')->group(function () {
        Route::get('/conversations', [ApiChatController::class, 'staffConversations']);
        Route::get('/conversations/{conversation}', [ApiChatController::class, 'staffConversationMessages']);
        Route::post('/conversations/{conversation}/messages', [ApiChatController::class, 'staffReply']);
        Route::delete('/conversations/{conversation}', [ApiChatController::class, 'staffDestroyConversation']);
        Route::delete('/conversations/{conversation}/messages/{message}', [ApiChatController::class, 'staffDestroyMessage']);
    });

    // ==========================================
    // NOS ÉTABLISSEMENTS (autres sites du groupe — Hotel Infos)
    // ==========================================
    Route::prefix('establishments')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\EstablishmentController::class, 'index']);
        Route::get('/{id}', [\App\Http\Controllers\Api\EstablishmentController::class, 'show']);
    });

    // ==========================================
    // ANNONCES & PUBLICITÉS
    // ==========================================
    Route::prefix('announcements')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\AnnouncementController::class, 'index']);
        Route::post('/{id}/view', [\App\Http\Controllers\Api\AnnouncementController::class, 'recordView']);
    });

    // ==========================================
    // AVIS / SATISFACTION CLIENT
    // ==========================================
    Route::prefix('reviews')->group(function () {
        Route::get('/pending', [\App\Http\Controllers\Api\GuestReviewController::class, 'pending']);
        Route::get('/', [\App\Http\Controllers\Api\GuestReviewController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Api\GuestReviewController::class, 'store']);
    });
});
