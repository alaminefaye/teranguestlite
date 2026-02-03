@extends('layouts.guest')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white/90">Nos Services</h1>
    <p class="text-gray-600 dark:text-gray-400 mt-1">Découvrez tous les services disponibles durant votre séjour</p>
</div>

<!-- Grille des services -->
<div class="grid grid-cols-2 gap-4">
    <!-- Restaurants & Bars -->
    <a href="{{ route('guest.restaurants.index') }}" class="tablet-card rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm hover:border-brand-500 dark:hover:border-brand-500 hover:shadow-md transition-all">
        <div class="flex flex-col items-center text-center gap-3">
            <div class="w-16 h-16 rounded-full bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center">
                <svg class="w-8 h-8 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900 dark:text-white/90">Restaurants & Bars</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Réservez une table</p>
            </div>
        </div>
    </a>

    <!-- Services Spa -->
    <a href="{{ route('guest.spa.index') }}" class="tablet-card rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm hover:border-brand-500 dark:hover:border-brand-500 hover:shadow-md transition-all">
        <div class="flex flex-col items-center text-center gap-3">
            <div class="w-16 h-16 rounded-full bg-success-100 dark:bg-success-900/30 flex items-center justify-center">
                <svg class="w-8 h-8 text-success-600 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900 dark:text-white/90">Spa & Bien-être</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Réservez un soin</p>
            </div>
        </div>
    </a>

    <!-- Excursions -->
    <a href="{{ route('guest.excursions.index') }}" class="tablet-card rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm hover:border-brand-500 dark:hover:border-brand-500 hover:shadow-md transition-all">
        <div class="flex flex-col items-center text-center gap-3">
            <div class="w-16 h-16 rounded-full bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center">
                <svg class="w-8 h-8 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900 dark:text-white/90">Excursions</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Découvrez le Sénégal</p>
            </div>
        </div>
    </a>

    <!-- Blanchisserie -->
    <a href="{{ route('guest.laundry.index') }}" class="tablet-card rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm hover:border-brand-500 dark:hover:border-brand-500 hover:shadow-md transition-all">
        <div class="flex flex-col items-center text-center gap-3">
            <div class="w-16 h-16 rounded-full bg-info-100 dark:bg-info-900/30 flex items-center justify-center">
                <svg class="w-8 h-8 text-info-600 dark:text-info-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900 dark:text-white/90">Blanchisserie</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Service de nettoyage</p>
            </div>
        </div>
    </a>

    <!-- Services Palace -->
    <a href="{{ route('guest.palace.index') }}" class="tablet-card rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm hover:border-brand-500 dark:hover:border-brand-500 hover:shadow-md transition-all col-span-2">
        <div class="flex flex-col items-center text-center gap-3">
            <div class="w-16 h-16 rounded-full bg-warning-100 dark:bg-warning-900/30 flex items-center justify-center">
                <svg class="w-8 h-8 text-warning-600 dark:text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                </svg>
            </div>
            <div>
                <h3 class="font-semibold text-gray-900 dark:text-white/90">Services Palace</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Conciergerie & Services Premium</p>
            </div>
        </div>
    </a>
</div>

<!-- Mes réservations -->
<div class="mt-8">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90 mb-4">Accès rapide</h2>
    <div class="grid grid-cols-2 gap-3">
        <a href="{{ route('guest.restaurants.my-reservations') }}" class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 text-center hover:bg-gray-50 dark:hover:bg-gray-700">
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Mes Réservations Restaurants</p>
        </a>
        <a href="{{ route('guest.spa.my-reservations') }}" class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 text-center hover:bg-gray-50 dark:hover:bg-gray-700">
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Mes Réservations Spa</p>
        </a>
        <a href="{{ route('guest.excursions.my-bookings') }}" class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 text-center hover:bg-gray-50 dark:hover:bg-gray-700">
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Mes Excursions</p>
        </a>
        <a href="{{ route('guest.laundry.my-requests') }}" class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 text-center hover:bg-gray-50 dark:hover:bg-gray-700">
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Mes Demandes Blanchisserie</p>
        </a>
    </div>
</div>
@endsection
