// Archivo js/dashboard.js
// Este script se encarga de la funcionalidad interactiva del dashboard,
// especialmente la gestión del sidebar en pantallas pequeñas.

document.addEventListener('DOMContentLoaded', () => {
    // Seleccionamos el sidebar y el contenido principal.
    const sidebar = document.querySelector('.admin-sidebar');
    const mainContent = document.querySelector('.main-content');
    
    // Suponemos que hay un botón para alternar el sidebar en pantallas móviles.
    // El botón debe tener la clase 'sidebar-toggle-btn'.
    const sidebarToggleBtn = document.querySelector('.sidebar-toggle-btn');

    // Función para manejar el clic en el botón de toggle del sidebar
    if (sidebarToggleBtn && sidebar) {
        sidebarToggleBtn.addEventListener('click', () => {
            // Alterna la clase 'open' en el sidebar para mostrarlo/ocultarlo
            sidebar.classList.toggle('open');
        });
    }

    // Función para cerrar el sidebar cuando se hace clic fuera de él en móviles
    // Esto es útil para una mejor experiencia de usuario.
    document.body.addEventListener('click', (e) => {
        // Si el sidebar está abierto y el clic no fue en el sidebar ni en el botón de toggle,
        // entonces cerramos el sidebar.
        if (sidebar && sidebar.classList.contains('open') && 
            !sidebar.contains(e.target) && 
            (!sidebarToggleBtn || !sidebarToggleBtn.contains(e.target))) {
            sidebar.classList.remove('open');
        }
    });

    // Función para asegurar que el sidebar esté abierto por defecto en escritorios
    function handleResize() {
        if (window.innerWidth > 992) {
            // En pantallas de escritorio, nos aseguramos de que el sidebar esté visible
            // y que el main-content tenga el margen correcto.
            if (sidebar) {
                sidebar.classList.remove('open');
            }
            if (mainContent) {
                mainContent.style.marginLeft = 'var(--sidebar-width)';
            }
        } else {
            // En móviles, el sidebar se oculta y el main-content no tiene margen
            if (mainContent) {
                mainContent.style.marginLeft = '0';
            }
        }
    }

    // Escuchamos el evento de redimensionamiento de la ventana
    window.addEventListener('resize', handleResize);
    
    // Llamamos a la función al cargar la página para establecer el estado inicial
    handleResize();
});