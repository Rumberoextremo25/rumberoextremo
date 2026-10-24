@extends('layouts.admin')

@section('title', 'Ficha de usuario')

@section('page_title_toolbar', 'Ficha de usuario')

@section('content')
    @php
        $name = trim(($user->firstname ?: $user->name) . ' ' . ($user->lastname ?: ''));
        $roleKey = $user->role ?: 'comun';
        $roleLabels = [
            'admin' => 'Administrador',
            'aliado' => 'Aliado comercial',
            'afiliado' => 'Afiliado',
            'comun' => 'Rumbero',
            'user' => 'Rumbero',
        ];
        $roleBadges = [
            'admin' => 'badge-primary',
            'aliado' => 'badge-info',
            'afiliado' => 'badge-warning',
            'comun' => 'badge-neutral',
            'user' => 'badge-neutral',
        ];
        $roleLabel = $roleLabels[$roleKey] ?? ucfirst($roleKey);
        $statusKey = $user->status ?: 'activo';
        $statusLabels = ['activo' => 'Activo', 'pendiente' => 'Pendiente', 'inactivo' => 'Inactivo'];
        $statusBadges = ['activo' => 'badge-success', 'pendiente' => 'badge-warning', 'inactivo' => 'badge-neutral'];
        $statusLabel = $statusLabels[$statusKey] ?? ucfirst($statusKey);
        $registration = $user->registration_date ? \Carbon\Carbon::parse($user->registration_date) : $user->created_at;
        $hasPhoto = $user->profile_photo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->profile_photo_path);
    @endphp

    <div class="pf-page">

        {{-- ============================ CABECERA ============================ --}}
        <div class="page-header-block">
            <div>
                <h1 class="title-main">Ficha de <span class="title-accent">usuario</span></h1>
                <p class="page-subtitle">
                    Cuenta #{{ $user->id }} · {{ $roleLabel }} ·
                    <span class="badge {{ $statusBadges[$statusKey] ?? 'badge-neutral' }}">{{ $statusLabel }}</span>
                </p>
            </div>

            <div class="header-actions">
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                    <i class="fa-solid fa-arrow-left"></i> Volver al listado
                </a>
                <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary">
                    <i class="fa-solid fa-pen"></i> Editar usuario
                </a>
            </div>
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

        <div class="pf-grid">

            {{-- ====================== COLUMNA DE IDENTIDAD ====================== --}}
            <div class="pf-col">

                <section class="pf-card">
                    <div class="pf-cover">
                        <div class="pf-avatar-wrap">
                            <div class="pf-avatar pf-avatar--stack">
                                <span class="pf-avatar__initial">{{ mb_strtoupper(mb_substr($user->firstname ?: $user->name, 0, 1)) }}</span>
                                @if ($hasPhoto)
                                    <img src="{{ asset('storage/' . ltrim($user->profile_photo_path, '/')) }}"
                                        alt="Foto de {{ $name }}" data-img>
                                @endif
                            </div>
                        </div>

                        <span class="pf-badge">{{ $roleLabel }}</span>
                    </div>

                    <div class="pf-identity">
                        <h2 class="pf-name">{{ $name ?: 'Usuario sin nombre' }}</h2>
                        <p class="pf-email"><i class="fa-solid fa-envelope"></i> {{ $user->email }}</p>

                        <div class="pf-stats">
                            <div class="pf-stat">
                                <span class="pf-stat__value">{{ optional($user->created_at)->format('M Y') ?? '—' }}</span>
                                <span class="pf-stat__label">Miembro desde</span>
                            </div>
                            <div class="pf-stat">
                                <span class="pf-stat__value">{{ $user->last_login_at?->diffForHumans() ?? 'Nunca' }}</span>
                                <span class="pf-stat__label">Último acceso</span>
                            </div>
                            <div class="pf-stat">
                                <span class="pf-stat__value">
                                    <span class="badge {{ $statusBadges[$statusKey] ?? 'badge-neutral' }}">{{ $statusLabel }}</span>
                                </span>
                                <span class="pf-stat__label">Estado</span>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="pf-card">
                    <div class="pf-card__head">
                        <i class="fa-solid fa-clipboard-list"></i>
                        <h3>Notas internas</h3>
                    </div>

                    <div class="pf-notes {{ $user->notes ? '' : 'is-empty' }}">
                        {{ $user->notes ?: 'No hay notas internas para este usuario.' }}
                    </div>
                </section>

                @if (auth()->id() !== $user->id)
                    <section class="pf-card">
                        <div class="pf-card__head">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                            <h3>Eliminar cuenta</h3>
                        </div>

                        <div class="pf-notes">
                            El usuario perderá el acceso y sus datos de cuenta se borrarán. Esta acción no se puede
                            deshacer.
                        </div>

                        <div class="pf-actions pf-actions--single" style="padding:0 1.15rem 1.15rem;">
                            <button type="button" class="btn btn-danger" data-delete-trigger
                                data-delete-id="{{ $user->id }}" data-delete-name="{{ $name }}">
                                <i class="fa-solid fa-trash-can"></i> Eliminar usuario
                            </button>

                            <form id="delete-form-{{ $user->id }}"
                                action="{{ route('admin.users.destroy', $user->id) }}" method="POST" hidden>
                                @csrf
                                @method('DELETE')
                            </form>
                        </div>
                    </section>
                @endif
            </div>

            {{-- ========================= DATOS DETALLADOS ========================= --}}
            <div class="pf-col">

                <section class="pf-card">
                    <div class="pf-card__head">
                        <i class="fa-solid fa-user"></i>
                        <h3>Información personal</h3>
                    </div>

                    <div class="pf-rows">
                        <div class="pf-row">
                            <span class="pf-row__label">Identificador</span>
                            <span class="pf-row__value">#{{ $user->id }}</span>
                        </div>
                        <div class="pf-row">
                            <span class="pf-row__label">Nombre completo</span>
                            <span class="pf-row__value">{{ $name ?: '—' }}</span>
                        </div>
                        <div class="pf-row">
                            <span class="pf-row__label">Correo electrónico</span>
                            <span class="pf-row__value">
                                <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                            </span>
                        </div>
                        <div class="pf-row">
                            <span class="pf-row__label">Teléfono</span>
                            <span class="pf-row__value">{{ $user->phone1 ?: 'No especificado' }}</span>
                        </div>
                        @if ($user->phone2)
                            <div class="pf-row">
                                <span class="pf-row__label">Teléfono adicional</span>
                                <span class="pf-row__value">{{ $user->phone2 }}</span>
                            </div>
                        @endif
                        @if ($user->identification)
                            <div class="pf-row">
                                <span class="pf-row__label">Cédula / RIF</span>
                                <span class="pf-row__value">{{ $user->identification }}</span>
                            </div>
                        @endif
                        @if ($user->dob)
                            <div class="pf-row">
                                <span class="pf-row__label">Fecha de nacimiento</span>
                                <span class="pf-row__value">{{ \Carbon\Carbon::parse($user->dob)->format('d/m/Y') }}</span>
                            </div>
                        @endif
                        @if ($user->address)
                            <div class="pf-row">
                                <span class="pf-row__label">Dirección</span>
                                <span class="pf-row__value">{{ $user->address }}</span>
                            </div>
                        @endif
                    </div>
                </section>

                <section class="pf-card">
                    <div class="pf-card__head">
                        <i class="fa-solid fa-user-gear"></i>
                        <h3>Detalles de la cuenta</h3>
                    </div>

                    <div class="pf-rows">
                        <div class="pf-row">
                            <span class="pf-row__label">Tipo de usuario</span>
                            <span class="pf-row__value">
                                <span class="badge {{ $roleBadges[$roleKey] ?? 'badge-neutral' }}">{{ $roleLabel }}</span>
                            </span>
                        </div>
                        <div class="pf-row">
                            <span class="pf-row__label">Estado</span>
                            <span class="pf-row__value">
                                <span class="badge {{ $statusBadges[$statusKey] ?? 'badge-neutral' }}">{{ $statusLabel }}</span>
                            </span>
                        </div>
                        <div class="pf-row">
                            <span class="pf-row__label">Fecha de registro</span>
                            <span class="pf-row__value">{{ optional($registration)->format('d/m/Y') ?? '—' }}</span>
                        </div>
                        <div class="pf-row">
                            <span class="pf-row__label">Última actualización</span>
                            <span class="pf-row__value">{{ optional($user->updated_at)->format('d/m/Y H:i') ?? '—' }}</span>
                        </div>
                        <div class="pf-row">
                            <span class="pf-row__label">Último acceso</span>
                            <span class="pf-row__value">{{ $user->last_login_at?->format('d/m/Y H:i') ?? 'Nunca' }}</span>
                        </div>
                        <div class="pf-row">
                            <span class="pf-row__label">Correo verificado</span>
                            <span class="pf-row__value">
                                @if ($user->email_verified_at)
                                    <span class="badge badge-success"><i class="fa-solid fa-check"></i> Sí</span>
                                @else
                                    <span class="badge badge-neutral"><i class="fa-solid fa-minus"></i> Pendiente</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    {{-- ====================== MODAL DE CONFIRMACIÓN ====================== --}}
    <div class="modal cf-modal" id="deleteModal" hidden>
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="deleteTitle">
            <div class="modal-body">
                <div class="cf-confirm">
                    <span class="cf-confirm__icon"><i class="fa-solid fa-triangle-exclamation"></i></span>
                    <h2 id="deleteTitle">Eliminar usuario</h2>
                    <p>¿Seguro que quieres eliminar a <span class="cf-confirm__name" data-delete-name></span>?</p>
                    <p>Se borrará su cuenta y no se puede deshacer.</p>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-delete-cancel>
                    <i class="fa-solid fa-xmark"></i> Cancelar
                </button>
                <button type="button" class="btn btn-danger" data-delete-confirm>
                    <i class="fa-solid fa-trash-can"></i> Sí, eliminar
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin-forms.js') }}"></script>
@endpush
