@extends('layouts.app')

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <div>
        <h1 class="text-title-md2 font-semibold text-gray-900 dark:text-white/90">{{ $title }}</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Période : du {{ \Carbon\Carbon::parse($date_from)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($date_to)->format('d/m/Y') }}</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('dashboard.reports.index') }}?date_from={{ $date_from }}&date_to={{ $date_to }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">
            ← Tous les rapports
        </a>
        <a href="{{ route('dashboard.reports.show', $type) }}?date_from={{ $date_from }}&date_to={{ $date_to }}&export=pdf" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
            Exporter PDF
        </a>
        @if(in_array($type, ['global', 'reservations', 'orders', 'audit'], true))
            <a href="{{ route('dashboard.reports.show', $type) }}?date_from={{ $date_from }}&date_to={{ $date_to }}&export=csv" class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">
                Exporter CSV
            </a>
        @endif
    </div>
</div>

@switch($type)
    @case('global')
        @php $totals = $data['totals'] ?? []; $months = $data['months'] ?? []; @endphp
        <!-- Totaux sur la période -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90 mb-3">Totaux sur la période</h2>
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Réservations</p>
                    <p class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ $totals['reservations_count'] ?? 0 }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs text-gray-500 dark:text-gray-400">CA Hébergement</p>
                    <p class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ number_format($totals['reservations_revenue'] ?? 0, 0, ',', ' ') }} F</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Commandes</p>
                    <p class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ $totals['orders_count'] ?? 0 }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs text-gray-500 dark:text-gray-400">CA Commandes</p>
                    <p class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ number_format($totals['orders_revenue'] ?? 0, 0, ',', ' ') }} F</p>
                </div>
                <div class="rounded-lg border border-brand-200 bg-brand-50 p-4 dark:border-brand-800 dark:bg-brand-900/20">
                    <p class="text-xs text-brand-600 dark:text-brand-400">Total CA</p>
                    <p class="text-lg font-semibold text-brand-700 dark:text-brand-300">{{ number_format($totals['total_revenue'] ?? 0, 0, ',', ' ') }} F</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Nouveaux clients</p>
                    <p class="text-xl font-semibold text-gray-800 dark:text-white/90">{{ $totals['new_guests'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        <!-- Services (totaux) -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90 mb-3">Activité des services</h2>
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-5">
                <div class="rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Résa. Spa</p>
                    <p class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $totals['spa'] ?? 0 }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Résa. Restaurants</p>
                    <p class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $totals['restaurant'] ?? 0 }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Blanchisserie</p>
                    <p class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $totals['laundry'] ?? 0 }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Services Palace</p>
                    <p class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $totals['palace'] ?? 0 }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Excursions</p>
                    <p class="text-lg font-semibold text-gray-800 dark:text-white/90">{{ $totals['excursions'] ?? 0 }}</p>
                </div>
            </div>
        </div>
        <!-- Détail par mois -->
        <div class="rounded-lg border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90 px-4 py-3 border-b border-gray-200 dark:border-gray-800">Détail par mois</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-800 dark:text-white/90">
                    <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-500 dark:text-gray-400 uppercase">
                        <tr>
                            <th class="px-4 py-3">Mois</th>
                            <th class="px-4 py-3 text-right">Résa.</th>
                            <th class="px-4 py-3 text-right">CA Héberg. (FCFA)</th>
                            <th class="px-4 py-3 text-right">Cmd.</th>
                            <th class="px-4 py-3 text-right">CA Cmd. (FCFA)</th>
                            <th class="px-4 py-3 text-right">Spa</th>
                            <th class="px-4 py-3 text-right">Resto</th>
                            <th class="px-4 py-3 text-right">Blanch.</th>
                            <th class="px-4 py-3 text-right">Palace</th>
                            <th class="px-4 py-3 text-right">Excurs.</th>
                            <th class="px-4 py-3 text-right">Clients</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($months as $monthKey => $row)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-4 py-3 font-medium">{{ $row['label'] }}</td>
                                <td class="px-4 py-3 text-right">{{ $row['reservations_count'] }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($row['reservations_revenue'], 0, ',', ' ') }}</td>
                                <td class="px-4 py-3 text-right">{{ $row['orders_count'] }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($row['orders_revenue'], 0, ',', ' ') }}</td>
                                <td class="px-4 py-3 text-right">{{ $row['spa'] }}</td>
                                <td class="px-4 py-3 text-right">{{ $row['restaurant'] }}</td>
                                <td class="px-4 py-3 text-right">{{ $row['laundry'] }}</td>
                                <td class="px-4 py-3 text-right">{{ $row['palace'] }}</td>
                                <td class="px-4 py-3 text-right">{{ $row['excursions'] }}</td>
                                <td class="px-4 py-3 text-right">{{ $row['new_guests'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="11" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">Aucune donnée sur la période.</td></tr>
                        @endforelse
                    </tbody>
                    @if(count($months) > 0)
                        <tfoot class="bg-gray-50 dark:bg-gray-800/50 font-semibold">
                            <tr>
                                <td class="px-4 py-3">Total</td>
                                <td class="px-4 py-3 text-right">{{ $totals['reservations_count'] ?? 0 }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($totals['reservations_revenue'] ?? 0, 0, ',', ' ') }}</td>
                                <td class="px-4 py-3 text-right">{{ $totals['orders_count'] ?? 0 }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($totals['orders_revenue'] ?? 0, 0, ',', ' ') }}</td>
                                <td class="px-4 py-3 text-right">{{ $totals['spa'] ?? 0 }}</td>
                                <td class="px-4 py-3 text-right">{{ $totals['restaurant'] ?? 0 }}</td>
                                <td class="px-4 py-3 text-right">{{ $totals['laundry'] ?? 0 }}</td>
                                <td class="px-4 py-3 text-right">{{ $totals['palace'] ?? 0 }}</td>
                                <td class="px-4 py-3 text-right">{{ $totals['excursions'] ?? 0 }}</td>
                                <td class="px-4 py-3 text-right">{{ $totals['new_guests'] ?? 0 }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
        @break

    @case('overview')
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-6">
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">Réservations (période)</p>
                <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $data['reservations_count'] ?? 0 }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">Chiffre d'affaires hébergement</p>
                <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ number_format($data['reservations_revenue'] ?? 0, 0, ',', ' ') }} FCFA</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">Commandes (période)</p>
                <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $data['orders_count'] ?? 0 }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">Chiffre d'affaires commandes</p>
                <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ number_format($data['orders_revenue'] ?? 0, 0, ',', ' ') }} FCFA</p>
            </div>
        </div>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 mb-6">
            <div class="rounded-lg border border-brand-200 bg-brand-50 p-4 dark:border-brand-800 dark:bg-brand-900/20">
                <p class="text-sm text-brand-600 dark:text-brand-400">Total CA (hébergement + commandes)</p>
                <p class="text-2xl font-semibold text-brand-700 dark:text-brand-300">{{ number_format($data['total_revenue'] ?? 0, 0, ',', ' ') }} FCFA</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">Nouveaux clients (période)</p>
                <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $data['new_guests'] ?? 0 }}</p>
            </div>
        </div>
        @break

    @case('reservations')
        <div class="rounded-lg border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-800 dark:text-white/90">
                    <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-500 dark:text-gray-400 uppercase">
                        <tr>
                            <th class="px-4 py-3">Référence</th>
                            <th class="px-4 py-3">Chambre</th>
                            <th class="px-4 py-3">Client</th>
                            <th class="px-4 py-3">Check-in</th>
                            <th class="px-4 py-3">Check-out</th>
                            <th class="px-4 py-3">Statut</th>
                            <th class="px-4 py-3 text-right">Montant (FCFA)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($data['items'] ?? [] as $r)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-4 py-3 font-medium">{{ $r->reservation_number }}</td>
                                <td class="px-4 py-3">{{ $r->room?->room_number ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $r->guest?->name ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $r->check_in?->format('d/m/Y') }}</td>
                                <td class="px-4 py-3">{{ $r->check_out?->format('d/m/Y') }}</td>
                                <td class="px-4 py-3">{{ $r->status ?? '—' }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($r->total_price ?? 0, 0, ',', ' ') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">Aucune réservation sur la période</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @break

    @case('orders')
        <div class="rounded-lg border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-800 dark:text-white/90">
                    <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-500 dark:text-gray-400 uppercase">
                        <tr>
                            <th class="px-4 py-3">N° Commande</th>
                            <th class="px-4 py-3">Chambre</th>
                            <th class="px-4 py-3">Client</th>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Statut</th>
                            <th class="px-4 py-3">Paiement</th>
                            <th class="px-4 py-3 text-right">Total (FCFA)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($data['items'] ?? [] as $o)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-4 py-3 font-medium">{{ $o->order_number }}</td>
                                <td class="px-4 py-3">{{ $o->room?->room_number ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $o->guest?->name ?? $o->user?->name ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $o->created_at?->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3">{{ $o->status ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $o->payment_method ?? '—' }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($o->total ?? 0, 0, ',', ' ') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">Aucune commande sur la période</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @break

    @case('billing')
        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="rounded-lg border border-warning-200 bg-warning-50 p-4 dark:border-warning-800 dark:bg-warning-900/20">
                <p class="text-sm text-warning-600 dark:text-warning-400">Total à régler (notes de chambre)</p>
                <p class="text-2xl font-semibold text-warning-700 dark:text-warning-300">{{ number_format($data['total_due'] ?? 0, 0, ',', ' ') }} FCFA</p>
            </div>
            <div class="rounded-lg border border-success-200 bg-success-50 p-4 dark:border-success-800 dark:bg-success-900/20">
                <p class="text-sm text-success-600 dark:text-success-400">Total déjà réglé</p>
                <p class="text-2xl font-semibold text-success-700 dark:text-success-300">{{ number_format($data['total_paid'] ?? 0, 0, ',', ' ') }} FCFA</p>
            </div>
        </div>
        <div class="rounded-lg border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-800 dark:text-white/90">
                    <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-500 dark:text-gray-400 uppercase">
                        <tr>
                            <th class="px-4 py-3">Référence</th>
                            <th class="px-4 py-3">Client</th>
                            <th class="px-4 py-3">Chambre</th>
                            <th class="px-4 py-3">Séjour</th>
                            <th class="px-4 py-3 text-right">À régler</th>
                            <th class="px-4 py-3 text-right">Réglé</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($data['rows'] ?? [] as $row)
                            @php $r = $row->reservation; @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-4 py-3 font-medium">{{ $r->reservation_number }}</td>
                                <td class="px-4 py-3">{{ $r->guest?->name ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $r->room?->room_number ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $r->check_in?->format('d/m/Y') }} → {{ $r->check_out?->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($row->total_due ?? 0, 0, ',', ' ') }}</td>
                                <td class="px-4 py-3 text-right">{{ number_format($row->total_paid ?? 0, 0, ',', ' ') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @break

    @case('services')
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">Réservations Spa</p>
                <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $data['spa'] ?? 0 }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">Réservations Restaurants</p>
                <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $data['restaurant'] ?? 0 }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">Demandes Blanchisserie</p>
                <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $data['laundry'] ?? 0 }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">Demandes Services Palace</p>
                <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $data['palace'] ?? 0 }}</p>
            </div>
            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
                <p class="text-sm text-gray-500 dark:text-gray-400">Réservations Excursions</p>
                <p class="text-2xl font-semibold text-gray-800 dark:text-white/90">{{ $data['excursions'] ?? 0 }}</p>
            </div>
        </div>
        @break

    @case('audit')
        <div class="rounded-lg border border-gray-200 bg-white shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-800 dark:text-white/90">
                    <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-500 dark:text-gray-400 uppercase">
                        <tr>
                            <th class="px-4 py-3">Date / Heure</th>
                            <th class="px-4 py-3">Utilisateur</th>
                            <th class="px-4 py-3">Action</th>
                            <th class="px-4 py-3">Description</th>
                            <th class="px-4 py-3">Modèle</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($data['items'] ?? [] as $a)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-4 py-3 whitespace-nowrap">{{ $a->created_at?->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3">{{ $a->user?->name ?? '—' }}</td>
                                <td class="px-4 py-3 font-medium">{{ $a->action }}</td>
                                <td class="px-4 py-3 max-w-xs truncate">{{ $a->description ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $a->model_type ? class_basename($a->model_type) : '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">Aucune activité sur la période. Les actions (création, modification, check-in/out) seront enregistrées ici.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(isset($data['items']) && method_exists($data['items'], 'links'))
                <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">{{ $data['items']->links() }}</div>
            @endif
        </div>
        @break

    @default
        <p class="text-gray-600 dark:text-gray-400">Rapport non disponible.</p>
@endswitch
@endsection
