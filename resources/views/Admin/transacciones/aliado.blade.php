{{-- resources/views/Admin/transacciones/aliado.blade.php --}}
@extends('layouts.admin')

@section('title', 'Mis Transacciones - Rumbero Extremo')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/transactions.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
@endpush

@section('content')
<div class="transactions-section-container">
    {{-- Header Moderno --}}
    <div class="page-header-modern">
        <div class="header-content">
            <div class="brand-badge">
                @if (auth()->user()->user_type === 'admin')
                    <span class="badge-admin">👑 ADMINISTRADOR</span>
                @else
                    <span class="badge-aliado">🏢 ALIADO</span>
                @endif
            </div>
            <h1 class="page-title">
                <span class="title-text">Mis</span>
                <span class="title-accent">Transacciones</span>
            </h1>
            <div class="page-subtitle">
                <i class="fa-regular fa-calendar"></i>
                <span>Historial de pagos recibidos · Actualizado {{ now()->format('d/m/Y H:i') }}</span>
            </div>
        </div>
        <div class="header-right">
            <button class="modern-primary-btn outline" id="refreshTable">
                <i class="fa-solid fa-rotate-right"></i>
                Actualizar
            </button>
        </div>
    </div>

    {{-- Tarjetas de resumen --}}
    <div class="summary-cards">
        <div class="summary-card">
            <div class="card-icon">
                <i class="fa-solid fa-coins"></i>
            </div>
            <div class="card-content">
                <span class="card-label">Total Recibido</span>
                <span class="card-value" id="total-recibido">
                    $ {{ number_format($totalRecibido ?? 0, 0, ',', '.') }}
                </span>
            </div>
        </div>

        <div class="summary-card">
            <div class="card-icon">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>
            <div class="card-content">
                <span class="card-label">Pendiente por Cobrar</span>
                <span class="card-value" id="pendiente-cobrar">
                    $ {{ number_format($pendienteCobrar ?? 0, 0, ',', '.') }}
                </span>
            </div>
        </div>

        <div class="summary-card">
            <div class="card-icon">
                <i class="fa-solid fa-arrow-right-arrow-left"></i>
            </div>
            <div class="card-content">
                <span class="card-label">Total Transacciones</span>
                <span class="card-value" id="total-transacciones">
                    {{ $totalTransacciones ?? 0 }}
                </span>
            </div>
        </div>
    </div>

    {{-- Selector de aliado (solo para admin) --}}
    @if (auth()->user()->user_type === 'admin')
        <div class="admin-selector">
            <div class="selector-card">
                <div class="selector-icon">
                    <i class="fa-solid fa-handshake"></i>
                </div>
                <div class="selector-content">
                    <h3>Ver transacciones de aliado</h3>
                    <select id="aliadoSelector" class="selector-select">
                        <option value="">Todos los aliados</option>
                        @foreach ($aliados ?? [] as $aliado)
                            <option value="{{ $aliado->id }}"
                                {{ request('aliado_id') == $aliado->id ? 'selected' : '' }}>
                                {{ $aliado->name }} - {{ $aliado->email }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    @endif

    {{-- Panel de control con filtros --}}
    <div class="control-panel">
        <div class="search-container-modern">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="searchInput" placeholder="Buscar por referencia, usuario...">
        </div>

        <div class="filter-actions">
            <select id="filterDate" class="filter-select-modern">
                <option value="all">📅 Todas las fechas</option>
                <option value="today">📅 Hoy</option>
                <option value="yesterday">📅 Ayer</option>
                <option value="week">📅 Esta semana</option>
                <option value="month">📅 Este mes</option>
            </select>

            <select id="filterStatus" class="filter-select-modern">
                <option value="all">📊 Todos los estados</option>
                <option value="confirmed">✅ Confirmada</option>
                <option value="pending_manual_confirmation">⏳ Pendiente</option>
                <option value="awaiting_review">⏳ En Revisión</option>
                <option value="failed">❌ Fallida</option>
            </select>

            <button class="filter-btn" id="applyFilters">
                <i class="fa-solid fa-filter"></i>
                Filtrar
            </button>

            <a href="{{ route('transacciones.exportar') }}?{{ http_build_query(request()->all()) }}" class="btn-export">
                <i class="fa-solid fa-download"></i>
                Exportar
            </a>
        </div>
    </div>

    {{-- Tabla de transacciones --}}
    <div class="modern-table-container">
        <table class="modern-data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Referencia</th>
                    @if (auth()->user()->user_type === 'admin')
                        <th>Aliado</th>
                    @endif
                    <th>Usuario</th>
                    <th>Original</th>
                    <th>Dto.</th>
                    <th>Comisión</th>
                    <th>Neto</th>
                    <th>Método</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="transactions-body">
                @forelse($transacciones as $transaccion)
                <tr class="table-row" data-id="{{ $transaccion->id }}">
                    <td>
                        <span class="id-badge">#{{ $transaccion->id }}</span>
                    </td>
                    <td>
                        <span class="reference-badge">{{ $transaccion->created_at->format('d/m/Y H:i') }}</span>
                    </td>
                    <td>
                        <span class="reference-badge">{{ $transaccion->reference_code }}</span>
                    </td>
                    
                    {{-- Mostrar columna de aliado solo para admin --}}
                    @if(auth()->user()->user_type === 'admin')
                    <td>
                        <div class="aliado-info">
                            <span class="aliado-name">{{ $transaccion->ally->name ?? 'N/A' }}</span>
                            <span class="aliado-email">{{ $transaccion->ally->email ?? '' }}</span>
                        </div>
                    </td>
                    @endif
                    
                    <td>
                        <div class="user-info">
                            <span class="user-name">{{ $transaccion->user->name ?? 'N/A' }}</span>
                            <span class="user-email">{{ $transaccion->user->email ?? '' }}</span>
                        </div>
                    </td>
                    <td>
                        <span class="amount-badge amount-original">${{ number_format($transaccion->original_amount, 0, ',', '.') }}</span>
                    </td>
                    <td>
                        @if($transaccion->discount_percentage > 0)
                            <span class="discount-badge">
                                <i class="fa-solid fa-tag"></i>
                                {{ $transaccion->discount_percentage }}%
                            </span>
                        @else
                            <span class="no-discount">-</span>
                        @endif
                    </td>
                    <td>
                        <span class="amount-badge commission-amount">${{ number_format($transaccion->platform_commission, 0, ',', '.') }}</span>
                    </td>
                    <td>
                        <span class="amount-badge net-amount">${{ number_format($transaccion->amount_to_ally, 0, ',', '.') }}</span>
                    </td>
                    <td>
                        <span class="payment-method-badge">
                            @if($transaccion->payment_method == 'pago_movil') 
                                <i class="fa-solid fa-mobile"></i> Pago Móvil
                            @elseif($transaccion->payment_method == 'transferencia_bancaria') 
                                <i class="fa-solid fa-building"></i> Transferencia
                            @else 
                                {{ $transaccion->payment_method }}
                            @endif
                        </span>
                    </td>
                    <td>
                        <span class="status-badge-modern badge-{{ $transaccion->status }}">
                            @switch($transaccion->status)
                                @case('confirmed') 
                                    <i class="fa-regular fa-circle-check"></i> Confirmada
                                    @break
                                @case('awaiting_review') 
                                    <i class="fa-regular fa-clock"></i> En Revisión
                                    @break
                                @case('pending_manual_confirmation') 
                                    <i class="fa-regular fa-hourglass"></i> Pendiente
                                    @break
                                @case('failed') 
                                    <i class="fa-regular fa-circle-xmark"></i> Fallida
                                    @break
                                @default 
                                    {{ $transaccion->status }}
                            @endswitch
                        </span>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="action-btn view-btn" onclick="verDetalle({{ $transaccion->id }})" title="Ver detalles">
                                <i class="fa-regular fa-eye"></i>
                                <span class="btn-tooltip">Ver detalles</span>
                            </button>
                            
                            @if(in_array($transaccion->status, ['pending_manual_confirmation', 'awaiting_review']) && auth()->user()->user_type === 'admin')
                                <button class="action-btn approve-btn" onclick="aprobarTransaccion({{ $transaccion->id }})" title="Confirmar">
                                    <i class="fa-regular fa-circle-check"></i>
                                    <span class="btn-tooltip">Confirmar</span>
                                </button>
                                <button class="action-btn reject-btn" onclick="rechazarTransaccion({{ $transaccion->id }})" title="Rechazar">
                                    <i class="fa-regular fa-circle-xmark"></i>
                                    <span class="btn-tooltip">Rechazar</span>
                                </button>
                            @endif
                            
                            <button class="action-btn print-btn" onclick="imprimirComprobante({{ $transaccion->id }})" title="Imprimir comprobante">
                                <i class="fa-regular fa-print"></i>
                                <span class="btn-tooltip">Imprimir</span>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ auth()->user()->user_type === 'admin' ? 12 : 11 }}" class="empty-state">
                        <div class="empty-content">
                            <i class="fa-regular fa-file-lines"></i>
                            <h3>No hay transacciones aún</h3>
                            <p>Las transacciones aparecerán cuando los usuarios realicen pagos</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginación Moderna --}}
    <div class="pagination-modern">
        <div class="pagination-info">
            <i class="fa-regular fa-chart-bar"></i>
            Mostrando {{ $transacciones->firstItem() ?? 0 }} - {{ $transacciones->lastItem() ?? 0 }} de {{ $transacciones->total() }} transacciones
        </div>
        <div class="pagination-links">
            {{ $transacciones->appends(request()->query())->links() }}
        </div>
    </div>
