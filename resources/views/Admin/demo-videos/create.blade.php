@extends('layouts.admin')

@section('title', 'Nuevo video de demo')

@section('page_title_toolbar', 'Nuevo video de demo')

@section('content')
    <div class="cm-page cf-page">

        <div class="page-header-block">
            <div>
                <h1 class="title-main">Nuevo <span class="title-accent">video</span></h1>
                <p class="page-subtitle">
                    Agrega un video al carrusel de la página de demo. Usa YouTube o sube un archivo.
                </p>
            </div>

            <a href="{{ route('admin.demo-videos.index') }}" class="btn btn-secondary">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Volver al listado
            </a>
        </div>

        @include('Admin.demo-videos._form', ['video' => null])
    </div>
@endsection
