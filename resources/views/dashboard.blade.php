{{-- resources/views/dashboard.blade.php (o el nombre de tu archivo) --}}

@extends('layouts.admin')

@section('title', 'Dashboard - Rumbero Extremo')

@section('page_title', 'Vista General del Dashboard')

@section('styles')
    {{-- Importa Font Awesome si no está ya en tu layout principal --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
@endsection

@section('content')
    <div class="main-content">
        <div class="dashboard-header">

        </div>

        {{-- Sección de Bienvenida --}}
        <div class="welcome-section">
            <h2 class="welcome-title">¡Hola, {{ Auth::user()->name }}!</h2>
            <p class="welcome-message">Un placer verte por aquí. Aquí tienes un resumen de la actividad reciente.</p>
        </div>

        {{-- Sección de Tarjetas de Resumen (KPIs) --}}
        <div class="dashboard-cards">
            <div class="dashboard-card primary-card">
                <div class="icon-wrapper"><i class="fas fa-users"></i></div>
                <div class="details">
                    <div class="value">{{ number_format($totalUsers) }}</div>
                    <div class="label">Usuarios Registrados</div>
                </div>
            </div>
            <div class="dashboard-card info-card">
                <div class="icon-wrapper"><i class="fas fa-chart-bar"></i></div>
                <div class="details">
                    <div class="value">{{ number_format($pageViews) }}</div>
                    <div class="label">Visitas a la Página</div>
                </div>
            </div>
            <div class="dashboard-card success-card">
                <div class="icon-wrapper"><i class="fas fa-dollar-sign"></i></div>
                <div class="details">
                    <div class="value">${{ number_format($totalSales, 2) }}</div>
                    <div class="label">Total de Ventas</div>
                </div>
            </div>
        </div>

        {{-- Sección de Últimas Actividades --}}
        <div class="latest-activity-section">
            <h3 class="section-title">Últimas Actividades</h3>
            <div class="activity-table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Actividad</th>
                            @if(Auth::user()->user_type !== 'comun')
                                <th>Usuario</th>
                            @endif
                            <th>Fecha</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(Auth::user()->user_type === 'comun')
                            @forelse ($latestProfileActivities as $activity)
                                <tr>
                                    <td data-label="Actividad">{{ $activity['activity'] }}</td>
                                    <td data-label="Fecha">{{ $activity['date'] }}</td>
                                    <td data-label="Estado"><span class="status-badge {{ $activity['status_class'] }}">{{ $activity['status'] }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">No hay actividades recientes de tu perfil.</td>
                                </tr>
                            @endforelse
                        @else
                            @forelse ($latestActivities as $activity)
                                <tr>
                                    <td data-label="Actividad">{{ $activity['activity'] }}</td>
                                    <td data-label="Usuario">{{ $activity['user'] }}</td>
                                    <td data-label="Fecha">{{ $activity['date'] }}</td>
                                    <td data-label="Estado"><span class="status-badge {{ $activity['status_class'] }}">{{ $activity['status'] }}</span></td>
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

@push('scripts')
    {{-- Enlaza el JavaScript específico de este dashboard --}}
    <script src="{{ asset('js/dashboard.js') }}"></script>
@endpush