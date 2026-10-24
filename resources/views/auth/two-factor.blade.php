@extends('layouts.guest')

@section('title', 'Verificación en dos pasos - Rumbero Extremo')

@section('content')
    @php
        $supportEmail = \App\Models\Setting::get('support_email', 'soporte@rumberoextremo.com');
    @endphp

    <div class="px-auth-head">
        <span class="px-auth-head__eyebrow">
            <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
            Seguridad
        </span>
        <h1>Verificación en dos pasos</h1>
        <p>Ingresa el código de 6 dígitos de tu aplicación de autenticación.</p>
    </div>

    @if ($errors->any())
        <div class="px-alert px-alert--error" role="alert">
            <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <div class="px-auth__shield" aria-hidden="true">
        <i class="fa-solid fa-shield-halved"></i>
    </div>

    <form method="POST" action="{{ route('2fa.verify.post') }}" id="twofaForm" novalidate>
        @csrf

        <div class="px-field">
            <label for="digit1">Código de verificación</label>

            <div class="px-otp" id="otpInputs" role="group" aria-label="Código de verificación de 6 dígitos">
                <input type="text" id="digit1" class="px-otp__digit" inputmode="numeric" pattern="[0-9]*"
                    maxlength="1" autocomplete="one-time-code" aria-label="Dígito 1" autofocus>
                <input type="text" id="digit2" class="px-otp__digit" inputmode="numeric" pattern="[0-9]*"
                    maxlength="1" aria-label="Dígito 2">
                <input type="text" id="digit3" class="px-otp__digit" inputmode="numeric" pattern="[0-9]*"
                    maxlength="1" aria-label="Dígito 3">
                <input type="text" id="digit4" class="px-otp__digit" inputmode="numeric" pattern="[0-9]*"
                    maxlength="1" aria-label="Dígito 4">
                <input type="text" id="digit5" class="px-otp__digit" inputmode="numeric" pattern="[0-9]*"
                    maxlength="1" aria-label="Dígito 5">
                <input type="text" id="digit6" class="px-otp__digit" inputmode="numeric" pattern="[0-9]*"
                    maxlength="1" aria-label="Dígito 6">
            </div>

            <input type="hidden" name="code" id="code" value="{{ old('code') }}">

            <p class="px-auth__note">
                <i class="fa-regular fa-lightbulb" aria-hidden="true"></i>
                Abre tu aplicación de autenticación y copia el código de 6 dígitos.
            </p>
        </div>

        <button type="submit" class="px-btn px-btn--primary px-btn--block" id="verifyBtn" disabled>
            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
            <span>Verificar código</span>
        </button>
    </form>

    <p class="px-auth__foot">
        <a href="{{ route('login') }}">
            <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
            Volver al inicio de sesión
        </a>
    </p>

    <p class="px-auth__note px-auth__note--center">
        ¿Problemas para entrar? Escríbenos a
        <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a>
    </p>
@endsection

@push('scripts')
    <script>
        (function () {
            var form = document.getElementById('twofaForm');
            var inputs = Array.prototype.slice.call(document.querySelectorAll('.px-otp__digit'));
            var hidden = document.getElementById('code');
            var button = document.getElementById('verifyBtn');

            if (!form || !inputs.length || !hidden || !button) {
                return;
            }

            function updateCode() {
                var code = inputs.map(function (input) {
                    return input.value.replace(/\D/g, '').slice(0, 1);
                }).join('');

                hidden.value = code;
                button.disabled = code.length !== 6;

                inputs.forEach(function (input) {
                    input.classList.toggle('is-filled', input.value !== '');
                });
            }

            function focusFirstEmpty() {
                var empty = inputs.find(function (input) {
                    return input.value === '';
                });

                (empty || inputs[inputs.length - 1]).focus();
            }

            inputs.forEach(function (input, index) {
                input.addEventListener('input', function () {
                    var digits = input.value.replace(/\D/g, '');

                    // Autocompletado del sistema (iOS/Android) puede pegar el
                    // código completo en la primera caja.
                    if (digits.length > 1) {
                        digits.slice(0, inputs.length - index).split('').forEach(function (digit, offset) {
                            inputs[index + offset].value = digit;
                        });
                        inputs[Math.min(index + digits.length, inputs.length) - 1].focus();
                        updateCode();
                        return;
                    }

                    var value = digits.slice(-1);
                    input.value = value;

                    if (value && index < inputs.length - 1) {
                        inputs[index + 1].focus();
                    }

                    updateCode();
                });

                input.addEventListener('keydown', function (event) {
                    if (event.key === 'Backspace' && !input.value && index > 0) {
                        inputs[index - 1].focus();
                        inputs[index - 1].value = '';
                        updateCode();
                        event.preventDefault();
                    }

                    if (event.key === 'ArrowLeft' && index > 0) {
                        inputs[index - 1].focus();
                        event.preventDefault();
                    }

                    if (event.key === 'ArrowRight' && index < inputs.length - 1) {
                        inputs[index + 1].focus();
                        event.preventDefault();
                    }
                });

                input.addEventListener('paste', function (event) {
                    event.preventDefault();

                    var text = (event.clipboardData || window.clipboardData).getData('text');
                    var digits = text.replace(/\D/g, '').slice(0, 6);

                    if (!digits) {
                        return;
                    }

                    digits.split('').forEach(function (digit, digitIndex) {
                        if (inputs[digitIndex]) {
                            inputs[digitIndex].value = digit;
                        }
                    });

                    inputs[Math.min(digits.length, inputs.length) - 1].focus();
                    updateCode();
                });
            });

            form.addEventListener('submit', function (event) {
                var code = inputs.map(function (input) {
                    return input.value;
                }).join('');

                if (code.length !== 6) {
                    event.preventDefault();
                    focusFirstEmpty();
                }
            });

            // Si el navegador conserva un código anterior, se reparte en las cajas.
            if (hidden.value) {
                hidden.value.replace(/\D/g, '').slice(0, 6).split('').forEach(function (digit, index) {
                    if (inputs[index]) {
                        inputs[index].value = digit;
                    }
                });
            }

            updateCode();
        }());
    </script>
@endpush
