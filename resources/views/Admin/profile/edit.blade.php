@extends('layouts.admin')

@section('title', 'Actualizar Perfil')

@section('page_title_toolbar', 'Actualizar Perfil')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/profile-update.css') }}">
@endpush

@section('content')
    <div class="profile-edit-wrapper">
        {{-- Header con bienvenida --}}
        <div class="profile-header-bar">
            <div class="header-content">
                <h1 class="page-title">
                    <span class="title-main">Actualizar</span>
                    <span class="title-accent">Perfil</span>
                </h1>
                <p class="page-subtitle">
                    <i class="fas fa-user-edit"></i>
                    Modifica tu información personal y de contacto
                </p>
            </div>
            <div class="header-actions">
                <div class="user-greeting">
                    <span>Editando,</span>
                    <strong>{{ Auth::user()->name }}</strong>
                </div>
                <div class="avatar-circle">
                    <span>{{ substr(Auth::user()->name, 0, 1) }}</span>
                </div>
            </div>
        </div>

        {{-- Mensajes de alerta --}}
        @if (session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
                <button type="button" class="alert-close">&times;</button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ session('error') }}</span>
                <button type="button" class="alert-close">&times;</button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <div style="flex: 1;">
                    <strong>Por favor corrige los siguientes errores:</strong>
                    <ul style="margin-top: 0.5rem; padding-left: 1.5rem;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button type="button" class="alert-close">&times;</button>
            </div>
        @endif

        {{-- Tarjeta principal del formulario --}}
        <div class="edit-card">
            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- Sección de Foto de Perfil --}}
                <div class="form-section">
                    <div class="section-header">
                        <div class="header-icon">
                            <i class="fas fa-camera"></i>
                        </div>
                        <div>
                            <h3>Foto de Perfil</h3>
                            <p>Actualiza tu imagen personal</p>
                        </div>
                    </div>

                    <div class="photo-upload-container">
                        <div class="photo-preview-wrapper">
                            <div class="photo-preview" id="photoPreview">
                                <img id="avatar_preview"
                                     src="{{ Auth::user()->profile_photo_url ?? 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&color=FFFFFF&background=A601B3' }}"
                                     alt="Avatar actual">
                            </div>
                            <div class="photo-upload-controls">
                                <label for="profile_photo_upload" class="btn-photo-upload">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    Seleccionar imagen
                                </label>
                                <input type="file" id="profile_photo_upload" name="profile_photo_upload" accept="image/*" hidden>
                                <p class="photo-hint">PNG, JPG o GIF (max. 2MB)</p>
                            </div>
                        </div>
                        @error('profile_photo_upload')
                            <div class="error-message">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="form-divider"></div>

                {{-- Sección de Información Personal --}}
                <div class="form-section">
                    <div class="section-header">
                        <div class="header-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <h3>Información Personal</h3>
                            <p>Tus datos básicos de identificación</p>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="name">
                                <i class="fas fa-user-tag"></i>
                                Nombre Completo
                            </label>
                            <input type="text" id="name" name="name"
                                value="{{ old('name', Auth::user()->name) }}" 
                                required
                                placeholder="Ej: Juan Pérez">
                            @error('name') <div class="error-message">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label for="email">
                                <i class="fas fa-envelope"></i>
                                Correo Electrónico
                            </label>
                            <input type="email" id="email" name="email"
                                value="{{ old('email', Auth::user()->email) }}" 
                                required
                                placeholder="ejemplo@correo.com">
                            @error('email') <div class="error-message">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label for="identification">
                                <i class="fas fa-id-card"></i>
                                Cédula / RIF
                            </label>
                            <input type="text" id="identification" name="identification"
                                value="{{ old('identification', Auth::user()->identification) }}" 
                                required
                                placeholder="V-12345678 o J-123456789">
                            @error('identification') <div class="error-message">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label for="dob">
                                <i class="fas fa-birthday-cake"></i>
                                Fecha de Nacimiento
                            </label>
                            <input type="date" id="dob" name="dob"
                                value="{{ old('dob', Auth::user()->dob ? \Carbon\Carbon::parse(Auth::user()->dob)->format('Y-m-d') : '') }}">
                            @error('dob') <div class="error-message">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="form-divider"></div>

                {{-- Sección de Contacto --}}
                <div class="form-section">
                    <div class="section-header">
                        <div class="header-icon">
                            <i class="fas fa-address-book"></i>
                        </div>
                        <div>
                            <h3>Información de Contacto</h3>
                            <p>Cómo pueden contactarte</p>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="phone1">
                                <i class="fas fa-phone-alt"></i>
                                Teléfono Principal
                            </label>
                            <input type="tel" id="phone1" name="phone1"
                                value="{{ old('phone1', Auth::user()->phone1) }}" 
                                required
                                placeholder="+58 412 1234567">
                            @error('phone1') <div class="error-message">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label for="phone2">
                                <i class="fas fa-phone"></i>
                                Teléfono Adicional (Opcional)
                            </label>
                            <input type="tel" id="phone2" name="phone2"
                                value="{{ old('phone2', Auth::user()->phone2) }}"
                                placeholder="+58 414 7654321">
                            @error('phone2') <div class="error-message">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group full-width">
                            <label for="address">
                                <i class="fas fa-map-marker-alt"></i>
                                Dirección
                            </label>
                            <textarea id="address" name="address" rows="3" required
                                placeholder="Calle, número, ciudad, estado">{{ old('address', Auth::user()->address) }}</textarea>
                            @error('address') <div class="error-message">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="form-divider"></div>

                {{-- Sección de Seguridad --}}
                <div class="form-section">
                    <div class="section-header">
                        <div class="header-icon">
                            <i class="fas fa-lock"></i>
                        </div>
                        <div>
                            <h3>Seguridad y Contraseña</h3>
                            <p>Cambia tu contraseña (opcional)</p>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label for="current_password">
                                <i class="fas fa-key"></i>
                                Contraseña Actual <span class="required-star">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input type="password" id="current_password" name="current_password"
                                    placeholder="Ingresa tu contraseña actual para confirmar cambios" required>
                                <button type="button" class="toggle-password" data-target="current_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('current_password') <div class="error-message">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label for="new_password">
                                <i class="fas fa-lock"></i>
                                Nueva Contraseña
                            </label>
                            <div class="input-wrapper">
                                <input type="password" id="new_password" name="new_password"
                                    placeholder="Deja vacío para no cambiar">
                                <button type="button" class="toggle-password" data-target="new_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small class="field-hint">Mínimo 8 caracteres, con mayúsculas, minúsculas, números y símbolos</small>
                            @error('new_password') <div class="error-message">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label for="new_password_confirmation">
                                <i class="fas fa-check-circle"></i>
                                Confirmar Nueva Contraseña
                            </label>
                            <div class="input-wrapper">
                                <input type="password" id="new_password_confirmation" name="new_password_confirmation"
                                    placeholder="Repite la nueva contraseña">
                                <button type="button" class="toggle-password" data-target="new_password_confirmation">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="password-match-feedback" id="passwordMatchFeedback"></div>
                            @error('new_password_confirmation') <div class="error-message">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                {{-- Botones de acción --}}
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="history.back();">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </button>
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-save"></i>
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // ========== PREVIEW DE IMAGEN ==========
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

            // ========== TOGGLE DE VISIBILIDAD DE CONTRASEÑA ==========
            document.querySelectorAll('.toggle-password').forEach(button => {
                button.addEventListener('click', function() {
                    const targetId = this.dataset.target;
                    const input = document.getElementById(targetId);
                    const icon = this.querySelector('i');
                    
                    if (input.type === 'password') {
                        input.type = 'text';
                        icon.className = 'fas fa-eye-slash';
                    } else {
                        input.type = 'password';
                        icon.className = 'fas fa-eye';
                    }
                });
            });

            // ========== VALIDACIÓN DE CONTRASEÑAS ==========
            const newPasswordInput = document.getElementById('new_password');
            const confirmPasswordInput = document.getElementById('new_password_confirmation');
            const passwordMatchFeedback = document.getElementById('passwordMatchFeedback');

            function validatePasswordMatch() {
                if (!newPasswordInput.value && !confirmPasswordInput.value) {
                    passwordMatchFeedback.innerHTML = '';
                    return true;
                }
                
                if (newPasswordInput.value === confirmPasswordInput.value) {
                    passwordMatchFeedback.innerHTML = '<i class="fas fa-check-circle"></i> Las contraseñas coinciden';
                    passwordMatchFeedback.className = 'password-match-feedback valid';
                    return true;
                } else {
                    passwordMatchFeedback.innerHTML = '<i class="fas fa-times-circle"></i> Las contraseñas no coinciden';
                    passwordMatchFeedback.className = 'password-match-feedback invalid';
                    return false;
                }
            }

            if (newPasswordInput && confirmPasswordInput) {
                newPasswordInput.addEventListener('input', validatePasswordMatch);
                confirmPasswordInput.addEventListener('input', validatePasswordMatch);
            }

            // ========== CERRAR ALERTAS ==========
            document.querySelectorAll('.alert-close').forEach(button => {
                button.addEventListener('click', function() {
                    this.closest('.alert').style.display = 'none';
                });
            });

            // Auto-cerrar alertas después de 5 segundos
            setTimeout(() => {
                document.querySelectorAll('.alert').forEach(alert => {
                    alert.style.display = 'none';
                });
            }, 5000);
        });
    </script>
@endpush