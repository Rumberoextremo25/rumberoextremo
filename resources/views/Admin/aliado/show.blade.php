{{-- resources/views/Admin/aliado/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'Detalles del Aliado - ' . $ally->company_name)

@section('page_title_toolbar', 'Detalles del Aliado')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/aliado-show.css') }}">
@endpush

@section('content')
    <div class="aliado-detail-container">
        {{-- Header Moderno --}}
        <div class="detail-header-modern">
            <div class="header-content">
                <div class="breadcrumb">
                    <a href="{{ route('aliados.index') }}" class="breadcrumb-link">
                        <i class="fas fa-arrow-left"></i>
                        Volver al Listado
                    </a>
                </div>
                <div class="title-section">
                    <h1 class="detail-title">
                        <span class="title-prefix">Aliado:</span>
                        <span class="title-main">{{ $ally->company_name }}</span>
                    </h1>
                    <div class="title-meta">
                        <span class="id-badge">ID #{{ $ally->id }}</span>
                        <span class="status-display badge-status-{{ strtolower($ally->status) }}">
                            <i class="fas fa-circle"></i>
                            {{ ucfirst($ally->status) }}
                        </span>
                        <span class="registration-date">
                            <i class="fas fa-calendar"></i>
                            Registrado: {{ \Carbon\Carbon::parse($ally->registered_at)->format('d/m/Y') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Estadísticas Rápidas --}}
        <div class="quick-stats-container">
            <div class="stat-card">
                <div class="stat-icon discount">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-number">{{ $ally->discount ?? '0' }}%</span>
                    <span class="stat-label">Descuento</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon category">
                    <i class="fas fa-tag"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-number">{{ $ally->category->name ?? 'N/A' }}</span>
                    <span class="stat-label">Categoría</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon type">
                    <i class="fas fa-briefcase"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-number">{{ $ally->businessType->name ?? 'N/A' }}</span>
                    <span class="stat-label">Tipo</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon contact">
                    <i class="fas fa-user"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-number">{{ $ally->contact_person_name }}</span>
                    <span class="stat-label">Contacto</span>
                </div>
            </div>
        </div>

        {{-- Tarjeta Principal con Pestañas --}}
        <div class="detail-card-modern">
            {{-- Navegación por Pestañas --}}
            <div class="tab-navigation">
                <button class="tab-button active" data-tab="general">
                    <i class="fas fa-info-circle"></i>
                    Información General
                </button>
                <button class="tab-button" data-tab="contact">
                    <i class="fas fa-address-book"></i>
                    Contacto
                </button>
                <button class="tab-button" data-tab="business">
                    <i class="fas fa-briefcase"></i>
                    Negocio
                </button>
                <button class="tab-button" data-tab="additional">
                    <i class="fas fa-file-alt"></i>
                    Adicional
                </button>
                @if($ally->image_url)
                <button class="tab-button" data-tab="media">
                    <i class="fas fa-image"></i>
                    Multimedia
                </button>
                @endif
            </div>

            {{-- Contenido de Pestañas --}}
            <div class="tab-content">
                {{-- Pestaña Información General --}}
                <div class="tab-pane active" id="general-tab">
                    <div class="info-grid">
                        <div class="info-item">
                            <label class="info-label">
                                <i class="fas fa-building"></i>
                                Nombre de la Empresa
                            </label>
                            <span class="info-value highlight">{{ $ally->company_name }}</span>
                        </div>
                        <div class="info-item">
                            <label class="info-label">
                                <i class="fas fa-id-card"></i>
                                RIF
                            </label>
                            <span class="info-value">{{ $ally->company_rif ?? 'No especificado' }}</span>
                        </div>
                        <div class="info-item">
                            <label class="info-label">
                                <i class="fas fa-map-marker-alt"></i>
                                Dirección
                            </label>
                            <span class="info-value">{{ $ally->company_address ?? 'No especificada' }}</span>
                        </div>
                        <div class="info-item">
                            <label class="info-label">
                                <i class="fas fa-globe"></i>
                                Sitio Web
                            </label>
                            <span class="info-value">
                                @if($ally->website_url)
                                    <a href="{{ $ally->website_url }}" target="_blank" class="website-link">
                                        <i class="fas fa-external-link-alt"></i>
                                        {{ $ally->website_url }}
                                    </a>
                                @else
                                    <span class="no-info">No especificado</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Pestaña Información de Contacto --}}
                <div class="tab-pane" id="contact-tab">
                    <div class="info-grid">
                        <div class="info-item">
                            <label class="info-label">
                                <i class="fas fa-user-tie"></i>
                                Persona de Contacto
                            </label>
                            <span class="info-value">{{ $ally->contact_person_name }}</span>
                        </div>
                        <div class="info-item">
                            <label class="info-label">
                                <i class="fas fa-envelope"></i>
                                Email de Contacto
                            </label>
                            <span class="info-value">
                                <a href="mailto:{{ $ally->contact_email }}" class="email-link">
                                    {{ $ally->contact_email }}
                                </a>
                            </span>
                        </div>
                        <div class="info-item">
                            <label class="info-label">
                                <i class="fas fa-phone"></i>
                                Teléfono Principal
                            </label>
                            <span class="info-value">
                                <a href="tel:{{ $ally->contact_phone }}" class="phone-link">
                                    {{ $ally->contact_phone }}
                                </a>
                            </span>
                        </div>
                        <div class="info-item">
                            <label class="info-label">
                                <i class="fas fa-phone-alt"></i>
                                Teléfono Alternativo
                            </label>
                            <span class="info-value">
                                @if($ally->contact_phone_alt)
                                    <a href="tel:{{ $ally->contact_phone_alt }}" class="phone-link">
                                        {{ $ally->contact_phone_alt }}
                                    </a>
                                @else
                                    <span class="no-info">No especificado</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Pestaña Información de Negocio --}}
                <div class="tab-pane" id="business-tab">
                    <div class="info-grid">
                        <div class="info-item">
                            <label class="info-label">
                                <i class="fas fa-tags"></i>
                                Tipo de Aliado
                            </label>
                            <span class="info-value badge-type-{{ strtolower($ally->businessType->name ?? 'sin-tipo') }}">
                                {{ $ally->businessType->name ?? 'N/A' }}
                            </span>
                        </div>
                        <div class="info-item">
                            <label class="info-label">
                                <i class="fas fa-layer-group"></i>
                                Categoría
                            </label>
                            <span class="info-value">{{ $ally->category->name ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <label class="info-label">
                                <i class="fas fa-sitemap"></i>
                                Subcategoría
                            </label>
                            <span class="info-value">{{ $ally->subCategory->name ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <label class="info-label">
                                <i class="fas fa-percentage"></i>
                                Descuento Ofrecido
                            </label>
                            <span class="info-value discount-highlight">
                                {{ $ally->discount ?? '0' }}%
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Pestaña Información Adicional --}}
                <div class="tab-pane" id="additional-tab">
                    <div class="info-grid full-width">
                        <div class="info-item full-width">
                            <label class="info-label">
                                <i class="fas fa-align-left"></i>
                                Descripción del Negocio
                            </label>
                            <div class="info-value description-text">
                                {{ $ally->description ?? 'Sin descripción proporcionada.' }}
                            </div>
                        </div>
                        @if($ally->notes)
                        <div class="info-item full-width">
                            <label class="info-label">
                                <i class="fas fa-sticky-note"></i>
                                Notas Adicionales
                            </label>
                            <div class="info-value notes-text">
                                {{ $ally->notes }}
                            </div>
                        </div>
                        @endif
                        <div class="info-item">
                            <label class="info-label">
                                <i class="fas fa-calendar-plus"></i>
                                Fecha de Registro
                            </label>
                            <span class="info-value">
                                {{ \Carbon\Carbon::parse($ally->registered_at)->format('d/m/Y H:i') }}
                            </span>
                        </div>
                        <div class="info-item">
                            <label class="info-label">
                                <i class="fas fa-calendar-check"></i>
                                Última Actualización
                            </label>
                            <span class="info-value">
                                {{ \Carbon\Carbon::parse($ally->updated_at)->format('d/m/Y H:i') }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Pestaña Multimedia --}}
                @if($ally->image_url)
                <div class="tab-pane" id="media-tab">
                    <div class="media-container">
                        <div class="image-preview-container">
                            <h4 class="media-title">
                                <i class="fas fa-image"></i>
                                Imagen del Aliado
                            </h4>
                            <div class="image-wrapper">
                                <img src="{{ Storage::url($ally->image_url) }}" 
                                     alt="{{ $ally->company_name }}" 
                                     class="aliado-image-preview"
                                     id="allyImage">
                                <div class="image-actions">
                                    <button class="image-action-btn view-btn" onclick="openImageModal('{{ Storage::url($ally->image_url) }}')">
                                        <i class="fas fa-expand"></i>
                                        Ver Completa
                                    </button>
                                    <a href="{{ Storage::url($ally->image_url) }}" 
                                       download="{{ $ally->company_name }}.jpg" 
                                       class="image-action-btn download-btn">
                                        <i class="fas fa-download"></i>
                                        Descargar
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Acciones Rápidas --}}
        <div class="quick-actions-panel">
            <div class="actions-header">
                <h3 class="actions-title">
                    <i class="fas fa-bolt"></i>
                    Acciones Rápidas
                </h3>
            </div>
            <div class="actions-grid">
                <a href="{{ route('aliado.edit', $ally->id) }}" class="action-card edit">
                    <div class="action-icon">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div class="action-content">
                        <span class="action-title">Editar Aliado</span>
                        <span class="action-desc">Modificar información del aliado</span>
                    </div>
                    <i class="fas fa-chevron-right action-arrow"></i>
                </a>
                
                <button class="action-card status" onclick="toggleStatus()">
                    <div class="action-icon">
                        <i class="fas fa-toggle-on"></i>
                    </div>
                    <div class="action-content">
                        <span class="action-title">Cambiar Estado</span>
                        <span class="action-desc">Activar/Desactivar aliado</span>
                    </div>
                    <i class="fas fa-chevron-right action-arrow"></i>
                </button>
                
                <a href="{{ route('aliados.index') }}" class="action-card back">
                    <div class="action-icon">
                        <i class="fas fa-list"></i>
                    </div>
                    <div class="action-content">
                        <span class="action-title">Volver al Listado</span>
                        <span class="action-desc">Ver todos los aliados</span>
                    </div>
                    <i class="fas fa-chevron-right action-arrow"></i>
                </a>
                
                <form action="{{ route('aliados.destroy', $ally->id) }}" method="POST" class="action-form">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="action-card delete" onclick="return confirmDelete()">
                        <div class="action-icon">
                            <i class="fas fa-trash"></i>
                        </div>
                        <div class="action-content">
                            <span class="action-title">Eliminar Aliado</span>
                            <span class="action-desc">Eliminar permanentemente</span>
                        </div>
                        <i class="fas fa-chevron-right action-arrow"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal para Imagen --}}
    <div id="imageModal" class="modal-overlay">
        <div class="modal-content image-modal">
            <button class="modal-close" onclick="closeImageModal()">
                <i class="fas fa-times"></i>
            </button>
            <img src="" alt="" class="modal-image" id="modalImage">
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Navegación por pestañas
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabPanes = document.querySelectorAll('.tab-pane');

        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const targetTab = this.getAttribute('data-tab');
                
                // Remover clase activa de todos los botones y paneles
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabPanes.forEach(pane => pane.classList.remove('active'));
                
                // Agregar clase activa al botón y panel actual
                this.classList.add('active');
                document.getElementById(`${targetTab}-tab`).classList.add('active');
            });
        });

        // Animaciones de entrada
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observar elementos para animaciones
        document.querySelectorAll('.stat-card, .info-item, .action-card').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(el);
        });
    });

    // Funciones de utilidad
    function openImageModal(imageSrc) {
        document.getElementById('modalImage').src = imageSrc;
        document.getElementById('imageModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeImageModal() {
        document.getElementById('imageModal').classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    function toggleStatus() {
        // Aquí iría la lógica para cambiar el estado del aliado
        alert('Funcionalidad para cambiar estado en desarrollo');
    }

    function confirmDelete() {
        return confirm('¿Estás seguro de que quieres eliminar este aliado? Esta acción no se puede deshacer.');
    }

    // Cerrar modal con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeImageModal();
        }
    });
</script>
@endpush