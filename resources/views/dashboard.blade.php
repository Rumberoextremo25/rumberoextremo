{{-- resources/views/dashboard.blade.php --}}

@extends('layouts.admin')

@section('page_title_toolbar', 'Panel de Control')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
@endpush

@section('content')
    <div class="main-content">

        {{-- Sección de Tarjetas de Métricas (Rediseñada) --}}
        <div class="metrics-cards-grid">
            <div class="metric-card-minimal users">
                <div class="icon-wrapper">
                    <i class="fas fa-users"></i>
                </div>
                <div class="details">
                    <div class="value">{{ number_format($totalUsers) ?? '0' }}</div>
                    <div class="label">Total usuarios</div>
                </div>
            </div>

            <div class="metric-card-minimal products">
                <div class="icon-wrapper">
                    <i class="fas fa-box"></i>
                </div>
                <div class="details">
                    <div class="value">25</div> {{-- Asume una variable para productos activos --}}
                    <div class="label">Productos activos</div>
                </div>
            </div>

            <div class="metric-card-minimal sales">
                <div class="icon-wrapper">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="details">
                    <div class="value">N/A</div> {{-- O usa {{ number_format($todaySales, 2) ?? 'N/A' }} si tienes el dato --}}
                    <div class="label">Ventas hoy</div>
                </div>
            </div>

            <div class="metric-card-minimal satisfaction">
                <div class="icon-wrapper">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="details">
                    <div class="value">N/A</div> {{-- O usa {{ $customerSatisfaction ?? 'N/A' }} --}}
                    <div class="label">Satisfacción del cliente</div>
                </div>
            </div>
        </div>

        {{-- Sección de Últimas Actividades (Rediseñada) --}}
        <div class="activities-section">
            <h3 class="section-title">Últimas actividades</h3>
            <div class="activity-table-container">
                <table class="activity-table">
                    <thead>
                        <tr>
                            <th>Actividad</th>
                            <th>Usuario</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Ejemplo de datos (reemplaza con tus variables $latestActivities) --}}
                        @php
                            $latestActivities = [
                                ['activity' => 'Nuevo producto añadido: Devant la Riviere', 'user' => 'Herbert Diaz', 'date' => '16/06/2025 19:38', 'status' => 'Completado', 'status_class' => 'status-completado'],
                                ['activity' => 'Últimas actividades', 'user' => 'María Gómez', 'date' => '16/06/2025 18:39', 'status' => 'Pendiente', 'status_class' => 'status-pendiente'],
                                ['activity' => 'Últimas actividades', 'user' => 'Carlos Ruíz', 'date' => '16/06/2025 16:38', 'status' => 'Completado', 'status_class' => 'status-completado'],
                                ['activity' => 'Últimas actividades', 'user' => 'Admin. R.E.', 'date' => '16/06/2025 14:38', 'status' => 'Error', 'status_class' => 'status-error'],
                                ['activity' => 'Últimas actividades', 'user' => 'Sistema', 'date' => '16/06/2025 19:38', 'status' => 'Completado', 'status_class' => 'status-completado'],
                            ];
                        @endphp

                        @forelse ($latestActivities as $activity)
                            <tr>
                                <td>{{ $activity['activity'] }}</td>
                                <td>{{ $activity['user'] }}</td>
                                <td>{{ $activity['date'] }}</td>
                                <td><span class="status-badge {{ $activity['status_class'] }}">{{ $activity['status'] }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="no-records-message">No hay actividades recientes.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- Tus scripts, si los necesitas --}}
@endpush