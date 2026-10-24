@extends('layouts.admin')

@section('title', 'Configuración')

@section('page_title_toolbar', 'Configuración')

@section('content')
    @php
        $pushActive = $user->push_enabled && $pushDevices > 0;
        $unusedBackupCodes = [];
        if ($user->two_factor_recovery_codes) {
            $backupCodes = json_decode($user->two_factor_recovery_codes, true);
            $unusedBackupCodes = is_array($backupCodes)
                ? array_values(array_filter($backupCodes, fn ($code) => !isset($code['used']) || !$code['used']))
                : [];
        }
    @endphp

    <div class="cm-page st-page">

        {{-- ============================ CABECERA ============================ --}}
        <div class="page-header-block">
            <div>
                <h1 class="title-main">Configuración de <span class="title-accent">cuenta</span></h1>
                <p class="page-subtitle">
                    Soporte, seguridad, notificaciones y preferencias del panel.
                </p>
            </div>

            <div class="header-actions">
                <span class="cm-chip"><i class="fa-solid fa-shield-halved"></i>
                    {{ $user->role === 'admin' ? 'Administrador' : 'Aliado' }}
                </span>
                <a href="{{ route('profile') }}" class="btn btn-secondary">
                    <i class="fa-regular fa-user"></i> Mi perfil
                </a>
            </div>
        </div>

        <div class="alert-container" id="alertContainer"></div>

        <div class="st-grid">

            {{-- ============================ COLUMNA PRINCIPAL ============================ --}}
            <div class="st-col">

                {{-- Soporte del chat --}}
                <section class="cf-card" id="soporte">
                    <div class="cf-card__head">
                        <span class="cf-card__icon"><i class="fa-solid fa-headset"></i></span>
                        <div class="cf-card__head-text">
                            <h2>Soporte del chat</h2>
                            <p>A dónde llegan los mensajes y cómo se responde a los usuarios.</p>
                        </div>
                    </div>

                    <div class="cf-card__body">
                        <div class="cf-grid">
                            <div class="form-group">
                                <label for="support_email">Correo que recibe los avisos <span class="required-star">*</span></label>
                                <input type="email" id="support_email" name="support_email" class="form-control"
                                    value="{{ \App\Models\Setting::get('support_email') }}"
                                    placeholder="soporte@tudominio.com">
                                <span class="field-hint">
                                    <i class="fa-solid fa-circle-info"></i>
                                    Cada mensaje que la IA no pueda responder llegará a este buzón.
                                </span>
                            </div>

                            <div class="form-group">
                                <label for="support_whatsapp">WhatsApp de soporte</label>
                                <input type="tel" id="support_whatsapp" name="support_whatsapp" class="form-control"
                                    value="{{ \App\Models\Setting::get('support_whatsapp') }}"
                                    placeholder="584124992125">
                                <span class="field-hint">
                                    <i class="fa-solid fa-circle-info"></i>
                                    Con código de país, sin espacios ni símbolos.
                                </span>
                            </div>
                        </div>

                        <label class="cf-switch" for="chat_ai_enabled" style="margin-top:.5rem;">
                            <input type="checkbox" id="chat_ai_enabled"
                                {{ \App\Models\Setting::get('chat_ai_enabled', '1') === '1' ? 'checked' : '' }}>
                            <span class="cf-switch__track" aria-hidden="true"></span>
                            <span class="cf-switch__text">
                                <strong>Responder con IA</strong>
                                <span>Si está activo y hay API key, la IA contesta; si falla o el usuario pide un asesor,
                                    pasa al Centro de Mensajes.</span>
                            </span>
                        </label>

                        <div class="cf-actions" style="justify-content:space-between;">
                            <span class="support-status" id="support-status"></span>

                            <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                                <button type="button" class="btn btn-secondary" id="btn-test-ai">
                                    <i class="fa-solid fa-vial"></i> Probar IA
                                </button>
                                <button type="button" class="btn btn-primary" id="btn-save-support">
                                    <i class="fa-solid fa-floppy-disk"></i> Guardar soporte
                                </button>
                            </div>
                        </div>

                        <div class="support-ai-result" id="support-ai-result" hidden></div>
                    </div>
                </section>

                {{-- Seguridad --}}
                <section class="cf-card">
                    <div class="cf-card__head">
                        <span class="cf-card__icon"><i class="fa-solid fa-lock"></i></span>
                        <div class="cf-card__head-text">
                            <h2>Seguridad</h2>
                            <p>Cambia tu contraseña periódicamente.</p>
                        </div>
                    </div>

                    <div class="cf-card__body">
                        <form action="{{ route('admin.settings.change-password') }}" method="POST" id="passwordForm">
                            @csrf

                            <div class="form-group">
                                <label for="current_password">Contraseña actual <span class="required-star">*</span></label>
                                <div class="uf-pass">
                                    <input type="password" name="current_password" id="current_password" required
                                        class="form-control" placeholder="Tu contraseña actual">
                                    <button type="button" class="uf-pass__toggle toggle-password"
                                        data-target="current_password" aria-label="Mostrar contraseña">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>
                                </div>
                                @error('current_password')
                                    <span class="error-message"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span>
                                @enderror
                            </div>

                            <div class="cf-grid">
                                <div class="form-group">
                                    <label for="new_password">Nueva contraseña <span class="required-star">*</span></label>
                                    <div class="uf-pass">
                                        <input type="password" name="new_password" id="new_password" required
                                            class="form-control" placeholder="Mínimo 8 caracteres">
                                        <button type="button" class="uf-pass__toggle toggle-password"
                                            data-target="new_password" aria-label="Mostrar contraseña">
                                            <i class="fa-regular fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="password-strength" id="passwordStrength"></div>
                                    @error('new_password')
                                        <span class="error-message"><i class="fa-solid fa-circle-exclamation"></i> {{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="new_password_confirmation">Confirmar contraseña <span class="required-star">*</span></label>
                                    <div class="uf-pass">
                                        <input type="password" name="new_password_confirmation"
                                            id="new_password_confirmation" required class="form-control"
                                            placeholder="Repite la nueva contraseña">
                                        <button type="button" class="uf-pass__toggle toggle-password"
                                            data-target="new_password_confirmation" aria-label="Mostrar contraseña">
                                            <i class="fa-regular fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="password-match" id="passwordMatch"></div>
                                </div>
                            </div>

                            <div class="cf-actions" style="justify-content:flex-end;">
                                <button type="submit" class="btn btn-primary" id="submitButton">
                                    <i class="fa-solid fa-rotate-right"></i> Actualizar contraseña
                                </button>
                            </div>
                        </form>
                    </div>
                </section>

                {{-- Preferencias --}}
                <section class="cf-card">
                    <div class="cf-card__head">
                        <span class="cf-card__icon"><i class="fa-solid fa-sliders"></i></span>
                        <div class="cf-card__head-text">
                            <h2>Preferencias</h2>
                            <p>Seguridad y avisos de tu cuenta.</p>
                        </div>
                    </div>

                    <div class="cf-card__body">
                        <div class="st-pref">
                            <span class="st-pref__icon"><i class="fa-solid fa-shield"></i></span>
                            <div class="st-pref__text">
                                <strong>Autenticación en dos pasos</strong>
                                <p>Añade una capa extra de seguridad.</p>
                            </div>
                            <label class="cf-switch cf-switch--bare" for="twoFactorToggle">
                                <input type="checkbox" id="twoFactorToggle"
                                    {{ $user->two_factor_enabled ? 'checked' : '' }}>
                                <span class="cf-switch__track" aria-hidden="true"></span>
                            </label>
                        </div>

                        <div class="st-pref">
                            <span class="st-pref__icon"><i class="fa-solid fa-bell"></i></span>
                            <div class="st-pref__text">
                                <strong>Notificaciones</strong>
                                <p>Ofertas, actualizaciones y avisos del panel.</p>
                            </div>
                            <label class="cf-switch cf-switch--bare" for="notificationsToggle">
                                <input type="checkbox" id="notificationsToggle"
                                    data-url="{{ route('admin.settings.update-notifications') }}"
                                    {{ $user->notifications_enabled ? 'checked' : '' }}>
                                <span class="cf-switch__track" aria-hidden="true"></span>
                            </label>
                        </div>

                        <div class="st-pref" data-rx-push data-state="{{ $pushActive ? 'active' : 'off' }}"
                            data-public-key="{{ $vapidPublicKey }}"
                            data-sw-url="{{ asset('sw.js') }}"
                            data-subscribe-url="{{ route('admin.push.subscribe') }}"
                            data-unsubscribe-url="{{ route('admin.push.unsubscribe') }}"
                            data-test-url="{{ route('admin.push.test') }}">
                            <span class="st-pref__icon"><i class="fa-solid fa-mobile-screen-button"></i></span>
                            <div class="st-pref__text">
                                <strong>Notificaciones push</strong>
                                <p>Recíbelas aunque el navegador esté cerrado.</p>

                                <div class="push-status">
                                    <span class="push-dot" aria-hidden="true"></span>
                                    <span data-rx-push-status>
                                        {{ $pushActive ? 'Activadas en ' . $pushDevices . ' dispositivo(s)' : 'Desactivadas en este dispositivo' }}
                                    </span>
                                </div>

                                @unless ($pushConfigured)
                                    <p class="push-warning">
                                        <i class="fa-solid fa-triangle-exclamation"></i>
                                        Faltan las claves VAPID: ejecuta <code>php artisan webpush:vapid</code>.
                                    </p>
                                @endunless

                                <button type="button" class="btn btn-secondary btn-sm push-test" data-rx-push-test
                                    @if (!$pushActive) disabled @endif>
                                    <i class="fa-solid fa-paper-plane"></i> Enviar prueba
                                </button>
                            </div>

                            <label class="cf-switch cf-switch--bare">
                                <input type="checkbox" data-rx-push-toggle {{ $pushActive ? 'checked' : '' }}>
                                <span class="cf-switch__track" aria-hidden="true"></span>
                            </label>
                        </div>

                        <div class="st-pref">
                            <span class="st-pref__icon"><i class="fa-solid fa-moon"></i></span>
                            <div class="st-pref__text">
                                <strong>Modo oscuro</strong>
                                <p>Alterna entre tema claro y oscuro.</p>
                            </div>
                            <label class="cf-switch cf-switch--bare" for="darkModeToggle">
                                <input type="checkbox" id="darkModeToggle"
                                    {{ $user->dark_mode_enabled ? 'checked' : '' }}>
                                <span class="cf-switch__track" aria-hidden="true"></span>
                            </label>
                        </div>
                    </div>
                </section>

                {{-- 2FA --}}
                <section class="cf-card" id="twofaSection" style="display: none;">
                    <div class="cf-card__head">
                        <span class="cf-card__icon"><i class="fa-solid fa-qrcode"></i></span>
                        <div class="cf-card__head-text">
                            <h2>Configurar autenticación en dos pasos</h2>
                            <p>Escanea el QR, verifica el código y guarda los códigos de respaldo.</p>
                        </div>
                    </div>

                    <div class="cf-card__body">
                        <div class="st-steps">
                            <div class="st-step">
                                <span class="st-step__num">1</span>
                                <div class="st-step__body">
                                    <h3>Escanea el código QR</h3>
                                    <p>Usa Google Authenticator, Authy o cualquier app compatible.</p>

                                    <div class="st-qr">
                                        @if ($qrCodeSvg)
                                            <div class="qr-code">{!! $qrCodeSvg !!}</div>
                                        @else
                                            <div class="st-qr__placeholder">
                                                <i class="fa-solid fa-qrcode"></i>
                                                <p>Error al generar código QR</p>
                                            </div>
                                        @endif

                                        <div class="st-key">
                                            <span>Clave secreta</span>
                                            <code id="secretKey">{{ $user->two_factor_secret }}</code>
                                            <button type="button" class="cm-icon-btn" onclick="copySecretKey()"
                                                title="Copiar clave">
                                                <i class="fa-regular fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="st-step">
                                <span class="st-step__num">2</span>
                                <div class="st-step__body">
                                    <h3>Verifica el código</h3>
                                    <p>Introduce el código de 6 dígitos de tu aplicación.</p>

                                    <div class="code-inputs">
                                        @for ($i = 0; $i < 6; $i++)
                                            <input type="text" maxlength="1" class="code-digit"
                                                data-index="{{ $i }}" inputmode="numeric" autocomplete="one-time-code">
                                        @endfor
                                    </div>

                                    <button type="button" class="btn btn-primary" id="verify2FABtn" style="margin-top:.8rem;">
                                        <i class="fa-solid fa-check"></i> Verificar código
                                    </button>
                                </div>
                            </div>

                            <div class="st-step">
                                <span class="st-step__num">3</span>
                                <div class="st-step__body">
                                    <h3>Códigos de respaldo</h3>
                                    <p>Guárdalos en un lugar seguro.</p>

                                    <div class="backup-codes" id="backupCodesContainer">
                                        @if (count($unusedBackupCodes) > 0)
                                            @foreach (array_slice($unusedBackupCodes, 0, 8) as $code)
                                                <div class="code-chip">{{ $code['code'] ?? $code }}</div>
                                            @endforeach
                                        @elseif ($user->two_factor_recovery_codes)
                                            <p class="text-muted">No hay códigos disponibles.</p>
                                        @else
                                            <p class="text-muted">Aparecerán después de activar 2FA.</p>
                                        @endif
                                    </div>

                                    <button type="button" class="btn btn-secondary btn-sm" id="generateBackupCodesBtn"
                                        style="margin-top:.75rem;">
                                        <i class="fa-solid fa-rotate"></i> Generar nuevos códigos
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            {{-- ============================ ASIDE ============================ --}}
            <aside class="st-aside">
                <section class="cf-card">
                    <div class="cf-card__head">
                        <span class="cf-card__icon"><i class="fa-regular fa-user"></i></span>
                        <div class="cf-card__head-text">
                            <h2>Tu cuenta</h2>
                        </div>
                    </div>

                    <div class="st-profile">
                        <div class="st-profile__avatar">
                            {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                        </div>
                        <h3>{{ $user->name }}</h3>
                        <p>{{ $user->email }}</p>

                        <div class="uf-chips">
                            <span class="badge badge-primary">{{ $user->role === 'admin' ? 'Administrador' : 'Aliado' }}</span>
                            <span class="badge {{ $user->two_factor_enabled ? 'badge-success' : 'badge-neutral' }}">
                                {{ $user->two_factor_enabled ? '2FA activo' : '2FA inactivo' }}
                            </span>
                        </div>

                        <a href="{{ route('profile') }}" class="btn btn-secondary btn-sm" style="margin-top:.85rem;">
                            <i class="fa-solid fa-user-pen"></i> Editar perfil
                        </a>
                    </div>
                </section>

                <section class="cf-card">
                    <div class="cf-card__head">
                        <span class="cf-card__icon"><i class="fa-solid fa-lightbulb"></i></span>
                        <div class="cf-card__head-text">
                            <h2>Recomendaciones</h2>
                        </div>
                    </div>

                    <div class="cf-tips">
                        <ul>
                            <li><i class="fa-solid fa-check"></i> Usa una contraseña única y guárdala en un gestor.</li>
                            <li><i class="fa-solid fa-check"></i> Activa 2FA para proteger el panel.</li>
                            <li><i class="fa-solid fa-check"></i> Mantén actualizado el correo de soporte.</li>
                            <li><i class="fa-solid fa-check"></i> Revisa los dispositivos con push activo.</li>
                        </ul>
                    </div>
                </section>
            </aside>
        </div>
    </div>

    @include('Admin.settings-script')
@endsection
