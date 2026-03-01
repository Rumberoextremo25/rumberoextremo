{{-- resources/views/Admin/aliado/show.blade.php --}}
@extends('layouts.admin')

@section('title', 'Detalles del Aliado - ' . $ally->company_name)

@section('page_title_toolbar', 'Detalles del Aliado')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/aliado-show.css') }}">
@endpush

@section('content')
    <div class="aliado-detail-wrapper">
        {{-- Header con bienvenida --}}
        <div class="detail-header-bar">
            <div class="header-left">
                <a href="{{ route('admin.aliados.index') }}" class="back-link">
                    <i class="fas fa-arrow-left"></i>
                    <span>Volver al Listado</span>
                </a>
                <div class="page-title">
                    <span class="title-main">Detalles del</span>
                    <span class="title-accent">Aliado</span>
                </div>
            </div>
            <div class="header-actions">
                <div class="user-greeting">
                    <span>Visualizando,</span>
                    <strong>{{ Auth::user()->name }}</strong>
                </div>
                <div class="avatar-circle">
                    <span>{{ substr(Auth::user()->name, 0, 1) }}</span>
                </div>
            </div>
        </div>

        {{-- Tarjeta principal del perfil --}}
        <div class="profile-main-card">
            {{-- Cabecera del perfil con portada --}}
            <div class="profile-cover">
                <div class="profile-avatar-wrapper">
                    <div class="profile-avatar">
                        <img src="{{ asset('assets/img/dashboard/logo_aliados.png') }}" alt="Aliado">
                    </div>
                </div>
                <div class="profile-badge-container">
                    <span class="badge badge-type-{{ strtolower($ally->businessType->name ?? 'sin-tipo') }}">
                        <i class="fas fa-tag"></i>
                        {{ $ally->businessType->name ?? 'Tipo no definido' }}
                    </span>
                    <span class="badge badge-status-{{ strtolower($ally->status) }}">
                        <i class="fas fa-circle"></i>
                        {{ ucfirst($ally->status) }}
                    </span>
                </div>
            </div>

            {{-- Información básica del aliado --}}
            <div class="profile-info-header">
                <h2>{{ $ally->company_name }}</h2>
                <p class="company-identifier">
                    <i class="fas fa-id-card"></i>
                    {{ $ally->company_rif ?? 'RIF no especificado' }}
                </p>
                <div class="company-meta">
                    <span><i class="fas fa-calendar-alt"></i> Registrado: {{ \Carbon\Carbon::parse($ally->registered_at ?? $ally->created_at)->format('d/m/Y') }}</span>
                    <span><i class="fas fa-sync-alt"></i> Actualizado: {{ $ally->updated_at->diffForHumans() }}</span>
                </div>
            </div>

            {{-- Tarjetas de estadísticas rápidas --}}
            <div class="quick-stats-grid">
                <div class="stat-card" data-color="purple">
                    <div class="stat-icon">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-value">{{ $ally->discount ?? '0' }}%</span>
                        <span class="stat-label">Descuento</span>
                    </div>
                </div>
                <div class="stat-card" data-color="blue">
                    <div class="stat-icon">
                        <i class="fas fa-tag"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-value">{{ $ally->category->name ?? 'N/A' }}</span>
                        <span class="stat-label">Categoría</span>
                    </div>
                </div>
                <div class="stat-card" data-color="green">
                    <div class="stat-icon">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-value">{{ $ally->businessType->name ?? 'N/A' }}</span>
                        <span class="stat-label">Tipo</span>
                    </div>
                </div>
                <div class="stat-card" data-color="orange">
                    <div class="stat-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-value">{{ $ally->contact_person_name ?? 'N/A' }}</span>
                        <span class="stat-label">Contacto</span>
                    </div>
                </div>
            </div>

            {{-- Navegación por pestañas --}}
            <div class="tabs-navigation">
                <button class="tab-btn active" data-tab="general">
                    <i class="fas fa-info-circle"></i>
                    General
                </button>
                <button class="tab-btn" data-tab="contact">
                    <i class="fas fa-address-book"></i>
                    Contacto
                </button>
                <button class="tab-btn" data-tab="business">
                    <i class="fas fa-briefcase"></i>
                    Negocio
                </button>
                <button class="tab-btn" data-tab="additional">
                    <i class="fas fa-file-alt"></i>
                    Adicional
                </button>
                @if($ally->image_url)
                <button class="tab-btn" data-tab="media">
                    <i class="fas fa-image"></i>
                    Multimedia
                </button>
                @endif
            </div>

            {{-- Contenido de pestañas --}}
            <div class="tabs-content">
                {{-- Pestaña General --}}
                <div class="tab-pane active" id="general-tab">
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Nombre de la Empresa</span>
                            <span class="info-value highlight">{{ $ally->company_name }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">RIF</span>
                            <span class="info-value">{{ $ally->company_rif ?? 'No especificado' }}</span>
                        </div>
                        <div class="info-item full-width">
                            <span class="info-label">Dirección</span>
                            <span class="info-value">{{ $ally->company_address ?? 'No especificada' }}</span>
                        </div>
                        <div class="info-item full-width">
                            <span class="info-label">Sitio Web</span>
                            <span class="info-value">
                                @if($ally->website_url)
                                    <a href="{{ $ally->website_url }}" target="_blank" class="website-link">
                                        {{ $ally->website_url }}
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                @else
                                    <span class="text-muted">No especificado</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Pestaña Contacto --}}
                <div class="tab-pane" id="contact-tab">
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Persona de Contacto</span>
                            <span class="info-value">{{ $ally->contact_person_name ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email de Contacto</span>
                            <span class="info-value">
                                <a href="mailto:{{ $ally->contact_email }}" class="email-link">
                                    {{ $ally->contact_email }}
                                </a>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Teléfono Principal</span>
                            <span class="info-value">
                                <a href="tel:{{ $ally->contact_phone }}" class="phone-link">
                                    {{ $ally->contact_phone }}
                                </a>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Teléfono Alternativo</span>
                            <span class="info-value">
                                @if($ally->contact_phone_alt)
                                    <a href="tel:{{ $ally->contact_phone_alt }}" class="phone-link">
                                        {{ $ally->contact_phone_alt }}
                                    </a>
                                @else
                                    <span class="text-muted">No especificado</span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Pestaña Negocio --}}
                <div class="tab-pane" id="business-tab">
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Tipo de Aliado</span>
                            <span class="info-value">
                                <span class="badge badge-type-{{ strtolower($ally->businessType->name ?? 'sin-tipo') }}">
                                    {{ $ally->businessType->name ?? 'N/A' }}
                                </span>
                            </span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Categoría</span>
                            <span class="info-value">{{ $ally->category->name ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Subcategoría</span>
                            <span class="info-value">{{ $ally->subCategory->name ?? 'N/A' }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Descuento Ofrecido</span>
                            <span class="info-value discount-highlight">{{ $ally->discount ?? '0' }}%</span>
                        </div>
                    </div>
                </div>

                {{-- Pestaña Adicional --}}
                <div class="tab-pane" id="additional-tab">
                    <div class="info-grid">
                        <div class="info-item full-width">
                            <span class="info-label">Descripción del Negocio</span>
                            <div class="description-box">
                                {{ $ally->description ?? 'Sin descripción proporcionada.' }}
                            </div>
                        </div>
                        @if($ally->notes)
                        <div class="info-item full-width">
                            <span class="info-label">Notas Adicionales</span>
                            <div class="notes-box">
                                {{ $ally->notes }}
                            </div>
                        </div>
                        @endif
                        <div class="info-item">
                            <span class="info-label">Fecha de Registro</span>
                            <span class="info-value">{{ \Carbon\Carbon::parse($ally->registered_at ?? $ally->created_at)->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Última Actualización</span>
                            <span class="info-value">{{ $ally->updated_at->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Pestaña Multimedia --}}
                @if($ally->image_url)
                <div class="tab-pane" id="media-tab">
                    <div class="media-container">
                        <h4 class="media-title">
                            <i class="fas fa-image"></i>
                            Imagen del Aliado
                        </h4>
                        <div class="image-preview-wrapper">
                            <img src="{{ Storage::url($ally->image_url) }}" 
                                 alt="{{ $ally->company_name }}" 
                                 class="aliado-image">
                            <div class="image-actions">
                                <a href="{{ Storage::url($ally->image_url) }}" download class="image-action">
                                    <i class="fas fa-download"></i> Descargar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            {{-- Acciones rápidas --}}
            <div class="actions-section">
                <h3 class="actions-title">
                    <i class="fas fa-bolt"></i>
                    Acciones Rápidas
                </h3>
                <div class="actions-grid">
                    <a href="{{ route('admin.aliados.edit', $ally->id) }}" class="action-card edit">
                        <div class="action-icon">
                            <i class="fas fa-edit"></i>
                        </div>
                        <div class="action-content">
                            <span class="action-title">Editar Aliado</span>
                            <span class="action-desc">Modificar información</span>
                        </div>
                    </a>

                    <a href="{{ route('admin.aliados.index') }}" class="action-card back">
                        <div class="action-icon">
                            <i class="fas fa-list"></i>
                        </div>
                        <div class="action-content">
                            <span class="action-title">Ver Listado</span>
                            <span class="action-desc">Todos los aliados</span>
                        </div>
                    </a>

                    <form action="{{ route('admin.aliados.destroy', $ally->id) }}" method="POST" class="delete-form">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="action-card delete" onclick="return confirm('¿Estás seguro de eliminar este aliado? Esta acción no se puede deshacer.')">
                            <div class="action-icon">
                                <i class="fas fa-trash-alt"></i>
                            </div>
                            <div class="action-content">
                                <span class="action-title">Eliminar Aliado</span>
                                <span class="action-desc">Eliminar permanentemente</span>
                            </div>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ========== NAVEGACIÓN POR PESTAÑAS ==========
        const tabButtons = document.querySelectorAll('.tab-btn');
        const tabPanes = document.querySelectorAll('.tab-pane');

        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const targetTab = this.dataset.tab;
                
                // Remover clase activa de todos los botones
                tabButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                
                // Ocultar todos los paneles
                tabPanes.forEach(pane => pane.classList.remove('active'));
                
                // Mostrar el panel correspondiente
                document.getElementById(targetTab + '-tab').classList.add('active');
            });
        });

        // ========== ANIMACIONES DE ENTRADA ==========
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.stat-card, .info-item, .action-card').forEach(el => {
            el.style.opacity = '0';
            el.style.transform = 'translateY(20px)';
            el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            observer.observe(el);
        });

        // ========== EFECTOS HOVER EN TARJETAS ==========
        document.querySelectorAll('.stat-card, .action-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
            });
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        });
    });
</script>
@endpush