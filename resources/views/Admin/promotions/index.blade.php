{{-- resources/views/Admin/Promotions/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Promociones')

@section('page_title_toolbar', 'Gestión de promociones')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/pages.css') }}">
@endpush

@section('content')
    @php
        $total = $promotions->count();
        $activas = $promotions->where('status', 'active')->count();
        $vencidas = $promotions->filter(fn ($p) => $p->expires_at && $p->expires_at->isPast())->count();
    @endphp

    <div class="cm-page">

        {{-- ============================ CABECERA ============================ --}}
        <div class="page-header-block">
            <div>
                <h1 class="title-main">Gestión de <span class="title-accent">promociones</span></h1>
                <p class="page-subtitle">Ofertas y descuentos que ven los Rumberos en la app.</p>
            </div>

            <a href="{{ route('admin.promotions.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Nueva promoción
            </a>
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
                <span class="cm-stat__icon"><i class="fa-solid fa-tags"></i></span>
                <span>
                    <span class="cm-stat__value">{{ $total }}</span>
                    <span class="cm-stat__label">Total</span>
                </span>
            </div>

            <div class="cm-stat">
                <span class="cm-stat__icon cm-stat__icon--green"><i class="fa-solid fa-circle-check"></i></span>
                <span>
                    <span class="cm-stat__value">{{ $activas }}</span>
                    <span class="cm-stat__label">Activas</span>
                </span>
            </div>

            <div class="cm-stat">
                <span class="cm-stat__icon cm-stat__icon--gold"><i class="fa-solid fa-clock"></i></span>
                <span>
                    <span class="cm-stat__value">{{ $vencidas }}</span>
                    <span class="cm-stat__label">Vencidas</span>
                </span>
            </div>
        </div>

        {{-- ============================= TARJETAS ============================= --}}
        @if ($promotions->isEmpty())
            <div class="cm-empty">
                <i class="fa-solid fa-tags"></i>
                <h3>Todavía no hay promociones</h3>
                <p>Crea la primera para que los Rumberos empiecen a verla.</p>
                <a href="{{ route('admin.promotions.create') }}" class="btn btn-primary" style="margin-top:.5rem;">
                    <i class="fa-solid fa-plus"></i> Crear promoción
                </a>
            </div>
        @else
            <div class="cm-grid">
                @foreach ($promotions as $promotion)
                    @php
                        $vencida = $promotion->expires_at && $promotion->expires_at->isPast();
                    @endphp
                    <article class="cm-card">
                        <div class="cm-card__media">
                            @if ($promotion->image_src)
                                <img src="{{ $promotion->image_src }}" alt="{{ $promotion->title }}" loading="lazy"
                                    data-img>
                            @else
                                <div class="cm-card__noimg"><i class="fa-solid fa-tag"></i></div>
                            @endif

                            <span class="cm-card__state {{ $promotion->is_active && !$vencida ? 'is-on' : 'is-off' }}">
                                <i
                                    class="fa-solid {{ $vencida ? 'fa-clock' : ($promotion->is_active ? 'fa-circle-check' : 'fa-circle-pause') }}"></i>
                                {{ $vencida ? 'Vencida' : ($promotion->is_active ? 'Activa' : 'Inactiva') }}
                            </span>

                            @if ($promotion->discount)
                                <span class="cm-card__order">{{ $promotion->discount_label }}</span>
                            @endif
                        </div>

                        <div class="cm-card__body">
                            <h2 class="cm-card__title">{{ $promotion->title ?: 'Promoción sin título' }}</h2>
                            <p class="cm-card__desc">{{ $promotion->description ?: 'Sin descripción.' }}</p>

                            <div class="cm-card__meta">
                                @if ($promotion->price)
                                    <span class="cm-chip cm-chip--primary">
                                        <i class="fa-solid fa-dollar-sign"></i>
                                        {{ number_format((float) $promotion->price, 2, ',', '.') }}
                                    </span>
                                @endif

                                <span class="cm-chip {{ $vencida ? 'cm-chip--danger' : '' }}">
                                    <i class="fa-regular fa-calendar"></i>
                                    {{ $promotion->expires_at ? $promotion->expires_at->format('d/m/Y') : 'Sin caducidad' }}
                                </span>
                            </div>
                        </div>

                        <div class="cm-card__actions">
                            <a href="{{ route('admin.promotions.edit', $promotion->id) }}" class="cm-btn">
                                <i class="fa-solid fa-pen"></i> Editar
                            </a>

                            <form method="POST" action="{{ route('admin.promotions.destroy', $promotion->id) }}"
                                onsubmit="return confirm('¿Eliminar la promoción «{{ $promotion->title }}»?');"
                                style="flex:1 1 0;display:flex;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="cm-btn cm-btn--danger" style="width:100%;">
                                    <i class="fa-solid fa-trash-can"></i> Eliminar
                                </button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
@endsection
