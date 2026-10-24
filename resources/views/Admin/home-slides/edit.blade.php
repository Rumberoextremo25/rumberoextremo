@extends('layouts.admin')

@section('title', 'Editar imagen del Home')

@section('page_title_toolbar', 'Editar imagen del Home')

@section('content')
    <div class="cm-page cf-page">
        <div class="page-header-block">
            <div>
                <h1 class="title-main">Editar <span class="title-accent">imagen</span></h1>
                <p class="page-subtitle">Actualiza «{{ $slide->title ?: 'imagen del carrusel' }}» o cambia su orden.</p>
            </div>

            <a href="{{ route('admin.home-slides.index') }}" class="btn btn-secondary">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Volver al listado
            </a>
        </div>

        @include('Admin.home-slides._form', ['slide' => $slide])
    </div>
@endsection
