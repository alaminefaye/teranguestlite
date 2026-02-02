<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tous les Billets - {{ $booking->booking_reference }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        @page {
            size: 80mm auto;
            margin: 0;
        }
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            background: white;
            padding: 0;
            margin: 0;
        }
        .ticket {
            width: 80mm;
            max-width: 80mm;
            margin: 0 auto;
            padding: 4mm 5mm;
            background: white;
            font-size: 9px;
            line-height: 1.2;
            page-break-after: always;
            page-break-inside: avoid;
        }
        .ticket:last-child {
            page-break-after: auto;
        }
        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 2mm;
            margin-bottom: 2mm;
        }
        .header-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 3px;
            margin-bottom: 1mm;
        }
        .header-logo img {
            height: 15px;
            width: auto;
            max-width: 30px;
        }
        .header h1 {
            font-size: 12px;
            font-weight: bold;
            color: #000;
            margin: 1mm 0 0.5mm 0;
        }
        .header .subtitle {
            font-size: 7px;
            color: #000;
            margin-bottom: 1mm;
        }
        .ticket-number {
            text-align: center;
            margin-top: 1mm;
        }
        .ticket-number-label {
            font-size: 7px;
            color: #000;
        }
        .ticket-number-value {
            font-size: 10px;
            font-weight: bold;
            color: #000;
        }
        .qr-section {
            text-align: center;
            margin: 2mm 0;
            padding: 2mm;
            border: 1px solid #000;
        }
        .qr-section-label {
            font-size: 8px;
            font-weight: bold;
            margin-bottom: 1mm;
            color: #000;
        }
        .qr-section img {
            width: 40mm;
            max-width: 40mm;
            height: 40mm;
            margin: 0 auto;
            display: block;
            object-fit: contain;
        }
        .qr-section-note {
            font-size: 6px;
            margin-top: 1mm;
            color: #000;
        }
        .info-section {
            margin: 2mm 0;
        }
        .info-row {
            margin-bottom: 1.5mm;
            border-bottom: 1px dotted #ccc;
            padding-bottom: 1mm;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-size: 7px;
            color: #666;
            margin-bottom: 0.5mm;
        }
        .info-value {
            font-size: 9px;
            font-weight: bold;
            color: #000;
        }
        .info-value-large {
            font-size: 14px;
            font-weight: bold;
            color: #000;
        }
        .route-section {
            margin: 2mm 0;
            padding: 1.5mm;
            border: 1px solid #000;
        }
        .route-row {
            margin-bottom: 1.5mm;
        }
        .route-row:last-child {
            margin-bottom: 0;
        }
        .route-label {
            font-size: 7px;
            color: #666;
            margin-bottom: 0.5mm;
        }
        .route-station {
            font-size: 10px;
            font-weight: bold;
            color: #000;
            margin-bottom: 0.5mm;
        }
        .route-datetime {
            font-size: 8px;
            color: #000;
        }
        .divider {
            border-top: 1px dashed #000;
            margin: 1.5mm 0;
        }
        .footer-section {
            margin-top: 2mm;
            padding-top: 1.5mm;
            border-top: 1px dashed #000;
            font-size: 6px;
            color: #000;
            text-align: center;
            line-height: 1.3;
        }
        .footer-section strong {
            font-weight: bold;
        }
        @media print {
            body {
                padding: 0;
                margin: 0;
            }
            .ticket {
                padding: 4mm 5mm;
                border: none;
                page-break-after: always;
            }
            .ticket:last-child {
                page-break-after: auto;
            }
            @page {
                size: 80mm auto;
                margin: 0;
            }
        }
        /* Aperçu à l'écran */
        @media screen {
            body {
                background: #f5f5f5;
                padding: 20px;
            }
            .ticket {
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
                border: 1px solid #ddd;
                margin-bottom: 20px;
                width: 302px; /* 80mm ≈ 302px */
                max-width: 302px;
            }
            .ticket:last-child {
                margin-bottom: 0;
            }
        }
    </style>
    @if(isset($autoPrint) && $autoPrint)
    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 250);
        };
    </script>
    @endif
