<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\EnterpriseController;
use App\Http\Controllers\Auth\AuthController;

// Auth
Route::get('/signin', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/signin', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/signup', function () {
    return view('pages.auth.signup', ['title' => 'Inscription']);
})->name('register');

// Change Password (première connexion)
Route::middleware(['auth'])->group(function () {
    Route::get('/auth/change-password', [\App\Http\Controllers\Auth\ChangePasswordController::class, 'showChangePasswordForm'])->name('auth.change-password.form');
    Route::post('/auth/change-password', [\App\Http\Controllers\Auth\ChangePasswordController::class, 'changePassword'])->name('auth.change-password.update');
});

// Routes protégées (à ajouter middleware auth plus tard)
Route::middleware(['auth'])->group(function () {
    // Profile
    Route::get('/profile', function () {
        return view('pages.profile', ['title' => 'Profil']);
    })->name('profile');

    // Routes Super Admin
    Route::prefix('admin')->name('admin.')->middleware(['enterprise'])->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('enterprises', EnterpriseController::class);
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
    });

    // Routes Admin Hôtel
    Route::prefix('dashboard')->name('dashboard.')->middleware(['enterprise'])->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('index');
        
        // Chambres
        Route::resource('rooms', \App\Http\Controllers\Dashboard\RoomController::class);
        
        // Réservations
        Route::resource('reservations', \App\Http\Controllers\Dashboard\ReservationController::class);
        
        // Actions réservations
        Route::post('reservations/{reservation}/checkin', [\App\Http\Controllers\Dashboard\ReservationController::class, 'checkIn'])
            ->name('reservations.checkin');
        Route::post('reservations/{reservation}/checkout', [\App\Http\Controllers\Dashboard\ReservationController::class, 'checkOut'])
            ->name('reservations.checkout');
        Route::post('reservations/{reservation}/cancel', [\App\Http\Controllers\Dashboard\ReservationController::class, 'cancel'])
            ->name('reservations.cancel');
        Route::post('reservations/{reservation}/settle', [\App\Http\Controllers\Dashboard\ReservationController::class, 'settle'])
            ->name('reservations.settle');
        
        // Clients (invités) - code tablette
        Route::resource('guests', \App\Http\Controllers\Dashboard\GuestController::class);
        Route::post('guests/{guest}/regenerate-code', [\App\Http\Controllers\Dashboard\GuestController::class, 'regenerateCode'])
            ->name('guests.regenerate-code');
        
        // Menus
        Route::resource('menu-categories', \App\Http\Controllers\Dashboard\MenuCategoryController::class);
        Route::resource('menu-items', \App\Http\Controllers\Dashboard\MenuItemController::class);
        
        // Restaurants & Bars
        Route::resource('restaurants', \App\Http\Controllers\Dashboard\RestaurantController::class);
        
        // Services Spa
        Route::resource('spa-services', \App\Http\Controllers\Dashboard\SpaServiceController::class);
        
        // Blanchisserie
        Route::resource('laundry-services', \App\Http\Controllers\Dashboard\LaundryServiceController::class);
        
        // Services Palace
        Route::resource('palace-services', \App\Http\Controllers\Dashboard\PalaceServiceController::class);
        
        // Excursions
        Route::resource('excursions', \App\Http\Controllers\Dashboard\ExcursionController::class);

        // Réservations & demandes (spa, excursions, restaurants, blanchisserie, palace)
        Route::get('spa-reservations', [\App\Http\Controllers\Dashboard\SpaReservationsController::class, 'index'])->name('spa-reservations.index');
        Route::get('excursion-bookings', [\App\Http\Controllers\Dashboard\ExcursionBookingsController::class, 'index'])->name('excursion-bookings.index');
        Route::get('restaurant-reservations', [\App\Http\Controllers\Dashboard\RestaurantReservationsController::class, 'index'])->name('restaurant-reservations.index');
        Route::get('laundry-requests', [\App\Http\Controllers\Dashboard\LaundryRequestsController::class, 'index'])->name('laundry-requests.index');
        Route::get('palace-requests', [\App\Http\Controllers\Dashboard\PalaceRequestsController::class, 'index'])->name('palace-requests.index');
        
        // Staff (personnel de l'hôtel)
        Route::get('staff', [\App\Http\Controllers\Dashboard\StaffController::class, 'index'])->name('staff.index');

        // Accès tablettes : comptes "Client Chambre XXX" (User role=guest) — gérant de l'hôtel
        Route::get('tablet-accesses', [\App\Http\Controllers\Dashboard\TabletAccessController::class, 'index'])->name('tablet-accesses.index');
        Route::get('tablet-accesses/create', [\App\Http\Controllers\Dashboard\TabletAccessController::class, 'create'])->name('tablet-accesses.create');
        Route::post('tablet-accesses', [\App\Http\Controllers\Dashboard\TabletAccessController::class, 'store'])->name('tablet-accesses.store');
        Route::get('tablet-accesses/{id}/edit', [\App\Http\Controllers\Dashboard\TabletAccessController::class, 'edit'])->name('tablet-accesses.edit');
        Route::put('tablet-accesses/{id}', [\App\Http\Controllers\Dashboard\TabletAccessController::class, 'update'])->name('tablet-accesses.update');
        Route::delete('tablet-accesses/{id}', [\App\Http\Controllers\Dashboard\TabletAccessController::class, 'destroy'])->name('tablet-accesses.destroy');

        // Commandes
        Route::resource('orders', \App\Http\Controllers\Dashboard\OrderController::class);
        
        // Actions commandes
        Route::post('orders/{order}/confirm', [\App\Http\Controllers\Dashboard\OrderController::class, 'confirm'])
            ->name('orders.confirm');
        Route::post('orders/{order}/prepare', [\App\Http\Controllers\Dashboard\OrderController::class, 'prepare'])
            ->name('orders.prepare');
        Route::post('orders/{order}/ready', [\App\Http\Controllers\Dashboard\OrderController::class, 'markReady'])
            ->name('orders.ready');
        Route::post('orders/{order}/deliver', [\App\Http\Controllers\Dashboard\OrderController::class, 'deliver'])
            ->name('orders.deliver');
        Route::post('orders/{order}/complete', [\App\Http\Controllers\Dashboard\OrderController::class, 'complete'])
            ->name('orders.complete');
        Route::post('orders/{order}/cancel', [\App\Http\Controllers\Dashboard\OrderController::class, 'cancel'])
            ->name('orders.cancel');
    });

    // Routes Guest (Client)
    Route::prefix('guest')->name('guest.')->middleware(['enterprise'])->group(function () {
        Route::get('/', [\App\Http\Controllers\Guest\GuestDashboardController::class, 'index'])->name('dashboard');
        
        // Services centraux
        Route::get('/services', [\App\Http\Controllers\Guest\ServicesController::class, 'index'])
            ->name('services.index');
        
        // Room Service
        Route::get('/room-service', [\App\Http\Controllers\Guest\RoomServiceController::class, 'index'])
            ->name('room-service.index');
        Route::get('/room-service/cart', [\App\Http\Controllers\Guest\RoomServiceController::class, 'cart'])
            ->name('room-service.cart');
        Route::post('/room-service/checkout', [\App\Http\Controllers\Guest\RoomServiceController::class, 'checkout'])
            ->name('room-service.checkout');
        Route::get('/room-service/{menuItem}', [\App\Http\Controllers\Guest\RoomServiceController::class, 'show'])
            ->name('room-service.show');
        
        // Commandes
        Route::get('/orders', [\App\Http\Controllers\Guest\OrderController::class, 'index'])
            ->name('orders.index');
        Route::get('/orders/{order}', [\App\Http\Controllers\Guest\OrderController::class, 'show'])
            ->name('orders.show');
        Route::post('/orders/{order}/reorder', [\App\Http\Controllers\Guest\OrderController::class, 'reorder'])
            ->name('orders.reorder');
        
        // Restaurants
        Route::get('/restaurants', [\App\Http\Controllers\Guest\RestaurantController::class, 'index'])
            ->name('restaurants.index');
        Route::get('/restaurants/{restaurant}', [\App\Http\Controllers\Guest\RestaurantController::class, 'show'])
            ->name('restaurants.show');
        Route::post('/restaurants/{restaurant}/reserve', [\App\Http\Controllers\Guest\RestaurantController::class, 'reserve'])
            ->name('restaurants.reserve');
        Route::get('/my-restaurant-reservations', [\App\Http\Controllers\Guest\RestaurantController::class, 'myReservations'])
            ->name('restaurants.my-reservations');
        
        // Spa
        Route::get('/spa', [\App\Http\Controllers\Guest\SpaServiceController::class, 'index'])
            ->name('spa.index');
        Route::get('/spa/{spaService}', [\App\Http\Controllers\Guest\SpaServiceController::class, 'show'])
            ->name('spa.show');
        Route::post('/spa/{spaService}/reserve', [\App\Http\Controllers\Guest\SpaServiceController::class, 'reserve'])
            ->name('spa.reserve');
        Route::get('/my-spa-reservations', [\App\Http\Controllers\Guest\SpaServiceController::class, 'myReservations'])
            ->name('spa.my-reservations');
        
        // Excursions
        Route::get('/excursions', [\App\Http\Controllers\Guest\ExcursionController::class, 'index'])
            ->name('excursions.index');
        Route::get('/excursions/{excursion}', [\App\Http\Controllers\Guest\ExcursionController::class, 'show'])
            ->name('excursions.show');
        Route::post('/excursions/{excursion}/book', [\App\Http\Controllers\Guest\ExcursionController::class, 'book'])
            ->name('excursions.book');
        Route::get('/my-excursion-bookings', [\App\Http\Controllers\Guest\ExcursionController::class, 'myBookings'])
            ->name('excursions.my-bookings');
        
        // Blanchisserie
        Route::get('/laundry', [\App\Http\Controllers\Guest\LaundryServiceController::class, 'index'])
            ->name('laundry.index');
        Route::post('/laundry/request', [\App\Http\Controllers\Guest\LaundryServiceController::class, 'request'])
            ->name('laundry.request');
        Route::get('/my-laundry-requests', [\App\Http\Controllers\Guest\LaundryServiceController::class, 'myRequests'])
            ->name('laundry.my-requests');
        
        // Services Palace
        Route::get('/palace', [\App\Http\Controllers\Guest\PalaceServiceController::class, 'index'])
            ->name('palace.index');
        Route::get('/palace/{palaceService}', [\App\Http\Controllers\Guest\PalaceServiceController::class, 'show'])
            ->name('palace.show');
        Route::post('/palace/{palaceService}/request', [\App\Http\Controllers\Guest\PalaceServiceController::class, 'request'])
            ->name('palace.request');
        Route::get('/my-palace-requests', [\App\Http\Controllers\Guest\PalaceServiceController::class, 'myRequests'])
            ->name('palace.my-requests');
    });
});

