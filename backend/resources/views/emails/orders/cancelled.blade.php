<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tu pedido #{{ $order->order_number }} ha sido cancelado</title>
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
                            <h1 style="color: #ef4444; margin: 0; font-size: 28px; font-weight: 800; letter-spacing: -0.025em;">Compra Saludable</h1>
                            <p style="color: #6b7280; margin: 8px 0 0 0; font-size: 14px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em;">Aviso de Cancelación</p>
                        </td>
                    </tr>
                    
                    <!-- MAIN MESSAGE -->
                    <tr>
                        <td style="padding: 40px;">
                            <h2 style="color: #ef4444; margin: 0 0 20px 0; font-size: 24px; font-weight: 700; text-align: center;">Hola, {{ $order->shipping_name ?? ($order->user ? $order->user->name : 'Cliente') }}</h2>
                            <p style="margin: 0 0 20px; font-size: 16px; line-height: 24px; color: #4b5563; text-align: center;">
                                Te informamos que tu pedido <strong style="color: #111827;">#{{ $order->order_number }}</strong> ha sido cancelado.
                            </p>
                            <p style="margin: 0 0 30px; font-size: 15px; line-height: 24px; color: #4b5563; text-align: center;">
                                Si realizaste algún pago, el reembolso se procesará de acuerdo con nuestras políticas. Si tienes dudas, contáctanos respondiendo a este correo.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