</head>
<body>
    @foreach($ticketsWithQrCodes as $ticketData)
        @php
            $ticket = $ticketData['ticket'];
            $qrCodeUrl = $ticketData['qrCodeUrl'];
            $qrCodeBase64 = $ticketData['qrCodeBase64'];
        @endphp
        <div class="ticket">
            <!-- En-tête -->
            <div class="header">
                <div class="header-logo">
                    @if(file_exists(public_path('images/logo/sennavette-logo.jpeg')))
                        <img src="{{ asset('images/logo/sennavette-logo.jpeg') }}" alt="SEN NAVETTE">
                    @elseif(file_exists(public_path('images/logo/logo.svg')))
                        <img src="{{ asset('images/logo/logo.svg') }}" alt="SEN NAVETTE">
                    @endif
                </div>
                <h1>SEN NAVETTE</h1>
                <p class="subtitle">Transport Collectif Digitalisé</p>
                <div class="ticket-number">
                    <p class="ticket-number-label">Billet N°</p>
                    <p class="ticket-number-value">{{ $ticket->ticket_number }}</p>
                </div>
            </div>

            <!-- QR Code -->
            <div class="qr-section">
                <p class="qr-section-label">CODE QR</p>
                @if($qrCodeBase64)
                    <img src="{{ $qrCodeBase64 }}" alt="QR Code">
                @elseif($qrCodeUrl)
                    <img src="{{ $qrCodeUrl }}" alt="QR Code" 
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                    <div style="display: none; width: 40mm; height: 40mm; background: #f3f4f6; margin: 0 auto; display: flex; align-items: center; justify-content: center; border: 1px solid #ddd;">
                        <p style="color: #999; font-size: 7px;">QR Code non disponible</p>
                    </div>
                @else
                    <div style="width: 40mm; height: 40mm; background: #f3f4f6; margin: 0 auto; display: flex; align-items: center; justify-content: center; border: 1px solid #ddd;">
                        <p style="color: #999; font-size: 7px;">QR Code non disponible</p>
                    </div>
                @endif
                <p class="qr-section-note">Scannez pour vérifier</p>
            </div>

            <!-- Informations passager -->
            <div class="info-section">
                <div class="info-row">
                    <p class="info-label">PASSAGER</p>
                    <p class="info-value">{{ $booking->user->name ?? 'N/A' }}</p>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 1.5mm; padding-bottom: 1mm; border-bottom: 1px dotted #ccc;">
                    <div style="flex: 1;">
                        <p class="info-label">RÉFÉRENCE</p>
                        <p class="info-value" style="font-size: 8px;">{{ $booking->booking_reference ?? 'N/A' }}</p>
                    </div>
                    <div style="flex: 1; text-align: right;">
                        <p class="info-label">SIÈGE</p>
                        <p class="info-value-large" style="font-size: 18px;">{{ $ticket->seat_number ?? $booking->seat_number ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <div class="divider"></div>

            <!-- Informations trajet -->
            <div class="route-section">
                <div class="route-row">
                    <p class="route-label">DÉPART</p>
                    <p class="route-station">{{ $booking->schedule->route->departureStation->name ?? 'N/A' }}</p>
                    <p class="route-datetime">{{ $booking->schedule->schedule_date->format('d/m/Y') }} à {{ $booking->schedule->departure_time }}</p>
                </div>
                <div class="route-row" style="margin-top: 1.5mm; padding-top: 1.5mm; border-top: 1px dotted #ccc;">
                    <p class="route-label">ARRIVÉE</p>
                    <p class="route-station">{{ $booking->schedule->route->arrivalStation->name ?? 'N/A' }}</p>
                    <p class="route-datetime">Arrivée: {{ $booking->schedule->arrival_time }}</p>
                </div>
            </div>

            <div class="divider"></div>

            <!-- Informations supplémentaires -->
            <div class="info-section">
                <div style="display: flex; justify-content: space-between;">
                    <div style="flex: 1;">
                        <p class="info-label">VÉHICULE</p>
                        <p class="info-value" style="font-size: 8px;">{{ $booking->schedule->vehicle->name ?? 'N/A' }}</p>
                    </div>
                    <div style="flex: 1; text-align: right;">
                        <p class="info-label">PRIX</p>
                        <p class="info-value">
                            @php
                                $seatsCount = $booking->seats->count() > 0 ? $booking->seats->count() : ($booking->tickets->count() > 0 ? $booking->tickets->count() : 1);
                                $pricePerSeat = $booking->total_price / $seatsCount;
                            @endphp
                            {{ number_format($pricePerSeat, 0, ',', ' ') }} FCFA
                        </p>
                    </div>
                </div>
            </div>

            <!-- Instructions -->
            <div class="footer-section">
                <p><strong>Instructions:</strong> Présentez ce billet au contrôleur lors de l'embarquement.</p>
                <p style="margin-top: 1mm;">Valable uniquement pour le trajet et la date indiqués.</p>
            </div>
        </div>
    @endforeach
</body>
</html>
