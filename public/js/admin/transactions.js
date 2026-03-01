// transactions.js - Funcionalidades para la vista de transacciones

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar event listeners
    initEventListeners();
    
    // Establecer valores de filtros desde URL
    setFiltersFromUrl();
});

// Inicializar todos los event listeners
function initEventListeners() {
    // Selector de aliado para admin
    const aliadoSelector = document.getElementById('aliadoSelector');
    if (aliadoSelector) {
        aliadoSelector.addEventListener('change', function() {
            const aliadoId = this.value;
            const url = new URL(window.location.href);

            if (aliadoId) {
                url.searchParams.set('aliado_id', aliadoId);
            } else {
                url.searchParams.delete('aliado_id');
            }

            window.location.href = url.toString();
        });
    }

    // Aplicar filtros
    const applyFiltersBtn = document.getElementById('applyFilters');
    if (applyFiltersBtn) {
        applyFiltersBtn.addEventListener('click', function() {
            const date = document.getElementById('filterDate').value;
            const status = document.getElementById('filterStatus').value;
            const url = new URL(window.location.href);

            if (date !== 'all') {
                url.searchParams.set('fecha', date);
            } else {
                url.searchParams.delete('fecha');
            }

            if (status !== 'all') {
                url.searchParams.set('estado', status);
            } else {
                url.searchParams.delete('estado');
            }

            window.location.href = url.toString();
        });
    }

    // Búsqueda en tiempo real
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('keyup', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('.table-row');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    }

    // Confirmar acción
    const confirmActionBtn = document.getElementById('confirmActionBtn');
    if (confirmActionBtn) {
        confirmActionBtn.addEventListener('click', function() {
            if (!window.currentTransactionId || !window.currentAction) return;

            const url = window.currentAction === 'approve' ?
                `/transacciones/${window.currentTransactionId}/aprobar` :
                `/transacciones/${window.currentTransactionId}/rechazar`;

            fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message || 'Error al procesar la solicitud');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al procesar la solicitud');
                })
                .finally(() => {
                    closeConfirmModal();
                });
        });
    }

    // Cerrar modales al hacer clic fuera
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('transactionModal');
        const confirmModal = document.getElementById('confirmModal');

        if (event.target == modal) {
            closeModal();
        }
        if (event.target == confirmModal) {
            closeConfirmModal();
        }
    });
}

// Variables globales
window.currentTransactionId = null;
window.currentAction = null;

// Ver detalle de transacción
window.verDetalle = function(id) {
    console.log('Ver detalle de transacción:', id);

    // Mostrar modal con spinner
    document.getElementById('transactionDetails').innerHTML = `
        <div class="loading-spinner">
            <div class="spinner"></div>
            <p>Cargando detalles...</p>
        </div>
    `;
    document.getElementById('transactionModal').style.display = 'flex';

    // Hacer la petición AJAX
    fetch(`/transacciones/${id}/detalle`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderTransactionDetail(data.transaccion);
            } else {
                showError(data.message || 'Error al cargar los detalles');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Error al cargar los detalles');
        });
};

// Renderizar detalle de transacción
function renderTransactionDetail(t) {
    const html = `
        <div class="transaction-detail">
            <div class="detail-header">
                <span class="detail-id">Transacción #${t.id}</span>
                <span class="detail-date">${new Date(t.created_at).toLocaleString('es-ES')}</span>
            </div>
            <div class="detail-sections">
                <div class="detail-section">
                    <h3>Información General</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Código de Referencia:</span>
                            <span class="detail-value">${t.reference_code}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Estado:</span>
                            <span class="status-badge status-${t.status}">${getStatusText(t.status)}</span>
                        </div>
                    </div>
                </div>
                <div class="detail-section">
                    <h3>Usuario</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Nombre:</span>
                            <span class="detail-value">${t.user ? t.user.name : 'N/A'}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Email:</span>
                            <span class="detail-value">${t.user ? t.user.email : 'N/A'}</span>
                        </div>
                    </div>
                </div>
                <div class="detail-section">
                    <h3>Detalles del Pago</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Monto Original:</span>
                            <span class="detail-value amount">$ ${formatMoney(t.original_amount)}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Descuento:</span>
                            <span class="detail-value">${t.discount_percentage}%</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Comisión:</span>
                            <span class="detail-value commission">$ ${formatMoney(t.platform_commission)}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Neto:</span>
                            <span class="detail-value neto">$ ${formatMoney(t.amount_to_ally)}</span>
                        </div>
                        <div class="detail-item full-width">
                            <span class="detail-label">Método de Pago:</span>
                            <span class="detail-value">${formatPaymentMethod(t.payment_method)}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="detail-footer">
                <button class="btn-close" onclick="closeModal()">Cerrar</button>
            </div>
        </div>
    `;
    document.getElementById('transactionDetails').innerHTML = html;
}

// Mostrar error en el modal
function showError(message) {
    document.getElementById('transactionDetails').innerHTML = `
        <div style="color: #ef4444; text-align: center; padding: 20px;">
            <p>${message}</p>
        </div>
    `;
}

// Aprobar transacción
window.aprobarTransaccion = function(id) {
    window.currentTransactionId = id;
    window.currentAction = 'approve';
    document.getElementById('confirmMessage').innerHTML = '¿Estás seguro de confirmar esta transacción?';
    document.getElementById('confirmModal').style.display = 'flex';
};

// Rechazar transacción
window.rechazarTransaccion = function(id) {
    window.currentTransactionId = id;
    window.currentAction = 'reject';
    document.getElementById('confirmMessage').innerHTML = '¿Estás seguro de rechazar esta transacción?';
    document.getElementById('confirmModal').style.display = 'flex';
};

// Imprimir comprobante
window.imprimirComprobante = function(id) {
    window.open(`/transacciones/${id}/comprobante`, '_blank');
};

// Cerrar modales
window.closeModal = function() {
    document.getElementById('transactionModal').style.display = 'none';
};

window.closeConfirmModal = function() {
    document.getElementById('confirmModal').style.display = 'none';
    window.currentTransactionId = null;
    window.currentAction = null;
};

// Funciones auxiliares
function formatMoney(amount) {
    return new Intl.NumberFormat('es-CO', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount);
}

function getStatusText(status) {
    const statusMap = {
        'confirmed': '✅ Confirmada',
        'awaiting_review': '⏳ En Revisión',
        'pending_manual_confirmation': '⌛ Pendiente',
        'failed': '❌ Fallida'
    };
    return statusMap[status] || status;
}

function formatPaymentMethod(method) {
    const methodMap = {
        'pago_movil': '📱 Pago Móvil',
        'transferencia_bancaria': '🏦 Transferencia'
    };
    return methodMap[method] || method;
}

// Establecer valores de filtros desde URL
function setFiltersFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);

    const fecha = urlParams.get('fecha');
    if (fecha) {
        const filterDate = document.getElementById('filterDate');
        if (filterDate) filterDate.value = fecha;
    }

    const estado = urlParams.get('estado');
    if (estado) {
        const filterStatus = document.getElementById('filterStatus');
        if (filterStatus) filterStatus.value = estado;
    }
}