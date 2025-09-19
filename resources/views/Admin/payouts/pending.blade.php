@extends('layouts.admin')

@section('page_title_toolbar', 'Pagos Pendientes a Aliados')

@section('content')
<div class="main-content">
    <h2 class="page-title text-gray-900">
        <span class="text-gray-900">Pagos</span>
        <span style="color: #8a2be2;">Pendientes</span>
    </h2>

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">Pagos Pendientes de Procesamiento</h3>
                <a href="{{ route('admin.payouts.index') }}" class="btn btn-secondary">
                    <i class="fas fa-list"></i> Ver Todos los Pagos
                </a>
            </div>
        </div>
        <div class="card-body">
            @if($payouts->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h4>No hay pagos pendientes</h4>
                    <p class="text-muted">Todos los pagos han sido procesados correctamente.</p>
                </div>
            @else
                {{-- Filtros --}}
                <div class="row mb-4">
                    <div class="col-md-12">
                        <form action="{{ route('admin.payouts.pending') }}" method="GET" class="form-inline">
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="fecha_inicio">Fecha Inicio:</label>
                                    <input type="date" name="fecha_inicio" class="form-control" 
                                           value="{{ request('fecha_inicio') }}">
                                </div>
                                <div class="col-md-3">
                                    <label for="fecha_fin">Fecha Fin:</label>
                                    <input type="date" name="fecha_fin" class="form-control" 
                                           value="{{ request('fecha_fin') }}">
                                </div>
                                <div class="col-md-3">
                                    <label for="ally_id">Aliado:</label>
                                    <select name="ally_id" class="form-control">
                                        <option value="">Todos los Aliados</option>
                                        @foreach($allies as $ally)
                                            <option value="{{ $ally->id }}" 
                                                {{ request('ally_id') == $ally->id ? 'selected' : '' }}>
                                                {{ $ally->company_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label>&nbsp;</label>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-filter"></i> Filtrar
                                        </button>
                                        <a href="{{ route('admin.payouts.pending') }}" class="btn btn-secondary">
                                            <i class="fas fa-sync"></i> Limpiar
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Estadísticas --}}
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="stat-card bg-primary text-white">
                            <div class="stat-icon">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="stat-content">
                                <h3>{{ $payouts->count() }}</h3>
                                <p>Pagos Pendientes</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card bg-success text-white">
                            <div class="stat-icon">
                                <i class="fas fa-coins"></i>
                            </div>
                            <div class="stat-content">
                                <h3>{{ number_format($payouts->sum('commission_amount'), 2, ',', '.') }} Bs.</h3>
                                <p>Total en Comisiones</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stat-card bg-info text-white">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-content">
                                <h3>{{ $payouts->unique('ally_id')->count() }}</h3>
                                <p>Aliados Involucrados</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Tabla de pagos pendientes --}}
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Aliado</th>
                                <th>Monto Comisión</th>
                                <th>Cuenta Destino</th>
                                <th>Fecha Generación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payouts as $payout)
                                <tr>
                                    <td>{{ $payout->id }}</td>
                                    <td>
                                        <strong>{{ $payout->ally->company_name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $payout->ally->company_rif }}</small>
                                    </td>
                                    <td>
                                        <span class="text-success">
                                            {{ number_format($payout->commission_amount, 2, ',', '.') }} Bs.
                                        </span>
                                        <br>
                                        <small class="text-muted">{{ $payout->commission_percentage }}%</small>
                                    </td>
                                    <td>
                                        {{ $payout->ally_account_number }}
                                        <br>
                                        <small class="text-muted">{{ $payout->ally_bank }}</small>
                                    </td>
                                    <td>{{ $payout->generation_date->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('admin.payouts.show', $payout->id) }}" 
                                               class="btn btn-sm btn-info" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.payouts.index', ['status' => 'pending']) }}" 
                                               class="btn btn-sm btn-primary" title="Procesar">
                                                <i class="fas fa-cog"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}
                @if($payouts->hasPages())
                <div class="pagination-container mt-4">
                    {{ $payouts->appends(request()->query())->links() }}
                </div>
                @endif
            @endif
        </div>
    </div>
</div>

<style>
.stat-card {
    border-radius: 0.5rem;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
}
.stat-icon {
    font-size: 2rem;
    opacity: 0.8;
}
.stat-content h3 {
    margin: 0;
    font-size: 1.5rem;
    font-weight: bold;
}
.stat-content p {
    margin: 0;
    opacity: 0.9;
}
.table th {
    background-color: #8a2be2;
    color: white;
    font-weight: 600;
}
.btn-group .btn {
    border-radius: 0.25rem;
    margin-right: 0.25rem;
}
</style>
@endsection