@extends('layouts.app') {{-- Asegúrate de que esto apunte a tu layout principal --}}

@section('title', 'Restablecer Contraseña - Rumbero Extremo') {{-- Título específico para esta página --}}

@section('content')
<div class="login-container"> {{-- Reutilizamos la clase del contenedor de login --}}
    <div class="login-card"> {{-- Reutilizamos la clase de la tarjeta de login --}}
        <div class="login-header">
            {{-- Logo de Rumbero Extremo --}}
            <h2>Establece una nueva contraseña</h2>
            <p>Ingresa tu correo electrónico y define tu nueva contraseña.</p>
        </div>

        <form method="POST" action="{{ route('password.store') }}" class="login-form"> {{-- Reutilizamos la clase del formulario --}}
            @csrf

            {{-- Campo Correo Electrónico --}}
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username"
                       class="form-control @error('email') is-invalid @enderror">
                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            {{-- Campo Nueva Contraseña --}}
            <div class="form-group">
                <label for="password">Nueva Contraseña</label>
                <input id="password" type="password" name="password" required autocomplete="new-password"
                       class="form-control @error('password') is-invalid @enderror">
                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            {{-- Campo Confirmar Nueva Contraseña --}}
            <div class="form-group">
                <label for="password_confirmation">Confirmar Nueva Contraseña</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                       class="form-control"> {{-- No necesita is-invalid aquí porque el error de confirmación es sobre 'password' --}}
                @error('password_confirmation')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            {{-- Botón de Restablecer Contraseña --}}
            <div class="form-actions center-button"> {{-- Usamos 'center-button' para centrarlo --}}
                <button type="submit" class="btn btn-primary btn-block">
                    Restablecer Contraseña
                </button>
            </div>
        </form>

        {{-- Opcional: Volver al Login --}}
        <div class="login-footer">
            <p>¿Prefieres iniciar sesión? <a href="{{ route('login') }}">Volver al Login</a></p>
        </div>
    </div>
</div>
@endsection
