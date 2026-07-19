<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        * { box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #111827;
            font-size: 12px;
            margin: 0;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 1px;
            color: #111827;
            margin: 0 0 4px;
        }

        .subtitle {
            color: #6b7280;
            font-size: 11px;
            margin: 0 0 24px;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 28px;
        }

        .meta-table td {
            vertical-align: top;
            padding: 0;
        }

        .meta-label {
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.5px;
            color: #9ca3af;
            margin-bottom: 4px;
        }

        .meta-value {
            font-size: 12px;
            color: #111827;
            line-height: 1.5;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }

        table.items thead th {
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #9ca3af;
            border-bottom: 2px solid #111827;
            padding: 0 4px 8px;
        }

        table.items thead th.numeric,
        table.items tbody td.numeric {
            text-align: right;
        }

        table.items tbody td {
            padding: 10px 4px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 12px;
        }

        table.items tbody .product-name {
            font-weight: bold;
        }

        table.items tbody .variant-name {
            color: #6b7280;
            font-size: 10px;
        }

        .totals {
            width: 100%;
            margin-bottom: 28px;
        }

        .totals table {
            margin-left: auto;
            width: 260px;
            border-collapse: collapse;
        }

        .totals td {
            padding: 4px 0;
            font-size: 12px;
        }

        .totals td.label {
            color: #6b7280;
        }

        .totals td.value {
            text-align: right;
        }

        .totals tr.grand-total td {
            border-top: 2px solid #111827;
            padding-top: 10px;
            font-size: 15px;
            font-weight: bold;
        }

        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 4px;
            background: #f3f4f6;
            color: #374151;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <p class="title">FACTURE</p>
    <p class="subtitle">Commande #{{ $order->id }} — {{ $order->created_at->translatedFormat('d F Y à H:i') }}</p>

    <table class="meta-table">
        <tr>
            <td style="width: 33%;">
                <div class="meta-label">Facturé par</div>
                <div class="meta-value">
                    {{ $merchant->name }}<br>
                    @if ($merchant->whatsapp_number)
                        {{ $merchant->whatsapp_number }}
                    @endif
                </div>
            </td>
            <td style="width: 34%;">
                <div class="meta-label">Client</div>
                <div class="meta-value">
                    {{ $order->customer?->name ?? 'Client' }}<br>
                    {{ $order->customer?->whatsapp_number }}
                </div>
            </td>
            <td style="width: 33%;">
                <div class="meta-label">Livraison</div>
                <div class="meta-value">
                    @if ($order->delivery_address_text || $order->delivery_city)
                        {{ trim(($order->delivery_address_text ?? '').' '.($order->delivery_city ?? '')) }}
                    @else
                        Non renseignée
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Article</th>
                <th class="numeric">Qté</th>
                <th class="numeric">Prix unitaire</th>
                <th class="numeric">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>
                        <div class="product-name">{{ $item->product_name_snapshot }}</div>
                        @if ($item->variant_name_snapshot)
                            <div class="variant-name">{{ $item->variant_name_snapshot }}</div>
                        @endif
                    </td>
                    <td class="numeric">{{ $item->quantity }}</td>
                    <td class="numeric">{{ number_format((float) $item->unit_price, 2) }} {{ $merchant->currency->value }}</td>
                    <td class="numeric">{{ number_format((float) $item->line_total, 2) }} {{ $merchant->currency->value }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <td class="label">Sous-total</td>
                <td class="value">{{ number_format((float) $order->subtotal, 2) }} {{ $merchant->currency->value }}</td>
            </tr>
            <tr>
                <td class="label">Livraison</td>
                <td class="value">{{ number_format((float) $order->delivery_fee, 2) }} {{ $merchant->currency->value }}</td>
            </tr>
            <tr class="grand-total">
                <td class="label">Total</td>
                <td class="value">{{ number_format((float) $order->total, 2) }} {{ $merchant->currency->value }}</td>
            </tr>
        </table>
    </div>

    <table class="meta-table">
        <tr>
            <td style="width: 50%;">
                <div class="meta-label">Moyen de paiement</div>
                <div class="meta-value">{{ $order->payment_method?->label() ?? 'Non renseigné' }}</div>
            </td>
            <td style="width: 50%;">
                <div class="meta-label">Statut du paiement</div>
                <div class="meta-value"><span class="badge">{{ $order->payment_status->label() }}</span></div>
            </td>
        </tr>
    </table>
</body>
</html>
