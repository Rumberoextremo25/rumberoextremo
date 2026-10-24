{{-- resources/views/demo/afiliado.blade.php --}}

@extends('layouts.app')

@section('title', 'Conviértete en Afiliado - Rumbero Extremo')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/public-modern.css') }}?v={{ filemtime(public_path('css/public-modern.css')) }}">
@endpush

@section('content')
    <div class="px-page">

        {{-- ============================== HERO ============================== --}}
        <section class="px-hero px-hero--compact">
            <div class="px-container">
                <span class="px-hero__kicker">
                    <i class="fa-solid fa-user-group"></i> Demo · Rumbero afiliado
                </span>

                <h1 class="px-hero__title">
                    Únete al programa de <span>afiliados</span>
                </h1>

                <p class="px-hero__text">
                    Si tienes comunidad y te mueves en la rumba, promociona los mejores eventos y locales
                    de Rumbero Extremo. Cuéntanos de ti y te contactamos.
                </p>
            </div>
        </section>

        {{-- ============================== FORMULARIO ============================== --}}
        <section class="px-section">
            <div class="px-container">
                <div class="px-form-grid">

                    <div class="px-form-card">
                        <div class="px-head px-head--left" style="margin-bottom:1.5rem;">
                            <span class="px-head__eyebrow">Solicitud</span>
                            <h2 class="px-head__title">Tus datos</h2>
                            <p class="px-head__text">Los campos con <span style="color:#ef4444">*</span> son obligatorios.</p>
                        </div>

                        @if ($errors->any())
                            <div class="px-alert px-alert--error">
                                <i class="fa-solid fa-circle-exclamation"></i>
                                <span>
                                    Revisa los campos marcados:
                                    <ul style="margin:.35rem 0 0;padding-left:1.1rem;">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </span>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('affiliate.store') }}">
                            @csrf

                            <div class="px-row">
                                <div class="px-field">
                                    <label for="full_name">Nombre completo <span class="req">*</span></label>
                                    <input type="text" id="full_name" name="full_name"
                                        class="px-input @error('full_name') is-invalid @enderror"
                                        value="{{ old('full_name') }}" placeholder="Nombre y apellido" required autofocus>
                                    @error('full_name')
                                        <span class="px-error">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="px-field">
                                    <label for="email">Correo electrónico <span class="req">*</span></label>
                                    <input type="email" id="email" name="email"
                                        class="px-input @error('email') is-invalid @enderror" value="{{ old('email') }}"
                                        placeholder="tucorreo@ejemplo.com" required autocomplete="email">
                                    @error('email')
                                        <span class="px-error">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="px-row">
                                <div class="px-field">
                                    <label for="phone">Teléfono</label>
                                    <input type="tel" id="phone" name="phone"
                                        class="px-input @error('phone') is-invalid @enderror" value="{{ old('phone') }}"
                                        placeholder="0414-1234567" autocomplete="tel">
                                    @error('phone')
                                        <span class="px-error">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="px-field">
                                    <label for="followers_count">Seguidores <span class="req">*</span></label>
                                    <input type="number" id="followers_count" name="followers_count"
                                        class="px-input @error('followers_count') is-invalid @enderror"
                                        value="{{ old('followers_count') }}" min="0" placeholder="1200" required>
                                    @error('followers_count')
                                        <span class="px-error">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="px-field">
                                <label for="social_media_link">Red social principal <span class="req">*</span></label>
                                <input type="url" id="social_media_link" name="social_media_link"
                                    class="px-input @error('social_media_link') is-invalid @enderror"
                                    value="{{ old('social_media_link') }}" placeholder="https://instagram.com/tu-usuario"
                                    required>
                                @error('social_media_link')
                                    <span class="px-error">{{ $message }}</span>
                                @enderror
                                <span class="px-hint">Tu perfil público donde se vea tu alcance.</span>
                            </div>

                            <div class="px-field">
                                <label for="affiliate_type">Tipo de afiliado <span class="req">*</span></label>
                                <select id="affiliate_type" name="affiliate_type"
                                    class="px-input @error('affiliate_type') is-invalid @enderror" required>
                                    <option value="" disabled {{ old('affiliate_type') ? '' : 'selected' }}>
                                        Selecciona una opción</option>
                                    <option value="influencer"
                                        {{ old('affiliate_type') === 'influencer' ? 'selected' : '' }}>Influencer</option>
                                    <option value="promoter" {{ old('affiliate_type') === 'promoter' ? 'selected' : '' }}>
                                        Promotor / animador</option>
                                    <option value="venue_owner"
                                        {{ old('affiliate_type') === 'venue_owner' ? 'selected' : '' }}>Dueño de local
                                    </option>
                                    <option value="other" {{ old('affiliate_type') === 'other' ? 'selected' : '' }}>Otro
                                    </option>
                                </select>
                                @error('affiliate_type')
                                    <span class="px-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="px-field">
                                <label for="message">Cuéntanos un poco más (opcional)</label>
                                <textarea id="message" name="message" rows="4"
                                    class="px-textarea @error('message') is-invalid @enderror"
                                    placeholder="¿Qué tipo de contenido haces? ¿En qué zonas te mueves?">{{ old('message') }}</textarea>
                                @error('message')
                                    <span class="px-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="px-field">
                                <label class="px-check">
                                    <input type="checkbox" id="terms" name="terms" value="1"
                                        {{ old('terms') ? 'checked' : '' }} required>
                                    <span>
                                        Acepto los
                                        <a href="{{ url('/terms') }}" target="_blank" rel="noopener">Términos y
                                            condiciones</a>
                                        y el uso de mis datos para gestionar la solicitud.
                                    </span>
                                </label>
                                @error('terms')
                                    <span class="px-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <button type="submit" class="px-btn px-btn--primary px-btn--block">
                                <i class="fa-solid fa-paper-plane"></i> Enviar solicitud
                            </button>
                        </form>
                    </div>

                    {{-- Panel lateral --}}
                    <aside class="px-form-aside">
                        <div class="px-card">
                            <span class="px-card__icon px-card__icon--gold"><i class="fa-solid fa-coins"></i></span>
                            <h3 class="px-card__title">Qué obtienes</h3>
                            <ul class="px-benefit-list">
                                <li><i class="fa-solid fa-check"></i> Comisiones por cada Rumbero que traigas</li>
                                <li><i class="fa-solid fa-check"></i> Acceso anticipado a eventos y promos</li>
                                <li><i class="fa-solid fa-check"></i> Material y soporte para tu contenido</li>
                                <li><i class="fa-solid fa-check"></i> Recompensas por rendimiento</li>
                            </ul>
                        </div>

                        <div class="px-card">
                            <span class="px-card__icon"><i class="fa-solid fa-star"></i></span>
                            <h3 class="px-card__title">¿A quién buscamos?</h3>
                            <p class="px-card__text">
                                Creadores de contenido, promotores y dueños de locales con comunidad activa
                                en Venezuela.
                            </p>
                        </div>

                        <div class="px-card">
                            <span class="px-card__icon"><i class="fa-solid fa-headset"></i></span>
                            <h3 class="px-card__title">¿Tienes dudas?</h3>
                            <a href="{{ route('contact') }}" class="px-btn px-btn--light px-btn--block">
                                <i class="fa-solid fa-envelope"></i> Escríbenos
                            </a>
                        </div>
                    </aside>
                </div>
            </div>
        </section>
    </div>
@endsection
