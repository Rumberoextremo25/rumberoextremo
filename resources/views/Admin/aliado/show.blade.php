{{-- resources/views/Admin/aliado/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'Detalles del Aliado - ' . $ally->company_name)

@section('page_title_toolbar', 'Detalles del Aliado')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/aliados.css') }}">
@endpush

@section('content')
    <div class="aliado-detail-container">
        {{-- Header --}}
        <div class="detail-header-modern">
            <div class="header-content">
                <div class="breadcrumb">
                    <a href="{{ route('aliados.index') }}" class="breadcrumb-link">
                        <i class="fas fa-arrow-left"></i>
                        Volver al Listado
                    </a>
                </div>
                <h1 class="detail-title">
                    <span class="title-prefix">Aliado:</span>
                    <span class="title-main">{{ $ally->company_name }}</span>
                </h1>
                <div class="header-actions">
                    <span class="status-display badge-status-{{ strtolower($ally->status) }}">
                        {{ ucfirst($ally->status) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Tarjeta Principal --}}
        <div class="detail-card-modern">
            {{-- Información General --}}
            <div class="card-section">
                <h3 class="section-title">
                    <i class="fas fa-info-circle"></i>
                    Información General
                </h3>
                <div class="info-grid">
                    <div class="info-item">
                        <label class="info-label">ID</label>
                        <span class="info-value">#{{ $ally->id }}</span>
                    </div>
                    <div class="info-item">
                        <label class="info-label">Nombre de la Empresa</label>
                        <span class="info-value highlight">{{ $ally->company_name }}</span>
                    </div>
                    <div class="info-item">
                        <label class="info-label">RIF</label>
                        <span class="info-value">{{ $ally->company_rif ?? 'No especificado' }}</span>
                    </div>
                    <div class="info-item">
                        <label class="info-label">Fecha de Registro</label>
                        <span class="info-value">
                            <i class="fas fa-calendar"></i>
                            {{ \Carbon\Carbon::parse($ally->registered_at)->format('d/m/Y') }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Información de Contacto --}}
            <div class="card-section">
                <h3 class="section-title">
                    <i class="fas fa-address-book"></i>
                    Información de Contacto
                </h3>
                <div class="info-grid">
                    <div class="info-item">
                        <label class="info-label">Persona de Contacto</label>
                        <span class="info-value">
                            <i class="fas fa-user"></i>
                            {{ $ally->contact_person_name }}
                        </span>
                    </div>
                    <div class="info-item">
                        <label class="info-label">Email de Contacto</label>
                        <span class="info-value">
                            <i class="fas fa-envelope"></i>
                            {{ $ally->contact_email }}
                        </span>
                    </div>
                    <div class="info-item">
                        <label class="info-label">Teléfono Principal</label>
                        <span class="info-value">
                            <i class="fas fa-phone"></i>
                            {{ $ally->contact_phone }}
                        </span>
                    </div>
                    <div class="info-item">
                        <label class="info-label">Teléfono Alternativo</label>
                        <span class="info-value">
                            <i class="fas fa-phone-alt"></i>
                            {{ $ally->contact_phone_alt ?? 'No especificado' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Información de Negocio --}}
            <div class="card-section">
                <h3 class="section-title">
                    <i class="fas fa-briefcase"></i>
                    Información de Negocio
                </h3>
                <div class="info-grid">
                    <div class="info-item">
                        <label class="info-label">Tipo de Aliado</label>
                        <span class="info-value badge-type-{{ strtolower($ally->businessType->name ?? 'sin-tipo') }}">
                            {{ $ally->businessType->name ?? 'N/A' }}
                        </span>
                    </div>
                    <div class="info-item">
                        <label class="info-label">Categoría</label>
                        <span class="info-value">{{ $ally->category->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <label class="info-label">Subcategoría</label>
                        <span class="info-value">{{ $ally->subCategory->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <label class="info-label">Descuento Ofrecido</label>
                        <span class="info-value discount-highlight">
                            {{ $ally->discount ?? '0' }}%
                        </span>
                    </div>
                </div>
            </div>

            {{-- Información Adicional --}}
            <div class="card-section">
                <h3 class="section-title">
                    <i class="fas fa-file-alt"></i>
                    Información Adicional
                </h3>
                <div class="info-grid full-width">
                    <div class="info-item full-width">
                        <label class="info-label">Dirección de la Empresa</label>
                        <span class="info-value">{{ $ally->company_address ?? 'No especificada' }}</span>
                    </div>
                    <div class="info-item">
                        <label class="info-label">Sitio Web</label>
                        <span class="info-value">
                            @if($ally->website_url)
                                <a href="{{ $ally->website_url }}" target="_blank" class="website-link">
                                    <i class="fas fa-external-link-alt"></i>
                                    Visitar Sitio Web
                                </a>
                            @else
                                No especificado
                            @endif
                        </span>
                    </div>
                    <div class="info-item full-width">
                        <label class="info-label">Descripción</label>
                        <div class="info-value description-text">
                            {{ $ally->description ?? 'Sin descripción' }}
                        </div>
                    </div>
                    @if($ally->notes)
                    <div class="info-item full-width">
                        <label class="info-label">Notas Adicionales</label>
                        <div class="info-value notes-text">
                            {{ $ally->notes }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Imagen del Aliado --}}
            @if($ally->image_url)
            <div class="card-section">
                <h3 class="section-title">
                    <i class="fas fa-image"></i>
                    Imagen del Aliado
                </h3>
                <div class="image-container">
                    <img src="{{ Storage::url($ally->image_url) }}" 
                         alt="{{ $ally->company_name }}" 
                         class="aliado-image-preview">
                </div>
            </div>
            @endif
        </div>

        {{-- Acciones --}}
        <div class="action-buttons-modern">
            <a href="{{ route('aliado.edit', $ally->id) }}" class="modern-primary-btn">
                <i class="fas fa-edit"></i>
                Editar Aliado
            </a>
            <a href="{{ route('aliados.index') }}" class="modern-secondary-btn">
                <i class="fas fa-list"></i>
                Volver al Listado
            </a>
        </div>
    </div>
@endsection