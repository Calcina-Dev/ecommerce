<style>
    .ios-report-container {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        background-color: #f2f2f7;
        padding: 20px;
        border-radius: 20px;
        color: #1c1c1e;
    }
    .dark .ios-report-container {
        background-color: #000000;
        color: #f2f2f7;
    }
    .ios-card {
        background-color: #ffffff;
        border-radius: 16px;
        padding: 16px;
        margin-bottom: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .dark .ios-card {
        background-color: #1c1c1e;
        box-shadow: none;
    }
    .ios-header {
        text-align: center;
        margin-bottom: 24px;
    }
    .ios-title {
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #8e8e93;
        font-weight: 600;
        margin-bottom: 4px;
    }
    .ios-amount {
        font-size: 42px;
        font-weight: 700;
        letter-spacing: -1px;
        margin: 0;
        line-height: 1;
    }
    .ios-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 0.5px solid #e5e5ea;
    }
    .dark .ios-row {
        border-bottom: 0.5px solid #38383a;
    }
    .ios-row:last-child {
        border-bottom: none;
    }
    .ios-label {
        font-size: 16px;
        color: #8e8e93;
    }
    .ios-value {
        font-size: 16px;
        font-weight: 500;
    }
    .ios-value.success { color: #34c759; }
    .ios-value.danger { color: #ff3b30; }
    .ios-value.warning { color: #ff9500; }
    
    .ios-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 100px;
        font-size: 13px;
        font-weight: 600;
    }
    .ios-badge.gray { background-color: #e5e5ea; color: #8e8e93; }
    .ios-badge.green { background-color: #e3f5e1; color: #34c759; }
    .dark .ios-badge.gray { background-color: #38383a; color: #aeaeb2; }
    .dark .ios-badge.green { background-color: #1c3320; color: #32d74b; }
    
    .ios-section-title {
        font-size: 20px;
        font-weight: 700;
        margin-bottom: 12px;
        padding-top: 8px;
    }
    
    .ios-footer-note {
        text-align: center;
        font-size: 13px;
        color: #8e8e93;
        margin-top: 16px;
    }
</style>

@php
    $totalVentas = 0;
    $efectivoVentas = 0;
    foreach($paymentsBreakdown as $payment) {
        $totalVentas += $payment->total;
        if (strtolower($payment->method_name) === 'efectivo') {
            $efectivoVentas = $payment->total;
        }
    }
    $efectivoEsperado = $record->opening_balance + $efectivoVentas;
@endphp

<div class="ios-report-container">
    
    <!-- Encabezado Estilo Billetera -->
    <div class="ios-header">
        <div class="ios-title">Recaudación Total del Turno</div>
        <h1 class="ios-amount">S/ {{ number_format($totalVentas, 2) }}</h1>
        <div style="margin-top: 12px;">
            <span class="ios-badge {{ $record->status === 'closed' ? 'gray' : 'green' }}">
                {{ $record->status === 'closed' ? 'Turno Cerrado' : 'Turno Abierto' }}
            </span>
        </div>
    </div>

    <!-- Tarjeta de Detalles del Turno -->
    <div class="ios-card">
        <div class="ios-row">
            <span class="ios-label">Cajero Responsable</span>
            <span class="ios-value">{{ $record->user->name }}</span>
        </div>
        <div class="ios-row">
            <span class="ios-label">Apertura</span>
            <span class="ios-value">{{ $record->opened_at->format('d M Y, h:i A') }}</span>
        </div>
        @if($record->closed_at)
        <div class="ios-row">
            <span class="ios-label">Cierre</span>
            <span class="ios-value">{{ $record->closed_at->format('d M Y, h:i A') }}</span>
        </div>
        @endif
    </div>

    <!-- Tarjeta de Desglose de Ventas -->
    <h2 class="ios-section-title">Medios de Pago</h2>
    <div class="ios-card">
        @forelse($paymentsBreakdown as $payment)
            <div class="ios-row">
                <span class="ios-label">{{ $payment->method_name }}</span>
                <span class="ios-value">S/ {{ number_format($payment->total, 2) }}</span>
            </div>
        @empty
            <div class="ios-row" style="justify-content: center;">
                <span class="ios-label" style="font-style: italic;">Sin ventas registradas</span>
            </div>
        @endforelse
    </div>

    <!-- Tarjeta de Cuadre de Efectivo -->
    <h2 class="ios-section-title">Control de Efectivo (Cajón)</h2>
    <div class="ios-card">
        <div class="ios-row">
            <span class="ios-label">Fondo Inicial</span>
            <span class="ios-value">S/ {{ number_format($record->opening_balance, 2) }}</span>
        </div>
        <div class="ios-row">
            <span class="ios-label">Ventas en Efectivo</span>
            <span class="ios-value">S/ {{ number_format($efectivoVentas, 2) }}</span>
        </div>
        <div class="ios-row" style="background-color: rgba(0,0,0,0.02); margin: 0 -16px; padding: 12px 16px;">
            <span class="ios-label" style="font-weight: 600; color: inherit;">Efectivo Esperado</span>
            <span class="ios-value" style="font-weight: 700; font-size: 18px;">S/ {{ number_format($efectivoEsperado, 2) }}</span>
        </div>
        
        @if($record->status === 'closed')
            @php
                $diferencia = $record->closing_balance - $efectivoEsperado;
            @endphp
            <div class="ios-row" style="margin-top: 8px;">
                <span class="ios-label">Efectivo Físico Contado</span>
                <span class="ios-value">S/ {{ number_format($record->closing_balance, 2) }}</span>
            </div>
            <div class="ios-row" style="border-bottom: none;">
                <span class="ios-label" style="font-weight: 600;">Resultado del Cuadre</span>
                <span class="ios-value" style="font-weight: 700; font-size: 18px; {{ $diferencia < 0 ? 'color: #ff3b30;' : ($diferencia > 0 ? 'color: #34c759;' : 'color: #8e8e93;') }}">
                    @if($diferencia < 0)
                        Faltante (S/ {{ number_format(abs($diferencia), 2) }})
                    @elseif($diferencia > 0)
                        Sobrante (S/ {{ number_format($diferencia, 2) }})
                    @else
                        Cuadre Perfecto
                    @endif
                </span>
            </div>
        @endif
    </div>
    
    @if($record->status === 'open')
        <div class="ios-footer-note">
            El cuadre final se realizará cuando se cierre el turno.
        </div>
    @endif
</div>
