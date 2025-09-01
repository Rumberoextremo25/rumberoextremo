@extends('layouts.app')

@section('title', 'Conviértete en Afiliado - Rumbero Extremo App')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
@endpush

@section('content')
    <div class="affiliate-container">
        <div class="affiliate-card">
            <div class="card-header">
                <h1 class="title">¡Únete a nuestro programa de afiliados!</h1>
                <p class="subtitle">¿Eres un rumbero influyente? ¿Tienes una comunicación activa? Asóciate con Rumbero Extremo y gana comisiones promocionando los mejores eventos y locales.</p>
            </div>

            <form method="POST" action="{{ route('affiliate.store') }}">
                @csrf

                <div class="form-grid">
                    <div class="input-group">
                        <label for="full_name">Nombre:</label>
                        <input id="full_name" type="text" name="full_name" value="{{ old('full_name') }}" required autofocus autocomplete="name" placeholder="Tu nombre completo">
                        @error('full_name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="input-group">
                        <label for="email">Correo electrónico:</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="Tu.correo@ejemplo.com">
                        @error('email')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="input-group">
                        <label for="phone">Número de teléfono:</label>
                        <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" placeholder="xxxx-xxx xx xx" autocomplete="tel">
                        @error('phone')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="input-group social-media-group">
                        <label for="social_media_link">Enlace a Red Social principal</label>
                        <div class="input-with-icons">
                            <input id="social_media_link" type="text" name="social_media_link" value="{{ old('social_media_link') }}" placeholder="@ejemplo" required>
                            <div class="social-icons">
                                <i class="fab fa-instagram"></i>
                                <i class="fab fa-tiktok"></i>
                                <i class="fab fa-facebook-f"></i>
                                <i class="fab fa-spotify"></i>
                            </div>
                        </div>
                        @error('social_media_link')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="input-group">
                        <label for="followers_count">Número aproximado de seguidores/audiencia:</label>
                        <input id="followers_count" type="number" name="followers_count" value="{{ old('followers_count') }}" min="0" required placeholder="xxxxxx">
                        @error('followers_count')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="input-group">
                        <label for="affiliate_type">Tipo de afiliado:</label>
                        <div class="custom-select-wrapper">
                            <select id="affiliate_type" name="affiliate_type" required>
                                <option value="" disabled selected>Selecciona una opción</option>
                                <option value="usuario_comun" {{ old('affiliate_type') == 'usuario_comun' ? 'selected' : '' }}>Usuario común</option>
                                <option value="rumbero" {{ old('affiliate_type') == 'rumbero' ? 'selected' : '' }}>Rumbero</option>
                            </select>
                            <i class="fas fa-chevron-down select-arrow"></i>
                        </div>
                        @error('affiliate_type')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="input-group full-width">
                        <label for="message">Cuéntanos por qué quieres ser afiliado a Rumbero Extremo:</label>
                        <textarea id="message" name="message" rows="4" placeholder="Opcional..."></textarea>
                        @error('message')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="checkbox-group">
                    <input type="checkbox" id="terms" name="terms" value="1" {{ old('terms') ? 'checked' : '' }} required>
                    <label for="terms">Acepto los <a href="{{ url('/terms-affiliate') }}" target="_blank">Términos y Condiciones del Programa de Afiliados</a></label>
                    @error('terms')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="form-button">
                    Enviar Solicitud
                </button>
            </form>
        </div>
    </div>
@endsection