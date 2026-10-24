@extends('layouts.admin')

@section('title', 'Editar video de demo')

@section('page_title_toolbar', 'Editar video de demo')

@section('content')
    <div class="cm-page cf-page">

        <div class="page-header-block">
            <div>
                <h1 class="title-main">Editar <span class="title-accent">video</span></h1>
                <p class="page-subtitle">
                    Actualiza «{{ $video->title }}» o cambia el orden en el carrusel.
                </p>
            </div>

            <a href="{{ route('admin.demo-videos.index') }}" class="btn btn-secondary">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Volver al listado
            </a>
        </div>

        @include('Admin.demo-videos._form', ['video' => $video])
    </div>
@endsection
