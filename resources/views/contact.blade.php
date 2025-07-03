{{-- resources/views/contact.blade.php --}}

@extends('layouts.app')

@section('title', 'Contacto - Rumbero Extremo App')

@section('content')
    <section class="content-section">
        <div class="container">
            <h1>Contáctanos</h1>
            <p>¿Tienes preguntas, sugerencias o simplemente quieres saludar? ¡Estamos aquí para escucharte!</p>
            <div class="contact-form">
                <form action="{{ route('contact.store') }}" method="POST">
                    @csrf {{-- <--- AÑADE ESTA LÍNEA AQUÍ --}}

                    {{-- Opcional: Para mostrar mensajes de éxito/error, como lo habíamos hecho antes --}}
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <label for="name">Nombre:</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required placeholder="Tu nombre completo">
                    @error('name') <span class="error-message">{{ $message }}</span> @enderror

                    <label for="email">Correo Electrónico:</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="tu.correo@ejemplo.com">
                    @error('email') <span class="error-message">{{ $message }}</span> @enderror

                    <label for="subject">Asunto:</label>
                    <input type="text" id="subject" name="subject" value="{{ old('subject') }}" placeholder="Ej: Consulta general, Soporte técnico">
                    @error('subject') <span class="error-message">{{ $message }}</span> @enderror

                    <label for="message">Mensaje:</label>
                    {{-- Cambié el nombre del input a 'message_content' para que coincida con la validación y el modelo --}}
                    <textarea id="message" name="message_content" required placeholder="Describe tu consulta aquí...">{{ old('message_content') }}</textarea>
                    @error('message_content') <span class="error-message">{{ $message }}</span> @enderror

                    <button type="submit">Enviar Mensaje</button>
                </form>
            </div>
            <p class="contact-info">
                También puedes encontrarnos en nuestras redes sociales o enviarnos un correo directamente a
                <a href="mailto:info@rumberoextremo.com">info@rumberoextremo.com</a> {{-- Corregí el email que tenías --}}
            </p>
        </div>
    </section>
@endsection