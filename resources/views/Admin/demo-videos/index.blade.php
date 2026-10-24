@extends('layouts.admin')

@section('title', 'Videos de demo')

@section('page_title_toolbar', 'Videos de la demo')

@section('content')
    @php
        $total = $videos->count();
        $active = $videos->where('is_active', true)->count();
        $youtube = $videos->where('source', 'youtube')->count();
        $files = $total - $youtube;
    @endphp

    <div class="cm-page dv-page">

        {{-- ============================ CABECERA ============================ --}}
        <div class="page-header-block">
            <div>
                <h1 class="title-main">Videos de la <span class="title-accent">demo</span></h1>
                <p class="page-subtitle">
                    Administra el carrusel de videos de la página de demo. Puedes usar YouTube o subir archivos MP4/WebM.
                </p>
            </div>

            <a href="{{ route('admin.demo-videos.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus" aria-hidden="true"></i> Nuevo video
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                <div class="alert-content">{{ session('success') }}</div>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                <div class="alert-content">{{ session('error') }}</div>
            </div>
        @endif

        {{-- ============================= MÉTRICAS ============================= --}}
        <div class="cm-stats">
            <div class="cm-stat">
                <span class="cm-stat__icon"><i class="fa-solid fa-clapperboard" aria-hidden="true"></i></span>
                <span>
                    <span class="cm-stat__value">{{ $total }}</span>
                    <span class="cm-stat__label">Total</span>
                </span>
            </div>
            <div class="cm-stat">
                <span class="cm-stat__icon cm-stat__icon--green"><i class="fa-solid fa-eye" aria-hidden="true"></i></span>
                <span>
                    <span class="cm-stat__value">{{ $active }}</span>
                    <span class="cm-stat__label">En el carrusel</span>
                </span>
            </div>
            <div class="cm-stat">
                <span class="cm-stat__icon"><i class="fa-brands fa-youtube" aria-hidden="true"></i></span>
                <span>
                    <span class="cm-stat__value">{{ $youtube }}</span>
                    <span class="cm-stat__label">YouTube</span>
                </span>
            </div>
            <div class="cm-stat">
                <span class="cm-stat__icon cm-stat__icon--gray"><i class="fa-solid fa-file-video" aria-hidden="true"></i></span>
                <span>
                    <span class="cm-stat__value">{{ $files }}</span>
                    <span class="cm-stat__label">Archivos</span>
                </span>
            </div>
        </div>

        {{-- ============================= TARJETAS ============================= --}}
        @if ($videos->isEmpty())
            <div class="cm-empty">
                <i class="fa-solid fa-clapperboard" aria-hidden="true"></i>
                <h3>Todavía no hay videos</h3>
                <p>Agrega el primero para que aparezca en el carrusel de la demo.</p>
                <a href="{{ route('admin.demo-videos.create') }}" class="btn btn-primary" style="margin-top:.5rem;">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i> Crear video
                </a>
            </div>
        @else
            <div class="dv-grid">
                @foreach ($videos as $video)
                    <article class="dv-card {{ $video->is_active ? '' : 'dv-card--inactive' }}">
                        <div class="dv-card__media">
                            @if ($video->thumbnail_src)
                                <img src="{{ $video->thumbnail_src }}" alt="{{ $video->title }}" loading="lazy">
                            @else
                                <div class="dv-card__placeholder">
                                    <i class="fa-solid fa-video" aria-hidden="true"></i>
                                </div>
                            @endif

                            <span class="dv-source {{ $video->is_youtube ? 'dv-source--youtube' : 'dv-source--file' }}">
                                <i class="fa-brands {{ $video->is_youtube ? 'fa-youtube' : 'fa-file-video' }}" aria-hidden="true"></i>
                                {{ $video->is_youtube ? 'YouTube' : 'Archivo' }}
                            </span>

                            <span class="dv-order">Orden {{ $video->display_order }}</span>

                            @if (! $video->is_active)
                                <span class="dv-off">Oculto</span>
                            @endif
                        </div>

                        <div class="dv-card__body">
                            <h2>{{ $video->title }}</h2>
                            <p>{{ $video->description ?: 'Sin descripción.' }}</p>
                            <span class="dv-card__meta">
                                <i class="fa-solid {{ $video->is_active ? 'fa-circle-check' : 'fa-circle-pause' }}" aria-hidden="true"></i>
                                {{ $video->is_active ? 'Visible en la demo' : 'Oculto en la demo' }}
                            </span>
                        </div>

                        <div class="dv-card__actions">
                            <a href="{{ route('admin.demo-videos.edit', $video) }}" class="cm-btn">
                                <i class="fa-solid fa-pen" aria-hidden="true"></i> Editar
                            </a>

                            <form method="POST" action="{{ route('admin.demo-videos.toggle', $video) }}">
                                @csrf
                                <button type="submit" class="cm-btn">
                                    <i class="fa-solid {{ $video->is_active ? 'fa-eye-slash' : 'fa-eye' }}" aria-hidden="true"></i>
                                    {{ $video->is_active ? 'Ocultar' : 'Mostrar' }}
                                </button>
                            </form>

                            <form method="POST" action="{{ route('admin.demo-videos.destroy', $video) }}"
                                data-confirm="¿Eliminar el video «{{ $video->title }}»?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="cm-btn cm-btn--danger">
                                    <i class="fa-solid fa-trash-can" aria-hidden="true"></i> Eliminar
                                </button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('form[data-confirm]').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!window.confirm(form.getAttribute('data-confirm') || '¿Continuar?')) {
                    event.preventDefault();
                }
            });
        });
    </script>
@endpush
