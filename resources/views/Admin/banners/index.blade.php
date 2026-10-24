{{-- resources/views/Admin/Banners/index.blade.php --}}
@extends('layouts.admin')

@section('title', 'Banners')

@section('page_title_toolbar', 'Gestión de banners')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/pages.css') }}">
@endpush

@section('content')
    @php
        $total = $banners->count();
        $activos = $banners->where('is_active', true)->count();
        $inactivos = $total - $activos;
    @endphp

    <div class="cm-page">

        {{-- ============================ CABECERA ============================ --}}
        <div class="page-header-block">
            <div>
                <h1 class="title-main">Gestión de <span class="title-accent">banners</span></h1>
                <p class="page-subtitle">Administra las imágenes publicitarias que se muestran en el sitio.</p>
            </div>

            <a href="{{ route('admin.banners.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Nuevo banner
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
                <span class="cm-stat__icon"><i class="fa-solid fa-images"></i></span>
                <span>
                    <span class="cm-stat__value">{{ $total }}</span>
                    <span class="cm-stat__label">Total</span>
                </span>
            </div>

            <div class="cm-stat">
                <span class="cm-stat__icon cm-stat__icon--green"><i class="fa-solid fa-eye"></i></span>
                <span>
                    <span class="cm-stat__value">{{ $activos }}</span>
                    <span class="cm-stat__label">Activos</span>
                </span>
            </div>

            <div class="cm-stat">
                <span class="cm-stat__icon cm-stat__icon--gray"><i class="fa-solid fa-eye-slash"></i></span>
                <span>
                    <span class="cm-stat__value">{{ $inactivos }}</span>
                    <span class="cm-stat__label">Inactivos</span>
                </span>
            </div>
        </div>

        {{-- ============================= TARJETAS ============================= --}}
        @if ($banners->isEmpty())
            <div class="cm-empty">
                <i class="fa-regular fa-image"></i>
                <h3>Todavía no hay banners</h3>
                <p>Crea el primero para que aparezca en la portada del sitio.</p>
                <a href="{{ route('admin.banners.create') }}" class="btn btn-primary" style="margin-top:.5rem;">
                    <i class="fa-solid fa-plus"></i> Crear banner
                </a>
            </div>
        @else
            <div class="cm-grid">
                @foreach ($banners as $banner)
                    <article class="cm-card">
                        <div class="cm-card__media">
                            @if ($banner->image_src)
                                <img src="{{ $banner->image_src }}" alt="{{ $banner->title }}" loading="lazy"
                                    data-img>
                            @else
                                <div class="cm-card__noimg"><i class="fa-regular fa-image"></i></div>
                            @endif

                            <span class="cm-card__state {{ $banner->is_active ? 'is-on' : 'is-off' }}">
                                <i class="fa-solid {{ $banner->is_active ? 'fa-circle-check' : 'fa-circle-pause' }}"></i>
                                {{ $banner->is_active ? 'Activo' : 'Inactivo' }}
                            </span>

                            <span class="cm-card__order">Orden {{ $banner->display_order }}</span>
                        </div>

                        <div class="cm-card__body">
                            <h2 class="cm-card__title">{{ $banner->title ?: 'Banner sin título' }}</h2>
                            <p class="cm-card__desc">{{ $banner->description ?: 'Sin descripción.' }}</p>
                        </div>

                        <div class="cm-card__actions">
                            <a href="{{ route('admin.banners.edit', $banner->id) }}" class="cm-btn">
                                <i class="fa-solid fa-pen"></i> Editar
                            </a>

                            <form method="POST" action="{{ route('admin.banners.destroy', $banner->id) }}"
                                onsubmit="return confirm('¿Eliminar el banner «{{ $banner->title }}»?');"
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
