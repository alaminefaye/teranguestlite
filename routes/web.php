<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
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

// Politique de confidentialité (accès public)
Route::get('/politique-de-confidentialite', function () {
    return view('pages.legal.privacy-policy', [
        'title' => 'Politique de confidentialité',
        'contactEmail' => 'contact@universaltechnologiesafrica.com',
        'contactPhones' => ['+221 77 096 79 94', '+221 77 330 96 13'],
    ]);
})->name('privacy-policy');

// Change Password (première connexion)
Route::middleware(['auth'])->group(function () {
    Route::get('/auth/change-password', [\App\Http\Controllers\Auth\ChangePasswordController::class, 'showChangePasswordForm'])->name('auth.change-password.form');
    Route::post('/auth/change-password', [\App\Http\Controllers\Auth\ChangePasswordController::class, 'changePassword'])->name('auth.change-password.update');
});

// Routes protégées (à ajouter middleware auth plus tard)
Route::middleware(['auth'])->group(function () {
    // Profil (utilisateur + données établissement pour modifier son entreprise directement)
    Route::get('/profile', [\App\Http\Controllers\Dashboard\ProfileController::class, 'index'])->name('profile');
    Route::put('/dashboard/my-enterprise', [\App\Http\Controllers\Dashboard\ProfileController::class, 'updateEnterprise'])->name('dashboard.my-enterprise.update');

    // Routes Super Admin
    Route::prefix('admin')->name('admin.')->middleware(['enterprise'])->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('enterprises', EnterpriseController::class);
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class);

        // ==========================================
        // ANNONCES & PUBLICITÉS — Super Admin
        // ==========================================
        Route::resource('announcements', \App\Http\Controllers\Dashboard\AnnouncementController::class);
        Route::post('announcements/{announcement}/toggle', [\App\Http\Controllers\Dashboard\AnnouncementController::class, 'toggleActive'])->name('announcements.toggle');
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
        Route::get('reservations/{reservation}/invoice', [\App\Http\Controllers\Dashboard\ReservationController::class, 'invoice'])
            ->name('reservations.invoice');
        Route::get('reservations/{reservation}/invoice/pdf', [\App\Http\Controllers\Dashboard\ReservationController::class, 'invoicePdf'])
            ->name('reservations.invoice.pdf');

        // Clients (invités) - code tablette
        Route::get('guests/search', [\App\Http\Controllers\Dashboard\GuestController::class, 'search'])->name('guests.search');
        Route::resource('guests', \App\Http\Controllers\Dashboard\GuestController::class);
        Route::post('guests/{guest}/regenerate-code', [\App\Http\Controllers\Dashboard\GuestController::class, 'regenerateCode'])
            ->name('guests.regenerate-code');

        // Menus
        Route::resource('menu-categories', \App\Http\Controllers\Dashboard\MenuCategoryController::class);
        Route::resource('menu-items', \App\Http\Controllers\Dashboard\MenuItemController::class);

        // Restaurants & Bars
        Route::post('restaurants/{restaurant}/toggle', [\App\Http\Controllers\Dashboard\RestaurantController::class, 'toggleActive'])->name('restaurants.toggle');
        Route::resource('restaurants', \App\Http\Controllers\Dashboard\RestaurantController::class);

        // Services Spa
        Route::resource('spa-services', \App\Http\Controllers\Dashboard\SpaServiceController::class);
        Route::post('spa-services/{spa_service}/toggle', [\App\Http\Controllers\Dashboard\SpaServiceController::class, 'toggleActive'])->name('spa-services.toggle');

        // Horaires salle de sport (établissement courant)
        Route::get('gym-hours', [\App\Http\Controllers\Dashboard\GymHoursController::class, 'index'])->name('gym-hours.index');
        Route::put('gym-hours', [\App\Http\Controllers\Dashboard\GymHoursController::class, 'update'])->name('gym-hours.update');

        // Hotel Infos & Sécurité (livret d'accueil, assistance urgence, chatbot)
        Route::get('hotel-infos-security', [\App\Http\Controllers\Dashboard\HotelInfosSecurityController::class, 'index'])->name('hotel-infos-security.index');
        Route::put('hotel-infos-security', [\App\Http\Controllers\Dashboard\HotelInfosSecurityController::class, 'update'])->name('hotel-infos-security.update');

        // Galerie (image d'établissement + albums)
        Route::get('gallery', [\App\Http\Controllers\Dashboard\GalleryController::class, 'index'])->name('gallery.index');
        Route::put('gallery/cover-photo', [\App\Http\Controllers\Dashboard\GalleryController::class, 'updateCoverPhoto'])->name('gallery.cover-photo.update');
        Route::resource('gallery-albums', \App\Http\Controllers\Dashboard\GalleryAlbumController::class)->names('gallery-albums')->except(['index']);
        Route::get('gallery-albums', fn() => redirect()->route('dashboard.gallery.index'));
        Route::get('gallery-albums/{gallery_album}/photos', [\App\Http\Controllers\Dashboard\GalleryPhotoController::class, 'index'])->name('gallery-albums.photos.index');
        Route::post('gallery-albums/{gallery_album}/photos', [\App\Http\Controllers\Dashboard\GalleryPhotoController::class, 'store'])->name('gallery-albums.photos.store');
        Route::put('gallery-albums/{gallery_album}/photos/{photo}', [\App\Http\Controllers\Dashboard\GalleryPhotoController::class, 'update'])->name('gallery-albums.photos.update');
        Route::delete('gallery-albums/{gallery_album}/photos/{photo}', [\App\Http\Controllers\Dashboard\GalleryPhotoController::class, 'destroy'])->name('gallery-albums.photos.destroy');

        // Nos établissements (autres sites du groupe)
        Route::resource('establishments', \App\Http\Controllers\Dashboard\EstablishmentController::class);
        Route::get('establishments/{establishment}/photos', [\App\Http\Controllers\Dashboard\EstablishmentPhotoController::class, 'index'])->name('establishments.photos.index');
        Route::post('establishments/{establishment}/photos', [\App\Http\Controllers\Dashboard\EstablishmentPhotoController::class, 'store'])->name('establishments.photos.store');
        Route::put('establishments/{establishment}/photos/{photo}', [\App\Http\Controllers\Dashboard\EstablishmentPhotoController::class, 'update'])->name('establishments.photos.update');
        Route::delete('establishments/{establishment}/photos/{photo}', [\App\Http\Controllers\Dashboard\EstablishmentPhotoController::class, 'destroy'])->name('establishments.photos.destroy');

        // Chat invité (messages depuis les tablettes)
        Route::get('hotel-chat', [\App\Http\Controllers\Dashboard\ChatController::class, 'index'])->name('hotel-chat.index');
        Route::get('hotel-chat/{conversation}', [\App\Http\Controllers\Dashboard\ChatController::class, 'show'])->name('hotel-chat.show');
        Route::post('hotel-chat/{conversation}/reply', [\App\Http\Controllers\Dashboard\ChatController::class, 'reply'])->name('hotel-chat.reply');

        // Bien-être, Sport & Loisirs (catégories principales Sport/Loisirs + sous-catégories dynamiques)
        Route::resource('leisure-categories', \App\Http\Controllers\Dashboard\LeisureCategoryController::class);
        Route::post('leisure-categories/{leisure_category}/toggle', [\App\Http\Controllers\Dashboard\LeisureCategoryController::class, 'toggleActive'])->name('leisure-categories.toggle');
        Route::get('leisure-categories/{leisure_category}/subcategories', [\App\Http\Controllers\Dashboard\LeisureSubcategoryController::class, 'index'])->name('leisure-categories.subcategories.index');
        Route::get('leisure-categories/{leisure_category}/subcategories/create', [\App\Http\Controllers\Dashboard\LeisureSubcategoryController::class, 'create'])->name('leisure-categories.subcategories.create');
        Route::post('leisure-categories/{leisure_category}/subcategories', [\App\Http\Controllers\Dashboard\LeisureSubcategoryController::class, 'store'])->name('leisure-categories.subcategories.store');
        Route::get('leisure-categories/{leisure_category}/subcategories/{subcategory}/edit', [\App\Http\Controllers\Dashboard\LeisureSubcategoryController::class, 'edit'])->name('leisure-categories.subcategories.edit');
        Route::put('leisure-categories/{leisure_category}/subcategories/{subcategory}', [\App\Http\Controllers\Dashboard\LeisureSubcategoryController::class, 'update'])->name('leisure-categories.subcategories.update');
        Route::delete('leisure-categories/{leisure_category}/subcategories/{subcategory}', [\App\Http\Controllers\Dashboard\LeisureSubcategoryController::class, 'destroy'])->name('leisure-categories.subcategories.destroy');
        Route::post('leisure-categories/{leisure_category}/subcategories/{subcategory}/toggle', [\App\Http\Controllers\Dashboard\LeisureSubcategoryController::class, 'toggleActive'])->name('leisure-categories.subcategories.toggle');

        // Blanchisserie
        Route::resource('laundry-services', \App\Http\Controllers\Dashboard\LaundryServiceController::class);
        Route::post('laundry-services/{laundry_service}/toggle', [\App\Http\Controllers\Dashboard\LaundryServiceController::class, 'toggleActive'])->name('laundry-services.toggle');

        // Services Palace
        Route::resource('palace-services', \App\Http\Controllers\Dashboard\PalaceServiceController::class);
        Route::post('palace-services/{palace_service}/toggle', [\App\Http\Controllers\Dashboard\PalaceServiceController::class, 'toggleActive'])->name('palace-services.toggle');

        // Amenities & Conciergerie (catégories + articles dynamiques pour l'app mobile)
        Route::resource('amenity-categories', \App\Http\Controllers\Dashboard\AmenityCategoryController::class);
        Route::post('amenity-categories/{amenity_category}/toggle', [\App\Http\Controllers\Dashboard\AmenityCategoryController::class, 'toggleActive'])->name('amenity-categories.toggle');
        Route::get('amenity-categories/{amenity_category}/items', [\App\Http\Controllers\Dashboard\AmenityItemController::class, 'index'])->name('amenity-categories.items.index');
        Route::get('amenity-categories/{amenity_category}/items/create', [\App\Http\Controllers\Dashboard\AmenityItemController::class, 'create'])->name('amenity-categories.items.create');
        Route::post('amenity-categories/{amenity_category}/items', [\App\Http\Controllers\Dashboard\AmenityItemController::class, 'store'])->name('amenity-categories.items.store');
        Route::get('amenity-categories/{amenity_category}/items/{item}/edit', [\App\Http\Controllers\Dashboard\AmenityItemController::class, 'edit'])->name('amenity-categories.items.edit');
        Route::put('amenity-categories/{amenity_category}/items/{item}', [\App\Http\Controllers\Dashboard\AmenityItemController::class, 'update'])->name('amenity-categories.items.update');
        Route::delete('amenity-categories/{amenity_category}/items/{item}', [\App\Http\Controllers\Dashboard\AmenityItemController::class, 'destroy'])->name('amenity-categories.items.destroy');
        Route::post('amenity-categories/{amenity_category}/items/{item}/toggle', [\App\Http\Controllers\Dashboard\AmenityItemController::class, 'toggleActive'])->name('amenity-categories.items.toggle');

        // Excursions
        Route::resource('excursions', \App\Http\Controllers\Dashboard\ExcursionController::class);
        Route::post('excursions/{excursion}/toggle', [\App\Http\Controllers\Dashboard\ExcursionController::class, 'toggleActive'])->name('excursions.toggle');

        // Réservations & demandes (spa, excursions, restaurants, blanchisserie, palace)
        Route::get('spa-reservations', [\App\Http\Controllers\Dashboard\SpaReservationsController::class, 'index'])->name('spa-reservations.index');
        Route::get('spa-reservations/{spaReservation}', [\App\Http\Controllers\Dashboard\SpaReservationsController::class, 'show'])->name('spa-reservations.show');
        Route::get('spa-reservations/{spaReservation}/edit', [\App\Http\Controllers\Dashboard\SpaReservationsController::class, 'edit'])->name('spa-reservations.edit');
        Route::put('spa-reservations/{spaReservation}', [\App\Http\Controllers\Dashboard\SpaReservationsController::class, 'update'])->name('spa-reservations.update');
        Route::post('spa-reservations/{spaReservation}/cancel', [\App\Http\Controllers\Dashboard\SpaReservationsController::class, 'cancel'])->name('spa-reservations.cancel');

        Route::get('excursion-bookings', [\App\Http\Controllers\Dashboard\ExcursionBookingsController::class, 'index'])->name('excursion-bookings.index');
        Route::get('excursion-bookings/{excursionBooking}', [\App\Http\Controllers\Dashboard\ExcursionBookingsController::class, 'show'])->name('excursion-bookings.show');
        Route::get('excursion-bookings/{excursionBooking}/edit', [\App\Http\Controllers\Dashboard\ExcursionBookingsController::class, 'edit'])->name('excursion-bookings.edit');
        Route::put('excursion-bookings/{excursionBooking}', [\App\Http\Controllers\Dashboard\ExcursionBookingsController::class, 'update'])->name('excursion-bookings.update');
        Route::post('excursion-bookings/{excursionBooking}/cancel', [\App\Http\Controllers\Dashboard\ExcursionBookingsController::class, 'cancel'])->name('excursion-bookings.cancel');

        Route::get('restaurant-reservations', [\App\Http\Controllers\Dashboard\RestaurantReservationsController::class, 'index'])->name('restaurant-reservations.index');
        Route::get('restaurant-reservations/{restaurantReservation}', [\App\Http\Controllers\Dashboard\RestaurantReservationsController::class, 'show'])->name('restaurant-reservations.show');
        Route::get('restaurant-reservations/{restaurantReservation}/edit', [\App\Http\Controllers\Dashboard\RestaurantReservationsController::class, 'edit'])->name('restaurant-reservations.edit');
        Route::put('restaurant-reservations/{restaurantReservation}', [\App\Http\Controllers\Dashboard\RestaurantReservationsController::class, 'update'])->name('restaurant-reservations.update');
        Route::post('restaurant-reservations/{restaurantReservation}/cancel', [\App\Http\Controllers\Dashboard\RestaurantReservationsController::class, 'cancel'])->name('restaurant-reservations.cancel');

        Route::get('laundry-requests', [\App\Http\Controllers\Dashboard\LaundryRequestsController::class, 'index'])->name('laundry-requests.index');
        Route::get('laundry-requests/{laundryRequest}', [\App\Http\Controllers\Dashboard\LaundryRequestsController::class, 'show'])->name('laundry-requests.show');
        Route::get('laundry-requests/{laundryRequest}/edit', [\App\Http\Controllers\Dashboard\LaundryRequestsController::class, 'edit'])->name('laundry-requests.edit');
        Route::put('laundry-requests/{laundryRequest}', [\App\Http\Controllers\Dashboard\LaundryRequestsController::class, 'update'])->name('laundry-requests.update');
        Route::post('laundry-requests/{laundryRequest}/cancel', [\App\Http\Controllers\Dashboard\LaundryRequestsController::class, 'cancel'])->name('laundry-requests.cancel');

        Route::get('palace-requests', [\App\Http\Controllers\Dashboard\PalaceRequestsController::class, 'index'])->name('palace-requests.index');
        Route::get('palace-requests/{palaceRequest}', [\App\Http\Controllers\Dashboard\PalaceRequestsController::class, 'show'])->name('palace-requests.show');
        Route::get('palace-requests/{palaceRequest}/edit', [\App\Http\Controllers\Dashboard\PalaceRequestsController::class, 'edit'])->name('palace-requests.edit');
        Route::put('palace-requests/{palaceRequest}', [\App\Http\Controllers\Dashboard\PalaceRequestsController::class, 'update'])->name('palace-requests.update');
        Route::post('palace-requests/{palaceRequest}/cancel', [\App\Http\Controllers\Dashboard\PalaceRequestsController::class, 'cancel'])->name('palace-requests.cancel');

        // Véhicules (location avec chauffeur)
        Route::resource('vehicles', \App\Http\Controllers\Dashboard\VehicleController::class);
        Route::post('vehicles/{vehicle}/toggle', [\App\Http\Controllers\Dashboard\VehicleController::class, 'toggleActive'])->name('vehicles.toggle');

        // Staff (personnel de l'hôtel)
        Route::get('staff', [\App\Http\Controllers\Dashboard\StaffController::class, 'index'])->name('staff.index');
        Route::get('staff/create', [\App\Http\Controllers\Dashboard\StaffController::class, 'create'])->name('staff.create');
        Route::post('staff', [\App\Http\Controllers\Dashboard\StaffController::class, 'store'])->name('staff.store');
        Route::get('staff/{id}/edit', [\App\Http\Controllers\Dashboard\StaffController::class, 'edit'])->name('staff.edit')->whereNumber('id');
        Route::put('staff/{id}', [\App\Http\Controllers\Dashboard\StaffController::class, 'update'])->name('staff.update')->whereNumber('id');
        Route::delete('staff/{id}', [\App\Http\Controllers\Dashboard\StaffController::class, 'destroy'])->name('staff.destroy')->whereNumber('id');

        // Accès tablettes : comptes "Client Chambre XXX" (User role=guest) — gérant de l'hôtel
        Route::get('tablet-accesses', [\App\Http\Controllers\Dashboard\TabletAccessController::class, 'index'])->name('tablet-accesses.index');
        Route::get('tablet-accesses/create', [\App\Http\Controllers\Dashboard\TabletAccessController::class, 'create'])->name('tablet-accesses.create');
        Route::post('tablet-accesses', [\App\Http\Controllers\Dashboard\TabletAccessController::class, 'store'])->name('tablet-accesses.store');
        Route::get('tablet-accesses/{id}/edit', [\App\Http\Controllers\Dashboard\TabletAccessController::class, 'edit'])->name('tablet-accesses.edit');
        Route::put('tablet-accesses/{id}', [\App\Http\Controllers\Dashboard\TabletAccessController::class, 'update'])->name('tablet-accesses.update');
        Route::delete('tablet-accesses/{id}', [\App\Http\Controllers\Dashboard\TabletAccessController::class, 'destroy'])->name('tablet-accesses.destroy');
        Route::post('tablet-accesses/{id}/regenerate-client-code', [\App\Http\Controllers\Dashboard\TabletAccessController::class, 'regenerateClientCode'])->name('tablet-accesses.regenerate-client-code');

        // QR Code Client Web App
        Route::get('qrcode-client', [\App\Http\Controllers\Dashboard\QrCodeClientController::class, 'index'])->name('qrcode-client.index');
        Route::get('qrcode-client/pdf', [\App\Http\Controllers\Dashboard\QrCodeClientController::class, 'pdf'])->name('qrcode-client.pdf');

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

        // Facturation / Notes de chambre (type Opera, Oracle)
        Route::get('billing', [\App\Http\Controllers\Dashboard\BillingController::class, 'index'])->name('billing.index');

        // Rapports & Audits
        Route::get('reports', [\App\Http\Controllers\Dashboard\ReportsController::class, 'index'])->name('reports.index');
        Route::get('reports/{type}', [\App\Http\Controllers\Dashboard\ReportsController::class, 'show'])->name('reports.show')->where('type', 'global|overview|reservations|orders|billing|services|audit');

        Route::get('guest-reviews', [\App\Http\Controllers\Dashboard\GuestReviewsController::class, 'index'])->name('guest-reviews.index');

        // Gestion des stocks (catégories, produits, mouvements, alertes)
        Route::get('stock', [\App\Http\Controllers\Dashboard\StockController::class, 'index'])->name('stock.index');
        Route::resource('stock-categories', \App\Http\Controllers\Dashboard\StockCategoryController::class)->names('stock-categories');
        Route::resource('stock-products', \App\Http\Controllers\Dashboard\StockProductController::class)->names('stock-products');
        Route::get('stock-movements', [\App\Http\Controllers\Dashboard\StockMovementController::class, 'index'])->name('stock-movements.index');
        Route::get('stock-movements/create', [\App\Http\Controllers\Dashboard\StockMovementController::class, 'create'])->name('stock-movements.create');
        Route::post('stock-movements', [\App\Http\Controllers\Dashboard\StockMovementController::class, 'store'])->name('stock-movements.store');

        // ==========================================
        // ANNONCES & PUBLICITÉS — Entreprise
        // ==========================================
        Route::resource('enterprise-announcements', \App\Http\Controllers\Dashboard\EnterpriseAnnouncementController::class)
            ->parameters(['enterprise-announcements' => 'enterpriseAnnouncement']);
        Route::post('enterprise-announcements/{enterpriseAnnouncement}/toggle', [\App\Http\Controllers\Dashboard\EnterpriseAnnouncementController::class, 'toggleActive'])->name('enterprise-announcements.toggle');
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
    if (Auth::check()) {
        $user = Auth::user();
        if ($user instanceof \App\Models\User && $user->isSuperAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        if ($user instanceof \App\Models\User && ($user->isAdmin() || $user->isStaff())) {
            return redirect()->route('dashboard.index');
        }
        return redirect()->route('guest.dashboard');
    }
    return redirect()->route('login');
});

// App Web Client (Flutter) — index.html pour /client et /client/*
Route::get('/client', function () {
    return file_get_contents(public_path('client/index.html'));
});
Route::get('/client/{any}', function () {
    return file_get_contents(public_path('client/index.html'));
})->where('any', '.*');

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
