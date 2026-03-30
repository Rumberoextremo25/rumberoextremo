@component('mail::message')
<div style="text-align: center; border-bottom: 3px solid #A601B3; padding-bottom: 15px; margin-bottom: 20px;">
    <h1 style="color: #3004E1; margin: 0;">Rumbero Extremo</h1>
    <p style="color: #A601B3; margin: 5px 0 0 0;">¡Bienvenido a la familia!</p>
</div>

<p style="font-size: 16px;">Hola <strong style="color: #3004E1;">{{ $subscriberEmail }}</strong>,</p>

<p>¡Gracias por suscribirte a nuestro newsletter! 🎉</p>

<p>Prepárate para recibir lo mejor del entretenimiento en Venezuela:</p>

<ul style="margin: 20px 0; padding-left: 20px;">
    <li style="margin-bottom: 10px;">🎉 Eventos exclusivos y fiestas</li>
    <li style="margin-bottom: 10px;">🏷️ Promociones y descuentos</li>
    <li style="margin-bottom: 10px;">📰 Noticias del mundo del entretenimiento</li>
    <li style="margin-bottom: 10px;">⭐ Los mejores sitios en tendencia</li>
</ul>

@component('mail::button', ['url' => url('/'), 'color' => 'success'])
Visitar Rumbero Extremo
@endcomponent

<div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0; text-align: center;">
    <p style="margin: 0;">
        <a href="https://facebook.com/rumberoextremo" style="color: #3004E1; text-decoration: none;">Facebook</a> 
        | 
        <a href="https://instagram.com/rumberoextremo" style="color: #3004E1; text-decoration: none;">Instagram</a>
    </p>
    <p style="color: #999; font-size: 12px; margin-top: 15px;">
        © {{ date('Y') }} Rumbero Extremo. Todos los derechos reservados.
    </p>
</div>
@endcomponent