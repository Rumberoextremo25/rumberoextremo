@extends('layouts.admin')

@section('title', 'Dashboard - Rumbero Extremo')

@section('page_title', 'Vista General del Dashboard')

@section('content')
    <div class="dashboard-container">
        {{-- Sección de Bienvenida (visible para todos los roles) --}}
        <div class="welcome-section">
            <h2 class="welcome-title">¡Hola, {{ Auth::user()->name }}!</h2>
            <p class="welcome-message">Bienvenido al panel de Rumbero Extremo.</p>
        </div>

        {{-- Sección de Tarjetas de Resumen (KPIs) - Visibilidad por Rol --}}
        @if(Auth::user()->role === 'admin')
            {{-- Admin ve todas las cards --}}
            <div class="dashboard-cards">
                <div class="dashboard-card primary-card">
                    <div class="icon"><i class="fas fa-users"></i></div>
                    <div class="details">
                        <div class="value">{{ number_format($totalUsers) }}</div>
                        <div class="label">Total Usuarios</div>
                    </div>
                </div>
                <div class="dashboard-card success-card">
                    <div class="icon"><i class="fas fa-box"></i></div>
                    <div class="details">
                        <div class="value">{{ number_format($totalActiveProducts) }}</div>
                        <div class="label">Productos Activos</div>
                    </div>
                </div>
                <div class="dashboard-card info-card">
                    <div class="icon"><i class="fas fa-dollar-sign"></i></div>
                    <div class="details">
                        <div class="value">${{ number_format($todaySales, 2) }}</div>
                        <div class="label">Ventas Hoy</div>
                    </div>
                </div>
                <div class="dashboard-card warning-card">
                    <div class="icon"><i class="fas fa-chart-line"></i></div>
                    <div class="details">
                        <div class="value">{{ $customerSatisfaction }}%</div>
                        <div class="label">Satisfacción Cliente</div>
                    </div>
                </div>
            </div>
        @elseif(Auth::user()->role === 'aliado')
            {{-- Aliado ve solo 'Ventas Hoy' y 'Satisfacción Cliente' --}}
            <div class="dashboard-cards">
                <div class="dashboard-card info-card">
                    <div class="icon"><i class="fas fa-dollar-sign"></i></div>
                    <div class="details">
                        <div class="value">${{ number_format($todaySales, 2) }}</div>
                        <div class="label">Ventas Hoy</div>
                    </div>
                </div>
                <div class="dashboard-card warning-card">
                    <div class="icon"><i class="fas fa-chart-line"></i></div>
                    <div class="details">
                        <div class="value">{{ $customerSatisfaction }}%</div>
                        <div class="label">Satisfacción Cliente</div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Sección de Últimas Actividades - Visibilidad y Contenido por Rol --}}
        <div class="latest-activity-section">
            <h3>Últimas Actividades</h3>
            <div class="activity-table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Actividad</th>
                            <th>Usuario</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(Auth::user()->role === 'comun')
                            {{-- Solo mostrar actividades relacionadas al perfil para usuarios 'comun' --}}
                            @forelse ($latestProfileActivities as $activity) {{-- ¡Asegúrate de pasar esta variable desde el controlador! --}}
                                <tr>
                                    <td>{{ $activity['activity'] }}</td>
                                    <td>{{ $activity['user'] }}</td>
                                    <td>{{ $activity['date'] }}</td>
                                    <td><span class="status-badge {{ $activity['status_class'] }}">{{ $activity['status'] }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No hay actividades recientes de tu perfil.</td>
                                </tr>
                            @endforelse
                        @else
                            {{-- Admin y Aliado ven todas las actividades (tu lógica original) --}}
                            @forelse ($latestActivities as $activity)
                                <tr>
                                    <td>{{ $activity['activity'] }}</td>
                                    <td>{{ $activity['user'] }}</td>
                                    <td>{{ $activity['date'] }}</td>
                                    <td><span class="status-badge {{ $activity['status_class'] }}">{{ $activity['status'] }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No hay actividades recientes.</td>
                                </tr>
                            @endforelse
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection