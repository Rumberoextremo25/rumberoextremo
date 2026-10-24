@extends('layouts.admin')

@section('title', 'Usuarios')

@section('page_title_toolbar', 'Gestión de usuarios')

@section('content')
    @php
        $roleMeta = [
            'admin' => ['label' => 'Administrador', 'badge' => 'badge-primary', 'icon' => 'fa-user-shield'],
            'aliado' => ['label' => 'Aliado', 'badge' => 'badge-info', 'icon' => 'fa-store'],
            'afiliado' => ['label' => 'Afiliado', 'badge' => 'badge-warning', 'icon' => 'fa-user-group'],
            'comun' => ['label' => 'Común', 'badge' => 'badge-neutral', 'icon' => 'fa-user'],
            'user' => ['label' => 'Rumbero', 'badge' => 'badge-neutral', 'icon' => 'fa-user'],
        ];
        $statusMeta = [
            'activo' => ['label' => 'Activo', 'badge' => 'badge-success', 'icon' => 'fa-circle-check'],
            'pendiente' => ['label' => 'Pendiente', 'badge' => 'badge-warning', 'icon' => 'fa-clock'],
            'inactivo' => ['label' => 'Inactivo', 'badge' => 'badge-neutral', 'icon' => 'fa-circle-pause'],
        ];
        $hasFilters = $q !== '' || in_array($rol, ['admin', 'aliado', 'afiliado', 'comun'], true) || in_array($estado, ['activo', 'inactivo', 'pendiente'], true);
    @endphp

    <div class="cm-page">

        {{-- ============================ CABECERA ============================ --}}
        <div class="page-header-block">
            <div>
                <h1 class="title-main">Gestión de <span class="title-accent">usuarios</span></h1>
                <p class="page-subtitle">
                    Administra las cuentas del panel: roles, estado de acceso y datos de contacto.
                </p>
            </div>

            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Nuevo usuario
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

        {{-- ============================= MÉTRICAS ============================= --}}
        <div class="cm-stats">
            <div class="cm-stat">
                <span class="cm-stat__icon"><i class="fa-solid fa-users"></i></span>
                <span class="cm-stat__body">
                    <span class="cm-stat__value">{{ number_format($stats['total']) }}</span>
                    <span class="cm-stat__label">Total</span>
                </span>
            </div>

            <div class="cm-stat">
                <span class="cm-stat__icon cm-stat__icon--green"><i class="fa-solid fa-user-check"></i></span>
                <span class="cm-stat__body">
                    <span class="cm-stat__value">{{ number_format($stats['active']) }}</span>
                    <span class="cm-stat__label">Activos</span>
                </span>
            </div>

            <div class="cm-stat">
                <span class="cm-stat__icon cm-stat__icon--gold"><i class="fa-solid fa-user-clock"></i></span>
                <span class="cm-stat__body">
                    <span class="cm-stat__value">{{ number_format($stats['pending']) }}</span>
                    <span class="cm-stat__label">Pendientes</span>
                </span>
            </div>

            <div class="cm-stat">
                <span class="cm-stat__icon cm-stat__icon--red"><i class="fa-solid fa-user-slash"></i></span>
                <span class="cm-stat__body">
                    <span class="cm-stat__value">{{ number_format($stats['inactive']) }}</span>
                    <span class="cm-stat__label">Inactivos</span>
                </span>
            </div>
        </div>

        {{-- ============================= FILTROS ============================= --}}
        <div class="cm-filters">
            <form method="GET" action="{{ route('admin.users.index') }}" data-cm-filters>
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="search" name="q" value="{{ $q }}"
                        placeholder="Buscar por nombre, correo o teléfono…" aria-label="Buscar usuarios">
                </div>

                <select name="rol" class="filter-select" aria-label="Filtrar por rol">
                    <option value="">Todos los roles</option>
                    <option value="admin" @selected($rol === 'admin')>Administradores</option>
                    <option value="aliado" @selected($rol === 'aliado')>Aliados</option>
                    <option value="afiliado" @selected($rol === 'afiliado')>Afiliados</option>
                    <option value="comun" @selected($rol === 'comun')>Comunes</option>
                </select>

                <select name="estado" class="filter-select" aria-label="Filtrar por estado">
                    <option value="">Todos los estados</option>
                    <option value="activo" @selected($estado === 'activo')>Activos</option>
                    <option value="pendiente" @selected($estado === 'pendiente')>Pendientes</option>
                    <option value="inactivo" @selected($estado === 'inactivo')>Inactivos</option>
                </select>

                <noscript>
                    <button type="submit" class="btn btn-secondary btn-sm">
                        <i class="fa-solid fa-filter"></i> Filtrar
                    </button>
                </noscript>

                @if ($hasFilters)
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fa-solid fa-xmark"></i> Limpiar
                    </a>
                @endif
            </form>

            <span class="cm-filters__count">
                {{ $users->total() }} {{ $users->total() === 1 ? 'usuario' : 'usuarios' }}
            </span>
        </div>

        {{-- ============================== TABLA ============================== --}}
        <div class="cm-table-card">
            @if ($users->isEmpty())
                <div class="cm-empty">
                    <i class="fa-solid fa-users-slash"></i>
                    @if ($hasFilters)
                        <h3>Sin resultados</h3>
                        <p>No hay usuarios que coincidan con la búsqueda o los filtros.</p>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fa-solid fa-xmark"></i> Quitar filtros
                        </a>
                    @else
                        <h3>Todavía no hay usuarios</h3>
                        <p>Crea la primera cuenta para empezar a gestionar el acceso al panel.</p>
                        <a href="{{ route('admin.users.create') }}" class="btn btn-primary" style="margin-top:.5rem;">
                            <i class="fa-solid fa-plus"></i> Crear usuario
                        </a>
                    @endif
                </div>
            @else
                <div class="rx-table-scroll">
                    <table class="rx-stack-table">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th class="hide-mobile">Contacto</th>
                                <th>Tipo</th>
                                <th>Estado</th>
                                <th class="hide-mobile">Registro</th>
                                <th style="text-align:right;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                @php
                                    $roleKey = $user->role ?: 'comun';
                                    $role = $roleMeta[$roleKey] ?? ['label' => ucfirst($roleKey), 'badge' => 'badge-neutral', 'icon' => 'fa-user'];
                                    $statusKey = $user->status ?: 'activo';
                                    $status = $statusMeta[$statusKey] ?? ['label' => ucfirst($statusKey), 'badge' => 'badge-neutral', 'icon' => 'fa-circle'];
                                    $registered = $user->registration_date ? \Carbon\Carbon::parse($user->registration_date) : $user->created_at;
                                    $name = trim(($user->firstname ?: $user->name) . ' ' . ($user->lastname ?: ''));
                                    $isSelf = auth()->id() === $user->id;
                                    $hasPhoto = $user->profile_photo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->profile_photo_path);
                                @endphp
                                <tr>
                                    <td data-label="Usuario">
                                        <div class="cm-user">
                                            <div class="cm-user__avatar">
                                                <span>{{ mb_strtoupper(mb_substr($user->firstname ?: $user->name, 0, 1)) }}</span>
                                                @if ($hasPhoto)
                                                    <img src="{{ asset('storage/' . ltrim($user->profile_photo_path, '/')) }}"
                                                        alt="Foto de {{ $name }}" loading="lazy" data-img>
                                                @endif
                                            </div>

                                            <div class="cm-user__text">
                                                <span class="cm-user__name">
                                                    {{ $name ?: 'Sin nombre' }}
                                                    @if ($isSelf)
                                                        <span class="cm-user__self">Tú</span>
                                                    @endif
                                                </span>
                                                <span class="cm-user__email">{{ $user->email }}</span>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="hide-mobile" data-label="Contacto">
                                        <span class="cm-ally__link">
                                            @if ($user->phone1)
                                                <i class="fa-solid fa-phone"></i> {{ $user->phone1 }}
                                            @else
                                                Sin teléfono
                                            @endif
                                        </span>
                                    </td>

                                    <td data-label="Tipo">
                                        <span class="badge {{ $role['badge'] }}">
                                            <i class="fa-solid {{ $role['icon'] }}"></i> {{ $role['label'] }}
                                        </span>
                                    </td>

                                    <td data-label="Estado">
                                        <span class="badge {{ $status['badge'] }}">
                                            <i class="fa-solid {{ $status['icon'] }}"></i> {{ $status['label'] }}
                                        </span>
                                    </td>

                                    <td class="hide-mobile" data-label="Registro">
                                        <span class="cm-date">
                                            {{ $registered ? $registered->format('d/m/Y') : '—' }}
                                            <small>{{ $registered ? $registered->diffForHumans() : '' }}</small>
                                        </span>
                                    </td>

                                    <td data-label="Acciones">
                                        <div class="cm-actions">
                                            <a href="{{ route('admin.users.show', $user->id) }}" class="cm-icon-btn"
                                                title="Ver detalle" aria-label="Ver detalle de {{ $name }}">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>

                                            <a href="{{ route('admin.users.edit', $user->id) }}" class="cm-icon-btn"
                                                title="Editar usuario" aria-label="Editar {{ $name }}">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>

                                            @if ($isSelf)
                                                <button type="button" class="cm-icon-btn" disabled
                                                    title="No puedes eliminar tu propia cuenta"
                                                    aria-label="No puedes eliminar tu propia cuenta">
                                                    <i class="fa-solid fa-lock"></i>
                                                </button>
                                            @else
                                                <button type="button" class="cm-icon-btn cm-icon-btn--danger"
                                                    title="Eliminar usuario" aria-label="Eliminar {{ $name }}"
                                                    data-delete-trigger data-delete-id="{{ $user->id }}"
                                                    data-delete-name="{{ $name }}">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>

                                                <form id="delete-form-{{ $user->id }}"
                                                    action="{{ route('admin.users.destroy', $user->id) }}"
                                                    method="POST" hidden>
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($users->hasPages())
                    <div class="cm-pagination">
                        <span class="cm-pagination__info">
                            Mostrando <strong>{{ $users->firstItem() }}</strong>–<strong>{{ $users->lastItem() }}</strong>
                            de <strong>{{ $users->total() }}</strong>
                        </span>

                        {{ $users->onEachSide(1)->links('pagination::bootstrap-4') }}
                    </div>
                @endif
            @endif
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
