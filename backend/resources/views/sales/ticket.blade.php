<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket de Venta</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px; /* Tamaño adecuado para 80mm */
            margin: 0;
            padding: 0;
            color: #000;
        }
        .ticket {
            width: 100%;
            padding: 5px;
        }
        .text-center {
            text-align: center;
        }
        .text-left {
            text-align: left;
        }
        .text-right {
            text-align: right;
        }
        .bold {
            font-weight: bold;
        }
        .header {
            margin-bottom: 10px;
        }
        .company-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 2px;
        }
        .separator {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th, .table td {
            padding: 3px 1px;
            vertical-align: top;
        }
        .table th {
            text-align: left;
            border-bottom: 1px dashed #000;
        }
        .totals {
            margin-top: 10px;
            width: 100%;
        }
        .totals td {
            padding: 2px 1px;
        }
        .total-amount {
            font-size: 12px;
            font-weight: bold;
        }
        .footer {
            margin-top: 15px;
            text-align: center;
            font-size: 9px;
        }
        .items-row td {
            padding-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="header text-center">
            <div class="company-name">VITAMIN OS</div>
            <div>RUC: 20123456789</div>
            <div>Av. Principal 123 - Ciudad</div>
            <div>Tel: 987 654 321</div>
            <div class="separator"></div>
            <div class="bold">{{ strtoupper($sale->document_type) }}</div>
            <div>N° {{ $sale->document_number ?? str_pad($sale->id, 8, '0', STR_PAD_LEFT) }}</div>
            <div class="separator"></div>
        </div>

        <div class="customer-info">
            <table>
                <tr>
                    <td style="width: 50px;">Fecha:</td>
                    <td>{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <td>Cliente:</td>
                    <td>{{ $sale->customer ? $sale->customer->name : 'Consumidor Final' }}</td>
                </tr>
                @if($sale->customer && $sale->customer->dni)
                <tr>
                    <td>DNI/RUC:</td>
                    <td>{{ $sale->customer->dni }}</td>
                </tr>
                @endif
                <tr>
                    <td>Cajero:</td>
                    <td>{{ $sale->user ? $sale->user->name : 'Sistema' }}</td>
                </tr>
            </table>
        </div>

        <div class="separator"></div>

        <table class="table">
            <thead>
                <tr>
                    <th>Cant</th>
                    <th>Descripción</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $item)
                <tr class="items-row">
                    <td>{{ $item->quantity }}</td>
                    <td>
                        {{ $item->product ? $item->product->name : 'Producto Desconocido' }}<br>
                        <small>S/ {{ number_format($item->price, 2) }} c/u</small>
                    </td>
                    <td class="text-right">S/ {{ number_format($item->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="separator"></div>

        <table class="totals">
            <tr>
                <td>Op. Gravada</td>
                <td class="text-right">S/ {{ number_format($sale->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td>IGV (18%)</td>
                <td class="text-right">S/ {{ number_format($sale->total_tax, 2) }}</td>
            </tr>
            <tr class="total-amount">
                <td>TOTAL</td>
                <td class="text-right">S/ {{ number_format($sale->total_amount, 2) }}</td>
            </tr>
        </table>

        <div class="separator"></div>

        <div class="payments">
            <div class="bold">Métodos de Pago:</div>
            <table class="table">
                @foreach($sale->payments as $payment)
                <tr>
                    <td>{{ $payment->paymentMethod ? $payment->paymentMethod->name : 'Otro' }}</td>
                    <td class="text-right">S/ {{ number_format($payment->amount, 2) }}</td>
                </tr>
                @endforeach
            </table>
        </div>

        <div class="separator"></div>

        <div class="footer">
            <div>¡Gracias por tu compra!</div>
            <div>Representación impresa de {{ ucfirst(strtolower($sale->document_type)) }} Electrónica</div>
            <div style="margin-top: 10px; font-style: italic;">
                -- Copia Cliente --
            </div>
        </div>
    </div>
</body>
</html>
