<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Facture {{ $invoiceNumber }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.4;
        }

        .container {
            padding: 30px;
        }

        /* Header */
        .header {
            border-bottom: 3px solid #4F46E5;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .header-content {
            display: table;
            width: 100%;
        }

        .company-info {
            display: table-cell;
            width: 60%;
            vertical-align: top;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #4F46E5;
            margin-bottom: 5px;
        }

        .company-details {
            color: #666;
            font-size: 11px;
        }

        .invoice-info {
            display: table-cell;
            width: 40%;
            text-align: right;
            vertical-align: top;
        }

        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #4F46E5;
            margin-bottom: 10px;
        }

        .invoice-number {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .invoice-date {
            color: #666;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 10px;
        }

        .status-completed {
            background-color: #DEF7EC;
            color: #03543F;
        }

        .status-cancelled {
            background-color: #FDE8E8;
            color: #9B1C1C;
        }

        /* Customer & Seller Info */
        .parties {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }

        .party-box {
            display: table-cell;
            width: 48%;
            vertical-align: top;
            padding: 15px;
            background-color: #F9FAFB;
            border-radius: 8px;
        }

        .party-box:first-child {
            margin-right: 4%;
        }

        .party-title {
            font-size: 10px;
            text-transform: uppercase;
            color: #6B7280;
            margin-bottom: 8px;
            font-weight: bold;
        }

        .party-name {
            font-size: 14px;
            font-weight: bold;
            color: #111827;
            margin-bottom: 5px;
        }

        .party-details {
            color: #4B5563;
            font-size: 11px;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .items-table th {
            background-color: #4F46E5;
            color: white;
            padding: 12px 10px;
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
        }

        .items-table th:first-child {
            border-radius: 8px 0 0 0;
        }

        .items-table th:last-child {
            border-radius: 0 8px 0 0;
            text-align: right;
        }

        .items-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #E5E7EB;
        }

        .items-table tr:last-child td {
            border-bottom: none;
        }

        .items-table .text-right {
            text-align: right;
        }

        .items-table .text-center {
            text-align: center;
        }

        .product-name {
            font-weight: bold;
            color: #111827;
        }

        .product-sku {
            font-size: 10px;
            color: #6B7280;
        }

        .discount-badge {
            display: inline-block;
            background-color: #FEF3C7;
            color: #92400E;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 10px;
            margin-left: 5px;
        }

        /* Totals */
        .totals-section {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }

        .totals-spacer {
            display: table-cell;
            width: 60%;
        }

        .totals-box {
            display: table-cell;
            width: 40%;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 8px 10px;
        }

        .totals-table .label {
            color: #6B7280;
        }

        .totals-table .value {
            text-align: right;
            font-weight: bold;
        }

        .totals-table .total-row td {
            background-color: #4F46E5;
            color: white;
            font-size: 16px;
            padding: 12px 10px;
        }

        .totals-table .total-row td:first-child {
            border-radius: 8px 0 0 8px;
        }

        .totals-table .total-row td:last-child {
            border-radius: 0 8px 8px 0;
        }

        /* Payment Info */
        .payment-info {
            background-color: #F0FDF4;
            border: 1px solid #86EFAC;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 30px;
        }

        .payment-title {
            font-weight: bold;
            color: #166534;
            margin-bottom: 5px;
        }

        .payment-method {
            color: #15803D;
        }

        /* Footer */
        .footer {
            border-top: 1px solid #E5E7EB;
            padding-top: 20px;
            text-align: center;
            color: #6B7280;
            font-size: 10px;
        }

        .footer-thanks {
            font-size: 14px;
            font-weight: bold;
            color: #4F46E5;
            margin-bottom: 10px;
        }

        /* Cancelled Overlay */
        .cancelled-watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            font-weight: bold;
            color: rgba(239, 68, 68, 0.15);
            text-transform: uppercase;
            z-index: -1;
        }
    </style>
