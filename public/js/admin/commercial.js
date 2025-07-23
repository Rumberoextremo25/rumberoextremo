// resources/js/admin/commercial-allies/index.js

document.addEventListener('DOMContentLoaded', () => {
    console.log('Commercial allies management scripts loaded.');

    // Lógica para la barra lateral (asumiendo que es manejada por el layout admin)
    const sidebarLinks = document.querySelectorAll('.sidebar ul li a');
    sidebarLinks.forEach(link => {
        link.classList.remove('active');
        if (window.location.pathname.includes('/admin/commercial-allies')) {
            if (link.getAttribute('href') && link.getAttribute('href').includes('/admin/commercial-allies')) {
                link.classList.add('active');
            }
        }
    });

    // --- Lógica del Modal de Confirmación de Eliminación ---
    const confirmationModal = document.getElementById('confirmationModal');
    const allyNameToDeleteSpan = document.getElementById('allyNameToDelete');
    const confirmModalBtn = document.querySelector('.modal-footer .confirm-modal-btn');
    const cancelModalBtn = document.querySelector('.modal-footer .cancel-modal-btn');
    const closeModalBtn = document.querySelector('.modal-header .close-modal-btn');

    let formToSubmit = null; // Variable para guardar la referencia al formulario a enviar

    // Abre el modal al hacer clic en el botón de eliminar
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function(event) {
            event.preventDefault(); // Previene el envío del formulario por defecto
            formToSubmit = this; // Guarda la referencia al formulario actual

            const allyName = this.dataset.allyName || 'este aliado';
            allyNameToDeleteSpan.textContent = allyName; // Actualiza el nombre en el modal

            confirmationModal.classList.add('show'); // Muestra el modal
        });
    });

    // Cierra el modal
    function hideModal() {
        confirmationModal.classList.remove('show');
        formToSubmit = null; // Limpia la referencia al formulario
    }

    cancelModalBtn.addEventListener('click', hideModal);
    closeModalBtn.addEventListener('click', hideModal);

    // Envía el formulario si se confirma la eliminación
    confirmModalBtn.addEventListener('click', () => {
        if (formToSubmit) {
            formToSubmit.submit(); // Envía el formulario
        }
        hideModal(); // Cierra el modal
    });

    // Cierra el modal si se hace clic fuera de él
    confirmationModal.addEventListener('click', (event) => {
        if (event.target === confirmationModal) {
            hideModal();
        }
    });

    // Cierra el modal con la tecla ESC
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && confirmationModal.classList.contains('show')) {
            hideModal();
        }
    });
});