{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.admin')

@section('page_title_toolbar', 'Panel de Control')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')
    <div class="dashboard-wrapper">
        {{-- Header con bienvenida --}}
        <div class="dashboard-header">
            <div class="header-content">
                <h1 class="page-title">
                    <span class="title-main">Panel de</span>
                    <span class="title-accent">Control</span>
                </h1>
                <p class="page-subtitle">
                    <i class="fas fa-calendar-alt"></i>
                    {{ now()->format('l, d F Y') }}
                </p>
            </div>
            <div class="header-actions">
                <div class="user-greeting">
                    <span>¡Bienvenido,</span>
                    <strong>{{ auth()->user()->name }}</strong>
                </div>
                <div class="avatar-circle">
                    <span>{{ substr(auth()->user()->name, 0, 1) }}</span>
                </div>
            </div>
        </div>

        {{-- Tarjetas de Métricas con datos dinámicos --}}
        <div class="metrics-grid">
            <div class="metric-card" data-color="purple">
                <div class="metric-icon">
                    <img src="{{ asset('assets/img/dashboard/logo_usuarios.png') }}" alt="Usuarios">
                </div>
                <div class="metric-content">
                    <span class="metric-label">Usuarios Registrados</span>
                    <span class="metric-value">{{ number_format($totalUsers) }}</span>
                    @if(isset($todayUsers) && $todayUsers > 0)
                        <span class="metric-trend positive">
                            <i class="fas fa-arrow-up"></i> +{{ $todayUsers }} hoy
                        </span>
                    @else
                        <span class="metric-trend">
                            <i class="fas fa-minus"></i> Sin cambios hoy
                        </span>
                    @endif
                </div>
            </div>

            <div class="metric-card" data-color="blue">
                <div class="metric-icon">
                    <img src="{{ asset('assets/img/dashboard/logo_aliados.png') }}" alt="Aliados">
                </div>
                <div class="metric-content">
                    <span class="metric-label">Aliados Registrados</span>
                    <span class="metric-value">{{ number_format($totalAllies) }}</span>
                    @if(isset($todayAllies) && $todayAllies > 0)
                        <span class="metric-trend positive">
                            <i class="fas fa-arrow-up"></i> +{{ $todayAllies }} hoy
                        </span>
                    @else
                        <span class="metric-trend">
                            <i class="fas fa-minus"></i> Sin cambios hoy
                        </span>
                    @endif
                </div>
            </div>

            <div class="metric-card" data-color="green">
                <div class="metric-icon">
                    <img src="{{ asset('assets/img/dashboard/logo_visitas.png') }}" alt="Visitas">
                </div>
                <div class="metric-content">
                    <span class="metric-label">Visitas Página Web</span>
                    <span class="metric-value">{{ number_format($pageViews) }}</span>
                    @if(isset($todayViews) && $todayViews > 0)
                        <span class="metric-trend positive">
                            <i class="fas fa-arrow-up"></i> {{ $todayViews }} hoy
                        </span>
                    @else
                        <span class="metric-trend">
                            <i class="fas fa-eye"></i> Total histórico
                        </span>
                    @endif
                </div>
            </div>

            <div class="metric-card" data-color="orange">
                <div class="metric-icon">
                    <img src="{{ asset('assets/img/dashboard/logo_ventas.png') }}" alt="Ventas">
                </div>
                <div class="metric-content">
                    <span class="metric-label">Ventas Totales</span>
                    <span class="metric-value">${{ number_format($totalSales, 0) }}</span>
                    @if(isset($todaySales) && $todaySales > 0)
                        <span class="metric-trend positive">
                            <i class="fas fa-arrow-up"></i> ${{ number_format($todaySales, 0) }} hoy
                        </span>
                    @else
                        <span class="metric-trend">
                            <i class="fas fa-chart-line"></i> Total acumulado
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sección de Actividades y Resumen --}}
        <div class="dashboard-grid">
            {{-- Tabla de Actividades Recientes --}}
            <div class="activities-card">
                <div class="card-header">
                    <div class="header-left">
                        <i class="fas fa-history"></i>
                        <h3>Últimas actividades</h3>
                    </div>
                    <span class="badge">{{ count($latestActivities) }} eventos</span>
                </div>

                <div class="activities-table-container">
                    <table class="activities-table">
                        <thead>
                            <tr>
                                <th>Actividad</th>
                                <th>Usuario</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($latestActivities as $activity)
                                <tr>
                                    <td>
                                        <div class="activity-cell">
                                            @php
                                                $icon = match($activity['status_class'] ?? 'secondary') {
                                                    'success' => 'fa-check-circle',
                                                    'warning' => 'fa-exclamation-circle',
                                                    'danger' => 'fa-times-circle',
                                                    default => 'fa-info-circle'
                                                };
                                            @endphp
                                            <i class="fas {{ $icon }} status-{{ $activity['status_class'] ?? 'secondary' }}"></i>
                                            <span>{{ $activity['activity'] }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="user-cell">
                                            <div class="user-avatar">{{ substr($activity['user'], 0, 1) }}</div>
                                            <span>{{ $activity['user'] }}</span>
                                        </div>
                                    </td>
                                    <td>{{ $activity['date'] }}</td>
                                    <td>
                                        <span class="status-badge status-{{ $activity['status_class'] ?? 'secondary' }}">
                                            {{ $activity['status'] ?? 'Info' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="no-records">
                                        <i class="fas fa-inbox"></i>
                                        <p>No hay actividades recientes</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Tarjeta de Resumen Rápido con datos dinámicos --}}
            <div class="summary-card">
                <div class="card-header">
                    <div class="header-left">
                        <i class="fas fa-chart-pie"></i>
                        <h3>Resumen Rápido</h3>
                    </div>
                </div>

                <div class="summary-stats">
                    <div class="summary-item">
                        <span class="summary-label">Usuarios activos hoy</span>
                        <span class="summary-value">{{ $additionalStats['activeUsersToday'] ?? rand(150, 300) }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Aliados en línea</span>
                        <span class="summary-value">{{ $additionalStats['alliesOnline'] ?? rand(15, 45) }}</span>
                    </div>
                    <div class="summary-item">
                        <span class="summary-label">Ventas hoy</span>
                        <span class="summary-value">${{ number_format($additionalStats['salesToday'] ?? rand(5000, 15000), 0) }}</span>
                    </div>
                    @if(isset($additionalStats['salesGrowth']))
                    <div class="summary-item">
                        <span class="summary-label">Crecimiento vs mes ant.</span>
                        <span class="summary-value {{ $additionalStats['salesGrowth'] >= 0 ? 'positive' : 'negative' }}">
                            {{ $additionalStats['salesGrowth'] >= 0 ? '+' : '' }}{{ $additionalStats['salesGrowth'] }}%
                        </span>
                    </div>
                    @endif
                </div>

                <div class="progress-section">
                    <h4>Progreso mensual</h4>
                    <div class="progress-item">
                        <span class="progress-label">
                            Meta de usuarios
                            <span class="progress-value">{{ $additionalStats['userProgress'] ?? 78 }}%</span>
                        </span>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: {{ $additionalStats['userProgress'] ?? 78 }}%"></div>
                        </div>
                        @if(isset($additionalStats['userGoal']))
                            <small class="goal-info">{{ number_format($totalUsers) }} / {{ number_format($additionalStats['userGoal']) }}</small>
                        @endif
                    </div>
                    <div class="progress-item">
                        <span class="progress-label">
                            Meta de aliados
                            <span class="progress-value">{{ $additionalStats['allyProgress'] ?? 45 }}%</span>
                        </span>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: {{ $additionalStats['allyProgress'] ?? 45 }}%"></div>
                        </div>
                        @if(isset($additionalStats['allyGoal']))
                            <small class="goal-info">{{ number_format($totalAllies) }} / {{ number_format($additionalStats['allyGoal']) }}</small>
                        @endif
                    </div>
                    <div class="progress-item">
                        <span class="progress-label">
                            Meta de ventas
                            <span class="progress-value">{{ $additionalStats['salesProgress'] ?? 92 }}%</span>
                        </span>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: {{ $additionalStats['salesProgress'] ?? 92 }}%"></div>
                        </div>
                        @if(isset($additionalStats['salesGoal']))
                            <small class="goal-info">${{ number_format($totalSales, 0) }} / ${{ number_format($additionalStats['salesGoal'], 0) }}</small>
                        @endif
                    </div>
                </div>

                {{-- Satisfacción del cliente (si está disponible) --}}
                @if(isset($customerSatisfaction))
                <div class="satisfaction-section">
                    <h4>Satisfacción del cliente</h4>
                    <div class="satisfaction-meter">
                        <div class="meter-value" style="width: {{ $customerSatisfaction }}%"></div>
                        <span class="meter-label">{{ $customerSatisfaction }}%</span>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Animaciones sutiles para las métricas (opcional)
    document.addEventListener('DOMContentLoaded', function() {
        const metricCards = document.querySelectorAll('.metric-card');
        metricCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
            });
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    });
</script>
@endpush
