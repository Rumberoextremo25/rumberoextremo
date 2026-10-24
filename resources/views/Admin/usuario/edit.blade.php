@extends('layouts.admin')

@section('title', 'Editar usuario')

@section('page_title_toolbar', 'Editar usuario')

@section('content')
    @php
        $name = trim(($user->firstname ?: $user->name) . ' ' . ($user->lastname ?: ''));
        $registrationDate = $user->registration_date
            ? \Carbon\Carbon::parse($user->registration_date)
            : $user->created_at;
    @endphp

    <div class="cm-page cf-page">

        {{-- ============================ CABECERA ============================ --}}
        <div class="page-header-block">
            <div>
                <h1 class="title-main">Editar <span class="title-accent">usuario</span></h1>
                <p class="page-subtitle">
                    Actualiza los datos de «{{ $name ?: 'usuario #' . $user->id }}» o cambia su acceso.
                </p>
            </div>

            <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-secondary">
                <i class="fa-solid fa-arrow-left"></i> Volver a la ficha
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

        @if (session('success'))
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i>
                <div class="alert-content">{{ session('success') }}</div>
            </div>
        @endif

        {{-- ============================== FORM ============================== --}}
        <form id="editUserForm" action="{{ route('admin.users.update', $user->id) }}" method="POST" class="cf-editor"
            data-uf-form="edit" autocomplete="off">
            @csrf
            @method('PUT')

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
                                        placeholder="Ej: Juan" value="{{ old('firstname', $user->firstname) }}" required>
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
                                        placeholder="Ej: Pérez" value="{{ old('lastname', $user->lastname) }}" required>
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
                                        placeholder="ejemplo@dominio.com" value="{{ old('email', $user->email) }}"
                                        required>
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
                                        placeholder="Ej: +58 412 1234567" value="{{ old('phone1', $user->phone1) }}">
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
                                <h2>Contraseña</h2>
                                <p>Déjala vacía para conservar la actual.</p>
                            </div>
                        </div>

                        <div class="cf-card__body">
                            <div class="cf-grid">
                                <div class="form-group">
                                    <label for="password">Nueva contraseña</label>
                                    <div class="uf-pass">
                                        <input type="password" id="password" name="password" minlength="8"
                                            class="form-control @error('password') is-invalid @enderror"
                                            placeholder="Mínimo 8 caracteres" autocomplete="new-password">
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
                                    <label for="password_confirmation">Repetir contraseña</label>
                                    <div class="uf-pass">
                                        <input type="password" id="password_confirmation" name="password_confirmation"
                                            class="form-control" placeholder="Repite la nueva contraseña"
                                            autocomplete="new-password">
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
                                        Si no escribes nada, la contraseña actual se mantiene.
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
                                        <option value="comun" @selected(old('user_type', $user->role) === 'comun')>Común</option>
                                        <option value="aliado" @selected(old('user_type', $user->role) === 'aliado')>Aliado</option>
                                        <option value="afiliado" @selected(old('user_type', $user->role) === 'afiliado')>Afiliado</option>
                                        <option value="admin" @selected(old('user_type', $user->role) === 'admin')>Administrador</option>
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
                                        <option value="activo" @selected(old('status', $user->status) === 'activo')>Activo</option>
                                        <option value="pendiente" @selected(old('status', $user->status) === 'pendiente')>Pendiente</option>
                                        <option value="inactivo" @selected(old('status', $user->status) === 'inactivo')>Inactivo</option>
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
                                        value="{{ old('registrationDate', optional($registrationDate)->format('Y-m-d')) }}"
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
                                        placeholder="Información adicional solo visible para el equipo…">{{ old('notes', $user->notes) }}</textarea>
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
                            <div class="uf-avatar"><span data-uf-initial>{{ mb_strtoupper(mb_substr($user->firstname ?: $user->name, 0, 1)) }}</span></div>
                            <h3 class="uf-name" data-uf-name>{{ $name ?: 'Nombre del usuario' }}</h3>
                            <p class="uf-email" data-uf-email>{{ $user->email }}</p>

                            <div class="uf-chips">
                                <span class="badge badge-neutral" data-uf-role>{{ ucfirst($user->role ?: 'común') }}</span>
                                <span class="badge badge-neutral" data-uf-status>{{ ucfirst($user->status ?: 'activo') }}</span>
                            </div>

                            <div class="uf-facts">
                                <span class="uf-fact"><i class="fa-solid fa-phone"></i><span data-uf-phone>{{ $user->phone1 ?: 'Sin teléfono' }}</span></span>
                                <span class="uf-fact"><i class="fa-regular fa-calendar"></i>
                                    <span data-uf-date>Registro: {{ optional($registrationDate)->format('d/m/Y') }}</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="cf-card">
                        <div class="cf-card__head">
                            <span class="cf-card__icon"><i class="fa-solid fa-clock-rotate-left"></i></span>
                            <div class="cf-card__head-text">
                                <h2>Datos del registro</h2>
                            </div>
                        </div>

                        <div class="cf-meta">
                            <div class="cf-meta__row">
                                <span>Identificador</span>
                                <strong>#{{ $user->id }}</strong>
                            </div>
                            <div class="cf-meta__row">
                                <span>Creado</span>
                                <strong>{{ optional($user->created_at)->format('d/m/Y H:i') ?? '—' }}</strong>
                            </div>
                            <div class="cf-meta__row">
                                <span>Última edición</span>
                                <strong>{{ optional($user->updated_at)->format('d/m/Y H:i') ?? '—' }}</strong>
                            </div>
                            <div class="cf-meta__row">
                                <span>Último acceso</span>
                                <strong>{{ $user->last_login_at?->diffForHumans() ?? 'Nunca' }}</strong>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>

            {{-- ------------------------------ Acciones ------------------------------ --}}
            <div class="cf-actions">
                <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-secondary">
                    <i class="fa-solid fa-xmark"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-primary" data-uf-submit>
                    <i class="fa-solid fa-floppy-disk"></i> Guardar cambios
                </button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin-forms.js') }}"></script>
@endpush
