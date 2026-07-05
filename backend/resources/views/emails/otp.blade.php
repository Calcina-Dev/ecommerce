<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tu código de acceso - Compra Saludable</title>
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
                            <p style="color: #d1fae5; margin: 6px 0 0 0; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em;">Código de Seguridad OTP</p>
                        </td>
                    </tr>
                    
                    <!-- MAIN MESSAGE -->
                    <tr>
                        <td style="padding: 40px 36px;">
                            <h2 style="color: #111827; margin: 0 0 16px 0; font-size: 22px; font-weight: 700; text-align: center;">¡Hola! Aquí está tu código de acceso</h2>
                            <p style="margin: 0 0 28px; font-size: 15px; line-height: 24px; color: #4b5563; text-align: center;">
                                Usa el siguiente código de verificación de 6 dígitos para iniciar sesión en tu cuenta de <strong style="color: #059669;">Compra Saludable</strong> de forma rápida y segura:
                            </p>
                            
                            <!-- CODE BLOCK (MERCADO LIBRE STYLE) -->
                            <div style="background-color: #f8fafc; border: 2px dashed #10b981; border-radius: 12px; padding: 24px 16px; margin: 0 auto 32px; text-align: center; max-width: 380px;">
                                <span style="display: block; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">Tu código de verificación</span>
                                <div style="font-family: 'Courier New', Courier, monospace; font-size: 36px; font-weight: 800; color: #059669; letter-spacing: 0.25em; text-indent: 0.25em; margin: 0;">
                                    {{ $code }}
                                </div>
                            </div>
                            
                            <!-- SECURITY ALERT -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 8px; padding: 16px; margin-bottom: 28px;">
                                <tr>
                                    <td style="font-size: 13px; line-height: 20px; color: #92400e;">
                                        <strong>⚠️ Aviso de seguridad:</strong> Este código caduca en <strong style="color: #b45309;">5 minutos</strong> y es de un solo uso. Por tu seguridad, no compartas este código con nadie ni con personal de soporte.
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0; font-size: 13px; line-height: 20px; color: #9ca3af; text-align: center;">
                                Si no intentaste iniciar sesión ni solicitaste este código, puedes ignorar este correo tranquilamente. Tu cuenta sigue estando protegida.
                            </p>
                        </td>
                    </tr>
                    
                    <!-- FOOTER -->
                    <tr>
                        <td align="center" style="background-color: #f8fafc; padding: 24px; border-top: 1px solid #e2e8f0;">
                            <p style="margin: 0 0 6px; font-size: 14px; font-weight: 700; color: #334155;">Compra Saludable</p>
                            <p style="margin: 0; font-size: 12px; color: #64748b;">
                                &copy; {{ date('Y') }} Compra Saludable. Todos los derechos reservados.<br>
                                Tu tienda de confianza en productos naturales y bienestar.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
