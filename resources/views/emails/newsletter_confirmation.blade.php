@component('mail::layout')
    {{-- Aquí puedes poner el header del correo --}}
    <div class="email-header">
        <h1>¡Hola!</h1>
    </div>

    {{-- El contenido principal del correo --}}
    @slot('body')
        <div class="email-content">
            <p>¡Gracias por suscribirte a nuestro Newsletter en Rumbero Extremo!</p>
            <p>Estás oficialmente dentro para recibir las últimas noticias, eventos exclusivos, promociones y todo lo relacionado con la rumba en Venezuela.</p>
            <p>Mantente atento a tu bandeja de entrada para no perderte nada.</p>
            @component('mail::button', ['url' => url('/'), 'class' => 'button'])
                ¡Visita Rumbero Extremo!
            @endcomponent
            <p>¡Nos vemos en la rumba!</p>
            <p>Saludos,<br>El equipo de Rumbero Extremo</p>
        </div>
    @endslot

    {{-- El footer del correo --}}
    @slot('footer')
        <div class="email-footer">
            <p>&copy; {{ date('Y') }} Rumbero Extremo. Todos los derechos reservados.</p>
            <p><a href="{{ url('/privacy') }}">Política de Privacidad</a> | <a href="{{ url('/terms') }}">Términos y Condiciones</a></p>
        </div>
    @endslot
@endcomponent