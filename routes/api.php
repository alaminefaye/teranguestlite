<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FcmTokenController;
use App\Http\Controllers\Api\RoomServiceController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\RestaurantController;
use App\Http\Controllers\Api\SpaServiceController;
use App\Http\Controllers\Api\ExcursionController;
use App\Http\Controllers\Api\LaundryServiceController;
use App\Http\Controllers\Api\PalaceServiceController;

/*
|--------------------------------------------------------------------------
| API Routes - Teranga Guest
|--------------------------------------------------------------------------
*/

// ==========================================
// AUTHENTIFICATION (Public)
// ==========================================
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
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
    
    // ==========================================
    // EXCURSIONS
    // ==========================================
    Route::prefix('excursions')->group(function () {
        Route::get('/', [ExcursionController::class, 'index']);
        Route::get('/{id}', [ExcursionController::class, 'show']);
        Route::post('/{id}/book', [ExcursionController::class, 'book']);
    });
    Route::get('/my-excursion-bookings', [ExcursionController::class, 'myBookings']);
    
    // ==========================================
    // BLANCHISSERIE (Laundry)
    // ==========================================
    Route::prefix('laundry')->group(function () {
        Route::get('/services', [LaundryServiceController::class, 'index']);
        Route::post('/request', [LaundryServiceController::class, 'request']);
    });
    Route::get('/my-laundry-requests', [LaundryServiceController::class, 'myRequests']);
    
    // ==========================================
    // SERVICES PALACE
    // ==========================================
    Route::prefix('palace-services')->group(function () {
        Route::get('/', [PalaceServiceController::class, 'index']);
        Route::get('/{id}', [PalaceServiceController::class, 'show']);
        Route::post('/{id}/request', [PalaceServiceController::class, 'request']);
    });
    Route::get('/my-palace-requests', [PalaceServiceController::class, 'myRequests']);
});
