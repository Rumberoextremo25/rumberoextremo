{{-- resources/views/auth/register.blade.php --}}

@extends('layouts.guest')

@section('title', 'Crear cuenta - Rumbero Extremo')

@section('content')
    <div class="px-auth-head">
        <span class="px-auth-head__eyebrow">Únete a la comunidad</span>
        <h1>Crea tu cuenta</h1>
        <p>Elige cómo quieres empezar: como Rumbero o como Aliado comercial.</p>
    </div>

    @if ($errors->any())
        <div class="px-alert px-alert--error">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span>
                Revisa los datos:
                <ul style="margin:.35rem 0 0;padding-left:1.1rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </span>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="px-field">
            <label for="name">Nombre completo <span class="req">*</span></label>
            <input id="name" type="text" name="name" value="{{ old('name') }}"
                class="px-input @error('name') is-invalid @enderror" placeholder="Nombre y apellido" required autofocus
                autocomplete="name">
            @error('name')
                <span class="px-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="px-field">
            <label for="email">Correo electrónico <span class="req">*</span></label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                class="px-input @error('email') is-invalid @enderror" placeholder="tucorreo@ejemplo.com" required
                autocomplete="username">
            @error('email')
                <span class="px-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="px-field">
            <label for="user_type">¿Cómo quieres registrarte? <span class="req">*</span></label>
            <select id="user_type" name="user_type" class="px-input @error('user_type') is-invalid @enderror" required>
                <option value="" disabled {{ old('user_type') ? '' : 'selected' }}>Selecciona una opción</option>
                <option value="user" {{ old('user_type') === 'user' ? 'selected' : '' }}>
                    🔥 Usuario Rumbero — disfruta descuentos
                </option>
                <option value="partner" {{ old('user_type') === 'partner' ? 'selected' : '' }}>
                    🏢 Aliado — suma tu local o marca
                </option>
            </select>
            @error('user_type')
                <span class="px-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="px-field">
            <label for="password">Contraseña <span class="req">*</span></label>
            <input id="password" type="password" name="password"
                class="px-input @error('password') is-invalid @enderror" placeholder="Mínimo 8 caracteres" required
                autocomplete="new-password">
            @error('password')
                <span class="px-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="px-field">
            <label for="password_confirmation">Repite la contraseña <span class="req">*</span></label>
            <input id="password_confirmation" type="password" name="password_confirmation" class="px-input"
                placeholder="Repite tu contraseña" required autocomplete="new-password">
        </div>

        <p class="px-auth__hint">
            <strong>Importante:</strong> si te registras como <strong>Rumbero</strong>, tu cuenta se crea al
            instante y puedes entrar ya. Si te registras como <strong>Aliado</strong>, tu solicitud se envía al
            equipo de ventas (te llega un correo de confirmación) y ellos te contactan para crear tu perfil.
        </p>

        <button type="submit" class="px-btn px-btn--primary px-btn--block">
            <i class="fa-solid fa-user-plus"></i> Crear mi cuenta
        </button>
    </form>

    <p class="px-auth__foot">
        ¿Ya tienes cuenta?
        <a href="{{ route('login') }}">Inicia sesión</a>
    </p>

    <p class="px-auth__legal" style="margin-top:.85rem;">
        Al registrarte aceptas los <a href="{{ url('/terms') }}">términos</a> y la
        <a href="{{ url('/privacy') }}">política de privacidad</a>.
    </p>
@endsection