// Redirection par défaut
Route::get('/', function () {
    if (auth()->check()) {
        if (auth()->user()->isSuperAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        if (auth()->user()->isAdmin() || auth()->user()->isStaff()) {
            return redirect()->route('dashboard.index');
        }
        // Guest
        return redirect()->route('guest.dashboard');
    }
    return redirect()->route('login');
});

// Pages TailAdmin (Form, Tables, Charts, UI, Blank, Error)
Route::get('/form-elements', function () {
    return view('pages.form.form-elements', ['title' => 'Form Elements']);
})->name('form-elements');

Route::get('/basic-tables', function () {
    return view('pages.tables.basic-tables', ['title' => 'Basic Tables']);
})->name('basic-tables');

Route::get('/blank', function () {
    return view('pages.blank', ['title' => 'Blank']);
})->name('blank');

Route::get('/error-404', function () {
    return view('pages.errors.error-404', ['title' => 'Error 404']);
})->name('error-404');

Route::get('/line-chart', function () {
    return view('pages.chart.line-chart', ['title' => 'Line Chart']);
})->name('line-chart');

Route::get('/bar-chart', function () {
    return view('pages.chart.bar-chart', ['title' => 'Bar Chart']);
})->name('bar-chart');

// UI elements
Route::get('/alerts', function () {
    return view('pages.ui-elements.alerts', ['title' => 'Alerts']);
})->name('alerts');

Route::get('/avatars', function () {
    return view('pages.ui-elements.avatars', ['title' => 'Avatars']);
})->name('avatars');

Route::get('/badge', function () {
    return view('pages.ui-elements.badges', ['title' => 'Badges']);
})->name('badges');

Route::get('/buttons', function () {
    return view('pages.ui-elements.buttons', ['title' => 'Buttons']);
})->name('buttons');

Route::get('/image', function () {
    return view('pages.ui-elements.images', ['title' => 'Images']);
})->name('images');

Route::get('/videos', function () {
    return view('pages.ui-elements.videos', ['title' => 'Videos']);
})->name('videos');
