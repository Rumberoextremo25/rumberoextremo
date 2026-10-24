@extends('layouts.admin')

@section('title', 'Pago a proveedores')

@section('page_title_toolbar', 'Pago a proveedores')

@section('content')
    @php
        $statusMeta = [
            'pending' => ['label' => 'Pendiente', 'badge' => 'badge-info', 'icon' => 'fa-clock'],
            'processing' => ['label' => 'Procesando', 'badge' => 'badge-warning', 'icon' => 'fa-rotate'],
            'completed' => ['label' => 'Completado', 'badge' => 'badge-success', 'icon' => 'fa-circle-check'],
            'reverted' => ['label' => 'Revertido', 'badge' => 'badge-danger', 'icon' => 'fa-rotate-left'],
        ];
        $stateCounts = $estadisticas['payouts_por_estado'] ?? [];
        $hasFilters = request()->filled('status') && request('status') !== 'all'
            || request()->filled('ally_id')
            || request()->filled('fecha_inicio')
            || request()->filled('fecha_fin')
            || request()->filled('search');
    @endphp

    <div class="cm-page">

        {{-- ============================ CABECERA ============================ --}}
        <div class="page-header-block">
            <div>
                <h1 class="title-main">Pago a <span class="title-accent">proveedores</span></h1>
                <p class="page-subtitle">
                    Comisiones, montos netos y estado de cada pago generado a los aliados.
                </p>
            </div>

            <div class="header-actions">
                <a href="{{ route('admin.payouts.pendientes') }}" class="btn btn-secondary">
                    <i class="fa-solid fa-clock"></i> Pendientes
                </a>
                <a href="{{ route('admin.payouts.estadisticas') }}" class="btn btn-secondary">
                    <i class="fa-solid fa-chart-bar"></i> Estadísticas
                </a>
                <a href="{{ route('admin.payouts.lotes') }}" class="btn btn-primary">
                    <i class="fa-solid fa-layer-group"></i> Lotes
                </a>
            </div>
        </div>

        {{-- ============================== AVISOS ============================== --}}
        @if (session('success'))
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i>
                <div class="alert-content">{{ session('success') }}</div>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <div class="alert-content">{{ session('error') }}</div>
            </div>
        @endif

        {{-- ============================= MÉTRICAS ============================= --}}
        <div class="cm-stats">
            <div class="cm-stat">
                <span class="cm-stat__icon cm-stat__icon--gold"><i class="fa-solid fa-clock"></i></span>
                <span class="cm-stat__body">
                    <span class="cm-stat__value">Bs. {{ number_format($estadisticas['total_pendiente'] ?? 0, 0, ',', '.') }}</span>
                    <span class="cm-stat__label">Por pagar</span>
                    <span class="cm-stat__delta"><i class="fa-solid fa-receipt"></i> {{ $stateCounts['pending'] ?? 0 }} {{ ($stateCounts['pending'] ?? 0) === 1 ? 'pago' : 'pagos' }}</span>
                </span>
            </div>

            <div class="cm-stat">
                <span class="cm-stat__icon"><i class="fa-solid fa-rotate"></i></span>
                <span class="cm-stat__body">
                    <span class="cm-stat__value">Bs. {{ number_format($estadisticas['total_procesando'] ?? 0, 0, ',', '.') }}</span>
                    <span class="cm-stat__label">Procesando</span>
                    <span class="cm-stat__delta"><i class="fa-solid fa-arrow-right-arrow-left"></i> {{ $stateCounts['processing'] ?? 0 }} en curso</span>
                </span>
            </div>

            <div class="cm-stat">
                <span class="cm-stat__icon cm-stat__icon--green"><i class="fa-solid fa-circle-check"></i></span>
                <span class="cm-stat__body">
                    <span class="cm-stat__value">Bs. {{ number_format($estadisticas['total_pagado'] ?? 0, 0, ',', '.') }}</span>
                    <span class="cm-stat__label">Pagado</span>
                    <span class="cm-stat__delta"><i class="fa-solid fa-check"></i> {{ $stateCounts['completed'] ?? 0 }} {{ ($stateCounts['completed'] ?? 0) === 1 ? 'completado' : 'completados' }}</span>
                </span>
            </div>

            <div class="cm-stat">
                <span class="cm-stat__icon cm-stat__icon--red"><i class="fa-solid fa-rotate-left"></i></span>
                <span class="cm-stat__body">
                    <span class="cm-stat__value">Bs. {{ number_format($estadisticas['total_revertido'] ?? 0, 0, ',', '.') }}</span>
                    <span class="cm-stat__label">Revertido</span>
                    <span class="cm-stat__delta"><i class="fa-solid fa-rotate-left"></i> {{ $stateCounts['reverted'] ?? 0 }} {{ ($stateCounts['reverted'] ?? 0) === 1 ? 'reversión' : 'reversiones' }}</span>
                </span>
            </div>
        </div>

        {{-- ============================= FILTROS ============================= --}}
        <section class="cf-card">
            <div class="cf-card__head">
                <span class="cf-card__icon"><i class="fa-solid fa-sliders"></i></span>
                <div class="cf-card__head-text">
                    <h2>Filtros</h2>
                    <p>Estado, aliado, fechas o búsqueda por referencia.</p>
                </div>
            </div>

            <div class="cf-card__body">
                <form action="{{ route('admin.payouts.index') }}" method="GET" data-cm-filters>
                    <div class="cf-grid">
                        <div class="form-group">
                            <label for="status">Estado</label>
                            <select name="status" id="status" class="filter-select">
                                <option value="all" @selected(request('status', 'all') === 'all')>Todos</option>
                                <option value="pending" @selected(request('status') === 'pending')>Pendiente</option>
                                <option value="processing" @selected(request('status') === 'processing')>Procesando</option>
                                <option value="completed" @selected(request('status') === 'completed')>Completado</option>
                                <option value="reverted" @selected(request('status') === 'reverted')>Revertido</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="ally_id">Aliado</label>
                            <select name="ally_id" id="ally_id" class="filter-select">
                                <option value="">Todos los aliados</option>
                                @foreach ($aliados ?? [] as $aliado)
                                    @php
                                        $aliadoId = $aliado['aliado_id'] ?? ($aliado['id'] ?? null);
                                        $aliadoNombre = $aliado['aliado_nombre'] ?? ($aliado['name'] ?? 'Aliado');
                                    @endphp
                                    @if ($aliadoId)
                                        <option value="{{ $aliadoId }}" @selected((string) request('ally_id') === (string) $aliadoId)>
                                            {{ $aliadoNombre }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="fecha_inicio">Desde</label>
                            <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control"
                                value="{{ request('fecha_inicio', now()->subMonth()->format('Y-m-d')) }}">
                        </div>

                        <div class="form-group">
                            <label for="fecha_fin">Hasta</label>
                            <input type="date" name="fecha_fin" id="fecha_fin" class="form-control"
                                value="{{ request('fecha_fin', now()->format('Y-m-d')) }}">
                        </div>

                        <div class="form-group cf-span">
                            <label for="search">Búsqueda</label>
                            <input type="search" name="search" id="search" class="form-control"
                                placeholder="ID, referencia o aliado…" value="{{ request('search') }}">
                        </div>
                    </div>

                    <div class="cf-actions" style="justify-content:flex-end;">
                        @if ($hasFilters)
                            <a href="{{ route('admin.payouts.index') }}" class="btn btn-secondary">
                                <i class="fa-solid fa-xmark"></i> Limpiar
                            </a>
                        @endif
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-magnifying-glass"></i> Filtrar
                        </button>
                    </div>
                </form>
            </div>
        </section>

        {{-- ============================== TABLA ============================== --}}
        <div class="cm-table-card">
            @if (empty($payouts) || count($payouts) === 0)
                <div class="cm-empty">
                    <i class="fa-solid fa-money-bill-transfer"></i>
                    <h3>No hay pagos registrados</h3>
                    <p>Los pagos aparecerán cuando se procesen ventas con aliados.</p>
                </div>
            @else
                <div class="rx-table-scroll">
                    <table class="rx-stack-table payout-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Aliado</th>
                                <th class="hide-mobile hide-wide">Venta</th>
                                <th class="hide-mobile">Monto</th>
                                <th class="hide-mobile hide-wide">Comisión</th>
                                <th>Neto</th>
                                <th>Estado</th>
                                <th class="hide-mobile">Generado</th>
                                <th class="hide-mobile hide-wide">Pagado</th>
                                <th class="hide-mobile hide-wide">Referencia</th>
                                <th style="text-align:right;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($payouts as $payout)
                                @php
                                    $payoutId = $payout->id ?? $payout['id'] ?? null;
                                    $saleAmount = $payout->sale_amount ?? $payout['sale_amount'] ?? 0;
                                    $commissionPercentage = $payout->commission_percentage ?? $payout['commission_percentage'] ?? 0;
                                    $commissionAmount = $payout->commission_amount ?? $payout['commission_amount'] ?? 0;
                                    $netAmount = $payout->net_amount ?? $payout['net_amount'] ?? 0;
                                    $status = $payout->status ?? $payout['status'] ?? 'pending';
                                    $generationDate = $payout->generation_date ?? $payout['generation_date'] ?? $payout->created_at ?? $payout['created_at'] ?? null;
                                    $paymentDate = $payout->payment_date ?? $payout['payment_date'] ?? null;
                                    $paymentReference = $payout->payment_reference ?? $payout['payment_reference'] ?? null;
                                    $saleId = $payout->sale_id ?? $payout['sale_id'] ?? null;

                                    $allyName = 'N/A';
                                    $allyEmail = '';
                                    if (isset($payout->ally) && $payout->ally) {
                                        $allyName = $payout->ally->company_name ?? $payout->ally->name ?? 'Aliado';
                                        $allyEmail = $payout->ally->email ?? '';
                                    } elseif (isset($payout['ally']) && is_array($payout['ally'])) {
                                        $allyName = $payout['ally']['company_name'] ?? $payout['ally']['name'] ?? 'Aliado';
                                        $allyEmail = $payout['ally']['email'] ?? '';
                                    }

                                    $statusItem = $statusMeta[$status] ?? ['label' => ucfirst($status), 'badge' => 'badge-neutral', 'icon' => 'fa-circle'];
                                @endphp

                                @if ($payoutId)
                                    <tr class="table-row payout-row">
                                        <td data-label="ID"><span class="cm-chip">#{{ $payoutId }}</span></td>
                                        <td data-label="Aliado">
                                            <div class="cm-user__text">
                                                <span class="cm-user__name">{{ $allyName }}</span>
                                                @if ($allyEmail)
                                                    <span class="cm-user__email">{{ $allyEmail }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="hide-mobile hide-wide" data-label="Venta">
                                            @if ($saleId)
                                                <a href="{{ route('admin.payouts.show', $payoutId) }}"
                                                    class="cm-user__email">#{{ $saleId }}</a>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="hide-mobile" data-label="Monto">
                                            Bs. {{ number_format($saleAmount, 0, ',', '.') }}
                                        </td>
                                        <td class="hide-mobile hide-wide" data-label="Comisión">
                                            <span class="badge badge-warning">{{ number_format($commissionPercentage, 1) }}%</span>
                                            <span class="cm-date"><small>Bs. {{ number_format($commissionAmount, 0, ',', '.') }}</small></span>
                                        </td>
                                        <td data-label="Neto">
                                            <strong>Bs. {{ number_format($netAmount, 0, ',', '.') }}</strong>
                                        </td>
                                        <td data-label="Estado">
                                            <span class="badge {{ $statusItem['badge'] }}">
                                                <i class="fa-solid {{ $statusItem['icon'] }}"></i> {{ $statusItem['label'] }}
                                            </span>
                                        </td>
                                        <td class="hide-mobile" data-label="Generado">
                                            @if ($generationDate)
                                                <span class="cm-date">
                                                    {{ \Carbon\Carbon::parse($generationDate)->format('d/m/Y') }}
                                                    <small>{{ \Carbon\Carbon::parse($generationDate)->format('H:i') }}</small>
                                                </span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="hide-mobile hide-wide" data-label="Pagado">
                                            {{ $paymentDate ? \Carbon\Carbon::parse($paymentDate)->format('d/m/Y') : '—' }}
                                        </td>
                                        <td class="hide-mobile hide-wide" data-label="Referencia">
                                            {{ $paymentReference ?: '—' }}
                                        </td>
                                        <td data-label="Acciones">
                                            <div class="cm-actions">
                                                <a href="{{ route('admin.payouts.show', $payoutId) }}" class="cm-icon-btn"
                                                    title="Ver detalle">
                                                    <i class="fa-solid fa-eye"></i>
                                                </a>

                                                @if ($status === 'pending')
                                                    <a href="{{ route('admin.payouts.edit', $payoutId) }}"
                                                        class="cm-icon-btn" title="Editar">
                                                        <i class="fa-solid fa-pen"></i>
                                                    </a>

                                                    <button type="button" class="cm-icon-btn cm-icon-btn--danger"
                                                        onclick="confirmarRevertir({{ $payoutId }})" title="Revertir">
                                                        <i class="fa-solid fa-rotate-left"></i>
                                                    </button>
                                                @endif

                                                <a href="{{ route('admin.payouts.auditoria', $payoutId) }}"
                                                    class="cm-icon-btn" title="Auditoría">
                                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if (isset($pagination))
                    <div class="cm-pagination">
                        <span class="cm-pagination__info">
                            Mostrando
                            <strong>{{ ($pagination['current_page'] - 1) * $pagination['per_page'] + 1 }}</strong>–<strong>{{ min($pagination['current_page'] * $pagination['per_page'], $pagination['total']) }}</strong>
                            de <strong>{{ $pagination['total'] }}</strong> pagos
                        </span>

                        <div class="payout-pagination">
                            @if ($pagination['current_page'] > 1)
                                <a href="{{ route('admin.payouts.index', array_merge(request()->query(), ['page' => $pagination['current_page'] - 1])) }}"
                                    class="cm-icon-btn"><i class="fa-solid fa-chevron-left"></i></a>
                            @endif

                            @for ($i = 1; $i <= $pagination['last_page']; $i++)
                                @if ($i == $pagination['current_page'])
                                    <span class="payout-page is-active">{{ $i }}</span>
                                @else
                                    <a href="{{ route('admin.payouts.index', array_merge(request()->query(), ['page' => $i])) }}"
                                        class="payout-page">{{ $i }}</a>
                                @endif
                            @endfor

                            @if ($pagination['current_page'] < $pagination['last_page'])
                                <a href="{{ route('admin.payouts.index', array_merge(request()->query(), ['page' => $pagination['current_page'] + 1])) }}"
                                    class="cm-icon-btn"><i class="fa-solid fa-chevron-right"></i></a>
                            @endif
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>

    {{-- ====================== MODAL DE REVERSIÓN ====================== --}}
    <div id="revertirModal" class="modal cf-modal hidden">
        <div class="modal-card" style="max-width:480px;">
            <div class="modal-header">
                <h3 class="modal-title"><i class="fa-solid fa-rotate-left"></i> Revertir pago</h3>
                <button type="button" class="close-modal-btn modal-close" aria-label="Cerrar">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="" method="POST" id="revertirForm">
                @csrf

                <div class="modal-body">
                    <p>¿Seguro que quieres revertir este pago?</p>
                    <p class="field-hint" style="margin-top:.35rem;">
                        <i class="fa-solid fa-triangle-exclamation"></i> Esta acción no se puede deshacer.
                    </p>

                    <div class="form-group" style="margin-top:1rem;">
                        <label for="motivo">Motivo de la reversión</label>
                        <textarea name="motivo" id="motivo" class="form-control" rows="3"
                            placeholder="Indica el motivo de la reversión…" required></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary cancel-modal-btn">
                        <i class="fa-solid fa-xmark"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fa-solid fa-rotate-left"></i> Revertir pago
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const revertirModal = document.getElementById('revertirModal');
            const revertirForm = document.getElementById('revertirForm');

            window.confirmarRevertir = function(payoutId) {
                revertirForm.action = '/admin/payouts/' + payoutId + '/revertir';
                revertirModal.classList.remove('hidden');
            };

            document.querySelectorAll('.close-modal-btn, .cancel-modal-btn').forEach(function(button) {
                button.addEventListener('click', function() {
                    revertirModal.classList.add('hidden');
                });
            });

            window.addEventListener('click', function(event) {
                if (event.target === revertirModal) {
                    revertirModal.classList.add('hidden');
                }
            });
        });
    </script>
@endpush
