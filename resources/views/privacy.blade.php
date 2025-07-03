@extends('layouts.app')

@section('title', 'Políticas de Privacidad - Rumbero Extremo App')

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

{{-- Puedes agregar estilos específicos para estas páginas en tu app.css o un archivo dedicado --}}
<style>
    /* Estilos básicos para las páginas de políticas */
    .policy-container {
        max-width: 900px;
        margin: 40px auto;
        padding: 30px;
        background-color: var(--card-bg-color, #fff); /* Usa una variable CSS si la tienes */
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        color: var(--text-color, #333);
    }

    .policy-container h1 {
        font-size: 2.5em;
        color: var(--primary-color, #ff5722);
        margin-bottom: 20px;
        text-align: center;
        font-weight: 700;
    }

    .policy-container .last-updated {
        text-align: center;
        font-size: 0.9em;
        color: var(--text-color-light, #666);
        margin-bottom: 40px;
        font-style: italic;
    }

    .policy-section {
        margin-bottom: 30px;
        line-height: 1.7;
    }

    .policy-section h2 {
        font-size: 1.8em;
        color: var(--primary-color-dark, #e64a19);
        margin-bottom: 15px;
        border-bottom: 2px solid var(--primary-color-light, #ff8a65);
        padding-bottom: 8px;
        font-weight: 600;
    }

    .policy-section p {
        margin-bottom: 15px;
    }

    .policy-section ul {
        list-style-type: disc;
        margin-left: 30px;
        margin-bottom: 15px;
    }

    .policy-section ul li {
        margin-bottom: 8px;
    }

    .policy-section a {
        color: var(--link-color, #007bff);
        text-decoration: none;
    }

    .policy-section a:hover {
        text-decoration: underline;
    }

    .policy-contact {
        margin-top: 40px;
        padding-top: 20px;
        border-top: 1px solid #eee;
        text-align: center;
        font-size: 0.95em;
        color: var(--text-color-light, #555);
    }
    .policy-contact ul {
        list-style: none;
        padding: 0;
        margin-top: 10px;
    }
    .policy-contact ul li {
        margin-bottom: 5px;
    }
</style>