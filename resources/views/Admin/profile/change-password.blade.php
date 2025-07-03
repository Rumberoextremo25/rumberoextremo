@extends('layouts.admin')

@section('title', 'Cambiar Contraseña')

@section('page_title', 'Cambiar Contraseña')

@section('content')
    <div class="dashboard-container">
        <div class="profile-card"> {{-- Reutilizamos la clase de tarjeta para consistencia --}}
            <h3 class="card-title"><i class="fas fa-key me-2"></i> Cambiar Contraseña</h3>

            {{-- Mensajes de Éxito o Error --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                @method('PUT') {{-- Usamos PUT para actualizar un recurso existente --}}

                <div class="mb-4">
                    <label for="current_password" class="form-label">Contraseña Actual</label>
                    <input type="password" class="form-control @error('current_password') is-invalid @enderror"
                           id="current_password" name="current_password" required autofocus>
                    @error('current_password')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="new_password" class="form-label">Nueva Contraseña</label>
                    <input type="password" class="form-control @error('new_password') is-invalid @enderror"
                           id="new_password" name="new_password" required>
                    @error('new_password')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="new_password_confirmation" class="form-label">Confirmar Nueva Contraseña</label>
                    <input type="password" class="form-control" id="new_password_confirmation"
                           name="new_password_confirmation" required>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">Cambiar Contraseña</button>
                </div>
            </form>
        </div>
    </div>
@endsection

{{-- Asumiendo que tus estilos CSS para .profile-card, .form-label, .form-control, .btn, etc. ya están definidos en tu admin layout --}}