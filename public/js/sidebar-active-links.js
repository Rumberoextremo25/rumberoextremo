document.addEventListener('DOMContentLoaded', () => {
    const sidebarLinks = document.querySelectorAll('.sidebar ul li a');
    // --- IMPORTANTE: Usamos la variable definida en el Blade ---
    const currentRouteName = LARAVEL_CURRENT_ROUTE_NAME; 

    sidebarLinks.forEach(link => {
        link.classList.remove('active');

        const href = link.getAttribute('href');
        let linkIsActive = false;

        // 1. Manejo específico para el enlace de Cerrar Sesión
        // Este link es dinámico en Blade, por lo que su valor se pasa correctamente.
        if (href === '{{ route('logout') }}') { 
            return;
        }

        // 2. Coincidencia por nombre de ruta exacto
        // Obtenemos la última parte del href (ej. "dashboard" de "http://.../dashboard")
        const lastSegmentOfHref = href.split('/').pop().split('?')[0]; // También maneja query parameters
        
        if (lastSegmentOfHref === currentRouteName) {
            linkIsActive = true;
        }

        // Una forma más robusta de verificar rutas exactas que no sean recursos:
        // Verifica si la ruta actual es exactamente el nombre del enlace (ej. 'dashboard' para el enlace de dashboard)
        // Puedes añadir data-attributes a tus enlaces si sus nombres de ruta son muy diferentes a su URL final
        // Ejemplo: <a href="/dashboard" data-route-name="dashboard">
        const linkRouteName = link.dataset.routeName; // Si decides usar un data-route-name
        if (linkRouteName && linkRouteName === currentRouteName) {
            linkIsActive = true;
        }


        // 3. Coincidencia para rutas de recursos o rutas con prefijo (ej. 'aliado.*', 'admin.banners.*')
        const dataRoutePattern = link.dataset.routePattern;
        if (dataRoutePattern) {
            const patternPrefix = dataRoutePattern.replace('*', '');
            if (currentRouteName.startsWith(patternPrefix)) {
                linkIsActive = true;
            }
        }

        // 4. Fallback: Coincidencia por inclusión de URL (si la URL actual contiene la URL del enlace)
        // Solo como última opción, ya que puede dar falsos positivos.
        // Asegúrate de que el href no sea vacío y que window.location.href incluya la ruta absoluta del href.
        if (!linkIsActive && href && window.location.href.includes(href)) {
             linkIsActive = true;
        }
        
        if (linkIsActive) {
            link.classList.add('active');
        }
    });
});