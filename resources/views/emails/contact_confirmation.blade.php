@component('mail::layout')
    {{-- Header del correo --}}
    @slot('header')
        <div class="email-header">
            <h1>¡Hola {{ $contactData['name'] ?? '!' }}</h1> {{-- Usar null coalescing para seguridad --}}
        </div>
    @endslot

    {{-- Contenido principal del correo --}}
    @slot('body')
        <div class="email-content">
            <p>Hemos recibido tu mensaje en Rumbero Extremo. Agradecemos que te hayas puesto en contacto con nosotros.</p>

            <div class="message-summary">
                <p><strong>Asunto:</strong> {{ $contactData['subject'] ?? 'N/A' }}</p>
                <p><strong>Mensaje:</strong></p>
                <p>{{ $contactData['message_content'] }}</p>
            </div>

            <p>Nos pondremos en contacto contigo lo antes posible.</p>

            <p>¡Gracias por ser parte de la rumba!</p>
        </div>
    @endslot

    {{-- Footer del correo --}}
    @slot('footer')
        <div class="email-footer">
            <p>&copy; {{ date('Y') }} Rumbero Extremo. Todos los derechos reservados.</p>
            <p><a href="{{ url('/privacy') }}">Política de Privacidad</a> | <a href="{{ url('/terms') }}">Términos y Condiciones</a></p>
        </div>
    @endslot
@endcomponent