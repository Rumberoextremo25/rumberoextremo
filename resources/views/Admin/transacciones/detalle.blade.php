{{-- resources/views/Admin/transacciones/detalle.blade.php --}}
@extends('layouts.admin')

@section('title', 'Detalle de Transacción - Rumbero Extremo')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/transactions.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
@endpush

@section('content')
<div class="transactions-section-container">
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
                <span class="title-text">Detalle de</span>
                <span class="title-accent">Transacción #{{ $transaccion->id }}</span>
            </h1>
            <div class="page-subtitle">
                <i class="fa-regular fa-calendar"></i>
                <span>Información completa de la transacción</span>
            </div>
        </div>
        <div class="header-right">
            <a href="{{ route('transacciones.index') }}" class="modern-primary-btn outline">
                <i class="fa-solid fa-arrow-left"></i>
                Volver
            </a>
            <button class="modern-primary-btn outline" onclick="window.print()">
                <i class="fa-solid fa-print"></i>
                Imprimir
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
                <span class="card-label">Monto Original</span>
                <span class="card-value">
                    $ {{ number_format($transaccion->original_amount, 0, ',', '.') }}
                </span>
            </div>
        </div>

        <div class="summary-card">
            <div class="card-icon">
                <i class="fa-solid fa-percent"></i>
            </div>
            <div class="card-content">
                <span class="card-label">Descuento</span>
                <span class="card-value">
                    {{ $transaccion->discount_percentage }}%
                </span>
            </div>
        </div>

        <div class="summary-card">
            <div class="card-icon">
                <i class="fa-solid fa-hand-holding-dollar"></i>
            </div>
            <div class="card-content">
                <span class="card-label">Comisión</span>
                <span class="card-value">
                    $ {{ number_format($transaccion->platform_commission, 0, ',', '.') }}
                </span>
            </div>
        </div>

        <div class="summary-card">
            <div class="card-icon">
                <i class="fa-solid fa-money-bill-wave"></i>
            </div>
            <div class="card-content">
                <span class="card-label">Neto para Aliado</span>
                <span class="card-value">
                    $ {{ number_format($transaccion->amount_to_ally, 0, ',', '.') }}
                </span>
            </div>
        </div>
    </div>

    {{-- Información detallada --}}
    <div class="modern-table-container">
        <table class="modern-data-table">
            <tr>
                <th style="width: 200px; background: #f8fafc;">ID Transacción</th>
                <td><strong>#{{ $transaccion->id }}</strong></td>
            </tr>
            <tr>
                <th style="background: #f8fafc;">Código de Referencia</th>
                <td><code>{{ $transaccion->reference_code }}</code></td>
            </tr>
            <tr>
                <th style="background: #f8fafc;">Fecha y Hora</th>
                <td>{{ $transaccion->created_at->format('d/m/Y H:i:s') }}</td>
            </tr>
            <tr>
                <th style="background: #f8fafc;">Estado</th>
                <td>
                    <span class="status-badge-modern badge-{{ $transaccion->status }}">
                        @switch($transaccion->status)
                            @case('confirmed') ✅ Confirmada @break
                            @case('awaiting_review') ⏳ En Revisión @break
                            @case('pending_manual_confirmation') ⌛ Pendiente @break
                            @case('failed') ❌ Fallida @break
                            @default {{ $transaccion->status }}
                        @endswitch
                    </span>
                </td>
            </tr>
            <tr>
                <th style="background: #f8fafc;">Método de Pago</th>
                <td>
                    @if($transaccion->payment_method == 'pago_movil') 
                        <i class="fa-solid fa-mobile"></i> Pago Móvil
                    @elseif($transaccion->payment_method == 'transferencia_bancaria') 
                        <i class="fa-solid fa-building"></i> Transferencia Bancaria
                    @else 
                        {{ $transaccion->payment_method }}
                    @endif
                </td>
            </tr>
        </table>
    </div>

    {{-- Información del Usuario y Aliado --}}
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-top: 1.5rem;">
        {{-- Información del Usuario --}}
        <div class="modern-table-container">
            <h3 style="padding: 1rem; margin: 0; border-bottom: 2px solid #A601B3;">
                <i class="fa-solid fa-user"></i> Información del Usuario
            </h3>
            <table class="modern-data-table">
                <tr>
                    <th style="width: 120px; background: #f8fafc;">Nombre</th>
                    <td>{{ $transaccion->user->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th style="background: #f8fafc;">Email</th>
                    <td>{{ $transaccion->user->email ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th style="background: #f8fafc;">Teléfono</th>
                    <td>{{ $transaccion->user->phone ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>

        {{-- Información del Aliado --}}
        <div class="modern-table-container">
            <h3 style="padding: 1rem; margin: 0; border-bottom: 2px solid #A601B3;">
                <i class="fa-solid fa-handshake"></i> Información del Aliado
            </h3>
            <table class="modern-data-table">
                <tr>
                    <th style="width: 120px; background: #f8fafc;">Nombre</th>
                    <td>{{ $transaccion->ally->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th style="background: #f8fafc;">Email</th>
                    <td>{{ $transaccion->ally->email ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th style="background: #f8fafc;">Teléfono</th>
                    <td>{{ $transaccion->ally->phone ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Datos de Confirmación (si existen) --}}
    @if($transaccion->confirmation_data)
    <div class="modern-table-container" style="margin-top: 1.5rem;">
        <h3 style="padding: 1rem; margin: 0; border-bottom: 2px solid #A601B3;">
            <i class="fa-solid fa-circle-check"></i> Datos de Confirmación
        </h3>
        <div style="padding: 1.5rem; background: #f8fafc;">
            <pre style="margin: 0; font-family: monospace; white-space: pre-wrap;">{{ json_encode($transaccion->confirmation_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
    </div>
    @endif

    {{-- Acciones --}}
    @if(in_array($transaccion->status, ['pending_manual_confirmation', 'awaiting_review']) && auth()->user()->user_type === 'admin')
    <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 2rem;">
        <button class="filter-btn approve-btn" onclick="aprobarTransaccion({{ $transaccion->id }})" style="width: auto;">
            <i class="fa-regular fa-circle-check"></i> Confirmar Transacción
        </button>
        <button class="filter-btn reject-btn" onclick="rechazarTransaccion({{ $transaccion->id }})" style="width: auto; background: linear-gradient(135deg, #ef4444, #dc2626);">
            <i class="fa-regular fa-circle-xmark"></i> Rechazar Transacción
        </button>
    </div>
    @endif
</div>
@endsection