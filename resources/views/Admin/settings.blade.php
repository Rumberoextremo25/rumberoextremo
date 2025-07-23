@extends('layouts.admin')

@section('title', 'Cambiar Contraseña - Rumbero Extremo')
@section('page_title_toolbar', 'Cambiar Contraseña') {{-- Usando page_title_toolbar si tu layout lo soporta --}}

@push('styles') {{-- Agregamos el CSS específico de esta vista --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    {{-- Enlazamos al nuevo archivo CSS para los ajustes --}}
    <link rel="stylesheet" href="{{ asset('css/admin/settings.css') }}">
@endpush

@section('content')
    <div class="account-settings-container">
        <h1>Ajustes de la Cuenta</h1>

        <div class="settings-card password-change-form">
            <h3><i class="fas fa-lock"></i> Cambiar Contraseña</h3>

            <form action="{{ route('admin.password.change') }}" method="POST">
                @csrf

                {{-- BLOQUE PARA MOSTRAR MENSAJES FLASH --}}
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
                {{-- FIN BLOQUE MENSAJES FLASH --}}

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
    </div>
@endsection

@push('scripts') {{-- Agregamos el JS específico de esta vista --}}
    <script src="{{ asset('js/admin/settings.js') }}"></script>
@endpush