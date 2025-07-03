@extends('layouts.app')

@section('title', 'Términos y Condiciones - Rumbero Extremo App')

@section('content')
<section class="content-section">
    <div class="container policy-container"> {{-- Reutilizamos la clase policy-container para los estilos --}}
        <h1>Términos y Condiciones</h1>
        <p class="last-updated">Última actualización: 15 de junio de {{ date('Y') }}</p>

        <div class="policy-section">
            <h2>1. Aceptación de los Términos</h2>
            <p>Al acceder y utilizar la aplicación y el sitio web de Rumbero Extremo ("el Servicio"), usted acepta estar sujeto a estos Términos y Condiciones ("Términos"), todas las leyes y regulaciones aplicables, y acepta que es responsable del cumplimiento de las leyes locales aplicables. Si no está de acuerdo con alguno de estos términos, tiene prohibido usar o acceder a este sitio. Los materiales contenidos en este sitio web están protegidos por las leyes de derechos de autor y marcas comerciales aplicables.</p>
        </div>

        <div class="policy-section">
            <h2>2. Licencia de Uso</h2>
            <p>Se concede permiso para descargar temporalmente una copia de los materiales (información o software) en el sitio web de Rumbero Extremo solo para visualización transitoria personal y no comercial. Esta es la concesión de una licencia, no una transferencia de título, y bajo esta licencia usted no puede:</p>
            <ul>
                <li>Modificar o copiar los materiales.</li>
                <li>Utilizar los materiales para cualquier propósito comercial, o para cualquier exhibición pública (comercial o no comercial).</li>
                <li>Intentar descompilar o aplicar ingeniería inversa a cualquier software contenido en el sitio web de Rumbero Extremo.</li>
                <li>Eliminar cualquier derecho de autor u otras anotaciones de propiedad de los materiales.</li>
                <li>Transferir los materiales a otra persona o "reflejar" los materiales en cualquier otro servidor.</li>
            </ul>
            <p>Esta licencia terminará automáticamente si usted viola cualquiera de estas restricciones y podrá ser terminada por Rumbero Extremo en cualquier momento. Al finalizar la visualización de estos materiales o al finalizar esta licencia, debe destruir cualquier material descargado en su posesión, ya sea en formato electrónico o impreso.</p>
        </div>

        <div class="policy-section">
            <h2>3. Descargo de Responsabilidad</h2>
            <p>Los materiales en el sitio web de Rumbero Extremo se proporcionan "tal cual". Rumbero Extremo no ofrece garantías, expresas o implícitas, y por la presente renuncia y niega todas las demás garantías, incluidas, entre otras, las garantías implícitas o las condiciones de comerciabilidad, idoneidad para un propósito particular o no infracción de la propiedad intelectual u otra violación de derechos.</p>
            <p>Además, Rumbero Extremo no garantiza ni hace ninguna representación con respecto a la precisión, los resultados probables o la confiabilidad del uso de los materiales en su sitio web o de otro modo relacionados con dichos materiales o en cualquier sitio vinculado a este sitio.</p>
        </div>

        <div class="policy-section">
            <h2>4. Limitaciones</h2>
            <p>En ningún caso Rumbero Extremo o sus proveedores serán responsables de ningún daño (incluidos, entre otros, daños por pérdida de datos o ganancias, o debido a la interrupción del negocio) que surjan del uso o la imposibilidad de usar los materiales en el sitio web de Rumbero Extremo, incluso si Rumbero Extremo o un representante autorizado de Rumbero Extremo ha sido notificado verbalmente o por escrito de la posibilidad de dicho daño.</p>
        </div>

        <div class="policy-section">
            <h2>5. Enlaces</h2>
            <p>Rumbero Extremo no ha revisado todos los sitios vinculados a su sitio web y no es responsable del contenido de dichos sitios vinculados. La inclusión de cualquier enlace no implica el respaldo de Rumbero Extremo al sitio. El uso de cualquier sitio web vinculado es bajo el propio riesgo del usuario.</p>
        </div>

        <div class="policy-section">
            <h2>6. Modificaciones de los Términos</h2>
            <p>Rumbero Extremo puede revisar estos términos de servicio para su sitio web en cualquier momento sin previo aviso. Al usar este sitio web, usted acepta estar sujeto a la versión actual de estos términos de servicio.</p>
        </div>

        <div class="policy-section">
            <h2>7. Ley Aplicable</h2>
            <p>Estos términos y condiciones se rigen e interpretan de acuerdo con las leyes de Venezuela y usted se somete irrevocablemente a la jurisdicción exclusiva de los tribunales de ese Estado o ubicación.</p>
        </div>

        <div class="policy-contact">
            <p>Si tienes alguna pregunta sobre estos Términos y Condiciones, puedes contactarnos:</p>
            <ul>
                <li>Por correo electrónico: <a href="mailto:rumberoextremo@gmail.com">rumberoextremo@gmail.com</a></li>
                <li>Visitando esta página en nuestro sitio web: <a href="{{ route('contact') }}">contactanos</a></li>
            </ul>
        </div>
    </div>
</section>
@endsection