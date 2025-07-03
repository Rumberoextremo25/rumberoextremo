@extends('layouts.admin')

@section('title', 'Configuración de Cuenta - Rumbero Extremo')
@section('page_title', 'Configuración de Cuenta')

@section('content')
    <div class="account-settings-container">
        <h1>Ajustes de la Cuenta</h1>

        <div class="settings-card">
            <div class="setting-item">
                <div class="info">
                    <h2>Notificaciones</h2>
                    <p>Recibe alertas sobre eventos, actualizaciones y promociones.</p>
                </div>
                <div class="actions">
                    <label class="modern-toggle-switch">
                        <input type="checkbox" id="notificationsToggle" {{ $notificationsEnabled ? 'checked' : '' }}>
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>

            <div class="setting-item">
                <div class="info">
                    <h2>Estilo de Pantalla</h2>
                    <p>Alterna entre el modo claro y el modo oscuro para una mejor visualización.</p>
                </div>
                <div class="actions">
                    <label class="modern-toggle-switch">
                        <input type="checkbox" id="darkModeToggle" {{ $darkModeEnabled ? 'checked' : '' }}>
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>

            {{-- Dentro de tu vista admin/settings.blade.php o en un modal --}}
            <form action="{{ route('admin.password.change') }}" method="POST">
                @csrf
                <h3>Cambiar Contraseña</h3>

                <div class="form-group">
                    <label for="current_password">Contraseña Actual:</label>
                    <input type="password" name="current_password" id="current_password" required class="form-control">
                    @error('current_password')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="new_password">Nueva Contraseña:</label>
                    <input type="password" name="new_password" id="new_password" required class="form-control">
                    @error('new_password')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="new_password_confirmation">Confirmar Nueva Contraseña:</label>
                    <input type="password" name="new_password_confirmation" id="new_password_confirmation" required
                        class="form-control">
                </div>

                <button type="submit" class="modern-button mt-3">Actualizar Contraseña</button>

                @if (session('success'))
                    <div class="alert alert-success mt-3">
                        {{ session('success') }}
                    </div>
                @endif
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Inicializar el modo oscuro basado en la preferencia del usuario o del sistema
            // Esto es importante para que el estilo se aplique al cargar la página si ya está activado
            function applyDarkMode(isEnabled) {
                if (isEnabled) {
                    document.body.classList.add('dark-mode');
                } else {
                    document.body.classList.remove('dark-mode');
                }
            }

            // Aplicar el estado inicial del modo oscuro
            const initialDarkModeState = document.getElementById('darkModeToggle').checked;
            applyDarkMode(initialDarkModeState);


            // --- Funcionalidad del Botón Cambiar Contraseña ---
            // Hemos cambiado el botón a un enlace para redirigir directamente
            // Si no usas 'password.request', ajusta la ruta a tu formulario de cambio de contraseña
            const changePasswordLink = document.querySelector('.modern-button[href*="password.request"]');
            if (changePasswordLink) {
                changePasswordLink.addEventListener('click', (event) => {
                    // Puedes añadir un console.log o un modal de confirmación antes de redirigir si lo deseas
                    console.log('Redirigiendo para cambiar contraseña...');
                });
            }

            // --- Funcionalidad del Toggle de Notificaciones ---
            const notificationsToggle = document.getElementById('notificationsToggle');
            if (notificationsToggle) {
                notificationsToggle.addEventListener('change', () => {
                    if (notificationsToggle.checked) {
                        alert('¡Notificaciones activadas! Te mantendremos al tanto de las novedades.');
                        // Aquí deberías enviar una solicitud AJAX a tu backend para guardar la preferencia.
                        // Ejemplo (requiere Axios o Fetch API):
                        // axios.post('/api/user/settings/notifications', { enabled: true })
                        //     .then(response => console.log('Notificaciones actualizadas en backend'))
                        //     .catch(error => console.error('Error al actualizar notificaciones', error));
                    } else {
                        alert('Notificaciones desactivadas. No recibirás más alertas por ahora.');
                        // Aquí deberías enviar una solicitud AJAX a tu backend para guardar la preferencia.
                        // Ejemplo:
                        // axios.post('/api/user/settings/notifications', { enabled: false })
                        //     .then(response => console.log('Notificaciones actualizadas en backend'))
                        //     .catch(error => console.error('Error al actualizar notificaciones', error));
                    }
                });
            }

            // --- Funcionalidad del Toggle de Estilo de Pantalla (Modo Oscuro) ---
            const darkModeToggle = document.getElementById('darkModeToggle');
            if (darkModeToggle) {
                darkModeToggle.addEventListener('change', () => {
                    const isDarkModeEnabled = darkModeToggle.checked;
                    applyDarkMode(isDarkModeEnabled); // Aplica la clase al body

                    if (isDarkModeEnabled) {
                        alert('¡Modo Oscuro activado! Disfruta de la nueva interfaz.');
                        // Aquí también enviarías la preferencia al backend para que se mantenga en futuras sesiones.
                        // Ejemplo:
                        // axios.post('/api/user/settings/dark-mode', { enabled: true })
                        //     .then(response => console.log('Modo oscuro actualizado en backend'))
                        //     .catch(error => console.error('Error al actualizar modo oscuro', error));
                    } else {
                        alert('Modo Claro activado.');
                        // Aquí enviarías la preferencia al backend.
                        // Ejemplo:
                        // axios.post('/api/user/settings/dark-mode', { enabled: false })
                        //     .then(response => console.log('Modo claro actualizado en backend'))
                        //     .catch(error => console.error('Error al actualizar modo oscuro', error));
                    }
                });
            }
        });
    </script>
@endsection

{{-- Estilos para darle un toque moderno (puedes moverlos a un archivo CSS) --}}
@section('styles')
    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color: #6c757d;
            --background-color: #f8f9fa;
            --card-background: #ffffff;
            --text-color: #343a40;
            --border-color: #e9ecef;
            --shadow-light: rgba(0, 0, 0, 0.08);
            --toggle-bg-off: #ccc;
            --toggle-bg-on: #007bff;
        }

        /* ESTILOS PARA MODO OSCURO */
        
    </style>
@endsection
