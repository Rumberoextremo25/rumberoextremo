@extends('layouts.admin')

@section('page_title_toolbar', 'Editar Pago')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="{{ asset('css/admin/payout.css') }}">
@endpush

@section('content')
    <div class="main-content">
        <h2 class="page-title text-gray-900">
            <span class="text-gray-900">Editar</span>
            <span class="text-purple">Pago #{{ $payout->id ?? $payout['id'] }}</span>
        </h2>

        {{-- Alertas --}}
        @if (session('success'))
            <div class="alert alert-success" role="alert">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
                <button type="button" class="close-alert">&times;</button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error" role="alert">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ session('error') }}</span>
                <button type="button" class="close-alert">&times;</button>
            </div>
        @endif

        <div class="edit-container">
            <div class="form-section">
                <form action="{{ route('admin.payouts.update', $payout->id ?? $payout['id']) }}" method="POST" class="edit-form">
                    @csrf
                    @method('PUT')

                    <div class="form-grid">
                        {{-- Información del Aliado --}}
                        <div class="form-card">
                            <div class="form-card-header">
                                <h4><i class="fas fa-user-friends"></i> Información del Aliado</h4>
                            </div>
                            <div class="form-card-body">
                                <div class="info-grid">
                                    <div class="info-item">
                                        <label>Nombre:</label>
                                        <span class="info-value">{{ $payout->aliado->nombre ?? $payout['aliado']['nombre'] }}</span>
                                    </div>
                                    <div class="info-item">
                                        <label>Email:</label>
                                        <span class="info-value">{{ $payout->aliado->email ?? $payout['aliado']['email'] }}</span>
                                    </div>
                                    <div class="info-item">
                                        <label>Cuenta Bancaria:</label>
                                        <span class="info-value">{{ $payout->aliado->cuenta_bancaria ?? $payout['aliado']['cuenta_bancaria'] }}</span>
                                    </div>
                                    <div class="info-item">
                                        <label>Banco:</label>
                                        <span class="info-value">{{ $payout->aliado->banco ?? $payout['aliado']['banco'] }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Información de la Venta --}}
                        <div class="form-card">
                            <div class="form-card-header">
                                <h4><i class="fas fa-shopping-cart"></i> Información de la Venta</h4>
                            </div>
                            <div class="form-card-body">
                                <div class="info-grid">
                                    <div class="info-item">
                                        <label>ID Venta:</label>
                                        <span class="info-value">#{{ $payout->venta->id ?? $payout['venta']['id'] }}</span>
                                    </div>
                                    <div class="info-item">
                                        <label>Monto Venta:</label>
                                        <span class="info-value text-success">Bs. {{ number_format($payout->montos->monto_venta ?? $payout['montos']['monto_venta'], 2, ',', '.') }}</span>
                                    </div>
                                    <div class="info-item">
                                        <label>Fecha Venta:</label>
                                        <span class="info-value">{{ \Carbon\Carbon::parse($payout->fechas->venta ?? $payout['fechas']['venta'])->format('d/m/Y H:i') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Campos Editables --}}
                        <div class="form-card">
                            <div class="form-card-header">
                                <h4><i class="fas fa-edit"></i> Editar Información del Pago</h4>
                            </div>
                            <div class="form-card-body">
                                <div class="form-group">
                                    <label for="monto_comision">Monto de Comisión (Bs.):</label>
                                    <input type="number" 
                                           name="monto_comision" 
                                           id="monto_comision"
                                           class="form-control"
                                           step="0.01"
                                           min="0"
                                           value="{{ old('monto_comision', $payout->montos->comision_monto ?? $payout['montos']['comision_monto']) }}"
                                           required>
                                    @error('monto_comision')
                                        <span class="error-message">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="estado">Estado del Pago:</label>
                                    <select name="estado" id="estado" class="form-control" required>
                                        <option value="pending" {{ (old('estado', $payout->estado ?? $payout['estado']) == 'pending') ? 'selected' : '' }}>Pendiente</option>
                                        <option value="processed" {{ (old('estado', $payout->estado ?? $payout['estado']) == 'processed') ? 'selected' : '' }}>Procesado</option>
                                        <option value="failed" {{ (old('estado', $payout->estado ?? $payout['estado']) == 'failed') ? 'selected' : '' }}>Fallido</option>
                                        <option value="reverted" {{ (old('estado', $payout->estado ?? $payout['estado']) == 'reverted') ? 'selected' : '' }}>Revertido</option>
                                    </select>
                                    @error('estado')
                                        <span class="error-message">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="observaciones">Observaciones:</label>
                                    <textarea name="observaciones" 
                                              id="observaciones" 
                                              class="form-control" 
                                              rows="4"
                                              placeholder="Agregar observaciones sobre el pago...">{{ old('observaciones', $payout->observaciones ?? $payout['observaciones'] ?? '') }}</textarea>
                                    @error('observaciones')
                                        <span class="error-message">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Resumen de Montos --}}
                        <div class="form-card">
                            <div class="form-card-header">
                                <h4><i class="fas fa-calculator"></i> Resumen de Montos</h4>
                            </div>
                            <div class="form-card-body">
                                <div class="amounts-grid">
                                    <div class="amount-item">
                                        <label>Monto Venta:</label>
                                        <span class="amount-value">Bs. {{ number_format($payout->montos->monto_venta ?? $payout['montos']['monto_venta'], 2, ',', '.') }}</span>
                                    </div>
                                    <div class="amount-item">
                                        <label>Descuento Aliado:</label>
                                        <span class="amount-value">{{ number_format($payout->montos->descuento_aliado ?? $payout['montos']['descuento_aliado'], 1) }}%</span>
                                    </div>
                                    <div class="amount-item">
                                        <label>Comisión:</label>
                                        <span class="amount-value">{{ number_format($payout->montos->comision_porcentaje ?? $payout['montos']['comision_porcentaje'], 1) }}%</span>
                                    </div>
                                    <div class="amount-item total">
                                        <label>Neto a Pagar:</label>
                                        <span class="amount-value text-success">Bs. {{ number_format($payout->montos->neto ?? $payout['montos']['neto'], 2, ',', '.') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Acciones --}}
                    <div class="form-actions">
                        <a href="{{ route('admin.payouts.show', $payout->id ?? $payout['id']) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                        <button type="button" class="btn btn-danger" onclick="confirmarReversion()">
                            <i class="fas fa-undo"></i> Revertir Pago
                        </button>
                    </div>
                </form>
            </div>

            {{-- Sidebar de Información --}}
            <div class="info-sidebar">
                <div class="info-card">
                    <h4>Información del Pago</h4>
                    <div class="info-list">
                        <div class="info-list-item">
                            <label>ID Pago:</label>
                            <span>#{{ $payout->id ?? $payout['id'] }}</span>
                        </div>
                        <div class="info-list-item">
                            <label>Fecha Generación:</label>
                            <span>{{ \Carbon\Carbon::parse($payout->fechas->generacion ?? $payout['fechas']['generacion'])->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="info-list-item">
                            <label>Estado Actual:</label>
                            <span class="status-badge status-{{ $payout->estado ?? $payout['estado'] }}">
                                {{ $payout->estado ?? $payout['estado'] }}
                            </span>
                        </div>
                        <div class="info-list-item">
                            <label>Última Actualización:</label>
                            <span>{{ \Carbon\Carbon::parse($payout->fechas->actualizacion ?? $payout['fechas']['actualizacion'])->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                </div>

                <div class="info-card">
                    <h4>Acciones Rápidas</h4>
                    <div class="action-links">
                        <a href="{{ route('admin.payouts.show', $payout->id ?? $payout['id']) }}" class="action-link">
                            <i class="fas fa-eye"></i> Ver Detalles
                        </a>
                        <a href="{{ route('admin.payouts.auditoria', $payout->id ?? $payout['id']) }}" class="action-link">
                            <i class="fas fa-history"></i> Ver Historial
                        </a>
                        <a href="{{ route('admin.payouts.por-aliado', $payout->aliado->id ?? $payout['aliado']['id']) }}" class="action-link">
                            <i class="fas fa-list"></i> Ver Todos los Pagos del Aliado
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de Confirmación de Reversión --}}
    <div id="reversionModal" class="modal-overlay hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Revertir Pago</h3>
                <button type="button" class="close-modal-btn">&times;</button>
            </div>
            <form action="{{ route('admin.payouts.revertir', $payout->id ?? $payout['id']) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>¿Estás seguro de que quieres revertir este pago?</p>
                    <div class="form-group">
                        <label for="motivo_reversion">Motivo de la reversión:</label>
                        <textarea name="motivo" id="motivo_reversion" class="form-control" rows="3" required placeholder="Especifica el motivo de la reversión..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn cancel-modal-btn">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Revertir Pago</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function confirmarReversion() {
        const modal = document.getElementById('reversionModal');
        modal.style.display = 'flex';
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Cerrar modales
        document.querySelectorAll('.close-modal-btn, .cancel-modal-btn').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('reversionModal').style.display = 'none';
            });
        });

        // Cerrar alertas
        document.querySelectorAll('.close-alert').forEach(button => {
            button.addEventListener('click', function() {
                this.parentElement.style.display = 'none';
            });
        });

        // Validación en tiempo real del monto de comisión
        const montoComisionInput = document.getElementById('monto_comision');
        const montoVenta = {{ $payout->montos->monto_venta ?? $payout['montos']['monto_venta'] }};
        
        montoComisionInput.addEventListener('change', function() {
            const comision = parseFloat(this.value);
            if (comision > montoVenta) {
                this.setCustomValidity('La comisión no puede ser mayor al monto de la venta');
            } else {
                this.setCustomValidity('');
            }
        });

        // Cerrar modal al hacer click fuera
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('reversionModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    });
</script>

<style>
    .edit-container {
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: 24px;
        align-items: start;
    }

    .form-section {
        grid-column: 1;
    }

    .info-sidebar {
        grid-column: 2;
        position: sticky;
        top: 20px;
    }

    .form-grid {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .form-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .form-card-header {
        background: var(--bg-light);
        padding: 16px 20px;
        border-bottom: 1px solid var(--border-color);
    }

    .form-card-header h4 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        color: var(--text-dark);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .form-card-body {
        padding: 20px;
    }

    .info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .info-item {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .info-item label {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
    }

    .info-value {
        font-weight: 500;
        color: var(--text-dark);
    }

    .amounts-grid {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .amount-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid var(--border-color);
    }

    .amount-item:last-child {
        border-bottom: none;
    }

    .amount-item.total {
        background: var(--bg-light);
        padding: 12px;
        border-radius: 6px;
        margin-top: 8px;
    }

    .amount-item label {
        font-weight: 500;
        color: var(--text-dark);
    }

    .amount-value {
        font-weight: 600;
    }

    .form-actions {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        margin-top: 24px;
        padding-top: 20px;
        border-top: 1px solid var(--border-color);
    }

    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s;
    }

    .btn-secondary {
        background: var(--bg-light);
        color: var(--text-dark);
        border: 1px solid var(--border-color);
    }

    .btn-secondary:hover {
        background: #e9ecef;
    }

    .btn-primary {
        background: var(--primary-color);
        color: white;
    }

    .btn-primary:hover {
        background: var(--primary-hover);
    }

    .btn-danger {
        background: var(--error-color);
        color: white;
    }

    .btn-danger:hover {
        background: #c82333;
    }

    .info-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        padding: 20px;
        margin-bottom: 20px;
    }

    .info-card h4 {
        margin: 0 0 16px 0;
        font-size: 16px;
        font-weight: 600;
        color: var(--text-dark);
    }

    .info-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .info-list-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .info-list-item label {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-muted);
    }

    .info-list-item span {
        font-weight: 500;
        color: var(--text-dark);
    }

    .status-badge {
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-pending {
        background: rgba(255, 193, 7, 0.1);
        color: var(--warning-color);
    }

    .status-processed {
        background: rgba(40, 167, 69, 0.1);
        color: var(--success-color);
    }

    .status-failed {
        background: rgba(220, 53, 69, 0.1);
        color: var(--error-color);
    }

    .status-reverted {
        background: rgba(108, 117, 125, 0.1);
        color: var(--text-muted);
    }

    .action-links {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .action-link {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        background: var(--bg-light);
        border-radius: 6px;
        text-decoration: none;
        color: var(--text-dark);
        font-size: 14px;
        transition: all 0.3s;
    }

    .action-link:hover {
        background: var(--primary-color);
        color: white;
        text-decoration: none;
    }

    .error-message {
        color: var(--error-color);
        font-size: 12px;
        margin-top: 4px;
        display: block;
    }

    @media (max-width: 1024px) {
        .edit-container {
            grid-template-columns: 1fr;
        }
        
        .info-sidebar {
            grid-column: 1;
            position: static;
        }
    }

    @media (max-width: 768px) {
        .info-grid {
            grid-template-columns: 1fr;
        }
        
        .form-actions {
            flex-direction: column;
        }
        
        .btn {
            justify-content: center;
        }
    }
</style>
@endpush