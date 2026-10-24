<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Nuevo mensaje en el chat</title>
</head>

<body style="margin:0;padding:0;background:#f8f9fa;font-family:Arial,Helvetica,sans-serif;color:#1e293b;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f8f9fa;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0"
                    style="max-width:600px;width:100%;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e2e8f0;">
                    <tr>
                        <td style="background:linear-gradient(135deg,#A601B3 0%,#3004E1 100%);padding:22px 24px;">
                            <p style="margin:0;color:#ffffff;font-size:13px;letter-spacing:1.5px;text-transform:uppercase;">
                                Rumbero Extremo · Soporte</p>
                            <h1 style="margin:6px 0 0;color:#ffffff;font-size:20px;">Nuevo mensaje en el chat</h1>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:24px;">
                            <p style="margin:0 0 4px;font-size:12px;color:#64748b;text-transform:uppercase;letter-spacing:1px;">
                                De</p>
                            <p style="margin:0 0 16px;font-size:16px;font-weight:bold;color:#1e293b;">
                                {{ $userName }}
                                @if ($userEmail)
                                    <span style="font-weight:normal;color:#64748b;font-size:13px;">&lt;{{ $userEmail }}&gt;</span>
                                @endif
                            </p>

                            <p style="margin:0 0 4px;font-size:12px;color:#64748b;text-transform:uppercase;letter-spacing:1px;">
                                Mensaje</p>
                            <div
                                style="margin:0 0 20px;padding:14px 16px;background:#f8fafc;border-left:4px solid #A601B3;border-radius:8px;font-size:15px;line-height:1.55;white-space:pre-wrap;">
                                {{ $body }}
                            </div>

                            <p style="margin:0 0 20px;font-size:13px;color:#64748b;">
                                Sesión: <code style="background:#f1f5f9;padding:2px 6px;border-radius:4px;">{{ $sessionId }}</code>
                            </p>

                            @if ($chatUrl)
                                <p style="margin:0 0 10px;">
                                    <a href="{{ $chatUrl }}"
                                        style="display:inline-block;padding:12px 22px;border-radius:10px;background:linear-gradient(135deg,#A601B3,#3004E1);color:#ffffff;text-decoration:none;font-weight:bold;font-size:14px;">
                                        Responder en el Centro de Mensajes
                                    </a>
                                </p>
                            @endif

                            @if ($whatsappUrl)
                                <p style="margin:0;">
                                    <a href="{{ $whatsappUrl }}"
                                        style="color:#065f46;font-size:13px;font-weight:bold;text-decoration:none;">
                                        Abrir conversación en WhatsApp &rarr;
                                    </a>
                                </p>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:16px 24px;background:#f8fafc;border-top:1px solid #e2e8f0;">
                            <p style="margin:0;font-size:11px;color:#94a3b8;line-height:1.5;">
                                Este aviso se generó automáticamente porque el chat no pudo responder con IA
                                o el usuario pidió hablar con una persona.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
