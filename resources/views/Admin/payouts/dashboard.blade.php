@extends('layouts.admin')

@section('title', 'Dashboard de Payouts')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">
                    <i class="fa-solid fa-chart-line"></i> Dashboard de Payouts
                </h1>
                <div>
                    <a href="{{ route('admin.payouts.index') }}" class="btn btn-secondary">
                        <i class="fa-solid fa-arrow-left"></i> Ver Todos
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjetas de estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Total Pagado</h6>
                            <h2 class="mb-0">${{ number_format($estadisticas['total_pagado'] ?? 0, 2) }}</h2>
                        </div>
                        <i class="fa-solid fa-dollar-sign fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Pagos Completados</h6>
                            <h2 class="mb-0">{{ number_format($estadisticas['completados'] ?? 0) }}</h2>
                        </div>
                        <i class="fa-solid fa-check-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Pagos Pendientes</h6>
                            <h2 class="mb-0">{{ number_format($estadisticas['pendientes'] ?? 0) }}</h2>
                        </div>
                        <i class="fa-solid fa-clock fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Aliados Activos</h6>
                            <h2 class="mb-0">{{ number_format(count($topAliados ?? [])) }}</h2>
                        </div>
                        <i class="fa-solid fa-users fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de pagos recientes -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fa-solid fa-receipt"></i> Pagos Recientes
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Aliado</th>
                                    <th>Monto</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pagosRecientes ?? [] as $pago)
                                <tr>
                                    <td>#{{ $pago['id'] ?? $pago->id }}</td>
                                    <td>{{ $pago['aliado_nombre'] ?? $pago->ally->company_name ?? 'N/A' }}</td>
                                    <td>${{ number_format($pago['monto'] ?? $pago->net_amount ?? 0, 2) }}</td>
                                    <td>
                                        @php
                                            $status = $pago['estado'] ?? $pago->status ?? 'pending';
                                            $badgeClass = match($status) {
                                                'completed' => 'success',
                                                'pending' => 'warning',
                                                'processing' => 'info',
                                                'reverted' => 'danger',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $badgeClass }}">
                                            {{ ucfirst($status) }}
                                        </span>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($pago['fecha'] ?? $pago->created_at)->format('d/m/Y') }}</td>
                                    <td>
                                        <a href="{{ route('admin.payouts.show', $pago['id'] ?? $pago->id) }}" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        No hay pagos recientes
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Aliados -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fa-solid fa-trophy"></i> Top Aliados por Ventas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @forelse($topAliados ?? [] as $aliado)
                        <div class="col-md-6 mb-3">
                            <div class="d-flex justify-content-between align-items-center p-3 border rounded">
                                <div>
                                    <strong>{{ $aliado['aliado_nombre'] ?? 'N/A' }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ $aliado['total_payouts'] ?? 0 }} pagos
                                    </small>
                                </div>
                                <div class="text-end">
                                    <span class="fw-bold text-success">
                                        ${{ number_format($aliado['total_monto'] ?? 0, 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12">
                            <p class="text-center text-muted py-4">No hay datos disponibles</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    // Actualizar cada 30 segundos
    setTimeout(function() {
        location.reload();
    }, 30000);
</script>
@endsection
@endsection