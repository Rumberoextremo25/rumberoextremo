@extends('layouts.guest')

@section('title', 'Verificación en dos pasos - Rumbero Extremo')

@section('content')
<div class="login-wrapper">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    
    {{-- Elementos decorativos de fondo --}}
    <div class="bg-particles"></div>
    <div class="bg-glow"></div>
    
    {{-- Tarjeta de verificación 2FA --}}
    <div class="login-card-modern">
        
        {{-- Header con icono --}}
        <div class="login-header-modern">
            <div class="logo-container">
                <div class="twofa-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="48" height="48">
                        <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" stroke-width="2"/>
                    </svg>
                </div>
            </div>
            <h1 class="login-title">
                <span class="title-extremo">VERIFICACIÓN</span>
                <span class="title-welcome">en dos pasos</span>
            </h1>
            <p class="login-subtitle">Ingresa el código de 6 dígitos de tu aplicación de autenticación</p>
        </div>
        
        {{-- Mensaje de error --}}
        @if ($errors->any())
            <div class="alert-modern alert-error">
                <svg class="alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ $errors->first('code') }}
            </div>
        @endif
        
        {{-- Formulario de verificación --}}
        <form method="POST" action="{{ route('2fa.verify.post') }}" class="form-modern" id="twofaForm">
            @csrf
            
            {{-- Campo de código 2FA --}}
            <div class="form-group-modern">
                <label class="form-label">
                    <svg class="label-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"/>
                    </svg>
                    Código de verificación
                </label>
                <div class="twofa-inputs">
                    <input type="text" maxlength="1" class="twofa-digit" id="digit1" autofocus>
                    <input type="text" maxlength="1" class="twofa-digit" id="digit2">
                    <input type="text" maxlength="1" class="twofa-digit" id="digit3">
                    <input type="text" maxlength="1" class="twofa-digit" id="digit4">
                    <input type="text" maxlength="1" class="twofa-digit" id="digit5">
                    <input type="text" maxlength="1" class="twofa-digit" id="digit6">
                </div>
                <input type="hidden" name="code" id="code">
                <p class="help-text">Abre tu aplicación de autenticación y copia el código de 6 dígitos</p>
            </div>
            
            {{-- Botón de verificación --}}
            <button type="submit" class="btn-login-modern" id="verifyBtn">
                <span>Verificar código</span>
                <svg class="btn-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </button>
            
            {{-- Link para volver al login --}}
            <div class="register-link-modern">
                <p><a href="{{ route('login') }}">← Volver al inicio de sesión</a></p>
            </div>
        </form>
    </div>
</div>

<style>
.twofa-inputs {
    display: flex;
    gap: 0.5rem;
    justify-content: center;
    margin: 1.5rem 0 0.5rem;
}

.twofa-digit {
    width: 50px;
    height: 60px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    text-align: center;
    font-size: 1.5rem;
    font-weight: 600;
    background: white;
    transition: all 0.2s;
}

.twofa-digit:focus {
    outline: none;
    border-color: #A601B3;
    box-shadow: 0 0 0 3px rgba(166, 1, 179, 0.1);
}

.help-text {
    text-align: center;
    font-size: 0.8rem;
    color: #64748b;
    margin-top: 0.5rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const digits = document.querySelectorAll('.twofa-digit');
    const codeInput = document.getElementById('code');
    const form = document.getElementById('twofaForm');
    const verifyBtn = document.getElementById('verifyBtn');

    digits.forEach((digit, index) => {
        digit.addEventListener('input', function() {
            if (this.value.length === 1 && index < digits.length - 1) {
                digits[index + 1].focus();
            }
            updateCode();
        });

        digit.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && this.value.length === 0 && index > 0) {
                digits[index - 1].focus();
            }
        });

        digit.addEventListener('paste', function(e) {
            e.preventDefault();
            const paste = e.clipboardData.getData('text');
            const numbers = paste.replace(/\D/g, '').slice(0, 6);
            
            for (let i = 0; i < numbers.length; i++) {
                if (digits[i]) {
                    digits[i].value = numbers[i];
                }
            }
            
            if (numbers.length === 6) {
                digits[5].focus();
            }
            updateCode();
        });
    });

    function updateCode() {
        const code = Array.from(digits).map(d => d.value).join('');
        codeInput.value = code;
        
        // Habilitar/deshabilitar botón según si el código está completo
        if (code.length === 6) {
            verifyBtn.classList.add('ready');
        } else {
            verifyBtn.classList.remove('ready');
        }
    }

    form.addEventListener('submit', function(e) {
        const code = Array.from(digits).map(d => d.value).join('');
        if (code.length !== 6) {
            e.preventDefault();
            alert('Por favor ingresa el código completo de 6 dígitos');
        }
    });
});
</script>
@endsection