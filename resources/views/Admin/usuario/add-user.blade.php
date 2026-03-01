@extends('layouts.admin')

@section('page_title_toolbar', 'Añadir Nuevo Usuario')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/user-add.css') }}">
@endpush

@section('content')
    <div class="user-add-wrapper">
        {{-- Header con bienvenida --}}
        <div class="add-header-bar">
            <div class="header-content">
                <h1 class="page-title">
                    <span class="title-main">Añadir Nuevo</span>
                    <span class="title-accent">Usuario</span>
                </h1>
                <p class="page-subtitle">
                    <i class="fas fa-user-plus"></i>
                    Completa la información para registrar un nuevo usuario
                </p>
            </div>
            <div class="header-actions">
                <div class="user-greeting">
                    <span>Creando nuevo,</span>
                    <strong>{{ Auth::user()->name }}</strong>
                </div>
                <div class="avatar-circle">
                    <span>{{ substr(Auth::user()->name, 0, 1) }}</span>
                </div>
            </div>
        </div>

        {{-- Tarjeta principal del formulario --}}
        <div class="form-main-card">
            {{-- Cabecera del formulario --}}
            <div class="form-header">
                <div class="header-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="header-title">
                    <h2>Información del Nuevo Usuario</h2>
                    <p>Todos los campos marcados con <span class="required-star">*</span> son obligatorios</p>
                </div>
            </div>

            <div class="form-divider"></div>

            {{-- Formulario --}}
            <form id="addUserForm" action="{{ route('admin.users.store') }}" method="POST" class="modern-form">
                @csrf

                {{-- Sección de Información Personal --}}
                <div class="form-section">
                    <div class="section-header">
                        <div class="section-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <h3>Información Personal</h3>
                            <p>Datos básicos de identificación del usuario</p>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="firstname">
                                <i class="fas fa-user-tag"></i>
                                Nombre <span class="required-star">*</span>
                            </label>
                            <input type="text" id="firstname" name="firstname" 
                                   placeholder="Ej: Juan" 
                                   value="{{ old('firstname') }}" 
                                   required>
                            @error('firstname') <div class="error-message">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label for="lastname">
                                <i class="fas fa-user-tag"></i>
                                Apellido <span class="required-star">*</span>
                            </label>
                            <input type="text" id="lastname" name="lastname" 
                                   placeholder="Ej: Pérez" 
                                   value="{{ old('lastname') }}" 
                                   required>
                            @error('lastname') <div class="error-message">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label for="email">
                                <i class="fas fa-envelope"></i>
                                Correo Electrónico <span class="required-star">*</span>
                            </label>
                            <input type="email" id="email" name="email" 
                                   placeholder="ejemplo@dominio.com" 
                                   value="{{ old('email') }}" 
                                   required>
                            @error('email') <div class="error-message">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label for="phone1">
                                <i class="fas fa-phone-alt"></i>
                                Teléfono (Opcional)
                            </label>
                            <input type="tel" id="phone1" name="phone1" 
                                   placeholder="Ej: +58 412 1234567" 
                                   value="{{ old('phone1') }}">
                            @error('phone1') <div class="error-message">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="form-divider"></div>

                {{-- Sección de Seguridad --}}
                <div class="form-section">
                    <div class="section-header">
                        <div class="section-icon">
                            <i class="fas fa-lock"></i>
                        </div>
                        <div>
                            <h3>Seguridad</h3>
                            <p>Establece la contraseña del nuevo usuario</p>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group password-group" id="passwordGroup">
                            <label for="password">
                                <i class="fas fa-key"></i>
                                Contraseña <span class="required-star">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input type="password" id="password" name="password" 
                                       placeholder="Mínimo 8 caracteres" 
                                       required minlength="8">
                                <button type="button" class="toggle-password" data-target="password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            
                            {{-- Medidor de fortaleza --}}
                            <div class="password-strength-container">
                                <div class="strength-meter">
                                    <div class="strength-fill" id="passwordStrengthFill"></div>
                                </div>
                                <span class="strength-label" id="strengthLabel">Muy débil</span>
                            </div>

                            {{-- Lista de validación --}}
                            <div class="validation-list" id="passwordValidationList">
                                <div class="validation-item pending" id="validationLength">
                                    <i class="fas fa-circle"></i>
                                    <span>Mínimo 8 caracteres</span>
                                </div>
                                <div class="validation-item pending" id="validationUppercase">
                                    <i class="fas fa-circle"></i>
                                    <span>Una letra mayúscula</span>
                                </div>
                                <div class="validation-item pending" id="validationLowercase">
                                    <i class="fas fa-circle"></i>
                                    <span>Una letra minúscula</span>
                                </div>
                                <div class="validation-item pending" id="validationNumber">
                                    <i class="fas fa-circle"></i>
                                    <span>Un número</span>
                                </div>
                                <div class="validation-item pending" id="validationSpecial">
                                    <i class="fas fa-circle"></i>
                                    <span>Un carácter especial (!@#$%^&*)</span>
                                </div>
                            </div>
                            @error('password') <div class="error-message">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group confirm-password-group" id="confirmPasswordGroup">
                            <label for="password_confirmation">
                                <i class="fas fa-check-circle"></i>
                                Confirmar Contraseña <span class="required-star">*</span>
                            </label>
                            <div class="input-wrapper">
                                <input type="password" id="password_confirmation" name="password_confirmation" 
                                       placeholder="Repite la contraseña" 
                                       required>
                                <button type="button" class="toggle-password" data-target="password_confirmation">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="password-match-feedback pending" id="passwordMatch">
                                <i class="fas fa-circle"></i>
                                <span>Las contraseñas deben coincidir</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-divider"></div>

                {{-- Sección de Configuración de Cuenta --}}
                <div class="form-section">
                    <div class="section-header">
                        <div class="section-icon">
                            <i class="fas fa-cog"></i>
                        </div>
                        <div>
                            <h3>Configuración de Cuenta</h3>
                            <p>Tipo de usuario, estado y fechas</p>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="user_type">
                                <i class="fas fa-user-tag"></i>
                                Tipo de Usuario <span class="required-star">*</span>
                            </label>
                            <div class="select-wrapper">
                                <select id="user_type" name="user_type" required>
                                    <option value="">Seleccione un tipo</option>
                                    <option value="comun" {{ old('user_type') == 'comun' ? 'selected' : '' }}>Común</option>
                                    <option value="aliado" {{ old('user_type') == 'aliado' ? 'selected' : '' }}>Aliado</option>
                                    <option value="afiliado" {{ old('user_type') == 'afiliado' ? 'selected' : '' }}>Afiliado</option>
                                    <option value="admin" {{ old('user_type') == 'admin' ? 'selected' : '' }}>Administrador</option>
                                </select>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            @error('user_type') <div class="error-message">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label for="status">
                                <i class="fas fa-toggle-on"></i>
                                Estado <span class="required-star">*</span>
                            </label>
                            <div class="select-wrapper">
                                <select id="status" name="status" required>
                                    <option value="activo" {{ old('status') == 'activo' ? 'selected' : '' }}>Activo</option>
                                    <option value="inactivo" {{ old('status') == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                                    <option value="pendiente" {{ old('status') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                </select>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            @error('status') <div class="error-message">{{ $message }}</div> @enderror
                        </div>

                        <div class="form-group">
                            <label for="registrationDate">
                                <i class="fas fa-calendar-alt"></i>
                                Fecha de Registro <span class="required-star">*</span>
                            </label>
                            <input type="date" id="registrationDate" name="registrationDate" 
                                   value="{{ old('registrationDate', \Carbon\Carbon::now()->format('Y-m-d')) }}" 
                                   required>
                            @error('registrationDate') <div class="error-message">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <div class="form-divider"></div>

                {{-- Sección de Notas --}}
                <div class="form-section">
                    <div class="section-header">
                        <div class="section-icon">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <div>
                            <h3>Notas Internas</h3>
                            <p>Información adicional sobre el usuario (opcional)</p>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <textarea id="notes" name="notes" 
                                  placeholder="Escribe aquí información adicional sobre el usuario..." 
                                  rows="4">{{ old('notes') }}</textarea>
                        @error('notes') <div class="error-message">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Botones de acción --}}
                <div class="form-actions">
                    <button type="button" class="btn-cancel" id="cancelAddUser">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </button>
                    <button type="submit" class="btn-submit" id="submitBtn">
                        <i class="fas fa-user-plus"></i>
                        Añadir Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
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

        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('password_confirmation');
        const passwordMatch = document.getElementById('passwordMatch');
        const passwordStrengthFill = document.getElementById('passwordStrengthFill');
        const strengthLabel = document.getElementById('strengthLabel');
        const submitBtn = document.getElementById('submitBtn');
        const passwordGroup = document.getElementById('passwordGroup');
        const confirmPasswordGroup = document.getElementById('confirmPasswordGroup');

        // Elementos de validación
        const validationElements = {
            length: document.getElementById('validationLength'),
            uppercase: document.getElementById('validationUppercase'),
            lowercase: document.getElementById('validationLowercase'),
            number: document.getElementById('validationNumber'),
            special: document.getElementById('validationSpecial')
        };

        // Expresiones regulares para validación
        const patterns = {
            uppercase: /[A-Z]/,
            lowercase: /[a-z]/,
            number: /[0-9]/,
            special: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/
        };

        // Función para validar la contraseña
        function validatePassword(password) {
            const validations = {
                length: password.length >= 8,
                uppercase: patterns.uppercase.test(password),
                lowercase: patterns.lowercase.test(password),
                number: patterns.number.test(password),
                special: patterns.special.test(password)
            };

            return validations;
        }

        // Función para actualizar la interfaz de validación
        function updatePasswordValidation(validations) {
            let validCount = 0;
            const totalValidations = Object.keys(validations).length;

            Object.keys(validations).forEach(key => {
                const isValid = validations[key];
                const element = validationElements[key];

                if (isValid) {
                    element.classList.remove('pending', 'invalid');
                    element.classList.add('valid');
                    element.innerHTML = '<i class="fas fa-check-circle"></i><span>' + element.querySelector('span').textContent + '</span>';
                    validCount++;
                } else {
                    element.classList.remove('pending', 'valid');
                    element.classList.add('invalid');
                    element.innerHTML = '<i class="fas fa-times-circle"></i><span>' + element.querySelector('span').textContent + '</span>';
                }
            });

            // Actualizar medidor de fuerza
            updateStrengthMeter(validCount, totalValidations);
        }

        // Función para actualizar el medidor de fuerza
        function updateStrengthMeter(validCount, totalValidations) {
            const percentage = (validCount / totalValidations) * 100;
            
            // Actualizar barra
            passwordStrengthFill.style.width = percentage + '%';
            
            // Actualizar clase y etiqueta
            passwordStrengthFill.className = 'strength-fill';
            
            if (percentage <= 20) {
                passwordStrengthFill.classList.add('strength-weak');
                strengthLabel.textContent = 'Muy débil';
                strengthLabel.style.color = '#ef4444';
            } else if (percentage <= 40) {
                passwordStrengthFill.classList.add('strength-fair');
                strengthLabel.textContent = 'Débil';
                strengthLabel.style.color = '#f59e0b';
            } else if (percentage <= 60) {
                passwordStrengthFill.classList.add('strength-good');
                strengthLabel.textContent = 'Buena';
                strengthLabel.style.color = '#3b82f6';
            } else if (percentage <= 80) {
                passwordStrengthFill.classList.add('strong');
                strengthLabel.textContent = 'Fuerte';
                strengthLabel.style.color = '#10b981';
            } else {
                passwordStrengthFill.classList.add('very-strong');
                strengthLabel.textContent = 'Muy fuerte';
                strengthLabel.style.color = '#10b981';
            }
        }

        // Función para validar coincidencia de contraseñas
        function validatePasswordMatch() {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;

            if (password === '' && confirmPassword === '') {
                passwordMatch.classList.remove('valid', 'invalid');
                passwordMatch.classList.add('pending');
                passwordMatch.innerHTML = '<i class="fas fa-circle"></i><span>Las contraseñas deben coincidir</span>';
                confirmPasswordGroup.classList.remove('password-valid', 'password-invalid');
            } else if (password === confirmPassword && password !== '') {
                passwordMatch.classList.remove('pending', 'invalid');
                passwordMatch.classList.add('valid');
                passwordMatch.innerHTML = '<i class="fas fa-check-circle"></i><span>Las contraseñas coinciden</span>';
                confirmPasswordGroup.classList.remove('password-invalid');
                confirmPasswordGroup.classList.add('password-valid');
            } else {
                passwordMatch.classList.remove('pending', 'valid');
                passwordMatch.classList.add('invalid');
                passwordMatch.innerHTML = '<i class="fas fa-times-circle"></i><span>Las contraseñas no coinciden</span>';
                confirmPasswordGroup.classList.remove('password-valid');
                confirmPasswordGroup.classList.add('password-invalid');
            }
        }

        // Función para validar el formulario completo
        function validateForm() {
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            const validations = validatePassword(password);
            const allValid = Object.values(validations).every(v => v) && password === confirmPassword && password !== '';
            
            submitBtn.disabled = !allValid;
            return allValid;
        }

        // Event listeners
        passwordInput.addEventListener('input', function() {
            const validations = validatePassword(this.value);
            updatePasswordValidation(validations);
            validatePasswordMatch();
            validateForm();
        });

        confirmPasswordInput.addEventListener('input', function() {
            validatePasswordMatch();
            validateForm();
        });

        // Validar al enviar el formulario
        document.getElementById('addUserForm').addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
                alert('Por favor, complete todos los campos requeridos y asegúrese de que la contraseña cumple con todos los criterios.');
            }
        });

        // Botón cancelar
        document.getElementById('cancelAddUser').addEventListener('click', function() {
            if (confirm('¿Estás seguro de cancelar? Los datos ingresados se perderán.')) {
                history.back();
            }
        });

        // Validación inicial
        validateForm();
    });
</script>
@endpush