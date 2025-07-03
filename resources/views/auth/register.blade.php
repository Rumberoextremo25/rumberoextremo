@extends('layouts.app') {{-- Asegúrate de que esto apunta a tu layout principal --}}

@section('title', 'Registro - Rumbero Extremo') {{-- Título específico para esta página --}}

@section('content')
<div class="login-container"> {{-- Reutilizamos la clase del contenedor de login --}}
    <div class="login-card"> {{-- Reutilizamos la clase de la tarjeta de login --}}
        <div class="login-header">
            {{-- Logo de Rumbero Extremo --}}
            <h2>Crea tu cuenta</h2>
            <p>Únete a la comunidad de Rumbero Extremo.</p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="login-form"> {{-- Reutilizamos la clase del formulario --}}
            @csrf

            {{-- Campo Nombre --}}
            <div class="form-group">
                <label for="name">Nombre Completo</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                       class="form-control @error('name') is-invalid @enderror">
                @error('name')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            {{-- Campo Correo Electrónico --}}
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                       class="form-control @error('email') is-invalid @enderror">
                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            {{-- Campo Contraseña --}}
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input id="password" type="password" name="password" required autocomplete="new-password"
                       class="form-control @error('password') is-invalid @enderror">
                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            {{-- Campo Confirmar Contraseña --}}
            <div class="form-group">
                <label for="password_confirmation">Confirmar Contraseña</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                       class="form-control"> {{-- No necesita is-invalid aquí porque el error de confirmación es sobre 'password' --}}
                @error('password_confirmation')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            {{-- Botón de Registrarse y Enlace a Login --}}
            <div class="form-actions"> {{-- Nueva clase para agrupar los botones/enlaces --}}
                <a class="already-registered-link" href="{{ route('login') }}">
                    ¿Ya estás registrado?
                </a>

                <button type="submit" class="btn btn-primary">
                    Registrarse
                </button>
            </div>
        </form>

        {{-- Pie de página de registro (opcional) --}}
        <div class="login-footer">
            <p>Al registrarte, aceptas nuestros <a href="#">Términos de Servicio</a> y <a href="#">Política de Privacidad</a>.</p>
        </div>
    </div>
</div>
@endsection
