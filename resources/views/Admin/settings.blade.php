@extends('layouts.admin')

@section('title', 'Ajustes de la Cuenta')
@section('page_title_toolbar', 'Ajustes de la Cuenta')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
@endpush

@section('content')
    <div class="account-settings-container">

        {{-- Única tarjeta para todos los ajustes --}}
        <div class="settings-card">
            
            {{-- Sección de Cambiar Contraseña --}}
            <div class="password-change-section">
                <h3 class="section-heading"><i class="fas fa-lock"></i> Cambiar Contraseña</h3>
                <form action="{{ route('admin.password.change') }}" method="POST">
                    @csrf
                    @if (session('success'))
                        <div class="alert alert-success mt-0 mb-4">
                            <i class="fas fa-check-circle"></i> {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger mt-0 mb-4">
                            <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                        </div>
                    @endif
                    <div class="form-group">
                        <label for="current_password">Contraseña Actual:</label>
                        <input type="password" name="current_password" id="current_password" required class="form-control">
                        @error('current_password')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="new_password">Nueva Contraseña:</label>
                        <input type="password" name="new_password" id="new_password" required class="form-control">
                        @error('new_password')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="new_password_confirmation">Confirmar Nueva Contraseña:</label>
                        <input type="password" name="new_password_confirmation" id="new_password_confirmation" required class="form-control">
                    </div>
                    <button type="submit" class="modern-button">
                        <i class="fas fa-save"></i> Actualizar Contraseña
                    </button>
                </form>
            </div>
            
            {{-- Sección de Otras Opciones --}}
            <h3 class="section-heading"><i class="fas fa-sliders-h"></i> Preferencias de la cuenta</h3> {{-- Título para esta sección --}}

            {{-- Autenticación en dos pasos (2FA) --}}
            <div class="setting-item">
                <div class="setting-info">
                    <h3>Autenticación en dos pasos (2FA)</h3>
                    <p>Añade una copia extra de seguridad a tu cuenta.</p>
                </div>
                <div class="action-control">
                    <label class="switch">
                        <input type="checkbox">
                        <span class="slider"></span>
                    </label>
                </div>
            </div>

            {{-- Notificaciones --}}
            <div class="setting-item">
                <div class="setting-info">
                    <h3>Notificaciones</h3>
                    <p>Recibe ofertas sobre eventos, actualizaciones y promociones.</p>
                </div>
                <div class="action-control">
                    <label class="switch">
                        <input type="checkbox" checked>
                        <span class="slider"></span>
                    </label>
                </div>
            </div>

            {{-- Estilo de pantalla --}}
            <div class="setting-item">
                <div class="setting-info">
                    <h3>Estilo de pantalla</h3>
                    <p>Alterna entre el modo claro y el modo oscuro para una mejor visualización.</p>
                </div>
                <div class="action-control">
                    <label class="switch">
                        <input type="checkbox">
                        <span class="slider"></span>
                    </label>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin/settings.js') }}"></script>
@endpush