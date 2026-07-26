<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Lead - Compra Saludable</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f3f4f6; padding: 40px 20px;">
        <tr>
            <td align="center">
                <!-- Wrapper Principal -->
                <table border="0" cellpadding="0" cellspacing="0" width="540" style="background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.04); border: 1px solid #e5e7eb;">
                    
                    <!-- HEADER PREMIO -->
                    <tr>
                        <td align="center" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 36px 20px;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 26px; font-weight: 800; letter-spacing: -0.025em;">Compra Saludable</h1>
                            <p style="color: #d1fae5; margin: 6px 0 0 0; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em;">Nuevo Lead de Compra por Llamada</p>
                        </td>
                    </tr>
                    
                    <!-- MAIN MESSAGE -->
                    <tr>
                        <td style="padding: 40px 36px;">
                            <h2 style="color: #111827; margin: 0 0 16px 0; font-size: 22px; font-weight: 700; text-align: center;">¡Tienes un nuevo lead!</h2>
                            <p style="margin: 0 0 28px; font-size: 15px; line-height: 24px; color: #4b5563; text-align: center;">
                                Un cliente ha solicitado continuar su compra a través de una llamada telefónica en <strong style="color: #059669;">Compra Saludable</strong>.
                            </p>
                            
                            <!-- DATA BLOCK -->
                            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; margin: 0 auto 32px;">
                                <p style="margin: 0 0 12px; font-size: 15px; color: #334155;">
                                    <strong>Nombre:</strong> {{ $lead->name }}
                                </p>
                                <p style="margin: 0 0 12px; font-size: 15px; color: #334155;">
                                    <strong>Teléfono:</strong> <a href="tel:{{ $lead->phone }}" style="color: #059669; text-decoration: none;">{{ $lead->phone }}</a>
                                </p>
                                <p style="margin: 0 0 12px; font-size: 15px; color: #334155;">
                                    <strong>Producto:</strong> {{ $lead->product_name ?? 'N/A' }}
                                </p>
                                <p style="margin: 0; font-size: 15px; color: #334155;">
                                    <strong>ID Producto:</strong> {{ $lead->product_id ?? 'N/A' }}
                                </p>
                            </div>

                            <div style="text-align: center; margin-bottom: 28px;">
                                <a href="tel:{{ $lead->phone }}" style="display: inline-block; background-color: #10b981; color: #ffffff; text-decoration: none; font-size: 16px; font-weight: 600; padding: 12px 32px; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.4);">
                                    Llamar ahora
                                </a>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- FOOTER -->
                    <tr>
                        <td align="center" style="background-color: #f8fafc; padding: 24px; border-top: 1px solid #e2e8f0;">
                            <p style="margin: 0 0 6px; font-size: 14px; font-weight: 700; color: #334155;">Compra Saludable</p>
                            <p style="margin: 0; font-size: 12px; color: #64748b;">
                                &copy; {{ date('Y') }} Compra Saludable. Todos los derechos reservados.<br>
                                Sistema automatizado de Leads.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
