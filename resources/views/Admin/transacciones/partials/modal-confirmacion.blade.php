{{-- resources/views/transacciones/partials/modal-confirmacion.blade.php --}}
<div class="modal" id="confirmModal">
    <div class="modal-content modal-small">
        <div class="modal-header">
            <h2>Confirmar Acción</h2>
            <button class="modal-close" onclick="closeConfirmModal()">&times;</button>
        </div>
        <div class="modal-body" id="confirmMessage">
            ¿Estás seguro de realizar esta acción?
        </div>
        <div class="modal-footer">
            <button class="btn-modal cancel" onclick="closeConfirmModal()">Cancelar</button>
            <button class="btn-modal confirm" id="confirmActionBtn">Confirmar</button>
        </div>
    </div>
</div>