@extends('layouts.app')

@section('title', 'Preguntas Frecuentes - Rumbero Extremo')

@push('styles')
    <link
        href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Poppins:wght@300;400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/faq.css') }}">
@endpush

@section('content')
    <section class="faq-section">
        <div class="faq-container">
            <h2 class="faq-title">Preguntas Frecuentes</h2>
            <p class="faq-intro-text">Encuentra respuestas a las preguntas más comunes sobre nuestros servicios. Si no
                encuentras lo que buscas, ¡no dudes en contactarnos!</p>

            <div class="faq-list">
                {{-- Pregunta 1 --}}
                <div class="faq-item">
                    <button class="faq-question">
                        ¿Qué es Rumbero Extremo?
                        <span class="faq-icon">+</span>
                    </button>
                    <div class="faq-answer">
                        <p>Es una alternativa de pago digital (app) para invertir en los Aliados Comerciales (comercios
                            afiliados a la aplicación) que ofrecen productos y servicios del área de recreación y
                            entretenimiento con beneficios de descuentos y/o promociones exclusivas para los Usuarios de
                            Rumbero Extremo.</p>
                    </div>
                </div>

                {{-- Pregunta 2 --}}
                <div class="faq-item">
                    <button class="faq-question">
                        ¿Cómo y dónde puedo utilizar Rumbero Extremo?
                        <span class="faq-icon">+</span>
                    </button>
                    <div class="faq-answer">
                        <p>Descargando la aplicación y registrando tus datos personales y de interés; te conviertes en
                            usuario activo, para disfrutar de los beneficios que ofrecen los Aliados Comerciales, agrupados
                            en rubros de actividades económicas relacionadas al Entretenimiento y Recreación, quienes
                            ofrecerán beneficios en sus compras/consumos.</p>
                    </div>
                </div>

                {{-- Pregunta 3 --}}
                <div class="faq-item">
                    <button class="faq-question">
                        ¿Cómo logro validar mi identidad?
                        <span class="faq-icon">+</span>
                    </button>
                    <div class="faq-answer">
                        <p>En el proceso de registro debes colocar foto de la cédula de identidad y validación biométrica
                            del usuario que está realizando el registro.</p>
                    </div>
                </div>

                {{-- Pregunta 4 --}}
                <div class="faq-item">
                    <button class="faq-question">
                        ¿Cómo funciona la aplicación Rumbero Extremo?
                        <span class="faq-icon">+</span>
                    </button>
                    <div class="faq-answer">
                        <p>Antes de realizar la compra/consumo certifica que el comercio es nuestro Aliado Comercial,
                            verifica el/los beneficio(s) que disfrutas como Rumbero Extremo (descuento / promociones / entre
                            otros). Los pagos se realizan bajo la aplicación con los instrumentos financieros como Pago
                            Móvil, Tarjeta de Crédito Nacional o Internacional y Código QR.
                        </p>
                    </div>
                </div>

                {{-- Pregunta 5 --}}
                <div class="faq-item">
                    <button class="faq-question">
                        ¿Cómo pagas con Rumbero Extremo?
                        <span class="faq-icon">+</span>
                    </button>
                    <div class="faq-answer">
                        <p>Es una transacción al contado, no maneja inicial ni cuotas, ni intereses. Rumbero Extremo es
                            aplicación que afiliarte podrás recibir los beneficios a través del escaneo de un código QR
                            pagando con instrumentos financieros como Pago Móvil, Tarjeta de Crédito Nacional o
                            Internacional. En el caso de pago de Delivery el Aliado Comercial debe incluirlo en el monto a
                            pagar.
                        </p>
                    </div>
                </div>

                {{-- Pregunta 6 --}}
                <div class="faq-item">
                    <button class="faq-question">
                        ¿Cómo pago a los Aliados Comerciales de Rumbero Extremo?
                        <span class="faq-icon">+</span>
                    </button>
                    <div class="faq-answer">
                        <p>Los pagos se realizan a través del escaneo de un código QR pagando con instrumentos financieros
                            como Pago Móvil, Tarjeta de Crédito Nacional o Internacional. En el caso de pago de Delivery el
                            Aliado Comercial debe incluirlo en el monto total del pago.
                        </p>
                    </div>
                </div>

                {{-- Pregunta 7 --}}
                <div class="faq-item">
                    <button class="faq-question">
                        ¿Qué beneficio obtengo como Rumbero Extremo?
                        <span class="faq-icon">+</span>
                    </button>
                    <div class="faq-answer">
                        <p>Los Aliados Comerciales ofrecen descuentos y/o promociones atractivas para los usuarios de
                            Rumbero Extremo, para mayor disfrute y ahorro en tus actividades de recreación y
                            entretenimiento. </p>
                    </div>
                </div>

                {{-- Pregunta 8 --}}
                <div class="faq-item">
                    <button class="faq-question">
                        ¿Afiliarme y hacer uso de Rumbero Extremo tiene algún costo?
                        <span class="faq-icon">+</span>
                    </button>
                    <div class="faq-answer">
                        <p>No, afiliarte y hacer uso de la app Rumbero Extremo no tiene costo alguno. Tampoco genera gastos
                            por el mantenimiento de uso de la app.</p>
                    </div>
                </div>

                {{-- Pregunta 9 --}}
                <div class="faq-item">
                    <button class="faq-question">
                        ¿Si tengo dinero en la app de Rumbero Extremo puedo hacer uso de este en otros comercios sin ser
                        Aliados Comerciales?
                        <span class="faq-icon">+</span>
                    </button>
                    <div class="faq-answer">
                        <p>No, para hacer uso de Rumbero Extremo el comercio debe ser Aliado Comercial, estos son los únicos
                            autorizados a través de una contratación para realizar transacciones con la app de Rumbero
                            Extremo.</p>
                    </div>
                </div>

                {{-- Pregunta 10 --}}
                <div class="faq-item">
                    <button class="faq-question">
                        ¿Cómo Usuario de Rumbero Extremo puede ver mis consumos?
                        <span class="faq-icon">+</span>
                    </button>
                    <div class="faq-answer">
                        <p>Si, en la app Rumbero Extremo contarás con una sección donde podrás revisar en detalle tus
                            transacciones.</p>
                    </div>
                </div>

                {{-- Pregunta 11 --}}
                <div class="faq-item">
                    <button class="faq-question">
                        ¿Existe algún monto límite mínimo o máximo para el pago/consumo con Rumbero Extremo?
                        <span class="faq-icon">+</span>
                    </button>
                    <div class="faq-answer">
                        <p>En cuanto al pago/consumo no hay límite de los productos y servicios que ofrecen los Aliados
                            Comerciales.</p>
                    </div>
                </div>

                {{-- Pregunta 12 --}}
                <div class="faq-item">
                    <button class="faq-question">
                        ¿Cómo cuidarte de fraude?
                        <span class="faq-icon">+</span>
                    </button>
                    <div class="faq-answer">
                        <p>Te recomendamos para que evites algún fraude, verificando la identidad del remitente, activar la
                            autenticación de dos factores, y no compartir información personal.
                            Verificación de la identidad del remitente
                            • Confirma que la dirección de correo electrónico o el número de teléfono corresponda al de
                            Rumbero Extremo
                            • Si no estás seguro del destinatario, contacta directamente a Rumbero Extremo a través de sus
                            canales de atención y confirma si han realizado alguna gestión para contactarte.
                            Activa la autenticación de dos factores
                            • Esto se refiere cuando realices tu registro colocar tu contraseña y utilizar el sensor de
                            huella dactilar.
                        </p>
                    </div>
                </div>

                {{-- Pregunta 13 --}}
                <div class="faq-item">
                    <button class="faq-question">
                        ¿Si tengo alguna sugerencia, comentario o novedad de Rumbero Extremo dónde me puedo comunicar?
                        <span class="faq-icon">+</span>
                    </button>
                    <div class="faq-answer">
                        <p>Puedes comunicarte a través de nuestros canales de atención:
                            - WhatsApp +584143806713
                            - Correo electrónico rumberoextremo@gmail.com
                            - DM Instagram @rumberoextremo
                        </p>
                    </div>
                </div>
            </div>

            <p class="faq-contact-prompt">
                ¿Aún tienes preguntas? <a href="{{ url('/contact') }}" class="faq-contact-link">Contáctanos
                    directamente</a>.
            </p>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('js/faq.js') }}"></script>
@endpush
