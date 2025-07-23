// resources/js/admin/banners/index.js

document.addEventListener('DOMContentLoaded', () => {
    console.log('Banner management scripts loaded.');

    // Aquí puedes añadir lógica específica para la gestión de banners si es necesario.
    // Por ejemplo, para la barra lateral, si es que los banners tienen una entrada en ella:
    const sidebarLinks = document.querySelectorAll('.sidebar ul li a');
    sidebarLinks.forEach(link => {
        link.classList.remove('active');
        if (window.location.pathname.includes('/admin/banners')) {
            if (link.getAttribute('href') && link.getAttribute('href').includes('/admin/banners')) {
                link.classList.add('active');
            }
        }
    });

    // Si necesitas una funcionalidad de búsqueda similar a la de aliados, puedes adaptarla aquí.
    // Por ahora, el script está vacío ya que la vista no incluye una barra de búsqueda.
});