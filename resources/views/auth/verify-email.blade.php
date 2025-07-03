@extends('layouts.app') {{-- Asegúrate de que esto apunte a tu layout principal --}}

@section('title', 'Verificar Correo - Rumbero Extremo') {{-- Título específico para esta página --}}

@section('content')
<div class="login-container"> {{-- Reutilizamos la clase del contenedor de login --}}
    <div class="login-card"> {{-- Reutilizamos la clase de la tarjeta de login --}}
        <div class="login-header">
            {{-- Logo de Rumbero Extremo --}}
            <h2>¡Gracias por registrarte!</h2>
            <p>Antes de comenzar, ¿podrías verificar tu dirección de correo electrónico haciendo clic en el enlace que acabamos de enviarte? Si no recibiste el correo, con gusto te enviaremos otro.</p>
        </div>

        {{-- Mensaje de éxito si el enlace de verificación ha sido reenviado --}}
        @if (session('status') == 'verification-link-sent')
            <div class="alert alert-success">
                Se ha enviado un nuevo enlace de verificación a la dirección de correo electrónico que proporcionaste durante el registro.
            </div>
        @endif

        <div class="form-actions action-buttons"> {{-- Nueva clase para agrupar los botones de acción --}}
            {{-- Formulario para reenviar el correo de verificación --}}
            <form method="POST" action="{{ route('verification.send') }}" class="inline-form">
                @csrf
                <button type="submit" class="btn btn-primary">
                    Reenviar Correo de Verificación
                </button>
            </form>

            {{-- Formulario para cerrar sesión --}}
            <form method="POST" action="{{ route('logout') }}" class="inline-form">
                @csrf
                <button type="submit" class="btn btn-secondary-link"> {{-- Nueva clase para el botón de enlace --}}
                    Cerrar Sesión
                </button>
            </form>
        </div>

        {{-- Pie de página (opcional, si quieres añadir más enlaces o información) --}}
        <div class="login-footer" style="margin-top: 30px;">
            <p>Revisa tu bandeja de entrada o spam.</p>
        </div>
    </div>
</div>
@endsection