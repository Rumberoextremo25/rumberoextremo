{{-- resources/views/faq.blade.php --}}

@extends('layouts.app')

@section('title', 'Preguntas Frecuentes - Rumbero Extremo')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/public-modern.css') }}?v={{ filemtime(public_path('css/public-modern.css')) }}">
@endpush

@section('content')
    <div class="px-page">

        <section class="px-hero px-hero--compact">
            <div class="px-container">
                <span class="px-hero__kicker">
                    <i class="fa-solid fa-circle-question"></i> Ayuda
                </span>

                <h1 class="px-hero__title">
                    Preguntas <span>frecuentes</span>
                </h1>

                <p class="px-hero__text">
                    Encuentra respuestas a las dudas más comunes sobre Rumbero Extremo. Si no ves la tuya,
                    escríbenos y la respondemos.
                </p>
            </div>
        </section>

        <section class="px-section">
            <div class="px-container">
                <div class="px-legal__grid">
                    <div class="px-legal__content">
                    <details class="px-acc" open>
                        <summary class="px-acc__q">
                            <span class="px-acc__num">01</span>
                            <span class="px-acc__text">¿Qué es Rumbero Extremo?</span>
                            <i class="fa-solid fa-chevron-down px-acc__chev" aria-hidden="true"></i>
                        </summary>
                        <div class="px-acc__a">
                            <p>Es una alternativa de pago digital (app) para invertir en los Aliados Comerciales (comercios afiliados a la aplicación) que ofrecen productos y servicios del área de recreación y entretenimiento con beneficios de descuentos y/o promociones exclusivas para los Usuarios de Rumbero Extremo.</p>
                        </div>
                    </details>
                    <details class="px-acc" >
                        <summary class="px-acc__q">
                            <span class="px-acc__num">02</span>
                            <span class="px-acc__text">¿Cómo y dónde puedo utilizar Rumbero Extremo?</span>
                            <i class="fa-solid fa-chevron-down px-acc__chev" aria-hidden="true"></i>
                        </summary>
                        <div class="px-acc__a">
                            <p>Descargando la aplicación y registrando tus datos personales y de interés; te conviertes en usuario activo, para disfrutar de los beneficios que ofrecen los Aliados Comerciales, agrupados en rubros de actividades económicas relacionadas al Entretenimiento y Recreación, quienes ofrecerán beneficios en sus compras/consumos.</p>
                        </div>
                    </details>
                    <details class="px-acc" >
                        <summary class="px-acc__q">
                            <span class="px-acc__num">03</span>
                            <span class="px-acc__text">¿Cómo logro validar mi identidad?</span>
                            <i class="fa-solid fa-chevron-down px-acc__chev" aria-hidden="true"></i>
                        </summary>
                        <div class="px-acc__a">
                            <p>En el proceso de registro debes colocar foto de la cédula de identidad y validación biométrica del usuario que está realizando el registro.</p>
                        </div>
                    </details>
                    <details class="px-acc" >
                        <summary class="px-acc__q">
                            <span class="px-acc__num">04</span>
                            <span class="px-acc__text">¿Cómo funciona la aplicación Rumbero Extremo?</span>
                            <i class="fa-solid fa-chevron-down px-acc__chev" aria-hidden="true"></i>
                        </summary>
                        <div class="px-acc__a">
                            <p>Antes de realizar la compra/consumo certifica que el comercio es nuestro Aliado Comercial, verifica el/los beneficio(s) que disfrutas como Rumbero Extremo (descuento / promociones / entre otros). Los pagos se realizan bajo la aplicación con los instrumentos financieros como Pago Móvil, Tarjeta de Crédito Nacional o Internacional y Código QR.</p>
                        </div>
                    </details>
                    <details class="px-acc" >
                        <summary class="px-acc__q">
                            <span class="px-acc__num">05</span>
                            <span class="px-acc__text">¿Cómo pagas con Rumbero Extremo?</span>
                            <i class="fa-solid fa-chevron-down px-acc__chev" aria-hidden="true"></i>
                        </summary>
                        <div class="px-acc__a">
                            <p>Es una transacción al contado, no maneja inicial ni cuotas, ni intereses. Rumbero Extremo es aplicación que afiliarte podrás recibir los beneficios a través del escaneo de un código QR pagando con instrumentos financieros como Pago Móvil, Tarjeta de Crédito Nacional o Internacional. En el caso de pago de Delivery el Aliado Comercial debe incluirlo en el monto a pagar.</p>
                        </div>
                    </details>
                    <details class="px-acc" >
                        <summary class="px-acc__q">
                            <span class="px-acc__num">06</span>
                            <span class="px-acc__text">¿Cómo pago a los Aliados Comerciales de Rumbero Extremo?</span>
                            <i class="fa-solid fa-chevron-down px-acc__chev" aria-hidden="true"></i>
                        </summary>
                        <div class="px-acc__a">
                            <p>Los pagos se realizan a través del escaneo de un código QR pagando con instrumentos financieros como Pago Móvil, Tarjeta de Crédito Nacional o Internacional. En el caso de pago de Delivery el Aliado Comercial debe incluirlo en el monto total del pago.</p>
                        </div>
                    </details>
                    <details class="px-acc" >
                        <summary class="px-acc__q">
                            <span class="px-acc__num">07</span>
                            <span class="px-acc__text">¿Qué beneficio obtengo como Rumbero Extremo?</span>
                            <i class="fa-solid fa-chevron-down px-acc__chev" aria-hidden="true"></i>
                        </summary>
                        <div class="px-acc__a">
                            <p>Los Aliados Comerciales ofrecen descuentos y/o promociones atractivas para los usuarios de Rumbero Extremo, para mayor disfrute y ahorro en tus actividades de recreación y entretenimiento.</p>
                        </div>
                    </details>
                    <details class="px-acc" >
                        <summary class="px-acc__q">
                            <span class="px-acc__num">08</span>
                            <span class="px-acc__text">¿Afiliarme y hacer uso de Rumbero Extremo tiene algún costo?</span>
                            <i class="fa-solid fa-chevron-down px-acc__chev" aria-hidden="true"></i>
                        </summary>
                        <div class="px-acc__a">
                            <p>No, afiliarte y hacer uso de la app Rumbero Extremo no tiene costo alguno. Tampoco genera gastos por el mantenimiento de uso de la app.</p>
                        </div>
                    </details>
                    <details class="px-acc" >
                        <summary class="px-acc__q">
                            <span class="px-acc__num">09</span>
                            <span class="px-acc__text">¿Si tengo dinero en la app de Rumbero Extremo puedo hacer uso de este en otros comercios sin ser Aliados Comerciales?</span>
                            <i class="fa-solid fa-chevron-down px-acc__chev" aria-hidden="true"></i>
                        </summary>
                        <div class="px-acc__a">
                            <p>No, para hacer uso de Rumbero Extremo el comercio debe ser Aliado Comercial, estos son los únicos autorizados a través de una contratación para realizar transacciones con la app de Rumbero Extremo.</p>
                        </div>
                    </details>
                    <details class="px-acc" >
                        <summary class="px-acc__q">
                            <span class="px-acc__num">10</span>
                            <span class="px-acc__text">¿Cómo Usuario de Rumbero Extremo puede ver mis consumos?</span>
                            <i class="fa-solid fa-chevron-down px-acc__chev" aria-hidden="true"></i>
                        </summary>
                        <div class="px-acc__a">
                            <p>Si, en la app Rumbero Extremo contarás con una sección donde podrás revisar en detalle tus transacciones.</p>
                        </div>
                    </details>
                    <details class="px-acc" >
                        <summary class="px-acc__q">
                            <span class="px-acc__num">11</span>
                            <span class="px-acc__text">¿Existe algún monto límite mínimo o máximo para el pago/consumo con Rumbero Extremo?</span>
                            <i class="fa-solid fa-chevron-down px-acc__chev" aria-hidden="true"></i>
                        </summary>
                        <div class="px-acc__a">
                            <p>En cuanto al pago/consumo no hay límite de los productos y servicios que ofrecen los Aliados Comerciales.</p>
                        </div>
                    </details>
                    <details class="px-acc" >
                        <summary class="px-acc__q">
                            <span class="px-acc__num">12</span>
                            <span class="px-acc__text">¿Cómo cuidarte de fraude?</span>
                            <i class="fa-solid fa-chevron-down px-acc__chev" aria-hidden="true"></i>
                        </summary>
                        <div class="px-acc__a">
                            <p>Te recomendamos para que evites algún fraude, verificando la identidad del remitente, activar la autenticación de dos factores, y no compartir información personal. Verificación de la identidad del remitente • Confirma que la dirección de correo electrónico o el número de teléfono corresponda al de Rumbero Extremo • Si no estás seguro del destinatario, contacta directamente a Rumbero Extremo a través de sus canales de atención y confirma si han realizado alguna gestión para contactarte. Activa la autenticación de dos factores • Esto se refiere cuando realices tu registro colocar tu contraseña y utilizar el sensor de huella dactilar.</p>
                        </div>
                    </details>
                    <details class="px-acc" >
                        <summary class="px-acc__q">
                            <span class="px-acc__num">13</span>
                            <span class="px-acc__text">¿Si tengo alguna sugerencia, comentario o novedad de Rumbero Extremo dónde me puedo comunicar?</span>
                            <i class="fa-solid fa-chevron-down px-acc__chev" aria-hidden="true"></i>
                        </summary>
                        <div class="px-acc__a">
                            <p>Puedes comunicarte a través de nuestros canales de atención: - WhatsApp +584265570676 - Correo electrónico rumberoextremo@gmail.com - DM Instagram @rumberoextremo</p>
                        </div>
                    </details>
                    </div>

                    <aside class="px-legal__aside">
                        <div class="px-card">
                            <span class="px-card__icon"><i class="fa-solid fa-headset"></i></span>
                            <h2 class="px-card__title">¿No encuentras tu respuesta?</h2>
                            <p class="px-card__text">El equipo de soporte te responde a la brevedad.</p>
                            <a href="{{ route('contact') }}" class="px-btn px-btn--primary px-btn--block">
                                <i class="fa-solid fa-envelope"></i> Contactar
                            </a>
                            <a href="{{ url('/cookies') }}" class="px-btn px-btn--light px-btn--block">
                                <i class="fa-solid fa-cookie-bite"></i> Política de cookies
                            </a>
                        </div>

                        <div class="px-card">
                            <span class="px-card__icon px-card__icon--gold"><i class="fa-solid fa-file-contract"></i></span>
                            <h2 class="px-card__title">Documentos legales</h2>
                            <ul class="px-benefit-list">
                                <li><i class="fa-solid fa-angle-right" style="color:#a601b3"></i><a href="{{ url('/terms') }}">Términos y condiciones</a></li>
                                <li><i class="fa-solid fa-angle-right" style="color:#a601b3"></i><a href="{{ url('/privacy') }}">Política de privacidad</a></li>
                            </ul>
                        </div>
                    </aside>
                </div>
            </div>
        </section>
    </div>
@endsection
