<!DOCTYPE html>
<html>
<head>
    <title>Tu pedido está en camino</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
        <h2 style="color: #4CAF50; text-align: center;">¡Buenas noticias, {{ $order->shipping_name }}!</h2>
        <p>Tu pedido <strong>#{{ $order->order_number }}</strong> ha sido marcado como enviado y está en camino hacia ti.</p>
        
        <div style="background: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h3 style="margin-top: 0; color: #4CAF50;">Detalles de Envío</h3>
            <p><strong>Dirección:</strong> {{ $order->shipping_address }}, {{ $order->shipping_city }}</p>
            
            @if($order->shippingMethod)
                <p><strong>Método de Envío:</strong> {{ $order->shippingMethod->name }}</p>
            @endif

            @if($order->tracking_code)
                <p><strong>Código de Seguimiento (Tracking):</strong> <span style="background: #e0f2f1; padding: 5px 10px; border-radius: 4px; font-weight: bold; letter-spacing: 1px;">{{ $order->tracking_code }}</span></p>
            @endif
        </div>

        <p>Si tienes alguna duda sobre el estado de tu pedido, no dudes en contactarnos respondiendo a este correo o mediante nuestro WhatsApp.</p>

        <hr style="border: 0; border-top: 1px solid #eee; margin: 30px 0;" />
        <p style="text-align: center; color: #888; font-size: 12px;">
            Gracias por confiar en Compra Saludable.<br>
            Tu bienestar, nuestra prioridad.
        </p>
    </div>
</body>
</html>
