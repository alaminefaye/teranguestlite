<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Facture {{ $reservation->reservation_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; line-height: 1.4; }
        .header { display: table; width: 100%; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid #ddd; }
        .header-left { display: table-cell; width: 60%; vertical-align: top; }
        .header-right { display: table-cell; width: 40%; text-align: right; vertical-align: top; }
        .logo { max-height: 56px; margin-bottom: 8px; }
        h1 { font-size: 18px; margin: 0 0 6px 0; }
        .meta { font-size: 11px; color: #444; margin: 4px 0; }
        .section { margin-bottom: 20px; }
        .section-title { font-size: 11px; font-weight: bold; text-transform: uppercase; color: #555; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { padding: 8px 10px; text-align: left; border-bottom: 1px solid #eee; }
        th { font-size: 10px; text-transform: uppercase; color: #555; background: #f5f5f5; }
        td.total-row { font-weight: bold; }
        .text-right { text-align: right; }
        .grand-total { margin-top: 20px; padding-top: 16px; border-top: 2px solid #333; text-align: right; }
        .grand-total .amount { font-size: 18px; font-weight: bold; }
        .footer-note { margin-top: 24px; font-size: 10px; color: #666; font-style: italic; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            @if($logoBase64)
                <img src="data:{{ $logoMime ?? 'image/png' }};base64,{{ $logoBase64 }}" alt="{{ $reservation->enterprise?->name }}" class="logo">
            @endif
            <h1>{{ $reservation->enterprise?->name ?? 'Établissement' }}</h1>
            @if($reservation->enterprise)
                @if($reservation->enterprise->address)
                    <p class="meta">{{ $reservation->enterprise->address }}</p>
                @endif
                @if($reservation->enterprise->phone)
                    <p class="meta">{{ $reservation->enterprise->phone }}</p>
                @endif
                @if($reservation->enterprise->email)
                    <p class="meta">{{ $reservation->enterprise->email }}</p>
                @endif
            @endif
        </div>
        <div class="header-right">
            <p style="font-size: 16px; font-weight: bold; margin: 0 0 4px 0;">FACTURE / REÇU</p>
            <p class="meta">Réservation {{ $reservation->reservation_number }}</p>
            <p class="meta">Date d'émission : {{ $emittedAt }}</p>
        </div>
    </div>

    <div class="section" style="display: table; width: 100%;">
        <div style="display: table-cell; width: 50%;">
            <div class="section-title">Client</div>
            <p style="margin: 0; font-weight: bold;">{{ $reservation->guest?->name ?? $reservation->user?->name ?? '—' }}</p>
            <p class="meta" style="margin: 2px 0 0 0;">{{ $reservation->guest?->email ?? $reservation->user?->email ?? '—' }}</p>
        </div>
        <div style="display: table-cell; width: 50%;">
            <div class="section-title">Séjour</div>
            <p style="margin: 0;">Chambre {{ $reservation->room->room_number }}</p>
            <p class="meta" style="margin: 2px 0 0 0;">Check-in : {{ $reservation->check_in->format('d/m/Y H:i') }}</p>
            <p class="meta" style="margin: 0;">Check-out : {{ $reservation->check_out->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Hébergement</div>
        <table>
            <thead>
                <tr>
                    <th>Désignation</th>
                    <th class="text-right">Montant</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Chambre {{ $reservation->room->room_number }} — {{ $reservation->nights_count }} nuit(s)</td>
                    <td class="text-right">{{ number_format($reservation->total_price, 0, ',', ' ') }} FCFA</td>
                </tr>
            </tbody>
        </table>
    </div>

    @if($roomBillOrders->count() > 0)
    <div class="section">
        <div class="section-title">Consommations (note de chambre)</div>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>N° commande</th>
                    <th>Détail</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($roomBillOrders as $order)
                <tr>
                    <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                    <td style="font-family: monospace;">{{ $order->order_number }}</td>
                    <td>
                        @foreach($order->orderItems as $item)
                            {{ $item->quantity }}× {{ $item->item_name }}<br>
                        @endforeach
                    </td>
                    <td class="text-right">{{ number_format($order->total, 0, ',', ' ') }} FCFA</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if($reservation->settlements->count() > 0)
    <div class="section">
        <div class="section-title">Règlements</div>
        <table>
            <tbody>
                @foreach($reservation->settlements as $s)
                <tr>
                    <td>{{ $s->paid_at->format('d/m/Y H:i') }} — {{ $s->payment_method_name }}</td>
                    <td class="text-right">{{ number_format($s->amount, 0, ',', ' ') }} FCFA</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="grand-total">
        <p class="meta" style="margin: 0 0 4px 0;">Total général</p>
        <p class="amount" style="margin: 0;">{{ number_format($grandTotal, 0, ',', ' ') }} FCFA</p>
    </div>

    <p class="footer-note">Merci pour votre séjour. Ce document fait office de reçu / facture.</p>
</body>
</html>
