{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.admin')

@section('title', 'Panel')

@section('page_title_toolbar', 'Panel Rumbero')

@section('content')
    @php
        $u = auth()->user();
        $roleLabel = match ($u->role) {
            'admin' => 'Administrador',
            'aliado' => 'Aliado',
            'afiliado' => 'Afiliado',
            default => 'Rumbero',
        };
        $roleMeta = [
            'admin' => ['label' => 'Administradores', 'icon' => 'fa-user-shield'],
            'aliado' => ['label' => 'Aliados', 'icon' => 'fa-store'],
            'afiliado' => ['label' => 'Afiliados', 'icon' => 'fa-user-group'],
            'comun' => ['label' => 'Rumberos', 'icon' => 'fa-user'],
        ];
        $roleCounts = $additionalStats['roleCounts'] ?? [];
        $roleTotal = max(array_sum($roleCounts), 1);
        $statusLabels = ['' => '', 'success' => 'Correcto', 'warning' => 'Pendiente', 'danger' => 'Error', 'secondary' => 'Info'];
        $statusBadges = ['success' => 'badge-success', 'warning' => 'badge-warning', 'danger' => 'badge-danger', 'secondary' => 'badge-neutral'];
    @endphp

    <div class="cm-page db-page">

        {{-- ============================ CABECERA ============================ --}}
        <div class="page-header-block">
            <div>
                <h1 class="title-main">Hola, <span class="title-accent">{{ \Illuminate\Support\Str::before($u->name, ' ') }}</span></h1>
                <p class="page-subtitle">
                    <i class="fa-regular fa-calendar"></i>
                    <span id="dbDate">{{ now()->format('l, d F Y') }}</span>
                    <span aria-hidden="true">·</span>
                    <span id="dbTime">{{ now()->format('h:i A') }}</span>
                </p>
            </div>

            <div class="header-actions">
                <span class="cm-chip"><i class="fa-solid fa-shield-halved"></i> {{ $roleLabel }}</span>
                <button type="button" class="btn btn-secondary" id="dbRefresh">
                    <i class="fa-solid fa-rotate"></i> Actualizar
                </button>
            </div>
        </div>

        {{-- ============================= MÉTRICAS ============================= --}}
        <div class="cm-stats db-kpis">
            <div class="cm-stat">
                <span class="cm-stat__icon"><i class="fa-solid fa-users"></i></span>
                <span class="cm-stat__body">
                    <span class="cm-stat__value">{{ number_format($totalUsers, 0, ',', '.') }}</span>
                    <span class="cm-stat__label">Usuarios</span>
                    <span class="cm-stat__delta {{ $todayUsers > 0 ? 'is-up' : '' }}">
                        <i class="fa-solid {{ $todayUsers > 0 ? 'fa-arrow-trend-up' : 'fa-minus' }}"></i>
                        {{ $todayUsers > 0 ? '+' . $todayUsers . ' hoy' : 'Sin altas hoy' }}
                    </span>
                </span>
            </div>

            <div class="cm-stat">
                <span class="cm-stat__icon cm-stat__icon--green"><i class="fa-solid fa-handshake"></i></span>
                <span class="cm-stat__body">
                    <span class="cm-stat__value">{{ number_format($totalAllies, 0, ',', '.') }}</span>
                    <span class="cm-stat__label">Aliados</span>
                    <span class="cm-stat__delta {{ $todayAllies > 0 ? 'is-up' : '' }}">
                        <i class="fa-solid {{ $todayAllies > 0 ? 'fa-arrow-trend-up' : 'fa-minus' }}"></i>
                        {{ $todayAllies > 0 ? '+' . $todayAllies . ' hoy' : 'Red de aliados' }}
                    </span>
                </span>
            </div>

            <div class="cm-stat">
                <span class="cm-stat__icon cm-stat__icon--gold"><i class="fa-solid fa-eye"></i></span>
                <span class="cm-stat__body">
                    <span class="cm-stat__value">{{ number_format($pageViews, 0, ',', '.') }}</span>
                    <span class="cm-stat__label">Visitas web</span>
                    <span class="cm-stat__delta {{ $todayViews > 0 ? 'is-up' : '' }}">
                        <i class="fa-solid {{ $todayViews > 0 ? 'fa-arrow-trend-up' : 'fa-minus' }}"></i>
                        {{ $todayViews > 0 ? $todayViews . ' hoy' : 'Histórico' }}
                    </span>
                </span>
            </div>

            <div class="cm-stat">
                <span class="cm-stat__icon cm-stat__icon--gray"><i class="fa-solid fa-coins"></i></span>
                <span class="cm-stat__body">
                    <span class="cm-stat__value">${{ number_format($totalSales, 0, ',', '.') }}</span>
                    <span class="cm-stat__label">Ventas</span>
                    <span class="cm-stat__delta {{ $todaySales > 0 ? 'is-up' : '' }}">
                        <i class="fa-solid {{ $todaySales > 0 ? 'fa-arrow-trend-up' : 'fa-minus' }}"></i>
                        {{ $todaySales > 0 ? '$' . number_format($todaySales, 0, ',', '.') . ' hoy' : 'Sin ventas hoy' }}
                    </span>
                </span>
            </div>
        </div>

        {{-- ====================== ACTIVIDAD + RESUMEN ====================== --}}
        <div class="db-grid">

            {{-- Actividad reciente --}}
            <section class="cf-card">
                <div class="cf-card__head">
                    <span class="cf-card__icon"><i class="fa-solid fa-clock-rotate-left"></i></span>
                    <div class="cf-card__head-text">
                        <h2>Actividad reciente</h2>
                        <p>Últimos movimientos registrados en el panel.</p>
                    </div>
                    <span class="cm-chip" style="margin-inline-start:auto;">{{ count($latestActivities) }}
                        {{ count($latestActivities) === 1 ? 'evento' : 'eventos' }}</span>
                </div>

                @if ($latestActivities->isEmpty())
                    <div class="cm-empty" style="border:0;border-radius:0;">
                        <i class="fa-regular fa-face-smile"></i>
                        <h3>Sin actividad todavía</h3>
                        <p>Cuando se creen usuarios, aliados o promociones aparecerán aquí.</p>
                    </div>
                @else
                    <div class="db-timeline">
                        @foreach ($latestActivities as $activity)
                            @php
                                $statusClass = $activity['status_class'] ?? 'secondary';
                            @endphp
                            <div class="db-event">
                                <span class="db-event__icon {{ $statusClass }}">
                                    <i class="fa-solid {{ $activity['icon'] ?? 'fa-circle-info' }}"></i>
                                </span>

                                <div class="db-event__body">
                                    <p class="db-event__title">{{ $activity['activity'] }}</p>
                                    <p class="db-event__meta">
                                        <span><i class="fa-regular fa-user"></i>{{ $activity['user'] }}</span>
                                        <span><i class="fa-regular fa-clock"></i>{{ $activity['date'] }}</span>
                                    </p>
                                </div>

                                <span class="badge {{ $statusBadges[$statusClass] ?? 'badge-neutral' }}">
                                    {{ $statusLabels[$statusClass] ?? 'Info' }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- Columna lateral --}}
            <aside class="db-side">

                {{-- Resumen de hoy --}}
                <section class="cf-card">
                    <div class="cf-card__head">
                        <span class="cf-card__icon"><i class="fa-solid fa-bolt"></i></span>
                        <div class="cf-card__head-text">
                            <h2>Resumen de hoy</h2>
                            <p>Datos en tiempo real del panel.</p>
                        </div>
                    </div>

                    <div class="db-facts">
                        <div class="db-fact">
                            <span class="db-fact__icon"><i class="fa-solid fa-user-check"></i></span>
                            <span>
                                <span class="db-fact__label">Usuarios activos hoy</span>
                                <span class="db-fact__value">{{ number_format($additionalStats['activeUsersToday'] ?? 0) }}</span>
                            </span>
                        </div>

                        <div class="db-fact">
                            <span class="db-fact__icon"><i class="fa-solid fa-store"></i></span>
                            <span>
                                <span class="db-fact__label">Aliados en línea</span>
                                <span class="db-fact__value">{{ number_format($additionalStats['alliesOnline'] ?? 0) }}</span>
                            </span>
                        </div>

                        <div class="db-fact">
                            <span class="db-fact__icon"><i class="fa-solid fa-cart-shopping"></i></span>
                            <span>
                                <span class="db-fact__label">Ventas de hoy</span>
                                <span
                                    class="db-fact__value">${{ number_format($additionalStats['salesToday'] ?? 0, 0, ',', '.') }}</span>
                            </span>
                        </div>

                        @if (($additionalStats['salesGrowth'] ?? 0) != 0)
                            <div class="db-fact">
                                <span class="db-fact__icon"><i class="fa-solid fa-chart-line"></i></span>
                                <span>
                                    <span class="db-fact__label">Crecimiento vs. mes anterior</span>
                                    <span
                                        class="db-fact__value {{ ($additionalStats['salesGrowth'] ?? 0) >= 0 ? 'cm-stat__delta is-up' : 'cm-stat__delta is-down' }}">
                                        {{ ($additionalStats['salesGrowth'] ?? 0) >= 0 ? '+' : '' }}{{ $additionalStats['salesGrowth'] }}%
                                    </span>
                                </span>
                            </div>
                        @endif
                    </div>
                </section>

                {{-- Distribución de usuarios (solo admin) --}}
                @if (($u->role ?? 'comun') === 'admin')
                    <section class="cf-card">
                        <div class="cf-card__head">
                            <span class="cf-card__icon"><i class="fa-solid fa-chart-pie"></i></span>
                            <div class="cf-card__head-text">
                                <h2>Usuarios por rol</h2>
                                <p>Reparto actual de la comunidad.</p>
                            </div>
                        </div>

                        <div class="db-dist">
                            @foreach ($roleMeta as $key => $meta)
                                @php
                                    $count = $roleCounts[$key] ?? 0;
                                    $percent = (int) round(($count / $roleTotal) * 100);
                                @endphp
                                <div class="db-dist__row">
                                    <div class="db-dist__top">
                                        <span class="db-dist__name"><i
                                                class="fa-solid {{ $meta['icon'] }}"></i> {{ $meta['label'] }}</span>
                                        <span class="db-dist__count">{{ $count }} · {{ $percent }}%</span>
                                    </div>
                                    <div class="db-bar">
                                        <span style="width: {{ $percent }}%"></span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- Satisfacción --}}
                <section class="cf-card">
                    <div class="cf-card__head">
                        <span class="cf-card__icon"><i class="fa-solid fa-star"></i></span>
                        <div class="cf-card__head-text">
                            <h2>Valoración de aliados</h2>
                            <p>Promedio de estrellas en la app.</p>
                        </div>
                    </div>

                    <div class="db-sat">
                        <span class="db-sat__value">{{ $customerSatisfaction }}%</span>
                        <span class="db-sat__label">
                            <span>{{ number_format((float) $ratingPromedio, 1, ',', '.') }} de 5 estrellas</span>
                            <span class="db-sat__stars" aria-hidden="true">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i class="fa-{{ $i <= round((float) $ratingPromedio) ? 'solid' : 'regular' }} fa-star"></i>
                                @endfor
                            </span>
                        </span>
                    </div>
                </section>

                {{-- Accesos rápidos (solo admin) --}}
                @if (($u->role ?? 'comun') === 'admin')
                    <section class="cf-card">
                        <div class="cf-card__head">
                            <span class="cf-card__icon"><i class="fa-solid fa-grip"></i></span>
                            <div class="cf-card__head-text">
                                <h2>Accesos rápidos</h2>
                            </div>
                        </div>

                        <div class="db-quick">
                            <a href="{{ route('admin.users.index') }}"><i class="fa-solid fa-users"></i><span>Usuarios</span></a>
                            <a href="{{ route('admin.aliados.index') }}"><i class="fa-solid fa-store"></i><span>Aliados</span></a>
                            <a href="{{ route('admin.banners.index') }}"><i class="fa-regular fa-image"></i><span>Banners</span></a>
                            <a href="{{ route('admin.promotions.index') }}"><i class="fa-solid fa-tags"></i><span>Promociones</span></a>
                            <a href="{{ route('admin.requests.index') }}"><i class="fa-solid fa-envelope-open-text"></i><span>Solicitudes</span></a>
                            <a href="{{ route('admin.settings') }}"><i class="fa-solid fa-gear"></i><span>Configuración</span></a>
                        </div>
                    </section>
                @endif
            </aside>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var dateEl = document.getElementById('dbDate');
            var timeEl = document.getElementById('dbTime');

            function tick() {
                var now = new Date();

                if (dateEl) {
                    var formatted = now.toLocaleDateString('es-ES', {
                        weekday: 'long',
                        day: 'numeric',
                        month: 'long',
                        year: 'numeric'
                    });
                    dateEl.textContent = formatted.charAt(0).toUpperCase() + formatted.slice(1);
                }

                if (timeEl) {
                    timeEl.textContent = now.toLocaleTimeString('es-ES', {
                        hour: '2-digit',
                        minute: '2-digit'
                    }).toUpperCase();
                }
            }

            tick();
            setInterval(tick, 30000);

            var refresh = document.getElementById('dbRefresh');
            if (refresh) {
                refresh.addEventListener('click', function() {
                    refresh.classList.add('is-loading');
                    window.location.reload();
                });
            }
        });
    </script>
@endpush
