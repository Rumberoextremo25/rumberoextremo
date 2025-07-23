@extends('layouts.admin')

@section('title', 'Cambiar Contraseña')

@section('page_title_toolbar', 'Cambiar Contraseña')

@push('styles')
    {{-- Asegúrate de que Font Awesome esté cargado en tu layout global, si no, puedes añadirlo aquí --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin/profile.css') }}"> {{-- Ahora apunta a profile.css --}}
@endpush

@section('content')
    <div class="dashboard-container">
        <div class="profile-card password-change-section"> {{-- Reutilizamos la clase de tarjeta para consistencia --}}
            <h3 class="card-title"><i class="fas fa-key me-2"></i> Cambiar Contraseña</h3>

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

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                @method('PUT') {{-- Usamos PUT para actualizar un recurso existente --}}

                <div class="form-group mb-4"> {{-- Usamos form-group para estandarizar con los formularios de promociones --}}
                    <label for="current_password" class="form-label">Contraseña Actual</label>
                    <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                           id="current_password" name="current_password" required autofocus>
                    @error('current_password')
                        <div class="error-message"> {{-- Reutilizamos la clase error-message --}}
                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-group mb-4">
                    <label for="new_password" class="form-label">Nueva Contraseña</label>
                    <input type="password" class="form-control @error('new_password') is-invalid @enderror"
                           id="new_password" name="new_password" required>
                    @error('new_password')
                        <div class="error-message">
                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-group mb-4">
                    <label for="new_password_confirmation" class="form-label">Confirmar Nueva Contraseña</label>
                    <input type="password" class="form-control" id="new_password_confirmation"
                           name="new_password_confirmation" required>
                </div>

                <div class="form-actions d-flex justify-content-end"> {{-- Reutilizamos form-actions --}}
                    <button type="submit" class="submit-btn"> {{-- Reutilizamos submit-btn --}}
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                    <a href="{{ route('profile') }}" class="cancel-link"> {{-- Cambiado a profile.index --}}
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
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