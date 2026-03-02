{{-- resources/views/Admin/transacciones/detalle.blade.php --}}
@extends('layouts.admin')

@section('title', 'Detalle de Transacción - Rumbero Extremo')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="{{ asset('css/admin/transaction-detail.css') }}">
@endpush

@section('content')
<div class="transaction-detail-wrapper">
    {{-- Header con Gradiente - Siempre visible --}}
    <div class="detail-header">
        <div class="header-content">
            <div class="role-badge">
                @if (auth()->user()->role === 'admin')
                    <span class="badge-admin">
                        <i class="fa-solid fa-crown"></i>
                        ADMINISTRADOR
                    </span>
                @else
                    <span class="badge-aliado">
                        <i class="fa-solid fa-handshake"></i>
                        ALIADO
                    </span>
                @endif
            </div>
            <h1 class="page-title">
                <span class="title-main">Detalle de</span>
                <span class="title-accent">Transacción #{{ $transaccion->id }}</span>
            </h1>
            <div class="page-subtitle">
                <i class="fa-regular fa-calendar"></i>
                <span>Información completa de la transacción</span>
            </div>
        </div>
        <div class="header-actions">
            <a href="{{ route('transacciones.index') }}" class="btn-back">
                <i class="fa-solid fa-arrow-left"></i>
                Volver al listado
            </a>
            <button class="btn-print" onclick="window.print()">
                <i class="fa-solid fa-print"></i>
                Imprimir
            </button>
        </div>
    </div>

    {{-- Tarjetas de Resumen - Datos básicos rápidos --}}
    <div class="summary-grid">
        <div class="summary-card" data-color="purple">
            <div class="card-icon">
                <i class="fa-solid fa-coins"></i>
            </div>
            <div class="card-content">
                <span class="card-label">Monto Original</span>
                <span class="card-value">$ {{ number_format($transaccion->original_amount, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="summary-card" data-color="orange">
            <div class="card-icon">
                <i class="fa-solid fa-percent"></i>
            </div>
            <div class="card-content">
                <span class="card-label">Descuento</span>
                <span class="card-value">{{ $transaccion->discount_percentage }}%</span>
            </div>
        </div>

        <div class="summary-card" data-color="red">
            <div class="card-icon">
                <i class="fa-solid fa-hand-holding-dollar"></i>
            </div>
            <div class="card-content">
                <span class="card-label">Comisión</span>
                <span class="card-value">$ {{ number_format($transaccion->platform_commission, 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="summary-card" data-color="green">
            <div class="card-icon">
                <i class="fa-solid fa-money-bill-wave"></i>
            </div>
            <div class="card-content">
                <span class="card-label">Neto para Aliado</span>
                <span class="card-value">$ {{ number_format($transaccion->amount_to_ally, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    {{-- Información Principal - Carga inmediata --}}
    <div class="detail-grid">
        {{-- Información de la Transacción --}}
        <div class="info-card">
            <div class="card-header">
                <i class="fa-solid fa-receipt"></i>
                <h3>Información de la Transacción</h3>
            </div>
            <div class="info-content">
                <div class="info-row">
                    <span class="info-label">ID Transacción:</span>
                    <span class="info-value"><strong>#{{ $transaccion->id }}</strong></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Código de Referencia:</span>
                    <span class="info-value"><code>{{ $transaccion->reference_code }}</code></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Fecha y Hora:</span>
                    <span class="info-value">{{ $transaccion->created_at->format('d/m/Y H:i:s') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Estado:</span>
                    <span class="info-value">
                        <span class="status-badge status-{{ $transaccion->status }}">
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
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Método de Pago:</span>
                    <span class="info-value">
                        <span class="method-badge">
                            @if($transaccion->payment_method == 'pago_movil') 
                                <i class="fa-solid fa-mobile"></i> Pago Móvil
                            @elseif($transaccion->payment_method == 'transferencia_bancaria') 
                                <i class="fa-solid fa-building"></i> Transferencia Bancaria
                            @else 
                                {{ $transaccion->payment_method }}
                            @endif
                        </span>
                    </span>
                </div>
            </div>
        </div>

        {{-- Información del Usuario --}}
        <div class="info-card">
            <div class="card-header">
                <i class="fa-solid fa-user"></i>
                <h3>Información del Usuario</h3>
            </div>
            <div class="info-content">
                <div class="info-row">
                    <span class="info-label">Nombre:</span>
                    <span class="info-value">{{ $transaccion->user->name ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{{ $transaccion->user->email ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Teléfono:</span>
                    <span class="info-value">{{ $transaccion->user->phone ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        {{-- Información del Aliado --}}
        <div class="info-card">
            <div class="card-header">
                <i class="fa-solid fa-handshake"></i>
                <h3>Información del Aliado</h3>
            </div>
            <div class="info-content">
                <div class="info-row">
                    <span class="info-label">Nombre:</span>
                    <span class="info-value">{{ $transaccion->ally->name ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">{{ $transaccion->ally->email ?? 'N/A' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Teléfono:</span>
                    <span class="info-value">{{ $transaccion->ally->phone ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        {{-- Datos de Confirmación - Carga bajo demanda --}}
        @if($transaccion->confirmation_data)
        <div class="info-card full-width" id="confirmationDataCard">
            <div class="card-header">
                <i class="fa-solid fa-circle-check"></i>
                <h3>Datos de Confirmación</h3>
                <button class="btn-toggle-json" onclick="toggleJsonData()">
                    <i class="fa-solid fa-chevron-down"></i>
                </button>
            </div>
            <div class="info-content" id="jsonDataContent" style="display: none;">
                <pre class="json-data">{{ json_encode($transaccion->confirmation_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
        </div>
        @endif
    </div>

    {{-- Acciones para Admin --}}
    @if(in_array($transaccion->status, ['pending_manual_confirmation', 'awaiting_review']) && auth()->user()->role === 'admin')
    <div class="action-footer">
        <div class="action-buttons">
            <button class="btn-approve" onclick="aprobarTransaccion({{ $transaccion->id }})">
                <i class="fa-regular fa-circle-check"></i>
                Confirmar Transacción
            </button>
            <button class="btn-reject" onclick="rechazarTransaccion({{ $transaccion->id }})">
                <i class="fa-regular fa-circle-xmark"></i>
                Rechazar Transacción
            </button>
        </div>
    </div>
    @endif
</div>

{{-- Modal de Confirmación --}}
<div class="modal-modern" id="confirmModal">
    <div class="modal-card" style="max-width: 450px;">
        <div class="modal-icon warning">
            <i class="fa-solid fa-exclamation-triangle"></i>
        </div>
        <h3 class="modal-title" id="confirmModalTitle">Confirmar Acción</h3>
        <div class="modal-body">
            <p id="confirmMessage">¿Estás seguro de realizar esta acción?</p>
        </div>
        <div class="modal-actions">
            <button class="btn-secondary" onclick="closeConfirmModal()">
                <i class="fa-regular fa-ban"></i>
                Cancelar
            </button>
            <button class="btn-danger" id="confirmActionBtn">
                <i class="fa-regular fa-check"></i>
                Confirmar
            </button>
        </div>
    </div>
</div>

<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Animaciones solo para elementos visibles inicialmente
        const visibleCards = document.querySelectorAll('.summary-card, .info-card:not(.full-width)');
        visibleCards.forEach((card, index) => {
            card.style.animation = `fadeInUp 0.3s ease forwards ${index * 0.05}s`;
        });
    });

    // Función para toggle JSON data (carga bajo demanda)
    function toggleJsonData() {
        const content = document.getElementById('jsonDataContent');
        const button = document.querySelector('.btn-toggle-json i');
        
        if (content.style.display === 'none') {
            content.style.display = 'block';
            button.classList.remove('fa-chevron-down');
            button.classList.add('fa-chevron-up');
        } else {
            content.style.display = 'none';
            button.classList.remove('fa-chevron-up');
            button.classList.add('fa-chevron-down');
        }
    }

    // Función para aprobar transacción
    function aprobarTransaccion(id) {
        document.getElementById('confirmModalTitle').textContent = 'Aprobar Transacción';
        document.getElementById('confirmMessage').innerHTML = 
            `¿Estás seguro de aprobar la transacción #${id}? Esta acción no se puede deshacer.`;
        
        document.getElementById('confirmActionBtn').onclick = function() {
            fetch(`/admin/transacciones/${id}/aprobar`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al procesar la solicitud');
            })
            .finally(() => {
                closeConfirmModal();
            });
        };
        
        document.getElementById('confirmModal').classList.add('active');
    }

    // Función para rechazar transacción
    function rechazarTransaccion(id) {
        document.getElementById('confirmModalTitle').textContent = 'Rechazar Transacción';
        document.getElementById('confirmMessage').innerHTML = 
            `¿Estás seguro de rechazar la transacción #${id}? Esta acción no se puede deshacer.`;
        
        document.getElementById('confirmActionBtn').onclick = function() {
            fetch(`/admin/transacciones/${id}/rechazar`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al procesar la solicitud');
            })
            .finally(() => {
                closeConfirmModal();
            });
        };
        
        document.getElementById('confirmModal').classList.add('active');
    }

    // Cerrar modal
    function closeConfirmModal() {
        document.getElementById('confirmModal').classList.remove('active');
    }

    // Cerrar modal con Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeConfirmModal();
        }
    });
</script>
@endpush