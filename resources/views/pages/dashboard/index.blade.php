@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">Dashboard</h1>
    <p class="text-gray-600 dark:text-gray-400">Vue d'ensemble de votre hôtel</p>
</div>

<div class="grid grid-cols-12 gap-4 md:gap-6">
    <!-- Statistiques chambres -->
    <div class="col-span-12 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Total Chambres -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Chambres</p>
                    <p class="text-title-md mt-2 font-semibold text-gray-800 dark:text-white/90">{{ number_format($totalRooms) }}</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-50 dark:bg-brand-500/10">
                    <svg class="h-6 w-6 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9v10a2 2 0 002 2h14a2 2 0 002-2V9M3 9l9-7 9 7"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2">
                <a href="{{ route('dashboard.rooms.index') }}" class="text-xs text-brand-500 hover:text-brand-600">Gérer les chambres →</a>
            </div>
        </div>

        <!-- Chambres Disponibles -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Disponibles</p>
                    <p class="text-title-md mt-2 font-semibold text-gray-800 dark:text-white/90">{{ $availableRooms }}/{{ $totalRooms }}</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-success-50 dark:bg-success-500/10">
                    <svg class="h-6 w-6 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2">
                <span class="text-xs text-gray-500 dark:text-gray-400">Occupées: {{ $occupiedRooms }}</span>
            </div>
        </div>

        <!-- Check-ins du jour -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Check-ins aujourd'hui</p>
                    <p class="text-title-md mt-2 font-semibold text-gray-800 dark:text-white/90">{{ $checkInsToday }}</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-light-50 dark:bg-blue-light-500/10">
                    <svg class="h-6 w-6 text-blue-light-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2">
                <span class="text-xs text-gray-500 dark:text-gray-400">Check-outs: {{ $checkOutsToday }}</span>
            </div>
        </div>

        <!-- Réservations -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Réservations</p>
                    <p class="text-title-md mt-2 font-semibold text-gray-800 dark:text-white/90">{{ number_format($totalReservations) }}</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-warning-50 dark:bg-warning-500/10">
                    <svg class="h-6 w-6 text-warning-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
            <div class="mt-4 flex items-center gap-2">
                <span class="text-xs text-gray-500 dark:text-gray-400">En attente: {{ $pendingReservations }}</span>
            </div>
        </div>
    </div>

    <!-- Services : Commandes, Restaurant, Spa, Blanchisserie, Palace, Excursions -->
    <div class="col-span-12 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Commandes</p>
                    <p class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ $ordersTotal ?? 0 }}</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-50 dark:bg-brand-500/10">
                    <svg class="h-5 w-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                </div>
            </div>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Aujourd'hui: {{ $ordersToday ?? 0 }} · En cours: {{ $ordersPending ?? 0 }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Restaurant</p>
                    <p class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ $restaurantReservationsTotal ?? 0 }}</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-warning-50 dark:bg-warning-500/10">
                    <svg class="h-5 w-5 text-warning-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                </div>
            </div>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Aujourd'hui: {{ $restaurantReservationsToday ?? 0 }} · À venir: {{ $restaurantReservationsPending ?? 0 }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Spa</p>
                    <p class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ $spaReservationsTotal ?? 0 }}</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-success-50 dark:bg-success-500/10">
                    <svg class="h-5 w-5 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                </div>
            </div>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Aujourd'hui: {{ $spaReservationsToday ?? 0 }} · À venir: {{ $spaReservationsPending ?? 0 }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Blanchisserie</p>
                    <p class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ $laundryRequestsTotal ?? 0 }}</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-light-50 dark:bg-blue-light-500/10">
                    <svg class="h-5 w-5 text-blue-light-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                </div>
            </div>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">En cours: {{ $laundryRequestsPending ?? 0 }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Palace / Conciergerie</p>
                    <p class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ $palaceRequestsTotal ?? 0 }}</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-50 dark:bg-brand-500/10">
                    <svg class="h-5 w-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path></svg>
                </div>
            </div>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">En cours: {{ $palaceRequestsPending ?? 0 }}</p>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Excursions</p>
                    <p class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ $excursionBookingsTotal ?? 0 }}</p>
                </div>
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-success-50 dark:bg-success-500/10">
                    <svg class="h-5 w-5 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2 1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5"></path></svg>
                </div>
            </div>
            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">À venir: {{ $excursionBookingsPending ?? 0 }}</p>
        </div>
    </div>

    <!-- Graphiques : Commandes dans le temps + CA + Articles les plus commandés -->
    <div class="col-span-12 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Commandes sur 14 jours</h3>
            <div id="dashboardOrdersChart" class="min-h-[280px]"></div>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Chiffre d'affaires commandes (FCFA) — 14 jours</h3>
            <div id="dashboardRevenueChart" class="min-h-[280px]"></div>
        </div>
    </div>
    <div class="col-span-12 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Articles les plus commandés</h3>
            <div id="dashboardTopItemsChart" class="min-h-[300px]"></div>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Commandes par statut</h3>
            <div id="dashboardOrdersByStatusChart" class="min-h-[280px]"></div>
        </div>
    </div>

    <!-- Statistiques détaillées -->
    <div class="col-span-12 grid grid-cols-1 gap-4 lg:grid-cols-3">
        <!-- Statut des Chambres -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Statut des Chambres</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Disponibles</span>
                    <span class="text-sm font-semibold text-success-600 dark:text-success-400">{{ $availableRooms }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Occupées</span>
                    <span class="text-sm font-semibold text-blue-light-600 dark:text-blue-light-400">{{ $occupiedRooms }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Maintenance</span>
                    <span class="text-sm font-semibold text-warning-600 dark:text-warning-400">{{ $maintenanceRooms }}</span>
                </div>
            </div>
        </div>

        <!-- Réservations par statut -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Réservations</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Confirmées</span>
                    <span class="text-sm font-semibold text-success-600 dark:text-success-400">{{ $confirmedReservations }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">En attente</span>
                    <span class="text-sm font-semibold text-warning-600 dark:text-warning-400">{{ $pendingReservations }}</span>
                </div>
            </div>
        </div>

        <!-- Check-ins/outs -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Aujourd'hui</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Check-ins</span>
                    <span class="text-sm font-semibold text-blue-light-600 dark:text-blue-light-400">{{ $checkInsToday }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Check-outs</span>
                    <span class="text-sm font-semibold text-gray-600 dark:text-gray-400">{{ $checkOutsToday }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Réservations récentes -->
    <div class="col-span-12">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Réservations Récentes</h3>
            </div>
            
            @if($recentReservations->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-800">
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-400">Référence</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-400">Client</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-400">Chambre</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-400">Check-in</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-400">Check-out</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-400">Prix</th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-400">Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentReservations as $reservation)
                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-800 dark:text-white/90">{{ $reservation->reservation_number }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $reservation->user?->name ?? $reservation->guest?->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $reservation->room->room_number }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $reservation->check_in->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $reservation->check_out->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-800 dark:text-white/90">{{ number_format($reservation->total_price, 0, ',', ' ') }} FCFA</td>
                                    <td class="px-4 py-3">
                                        @php
                                            $statusColors = [
                                                'pending' => 'bg-warning-50 text-warning-600 dark:bg-warning-500/10 dark:text-warning-400',
                                                'confirmed' => 'bg-success-50 text-success-600 dark:bg-success-500/10 dark:text-success-400',
                                                'checked_in' => 'bg-blue-light-50 text-blue-light-600 dark:bg-blue-light-500/10 dark:text-blue-light-400',
                                                'checked_out' => 'bg-gray-50 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
                                                'cancelled' => 'bg-error-50 text-error-600 dark:bg-error-500/10 dark:text-error-400',
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusColors[$reservation->status] ?? 'bg-gray-50 text-gray-600' }}">
                                            {{ $reservation->status_name }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-8">
                    <p class="text-gray-500 dark:text-gray-400">Aucune réservation pour le moment</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Actions rapides -->
    <div class="col-span-12">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90 mb-4">Actions rapides</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('dashboard.rooms.create') }}" class="flex items-center gap-3 p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-50 dark:bg-brand-500/10">
                        <svg class="h-6 w-6 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">Nouvelle chambre</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Ajouter une chambre</p>
                    </div>
                </a>

                <a href="{{ route('dashboard.rooms.index') }}" class="flex items-center gap-3 p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-success-50 dark:bg-success-500/10">
                        <svg class="h-6 w-6 text-success-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9v10a2 2 0 002 2h14a2 2 0 002-2V9M3 9l9-7 9 7"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">Voir chambres</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Gérer les chambres</p>
                    </div>
                </a>

                <a href="{{ route('dashboard.reservations.create') }}" class="flex items-center gap-3 p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-light-50 dark:bg-blue-light-500/10">
                        <svg class="h-6 w-6 text-blue-light-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">Nouvelle réservation</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Créer une réservation</p>
                    </div>
                </a>

                <a href="{{ route('dashboard.orders.index') }}" class="flex items-center gap-3 p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-warning-50 dark:bg-warning-500/10">
                        <svg class="h-6 w-6 text-warning-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">Commandes</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Room Service</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ordersLabels = @json($ordersChartLabels ?? []);
    const ordersData = @json($ordersChartData ?? []);
    const revenueLabels = @json($revenueChartLabels ?? []);
    const revenueData = @json($revenueChartData ?? []);
    const topItems = @json($topOrderedItems ?? []);
    const statusLabels = @json($ordersByStatusLabels ?? []);
    const statusData = @json($ordersByStatusData ?? []);

    if (document.querySelector('#dashboardOrdersChart') && ordersLabels.length) {
        new ApexCharts(document.querySelector('#dashboardOrdersChart'), {
            chart: { type: 'area', height: 280, toolbar: { show: false }, fontFamily: 'inherit' },
            series: [{ name: 'Commandes', data: ordersData }],
            colors: ['#465FFF'],
            fill: { type: 'gradient', gradient: { opacityFrom: 0.5, opacityTo: 0.05 } },
            stroke: { curve: 'smooth', width: 2 },
            xaxis: { categories: ordersLabels, labels: { style: { colors: '#64748b' } } },
            yaxis: { labels: { style: { colors: '#64748b' } } },
            grid: { borderColor: '#e2e8f0', strokeDashArray: 4 },
            dataLabels: { enabled: false },
            tooltip: { theme: 'light' }
        }).render();
    }
    if (document.querySelector('#dashboardRevenueChart') && revenueLabels.length) {
        new ApexCharts(document.querySelector('#dashboardRevenueChart'), {
            chart: { type: 'line', height: 280, toolbar: { show: false }, fontFamily: 'inherit' },
            series: [{ name: 'CA (FCFA)', data: revenueData }],
            colors: ['#10b981'],
            stroke: { curve: 'smooth', width: 2 },
            xaxis: { categories: revenueLabels, labels: { style: { colors: '#64748b' } } },
            yaxis: { labels: { style: { colors: '#64748b' }, formatter: function(v) { return (v/1000).toFixed(0) + 'k'; } } },
            grid: { borderColor: '#e2e8f0', strokeDashArray: 4 },
            dataLabels: { enabled: false },
            tooltip: { theme: 'light', y: { formatter: function(v) { return v.toLocaleString('fr-FR') + ' FCFA'; } } }
        }).render();
    }
    if (document.querySelector('#dashboardTopItemsChart') && topItems.length) {
        new ApexCharts(document.querySelector('#dashboardTopItemsChart'), {
            chart: { type: 'bar', height: 300, toolbar: { show: false }, fontFamily: 'inherit' },
            plotOptions: { bar: { horizontal: true, barHeight: '70%', borderRadius: 4 } },
            series: [{ name: 'Quantité', data: topItems.map(i => i.total_quantity) }],
            colors: ['#8b5cf6'],
            xaxis: { categories: topItems.map(i => i.item_name || '—'), labels: { style: { colors: '#64748b' }, maxWidth: 180 } },
            yaxis: { labels: { style: { colors: '#64748b' } } },
            grid: { borderColor: '#e2e8f0', strokeDashArray: 4 },
            dataLabels: { enabled: true },
            tooltip: { theme: 'light' }
        }).render();
    }
    if (document.querySelector('#dashboardOrdersByStatusChart') && statusLabels.length) {
        new ApexCharts(document.querySelector('#dashboardOrdersByStatusChart'), {
            chart: { type: 'donut', height: 280, fontFamily: 'inherit' },
            series: statusData,
            labels: statusLabels,
            colors: ['#f59e0b', '#10b981', '#3b82f6', '#8b5cf6', '#6366f1', '#14b8a6', '#ef4444'],
            legend: { position: 'bottom', horizontalAlign: 'center' },
            dataLabels: { enabled: true },
            plotOptions: { pie: { donut: { size: '65%' } } },
            tooltip: { theme: 'light' }
        }).render();
    }
});
</script>
@endpush
@endsection
