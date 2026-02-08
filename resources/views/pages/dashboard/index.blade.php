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

                <div class="flex items-center gap-3 p-4 border border-gray-200 dark:border-gray-700 rounded-lg opacity-50">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-light-50 dark:bg-blue-light-500/10">
                        <svg class="h-6 w-6 text-blue-light-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">Nouvelle réservation</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Bientôt disponible</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 p-4 border border-gray-200 dark:border-gray-700 rounded-lg opacity-50">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-warning-50 dark:bg-warning-500/10">
                        <svg class="h-6 w-6 text-warning-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-800 dark:text-white/90">Room Service</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Bientôt disponible</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
