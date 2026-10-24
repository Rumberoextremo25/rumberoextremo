@extends('layouts.admin')

@section('title', 'Aliados comerciales')

@section('page_title_toolbar', 'Aliados comerciales')


@section('content')
    <div class="cm-page">

        {{-- ============================ CABECERA ============================ --}}
        <div class="page-header-block">
            <div>
                <h1 class="title-main">Aliados <span class="title-accent">comerciales</span></h1>
                <p class="page-subtitle">
                    Los locales y marcas que aparecen en la app. Revisa su ficha, su logo y su estado de publicación.
                </p>
            </div>

            <a href="{{ route('admin.commercial-allies.create') }}" class="btn btn-primary">
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
                <span>
                    <span class="cm-stat__value">{{ $stats['total'] }}</span>
                    <span class="cm-stat__label">Total</span>
                </span>
            </div>

            <div class="cm-stat">
                <span class="cm-stat__icon cm-stat__icon--green"><i class="fa-solid fa-circle-check"></i></span>
                <span>
                    <span class="cm-stat__value">{{ $stats['active'] }}</span>
                    <span class="cm-stat__label">Activos</span>
                </span>
            </div>

            <div class="cm-stat">
                <span class="cm-stat__icon cm-stat__icon--gold"><i class="fa-solid fa-star"></i></span>
                <span>
                    <span class="cm-stat__value">{{ number_format((float) $stats['rating'], 1, ',', '.') }}</span>
                    <span class="cm-stat__label">Rating promedio</span>
                </span>
            </div>

            <div class="cm-stat">
                <span class="cm-stat__icon cm-stat__icon--red"><i class="fa-solid fa-circle-pause"></i></span>
                <span>
                    <span class="cm-stat__value">{{ $stats['inactive'] }}</span>
                    <span class="cm-stat__label">Inactivos</span>
                </span>
            </div>
        </div>

        {{-- ============================= FILTROS ============================= --}}
        <div class="cm-filters">
            <form method="GET" action="{{ route('admin.commercial-allies.index') }}" data-cm-filters>
                <div class="search-box">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="search" name="q" value="{{ $q }}" placeholder="Buscar por nombre, descripción o web…"
                        aria-label="Buscar aliados comerciales">
                </div>

                <select name="estado" class="filter-select" aria-label="Filtrar por estado">
                    <option value="">Todos los estados</option>
                    <option value="1" @selected($estado === '1')>Activos</option>
                    <option value="0" @selected($estado === '0')>Inactivos</option>
                </select>

                <noscript>
                    <button type="submit" class="btn btn-secondary btn-sm">
                        <i class="fa-solid fa-filter"></i> Filtrar
                    </button>
                </noscript>

                @if ($q !== '' || $estado === '1' || $estado === '0')
                    <a href="{{ route('admin.commercial-allies.index') }}" class="btn btn-secondary btn-sm">
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
                @if ($q !== '' || $estado === '1' || $estado === '0')
                    <div class="cm-empty">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <h3>Sin resultados</h3>
                        <p>No hay aliados que coincidan con la búsqueda o el filtro.</p>
                        <a href="{{ route('admin.commercial-allies.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fa-solid fa-xmark"></i> Quitar filtros
                        </a>
                    </div>
                @else
                    <div class="cm-empty">
                        <i class="fa-solid fa-store"></i>
                        <h3>Todavía no hay aliados comerciales</h3>
                        <p>Registra el primero para que aparezca en la app.</p>
                        <a href="{{ route('admin.commercial-allies.create') }}" class="btn btn-primary"
                            style="margin-top:.5rem;">
                            <i class="fa-solid fa-plus"></i> Crear aliado
                        </a>
                    </div>
                @endif
            @else
                <div class="rx-table-scroll">
                    <table class="rx-stack-table">
                        <thead>
                            <tr>
                                <th>Aliado</th>
                                <th class="hide-mobile">Descripción</th>
                                <th>Rating</th>
                                <th>Estado</th>
                                <th class="hide-mobile">Registro</th>
                                <th style="text-align:right;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($allies as $ally)
                                <tr>
                                    <td data-label="Aliado">
                                        <div class="cm-ally">
                                            <div class="cm-ally__logo">
                                                <span>{{ mb_strtoupper(mb_substr($ally->name, 0, 1)) }}</span>
                                                @if ($ally->logo_src)
                                                    <img src="{{ $ally->logo_src }}" alt="Logo de {{ $ally->name }}"
                                                        loading="lazy" data-img>
                                                @endif
                                            </div>

                                            <div class="cm-ally__text">
                                                <span class="cm-ally__name">{{ $ally->name }}</span>

                                                @if ($ally->website_url)
                                                    <a class="cm-ally__link" href="{{ $ally->website_url }}"
                                                        target="_blank" rel="noopener noreferrer">
                                                        <i class="fa-solid fa-link"></i>
                                                        {{ parse_url($ally->website_url, PHP_URL_HOST) ?: $ally->website_url }}
                                                    </a>
                                                @else
                                                    <span class="cm-ally__link">Sin sitio web</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    <td class="hide-mobile" data-label="Descripción">
                                        <p class="cm-desc">{{ $ally->description ?: '—' }}</p>
                                    </td>

                                    <td data-label="Rating">
                                        <span class="cm-rating">
                                            <span class="cm-rating__stars" aria-hidden="true">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    <i class="fa-{{ $i <= round((float) $ally->rating) ? 'solid' : 'regular' }} fa-star"></i>
                                                @endfor
                                            </span>
                                            <span class="cm-rating__value">{{ number_format((float) $ally->rating, 1, ',', '.') }}</span>
                                        </span>
                                    </td>

                                    <td data-label="Estado">
                                        @if ($ally->is_active)
                                            <span class="badge badge-success">
                                                <i class="fa-solid fa-circle-check"></i> Activo
                                            </span>
                                        @else
                                            <span class="badge badge-neutral">
                                                <i class="fa-solid fa-circle-pause"></i> Inactivo
                                            </span>
                                        @endif
                                    </td>

                                    <td class="hide-mobile" data-label="Registro">
                                        <span class="cm-date">
                                            {{ optional($ally->created_at)->format('d/m/Y') ?? '—' }}
                                            <small>{{ optional($ally->created_at)->diffForHumans() ?? '' }}</small>
                                        </span>
                                    </td>

                                    <td data-label="Acciones">
                                        <div class="cm-actions">
                                            <a href="{{ route('admin.commercial-allies.show', $ally->id) }}"
                                                class="cm-icon-btn" title="Ver detalle"
                                                aria-label="Ver detalle de {{ $ally->name }}">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>

                                            <a href="{{ route('admin.commercial-allies.edit', $ally->id) }}"
                                                class="cm-icon-btn" title="Editar aliado"
                                                aria-label="Editar {{ $ally->name }}">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>

                                            <button type="button" class="cm-icon-btn cm-icon-btn--danger"
                                                title="Eliminar aliado" aria-label="Eliminar {{ $ally->name }}"
                                                data-delete-trigger data-delete-id="{{ $ally->id }}"
                                                data-delete-name="{{ $ally->name }}">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>

                                            <form id="delete-form-{{ $ally->id }}"
                                                action="{{ route('admin.commercial-allies.destroy', $ally->id) }}"
                                                method="POST" hidden>
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
                    <h2 id="deleteTitle">Eliminar aliado comercial</h2>
                    <p>¿Seguro que quieres eliminar a <span class="cf-confirm__name" data-delete-name></span>?</p>
                    <p>También se borrará su logo. Esta acción no se puede deshacer.</p>
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
