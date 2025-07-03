@extends('layouts.app') {{-- Asegúrate de que esto apunte a tu layout principal --}}

@section('title', 'Confirmar Contraseña - Rumbero Extremo') {{-- Título específico para esta página --}}

@section('content')
<div class="login-container"> {{-- Reutilizamos la clase del contenedor de login --}}
    <div class="login-card"> {{-- Reutilizamos la clase de la tarjeta de login --}}
        <div class="login-header">
            {{-- Logo de Rumbero Extremo --}}
            <h2>Confirma tu identidad</h2>
            <p>Esta es un área segura de la aplicación. Por favor, confirma tu contraseña para continuar.</p>
        </div>

        <form method="POST" action="{{ route('password.confirm') }}" class="login-form"> {{-- Reutilizamos la clase del formulario --}}
            @csrf

            {{-- Campo Contraseña --}}
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input id="password" type="password" name="password" required autocomplete="current-password"
                       class="form-control @error('password') is-invalid @enderror">
                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            {{-- Botón de Confirmar --}}
            <div class="form-actions center-button"> {{-- Usamos 'center-button' para centrarlo --}}
                <button type="submit" class="btn btn-primary btn-block">
                    Confirmar
                </button>
            </div>
        </form>

        {{-- Opcional: Enlace para Olvidé Contraseña si el usuario no recuerda --}}
        <div class="login-footer">
            @if (Route::has('password.request'))
                <p>¿Olvidaste tu contraseña? <a href="{{ route('password.request') }}">Restablécela aquí</a></p>
            @endif
        </div>
    </div>
</div>
@endsection
