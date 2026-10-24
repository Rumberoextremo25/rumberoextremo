@extends('layouts.admin')

@section('title', 'Solicitudes')

@section('page_title_toolbar', 'Solicitudes de la web')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/pages.css') }}">
@endpush

@section('content')
    <div class="requests-page">

        {{-- ============================ CABECERA ============================ --}}
        <div class="page-header-block">
            <div>
                <h1 class="title-main">Solicitudes <span class="title-accent">de la web</span></h1>
                <p class="page-subtitle">
                    Locales que quieren ser aliados y rumberos que quieren afiliarse. Revisa, contacta y cambia el estado.
                </p>
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

        {{-- ============================ ESTADÍSTICAS ============================ --}}
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-store"></i></div>
                <div class="stat-value">{{ $stats['locales'] }}</div>
                <div class="stat-label">Solicitudes de locales</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-hourglass-half"></i></div>
                <div class="stat-value">{{ $stats['locales_pendientes'] }}</div>
                <div class="stat-label">Locales pendientes</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-user-group"></i></div>
                <div class="stat-value">{{ $stats['afiliados'] }}</div>
                <div class="stat-label">Solicitudes de rumberos</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-bell"></i></div>
                <div class="stat-value">{{ $stats['afiliados_pendientes'] }}</div>
                <div class="stat-label">Rumberos pendientes</div>
            </div>
        </div>

        {{-- ========================= CORREO DE VENTAS ========================= --}}
        <div class="card" style="margin-bottom:1.25rem;">
            <div class="card-header">
                <h3><i class="fa-solid fa-envelope-open-text"></i> Correo que recibe las solicitudes</h3>
            </div>

            @if ($canManage)
                <form method="POST" action="{{ route('admin.requests.sales-email') }}" class="sales-email-form">
                    @csrf
                    <div class="form-group" style="margin:0;flex:1 1 280px;">
                        <label for="sales_email">Destinatario de ventas</label>
                        <input type="email" id="sales_email" name="sales_email" class="form-control"
                            value="{{ $salesEmail }}" required>
                        <span class="field-hint">Cada nueva solicitud se envía a este buzón.</span>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar correo
                    </button>
                </form>
            @else
                <div class="sales-email-form">
                    <div class="form-group" style="margin:0;flex:1 1 280px;">
                        <label>Destinatario de ventas</label>
                        <p class="field-hint" style="margin:0;">
                            <strong>{{ $salesEmail }}</strong><br>
                            Solo un administrador puede cambiar este correo.
                        </p>
                    </div>
                </div>
            @endif
        </div>

        {{-- ======================== FILTROS Y PESTAÑAS ======================== --}}
        <div class="card">
            <div class="card-header">
                <div class="tabs" role="tablist">
                    <a href="{{ route('admin.requests.index', ['tipo' => 'locales']) }}"
                        class="tab {{ $type === 'locales' ? 'active' : '' }}">
                        <i class="fa-solid fa-store"></i> Locales ({{ $stats['locales'] }})
                    </a>
                    <a href="{{ route('admin.requests.index', ['tipo' => 'afiliados']) }}"
                        class="tab {{ $type === 'afiliados' ? 'active' : '' }}">
                        <i class="fa-solid fa-user-group"></i> Rumberos ({{ $stats['afiliados'] }})
                    </a>
                </div>

                <form method="GET" action="{{ route('admin.requests.index') }}" class="requests-filters">
                    <input type="hidden" name="tipo" value="{{ $type }}">

                    <select name="estado" class="form-control" onchange="this.form.submit()">
                        <option value="">Todos los estados</option>
                        @foreach ($statuses as $key => $label)
                            <option value="{{ $key }}" {{ $status === $key ? 'selected' : '' }}>{{ $label }}
                            </option>
                        @endforeach
                    </select>

                    <input type="search" name="buscar" value="{{ $search }}" class="form-control"
                        placeholder="Buscar por nombre o correo">
                </form>
            </div>

            {{-- ============================== TABLA ============================== --}}
            @if ($requests->isEmpty())
                <div class="empty-state">
                    <i class="fa-regular fa-inbox"></i>
                    <h3>No hay solicitudes</h3>
                    <p>Cuando alguien envíe el formulario desde la web, aparecerá aquí.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                @if ($type === 'locales')
                                    <th>Empresa</th>
                                    <th>Contacto</th>
                                    <th>Alianza</th>
                                @else
                                    <th>Rumbero</th>
                                    <th>Red social</th>
                                    <th>Seguidores</th>
                                @endif
                                <th>Recibida</th>
                                <th>Estado</th>
                                <th>Detalle</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($requests as $item)
                                @php
                                    $statusKey = $item->status ?? 'pendiente';
                                    $badge = match ($statusKey) {
                                        'aceptado' => 'badge-success',
                                        'descartado' => 'badge-danger',
                                        'contactado', 'en_revision' => 'badge-warning',
                                        default => 'badge-info',
                                    };
                                @endphp
                                <tr>
                                    @if ($type === 'locales')
                                        <td><strong>{{ $item->company_name }}</strong></td>
                                        <td>
                                            {{ $item->contact_person }}
                                            <br><small class="text-muted">{{ $item->email }}</small>
                                        </td>
                                        <td><span class="badge badge-neutral">{{ $item->partnership_type }}</span></td>
                                    @else
                                        <td>
                                            <strong>{{ $item->full_name }}</strong>
                                            <br><small class="text-muted">{{ $item->email }}</small>
                                        </td>
                                        <td>
                                            <a href="{{ $item->social_media_link }}" target="_blank"
                                                rel="noopener">Ver perfil</a>
                                        </td>
                                        <td>{{ number_format($item->followers_count) }}</td>
                                    @endif

                                    <td>
                                        {{ $item->created_at?->format('d/m/Y') }}
                                        <br><small class="text-muted">{{ $item->created_at?->format('H:i') }}</small>
                                    </td>

                                    <td>
                                        <span class="badge {{ $badge }}">{{ $statuses[$statusKey] ?? $statusKey }}</span>
                                    </td>

                                    <td>
                                        <details class="request-detail">
                                            <summary class="action-btn">
                                                <i class="fa-solid fa-chevron-down"></i> Ver
                                            </summary>

                                            <div class="request-detail__body">
                                                @if ($type === 'locales')
                                                    <p><strong>Teléfono:</strong> {{ $item->phone ?: '—' }}</p>
                                                    <p><strong>Web:</strong> {{ $item->website ?: '—' }}</p>
                                                    <p><strong>Mensaje:</strong><br>{{ $item->message }}</p>
                                                @else
                                                    <p><strong>Teléfono:</strong> {{ $item->phone ?: '—' }}</p>
                                                    <p><strong>Tipo:</strong> {{ $item->affiliate_type }}</p>
                                                    <p><strong>Mensaje:</strong><br>{{ $item->message ?: '—' }}</p>
                                                @endif

                                                @if ($canManage)
                                                    <form method="POST"
                                                        action="{{ route('admin.requests.status', ['type' => $type, 'id' => $item->id]) }}"
                                                        class="request-status-form">
                                                        @csrf
                                                        @method('PUT')

                                                        <select name="status" class="form-control">
                                                            @foreach ($statuses as $key => $label)
                                                                <option value="{{ $key }}"
                                                                    {{ $statusKey === $key ? 'selected' : '' }}>
                                                                    {{ $label }}</option>
                                                            @endforeach
                                                        </select>

                                                        <input type="text" name="admin_notes" class="form-control"
                                                            value="{{ $item->admin_notes }}"
                                                            placeholder="Nota interna (opcional)">

                                                        <button type="submit" class="btn btn-primary btn-sm">
                                                            <i class="fa-solid fa-check"></i> Guardar
                                                        </button>
                                                    </form>

                                                    <p class="field-hint" style="margin:.4rem 0 0;">
                                                        @if ($type === 'locales')
                                                            Al marcar <strong>Aceptado</strong> se crea el aliado en
                                                            Aliados y su usuario (rol aliado, pendiente).
                                                        @else
                                                            Al marcar <strong>Aceptado</strong> se crea el usuario
                                                            rumbero activo en Usuarios.
                                                        @endif
                                                    </p>
                                                @else
                                                    <p class="field-hint" style="margin:.4rem 0 0;">
                                                        <i class="fa-solid fa-lock"></i>
                                                        Solo un administrador puede cambiar el estado de esta solicitud.
                                                    </p>
                                                @endif

                                                <p class="request-detail__meta">
                                                    <a href="mailto:{{ $item->email }}" class="btn btn-secondary btn-sm">
                                                        <i class="fa-solid fa-reply"></i> Responder por correo
                                                    </a>
                                                    @if ($item->reviewed_at)
                                                        <small>Revisada
                                                            {{ \Illuminate\Support\Carbon::parse($item->reviewed_at)->diffForHumans() }}</small>
                                                    @endif
                                                </p>
                                            </div>
                                        </details>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($requests->hasPages())
                    <div class="card-footer">
                        {{ $requests->onEachSide(1)->links('pagination::default') }}
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection
