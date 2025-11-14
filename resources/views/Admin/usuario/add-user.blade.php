@extends('layouts.admin')

@section('page_title_toolbar', 'Añadir Nuevo Usuario')

@push('styles')
    {{-- Tus estilos actuales se mantienen igual --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Tus estilos CSS actuales se mantienen igual */
        .password-validation {
            margin-top: 0.5rem;
        }
        
        .validation-list {
            list-style: none;
            padding: 0;
            margin: 0.5rem 0;
        }
        
        .validation-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.25rem;
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }
        
        .validation-item i {
            font-size: 0.7rem;
            width: 14px;
            text-align: center;
        }
        
        .validation-item.valid {
            color: #10b981;
        }
        
        .validation-item.invalid {
            color: #ef4444;
        }
        
        .validation-item.pending {
            color: #6b7280;
        }
        
        .password-strength-meter {
            height: 4px;
            background: #e5e7eb;
            border-radius: 2px;
            margin-top: 0.5rem;
            overflow: hidden;
        }
        
        .password-strength-fill {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
            border-radius: 2px;
        }
        
        .strength-weak {
            background: #ef4444;
            width: 25%;
        }
        
        .strength-fair {
            background: #f59e0b;
            width: 50%;
        }
        
        .strength-good {
            background: #3b82f6;
            width: 75%;
        }
        
        .strength-strong {
            background: #10b981;
            width: 100%;
        }
        
        .password-match {
            margin-top: 0.25rem;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .password-match.valid {
            color: #10b981;
        }
        
        .password-match.invalid {
            color: #ef4444;
        }
        
        .password-match.pending {
            color: #6b7280;
        }
        
        .form-group.password-valid input {
            border-color: #10b981;
            background-color: #f0fdf4;
        }
        
        .form-group.password-invalid input {
            border-color: #ef4444;
            background-color: #fef2f2;
        }
    </style>
@endpush

@section('content')
    <div class="add-user-container">
        <div class="form-card">
            <div class="form-header">
                <div class="form-icon-wrapper">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h2>Información del Nuevo Usuario</h2>
            </div>

            <hr class="section-divider">

            {{-- El action del formulario apunta a la ruta para almacenar nuevos usuarios --}}
            <form id="addUserForm" action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="form-grid">
                    {{-- CAMBIO: firstName -> firstname --}}
                    <div class="form-group">
                        <label for="firstname">Nombre:</label>
                        <input type="text" id="firstname" name="firstname" placeholder="Ej: Juan" value="{{ old('firstname') }}" required>
                        @error('firstname') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    {{-- CAMBIO: lastName -> lastname --}}
                    <div class="form-group">
                        <label for="lastname">Apellido:</label>
                        <input type="text" id="lastname" name="lastname" placeholder="Ej: Pérez" value="{{ old('lastname') }}" required>
                        @error('lastname') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label for="email">Correo Electrónico:</label>
                        <input type="email" id="email" name="email" placeholder="ejemplo@dominio.com" value="{{ old('email') }}" required>
                        @error('email') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group password-group">
                        <label for="password">Contraseña:</label>
                        {{-- CAMBIO: minlength="6" -> minlength="8" --}}
                        <input type="password" id="password" name="password" placeholder="Mínimo 8 caracteres" required minlength="8">
                        <div class="password-validation">
                            <div class="password-strength-meter">
                                <div class="password-strength-fill" id="passwordStrengthFill"></div>
                            </div>
                            <ul class="validation-list" id="passwordValidationList">
                                {{-- CAMBIO: 6 caracteres -> 8 caracteres --}}
                                <li class="validation-item pending" id="validationLength">
                                    <i class="fas fa-circle"></i>
                                    <span>Mínimo 8 caracteres</span>
                                </li>
                                <li class="validation-item pending" id="validationUppercase">
                                    <i class="fas fa-circle"></i>
                                    <span>Una letra mayúscula</span>
                                </li>
                                <li class="validation-item pending" id="validationLowercase">
                                    <i class="fas fa-circle"></i>
                                    <span>Una letra minúscula</span>
                                </li>
                                <li class="validation-item pending" id="validationNumber">
                                    <i class="fas fa-circle"></i>
                                    <span>Un número</span>
                                </li>
                                <li class="validation-item pending" id="validationSpecial">
                                    <i class="fas fa-circle"></i>
                                    <span>Un carácter especial</span>
                                </li>
                            </ul>
                        </div>
                        @error('password') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group confirm-password-group">
                        <label for="password_confirmation">Confirmar Contraseña:</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Repite la contraseña" required>
                        <div class="password-match pending" id="passwordMatch">
                            <i class="fas fa-circle"></i>
                            <span>Las contraseñas deben coincidir</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="user_type">Tipo de Usuario:</label>
                        <select id="user_type" name="user_type" required>
                            <option value="">Seleccione un tipo</option>
                            <option value="comun" {{ old('user_type') == 'comun' ? 'selected' : '' }}>Común</option>
                            <option value="aliado" {{ old('user_type') == 'aliado' ? 'selected' : '' }}>Aliado</option>
                            <option value="afiliado" {{ old('user_type') == 'afiliado' ? 'selected' : '' }}>Afiliado</option>
                            <option value="admin" {{ old('user_type') == 'admin' ? 'selected' : '' }}>Administrador</option>
                        </select>
                        @error('user_type') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    {{-- CAMBIO: phone -> phone1 --}}
                    <div class="form-group">
                        <label for="phone1">Teléfono (Opcional):</label>
                        <input type="tel" id="phone1" name="phone1" placeholder="Ej: +58 412 1234567" value="{{ old('phone1') }}">
                        @error('phone1') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label for="status">Estado:</label>
                        <select id="status" name="status" required>
                            <option value="activo" {{ old('status') == 'activo' ? 'selected' : '' }}>Activo</option>
                            <option value="inactivo" {{ old('status') == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                            <option value="pendiente" {{ old('status') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                        </select>
                        @error('status') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group">
                        <label for="registrationDate">Fecha de Registro:</label>
                        <input type="date" id="registrationDate" name="registrationDate" value="{{ old('registrationDate', \Carbon\Carbon::now()->format('Y-m-d')) }}" required>
                        @error('registrationDate') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-group full-width">
                        <label for="notes">Notas Internas (Opcional):</label>
                        <textarea id="notes" name="notes" placeholder="Información adicional sobre el usuario..." rows="3">{{ old('notes') }}</textarea>
                        @error('notes') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="button-group">
                    <button type="button" class="cancel-btn" id="cancelAddUser">
                        <i class="fas fa-times-circle"></i> Cancelar
                    </button>
                    <button type="submit" class="submit-btn" id="submitBtn">
                        <i class="fas fa-user-plus"></i> Añadir Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('password_confirmation');
        const passwordMatch = document.getElementById('passwordMatch');
        const passwordStrengthFill = document.getElementById('passwordStrengthFill');
        const submitBtn = document.getElementById('submitBtn');
        const passwordGroup = document.querySelector('.password-group');
        const confirmPasswordGroup = document.querySelector('.confirm-password-group');

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

        // Función para validar la contraseña - CAMBIO: 6 -> 8
        function validatePassword(password) {
            const validations = {
                length: password.length >= 8, // Cambiado de 6 a 8
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
                    element.innerHTML = '<i class="fas fa-check-circle"></i><span>' + element.textContent + '</span>';
                    validCount++;
                } else {
                    element.classList.remove('pending', 'valid');
                    element.classList.add('invalid');
                    element.innerHTML = '<i class="fas fa-times-circle"></i><span>' + element.textContent + '</span>';
                }
            });

            // Actualizar medidor de fuerza
            updateStrengthMeter(validCount, totalValidations);
            
            // Actualizar estado del grupo de contraseña
            updatePasswordGroupState(validCount, totalValidations);
        }

        // Función para actualizar el medidor de fuerza
        function updateStrengthMeter(validCount, totalValidations) {
            const percentage = (validCount / totalValidations) * 100;
            passwordStrengthFill.className = 'password-strength-fill';

            if (percentage <= 25) {
                passwordStrengthFill.classList.add('strength-weak');
            } else if (percentage <= 50) {
                passwordStrengthFill.classList.add('strength-fair');
            } else if (percentage <= 75) {
                passwordStrengthFill.classList.add('strength-good');
            } else {
                passwordStrengthFill.classList.add('strength-strong');
            }
        }

        // Función para actualizar el estado del grupo de contraseña
        function updatePasswordGroupState(validCount, totalValidations) {
            if (validCount === totalValidations) {
                passwordGroup.classList.remove('password-invalid');
                passwordGroup.classList.add('password-valid');
            } else if (passwordInput.value.length > 0) {
                passwordGroup.classList.remove('password-valid');
                passwordGroup.classList.add('password-invalid');
            } else {
                passwordGroup.classList.remove('password-valid', 'password-invalid');
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
            history.back();
        });

        // Validación inicial
        validateForm();
    });
</script>
@endpush