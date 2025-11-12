@component('mail::message')
<div style="text-align: center;">
    <h1 style="color: #7700ffff;">¡Hola!</h1>
</div>

<p style="font-size: 16px; color: #000000ff;">¡Gracias por suscribirte a nuestro Boletín en Rumbero Extremo!</p>
<p style="font-size: 16px; color: #000000ff; font-weight: bold;">Estás oficialmente dentro para recibir las últimas noticias, eventos exclusivos, promociones y todo lo relacionado al entretenimiento en Venezuela.</p>
<p style="font-size: 16px; color: #333333;">Mantente atento a tu bandeja de entrada para no perderte nada.</p>

{{-- El botón ya tiene un estilo predeterminado, pero puedes agregarle un estilo de color --}}
@component('mail::button', ['url' => url('/'), 'color' => 'success']) 
    ¡Visita Rumbero Extremo!
@endcomponent

<p style="font-size: 16px; color: #7700ffff; margin-top: 20px;">¡Nos vemos en la rumba!</p>
<p style="font-size: 14px; color: #000000ff;">Saludos,<br>El equipo de Rumbero Extremo</p>

@endcomponent