@extends('layouts.app')

@section('title', 'Contacto para Aliados - Rumbero Extremo App')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/allies-form.css') }}"> {{-- Nuevo CSS para el formulario --}}
@endpush

@section('content')
    <section class="allies-contact-section">
        <div class="contact-card">
            <div class="card-header">
                <h1>Únete a Rumbero Extremo como Aliado Comercial</h1>
                <p class="subtitle">¿Tienes un local, un evento, una marca o un servicio que encienda la rumba en Venezuela? ¡Queremos hacer equipo contigo! Completa este formulario y forjemos alianzas épicas que hagan vibrar a todo el país.</p>
            </div>

            <form method="POST" action="{{ route('allies.store') }}">
                @csrf

                <div class="form-grid">
                    <div class="input-group">
                        <label for="company_name"><i class="fas fa-building form-icon"></i> Nombre del Local / Empresa / Evento</label>
                        <input id="company_name" type="text" name="company_name" value="{{ old('company_name') }}" required autofocus placeholder="Ej: Terraza Sonora, Eventos Épicos C.A.">
                        @error('company_name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="input-group">
                        <label for="contact_person"><i class="fas fa-user form-icon"></i> Nombre de Contacto</label>
                        <input id="contact_person" type="text" name="contact_person" value="{{ old('contact_person') }}" required placeholder="Ej: Juan Pérez">
                        @error('contact_person')
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

                    <div class="input-group full-width">
                        <label for="partnership_type"><i class="fas fa-handshake form-icon"></i> Tipo de Alianza Interesada</label>
                        <div class="select-wrapper">
                            <select id="partnership_type" name="partnership_type" required>
                                <option value="">Selecciona una opción</option>
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

                    <div class="input-group full-width">
                        <label for="website"><i class="fas fa-globe form-icon"></i> Sitio Web (si aplica)</label>
                        <input id="website" type="url" name="website" value="{{ old('website') }}" placeholder="https://www.tuweb.com">
                        @error('website')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="input-group full-width">
                        <label for="message"><i class="fas fa-comment-dots form-icon"></i> Cuéntanos sobre tu propuesta</label>
                        <textarea id="message" name="message" rows="6" required placeholder="Describe tu idea de colaboración, qué esperas lograr y cómo podemos potenciar la rumba juntos.">{{ old('message') }}</textarea>
                        @error('message')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div> {{-- End form-grid --}}

                <button type="submit" class="form-button">
                    <i class="fas fa-paper-plane button-icon"></i> Enviar Propuesta
                </button>
            </form>
        </div>
    </section>
@endsection

@push('scripts')
    {{-- Aquí puedes añadir cualquier JS específico si lo necesitas, por ahora no es estrictamente necesario para el diseño moderno --}}
@endpush