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
                    {{-- Reemplazado el icono por una imagen --}}
                    <img src="{{ asset('assets/img/dashboard/logo_usuarios.png') }}" alt="Ícono de Usuarios">
                </div>
                <div class="details">
                    <div class="value">{{ number_format($totalUsers) ?? '0' }}</div>
                    <div class="label">Usuarios Registrados</div>
                </div>
            </div>

            <div class="metric-card-minimal products"> {{-- 'products' en el CSS original, pero representa 'aliados' --}}
                <div class="icon-wrapper">
                    {{-- Reemplazado el icono por una imagen --}}
                    <img src="{{ asset('assets/img/dashboard/logo_aliados.png') }}" alt="Ícono de Aliados">
                </div>
                <div class="details">
                    <div class="value">{{ number_format($totalAllies) ?? '0' }}</div>
                    <div class="label">Aliados Registrados</div>
                </div>
            </div>

            <div class="metric-card-minimal sales"> {{-- 'sales' en el CSS original, pero representa 'visitas' --}}
                <div class="icon-wrapper">
                    {{-- Reemplazado el icono por una imagen --}}
                    <img src="{{ asset('assets/img/dashboard/logo_visitas.png') }}" alt="Ícono de Visitas">
                </div>
                <div class="details">
                    <div class="value">{{ number_format($pageViews) ?? '0' }}</div>
                    <div class="label">Visitas Pagina Web</div>
                </div>
            </div>

            <div class="metric-card-minimal satisfaction"> {{-- 'satisfaction' en el CSS original, pero representa 'ventas' --}}
                <div class="icon-wrapper">
                    {{-- Reemplazado el icono por una imagen --}}
                    <img src="{{ asset('assets/img/dashboard/logo_ventas.png') }}" alt="Ícono de Ventas">
                </div>
                <div class="details">
                    <div class="value">{{ number_format($totalSales, 2) ?? 'N/A' }}</div>
                    <div class="label">Ventas</div>
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
                        @forelse ($latestActivities as $activity)
                            <tr>
                                <td>{{ $activity['activity'] }}</td>
                                <td>{{ $activity['user'] }}</td>
                                <td>{{ $activity['date'] }}</td>
                                <td>
                                    <span class="status-badge {{ $activity['status_class'] }}">
                                        {{ $activity['status'] }}
                                    </span>
                                </td>
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
