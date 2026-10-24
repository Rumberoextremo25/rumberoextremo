@extends('layouts.admin')

@section('title', 'Aliado · ' . $ally->company_name)

@section('page_title_toolbar', 'Detalle del aliado')

@section('content')
    @php
        $statusMeta = [
            'activo' => ['label' => 'Activo', 'badge' => 'badge-success', 'icon' => 'fa-circle-check'],
            'pendiente' => ['label' => 'Pendiente', 'badge' => 'badge-warning', 'icon' => 'fa-clock'],
            'inactivo' => ['label' => 'Inactivo', 'badge' => 'badge-neutral', 'icon' => 'fa-circle-pause'],
        ];
        $statusKey = $ally->status ?: 'activo';
        $status = $statusMeta[$statusKey] ?? ['label' => ucfirst($statusKey), 'badge' => 'badge-neutral', 'icon' => 'fa-circle'];
        $registered = $ally->registered_at ?? $ally->created_at;
        $gallery = collect($ally->product_images ?? [])
            ->filter(fn ($path) => is_string($path) && \Illuminate\Support\Facades\Storage::disk('public')->exists($path))
            ->values();
    @endphp

    <div class="pf-page">

        {{-- ============================ CABECERA ============================ --}}
        <div class="page-header-block">
            <div>
                <h1 class="title-main">Ficha del <span class="title-accent">aliado</span></h1>
                <p class="page-subtitle">
                    {{ $ally->company_name }} · {{ $ally->businessType->name ?? 'Sin tipo' }} ·
                    <span class="badge {{ $status['badge'] }}">{{ $status['label'] }}</span>
                </p>
            </div>

            <div class="header-actions">
                <a href="{{ route('admin.aliados.index') }}" class="btn btn-secondary">
                    <i class="fa-solid fa-arrow-left"></i> Volver al listado
                </a>
                <a href="{{ route('admin.aliados.edit', $ally->id) }}" class="btn btn-primary">
                    <i class="fa-solid fa-pen"></i> Editar aliado
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
                                <span class="pf-avatar__initial">{{ mb_strtoupper(mb_substr($ally->company_name, 0, 1)) }}</span>
                                @if ($ally->image_src)
                                    <img src="{{ $ally->image_src }}" alt="Logo de {{ $ally->company_name }}" data-img>
                                @endif
                            </div>
                        </div>

                        <span class="pf-badge">{{ $ally->businessType->name ?? 'Aliado' }}</span>
                    </div>

                    <div class="pf-identity">
                        <h2 class="pf-name">{{ $ally->company_name }}</h2>
                        <p class="pf-email">
                            <i class="fa-solid fa-id-card"></i>
                            {{ $ally->company_rif ?: 'RIF no especificado' }}
                        </p>

                        <div class="pf-stats">
                            <div class="pf-stat">
                                <span class="pf-stat__value">{{ $ally->discount_label ?: '0%' }}</span>
                                <span class="pf-stat__label">Descuento</span>
                            </div>
                            <div class="pf-stat">
                                <span class="pf-stat__value">{{ $ally->category->name ?? '—' }}</span>
                                <span class="pf-stat__label">Categoría</span>
                            </div>
                            <div class="pf-stat">
                                <span class="pf-stat__value">{{ $ally->businessType->name ?? '—' }}</span>
                                <span class="pf-stat__label">Tipo</span>
                            </div>
                        </div>

                        <div class="pf-actions">
                            <a href="{{ route('admin.aliados.edit', $ally->id) }}" class="btn btn-primary">
                                <i class="fa-solid fa-pen"></i> Editar
                            </a>
                            <a href="{{ route('admin.aliados.index') }}" class="btn btn-secondary">
                                <i class="fa-solid fa-list"></i> Listado
                            </a>
                        </div>
                    </div>
                </section>

                <section class="pf-card">
                    <div class="pf-card__head">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                        <h3>Registro</h3>
                    </div>

                    <div class="pf-rows">
                        <div class="pf-row">
                            <span class="pf-row__label">Fecha de registro</span>
                            <span class="pf-row__value">{{ $registered ? \Carbon\Carbon::parse($registered)->format('d/m/Y') : '—' }}</span>
                        </div>
                        <div class="pf-row">
                            <span class="pf-row__label">Última actualización</span>
                            <span class="pf-row__value">{{ optional($ally->updated_at)->format('d/m/Y H:i') ?? '—' }}</span>
                        </div>
                        <div class="pf-row">
                            <span class="pf-row__label">Estado</span>
                            <span class="pf-row__value">
                                <span class="badge {{ $status['badge'] }}">{{ $status['label'] }}</span>
                            </span>
                        </div>
                    </div>
                </section>

                <section class="pf-card">
                    <div class="pf-card__head">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <h3>Eliminar aliado</h3>
                    </div>

                    <div class="pf-notes">
                        Se borrará la ficha del aliado y su relación con la app. Esta acción no se puede deshacer.
                    </div>

                    <div class="pf-actions pf-actions--single" style="padding:0 1.15rem 1.15rem;">
                        <button type="button" class="btn btn-danger" data-delete-trigger
                            data-delete-id="{{ $ally->id }}" data-delete-name="{{ $ally->company_name }}">
                            <i class="fa-solid fa-trash-can"></i> Eliminar aliado
                        </button>

                        <form id="delete-form-{{ $ally->id }}"
                            action="{{ route('admin.aliados.destroy', $ally->id) }}" method="POST" hidden>
                            @csrf
                            @method('DELETE')
                        </form>
                    </div>
                </section>
            </div>

            {{-- ========================= DATOS DETALLADOS ========================= --}}
            <div class="pf-col">

                <section class="pf-card">
                    <div class="pf-card__head">
                        <i class="fa-solid fa-store"></i>
                        <h3>Información del negocio</h3>
                    </div>

                    <div class="pf-rows">
                        <div class="pf-row">
                            <span class="pf-row__label">Empresa</span>
                            <span class="pf-row__value">{{ $ally->company_name }}</span>
                        </div>
                        <div class="pf-row">
                            <span class="pf-row__label">RIF</span>
                            <span class="pf-row__value">{{ $ally->company_rif ?: 'No especificado' }}</span>
                        </div>
                        <div class="pf-row">
                            <span class="pf-row__label">Categoría</span>
                            <span class="pf-row__value">{{ $ally->category->name ?? 'No especificada' }}</span>
                        </div>
                        <div class="pf-row">
                            <span class="pf-row__label">Subcategoría</span>
                            <span class="pf-row__value">{{ $ally->subCategory->name ?? 'No especificada' }}</span>
                        </div>
                        <div class="pf-row">
                            <span class="pf-row__label">Tipo de negocio</span>
                            <span class="pf-row__value">
                                <span class="badge badge-info">{{ $ally->businessType->name ?? 'Sin tipo' }}</span>
                            </span>
                        </div>
                        <div class="pf-row">
                            <span class="pf-row__label">Descuento</span>
                            <span class="pf-row__value">{{ $ally->discount_label ?: 'No definido' }}</span>
                        </div>
                        @if ($ally->website_url)
                            <div class="pf-row">
                                <span class="pf-row__label">Sitio web</span>
                                <span class="pf-row__value">
                                    <a href="{{ $ally->website_url }}" target="_blank" rel="noopener noreferrer">
                                        {{ $ally->website_url }}
                                    </a>
                                </span>
                            </div>
                        @endif
                        @if ($ally->company_address)
                            <div class="pf-row">
                                <span class="pf-row__label">Dirección</span>
                                <span class="pf-row__value">{{ $ally->company_address }}</span>
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
                            <span class="pf-row__label">Persona de contacto</span>
                            <span class="pf-row__value">{{ $ally->contact_person_name ?: 'No especificada' }}</span>
                        </div>
                        <div class="pf-row">
                            <span class="pf-row__label">Correo</span>
                            <span class="pf-row__value">
                                @if ($ally->contact_email)
                                    <a href="mailto:{{ $ally->contact_email }}">{{ $ally->contact_email }}</a>
                                @else
                                    No especificado
                                @endif
                            </span>
                        </div>
                        <div class="pf-row">
                            <span class="pf-row__label">Teléfono principal</span>
                            <span class="pf-row__value">{{ $ally->contact_phone ?: 'No especificado' }}</span>
                        </div>
                        @if ($ally->contact_phone_alt)
                            <div class="pf-row">
                                <span class="pf-row__label">Teléfono alternativo</span>
                                <span class="pf-row__value">{{ $ally->contact_phone_alt }}</span>
                            </div>
                        @endif
                    </div>
                </section>

                @if ($ally->user)
                    <section class="pf-card">
                        <div class="pf-card__head">
                            <i class="fa-solid fa-user-lock"></i>
                            <h3>Cuenta de acceso</h3>
                        </div>

                        <div class="pf-rows">
                            <div class="pf-row">
                                <span class="pf-row__label">Usuario</span>
                                <span class="pf-row__value">{{ $ally->user->name }}</span>
                            </div>
                            <div class="pf-row">
                                <span class="pf-row__label">Correo de acceso</span>
                                <span class="pf-row__value">{{ $ally->user->email }}</span>
                            </div>
                            <div class="pf-row">
                                <span class="pf-row__label">Creado</span>
                                <span class="pf-row__value">{{ optional($ally->user->created_at)->format('d/m/Y') ?? '—' }}</span>
                            </div>
                        </div>
                    </section>
                @endif

                <section class="pf-card">
                    <div class="pf-card__head">
                        <i class="fa-solid fa-clipboard-list"></i>
                        <h3>Descripción y notas</h3>
                    </div>

                    <div class="pf-notes {{ $ally->description ? '' : 'is-empty' }}">
                        {{ $ally->description ?: 'Sin descripción del negocio.' }}
                    </div>

                    @if ($ally->notes)
                        <div class="pf-notes">
                            <strong>Notas internas:</strong> {{ $ally->notes }}
                        </div>
                    @endif
                </section>

                @if ($gallery->isNotEmpty())
                    <section class="pf-card">
                        <div class="pf-card__head">
                            <i class="fa-regular fa-images"></i>
                            <h3>Galería de productos</h3>
                        </div>

                        <div class="db-quick" style="grid-template-columns:repeat(auto-fill,minmax(120px,1fr));">
                            @foreach ($gallery as $path)
                                <a href="{{ asset('storage/' . ltrim($path, '/')) }}" target="_blank"
                                    rel="noopener noreferrer" style="padding:0;overflow:hidden;">
                                    <img src="{{ asset('storage/' . ltrim($path, '/')) }}"
                                        alt="Imagen de {{ $ally->company_name }}" data-img
                                        style="width:100%;height:100%;object-fit:cover;aspect-ratio:1;">
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>
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
