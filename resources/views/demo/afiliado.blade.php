@extends('layouts.app')

@section('title', 'Conviértete en Afiliado - Rumbero Extremo App')

@push('styles') {{-- Agregamos el CSS específico de esta vista --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/demo.css') }}">
@endpush

@section('content')
    <section class="affiliate-section">
        <div class="affiliate-card">
            <h1>¡Únete a Nuestro Programa de Afiliados!</h1>
            <p class="subtitle">¿Eres un rumbero influyente? ¿Tienes una comunidad activa? Asóciate con Rumbero Extremo y gana comisiones promocionando los mejores eventos y locales.</p>

            <form method="POST" action="{{ route('affiliate.store') }}">
                @csrf

                <div class="input-group">
                    <label for="full_name">Nombre Completo</label>
                    <input id="full_name" type="text" name="full_name" value="{{ old('full_name') }}" required autofocus autocomplete="name">
                    @error('full_name')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="input-group">
                    <label for="email">Correo Electrónico</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email">
                    @error('email')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="input-group">
                    <label for="phone">Número de Teléfono</label>
                    <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" placeholder="Ej: +58412XXXXXXX" autocomplete="tel">
                    @error('phone')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="input-group">
                    <label for="social_media_link">Enlace a Red Social Principal (ej. Instagram, TikTok)</label>
                    <input id="social_media_link" type="url" name="social_media_link" value="{{ old('social_media_link') }}" placeholder="https://instagram.com/tuusuario" required>
                    @error('social_media_link')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="input-group">
                    <label for="followers_count">Número Aproximado de Seguidores/Audiencia</label>
                    <input id="followers_count" type="number" name="followers_count" value="{{ old('followers_count') }}" min="0" required>
                    @error('followers_count')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="input-group">
                    <label for="affiliate_type">Tipo de Afiliado</label>
                    <select id="affiliate_type" name="affiliate_type" required>
                        <option value="">Selecciona una opción</option>
                        <option value="influencer" {{ old('affiliate_type') == 'influencer' ? 'selected' : '' }}>Influencer / Creador de Contenido</option>
                        <option value="promoter" {{ old('affiliate_type') == 'promoter' ? 'selected' : '' }}>Promotor de Eventos</option>
                        <option value="venue_owner" {{ old('affiliate_type') == 'venue_owner' ? 'selected' : '' }}>Dueño de Local / Bar</option>
                        <option value="other" {{ old('affiliate_type') == 'other' ? 'selected' : '' }}>Otro</option>
                    </select>
                    @error('affiliate_type')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="input-group">
                    <label for="message">Cuéntanos por qué quieres ser afiliado de Rumbero Extremo (opcional)</label>
                    <textarea id="message" name="message" rows="4">{{ old('message') }}</textarea>
                    @error('message')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" id="terms" name="terms" value="1" {{ old('terms') ? 'checked' : '' }} required>
                    <label for="terms">Acepto los <a href="{{ url('/terms-affiliate') }}" target="_blank">Términos y Condiciones del Programa de Afiliados</a>.</label>
                    @error('terms')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="form-button">Enviar Solicitud</button>
            </form>
        </div>
    </section>
@endsection

@push('scripts') {{-- Agregamos el JS específico de esta vista --}}
    <script src="{{ asset('js/demo.js') }}"></script>
@endpush