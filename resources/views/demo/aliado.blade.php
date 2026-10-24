{{-- resources/views/demo/aliado.blade.php --}}

@extends('layouts.app')

@section('title', 'Registro de Aliado Comercial - Rumbero Extremo')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/public-modern.css') }}?v={{ filemtime(public_path('css/public-modern.css')) }}">
@endpush

@section('content')
    <div class="px-page">

        {{-- ============================== HERO ============================== --}}
        <section class="px-hero px-hero--compact">
            <div class="px-container">
                <span class="px-hero__kicker">
                    <i class="fa-solid fa-handshake"></i> Demo · Aliado comercial
                </span>

                <h1 class="px-hero__title">
                    Suma tu local a <span>Rumbero Extremo</span>
                </h1>

                <p class="px-hero__text">
                    Completa tus datos y el equipo de ventas se pondrá en contacto contigo para activar
                    tus promociones y atraer nuevo público.
                </p>
            </div>
        </section>

        {{-- ============================== FORMULARIO ============================== --}}
        <section class="px-section">
            <div class="px-container">
                <div class="px-form-grid">

                    <div class="px-form-card">
                        <div class="px-head px-head--left" style="margin-bottom:1.5rem;">
                            <span class="px-head__eyebrow">Registro</span>
                            <h2 class="px-head__title">Datos de tu local</h2>
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

                        <form method="POST" action="{{ route('allies.store') }}">
                            @csrf

                            <div class="px-row">
                                <div class="px-field">
                                    <label for="company_name">Nombre del local o empresa <span class="req">*</span></label>
                                    <input type="text" id="company_name" name="company_name"
                                        class="px-input @error('company_name') is-invalid @enderror"
                                        value="{{ old('company_name') }}" placeholder="Ej. La Jugada Bar" required
                                        autofocus>
                                    @error('company_name')
                                        <span class="px-error">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="px-field">
                                    <label for="contact_person">Persona de contacto <span class="req">*</span></label>
                                    <input type="text" id="contact_person" name="contact_person"
                                        class="px-input @error('contact_person') is-invalid @enderror"
                                        value="{{ old('contact_person') }}" placeholder="Nombre y apellido" required>
                                    @error('contact_person')
                                        <span class="px-error">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="px-row">
                                <div class="px-field">
                                    <label for="email">Correo electrónico <span class="req">*</span></label>
                                    <input type="email" id="email" name="email"
                                        class="px-input @error('email') is-invalid @enderror" value="{{ old('email') }}"
                                        placeholder="local@ejemplo.com" required autocomplete="email">
                                    @error('email')
                                        <span class="px-error">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="px-field">
                                    <label for="phone">Teléfono</label>
                                    <input type="tel" id="phone" name="phone"
                                        class="px-input @error('phone') is-invalid @enderror" value="{{ old('phone') }}"
                                        placeholder="0414-1234567" autocomplete="tel">
                                    @error('phone')
                                        <span class="px-error">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="px-field">
                                <label for="partnership_type">Tipo de alianza <span class="req">*</span></label>
                                <select id="partnership_type" name="partnership_type"
                                    class="px-input @error('partnership_type') is-invalid @enderror" required>
                                    <option value="" disabled {{ old('partnership_type') ? '' : 'selected' }}>
                                        Selecciona una opción</option>
                                    <option value="venue_partnership"
                                        {{ old('partnership_type') === 'venue_partnership' ? 'selected' : '' }}>
                                        Local / establecimiento</option>
                                    <option value="event_promotion"
                                        {{ old('partnership_type') === 'event_promotion' ? 'selected' : '' }}>
                                        Promoción de eventos</option>
                                    <option value="brand_collaboration"
                                        {{ old('partnership_type') === 'brand_collaboration' ? 'selected' : '' }}>
                                        Colaboración de marca</option>
                                    <option value="media_partnership"
                                        {{ old('partnership_type') === 'media_partnership' ? 'selected' : '' }}>
                                        Alianza con medios</option>
                                    <option value="other" {{ old('partnership_type') === 'other' ? 'selected' : '' }}>
                                        Otra</option>
                                </select>
                                @error('partnership_type')
                                    <span class="px-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="px-field">
                                <label for="website">Sitio web o redes (opcional)</label>
                                <input type="url" id="website" name="website"
                                    class="px-input @error('website') is-invalid @enderror" value="{{ old('website') }}"
                                    placeholder="https://www.ejemplo.com">
                                @error('website')
                                    <span class="px-error">{{ $message }}</span>
                                @enderror
                                <span class="px-hint">Incluye el https:// para que podamos visitarte.</span>
                            </div>

                            <div class="px-field">
                                <label for="message">Cuéntanos tu propuesta <span class="req">*</span></label>
                                <textarea id="message" name="message" rows="5"
                                    class="px-textarea @error('message') is-invalid @enderror"
                                    placeholder="¿Qué tipo de promociones o beneficios quieres ofrecer a los Rumberos?" required>{{ old('message') }}</textarea>
                                @error('message')
                                    <span class="px-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <button type="submit" class="px-btn px-btn--primary px-btn--block">
                                <i class="fa-solid fa-paper-plane"></i> Enviar solicitud
                            </button>

                            <p class="px-hint" style="margin-top:.75rem;text-align:center;">
                                Al enviar aceptas ser contactado por el equipo de ventas de Rumbero Extremo.
                            </p>
                        </form>
                    </div>

                    {{-- Panel lateral --}}
                    <aside class="px-form-aside">
                        <div class="px-card">
                            <span class="px-card__icon px-card__icon--gold"><i class="fa-solid fa-gift"></i></span>
                            <h3 class="px-card__title">Qué ganas como aliado</h3>
                            <ul class="px-benefit-list">
                                <li><i class="fa-solid fa-check"></i> Más afluencia de público real</li>
                                <li><i class="fa-solid fa-check"></i> Difusión de tus promociones en la app</li>
                                <li><i class="fa-solid fa-check"></i> Clientes que ya quieren salir</li>
                                <li><i class="fa-solid fa-check"></i> Panel propio para gestionar beneficios</li>
                            </ul>
                        </div>

                        <div class="px-card">
                            <span class="px-card__icon"><i class="fa-solid fa-headset"></i></span>
                            <h3 class="px-card__title">¿Dudas antes de enviar?</h3>
                            <p class="px-card__text">Escríbenos y te respondemos a la brevedad.</p>
                            <a href="{{ route('contact') }}" class="px-btn px-btn--light px-btn--block">
                                <i class="fa-solid fa-envelope"></i> Ir a contacto
                            </a>
                        </div>

                        <div class="px-card">
                            <span class="px-card__icon"><i class="fa-solid fa-clock"></i></span>
                            <h3 class="px-card__title">Tiempo de respuesta</h3>
                            <p class="px-card__text">Normalmente respondemos en menos de 24 horas hábiles.</p>
                        </div>
                    </aside>
                </div>
            </div>
        </section>
    </div>
@endsection
