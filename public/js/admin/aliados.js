// resources/js/admin/aliados/index.js

document.addEventListener('DOMContentLoaded', () => {
    console.log('Allies management scripts loaded.');

    // Lógica para la barra lateral (asumiendo que es manejada por el layout admin)
    const sidebarLinks = document.querySelectorAll('.sidebar ul li a');
    sidebarLinks.forEach(link => {
        link.classList.remove('active');
        if (window.location.pathname.includes('/admin/aliados')) {
            if (link.getAttribute('href') && link.getAttribute('href').includes('/admin/aliados')) {
                link.classList.add('active');
            }
        }
    });

    // Lógica de búsqueda en la tabla (adaptada a las columnas reducidas)
    const searchInput = document.getElementById('allySearch');
    const tableBody = document.querySelector('.allies-table tbody');
    // Captura las filas iniciales, excluyendo el mensaje "no-records-message" si existe
    const initialAllyRows = Array.from(tableBody.querySelectorAll('tr:not(.no-records-message)'));

    searchInput.addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase().trim();
        let resultsFound = false;
        let noRecordsMessageRow = tableBody.querySelector('.no-records-message');

        // Si no hay filas iniciales y la búsqueda está vacía, muestra el mensaje original
        if (initialAllyRows.length === 0 && noRecordsMessageRow && searchTerm === '') {
            noRecordsMessageRow.style.display = '';
            noRecordsMessageRow.querySelector('td').textContent = 'No hay aliados registrados en este momento.';
            return; // Salir de la función
        }

        initialAllyRows.forEach(row => {
            // Columnas visibles: ID, Nombre, RIF, Categoría, Subcategoría, Descuento, Contacto, Email, Estado
            // Ajusta los índices de `children[]` según el nuevo orden de las celdas <td>
            const id = row.children[0]?.textContent.toLowerCase() || '';
            const name = row.children[1]?.textContent.toLowerCase() || '';
            const rif = row.children[2]?.textContent.toLowerCase() || '';
            const category = row.children[3]?.textContent.toLowerCase() || ''; // Nueva columna
            const subcategory = row.children[4]?.textContent.toLowerCase() || ''; // Nueva columna
            const discount = row.children[5]?.textContent.toLowerCase() || ''; // Nueva columna
            const contact = row.children[6]?.textContent.toLowerCase() || '';
            const email = row.children[7]?.textContent.toLowerCase() || '';
            const status = row.children[8]?.querySelector('.status-badge')?.textContent.toLowerCase() || '';

            const rowText = `${id} ${name} ${rif} ${category} ${subcategory} ${discount} ${contact} ${email} ${status}`;

            if (rowText.includes(searchTerm)) {
                row.style.display = '';
                resultsFound = true;
            } else {
                row.style.display = 'none';
            }
        });

        // Manejar el mensaje "no hay registros"
        if (!noRecordsMessageRow) {
            noRecordsMessageRow = document.createElement('tr');
            noRecordsMessageRow.classList.add('no-records-message');
            const td = document.createElement('td');
            // COLSPAN AJUSTADO A 10
            td.setAttribute('colspan', '10');
            noRecordsMessageRow.appendChild(td);
            tableBody.appendChild(noRecordsMessageRow);
        }

        if (!resultsFound && searchTerm !== '') {
            noRecordsMessageRow.style.display = '';
            noRecordsMessageRow.querySelector('td').textContent =
                'No se encontraron aliados que coincidan con la búsqueda.';
        } else if (initialAllyRows.length === 0 && searchTerm === '') {
            // Si no hay aliados en la base de datos y la búsqueda está vacía
            noRecordsMessageRow.style.display = '';
            noRecordsMessageRow.querySelector('td').textContent =
                'No hay aliados registrados en este momento.';
        }
        else {
            noRecordsMessageRow.style.display = 'none';
        }
    });

    // Disparar el evento keyup al cargar la página para inicializar la tabla correctamente
    searchInput.dispatchEvent(new Event('keyup'));
});