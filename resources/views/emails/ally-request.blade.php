<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Solicitud de aliado</title>
</head>

<body style="margin:0;padding:0;background:#f8f9fa;font-family:Arial,Helvetica,sans-serif;color:#1e293b;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f8f9fa;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="620" cellpadding="0" cellspacing="0"
                    style="max-width:620px;width:100%;background:#ffffff;border-radius:16px;overflow:hidden;border:1px solid #e2e8f0;">
                    <tr>
                        <td style="background:linear-gradient(135deg,#A601B3 0%,#3004E1 100%);padding:22px 24px;">
                            <p style="margin:0;color:#ffffff;font-size:12px;letter-spacing:1.5px;text-transform:uppercase;">
                                Rumbero Extremo · Ventas</p>
                            <h1 style="margin:6px 0 0;color:#ffffff;font-size:21px;">
                                {{ $kind === 'afiliado' ? 'Nueva solicitud de afiliado' : 'Nueva solicitud de local' }}
                            </h1>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:24px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                style="font-size:14px;line-height:1.6;">
                                @foreach ($data as $label => $value)
                                    @php
                                        $isMessage = in_array($label, ['Mensaje', 'message', 'message_content'], true);
                                    @endphp
                                    <tr>
                                        <td style="padding:7px 0;width:150px;color:#64748b;font-size:12px;text-transform:uppercase;letter-spacing:.5px;vertical-align:top;">
                                            {{ $label }}</td>
                                        <td style="padding:7px 0;color:#1e293b;font-weight:600;white-space:pre-line;">
                                            {{ is_scalar($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE) }}</td>
                                    </tr>
                                @endforeach
                            </table>

                            <p style="margin:22px 0 0;">
                                <a href="{{ route('admin.requests.index') }}"
                                    style="display:inline-block;padding:12px 22px;border-radius:10px;background:linear-gradient(135deg,#A601B3,#3004E1);color:#ffffff;text-decoration:none;font-weight:bold;font-size:14px;">
                                    Revisar en el panel
                                </a>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:16px 24px;background:#f8fafc;border-top:1px solid #e2e8f0;">
                            <p style="margin:0;font-size:11px;color:#94a3b8;line-height:1.5;">
                                Aviso automático de la web de Rumbero Extremo. Puedes cambiar el correo de ventas
                                desde Configuración → Soporte del chat → Correo de ventas.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
