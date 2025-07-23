@extends('layouts.app')

@section('title', 'Políticas de Privacidad - Rumbero Extremo App')

@push('styles') {{-- Agregamos el CSS específico de esta vista --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/policy.css') }}">
@endpush

@section('content')
<section class="content-section">
    <div class="container policy-container">
        <h1>Políticas de Privacidad</h1>
        <p class="last-updated">Última actualización: 15 de junio de {{ date('Y') }}</p>

        <div class="policy-section">
            <h2>1. Información que Recopilamos</h2>
            <p>En Rumbero Extremo, recopilamos información para proporcionarte y mejorar nuestros servicios. Esto puede incluir:</p>
            <ul>
                <li><strong>Información de Registro:</strong> Nombre, correo electrónico, número de teléfono, fecha de nacimiento, al crear una cuenta.</li>
                <li><strong>Datos de Uso:</strong> Información sobre cómo interactúas con nuestra aplicación, incluyendo las páginas visitadas, funciones utilizadas y el tiempo de permanencia.</li>
                <li><strong>Información de Dispositivo:</strong> Tipo de dispositivo, sistema operativo, identificadores únicos del dispositivo, información de red móvil.</li>
                <li><strong>Datos de Ubicación:</strong> Con tu consentimiento, podemos recopilar datos de ubicación precisa para ofrecerte eventos y locales cercanos.</li>
                <li><strong>Contenido Generado por el Usuario:</strong> Fotos, comentarios, reseñas y cualquier otra información que publiques en nuestra plataforma.</li>
            </ul>
        </div>

        <div class="policy-section">
            <h2>2. Cómo Utilizamos tu Información</h2>
            <p>Utilizamos la información recopilada para:</p>
            <ul>
                <li>Proveer, operar y mantener nuestros servicios.</li>
                <li>Mejorar, personalizar y expandir nuestros servicios.</li>
                <li>Comprender y analizar cómo utilizas nuestros servicios.</li>
                <li>Desarrollar nuevos productos, servicios, características y funcionalidades.</li>
                <li>Comunicarnos contigo, directamente o a través de uno de nuestros socios, incluyendo para servicio al cliente, para proporcionarte actualizaciones y otra información relacionada con el servicio, y con fines de marketing y promocionales.</li>
                <li>Enviarte correos electrónicos y notificaciones push.</li>
                <li>Detectar y prevenir fraudes.</li>
            </ul>
        </div>

        <div class="policy-section">
            <h2>3. Compartir tu Información</h2>
            <p>No compartimos tu información personal con terceros, excepto en las siguientes circunstancias:</p>
            <ul>
                <li><strong>Con tu Consentimiento:</strong> Podemos compartir tu información cuando nos des tu consentimiento explícito.</li>
                <li><strong>Proveedores de Servicios:</strong> Podemos emplear empresas y personas de terceros para facilitar nuestros servicios, para proporcionar el servicio en nuestro nombre, para realizar servicios relacionados con el servicio o para ayudarnos a analizar cómo se utilizan nuestros servicios.</li>
                <li><strong>Fines Legales:</strong> Podemos divulgar tu información cuando sea requerido por ley, citación o cualquier proceso legal.</li>
                <li><strong>Transferencias de Negocios:</strong> Si Rumbero Extremo se involucra en una fusión, adquisición o venta de activos, tu información personal puede ser transferida.</li>
            </ul>
        </div>

        <div class="policy-section">
            <h2>4. Seguridad de los Datos</h2>
            <p>Nos esforzamos por utilizar medios comercialmente aceptables para proteger tu información personal, pero recuerda que ningún método de transmisión por Internet o método de almacenamiento electrónico es 100% seguro y confiable, y no podemos garantizar su seguridad absoluta.</p>
        </div>

        <div class="policy-section">
            <h2>5. Tus Derechos de Privacidad</h2>
            <p>Dependiendo de tu ubicación, puedes tener ciertos derechos con respecto a tu información personal, como el derecho a acceder, corregir o eliminar tus datos personales.</p>
            <p>Para ejercer estos derechos, por favor contáctanos a <a href="mailto:rumberoextremo@gmail.com">rumberoextremo@gmail.com</a>.</p>
        </div>

        <div class="policy-section">
            <h2>6. Cambios en esta Política de Privacidad</h2>
            <p>Podemos actualizar nuestra Política de Privacidad de vez en cuando. Te notificaremos cualquier cambio publicando la nueva Política de Privacidad en esta página. Se te aconseja revisar esta Política de Privacidad periódicamente para cualquier cambio.</p>
        </div>

        <div class="policy-contact">
            <p>Si tienes alguna pregunta sobre estas Políticas de Privacidad, puedes contactarnos:</p>
            <ul>
                <li>Por correo electrónico: <a href="mailto:rumberoextremo@gmail.com">rumberoextremo@gmail.com</a></li>
                <li>Visitando esta página en nuestro sitio web: <a href="{{ route('contact') }}">contactanos</a></li>
            </ul>
        </div>
    </div>
</section>
@endsection

@push('scripts') {{-- Agregamos el JS específico de esta vista --}}
    <script src="{{ asset('js/policy.js') }}"></script>
@endpush