</div>

{{-- Modal de detalles --}}
<div class="modal" id="transactionModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Detalles de Transacción</h2>
            <button class="modal-close" onclick="closeModal()">
                <i class="fa-regular fa-times"></i>
            </button>
        </div>
        <div class="modal-body" id="transactionDetails">
            <div class="loading-spinner">
                <div class="spinner"></div>
                <p>Cargando detalles...</p>
            </div>
        </div>
    </div>
</div>

{{-- Modal de confirmación --}}
<div class="modal" id="confirmModal">
    <div class="modal-content modal-small">
        <div class="modal-header">
            <h2>Confirmar Acción</h2>
            <button class="modal-close" onclick="closeConfirmModal()">
                <i class="fa-regular fa-times"></i>
            </button>
        </div>
        <div class="modal-body" id="confirmMessage">
            ¿Estás seguro de realizar esta acción?
        </div>
        <div class="modal-footer">
            <button class="btn-modal cancel" onclick="closeConfirmModal()">
                <i class="fa-regular fa-ban"></i>
                Cancelar
            </button>
            <button class="btn-modal confirm" id="confirmActionBtn">
                <i class="fa-regular fa-check"></i>
                Confirmar
            </button>
        </div>
    </div>
</div>

<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@push('scripts')
<script src="{{ asset('js/admin/transactions.js') }}"></script>
<script>
    // Mostrar/ocultar botón de limpiar búsqueda (opcional)
    document.getElementById('searchInput')?.addEventListener('input', function() {
        // Funcionalidad adicional si es necesario
    });

    // Refresh button functionality
    document.getElementById('refreshTable')?.addEventListener('click', function() {
        location.reload();
    });
</script>
@endpush