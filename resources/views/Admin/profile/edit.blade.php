@extends('layouts.admin')

@section('title', 'Actualizar Perfil')

@section('page_title_toolbar', 'Actualizar Perfil')

@push('styles')
    {{-- Dependencias de CSS para la nueva vista, adaptadas al diseño de "añadir usuario" --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
@endpush

@section('content')
    <div class="profile-update-container">
        {{-- Mensajes de Éxito o Error (se mantienen) --}}
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

        <div class="form-card">
            <div class="form-header">
                <div class="form-icon-wrapper">
                    <i class="fas fa-user-edit"></i>
                </div>
                <h2>Actualizar Perfil</h2>
            </div>

            <hr class="section-divider">

            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- Sección de Información Personal --}}
                <div class="form-section-group">
                    <h4 class="section-title"><i class="fas fa-user me-2"></i> Información Personal</h4>

                    <div class="form-group profile-photo-group">
                        <label for="profile_photo_upload">Foto de Perfil</label>
                        <div class="avatar-preview-wrapper">
                            <img id="avatar_preview"
                                 src="{{ Auth::user()->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&color=FFFFFF&background=6a0dad' }}"
                                 alt="Avatar actual" class="current-avatar">
                            <input type="file" id="profile_photo_upload" name="profile_photo_upload" accept="image/*">
                        </div>
                        <small class="form-text-hint">Sube una nueva imagen para actualizar tu foto de perfil.</small>
                        @error('profile_photo_upload')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="name">Nombre Completo:</label>
                            <input type="text" id="name" name="name"
                                value="{{ old('name', Auth::user()->name) }}" required>
                            @error('name') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label for="email">Correo Electrónico:</label>
                            <input type="email" id="email" name="email"
                                value="{{ old('email', Auth::user()->email) }}" required>
                            @error('email') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label for="identification">Cédula/RIF:</label>
                            <input type="text" id="identification" name="identification"
                                value="{{ old('identification', Auth::user()->identification) }}" required>
                            @error('identification') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label for="dob">Fecha de Nacimiento:</label>
                            <input type="date" id="dob" name="dob"
                                value="{{ old('dob', Auth::user()->dob ? \Carbon\Carbon::parse(Auth::user()->dob)->format('Y-m-d') : '') }}">
                            @error('dob') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label for="phone1">Teléfono Principal:</label>
                            <input type="tel" id="phone1" name="phone1"
                                value="{{ old('phone1', Auth::user()->phone1) }}" required>
                            @error('phone1') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label for="phone2">Teléfono Adicional (Opcional):</label>
                            <input type="tel" id="phone2" name="phone2"
                                value="{{ old('phone2', Auth::user()->phone2) }}">
                            @error('phone2') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group full-width">
                            <label for="address">Dirección:</label>
                            <textarea id="address" name="address" rows="3" required>{{ old('address', Auth::user()->address) }}</textarea>
                            @error('address') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <hr class="section-divider">

                {{-- Sección de Seguridad --}}
                <div class="form-section-group">
                    <h4 class="section-title"><i class="fas fa-lock me-2"></i> Seguridad y Contraseña</h4>

                    <div class="form-group">
                        <label for="current_password">Contraseña Actual <span class="required-asterisk">*</span></label>
                        <input type="password" id="current_password" name="current_password"
                            placeholder="Ingresa tu contraseña actual para confirmar cambios" autocomplete="off" required>
                        @error('current_password') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="new_password">Nueva Contraseña (Opcional):</label>
                            <input type="password" id="new_password" name="new_password"
                                placeholder="Deja vacío para no cambiar" autocomplete="new-password">
                            <small class="form-text-hint">Mínimo 8 caracteres, con mayúsculas, minúsculas, números y símbolos.</small>
                            @error('new_password') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label for="new_password_confirmation">Confirmar Nueva Contraseña:</label>
                            <input type="password" id="new_password_confirmation" name="new_password_confirmation"
                                placeholder="Repite la nueva contraseña" autocomplete="new-password">
                            @error('new_password_confirmation') <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="button-group">
                    <button type="button" class="cancel-btn" onclick="history.back();">
                        <i class="fas fa-times-circle"></i> Cancelar
                    </button>
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
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
                confirmPasswordInput.reportValidity();
            }

            if (newPasswordInput && confirmPasswordInput) {
                newPasswordInput.addEventListener('input', validateNewPasswords);
                confirmPasswordInput.addEventListener('input', validateNewPasswords);
            }
        });
    </script>
@endpush