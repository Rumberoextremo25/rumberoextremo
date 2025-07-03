@extends('layouts.app') {{-- Asegúrate de que esto apunta a tu layout principal --}}

@section('title', 'Recuperar Contraseña - Rumbero Extremo') {{-- Título específico para esta página --}}

@section('content')
<div class="login-container"> {{-- Reutilizamos la clase del contenedor de login --}}
    <div class="login-card"> {{-- Reutilizamos la clase de la tarjeta de login --}}
        <div class="login-header">
            {{-- Logo de Rumbero Extremo --}}
            <h2>¿Olvidaste tu contraseña?</h2>
            <p>Ingresa tu correo electrónico y te enviaremos un enlace para restablecerla.</p>
        </div>

        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="login-form"> {{-- Reutilizamos la clase del formulario --}}
            @csrf

            {{-- Campo Correo Electrónico --}}
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="form-control @error('email') is-invalid @enderror">
                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            {{-- Botón de Enviar Enlace --}}
            <div class="form-actions" style="justify-content: center;"> {{-- Alineamos el botón al centro --}}
                <button type="submit" class="btn btn-primary btn-block">
                    Enviar Enlace de Restablecimiento
                </button>
            </div>
        </form>

        {{-- Opciones adicionales: Volver al Login --}}
        <div class="login-footer">
            <p>¿Recordaste tu contraseña? <a href="{{ route('login') }}">Volver al Login</a></p>
        </div>
    </div>
</div>
@endsection