</head>
<body>
    @if($sale->status === 'cancelled')
    <div class="cancelled-watermark">ANNULEE</div>
    @endif

    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-content">
                <div class="company-info">
                    <div class="company-name">Boutique Electronique</div>
                    <div class="company-details">
                        Vente de materiel electronique<br>
                        Tel: +XXX XX XX XX XX<br>
                        Email: contact@boutique-elec.com
                    </div>
                </div>
                <div class="invoice-info">
                    <div class="invoice-title">FACTURE</div>
                    <div class="invoice-number">{{ $invoiceNumber }}</div>
                    <div class="invoice-date">{{ $sale->created_at->format('d/m/Y H:i') }}</div>
                    <span class="status-badge {{ $sale->status === 'cancelled' ? 'status-cancelled' : 'status-completed' }}">
                        {{ $sale->status === 'cancelled' ? 'Annulee' : 'Payee' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Customer & Seller -->
        <div class="parties">
            <div class="party-box">
                <div class="party-title">Client</div>
                @if($sale->customer)
                    <div class="party-name">{{ $sale->customer->name }}</div>
                    <div class="party-details">
                        @if($sale->customer->phone)
                            Tel: {{ $sale->customer->phone }}<br>
                        @endif
                        @if($sale->customer->email)
                            Email: {{ $sale->customer->email }}<br>
                        @endif
                        @if($sale->customer->address)
                            {{ $sale->customer->address }}
                        @endif
                    </div>
                @else
                    <div class="party-name">Client anonyme</div>
                    <div class="party-details">Vente au comptoir</div>
                @endif
            </div>
            <div style="display: table-cell; width: 4%;"></div>
            <div class="party-box">
                <div class="party-title">Vendeur</div>
                <div class="party-name">{{ $sale->user->name }}</div>
                <div class="party-details">
                    {{ ucfirst($sale->user->role) }}<br>
                    {{ $sale->user->email }}
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 40%;">Produit</th>
                    <th style="width: 15%;" class="text-center">Prix unit.</th>
                    <th style="width: 10%;" class="text-center">Qte</th>
                    <th style="width: 15%;" class="text-center">Remise</th>
                    <th style="width: 20%;" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $item)
                <tr>
                    <td>
                        <div class="product-name">
                            {{ $item->product->name ?? 'Produit supprime' }}
                        </div>
                        @if($item->product)
                            <div class="product-sku">SKU: {{ $item->product->sku }}</div>
                        @endif
                    </td>
                    <td class="text-center">{{ number_format($item->unit_price, 0, ',', ' ') }} F</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-center">
                        @if($item->discount > 0)
                            <span class="discount-badge">-{{ number_format($item->discount, 0, ',', ' ') }} F</span>
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-right">{{ number_format(($item->unit_price * $item->quantity) - $item->discount, 0, ',', ' ') }} F</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-section">
            <div class="totals-spacer"></div>
            <div class="totals-box">
                <table class="totals-table">
                    <tr>
                        <td class="label">Sous-total</td>
                        <td class="value">{{ number_format($sale->total + $sale->discount, 0, ',', ' ') }} F</td>
                    </tr>
                    @if($sale->discount > 0)
                    <tr>
                        <td class="label">Remises</td>
                        <td class="value" style="color: #DC2626;">-{{ number_format($sale->discount, 0, ',', ' ') }} F</td>
                    </tr>
                    @endif
                    <tr class="total-row">
                        <td>TOTAL</td>
                        <td class="value">{{ number_format($sale->total, 0, ',', ' ') }} F</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Payment Info -->
        <div class="payment-info">
            <div class="payment-title">Mode de paiement</div>
            <div class="payment-method">
                @php
                    $paymentLabels = [
                        'especes' => 'Especes',
                        'mobile_money' => 'Mobile Money',
                        'carte' => 'Carte bancaire',
                    ];
                @endphp
                {{ $paymentLabels[$sale->payment_method] ?? $sale->payment_method }}
            </div>
        </div>

        @if($sale->status === 'cancelled')
        <div style="background-color: #FEE2E2; border: 1px solid #FECACA; border-radius: 8px; padding: 15px; margin-bottom: 30px;">
            <div style="font-weight: bold; color: #991B1B; margin-bottom: 5px;">Vente annulee</div>
            <div style="color: #B91C1C; font-size: 11px;">
                <strong>Motif :</strong> {{ $sale->cancel_reason ?? 'Non specifie' }}<br>
                @if($sale->cancelledBy)
                    <strong>Annulee par :</strong> {{ $sale->cancelledBy->name }}<br>
                @endif
                @if($sale->cancelled_at)
                    <strong>Date :</strong> {{ $sale->cancelled_at->format('d/m/Y H:i') }}
                @endif
            </div>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <div class="footer-thanks">Merci pour votre achat !</div>
            <div>
                Boutique Electronique - Facture generee le {{ now()->format('d/m/Y H:i') }}<br>
                Cette facture fait foi de preuve d'achat.
            </div>
        </div>
    </div>
</body>
</html>
