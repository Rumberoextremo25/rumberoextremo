@extends('layouts.admin')

@section('title', 'Carrusel del Home')

@section('page_title_toolbar', 'Carrusel del Home')

@section('content')
    @php
        $total = $slides->count();
        $active = $slides->where('is_active', true)->count();
    @endphp

    <div class="cm-page hs-page">

        <div class="page-header-block">
            <div>
                <h1 class="title-main">Carrusel del <span class="title-accent">Home</span></h1>
                <p class="page-subtitle">
                    Administra las imágenes de la galería «Así se vive la experiencia» en la portada del sitio.
                </p>
            </div>

            <a href="{{ route('admin.home-slides.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus" aria-hidden="true"></i> Nueva imagen
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

        <div class="cm-stats">
            <div class="cm-stat">
                <span class="cm-stat__icon"><i class="fa-solid fa-images" aria-hidden="true"></i></span>
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
        </div>

        @if ($slides->isEmpty())
            <div class="cm-empty">
                <i class="fa-solid fa-images" aria-hidden="true"></i>
                <h3>Todavía no hay imágenes</h3>
                <p>Agrega la primera para que aparezca en el carrusel del Home.</p>
                <a href="{{ route('admin.home-slides.create') }}" class="btn btn-primary" style="margin-top:.5rem;">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i> Crear imagen
                </a>
            </div>
        @else
            <div class="hs-grid">
                @foreach ($slides as $slide)
                    <article class="hs-card {{ $slide->is_active ? '' : 'hs-card--inactive' }}">
                        <div class="hs-card__media">
                            @if ($slide->image_src)
                                <img src="{{ $slide->image_src }}" alt="{{ $slide->alt_text ?: $slide->title }}" loading="lazy">
                            @else
                                <div class="hs-card__placeholder">
                                    <i class="fa-regular fa-image" aria-hidden="true"></i>
                                </div>
                            @endif

                            <span class="hs-order">Orden {{ $slide->display_order }}</span>

                            @if (! $slide->is_active)
                                <span class="hs-off">Oculto</span>
                            @endif
                        </div>

                        <div class="hs-card__body">
                            <h2>{{ $slide->title ?: 'Imagen sin título' }}</h2>
                            <p>{{ $slide->alt_text ?: 'Sin texto alternativo.' }}</p>
                            @if ($slide->target_url)
                                <span class="hs-link"><i class="fa-solid fa-link" aria-hidden="true"></i> {{ $slide->target_url }}</span>
                            @endif
                        </div>

                        <div class="hs-card__actions">
                            <a href="{{ route('admin.home-slides.edit', $slide) }}" class="cm-btn">
                                <i class="fa-solid fa-pen" aria-hidden="true"></i> Editar
                            </a>

                            <form method="POST" action="{{ route('admin.home-slides.toggle', $slide) }}">
                                @csrf
                                <button type="submit" class="cm-btn">
                                    <i class="fa-solid {{ $slide->is_active ? 'fa-eye-slash' : 'fa-eye' }}" aria-hidden="true"></i>
                                    {{ $slide->is_active ? 'Ocultar' : 'Mostrar' }}
                                </button>
                            </form>

                            <form method="POST" action="{{ route('admin.home-slides.destroy', $slide) }}"
                                data-confirm="¿Eliminar la imagen «{{ $slide->title ?: 'sin título' }}» del carrusel?">
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
