@extends('layouts.admin')

@section('title', 'Ver Aliado Comercial')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/commercial-ally-show.css') }}">
@endpush

@section('content')
<div class="ally-show-wrapper">
    {{-- HEADER CON GRADIENTE --}}
    <div class="ally-header">
        <div class="header-content">
            <div class="header-tag">
                <i class="fas fa-store"></i>
                <span>ALIADO COMERCIAL</span>
            </div>
            <h1 class="header-title">
                Detalles del <span class="gradient-text">Aliado</span>
            </h1>
            <p class="header-subtitle">
                <i class="fas fa-info-circle"></i>
                Información completa del aliado comercial
            </p>
        </div>
        <div class="header-actions">
            <a href="{{ route('admin.commercial-allies.index') }}" class="btn-back">
                <i class="fas fa-arrow-left"></i>
                Volver al Listado
            </a>
        </div>
    </div>

    {{-- ALERTAS --}}
    @if (session('success'))
        <div class="alert-modern success">
            <div class="alert-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="alert-content">
                <strong>¡Éxito!</strong>
                <p>{{ session('success') }}</p>
            </div>
            <button class="alert-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert-modern error">
            <div class="alert-icon">
                <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="alert-content">
                <strong>¡Error!</strong>
                <p>{{ session('error') }}</p>
            </div>
            <button class="alert-close" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    {{-- TARJETA DE INFORMACIÓN DEL ALIADO --}}
    <div class="ally-profile-card">
        <div class="profile-header">
            <div class="profile-avatar">
                @if($commercialAlly->logo_url)
                    <img src="{{ $commercialAlly->logo_url }}" alt="{{ $commercialAlly->name }}">
                @else
                    <div class="avatar-placeholder">
                        <i class="fas fa-store"></i>
                    </div>
                @endif
            </div>
            <div class="profile-title">
                <h2>{{ $commercialAlly->name }}</h2>
                <div class="profile-rating">
                    <div class="stars">
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= round($commercialAlly->rating))
                                <i class="fas fa-star star-filled"></i>
                            @else
                                <i class="far fa-star star-empty"></i>
                            @endif
                        @endfor
                    </div>
                    <span class="rating-value">{{ number_format($commercialAlly->rating, 1) }} / 5.0</span>
                </div>
            </div>
            <div class="profile-actions">
                <a href="{{ route('admin.commercial-allies.edit', $commercialAlly->id) }}" class="btn-edit">
                    <i class="fas fa-edit"></i>
                    Editar
                </a>
            </div>
        </div>

        <div class="profile-stats">
            <div class="stat-item">
                <div class="stat-icon purple">
                    <i class="fas fa-hashtag"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">ID</span>
                    <span class="stat-value">#{{ $commercialAlly->id }}</span>
                </div>
            </div>
            <div class="stat-item">
                <div class="stat-icon green">
                    <i class="fas fa-calendar-plus"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Creado</span>
                    <span class="stat-value">{{ $commercialAlly->created_at->format('d/m/Y') }}</span>
                </div>
            </div>
            <div class="stat-item">
                <div class="stat-icon orange">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Actualizado</span>
                    <span class="stat-value">{{ $commercialAlly->updated_at->diffForHumans() }}</span>
                </div>
            </div>
            <div class="stat-item">
                <div class="stat-icon red">
                    <i class="fas fa-link"></i>
                </div>
                <div class="stat-info">
                    <span class="stat-label">Website</span>
                    <span class="stat-value">{{ $commercialAlly->website_url ? 'Sí' : 'No' }}</span>
                </div>
            </div>
        </div>

        <div class="profile-details">
            {{-- Descripción --}}
            <div class="detail-section">
                <h3 class="section-title">
                    <i class="fas fa-align-left"></i>
                    Descripción
                </h3>
                <div class="detail-card">
                    @if($commercialAlly->description)
                        <p>{{ $commercialAlly->description }}</p>
                    @else
                        <p class="text-muted">No hay descripción disponible.</p>
                    @endif
                </div>
            </div>

            {{-- Información adicional --}}
            <div class="detail-section">
                <h3 class="section-title">
                    <i class="fas fa-info-circle"></i>
                    Información Adicional
                </h3>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Nombre:</span>
                        <span class="info-value">{{ $commercialAlly->name }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Rating:</span>
                        <span class="info-value">
                            <div class="stars small">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= round($commercialAlly->rating))
                                        <i class="fas fa-star star-filled"></i>
                                    @else
                                        <i class="far fa-star star-empty"></i>
                                    @endif
                                @endfor
                            </div>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Website:</span>
                        <span class="info-value">
                            @if($commercialAlly->website_url)
                                <a href="{{ $commercialAlly->website_url }}" target="_blank" class="website-link">
                                    {{ $commercialAlly->website_url }}
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            @else
                                No disponible
                            @endif
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Fecha creación:</span>
                        <span class="info-value">{{ $commercialAlly->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Última actualización:</span>
                        <span class="info-value">{{ $commercialAlly->updated_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>

            {{-- Logo --}}
            @if($commercialAlly->logo_url)
                <div class="detail-section">
                    <h3 class="section-title">
                        <i class="fas fa-image"></i>
                        Logo
                    </h3>
                    <div class="logo-preview-card">
                        <img src="{{ asset('storage/' . $commercialAlly->logo_url) }}" class="logo-full">
                    </div>
                </div>
            @endif
        </div>

        {{-- BOTONES DE ACCIÓN --}}
        <div class="profile-footer">
            <a href="{{ route('admin.commercial-allies.index') }}" class="btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Volver al Listado
            </a>
            <div class="footer-actions">
                <a href="{{ route('admin.commercial-allies.edit', $commercialAlly->id) }}" class="btn-edit">
                    <i class="fas fa-edit"></i>
                    Editar Aliado
                </a>
                <button type="button" class="btn-delete" onclick="confirmDelete({{ $commercialAlly->id }}, '{{ $commercialAlly->name }}')">
                    <i class="fas fa-trash-alt"></i>
                    Eliminar
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL DE CONFIRMACIÓN DE ELIMINACIÓN --}}
    <div class="modal-modern" id="deleteModal">
        <div class="modal-card" style="max-width: 450px;">
            <div class="modal-icon warning">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3 class="modal-title">Confirmar Eliminación</h3>
            <div class="modal-body">
                <p>¿Estás seguro de que quieres eliminar el aliado comercial:</p>
                <p class="modal-highlight" id="deleteAllyTitle"></p>
                <p class="modal-warning">
                    <i class="fas fa-exclamation-circle"></i>
                    Esta acción no se puede deshacer.
                </p>
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-secondary" onclick="closeDeleteModal()">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>
                <button type="button" class="btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash-alt"></i>
                    Eliminar
                </button>
            </div>
        </div>
    </div>

    {{-- FORMULARIO OCULTO PARA ELIMINAR --}}
    <form action="{{ route('admin.commercial-allies.destroy', $commercialAlly->id) }}" 
          method="POST" 
          id="delete-form-{{ $commercialAlly->id }}" 
          style="display: none;">
        @csrf
        @method('DELETE')
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-cerrar alertas
        document.querySelectorAll('.alert-modern').forEach(alert => {
            setTimeout(() => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        });
    });

    // Funciones para eliminación
    let deleteId = null;

    function confirmDelete(id, title) {
        deleteId = id;
        document.getElementById('deleteAllyTitle').textContent = `"${title}"`;
        document.getElementById('deleteModal').classList.add('active');
        document.body.style.overflow = 'hidden';
        
        document.getElementById('confirmDeleteBtn').onclick = function() {
            document.getElementById(`delete-form-${deleteId}`).submit();
        };
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.remove('active');
        document.body.style.overflow = '';
        deleteId = null;
    }

    // Cerrar modal con Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDeleteModal();
        }
    });

    // Cerrar modal al hacer clic fuera
    window.onclick = function(event) {
        const modal = document.getElementById('deleteModal');
        if (event.target === modal) {
            closeDeleteModal();
        }
    };
</script>
@endpush
@endsection