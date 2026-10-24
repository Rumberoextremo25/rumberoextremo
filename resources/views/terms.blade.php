{{-- resources/views/terms.blade.php --}}

@extends('layouts.app')

@section('title', 'Términos y Condiciones - Rumbero Extremo')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/public-modern.css') }}?v={{ filemtime(public_path('css/public-modern.css')) }}">
@endpush

@section('content')
    <div class="px-page">

        <section class="px-hero px-hero--compact">
            <div class="px-container">
                <span class="px-hero__kicker">
                    <i class="fa-solid fa-file-contract"></i> Legal
                </span>

                <h1 class="px-hero__title">Términos y Condiciones</h1>

                <p class="px-hero__text">Las reglas de uso de la aplicación, la plataforma y los servicios de Rumbero Extremo.</p>
            </div>
        </section>

        <section class="px-section">
            <div class="px-container">
                <div class="px-legal__grid">
                    <aside class="px-legal__aside px-legal__aside--toc">
                        <nav class="px-toc" aria-label="Contenido">
                            <p class="px-toc__title">Contenido</p>
                            <ol class="px-toc__list">
                            <li><a href="#1-aceptacion-de-los-terminos">Aceptación de los Términos</a></li>
                            <li><a href="#2-licencia-de-uso">Licencia de Uso</a></li>
                            <li><a href="#3-descargo-de-responsabilidad">Descargo de Responsabilidad</a></li>
                            <li><a href="#4-limitaciones">Limitaciones</a></li>
                            <li><a href="#5-enlaces">Enlaces</a></li>
                            <li><a href="#6-modificaciones-de-los-terminos">Modificaciones de los Términos</a></li>
                            <li><a href="#7-ley-aplicable">Ley Aplicable</a></li>
                            </ol>
                        </nav>
                    </aside>

                    <div class="px-legal__content">
                    <section class="px-legal__section" id="1-aceptacion-de-los-terminos">
                        <h2 class="px-legal__title">
                            <span class="px-legal__num">1</span>
                            Aceptación de los Términos
                        </h2>
                        <div class="px-legal__body">
<p>Al acceder y utilizar la aplicación y el sitio web de Rumbero Extremo ("el Servicio"), usted acepta estar sujeto a estos Términos y Condiciones ("Términos"), todas las leyes y regulaciones aplicables, y acepta que es responsable del cumplimiento de las leyes locales aplicables. Si no está de acuerdo con alguno de estos términos, tiene prohibido usar o acceder a este sitio. Los materiales contenidos en este sitio web están protegidos por las leyes de derechos de autor y marcas comerciales aplicables.</p>
                        </div>
                    </section>

                    <section class="px-legal__section" id="2-licencia-de-uso">
                        <h2 class="px-legal__title">
                            <span class="px-legal__num">2</span>
                            Licencia de Uso
                        </h2>
                        <div class="px-legal__body">
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
                    </section>

                    <section class="px-legal__section" id="3-descargo-de-responsabilidad">
                        <h2 class="px-legal__title">
                            <span class="px-legal__num">3</span>
                            Descargo de Responsabilidad
                        </h2>
                        <div class="px-legal__body">
<p>Los materiales en el sitio web de Rumbero Extremo se proporcionan "tal cual". Rumbero Extremo no ofrece garantías, expresas o implícitas, y por la presente renuncia y niega todas las demás garantías, incluidas, entre otras, las garantías implícitas o las condiciones de comerciabilidad, idoneidad para un propósito particular o no infracción de la propiedad intelectual u otra violación de derechos.</p>
            <p>Además, Rumbero Extremo no garantiza ni hace ninguna representación con respecto a la precisión, los resultados probables o la confiabilidad del uso de los materiales en su sitio web o de otro modo relacionados con dichos materiales o en cualquier sitio vinculado a este sitio.</p>
                        </div>
                    </section>

                    <section class="px-legal__section" id="4-limitaciones">
                        <h2 class="px-legal__title">
                            <span class="px-legal__num">4</span>
                            Limitaciones
                        </h2>
                        <div class="px-legal__body">
<p>En ningún caso Rumbero Extremo o sus proveedores serán responsables de ningún daño (incluidos, entre otros, daños por pérdida de datos o ganancias, o debido a la interrupción del negocio) que surjan del uso o la imposibilidad de usar los materiales en el sitio web de Rumbero Extremo, incluso si Rumbero Extremo o un representante autorizado de Rumbero Extremo ha sido notificado verbalmente o por escrito de la posibilidad de dicho daño.</p>
                        </div>
                    </section>

                    <section class="px-legal__section" id="5-enlaces">
                        <h2 class="px-legal__title">
                            <span class="px-legal__num">5</span>
                            Enlaces
                        </h2>
                        <div class="px-legal__body">
<p>Rumbero Extremo no ha revisado todos los sitios vinculados a su sitio web y no es responsable del contenido de dichos sitios vinculados. La inclusión de cualquier enlace no implica el respaldo de Rumbero Extremo al sitio. El uso de cualquier sitio web vinculado es bajo el propio riesgo del usuario.</p>
                        </div>
                    </section>

                    <section class="px-legal__section" id="6-modificaciones-de-los-terminos">
                        <h2 class="px-legal__title">
                            <span class="px-legal__num">6</span>
                            Modificaciones de los Términos
                        </h2>
                        <div class="px-legal__body">
<p>Rumbero Extremo puede revisar estos términos de servicio para su sitio web en cualquier momento sin previo aviso. Al usar este sitio web, usted acepta estar sujeto a la versión actual de estos términos de servicio.</p>
                        </div>
                    </section>

                    <section class="px-legal__section" id="7-ley-aplicable">
                        <h2 class="px-legal__title">
                            <span class="px-legal__num">7</span>
                            Ley Aplicable
                        </h2>
                        <div class="px-legal__body">
<p>Estos términos y condiciones se rigen e interpretan de acuerdo con las leyes de Venezuela y usted se somete irrevocablemente a la jurisdicción exclusiva de los tribunales de ese Estado o ubicación.</p>
        


        

            <p>Si tienes alguna pregunta sobre estos Términos y Condiciones, puedes contactarnos:</p>
            <ul>
                <li>Por correo electrónico: <a href="mailto:rumberoextremo@gmail.com">rumberoextremo@gmail.com</a></li>
                <li>Visitando esta página en nuestro sitio web: <a href="{{ route('contact') }}">contactanos</a></li>
            </ul>
                        </div>
                    </section>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
