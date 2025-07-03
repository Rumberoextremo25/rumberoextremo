@component('mail::message')
# ¡Hola {{ $contactData['name'] }}!

Hemos recibido tu mensaje en Rumbero Extremo. Agradecemos que te hayas puesto en contacto con nosotros.

Aquí está un resumen de tu mensaje:

**Asunto:** {{ $contactData['subject'] ?? 'N/A' }}
**Mensaje:**
{{ $contactData['message_content'] }}

Nos pondremos en contacto contigo lo antes posible.

¡Gracias por ser parte de la rumba!

Saludos,
El equipo de Rumbero Extremo
@endcomponent