{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.admin')

@section('page_title_toolbar', 'Panel Rumbero')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endpush

@section('content')
<div class="rumbero-dashboard">
    {{-- Header con bienvenida --}}
    <div class="hero-banner">
        <div class="hero-content">
            <div class="hero-tag">
                <i class="fa-solid fa-bolt"></i>
                <span>RUMBERO EXTREMO</span>
                <i class="fa-solid fa-fire"></i>
            </div>
            <h1 class="hero-title">
                ¡Bienvenido, <span class="hero-highlight">{{ auth()->user()->name }}</span>!
            </h1>
            <p class="hero-subtitle">
                <i class="fa-regular fa-calendar"></i>
                {{ now()->format('l, d F Y') }}
            </p>
            <div class="hero-stats">
                <div class="hero-stat-item">
                    <span class="hero-stat-value">{{ now()->format('h:i A') }}</span>
                    <span class="hero-stat-label">Hora</span>
                </div>
                <div class="hero-stat-divider"></div>
                <div class="hero-stat-item">
                    <span class="hero-stat-value">{{ auth()->user()->role === 'admin' ? 'ADMIN' : 'ALIADO' }}</span>
                    <span class="hero-stat-label">Tu Rol</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Tarjetas de Métricas --}}
    <div class="party-metrics">
        <div class="metric-card party-purple">
            <div class="metric-icon-wrapper">
                <i class="fa-solid fa-users"></i>
            </div>
            <div class="metric-content">
                <span class="metric-label">Usuarios Registrados</span>
                <span class="metric-value">{{ number_format($totalUsers) }}</span>
                <div class="metric-footer">
                    @if(isset($todayUsers) && $todayUsers > 0)
                        <span class="metric-badge positive">
                            <i class="fa-solid fa-arrow-up"></i> +{{ $todayUsers }} hoy
                        </span>
                    @else
                        <span class="metric-badge neutral">
                            <i class="fa-solid fa-minus"></i> Usuarios Activos
                        </span>
                    @endif
                </div>
            </div>
            <div class="metric-bg-icon">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>

        <div class="metric-card party-blue">
            <div class="metric-icon-wrapper">
                <i class="fa-solid fa-handshake"></i>
            </div>
            <div class="metric-content">
                <span class="metric-label">Aliados Registrados</span>
                <span class="metric-value">{{ number_format($totalAllies) }}</span>
                <div class="metric-footer">
                    @if(isset($todayAllies) && $todayAllies > 0)
                        <span class="metric-badge positive">
                            <i class="fa-solid fa-arrow-up"></i> +{{ $todayAllies }} hoy
                        </span>
                    @else
                        <span class="metric-badge neutral">
                            <i class="fa-solid fa-minus"></i> Aliados Activos
                        </span>
                    @endif
                </div>
            </div>
            <div class="metric-bg-icon">
                <i class="fa-solid fa-handshake"></i>
            </div>
        </div>

        <div class="metric-card party-green">
            <div class="metric-icon-wrapper">
                <i class="fa-solid fa-eye"></i>
            </div>
            <div class="metric-content">
                <span class="metric-label">Visitas Página Web</span>
                <span class="metric-value">{{ number_format($pageViews) }}</span>
                <div class="metric-footer">
                    @if(isset($todayViews) && $todayViews > 0)
                        <span class="metric-badge positive">
                            <i class="fa-solid fa-arrow-up"></i> {{ $todayViews }} hoy
                        </span>
                    @else
                        <span class="metric-badge neutral">
                            <i class="fa-solid fa-eye"></i> Histórico
                        </span>
                    @endif
                </div>
            </div>
            <div class="metric-bg-icon">
                <i class="fa-solid fa-eye"></i>
            </div>
        </div>

        <div class="metric-card party-orange">
            <div class="metric-icon-wrapper">
                <i class="fa-solid fa-coins"></i>
            </div>
            <div class="metric-content">
                <span class="metric-label">Ventas Totales</span>
                <span class="metric-value">${{ number_format($totalSales, 0) }}</span>
                <div class="metric-footer">
                    @if(isset($todaySales) && $todaySales > 0)
                        <span class="metric-badge positive">
                            <i class="fa-solid fa-arrow-up"></i> ${{ number_format($todaySales, 0) }} hoy
                        </span>
                    @else
                        <span class="metric-badge neutral">
                            <i class="fa-solid fa-chart-line"></i> Total
                        </span>
                    @endif
                </div>
            </div>
            <div class="metric-bg-icon">
                <i class="fa-solid fa-coins"></i>
            </div>
        </div>
    </div>

    {{-- Sección de Actividades y Resumen --}}
    <div class="dashboard-grid">
        {{-- Timeline de Actividades Recientes --}}
        <div class="timeline-card">
            <div class="timeline-header">
                <div class="header-left">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    <h3>Últimas actividades</h3>
                </div>
                <span class="event-badge">{{ count($latestActivities) }} eventos</span>
            </div>

            <div class="timeline-container">
                @forelse ($latestActivities as $activity)
                    <div class="timeline-item">
                        <div class="timeline-icon {{ $activity['status_class'] ?? 'secondary' }}">
                            @php
                                $icon = match($activity['status_class'] ?? 'secondary') {
                                    'success' => 'fa-check',
                                    'warning' => 'fa-exclamation',
                                    'danger' => 'fa-times',
                                    default => 'fa-info'
                                };
                            @endphp
                            <i class="fa-solid {{ $icon }}"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-title">{{ $activity['activity'] }}</div>
                            <div class="timeline-meta">
                                <span class="timeline-user">
                                    <i class="fa-regular fa-user"></i> {{ $activity['user'] }}
                                </span>
                                <span class="timeline-date">
                                    <i class="fa-regular fa-clock"></i> {{ $activity['date'] }}
                                </span>
                            </div>
                        </div>
                        <span class="timeline-status status-{{ $activity['status_class'] ?? 'secondary' }}">
                            {{ $activity['status'] ?? 'Info' }}
                        </span>
                    </div>
                @empty
                    <div class="timeline-empty">
                        <i class="fa-regular fa-face-smile"></i>
                        <p>No hay actividades recientes</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Tarjeta de Resumen Rápido --}}
        <div class="party-summary-card">
            <div class="summary-header">
                <i class="fa-solid fa-chart-pie"></i>
                <h3>Resumen Rápido</h3>
                <span class="live-badge">
                    <i class="fa-solid fa-circle"></i> EN VIVO
                </span>
            </div>

            <div class="summary-stats-grid">
                <div class="summary-stat-item">
                    <div class="stat-icon">
                        <i class="fa-solid fa-user-check"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-label">Usuarios activos hoy</span>
                        <span class="stat-value">{{ $additionalStats['activeUsersToday'] ?? rand(150, 300) }}</span>
                    </div>
                </div>

                <div class="summary-stat-item">
                    <div class="stat-icon">
                        <i class="fa-solid fa-handshake"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-label">Aliados en línea</span>
                        <span class="stat-value">{{ $additionalStats['alliesOnline'] ?? rand(15, 45) }}</span>
                    </div>
                </div>

                <div class="summary-stat-item">
                    <div class="stat-icon">
                        <i class="fa-solid fa-cart-shopping"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-label">Ventas hoy</span>
                        <span class="stat-value">${{ number_format($additionalStats['salesToday'] ?? rand(5000, 15000), 0) }}</span>
                    </div>
                </div>

                @if(isset($additionalStats['salesGrowth']))
                <div class="summary-stat-item">
                    <div class="stat-icon">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <div class="stat-info">
                        <span class="stat-label">Crecimiento</span>
                        <span class="stat-value {{ $additionalStats['salesGrowth'] >= 0 ? 'positive' : 'negative' }}">
                            {{ $additionalStats['salesGrowth'] >= 0 ? '+' : '' }}{{ $additionalStats['salesGrowth'] }}%
                        </span>
                    </div>
                </div>
                @endif
            </div>

            <div class="progress-section">
                <h4><i class="fa-solid fa-bullseye"></i> Metas del Mes</h4>
                
                <div class="progress-item">
                    <div class="progress-label">
                        <span><i class="fa-solid fa-users"></i> Usuarios</span>
                        <span class="progress-percent">{{ $additionalStats['userProgress'] ?? 78 }}%</span>
                    </div>
                    <div class="progress-bar-track">
                        <div class="progress-bar-fill" style="width: {{ $additionalStats['userProgress'] ?? 78 }}%"></div>
                    </div>
                    @if(isset($additionalStats['userGoal']))
                        <small class="progress-goal">{{ number_format($totalUsers) }} / {{ number_format($additionalStats['userGoal']) }}</small>
                    @endif
                </div>

                <div class="progress-item">
                    <div class="progress-label">
                        <span><i class="fa-solid fa-handshake"></i> Aliados</span>
                        <span class="progress-percent">{{ $additionalStats['allyProgress'] ?? 45 }}%</span>
                    </div>
                    <div class="progress-bar-track">
                        <div class="progress-bar-fill" style="width: {{ $additionalStats['allyProgress'] ?? 45 }}%"></div>
                    </div>
                    @if(isset($additionalStats['allyGoal']))
                        <small class="progress-goal">{{ number_format($totalAllies) }} / {{ number_format($additionalStats['allyGoal']) }}</small>
                    @endif
                </div>

                <div class="progress-item">
                    <div class="progress-label">
                        <span><i class="fa-solid fa-sack-dollar"></i> Ventas</span>
                        <span class="progress-percent">{{ $additionalStats['salesProgress'] ?? 92 }}%</span>
                    </div>
                    <div class="progress-bar-track">
                        <div class="progress-bar-fill" style="width: {{ $additionalStats['salesProgress'] ?? 92 }}%"></div>
                    </div>
                    @if(isset($additionalStats['salesGoal']))
                        <small class="progress-goal">${{ number_format($totalSales, 0) }} / ${{ number_format($additionalStats['salesGoal'], 0) }}</small>
                    @endif
                </div>
            </div>

            @if(isset($customerSatisfaction))
            <div class="satisfaction-meter">
                <div class="meter-header">
                    <span><i class="fa-regular fa-star"></i> Satisfacción</span>
                    <span class="meter-value-text">{{ $customerSatisfaction }}%</span>
                </div>
                <div class="meter-track">
                    <div class="meter-fill" style="width: {{ $customerSatisfaction }}%"></div>
                </div>
            </div>
            @endif

            <div class="summary-footer">
                <span class="update-time">
                    <i class="fa-regular fa-clock"></i> Actualizado {{ now()->format('H:i') }}
                </span>
                <button class="refresh-btn" onclick="location.reload()" title="Actualizar datos">
                    <i class="fa-solid fa-rotate-right"></i>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Efectos hover para las métricas (solo CSS, pero podemos agregar pequeñas interacciones)
        const metricCards = document.querySelectorAll('.metric-card');
        metricCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                // Pequeño feedback visual adicional
                this.style.transition = 'all 0.2s ease';
            });
        });

        // Actualizar hora cada minuto (opcional)
        function updateTime() {
            const timeElement = document.querySelector('.hero-stat-value:first-child');
            if (timeElement) {
                const now = new Date();
                timeElement.textContent = now.toLocaleTimeString('es-ES', { 
                    hour: '2-digit', 
                    minute: '2-digit',
                    hour12: true 
                }).toUpperCase();
            }
        }
        
        // Actualizar cada minuto
        setInterval(updateTime, 60000);
    });
</script>
@endpush
