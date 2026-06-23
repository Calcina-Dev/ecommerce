<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Actualización de tu pedido #{{ $note->order->order_number }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f3f4f6; padding: 40px 20px;">
        <tr>
            <td align="center">
                <!-- Wrapper Principal -->
                <table border="0" cellpadding="0" cellspacing="0" width="600" style="background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);">
                    
                    <!-- HEADER PREMIO -->
                    <tr>
                        <td align="center" style="background-color: #ffffff; padding: 40px 0 30px; border-bottom: 1px solid #f3f4f6;">
                            <h1 style="color: #16a34a; margin: 0; font-size: 28px; font-weight: 800; letter-spacing: -0.025em;">Compra Saludable</h1>
                            <p style="color: #6b7280; margin: 8px 0 0 0; font-size: 14px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em;">Tu salud en buenas manos</p>
                        </td>
                    </tr>
                    
                    <!-- MAIN MESSAGE -->
                    <tr>
                        <td style="padding: 40px;">
                            <h2 style="color: #111827; margin: 0 0 20px 0; font-size: 20px; font-weight: 600;">Actualización de tu pedido #{{ $note->order->order_number }}</h2>
                            <p style="margin: 0 0 20px; font-size: 16px; line-height: 24px; color: #4b5563;">
                                Hola <strong style="color: #111827;">{{ $note->order->shipping_name ?? ($note->order->user ? $note->order->user->name : 'Cliente') }}</strong>,<br><br>
                                Nuestro equipo ha añadido una nueva nota respecto a tu compra. Por favor, lee el mensaje a continuación:
                            </p>
                            
                            <!-- NOTE BLOCK -->
                            <div style="background-color: #f8fafc; border-left: 4px solid #3b82f6; border-radius: 0 8px 8px 0; padding: 24px; margin-bottom: 32px;">
                                <p style="margin: 0; font-size: 16px; line-height: 1.6; color: #1e293b; font-style: italic;">
                                    "{!! nl2br(e($note->content)) !!}"
                                </p>
                            </div>
                            
                            <!-- BOTÓN RASTREAR -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 40px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ env('FRONTEND_URL', 'http://localhost:3000') }}/rastrear-pedido?order_id={{ $note->order->order_number }}" style="display: inline-block; background-color: #111827; color: #ffffff; padding: 14px 32px; text-decoration: none; font-weight: 600; border-radius: 8px; font-size: 16px; transition: background-color 0.2s;">
                                            Rastrear mi pedido
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- SUMMARY HEADER -->
                            <div style="border-bottom: 2px solid #f3f4f6; margin-bottom: 24px; padding-bottom: 12px;">
                                <h3 style="color: #111827; margin: 0; font-size: 16px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Resumen de la Orden</h3>
                            </div>
                            
                            <!-- ORDER ITEMS -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 24px;">
                                @if($note->order->items)
                                    @foreach($note->order->items as $item)
                                    <tr>
                                        <td style="padding: 12px 0; border-bottom: 1px solid #f3f4f6;">
                                            <p style="margin: 0; font-size: 15px; color: #111827; font-weight: 500;">{{ $item->product_name }}</p>
                                            <p style="margin: 4px 0 0; font-size: 13px; color: #6b7280;">Cant: {{ $item->quantity }} × S/ {{ number_format($item->price, 2) }}</p>
                                        </td>
                                        <td align="right" style="padding: 12px 0; border-bottom: 1px solid #f3f4f6; font-size: 15px; color: #111827; font-weight: 600;">
                                            S/ {{ number_format($item->price * $item->quantity, 2) }}
                                        </td>
                                    </tr>
                                    @endforeach
                                @endif
                            </table>
                            
                            <!-- TOTALS -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 40px;">
                                <tr>
                                    <td width="60%"></td>
                                    <td width="40%">
                                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                            <tr>
                                                <td align="left" style="padding: 8px 0; font-size: 14px; color: #6b7280;">Subtotal</td>
                                                <td align="right" style="padding: 8px 0; font-size: 14px; color: #111827; font-weight: 500;">S/ {{ number_format($note->order->items->sum(fn($i) => $i->price * $i->quantity), 2) }}</td>
                                            </tr>
                                            @if($note->order->discount_amount > 0)
                                            <tr>
                                                <td align="left" style="padding: 8px 0; font-size: 14px; color: #ef4444;">Descuento</td>
                                                <td align="right" style="padding: 8px 0; font-size: 14px; color: #ef4444; font-weight: 500;">- S/ {{ number_format($note->order->discount_amount, 2) }}</td>
                                            </tr>
                                            @endif
                                            <tr>
                                                <td align="left" style="padding: 8px 0; font-size: 14px; color: #6b7280;">Envío</td>
                                                <td align="right" style="padding: 8px 0; font-size: 14px; color: #111827; font-weight: 500;">S/ {{ number_format($note->order->shipping_cost ?? 0, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td align="left" style="padding: 16px 0 0; font-size: 16px; color: #111827; font-weight: 700; border-top: 1px solid #e5e7eb; margin-top: 8px;">Total</td>
                                                <td align="right" style="padding: 16px 0 0; font-size: 18px; color: #3b82f6; font-weight: 800; border-top: 1px solid #e5e7eb; margin-top: 8px;">S/ {{ number_format($note->order->total_amount ?? 0, 2) }}</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- INFO ADICIONAL -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f9fafb; border-radius: 8px; padding: 24px;">
                                <tr>
                                    <td width="50%" valign="top" style="padding-right: 16px;">
                                        <h4 style="margin: 0 0 12px; font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Dirección de envío</h4>
                                        <p style="margin: 0; font-size: 14px; line-height: 20px; color: #1f2937;">
                                            <strong>{{ $note->order->shipping_name }}</strong><br>
                                            {{ $note->order->shipping_address }}<br>
                                            @if($note->order->shipping_district){{ $note->order->shipping_district }}, @endif
                                            @if($note->order->shipping_province){{ $note->order->shipping_province }}<br>@endif
                                            @if($note->order->shipping_department){{ $note->order->shipping_department }}@endif
                                        </p>
                                    </td>
                                    <td width="50%" valign="top" style="padding-left: 16px; border-left: 1px solid #e5e7eb;">
                                        <h4 style="margin: 0 0 12px; font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">Información</h4>
                                        <p style="margin: 0; font-size: 14px; line-height: 20px; color: #1f2937;">
                                            <strong>Teléfono:</strong><br>{{ $note->order->shipping_phone }}<br><br>
                                            <strong>Método de pago:</strong><br>{{ ucfirst($note->order->payment_method ?? 'Tarjeta') }}
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                
                <!-- FOOTER EXTERNO -->
                <table border="0" cellpadding="0" cellspacing="0" width="600">
                    <tr>
                        <td align="center" style="padding: 32px 20px; font-size: 13px; color: #9ca3af; line-height: 20px;">
                            <p style="margin: 0 0 8px;">Este es un mensaje automático de <strong>Compra Saludable</strong>.</p>
                            <p style="margin: 0;">Si tienes alguna pregunta, responde a este correo y te ayudaremos encantados.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
