{{-- resources/views/auth/forgot-password.blade.php --}}

@extends('layouts.guest')

@section('title', 'Recuperar contraseña - Rumbero Extremo')

@section('content')
    <div class="px-auth-head">
        <span class="px-auth-head__eyebrow">Recuperar acceso</span>
        <h1>¿Olvidaste tu contraseña?</h1>
        <p>Escribe tu correo y te enviaremos un enlace para restablecerla.</p>
    </div>

    @if (session('status'))
        <div class="px-alert px-alert--success">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="px-alert px-alert--error">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="px-field">
            <label for="email">Correo electrónico</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                class="px-input @error('email') is-invalid @enderror" placeholder="tucorreo@ejemplo.com" required
                autofocus autocomplete="username">
            @error('email')
                <span class="px-error">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="px-btn px-btn--primary px-btn--block">
            <i class="fa-solid fa-paper-plane"></i> Enviar enlace
        </button>
    </form>

    <p class="px-auth__hint" style="margin-top:1rem;">
        El enlace caduca en 60 minutos. Si no lo ves, revisa la carpeta de spam.
    </p>

    <p class="px-auth__foot">
        ¿Recordaste tu contraseña?
        <a href="{{ route('login') }}">Volver al login</a>
    </p>
@endsection
