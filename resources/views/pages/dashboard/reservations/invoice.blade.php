@extends('layouts.app')

@section('content')
<div class="mb-6 flex flex-wrap items-center justify-between gap-4">
    <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
        <a href="{{ route('dashboard.reservations.index') }}" class="hover:text-brand-500">Réservations</a>
        <span>/</span>
        <a href="{{ route('dashboard.reservations.show', $reservation) }}" class="hover:text-brand-500">{{ $reservation->reservation_number }}</a>
        <span>/</span>
        <span>Facture</span>
    </div>
    <div class="flex items-center gap-2 no-print">
        <a href="{{ route('dashboard.reservations.show', $reservation) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Retour
        </a>
        <button type="button" onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-brand-500 text-white rounded-md hover:bg-brand-600">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            Imprimer
        </button>
    </div>
</div>

<div class="rounded-lg border border-gray-200 bg-white p-8 shadow-theme-sm dark:border-gray-800 dark:bg-gray-900 print:border-0 print:shadow-none" id="invoice-content">
    {{-- En-tête : logo + infos entreprise --}}
    <div class="flex flex-wrap items-start justify-between gap-6 pb-6 mb-6 border-b border-gray-200 dark:border-gray-700">
        <div class="flex flex-col items-start">
            @if($reservation->enterprise && $reservation->enterprise->logo)
                <img src="{{ asset('storage/' . $reservation->enterprise->logo) }}" alt="{{ $reservation->enterprise->name }}" class="h-14 w-auto object-contain mb-3">
            @endif
            <h1 class="text-xl font-bold text-gray-900 dark:text-white/90">{{ $reservation->enterprise?->name ?? 'Établissement' }}</h1>
            @if($reservation->enterprise)
                @if($reservation->enterprise->address)
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $reservation->enterprise->address }}</p>
                @endif
                @if($reservation->enterprise->phone)
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $reservation->enterprise->phone }}</p>
                @endif
                @if($reservation->enterprise->email)
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $reservation->enterprise->email }}</p>
                @endif
            @endif
        </div>
        <div class="text-right">
            <p class="text-lg font-semibold text-gray-900 dark:text-white/90">FACTURE / REÇU</p>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Réservation {{ $reservation->reservation_number }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">Date d’émission : {{ now()->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    {{-- Client et séjour --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
            <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">Client</h3>
            <p class="font-medium text-gray-900 dark:text-white/90">{{ $reservation->guest?->name ?? $reservation->user?->name ?? '—' }}</p>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $reservation->guest?->email ?? $reservation->user?->email ?? '—' }}</p>
        </div>
        <div>
            <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase mb-2">Séjour</h3>
            <p class="text-gray-800 dark:text-white/90">Chambre {{ $reservation->room->room_number }}</p>
            <p class="text-sm text-gray-600 dark:text-gray-400">Check-in : {{ $reservation->check_in->format('d/m/Y H:i') }}</p>
            <p class="text-sm text-gray-600 dark:text-gray-400">Check-out : {{ $reservation->check_out->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    {{-- Hébergement (prix chambre) --}}
    <div class="mb-6">
        <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90 mb-3">Hébergement</h3>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800/50">
                <tr>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Désignation</th>
                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Montant</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <tr>
                    <td class="px-3 py-2 text-gray-700 dark:text-gray-300">Chambre {{ $reservation->room->room_number }} — {{ $reservation->nights_count }} nuit(s)</td>
                    <td class="px-3 py-2 text-right font-medium">{{ number_format($reservation->total_price, 0, ',', ' ') }} FCFA</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Consommations (note de chambre) --}}
    @if($roomBillOrders->count() > 0)
    <div class="mb-6">
        <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90 mb-3">Consommations (note de chambre)</h3>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800/50">
                <tr>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">N° commande</th>
                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Détail</th>
                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($roomBillOrders as $order)
                <tr>
                    <td class="px-3 py-2 text-gray-700 dark:text-gray-300">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-3 py-2 font-mono text-gray-700 dark:text-gray-300">{{ $order->order_number }}</td>
                    <td class="px-3 py-2 text-gray-600 dark:text-gray-400">
                        @foreach($order->orderItems as $item)
                            {{ $item->quantity }}× {{ $item->item_name }}<br>
                        @endforeach
                    </td>
                    <td class="px-3 py-2 text-right font-medium">{{ number_format($order->total, 0, ',', ' ') }} FCFA</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Règlements --}}
    @if($reservation->settlements->count() > 0)
    <div class="mb-6">
        <h3 class="text-sm font-semibold text-gray-800 dark:text-white/90 mb-3">Règlements</h3>
        <ul class="space-y-2 text-sm">
            @foreach($reservation->settlements as $s)
                <li class="flex justify-between items-center">
                    <span>{{ $s->paid_at->format('d/m/Y H:i') }} — {{ $s->payment_method_name }}</span>
                    <span class="font-medium">{{ number_format($s->amount, 0, ',', ' ') }} FCFA</span>
                </li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Total --}}
    @php
        $totalConsos = $roomBillOrders->sum('total');
        $grandTotal = (float) $reservation->total_price + $totalConsos;
    @endphp
    <div class="pt-4 border-t-2 border-gray-200 dark:border-gray-700">
        <div class="flex justify-end">
            <div class="text-right">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total général</p>
                <p class="text-2xl font-bold text-brand-600 dark:text-brand-400">{{ number_format($grandTotal, 0, ',', ' ') }} FCFA</p>
            </div>
        </div>
    </div>

    <p class="mt-6 text-xs text-gray-500 dark:text-gray-400 italic">Merci pour votre séjour. Ce document fait office de reçu / facture.</p>
</div>

<style>
@media print {
    .no-print, #sidebar, header[class*="sticky"], .preloader, [x-data] { display: none !important; }
    body { background: white !important; }
    main { padding: 0 !important; }
    #invoice-content { box-shadow: none; border: none; }
}
</style>
@endsection
