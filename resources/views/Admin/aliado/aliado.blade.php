@extends('layouts.admin')

@section('title', 'Aliados')

@section('page_title_toolbar', 'Gestión de aliados')

@section('content')
    @php
        $statusMeta = [
            'activo' => ['label' => 'Activo', 'badge' => 'badge-success', 'icon' => 'fa-circle-check'],
            'pendiente' => ['label' => 'Pendiente', 'badge' => 'badge-warning', 'icon' => 'fa-clock'],
            'inactivo' => ['label' => 'Inactivo', 'badge' => 'badge-neutral', 'icon' => 'fa-circle-pause'],
        ];
        $hasFilters = $q !== '' || ctype_digit((string) $tipo) || in_array($estado, ['activo', 'pendiente', 'inactivo'], true);
    @endphp

    <div class="cm-page">

        {{-- ============================ CABECERA ============================ --}}
        <div class="page-header-block">
            <div>
                <h1 class="title-main">Gestión de <span class="title-accent">aliados</span></h1>
                <p class="page-subtitle">
                    Locales y marcas asociadas: datos de contacto, categoría, descuento y estado.
                </p>
            </div>

            <a href="{{ route('admin.aliados.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Nuevo aliado
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
                <span class="cm-stat__icon"><i class="fa-solid fa-handshake"></i></span>
                <span class="cm-stat__body">
                    <span class="cm-stat__value">{{ number_format($stats['total']) }}</span>
                    <span class="cm-stat__label">Total</span>
                </span>
            </div>

            <div class="cm-stat">
                <span class="cm-stat__icon cm-stat__icon--green"><i class="fa-solid fa-circle-check"></i></span>
                <span class="cm-stat__body">
                    <span class="cm-stat__value">{{ number_format($stats['active']) }}</span>
                    <span class="cm-stat__label">Activos</span>
                </span>
            </div>

            <div class="cm-stat">
                <span class="cm-stat__icon cm-stat__icon--gold"><i class="fa-solid fa-clock"></i></span>
                <span class="cm-stat__body">
                    <span class="cm-stat__value">{{ number_format($stats['pending']) }}</span>
                    <span class="cm-stat__label">Pendientes</span>
                </span>
            </div>

            <div class="cm-stat">
                <span class="cm-stat__icon cm-stat__icon--red"><i class="fa-solid fa-ban"></i></span>
                <span class="cm-stat__body">
                    <span class="cm-stat__value">{{ number_format($stats['inactive']) }}</span>
                    <span class="cm-stat__label">Inactivos</span>
                </span>
            </div>
        </div>

        {{-- ============================= FILTROS ============================= --}}
        <div class="cm-filters">
            <form method="GET" action="{{ route('admin.aliados.index') }}" data-cm-filters>
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="search" name="q" value="{{ $q }}"
                        placeholder="Buscar por empresa, RIF, contacto o correo…" aria-label="Buscar aliados">
                </div>

                <select name="tipo" class="filter-select" aria-label="Filtrar por tipo de negocio">
                    <option value="">Todos los tipos</option>
                    @foreach ($businessTypes as $type)
                        <option value="{{ $type->id }}" @selected((string) $tipo === (string) $type->id)>{{ $type->name }}</option>
                    @endforeach
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
                    <a href="{{ route('admin.aliados.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fa-solid fa-xmark"></i> Limpiar
                    </a>
                @endif
            </form>

            <span class="cm-filters__count">
                {{ $allies->total() }} {{ $allies->total() === 1 ? 'aliado' : 'aliados' }}
            </span>
        </div>

        {{-- ============================== TABLA ============================== --}}
        <div class="cm-table-card">
            @if ($allies->isEmpty())
                <div class="cm-empty">
                    <i class="fa-solid fa-handshake-slash"></i>
                    @if ($hasFilters)
                        <h3>Sin resultados</h3>
                        <p>No hay aliados que coincidan con la búsqueda o los filtros.</p>
                        <a href="{{ route('admin.aliados.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fa-solid fa-xmark"></i> Quitar filtros
                        </a>
                    @else
                        <h3>Todavía no hay aliados</h3>
                        <p>Registra el primer aliado comercial para que aparezca en la app.</p>
                        <a href="{{ route('admin.aliados.create') }}" class="btn btn-primary" style="margin-top:.5rem;">
                            <i class="fa-solid fa-plus"></i> Crear aliado
                        </a>
                    @endif
                </div>
            @else
                <div class="rx-table-scroll">
                    <table class="rx-stack-table">
                        <thead>
                            <tr>
                                <th>Empresa</th>
                                <th class="hide-mobile">Categoría</th>
                                <th>Tipo</th>
                                <th>Descuento</th>
                                <th>Estado</th>
                                <th class="hide-mobile">Registro</th>
                                <th style="text-align:right;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($allies as $ally)
                                @php
                                    $statusKey = $ally->status ?: 'activo';
                                    $status = $statusMeta[$statusKey] ?? ['label' => ucfirst($statusKey), 'badge' => 'badge-neutral', 'icon' => 'fa-circle'];
                                    $registered = $ally->registered_at ?? $ally->created_at;
                                @endphp
                                <tr>
                                    <td data-label="Empresa">
                                        <div class="cm-ally">
                                            <div class="cm-ally__logo">
                                                <span>{{ mb_strtoupper(mb_substr($ally->company_name, 0, 1)) }}</span>
                                                @if ($ally->image_src)
                                                    <img src="{{ $ally->image_src }}" alt="Logo de {{ $ally->company_name }}"
                                                        loading="lazy" data-img>
                                                @endif
                                            </div>

                                            <div class="cm-ally__text">
                                                <span class="cm-ally__name">{{ $ally->company_name }}</span>
                                                @if ($ally->contact_email)
                                                    <span class="cm-user__email">{{ $ally->contact_email }}</span>
                                                @endif
                                                @if ($ally->contact_person_name)
                                                    <span class="cm-ally__link">
                                                        <i class="fa-regular fa-user"></i> {{ $ally->contact_person_name }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    <td class="hide-mobile" data-label="Categoría">
                                        <span class="cm-desc">{{ $ally->category->name ?? 'Sin categoría' }}</span>
                                        @if ($ally->subCategory)
                                            <span class="cm-date"><small>{{ $ally->subCategory->name }}</small></span>
                                        @endif
                                    </td>

                                    <td data-label="Tipo">
                                        <span class="badge badge-info">
                                            <i class="fa-solid fa-tag"></i> {{ $ally->businessType->name ?? 'Sin tipo' }}
                                        </span>
                                    </td>

                                    <td data-label="Descuento">
                                        @if ($ally->discount)
                                            <span class="cm-chip cm-chip--primary">{{ $ally->discount_label }}</span>
                                        @else
                                            <span class="cm-chip">—</span>
                                        @endif
                                    </td>

                                    <td data-label="Estado">
                                        <span class="badge {{ $status['badge'] }}">
                                            <i class="fa-solid {{ $status['icon'] }}"></i> {{ $status['label'] }}
                                        </span>
                                    </td>

                                    <td class="hide-mobile" data-label="Registro">
                                        <span class="cm-date">
                                            {{ $registered ? \Carbon\Carbon::parse($registered)->format('d/m/Y') : '—' }}
                                            <small>{{ $registered ? \Carbon\Carbon::parse($registered)->diffForHumans() : '' }}</small>
                                        </span>
                                    </td>

                                    <td data-label="Acciones">
                                        <div class="cm-actions">
                                            <a href="{{ route('admin.aliados.show', $ally->id) }}" class="cm-icon-btn"
                                                title="Ver detalle" aria-label="Ver detalle de {{ $ally->company_name }}">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>

                                            <a href="{{ route('admin.aliados.edit', $ally->id) }}" class="cm-icon-btn"
                                                title="Editar aliado" aria-label="Editar {{ $ally->company_name }}">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>

                                            <button type="button" class="cm-icon-btn cm-icon-btn--danger"
                                                title="Eliminar aliado" aria-label="Eliminar {{ $ally->company_name }}"
                                                data-delete-trigger data-delete-id="{{ $ally->id }}"
                                                data-delete-name="{{ $ally->company_name }}">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>

                                            <form id="delete-form-{{ $ally->id }}"
                                                action="{{ route('admin.aliados.destroy', $ally->id) }}" method="POST"
                                                hidden>
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($allies->hasPages())
                    <div class="cm-pagination">
                        <span class="cm-pagination__info">
                            Mostrando <strong>{{ $allies->firstItem() }}</strong>–<strong>{{ $allies->lastItem() }}</strong>
                            de <strong>{{ $allies->total() }}</strong>
                        </span>

                        {{ $allies->onEachSide(1)->links('pagination::bootstrap-4') }}
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
                    <h2 id="deleteTitle">Eliminar aliado</h2>
                    <p>¿Seguro que quieres eliminar a <span class="cf-confirm__name" data-delete-name></span>?</p>
                    <p>Se borrará su ficha y no se puede deshacer.</p>
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
