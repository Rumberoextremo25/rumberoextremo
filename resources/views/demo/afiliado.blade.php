@extends('layouts.app')

@section('title', 'Conviértete en Afiliado - Rumbero Extremo App')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/affiliate-form.css') }}"> {{-- Nuevo CSS para el formulario de afiliados --}}
@endpush

@section('content')
    <section class="affiliate-section">
        <div class="contact-card">
            <div class="card-header">
                <h1>¡Únete a Nosotros como Usuario Rumbero!</h1>
                <p class="subtitle">¿Tienes una comunidad activa y pasión por la rumba? Únete a nuestro programa de afiliados, promociona los mejores eventos y locales, ¡y gana comisiones!</p>
            </div>

            <form method="POST" action="{{ route('affiliate.store') }}">
                @csrf

                <div class="form-grid">
                    <div class="input-group">
                        <label for="full_name"><i class="fas fa-user form-icon"></i> Nombre Completo</label>
                        <input id="full_name" type="text" name="full_name" value="{{ old('full_name') }}" required autofocus autocomplete="name" placeholder="Ej: Juan Pérez">
                        @error('full_name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="input-group">
                        <label for="email"><i class="fas fa-envelope form-icon"></i> Correo Electrónico</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="ejemplo@correo.com">
                        @error('email')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="input-group">
                        <label for="phone"><i class="fas fa-phone-alt form-icon"></i> Número de Teléfono</label>
                        <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" placeholder="Ej: +584121234567" autocomplete="tel">
                        @error('phone')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="input-group">
                        <label for="social_media_link"><i class="fas fa-link form-icon"></i> Enlace a Red Social Principal</label>
                        <input id="social_media_link" type="url" name="social_media_link" value="{{ old('social_media_link') }}" placeholder="https://instagram.com/tuusuario" required>
                        @error('social_media_link')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="input-group">
                        <label for="followers_count"><i class="fas fa-users form-icon"></i> Número Aproximado de Seguidores/Audiencia</label>
                        <input id="followers_count" type="number" name="followers_count" value="{{ old('followers_count') }}" min="0" required placeholder="Ej: 5000">
                        @error('followers_count')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="input-group">
                        <label for="affiliate_type"><i class="fas fa-handshake form-icon"></i> Tipo de Afiliado</label>
                        <div class="select-wrapper">
                            <select id="affiliate_type" name="affiliate_type" required>
                                <option value="">Selecciona una opción</option>
                                <option value="usuario_comun" {{ old('affiliate_type') == 'usuario_comun' ? 'selected' : '' }}>Usuario comun</option>
                                <option value="rumbero" {{ old('affiliate_type') == 'rumbero' ? 'selected' : '' }}>Rumbero</option>
                            </select>
                            <i class="fas fa-chevron-down select-arrow"></i>
                        </div>
                        @error('affiliate_type')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="input-group full-width">
                        <label for="message"><i class="fas fa-comment-dots form-icon"></i> Cuéntanos por qué quieres ser afiliado</label>
                        <textarea id="message" name="message" rows="4" placeholder="Describe brevemente tus ideas de promoción o tu comunidad. (opcional)">{{ old('message') }}</textarea>
                        @error('message')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="checkbox-group full-width">
                        <input type="checkbox" id="terms" name="terms" value="1" {{ old('terms') ? 'checked' : '' }} required>
                        <label for="terms">Acepto los <a href="{{ url('/terms-affiliate') }}" target="_blank">Términos y Condiciones del Programa de Afiliados</a>.</label>
                        @error('terms')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div> {{-- End form-grid --}}

                <button type="submit" class="form-button">
                    <i class="fas fa-paper-plane button-icon"></i> Enviar Solicitud
                </button>
            </form>
        </div>
    </section>
@endsection