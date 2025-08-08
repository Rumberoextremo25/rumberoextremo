@extends('layouts.admin')

@section('title', 'Rumbero Extremo - Actualizar Perfil')

@section('page_title_toolbar', 'Actualizar Perfil')

@push('styles')
    {{-- Asegúrate de que Font Awesome esté cargado en tu layout global, si no, puedes añadirlo aquí --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    {{-- Enlaza al archivo CSS específico para la vista de perfil (que ahora incluye edición) --}}
    <link rel="stylesheet" href="{{ asset('css/admin/profile.css') }}">
@endpush

@section('content')
    <div class="dashboard-container">
        {{-- Mensajes de Éxito o Error --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <h4 class="alert-heading">¡Ups! Algo salió mal.</h4>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="profile-card form-card"> {{-- Usamos 'profile-card' para el estilo base y 'form-card' para estilos específicos de formulario --}}
            <h3 class="card-title"><i class="fas fa-user-edit me-2"></i> Actualizar Datos de Perfil</h3>

            {{-- IMPORTANTE: Cambia la acción del formulario a la ruta 'profile.update' y usa el método PUT --}}
            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT') {{-- Esto es crucial para enviar la solicitud como PUT --}}

                <div class="form-section-group"> {{-- Nuevo contenedor para agrupar campos visualmente --}}
                    <h4>Información Personal</h4>
                    <div class="form-group avatar-upload-group">
                        <label for="profile_photo_upload" class="form-label">Imagen de Perfil</label>
                        <div class="avatar-preview-wrapper">
                            <img id="avatar_preview"
                                 src="{{ Auth::user()->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&color=FFFFFF&background=FF4B4B' }}"
                                 alt="Avatar actual" class="current-avatar">
                            <input type="file" id="profile_photo_upload" name="profile_photo_upload" accept="image/*" class="form-control-file">
                        </div>
                        <small class="form-text-hint">Sube una nueva imagen para actualizar tu foto de perfil.</small>
                        @error('profile_photo_upload')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-grid"> {{-- Grid para organizar los campos en dos columnas --}}
                        <div class="form-group">
                            <label for="name" class="form-label">Nombre Completo</label>
                            <input type="text" id="name" name="name"
                                   value="{{ old('name', Auth::user()->name) }}"
                                   class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="email" class="form-label">Correo Electrónico</label>
                            <input type="email" id="email" name="email"
                                   value="{{ old('email', Auth::user()->email) }}"
                                   class="form-control @error('email') is-invalid @enderror" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="identification" class="form-label">Cédula/RIF</label>
                            <input type="text" id="identification" name="identification"
                                   value="{{ old('identification', Auth::user()->identification) }}"
                                   class="form-control @error('identification') is-invalid @enderror" required>
                            @error('identification')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="dob" class="form-label">Fecha de Nacimiento</label>
                            <input type="date" id="dob" name="dob"
                                   value="{{ old('dob', Auth::user()->dob ? \Carbon\Carbon::parse(Auth::user()->dob)->format('Y-m-d') : '') }}"
                                   class="form-control @error('dob') is-invalid @enderror">
                            @error('dob')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="phone1" class="form-label">Teléfono Principal</label>
                            <input type="tel" id="phone1" name="phone1"
                                   value="{{ old('phone1', Auth::user()->phone1) }}"
                                   class="form-control @error('phone1') is-invalid @enderror" required>
                            @error('phone1')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="phone2" class="form-label">Teléfono Adicional (Opcional)</label>
                            <input type="tel" id="phone2" name="phone2"
                                   value="{{ old('phone2', Auth::user()->phone2) }}"
                                   class="form-control @error('phone2') is-invalid @enderror">
                            @error('phone2')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div> {{-- Fin form-grid --}}

                    <div class="form-group full-width-field"> {{-- Campo de ancho completo --}}
                        <label for="address" class="form-label">Dirección</label>
                        <textarea id="address" name="address" rows="3"
                                  class="form-control @error('address') is-invalid @enderror" required>{{ old('address', Auth::user()->address) }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div> {{-- Fin form-section-group Personal --}}

                <div class="form-section-group mt-5"> {{-- Sección para credenciales --}}
                    <h4>Seguridad y Contraseña</h4>
                    <div class="form-group">
                        <label for="current_password" class="form-label">Contraseña Actual <span class="required-asterisk">*</span></label>
                        <input type="password" id="current_password" name="current_password"
                               placeholder="Ingresa tu contraseña actual para confirmar cambios"
                               class="form-control @error('current_password') is-invalid @enderror" autocomplete="off" required>
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="new_password" class="form-label">Nueva Contraseña (Opcional)</label>
                            <input type="password" id="new_password" name="new_password"
                                   placeholder="Deja vacío para no cambiar"
                                   class="form-control @error('new_password') is-invalid @enderror" autocomplete="new-password">
                            <small class="form-text-hint">Mínimo 8 caracteres, incluye mayúsculas, minúsculas, números y símbolos.</small>
                            @error('new_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="new_password_confirmation" class="form-label">Confirmar Nueva Contraseña</label>
                            <input type="password" id="new_password_confirmation" name="new_password_confirmation"
                                   placeholder="Repite la nueva contraseña"
                                   class="form-control @error('new_password_confirmation') is-invalid @enderror" autocomplete="new-password">
                            @error('new_password_confirmation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div> {{-- Fin form-grid --}}
                </div> {{-- Fin form-section-group Credenciales --}}

                <div class="form-actions mt-5">
                    <button type="button" class="btn btn-secondary" onclick="history.back();">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Mostrar/ocultar sección de aliado
            // Nota: La variable 'is_ally' debe ser pasada desde el controlador a la vista.
            // Ejemplo: return view('admin.profile.edit', ['is_ally' => Auth::user()->is_ally]);
            const isAlly = @json(Auth::user()->is_ally ?? false);
            const allyFieldsSection = document.getElementById('ally_fields_section');
            if (allyFieldsSection) { // Verifica si el elemento existe antes de manipularlo
                if (isAlly) {
                    allyFieldsSection.style.display = 'block';
                } else {
                    allyFieldsSection.style.display = 'none';
                }
            }

            // Preview de la imagen de perfil
            const profilePhotoUpload = document.getElementById('profile_photo_upload');
            const avatarPreview = document.getElementById('avatar_preview');

            if (profilePhotoUpload && avatarPreview) {
                profilePhotoUpload.addEventListener('change', function(event) {
                    const file = event.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            avatarPreview.src = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }

            // Validación en tiempo real para la confirmación de contraseña
            const newPasswordInput = document.getElementById('new_password');
            const confirmPasswordInput = document.getElementById('new_password_confirmation');

            function validateNewPasswords() {
                if (newPasswordInput.value !== confirmPasswordInput.value && confirmPasswordInput.value !== '') {
                    confirmPasswordInput.setCustomValidity('Las contraseñas no coinciden.');
                } else {
                    confirmPasswordInput.setCustomValidity('');
                }
                // Dispara la validación del navegador
                confirmPasswordInput.reportValidity();
            }

            if (newPasswordInput && confirmPasswordInput) {
                newPasswordInput.addEventListener('input', validateNewPasswords);
                confirmPasswordInput.addEventListener('input', validateNewPasswords);
            }
        });
    </script>
@endpush