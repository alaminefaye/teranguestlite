@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('reservations.show', $booking) }}" 
               class="inline-flex items-center justify-center w-10 h-10 rounded-lg text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-white/5">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div>
                <h1 class="text-title-md font-semibold text-gray-800 dark:text-white/90">Billet de Voyage</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Numéro: {{ $ticket->ticket_number ?? 'N/A' }}</p>
                @if(isset($allTickets) && $allTickets->count() > 1)
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $allTickets->count() }} billets au total</p>
                @endif
            </div>
        </div>
        <div class="flex items-center gap-3">
            @if(isset($allTickets) && $allTickets->count() > 1)
                <!-- Sélecteur de ticket si plusieurs billets -->
                <div class="flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 dark:border-gray-700 dark:bg-gray-800">
                    <span class="text-xs text-gray-600 dark:text-gray-400">Billet:</span>
                    <select id="ticket-selector" class="text-sm text-gray-800 dark:text-white/90 bg-transparent border-none focus:outline-none">
                        @foreach($allTickets as $t)
                            <option value="{{ route('tickets.show', ['booking' => $booking->id, 'ticket' => $t->id]) }}" 
                                    {{ $ticket && $ticket->id === $t->id ? 'selected' : '' }}>
                                Siège {{ $t->seat_number ?? 'N/A' }} - {{ $t->ticket_number }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
            <a href="{{ route('tickets.download', $booking->id) }}?ticket={{ $ticket->id ?? '' }}" target="_blank"
               class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Télécharger
            </a>
            <a href="{{ route('tickets.download', $booking->id) }}?ticket={{ $ticket->id ?? '' }}&print=1" target="_blank"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white shadow-theme-xs hover:bg-brand-600">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Imprimer
            </a>
        </div>
    </div>
    
    @if(isset($allTickets) && $allTickets->count() > 1)
    <script>
        document.getElementById('ticket-selector')?.addEventListener('change', function() {
            window.location.href = this.value;
        });
    </script>
    @endif

    <!-- Billet -->
    <div class="mx-auto max-w-2xl">
        <div class="rounded-lg border-2 border-gray-300 bg-white p-8 shadow-theme-lg dark:border-gray-700 dark:bg-gray-900">
            <!-- En-tête -->
            <div class="mb-6 border-b-2 border-dashed border-gray-300 pb-6 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        @if(file_exists(public_path('images/logo/sennavette-logo.jpeg')))
                            <img src="{{ asset('images/logo/sennavette-logo.jpeg') }}" alt="SEN NAVETTE" class="h-16 w-auto">
                        @elseif(file_exists(public_path('images/logo/logo.svg')))
                            <img src="{{ asset('images/logo/logo.svg') }}" alt="SEN NAVETTE" class="h-16 w-auto">
                        @endif
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">SEN NAVETTE</h2>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Transport Collectif Digitalisé</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-500 dark:text-gray-400">Billet N°</p>
                        <p class="text-lg font-bold text-brand-500">{{ $ticket->ticket_number }}</p>
                    </div>
                </div>
            </div>

            <!-- QR Code et Informations -->
            <div class="mb-6 grid grid-cols-1 gap-6 md:grid-cols-2">
                <!-- QR Code -->
                <div class="flex flex-col items-center justify-center rounded-lg border border-gray-200 bg-gray-50 p-6 dark:border-gray-800 dark:bg-gray-800">
                    <p class="mb-3 text-xs font-medium text-gray-600 dark:text-gray-400">Code QR</p>
                    <div class="mb-2 rounded-lg bg-white p-3">
                        @php
                            // Utiliser asset() pour générer l'URL correcte du QR code
                            $qrCodeUrl = asset('storage/' . $ticket->qr_code);
                            // Vérifier si le fichier existe
                            $qrCodePath = storage_path('app/public/' . $ticket->qr_code);
                            $qrCodeExists = file_exists($qrCodePath);
                        @endphp
                        @if($qrCodeExists)
                            <img src="{{ $qrCodeUrl }}" alt="QR Code" class="h-48 w-48 object-contain">
                        @else
                            <div class="flex h-48 w-48 items-center justify-center rounded border border-gray-300 bg-gray-100">
                                <p class="text-xs text-gray-500">QR Code non disponible</p>
                            </div>
                        @endif
                    </div>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Scannez pour vérifier</p>
                </div>

                <!-- Informations du passager -->
                <div class="space-y-4">
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Passager</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $booking->user->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Référence</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $booking->booking_reference }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Siège</p>
                        <p class="text-lg font-bold text-brand-500">{{ $ticket->seat_number ?? $booking->seat_number ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <!-- Informations du trajet -->
            <div class="mb-6 rounded-lg border border-gray-200 bg-gray-50 p-6 dark:border-gray-800 dark:bg-gray-800">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <p class="mb-1 text-xs font-medium text-gray-500 dark:text-gray-400">Départ</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $booking->schedule->route->departureStation->name ?? 'N/A' }}
                        </p>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ $booking->schedule->schedule_date->format('d/m/Y') }} à {{ $booking->schedule->departure_time }}
                        </p>
                    </div>
                    <div>
                        <p class="mb-1 text-xs font-medium text-gray-500 dark:text-gray-400">Arrivée</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $booking->schedule->route->arrivalStation->name ?? 'N/A' }}
                        </p>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Arrivée prévue: {{ $booking->schedule->arrival_time }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Informations supplémentaires -->
            <div class="grid grid-cols-2 gap-4 border-t border-gray-200 pt-6 dark:border-gray-700">
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Véhicule</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $booking->schedule->vehicle->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Prix</p>
                    <p class="text-sm font-bold text-gray-900 dark:text-white">{{ number_format($booking->total_price, 0, ',', ' ') }} FCFA</p>
                </div>
            </div>

            <!-- Instructions -->
            <div class="mt-6 rounded-lg bg-brand-50 p-4 dark:bg-brand-500/10">
                <p class="text-xs font-medium text-brand-700 dark:text-brand-400">
                    <strong>Instructions:</strong> Présentez ce billet (ou le QR Code) au contrôleur lors de l'embarquement. 
                    Le billet est valable uniquement pour le trajet et la date indiqués.
                </p>
            </div>

            <!-- Statut -->
            <div class="mt-6 flex items-center justify-center">
                @php
                    $statusConfig = [
                        'valid' => ['color' => 'bg-success-50 text-success-600', 'label' => 'Valide'],
                        'used' => ['color' => 'bg-gray-50 text-gray-600', 'label' => 'Utilisé'],
                        'expired' => ['color' => 'bg-error-50 text-error-600', 'label' => 'Expiré'],
                    ];
                    $currentStatus = $statusConfig[$ticket->status] ?? $statusConfig['valid'];
                @endphp
                <span class="inline-flex items-center rounded-full px-4 py-2 text-sm font-medium {{ $currentStatus['color'] }}">
                    Statut: {{ $currentStatus['label'] }}
                </span>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    @media print {
        body * {
            visibility: hidden;
        }
        .rounded-lg, .rounded-lg * {
            visibility: visible;
        }
        .rounded-lg {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }
        button, a {
            display: none !important;
        }
    }
</style>
@endpush
@endsection
