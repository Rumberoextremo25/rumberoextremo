{{-- resources/views/Admin/profile/profile.blade.php --}}
@extends('layouts.admin')

@section('title', 'Mi Perfil')

@section('page_title_toolbar', 'Mi Perfil')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/pages.css') }}">
@endpush

@section('content')
    @php
        $u = Auth::user();
        $photo = $u->profile_photo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($u->profile_photo_path)
            ? \Illuminate\Support\Facades\Storage::url($u->profile_photo_path)
            : null;
        $roleLabel = match ($u->role) {
            'admin' => 'Administrador',
            'aliado' => 'Aliado comercial',
            default => 'Rumbero',
        };
    @endphp

    <div class="pf-page">

        {{-- ============================ CABECERA ============================ --}}
        <div class="page-header-block">
            <div>
                <h1 class="title-main">Mi <span class="title-accent">perfil</span></h1>
                <p class="page-subtitle">Tu información personal, tu foto y la actividad de tu cuenta.</p>
            </div>

            <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                <i class="fa-solid fa-pen"></i> Editar perfil
            </a>
        </div>

        {{-- ============================== AVISOS ============================== --}}
        @if (session('success'))
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i>
                <div class="alert-content">{{ session('success') }}</div>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <div class="alert-content">{{ session('error') }}</div>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <div class="alert-content">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="pf-grid">

            {{-- ======================= TARJETA DE PERFIL ======================= --}}
            <section class="pf-card pf-card--identity">
                <div class="pf-cover">
                    <div class="pf-avatar-wrap">
                        <div class="pf-avatar" id="pfAvatar">
                            @if ($photo)
                                <img src="{{ $photo }}" alt="Foto de {{ $u->name }}" id="pfAvatarImg">
                            @else
                                <span class="pf-avatar__initial">{{ mb_substr($u->name, 0, 1) }}</span>
                            @endif
                        </div>

                        {{-- Botón de cámara: abre el selector de archivos --}}
                        <button type="button" class="pf-avatar-btn" id="pfPickPhoto"
                            title="Cambiar foto (máx. 15 MB)" aria-label="Cambiar foto de perfil">
                            <i class="fa-solid fa-camera"></i>
                        </button>
                    </div>

                    <span class="pf-badge pf-badge--{{ $u->role }}">{{ $roleLabel }}</span>
                </div>

                <div class="pf-identity">
                    <h2 class="pf-name">{{ $u->full_name ?: $u->name }}</h2>
                    <p class="pf-email"><i class="fa-solid fa-envelope"></i> {{ $u->email }}</p>

                    <div class="pf-stats">
                        <div class="pf-stat">
                            <span class="pf-stat__value">{{ $u->created_at?->translatedFormat('M Y') }}</span>
                            <span class="pf-stat__label">Miembro desde</span>
                        </div>
                        <div class="pf-stat">
                            <span class="pf-stat__value">{{ $u->last_login_at?->diffForHumans() ?? '—' }}</span>
                            <span class="pf-stat__label">Último acceso</span>
                        </div>
                        <div class="pf-stat">
                            <span class="pf-stat__value">{{ $u->status ?? '—' }}</span>
                            <span class="pf-stat__label">Estado</span>
                        </div>
                    </div>

                    {{-- Subida de foto --}}
                    <form method="POST" action="{{ route('profile.photo') }}" enctype="multipart/form-data"
                        class="pf-upload" id="pfUploadForm">
                        @csrf
                        <input type="file" name="profile_photo" id="pfPhotoInput" accept="image/*" hidden>

                        <button type="button" class="btn btn-secondary pf-upload__btn" id="pfPickPhoto2">
                            <i class="fa-solid fa-upload"></i>
                            {{ $photo ? 'Cambiar foto' : 'Subir foto' }}
                        </button>

                        <span class="pf-upload__hint">JPG, PNG, GIF o WEBP · máx. 15 MB</span>

                        <div class="pf-upload__selected" id="pfSelected" hidden>
                            <span id="pfSelectedName">archivo</span>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fa-solid fa-check"></i> Guardar
                            </button>
                        </div>
                    </form>

                    @if ($photo)
                        <form method="POST" action="{{ route('profile.photo.destroy') }}" class="pf-remove"
                            onsubmit="return confirm('¿Eliminar tu foto de perfil?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="pf-remove__btn">
                                <i class="fa-solid fa-trash-can"></i> Quitar foto
                            </button>
                        </form>
                    @endif

                    <div class="pf-actions">
                        <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                            <i class="fa-solid fa-pen"></i> Editar datos
                        </a>
                        <a href="{{ route('password.change') }}" class="btn btn-secondary">
                            <i class="fa-solid fa-key"></i> Contraseña
                        </a>
                    </div>
                </div>
            </section>

            {{-- ======================== DATOS ======================== --}}
            <div class="pf-col">

                <section class="pf-card">
                    <div class="pf-card__head">
                        <i class="fa-solid fa-user"></i>
                        <h3>Información personal</h3>
                    </div>

                    <div class="pf-rows">
                        <div class="pf-row">
                            <span class="pf-row__label">Nombre completo</span>
                            <span class="pf-row__value">{{ $u->full_name ?: $u->name }}</span>
                        </div>
                        <div class="pf-row">
                            <span class="pf-row__label">Identificación</span>
                            <span class="pf-row__value">{{ $u->identification ?: '—' }}</span>
                        </div>
                        <div class="pf-row">
                            <span class="pf-row__label">Fecha de nacimiento</span>
                            <span class="pf-row__value">
                                {{ $u->dob ? \Illuminate\Support\Carbon::parse($u->dob)->format('d/m/Y') : '—' }}
                            </span>
                        </div>

                        @if ($u->is_ally)
                            <div class="pf-row">
                                <span class="pf-row__label">Empresa</span>
                                <span class="pf-row__value">{{ $u->allied_company_name ?: '—' }}</span>
                            </div>
                            <div class="pf-row">
                                <span class="pf-row__label">RIF</span>
                                <span class="pf-row__value">{{ $u->allied_company_rif ?: '—' }}</span>
                            </div>
                            <div class="pf-row">
                                <span class="pf-row__label">Descuento</span>
                                <span class="pf-row__value">{{ $u->discount ? $u->discount . ' %' : '—' }}</span>
                            </div>
                        @endif
                    </div>
                </section>

                <section class="pf-card">
                    <div class="pf-card__head">
                        <i class="fa-solid fa-address-book"></i>
                        <h3>Contacto</h3>
                    </div>

                    <div class="pf-rows">
                        <div class="pf-row">
                            <span class="pf-row__label">Correo</span>
                            <span class="pf-row__value">{{ $u->email }}</span>
                        </div>
                        <div class="pf-row">
                            <span class="pf-row__label">Teléfono principal</span>
                            <span class="pf-row__value">{{ $u->phone1 ?: '—' }}</span>
                        </div>
                        <div class="pf-row">
                            <span class="pf-row__label">Teléfono adicional</span>
                            <span class="pf-row__value">{{ $u->phone2 ?: '—' }}</span>
                        </div>
                        <div class="pf-row">
                            <span class="pf-row__label">Dirección</span>
                            <span class="pf-row__value">{{ $u->address ?: '—' }}</span>
                        </div>
                        @if ($u->website_url)
                            <div class="pf-row">
                                <span class="pf-row__label">Sitio web</span>
                                <span class="pf-row__value">
                                    <a href="{{ $u->website_url }}" target="_blank"
                                        rel="noopener">{{ $u->website_url }}</a>
                                </span>
                            </div>
                        @endif
                    </div>
                </section>

                <section class="pf-card" id="payment-methods">
                    <div class="pf-card__head">
                        <i class="fa-solid fa-credit-card"></i>
                        <h3>Métodos de pago</h3>
                    </div>

                    <p class="field-hint" style="margin:0 0 .9rem;">
                        Estos son los datos que la app envía al BNC. La cuenta y la tarjeta se guardan cifradas.
                        El CVV, el PIN y el token se piden al momento de pagar y nunca se almacenan.
                    </p>

                    <form method="POST" action="{{ route('profile.payments.update') }}">
                        @csrf

                        <h4 class="pf-subtitle">Pago Móvil · C2P</h4>
                        <div class="cf-grid">
                            <div class="form-group">
                                <label for="debtor_bank_code">Banco</label>

                                @if (count($banks))
                                    <select id="debtor_bank_code" name="debtor_bank_code" class="form-control" required>
                                        <option value="">Selecciona tu banco</option>
                                        @foreach ($banks as $bank)
                                            <option value="{{ $bank['Code'] }}"
                                                data-name="{{ $bank['Name'] }}"
                                                @selected(old('debtor_bank_code', $u->debtor_bank_code) === $bank['Code'])>
                                                {{ $bank['Code'] }} · {{ $bank['Name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="text" id="debtor_bank_code" name="debtor_bank_code"
                                        class="form-control" value="{{ old('debtor_bank_code', $u->debtor_bank_code) }}"
                                        placeholder="0134" inputmode="numeric" maxlength="4" required>
                                    <span class="field-hint">No se pudo cargar la lista del BNC. Escribe el código de 4 dígitos.</span>
                                @endif

                                <input type="hidden" id="debtor_bank_name" name="debtor_bank_name"
                                    value="{{ old('debtor_bank_name', $u->debtor_bank_name) }}">
                            </div>
                            <div class="form-group">
                                <label for="debtor_cell_phone">Teléfono</label>
                                <input type="tel" id="debtor_cell_phone" name="debtor_cell_phone" class="form-control"
                                    value="{{ old('debtor_cell_phone', $u->debtor_cell_phone) }}" placeholder="04141234567">
                            </div>
                            <div class="form-group">
                                <label for="debtor_id">Cédula</label>
                                <input type="text" id="debtor_id" name="debtor_id" class="form-control"
                                    value="{{ old('debtor_id', $u->debtor_id) }}" placeholder="V-12345678">
                            </div>
                        </div>

                        <h4 class="pf-subtitle">Débito inmediato</h4>
                        <p class="field-hint" style="margin:0 0 .6rem;">
                            Usa el mismo banco, teléfono y cédula del Pago Móvil. Solo cambia el tipo de cuenta.
                        </p>
                        <div class="cf-grid">
                            <div class="form-group">
                                <label for="debtor_account_type">Tipo de cuenta</label>
                                <select id="debtor_account_type" name="debtor_account_type" class="form-control">
                                    @foreach (['CELE' => 'Ahorro (CELE)', 'CTAH' => 'Corriente (CTAH)', 'CTAV' => 'Vista (CTAV)', 'AHORROS' => 'Ahorros', 'CORRIENTE' => 'Corriente'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('debtor_account_type', $u->debtor_account_type ?: 'CELE') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <h4 class="pf-subtitle">Tarjeta · VPOS</h4>
                        <div class="cf-grid">
                            <div class="form-group cf-span">
                                <label for="card_number">Número de tarjeta</label>
                                <input type="text" id="card_number" name="card_number" class="form-control"
                                    value="{{ old('card_number', $u->card_number) }}" inputmode="numeric"
                                    placeholder="Número completo de la tarjeta">
                            </div>
                            <div class="form-group">
                                <label for="card_expiration">Vencimiento (MMAAAA)</label>
                                <input type="text" id="card_expiration" name="card_expiration" class="form-control"
                                    value="{{ old('card_expiration', $u->card_expiration) }}" maxlength="6"
                                    placeholder="122030">
                            </div>
                            <div class="form-group">
                                <label for="card_holder_id">Cédula del titular</label>
                                <input type="text" id="card_holder_id" name="card_holder_id" class="form-control"
                                    value="{{ old('card_holder_id', $u->card_holder_id) }}" placeholder="12345678">
                            </div>
                            <div class="form-group cf-span">
                                <label for="card_holder_name">Nombre del titular</label>
                                <input type="text" id="card_holder_name" name="card_holder_name" class="form-control"
                                    value="{{ old('card_holder_name', $u->card_holder_name) }}">
                            </div>
                            <div class="form-group">
                                <label for="card_type_id">Tipo de tarjeta</label>
                                <select id="card_type_id" name="card_type_id" class="form-control">
                                    <option value="1" @selected((int) old('card_type_id', $u->card_type_id) === 1)>1 · Visa</option>
                                    <option value="2" @selected((int) old('card_type_id', $u->card_type_id) === 2)>2 · Mastercard</option>
                                    <option value="3" @selected((int) old('card_type_id', $u->card_type_id) === 3)>3 · Amex</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="card_account_type">Tipo de cuenta</label>
                                <input type="number" id="card_account_type" name="card_account_type" class="form-control"
                                    value="{{ old('card_account_type', $u->card_account_type ?? 20) }}">
                            </div>
                        </div>

                        <div class="cf-actions" style="justify-content:flex-end;">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-floppy-disk"></i> Guardar métodos de pago
                            </button>
                        </div>
                    </form>
                </section>

                <section class="pf-card">
                    <div class="pf-card__head">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        <h3>Actividad</h3>
                    </div>

                    <ul class="pf-timeline">
                        <li>
                            <span class="pf-timeline__dot"><i class="fa-solid fa-right-to-bracket"></i></span>
                            <div>
                                <strong>Último acceso</strong>
                                <small>{{ $u->last_login_at?->diffForHumans() ?? 'Sin registro' }}</small>
                            </div>
                        </li>
                        <li>
                            <span class="pf-timeline__dot"><i class="fa-solid fa-pen"></i></span>
                            <div>
                                <strong>Perfil actualizado</strong>
                                <small>{{ $u->updated_at?->diffForHumans() ?? '—' }}</small>
                            </div>
                        </li>
                        <li>
                            <span class="pf-timeline__dot"><i class="fa-solid fa-user-check"></i></span>
                            <div>
                                <strong>Cuenta creada</strong>
                                <small>{{ $u->created_at?->diffForHumans() ?? '—' }}</small>
                            </div>
                        </li>
                    </ul>
                </section>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            const input = document.getElementById('pfPhotoInput');
            const form = document.getElementById('pfUploadForm');
            const selected = document.getElementById('pfSelected');
            const selectedName = document.getElementById('pfSelectedName');
            const avatarImg = document.getElementById('pfAvatarImg');
            const avatar = document.getElementById('pfAvatar');
            const MAX = 15 * 1024 * 1024; // 15 MB

            const open = () => input && input.click();

            ['pfPickPhoto', 'pfPickPhoto2'].forEach(id => {
                document.getElementById(id)?.addEventListener('click', open);
            });

            input?.addEventListener('change', function() {
                const file = this.files && this.files[0];
                if (!file) return;

                if (!file.type.startsWith('image/')) {
                    alert('El archivo debe ser una imagen.');
                    this.value = '';
                    return;
                }

                if (file.size > MAX) {
                    alert('La imagen pesa ' + (file.size / 1048576).toFixed(1) +
                        ' MB y el máximo permitido es 15 MB.');
                    this.value = '';
                    return;
                }

                // Vista previa inmediata
                const url = URL.createObjectURL(file);
                if (avatarImg) {
                    avatarImg.src = url;
                } else if (avatar) {
                    avatar.innerHTML = '<img src="' + url + '" alt="Vista previa" id="pfAvatarImg">';
                }

                selectedName.textContent = file.name + ' · ' + (file.size / 1048576).toFixed(1) + ' MB';
                selected.hidden = false;
            });
        })();

        // Guarda el nombre del banco elegido desde la lista del BNC.
        const bankSelect = document.getElementById('debtor_bank_code');
        const bankNameInput = document.getElementById('debtor_bank_name');

        if (bankSelect && bankSelect.tagName === 'SELECT' && bankNameInput) {
            const syncBankName = () => {
                const option = bankSelect.options[bankSelect.selectedIndex];
                bankNameInput.value = option?.dataset?.name || '';
            };

            bankSelect.addEventListener('change', syncBankName);
            syncBankName();
        }
    </script>
@endpush
