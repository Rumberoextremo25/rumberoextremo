<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Gracias por registrarte</title>
</head>

<body style="margin:0;padding:0;background:#f6f4fb;font-family:Arial,Helvetica,sans-serif;color:#1e293b;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f6f4fb;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0"
                    style="max-width:600px;width:100%;background:#ffffff;border-radius:18px;overflow:hidden;border:1px solid #ece7f7;">
                    <tr>
                        <td
                            style="background:linear-gradient(135deg,#A601B3 0%,#3004E1 100%);padding:26px 26px;text-align:center;">
                            <p
                                style="margin:0 0 6px;color:#ffffff;font-size:11px;letter-spacing:2px;text-transform:uppercase;opacity:.85;">
                                Rumbero Extremo</p>
                            <h1 style="margin:0;color:#ffffff;font-size:22px;">¡Gracias por registrarte!</h1>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:26px;">
                            <p style="margin:0 0 14px;font-size:15px;line-height:1.6;">
                                Hola <strong>{{ $name }}</strong>,
                            </p>

                            <p style="margin:0 0 14px;font-size:15px;line-height:1.6;">
                                Recibimos tu registro como <strong>aliado comercial</strong>. Tu solicitud ya está en
                                la bandeja de nuestro equipo de <strong>ventas</strong>, que la revisará y se pondrá en
                                contacto contigo para coordinar los siguientes pasos.
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                style="background:#f8f6fd;border:1px solid #ece7f7;border-radius:12px;margin:0 0 18px;">
                                <tr>
                                    <td style="padding:16px 18px;">
                                        <p style="margin:0 0 8px;font-size:13px;font-weight:bold;color:#1e293b;">¿Qué
                                            pasa ahora?</p>
                                        <p style="margin:0;font-size:13px;line-height:1.6;color:#475569;">
                                            1. Ventas revisa tu solicitud.<br>
                                            2. Te contactamos por correo o teléfono.<br>
                                            3. Al aprobarla, creado tu perfil de aliado y tu acceso a la plataforma.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 18px;font-size:14px;line-height:1.6;color:#475569;">
                                Si necesitas adelantar información, puedes escribirnos a
                                <a href="mailto:rumberoextremo@gmail.com"
                                    style="color:#A601B3;font-weight:bold;">rumberoextremo@gmail.com</a>.
                            </p>

                            <p style="margin:0;">
                                <a href="{{ url('/') }}"
                                    style="display:inline-block;padding:12px 22px;border-radius:10px;background:linear-gradient(135deg,#A601B3,#3004E1);color:#ffffff;text-decoration:none;font-weight:bold;font-size:14px;">
                                    Visitar Rumbero Extremo
                                </a>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:16px 26px;background:#f8fafc;border-top:1px solid #ece7f7;">
                            <p style="margin:0;font-size:11px;color:#94a3b8;line-height:1.5;">
                                Recibes este correo porque te registraste en Rumbero Extremo. Si no fuiste tú,
                                ignóralo o escríbenos.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
