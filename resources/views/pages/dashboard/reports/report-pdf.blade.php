<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $reportTitle }} — {{ $enterprise->name }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; line-height: 1.35; }
        .header { display: table; width: 100%; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid #333; }
        .header-left { display: table-cell; width: 60%; vertical-align: top; }
        .header-right { display: table-cell; width: 40%; text-align: right; vertical-align: top; }
        .logo { max-height: 52px; margin-bottom: 6px; }
        .enterprise-name { font-size: 16px; font-weight: bold; margin: 0 0 4px 0; }
        .meta { font-size: 10px; color: #444; margin: 2px 0; }
        .report-title { font-size: 14px; font-weight: bold; margin: 0 0 4px 0; }
        .section { margin-bottom: 16px; }
        .section-title { font-size: 10px; font-weight: bold; text-transform: uppercase; color: #555; margin-bottom: 6px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 10px; }
        th, td { padding: 6px 8px; text-align: left; border: 1px solid #ddd; }
        th { background: #f0f0f0; font-weight: bold; }
        .text-right { text-align: right; }
        .total-row-red { font-weight: bold; color: #c00; }
        .box { border: 1px solid #ddd; padding: 10px; margin-bottom: 10px; }
        .grid-2 { width: 100%; display: table; }
        .grid-2 > div { display: table-cell; width: 50%; padding-right: 10px; }
    </style>
</head>
<body>
    {{-- En-tête : logo + nom de l'entreprise (comme facturation) --}}
    <div class="header">
        <div class="header-left">
            @if($logoBase64 ?? null)
                <img src="data:{{ $logoMime ?? 'image/png' }};base64,{{ $logoBase64 }}" alt="{{ $enterprise->name }}" class="logo">
            @endif
            <h1 class="enterprise-name">{{ $enterprise->name ?? 'Établissement' }}</h1>
            @if($enterprise->address)
                <p class="meta">{{ $enterprise->address }}</p>
            @endif
            @if($enterprise->phone)
                <p class="meta">{{ $enterprise->phone }}</p>
            @endif
            @if($enterprise->email)
                <p class="meta">{{ $enterprise->email }}</p>
            @endif
        </div>
        <div class="header-right">
            <p class="report-title">{{ $reportTitle }}</p>
            <p class="meta">Période : du {{ $date_from_formatted ?? $date_from }} au {{ $date_to_formatted ?? $date_to }}</p>
            <p class="meta">Date d'édition : {{ $emittedAt }}</p>
        </div>
    </div>

    @switch($type)
        @case('global')
            @php $totals = $data['totals'] ?? []; $months = $data['months'] ?? []; @endphp
            <div class="section">
                <div class="section-title">Totaux sur la période</div>
                <table>
                    <tr>
                        <th>Réservations</th><th class="text-right">CA Héberg. (FCFA)</th>
                        <th>Commandes</th><th class="text-right">CA Cmd. (FCFA)</th>
                        <th class="text-right">Total CA (FCFA)</th><th>Nouveaux clients</th>
                    </tr>
                    <tr>
                        <td>{{ $totals['reservations_count'] ?? 0 }}</td>
                        <td class="text-right">{{ number_format($totals['reservations_revenue'] ?? 0, 0, ',', ' ') }}</td>
                        <td>{{ $totals['orders_count'] ?? 0 }}</td>
                        <td class="text-right">{{ number_format($totals['orders_revenue'] ?? 0, 0, ',', ' ') }}</td>
                        <td class="text-right">{{ number_format($totals['total_revenue'] ?? 0, 0, ',', ' ') }}</td>
                        <td>{{ $totals['new_guests'] ?? 0 }}</td>
                    </tr>
                </table>
            </div>
            <div class="section">
                <div class="section-title">Activité des services (totaux)</div>
                <p class="meta">Spa : {{ $totals['spa'] ?? 0 }} — Restaurants : {{ $totals['restaurant'] ?? 0 }} — Blanchisserie : {{ $totals['laundry'] ?? 0 }} — Palace : {{ $totals['palace'] ?? 0 }} — Excursions : {{ $totals['excursions'] ?? 0 }}</p>
            </div>
            <div class="section">
                <div class="section-title">Détail par mois</div>
                <table>
                    <thead>
                        <tr>
                            <th>Mois</th><th class="text-right">Résa.</th><th class="text-right">CA Héberg.</th><th class="text-right">Cmd.</th><th class="text-right">CA Cmd.</th>
                            <th class="text-right">Spa</th><th class="text-right">Resto</th><th class="text-right">Blanch.</th><th class="text-right">Palace</th><th class="text-right">Excurs.</th><th class="text-right">Clients</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($months as $row)
                            <tr>
                                <td>{{ $row['label'] }}</td>
                                <td class="text-right">{{ $row['reservations_count'] }}</td>
                                <td class="text-right">{{ number_format($row['reservations_revenue'], 0, ',', ' ') }}</td>
                                <td class="text-right">{{ $row['orders_count'] }}</td>
                                <td class="text-right">{{ number_format($row['orders_revenue'], 0, ',', ' ') }}</td>
                                <td class="text-right">{{ $row['spa'] }}</td>
                                <td class="text-right">{{ $row['restaurant'] }}</td>
                                <td class="text-right">{{ $row['laundry'] }}</td>
                                <td class="text-right">{{ $row['palace'] }}</td>
                                <td class="text-right">{{ $row['excursions'] }}</td>
                                <td class="text-right">{{ $row['new_guests'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    @if(count($months) > 0)
                        <tfoot style="font-weight: bold;">
                            <tr>
                                <td>Total</td>
                                <td class="text-right">{{ $totals['reservations_count'] ?? 0 }}</td>
                                <td class="text-right">{{ number_format($totals['reservations_revenue'] ?? 0, 0, ',', ' ') }}</td>
                                <td class="text-right">{{ $totals['orders_count'] ?? 0 }}</td>
                                <td class="text-right">{{ number_format($totals['orders_revenue'] ?? 0, 0, ',', ' ') }}</td>
                                <td class="text-right">{{ $totals['spa'] ?? 0 }}</td>
                                <td class="text-right">{{ $totals['restaurant'] ?? 0 }}</td>
                                <td class="text-right">{{ $totals['laundry'] ?? 0 }}</td>
                                <td class="text-right">{{ $totals['palace'] ?? 0 }}</td>
                                <td class="text-right">{{ $totals['excursions'] ?? 0 }}</td>
                                <td class="text-right">{{ $totals['new_guests'] ?? 0 }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
            @break

        @case('overview')
            <table>
                <tr><th>Réservations (période)</th><td>{{ $data['reservations_count'] ?? 0 }}</td></tr>
                <tr><th>CA hébergement (FCFA)</th><td>{{ number_format($data['reservations_revenue'] ?? 0, 0, ',', ' ') }}</td></tr>
                <tr><th>Commandes (période)</th><td>{{ $data['orders_count'] ?? 0 }}</td></tr>
                <tr><th>CA commandes (FCFA)</th><td>{{ number_format($data['orders_revenue'] ?? 0, 0, ',', ' ') }}</td></tr>
                <tr><th>Total CA (FCFA)</th><td><strong>{{ number_format($data['total_revenue'] ?? 0, 0, ',', ' ') }}</strong></td></tr>
                <tr><th>Nouveaux clients</th><td>{{ $data['new_guests'] ?? 0 }}</td></tr>
            </table>
            @break

        @case('reservations')
            @php $itemsReservations = $data['items'] ?? []; $totalReservations = collect($itemsReservations)->sum('total_price'); @endphp
            <table>
                <thead>
                    <tr>
                        <th>Référence</th><th>Chambre</th><th>Client</th><th>Check-in</th><th>Check-out</th><th>Statut</th><th class="text-right">Montant (FCFA)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($itemsReservations as $r)
                        <tr>
                            <td>{{ $r->reservation_number }}</td>
                            <td>{{ $r->room?->room_number ?? '—' }}</td>
                            <td>{{ $r->guest?->name ?? '—' }}</td>
                            <td>{{ $r->check_in?->format('d/m/Y') }}</td>
                            <td>{{ $r->check_out?->format('d/m/Y') }}</td>
                            <td>{{ $r->status ?? '—' }}</td>
                            <td class="text-right">{{ number_format($r->total_price ?? 0, 0, ',', ' ') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7">Aucune réservation sur la période.</td></tr>
                    @endforelse
                </tbody>
                @if(count($itemsReservations) > 0)
                    <tfoot>
                        <tr class="total-row-red">
                            <td colspan="6" class="text-right">Total</td>
                            <td class="text-right">{{ number_format($totalReservations, 0, ',', ' ') }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
            @break

        @case('orders')
            <table>
                <thead>
                    <tr>
                        <th>N° Commande</th><th>Chambre</th><th>Client</th><th>Date</th><th>Statut</th><th>Paiement</th><th class="text-right">Total (FCFA)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data['items'] ?? [] as $o)
                        <tr>
                            <td>{{ $o->order_number }}</td>
                            <td>{{ $o->room?->room_number ?? '—' }}</td>
                            <td>{{ $o->guest?->name ?? $o->user?->name ?? '—' }}</td>
                            <td>{{ $o->created_at?->format('d/m/Y H:i') }}</td>
                            <td>{{ $o->status ?? '—' }}</td>
                            <td>{{ $o->payment_method ?? '—' }}</td>
                            <td class="text-right">{{ number_format($o->total ?? 0, 0, ',', ' ') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7">Aucune commande sur la période.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @break

        @case('billing')
            <div class="section">
                <p><strong>Total à régler :</strong> {{ number_format($data['total_due'] ?? 0, 0, ',', ' ') }} FCFA — <strong>Total réglé :</strong> {{ number_format($data['total_paid'] ?? 0, 0, ',', ' ') }} FCFA</p>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Référence</th><th>Client</th><th>Chambre</th><th>Séjour</th><th class="text-right">À régler</th><th class="text-right">Réglé</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['rows'] ?? [] as $row)
                        @php $r = $row->reservation; @endphp
                        <tr>
                            <td>{{ $r->reservation_number }}</td>
                            <td>{{ $r->guest?->name ?? '—' }}</td>
                            <td>{{ $r->room?->room_number ?? '—' }}</td>
                            <td>{{ $r->check_in?->format('d/m/Y') }} → {{ $r->check_out?->format('d/m/Y') }}</td>
                            <td class="text-right">{{ number_format($row->total_due ?? 0, 0, ',', ' ') }}</td>
                            <td class="text-right">{{ number_format($row->total_paid ?? 0, 0, ',', ' ') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @break

        @case('services')
            <table>
                <tr><th>Réservations Spa</th><td>{{ $data['spa'] ?? 0 }}</td></tr>
                <tr><th>Réservations Restaurants</th><td>{{ $data['restaurant'] ?? 0 }}</td></tr>
                <tr><th>Demandes Blanchisserie</th><td>{{ $data['laundry'] ?? 0 }}</td></tr>
                <tr><th>Demandes Services Palace</th><td>{{ $data['palace'] ?? 0 }}</td></tr>
                <tr><th>Réservations Excursions</th><td>{{ $data['excursions'] ?? 0 }}</td></tr>
            </table>
            @break

        @case('audit')
            <table>
                <thead>
                    <tr>
                        <th>Date / Heure</th><th>Utilisateur</th><th>Action</th><th>Description</th><th>Modèle</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($data['items'] ?? [] as $a)
                        <tr>
                            <td>{{ $a->created_at?->format('d/m/Y H:i') }}</td>
                            <td>{{ $a->user?->name ?? '—' }}</td>
                            <td>{{ $a->action }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($a->description ?? '—', 60) }}</td>
                            <td>{{ $a->model_type ? class_basename($a->model_type) : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5">Aucune activité sur la période.</td></tr>
                    @endforelse
                </tbody>
            </table>
            @break

        @default
            <p>Rapport non disponible.</p>
    @endswitch
</body>
</html>
