@extends('layouts.admin')

@section('title', 'Notificaciones')

@section('page_title_toolbar', 'Notificaciones')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/pages.css') }}">
@endpush

@section('content')
    <div class="notifications-page">

        <div class="page-header-block">
            <div>
                <h1 class="title-main">Notificaciones <span class="title-accent">del panel</span></h1>
                <p class="page-subtitle">
                    Historial de cada creación, edición y eliminación de usuarios, aliados, promociones y banners.
                </p>
            </div>
            <div class="header-actions">
                @if ($unread > 0)
                    <span class="badge badge-primary">{{ $unread }} sin leer</span>
                @endif
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary btn-sm">
                    <i class="fa-solid fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3><i class="fa-regular fa-bell"></i> Actividad reciente</h3>
                <span class="badge badge-neutral">{{ $notifications->total() }} eventos</span>
            </div>

            @forelse ($notifications as $notification)
                @php $data = $notification->data; @endphp
                <a href="{{ $data['url'] ?? '#' }}"
                    class="notification-row {{ $notification->read_at ? '' : 'is-unread' }}">
                    <span class="notification-icon {{ $data['color'] ?? 'info' }}">
                        <i class="{{ $data['icon'] ?? 'fa-solid fa-bell' }}" aria-hidden="true"></i>
                    </span>

                    <span class="notification-body">
                        <span class="notification-title">{{ $data['title'] ?? 'Actividad' }}</span>
                        <span class="notification-desc">{{ $data['message'] ?? '' }}</span>

                        @if (! empty($data['changes']))
                            <span class="notification-changes">
                                @foreach (array_slice($data['changes'], 0, 4) as $change)
                                    <span class="notification-change">
                                        <strong>{{ $change['label'] }}:</strong>
                                        {{ $change['from'] }} <i class="fa-solid fa-arrow-right"></i> {{ $change['to'] }}
                                    </span>
                                @endforeach
                            </span>
                        @endif
                    </span>

                    <span class="notification-date">
                        {{ $notification->created_at?->format('d/m/Y') }}
                        <small>{{ $notification->created_at?->format('H:i') }}</small>
                    </span>
                </a>
            @empty
                <div class="empty-state">
                    <i class="fa-regular fa-bell-slash"></i>
                    <h3>Todavía no hay actividad</h3>
                    <p>Crea o edita un usuario, aliado, promoción o banner y aparecerá aquí al instante.</p>
                </div>
            @endforelse

            @if ($notifications->hasPages())
                <div class="card-footer">
                    {{-- Vista de paginación clásica: encaja con el CSS del panel --}}
                    {{ $notifications->onEachSide(1)->links('pagination::default') }}
                </div>
            @endif
        </div>
    </div>
@endsection
