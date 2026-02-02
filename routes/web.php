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
