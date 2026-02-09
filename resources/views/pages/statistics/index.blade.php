@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-title-md font-semibold text-gray-800 dark:text-white/90">Statistiques Avancées</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Analysez les performances de votre activité</p>
        </div>
        <div class="flex items-center gap-3">
            <form method="GET" action="{{ route('statistics.index') }}" class="flex items-center gap-2">
                <select name="period" 
                        onchange="this.form.submit()"
                        class="h-10 rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 focus:border-brand-300 focus:ring-3 focus:ring-brand-500/10 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    <option value="7" {{ $period == '7' ? 'selected' : '' }}>7 derniers jours</option>
                    <option value="30" {{ $period == '30' ? 'selected' : '' }}>30 derniers jours</option>
                    <option value="90" {{ $period == '90' ? 'selected' : '' }}>3 derniers mois</option>
                    <option value="365" {{ $period == '365' ? 'selected' : '' }}>12 derniers mois</option>
                </select>
            </form>
            <a href="{{ route('statistics.index', array_merge(request()->query(), ['export' => 'pdf'])) }}" 
               class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
                Exporter PDF
            </a>
        </div>
    </div>

    <!-- Statistiques globales -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Revenus Total</p>
                    <p class="text-title-md mt-2 font-semibold text-gray-800 dark:text-white/90">
                        {{ number_format($totalRevenue, 0, ',', ' ') }} FCFA
                    </p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        {{ $paidBookings }} réservations payées
                    </p>
                </div>
                @if(isset($revenueVariation))
                    <div class="text-right">
                        @if($revenueVariation > 0)
                            <span class="inline-flex items-center rounded-full bg-success-50 px-2 py-1 text-xs font-medium text-success-600">
                                <svg class="mr-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                                +{{ number_format(abs($revenueVariation), 1) }}%
                            </span>
                        @elseif($revenueVariation < 0)
                            <span class="inline-flex items-center rounded-full bg-error-50 px-2 py-1 text-xs font-medium text-error-600">
                                <svg class="mr-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                                </svg>
                                {{ number_format($revenueVariation, 1) }}%
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600">
                                =
                            </span>
                        @endif
                    </div>
                @endif
            </div>
            @if(isset($previousRevenue))
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    Période précédente: {{ number_format($previousRevenue, 0, ',', ' ') }} FCFA
                </p>
            @endif
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Réservations</p>
                    <p class="text-title-md mt-2 font-semibold text-gray-800 dark:text-white/90">
                        {{ number_format($totalBookings) }}
                    </p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Période sélectionnée
                    </p>
                </div>
                @if(isset($bookingsVariation))
                    <div class="text-right">
                        @if($bookingsVariation > 0)
                            <span class="inline-flex items-center rounded-full bg-success-50 px-2 py-1 text-xs font-medium text-success-600">
                                <svg class="mr-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                                +{{ number_format(abs($bookingsVariation), 1) }}%
                            </span>
                        @elseif($bookingsVariation < 0)
                            <span class="inline-flex items-center rounded-full bg-error-50 px-2 py-1 text-xs font-medium text-error-600">
                                <svg class="mr-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                                </svg>
                                {{ number_format($bookingsVariation, 1) }}%
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600">
                                =
                            </span>
                        @endif
                    </div>
                @endif
            </div>
            @if(isset($previousBookings))
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    Période précédente: {{ number_format($previousBookings) }}
                </p>
            @endif
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 card-hover">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Prix Moyen</p>
                    <p class="text-title-md mt-2 font-semibold text-gray-800 dark:text-white/90">
                        {{ number_format($averageTicketPrice, 0, ',', ' ') }} FCFA
                    </p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Par réservation
                    </p>
                </div>
                @if(isset($averagePriceVariation))
                    <div class="text-right">
                        @if($averagePriceVariation > 0)
                            <span class="inline-flex items-center rounded-full bg-success-50 px-2 py-1 text-xs font-medium text-success-600">
                                <svg class="mr-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                                +{{ number_format(abs($averagePriceVariation), 1) }}%
                            </span>
                        @elseif($averagePriceVariation < 0)
                            <span class="inline-flex items-center rounded-full bg-error-50 px-2 py-1 text-xs font-medium text-error-600">
                                <svg class="mr-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                                </svg>
                                {{ number_format($averagePriceVariation, 1) }}%
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600">
                                =
                            </span>
                        @endif
                    </div>
                @endif
            </div>
            @if(isset($previousAverageTicketPrice))
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    Période précédente: {{ number_format($previousAverageTicketPrice, 0, ',', ' ') }} FCFA
                </p>
            @endif
        </div>

        <!-- Prévision -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 card-hover">
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Prévision 7 jours</p>
            <p class="text-title-md mt-2 font-semibold text-gray-800 dark:text-white/90">
                {{ number_format($forecastRevenue ?? 0, 0, ',', ' ') }} FCFA
            </p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                @if(isset($trend))
                    @if($trend > 5)
                        <span class="text-success-600">↗ Tendance haussière</span>
                    @elseif($trend < -5)
                        <span class="text-error-600">↘ Tendance baissière</span>
                    @else
                        <span class="text-gray-600">→ Tendance stable</span>
                    @endif
                @endif
            </p>
            @if(isset($averageDailyRevenue))
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    Moyenne quotidienne: {{ number_format($averageDailyRevenue, 0, ',', ' ') }} FCFA
                </p>
            @endif
        </div>
    </div>

    <!-- Section Comparaison -->
    @if(isset($revenueVariation) || isset($bookingsVariation))
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Comparaison avec la Période Précédente</h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Revenus</p>
                <div class="mt-2 flex items-baseline gap-2">
                    <p class="text-lg font-semibold text-gray-800 dark:text-white/90">
                        {{ number_format($totalRevenue, 0, ',', ' ') }} FCFA
                    </p>
                    @if(isset($revenueVariation))
                        @if($revenueVariation > 0)
                            <span class="text-xs text-success-600">+{{ number_format(abs($revenueVariation), 1) }}%</span>
                        @elseif($revenueVariation < 0)
                            <span class="text-xs text-error-600">{{ number_format($revenueVariation, 1) }}%</span>
                        @endif
                    @endif
                </div>
                @if(isset($previousRevenue))
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        vs {{ number_format($previousRevenue, 0, ',', ' ') }} FCFA
                    </p>
                @endif
            </div>
            <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Réservations</p>
                <div class="mt-2 flex items-baseline gap-2">
                    <p class="text-lg font-semibold text-gray-800 dark:text-white/90">
                        {{ number_format($totalBookings) }}
                    </p>
                    @if(isset($bookingsVariation))
                        @if($bookingsVariation > 0)
                            <span class="text-xs text-success-600">+{{ number_format(abs($bookingsVariation), 1) }}%</span>
                        @elseif($bookingsVariation < 0)
                            <span class="text-xs text-error-600">{{ number_format($bookingsVariation, 1) }}%</span>
                        @endif
                    @endif
                </div>
                @if(isset($previousBookings))
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        vs {{ number_format($previousBookings) }}
                    </p>
                @endif
            </div>
            <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-800">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Prix Moyen</p>
                <div class="mt-2 flex items-baseline gap-2">
                    <p class="text-lg font-semibold text-gray-800 dark:text-white/90">
                        {{ number_format($averageTicketPrice, 0, ',', ' ') }} FCFA
                    </p>
                    @if(isset($averagePriceVariation))
                        @if($averagePriceVariation > 0)
                            <span class="text-xs text-success-600">+{{ number_format(abs($averagePriceVariation), 1) }}%</span>
                        @elseif($averagePriceVariation < 0)
                            <span class="text-xs text-error-600">{{ number_format($averagePriceVariation, 1) }}%</span>
                        @endif
                    @endif
                </div>
                @if(isset($previousAverageTicketPrice))
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        vs {{ number_format($previousAverageTicketPrice, 0, ',', ' ') }} FCFA
                    </p>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Graphiques -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Revenus par jour avec comparaison -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Revenus par Jour</h3>
            @if(isset($previousRevenueByDay) && $previousRevenueByDay->count() > 0)
                <div class="mb-3 flex items-center gap-4 text-xs">
                    <div class="flex items-center gap-2">
                        <div class="h-3 w-3 rounded bg-brand-500"></div>
                        <span class="text-gray-600 dark:text-gray-400">Période actuelle</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="h-3 w-3 rounded bg-gray-400"></div>
                        <span class="text-gray-600 dark:text-gray-400">Période précédente</span>
                    </div>
                </div>
            @endif
            <div id="revenueChart" style="min-height: 300px;"></div>
        </div>

        <!-- Réservations par jour -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Réservations par Jour</h3>
            <div id="bookingsChart" style="min-height: 300px;"></div>
        </div>

        <!-- Revenus par méthode de paiement -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Revenus par Méthode de Paiement</h3>
            <div id="paymentMethodChart" style="min-height: 300px;"></div>
        </div>

        <!-- Répartition des statuts -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Répartition des Statuts</h3>
            <div id="statusChart" style="min-height: 300px;"></div>
        </div>
    </div>

    <!-- Top trajets -->
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Top 10 Trajets les Plus Réservés</h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-800">
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-400">Trajet</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-400">Réservations</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-400">Revenus</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topRoutes as $route)
                        <tr class="border-b border-gray-100 dark:border-gray-800 table-row-hover">
                            <td class="px-4 py-3 font-medium text-gray-800 dark:text-white/90">
                                {{ $route->departure }} → {{ $route->arrival }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $route->bookings_count }}</td>
                            <td class="px-4 py-3 font-medium text-gray-800 dark:text-white/90">
                                {{ number_format($route->revenue, 0, ',', ' ') }} FCFA
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                Aucune donnée disponible
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Statistiques par véhicule -->
    <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900">
        <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Top 10 Véhicules par Revenus</h3>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-800">
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-400">Véhicule</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-400">Type</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-400">Horaires</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-400">Locations</th>
                        <th class="px-4 py-3 text-left text-sm font-medium text-gray-700 dark:text-gray-400">Revenus</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vehicleStats as $vehicle)
                        <tr class="border-b border-gray-100 dark:border-gray-800 table-row-hover">
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-800 dark:text-white/90">{{ $vehicle['name'] }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $vehicle['plate_number'] }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ ucfirst($vehicle['type']) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $vehicle['schedules_count'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $vehicle['rentals_count'] }}</td>
                            <td class="px-4 py-3 font-medium text-gray-800 dark:text-white/90">
                                {{ number_format($vehicle['revenue'], 0, ',', ' ') }} FCFA
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                                Aucune donnée disponible
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Configuration ApexCharts
        const chartOptions = {
            chart: {
                type: 'line',
                height: 300,
                toolbar: { show: false },
                fontFamily: 'Outfit, sans-serif',
            },
            colors: ['#D4AF37'],
            stroke: {
                curve: 'smooth',
                width: 3,
            },
            xaxis: {
                type: 'datetime',
            },
            tooltip: {
                theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
            },
            grid: {
                borderColor: '#e5e7eb',
            },
        };

        // Graphique des revenus par jour avec comparaison
        const revenueData = @json($revenueByDay->map(function($item) {
            $date = \Carbon\Carbon::parse($item->date);
            return [
                $date->timestamp * 1000,
                (float) ($item->revenue ?? 0)
            ];
        }));

        @if(isset($previousRevenueByDay) && $previousRevenueByDay->count() > 0)
        const previousRevenueData = @json($previousRevenueByDay->map(function($item) use ($period) {
            $date = \Carbon\Carbon::parse($item->date);
            // Décaler les dates pour aligner avec la période actuelle
            $shiftedDate = $date->copy()->addDays($period);
            return [
                $shiftedDate->timestamp * 1000,
                (float) ($item->revenue ?? 0)
            ];
        }));

        new ApexCharts(document.querySelector("#revenueChart"), {
            ...chartOptions,
            colors: ['#D4AF37', '#9ca3af'],
            series: [
                {
                    name: 'Période actuelle',
                    data: revenueData
                },
                {
                    name: 'Période précédente',
                    data: previousRevenueData
                }
            ],
            yaxis: {
                labels: {
                    formatter: function(val) {
                        return new Intl.NumberFormat('fr-FR').format(val) + ' FCFA';
                    }
                }
            },
            legend: {
                show: true,
                position: 'top',
            }
        }).render();
        @else
        new ApexCharts(document.querySelector("#revenueChart"), {
            ...chartOptions,
            series: [{
                name: 'Revenus (FCFA)',
                data: revenueData
            }],
            yaxis: {
                labels: {
                    formatter: function(val) {
                        return new Intl.NumberFormat('fr-FR').format(val) + ' FCFA';
                    }
                }
            }
        }).render();
        @endif

        // Graphique des réservations par jour
        const bookingsData = @json($bookingsByDay->map(function($item) {
            $date = \Carbon\Carbon::parse($item->date);
            return [
                $date->timestamp * 1000,
                (int) ($item->count ?? 0)
            ];
        }));

        new ApexCharts(document.querySelector("#bookingsChart"), {
            ...chartOptions,
            colors: ['#10b981'],
            series: [{
                name: 'Réservations',
                data: bookingsData
            }],
            yaxis: {
                labels: {
                    formatter: function(val) {
                        return Math.round(val);
                    }
                }
            }
        }).render();

        // Graphique des revenus par méthode de paiement
        const paymentMethods = @json($revenueByPaymentMethod->pluck('method'));
        const paymentRevenues = @json($revenueByPaymentMethod->pluck('total'));
        const paymentLabels = {
            'orange_money': 'Orange Money',
            'free_money': 'Free Money',
            'wave': 'Wave',
            'cash': 'Espèces',
            'card': 'Carte',
        };

        new ApexCharts(document.querySelector("#paymentMethodChart"), {
            chart: {
                type: 'donut',
                height: 300,
                toolbar: { show: false },
            },
            labels: paymentMethods.map(m => paymentLabels[m] || m),
            series: paymentRevenues,
            colors: ['#D4AF37', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
            legend: {
                position: 'bottom',
            },
            tooltip: {
                theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
                y: {
                    formatter: function(val) {
                        return new Intl.NumberFormat('fr-FR').format(val) + ' FCFA';
                    }
                }
            }
        }).render();

        // Graphique de répartition des statuts
        const statusData = @json($bookingStatusDistribution);
        const statusLabels = {
            'pending': 'En attente',
            'confirmed': 'Confirmée',
            'cancelled': 'Annulée',
            'completed': 'Terminée',
        };

        new ApexCharts(document.querySelector("#statusChart"), {
            chart: {
                type: 'pie',
                height: 300,
                toolbar: { show: false },
            },
            labels: statusData.map(s => statusLabels[s.status] || s.status),
            series: statusData.map(s => s.count),
            colors: ['#f59e0b', '#10b981', '#ef4444', '#6b7280'],
            legend: {
                position: 'bottom',
            },
            tooltip: {
                theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
            }
        }).render();
    });
</script>
@endpush
@endsection
