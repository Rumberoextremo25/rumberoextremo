@extends('layouts.app')

@section('title', 'Contacto para Aliados - Rumbero Extremo App')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/allies-form.css') }}">
@endpush

@section('content')
    <div class="allies-container">
        <div class="contact-card">
            <div class="card-header">
                <h1 class="title">Colabora con Rumbero Extremo</h1>
                <p class="subtitle">¿Tienes un local, un evento, una marca o un servicio que potencie la rumba en venezuela? ¡Queremos conocerte! Rellena este formulario y creemos sinergias inolvidables.</p>
            </div>

            <form method="POST" action="{{ route('allies.store') }}">
                @csrf

                <div class="form-grid">
                    <div class="input-group">
                        <label for="company_name">Nombre del Local / Empresa / Evento:</label>
                        <input id="company_name" type="text" name="company_name" value="{{ old('company_name') }}" required autofocus placeholder="Ej: Terraza Sonora, Eventos Épicos C.A.">
                        @error('company_name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="input-group">
                        <label for="contact_person">Nombre de contacto:</label>
                        <input id="contact_person" type="text" name="contact_person" value="{{ old('contact_person') }}" required placeholder="Ej: Juan Pérez">
                        @error('contact_person')
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

                    <div class="input-group">
                        <label for="partnership_type">Tipo de alianza interesada:</label>
                        <div class="custom-select-wrapper">
                            <select id="partnership_type" name="partnership_type" required>
                                <option value="" disabled selected>Selecciona una opción</option>
                                <option value="venue_partnership" {{ old('partnership_type') == 'venue_partnership' ? 'selected' : '' }}>Alianza con Local/Bar</option>
                                <option value="event_promotion" {{ old('partnership_type') == 'event_promotion' ? 'selected' : '' }}>Promoción de Eventos</option>
                                <option value="brand_collaboration" {{ old('partnership_type') == 'brand_collaboration' ? 'selected' : '' }}>Colaboración de Marca</option>
                                <option value="media_partnership" {{ old('partnership_type') == 'media_partnership' ? 'selected' : '' }}>Alianza de Medios</option>
                                <option value="other" {{ old('partnership_type') == 'other' ? 'selected' : '' }}>Otro</option>
                            </select>
                            <i class="fas fa-chevron-down select-arrow"></i>
                        </div>
                        @error('partnership_type')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="input-group">
                        <label for="website">Sitio Web (si aplica):</label>
                        <input id="website" type="url" name="website" value="{{ old('website') }}" placeholder="www.ejemplo.com">
                        @error('website')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="input-group full-width">
                        <label for="message">Mensaje / Propuesta de colaboración:</label>
                        <textarea id="message" name="message" rows="6" required placeholder="Describe tu idea de colaboración, qué esperas lograr y cómo podemos potenciar la rumba juntos.">{{ old('message') }}</textarea>
                        @error('message')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <button type="submit" class="form-button">
                    Enviar Propuesta
                </button>
            </form>
        </div>
    </div>
@endsection