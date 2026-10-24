{{-- resources/views/auth/login.blade.php --}}

@extends('layouts.guest')

@section('title', 'Iniciar sesión - Rumbero Extremo')

@section('content')
    <div class="px-auth-head">
        <span class="px-auth-head__eyebrow">Bienvenido de vuelta</span>
        <h1>Inicia sesión</h1>
        <p>Ingresa tus credenciales para continuar la fiesta.</p>
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

    <form method="POST" action="{{ url('/login') }}">
        @csrf
        <input type="hidden" name="web_request" value="1">

        <div class="px-field">
            <label for="email">Correo electrónico</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                class="px-input @error('email') is-invalid @enderror" placeholder="tu.correo@rumbero.com" required
                autofocus autocomplete="username">
            @error('email')
                <span class="px-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="px-field">
            <label for="password">Contraseña</label>
            <input id="password" type="password" name="password"
                class="px-input @error('password') is-invalid @enderror" placeholder="••••••••" required
                autocomplete="current-password">
            @error('password')
                <span class="px-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="px-auth__row">
            <label class="px-auth__check" for="remember_me">
                <input type="checkbox" id="remember_me" name="remember">
                <span>Recuérdame</span>
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="px-auth__link">¿Olvidaste tu contraseña?</a>
            @endif
        </div>

        <button type="submit" class="px-btn px-btn--primary px-btn--block">
            <i class="fa-solid fa-right-to-bracket"></i> Entrar
        </button>
    </form>

    <div class="px-auth__sep">o</div>

    <p class="px-auth__foot">
        ¿Todavía no tienes cuenta?
        <a href="{{ route('register') }}">Regístrate gratis</a>
    </p>
@endsection
