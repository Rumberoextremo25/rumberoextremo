@extends('layouts.admin')

@section('title', 'Nuevo usuario')

@section('page_title_toolbar', 'Nuevo usuario')

@section('content')
    <div class="cm-page cf-page">

        {{-- ============================ CABECERA ============================ --}}
        <div class="page-header-block">
            <div>
                <h1 class="title-main">Nuevo <span class="title-accent">usuario</span></h1>
                <p class="page-subtitle">
                    Crea una cuenta, define su rol y su estado de acceso al panel.
                </p>
            </div>

            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                <i class="fa-solid fa-arrow-left"></i> Volver al listado
            </a>
        </div>

        {{-- ============================== AVISOS ============================== --}}
        @if ($errors->any())
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <div class="alert-content">
                    <strong>Revisa los siguientes campos:</strong>
                    <ul style="margin:.35rem 0 0;padding-left:1.1rem;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- ============================== FORM ============================== --}}
        <form id="addUserForm" action="{{ route('admin.users.store') }}" method="POST" class="cf-editor"
            data-uf-form="create" autocomplete="off">
            @csrf

            <div class="cf-form">

                {{-- ------------------------- Columna principal ------------------------- --}}
                <div class="cf-col">

                    {{-- Datos personales --}}
                    <section class="cf-card">
                        <div class="cf-card__head">
                            <span class="cf-card__icon"><i class="fa-solid fa-user"></i></span>
                            <div class="cf-card__head-text">
                                <h2>Datos personales</h2>
                                <p>Nombre, correo y teléfono de contacto.</p>
                            </div>
                        </div>

                        <div class="cf-card__body">
                            <div class="cf-grid">
                                <div class="form-group">
                                    <label for="firstname">Nombre <span class="required-star">*</span></label>
                                    <input type="text" id="firstname" name="firstname" maxlength="255"
                                        class="form-control @error('firstname') is-invalid @enderror"
                                        placeholder="Ej: Juan" value="{{ old('firstname') }}" required>
                                    @error('firstname')
                                        <span class="error-message">
                                            <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="lastname">Apellido <span class="required-star">*</span></label>
                                    <input type="text" id="lastname" name="lastname" maxlength="255"
                                        class="form-control @error('lastname') is-invalid @enderror"
                                        placeholder="Ej: Pérez" value="{{ old('lastname') }}" required>
                                    @error('lastname')
                                        <span class="error-message">
                                            <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="email">Correo electrónico <span class="required-star">*</span></label>
                                    <input type="email" id="email" name="email" maxlength="255"
                                        class="form-control @error('email') is-invalid @enderror"
                                        placeholder="ejemplo@dominio.com" value="{{ old('email') }}" required>
                                    @error('email')
                                        <span class="error-message">
                                            <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="phone1">Teléfono</label>
                                    <input type="tel" id="phone1" name="phone1" maxlength="20"
                                        class="form-control @error('phone1') is-invalid @enderror"
                                        placeholder="Ej: +58 412 1234567" value="{{ old('phone1') }}">
                                    @error('phone1')
                                        <span class="error-message">
                                            <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- Seguridad --}}
                    <section class="cf-card">
                        <div class="cf-card__head">
                            <span class="cf-card__icon"><i class="fa-solid fa-lock"></i></span>
                            <div class="cf-card__head-text">
                                <h2>Seguridad</h2>
                                <p>La contraseña debe tener al menos 8 caracteres.</p>
                            </div>
                        </div>

                        <div class="cf-card__body">
                            <div class="cf-grid">
                                <div class="form-group" id="passwordGroup">
                                    <label for="password">Contraseña <span class="required-star">*</span></label>
                                    <div class="uf-pass">
                                        <input type="password" id="password" name="password" minlength="8"
                                            class="form-control @error('password') is-invalid @enderror"
                                            placeholder="Mínimo 8 caracteres" autocomplete="new-password" required>
                                        <button type="button" class="uf-pass__toggle" data-target="password"
                                            aria-label="Mostrar contraseña">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                    </div>

                                    <div class="uf-strength">
                                        <span class="uf-strength__track">
                                            <span class="uf-strength__bar" data-uf-strength></span>
                                        </span>
                                        <span class="uf-strength__label" data-uf-strength-label>Sin contraseña</span>
                                    </div>

                                    <ul class="uf-rules">
                                        <li class="uf-rule" data-uf-rule="length"><i class="fa-solid fa-circle"></i> Mínimo 8 caracteres</li>
                                        <li class="uf-rule" data-uf-rule="upper"><i class="fa-solid fa-circle"></i> Una mayúscula</li>
                                        <li class="uf-rule" data-uf-rule="lower"><i class="fa-solid fa-circle"></i> Una minúscula</li>
                                        <li class="uf-rule" data-uf-rule="number"><i class="fa-solid fa-circle"></i> Un número</li>
                                        <li class="uf-rule" data-uf-rule="special"><i class="fa-solid fa-circle"></i> Un carácter especial (!@#$%^&*)</li>
                                    </ul>

                                    @error('password')
                                        <span class="error-message">
                                            <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="password_confirmation">Repetir contraseña <span class="required-star">*</span></label>
                                    <div class="uf-pass">
                                        <input type="password" id="password_confirmation" name="password_confirmation"
                                            class="form-control" placeholder="Repite la contraseña"
                                            autocomplete="new-password" required>
                                        <button type="button" class="uf-pass__toggle"
                                            data-target="password_confirmation" aria-label="Mostrar contraseña">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                    </div>

                                    <div class="uf-match" data-uf-match>
                                        <i class="fa-solid fa-circle"></i> Repite la contraseña
                                    </div>

                                    <p class="field-hint" style="margin-top:.6rem;">
                                        <i class="fa-solid fa-circle-info"></i>
                                        Las mayúsculas, números y símbolos son recomendaciones, no obligatorios.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- Cuenta --}}
                    <section class="cf-card">
                        <div class="cf-card__head">
                            <span class="cf-card__icon"><i class="fa-solid fa-user-gear"></i></span>
                            <div class="cf-card__head-text">
                                <h2>Cuenta y acceso</h2>
                                <p>Rol, estado y fecha de registro.</p>
                            </div>
                        </div>

                        <div class="cf-card__body">
                            <div class="cf-grid">
                                <div class="form-group">
                                    <label for="user_type">Tipo de usuario <span class="required-star">*</span></label>
                                    <select id="user_type" name="user_type"
                                        class="form-control @error('user_type') is-invalid @enderror" required>
                                        <option value="">Selecciona un rol</option>
                                        <option value="comun" @selected(old('user_type') === 'comun')>Común</option>
                                        <option value="aliado" @selected(old('user_type') === 'aliado')>Aliado</option>
                                        <option value="afiliado" @selected(old('user_type') === 'afiliado')>Afiliado</option>
                                        <option value="admin" @selected(old('user_type') === 'admin')>Administrador</option>
                                    </select>
                                    @error('user_type')
                                        <span class="error-message">
                                            <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="status">Estado <span class="required-star">*</span></label>
                                    <select id="status" name="status"
                                        class="form-control @error('status') is-invalid @enderror" required>
                                        <option value="activo" @selected(old('status', 'activo') === 'activo')>Activo</option>
                                        <option value="pendiente" @selected(old('status') === 'pendiente')>Pendiente</option>
                                        <option value="inactivo" @selected(old('status') === 'inactivo')>Inactivo</option>
                                    </select>
                                    <span class="field-hint">
                                        <i class="fa-solid fa-circle-info"></i> Los usuarios inactivos no pueden entrar.
                                    </span>
                                    @error('status')
                                        <span class="error-message">
                                            <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="registrationDate">Fecha de registro <span class="required-star">*</span></label>
                                    <input type="date" id="registrationDate" name="registrationDate"
                                        class="form-control @error('registrationDate') is-invalid @enderror"
                                        value="{{ old('registrationDate', \Carbon\Carbon::now()->format('Y-m-d')) }}"
                                        required>
                                    @error('registrationDate')
                                        <span class="error-message">
                                            <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group cf-span">
                                    <label for="notes">Notas internas</label>
                                    <textarea id="notes" name="notes" rows="3"
                                        class="form-control @error('notes') is-invalid @enderror"
                                        placeholder="Información adicional solo visible para el equipo…">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <span class="error-message">
                                            <i class="fa-solid fa-circle-exclamation"></i> {{ $message }}
                                        </span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                {{-- ----------------------------- Aside ------------------------------ --}}
                <aside class="cf-aside">

                    <div class="cf-card" data-uf-preview>
                        <div class="cf-card__head">
                            <span class="cf-card__icon"><i class="fa-regular fa-address-card"></i></span>
                            <div class="cf-card__head-text">
                                <h2>Vista previa</h2>
                                <p>Así se verá la cuenta en el listado.</p>
                            </div>
                        </div>

                        <div class="uf-preview__body">
                            <div class="uf-avatar"><span data-uf-initial>?</span></div>
                            <h3 class="uf-name" data-uf-name>Nombre del usuario</h3>
                            <p class="uf-email" data-uf-email>correo@ejemplo.com</p>

                            <div class="uf-chips">
                                <span class="badge badge-neutral" data-uf-role>Sin rol</span>
                                <span class="badge badge-neutral" data-uf-status>Sin estado</span>
                            </div>

                            <div class="uf-facts">
                                <span class="uf-fact"><i class="fa-solid fa-phone"></i><span data-uf-phone>Sin teléfono</span></span>
                                <span class="uf-fact"><i class="fa-regular fa-calendar"></i><span data-uf-date>Sin fecha de registro</span></span>
                            </div>
                        </div>
                    </div>

                    <div class="cf-card">
                        <div class="cf-card__head">
                            <span class="cf-card__icon"><i class="fa-solid fa-lightbulb"></i></span>
                            <div class="cf-card__head-text">
                                <h2>Buenas prácticas</h2>
                            </div>
                        </div>

                        <div class="cf-tips">
                            <ul>
                                <li><i class="fa-solid fa-check"></i> Usa una contraseña distinta a la del correo.</li>
                                <li><i class="fa-solid fa-check"></i> Deja el estado «Pendiente» si aún no debe entrar.</li>
                                <li><i class="fa-solid fa-check"></i> El rol define qué secciones verá en el panel.</li>
                                <li><i class="fa-solid fa-check"></i> Las notas internas no las ve el usuario.</li>
                            </ul>
                        </div>
                    </div>
                </aside>
            </div>

            {{-- ------------------------------ Acciones ------------------------------ --}}
            <div class="cf-actions">
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                    <i class="fa-solid fa-xmark"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary" data-uf-submit>
                    <i class="fa-solid fa-user-plus"></i> Crear usuario
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin-forms.js') }}"></script>
@endpush
