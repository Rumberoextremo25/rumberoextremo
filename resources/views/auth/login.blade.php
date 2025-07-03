@extends('layouts.app') {{-- Asegúrate de que esto apunta a tu layout principal --}}

@section('title', 'Iniciar Sesión - Rumbero Extremo') {{-- Título específico para esta página --}}

@section('content')
<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            {{-- Logo de Rumbero Extremo --}}
            <h2>¡Bienvenido de nuevo!</h2>
            <p>Ingresa tus credenciales para acceder a tu cuenta.</p>
        </div>

        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="login-form">
            @csrf

            {{-- Campo Email --}}
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
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
                <input id="password" type="password" name="password" required autocomplete="current-password"
                       class="form-control @error('password') is-invalid @enderror">
                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            {{-- Recordarme y Olvidé mi Contraseña --}}
            <div class="form-options">
                <label for="remember_me" class="remember-me">
                    <input id="remember_me" type="checkbox" name="remember">
                    <span>Recuérdame</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="forgot-password-link" href="{{ route('password.request') }}">
                        ¿Olvidaste tu contraseña?
                    </a>
                @endif
            </div>

            {{-- Botón de Iniciar Sesión --}}
            <button type="submit" class="btn btn-primary btn-block">
                Iniciar Sesión
            </button>
        </form>

        {{-- Opciones adicionales: Registrarse --}}
        <div class="login-footer">
            <p>¿No tienes una cuenta? <a href="{{ route('register') }}">Regístrate aquí</a></p>
        </div>
    </div>
</div>
@endsection