{{-- resources/views/Admin/partials/transacciones/detalle.blade.php --}}
<div class="transaction-detail">
    <div class="detail-header">
        <span class="detail-id">Transacción #{{ $transaccion->id }}</span>
        <span class="detail-date">{{ $transaccion->created_at->format('d/m/Y H:i:s') }}</span>
    </div>
    
    <div class="detail-sections">
        {{-- Información general --}}
        <div class="detail-section">
            <h3>Información General</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Referencia:</span>
                    <span class="detail-value">{{ $transaccion->reference }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Concepto:</span>
                    <span class="detail-value">{{ $transaccion->description }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Estado:</span>
                    <span class="status-badge status-{{ $transaccion->status }}">
                        @switch($transaccion->status)
                            @case('approved') ✅ Aprobada @break
                            @case('pending') ⏳ Pendiente @break
                            @case('rejected') ❌ Rechazada @break
                            @case('refunded') ↩️ Reembolsada @break
                            @default {{ $transaccion->status }}
                        @endswitch
                    </span>
                </div>
            </div>
        </div>
        
        {{-- Información del aliado (si es admin) --}}
        @if(auth()->user()->user_type === 'admin')
        <div class="detail-section">
            <h3>Aliado</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Nombre:</span>
                    <span class="detail-value">{{ $transaccion->aliado->name ?? 'N/A' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value">{{ $transaccion->aliado->email ?? 'N/A' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Teléfono:</span>
                    <span class="detail-value">{{ $transaccion->aliado->phone ?? 'N/A' }}</span>
                </div>
            </div>
        </div>
        @endif
        
        {{-- Información del usuario --}}
        <div class="detail-section">
            <h3>Usuario</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Nombre:</span>
                    <span class="detail-value">{{ $transaccion->user->name ?? 'N/A' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value">{{ $transaccion->user->email ?? 'N/A' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Teléfono:</span>
                    <span class="detail-value">{{ $transaccion->user->phone ?? 'N/A' }}</span>
                </div>
            </div>
        </div>
        
        {{-- Información financiera --}}
        <div class="detail-section">
            <h3>Detalles del Pago</h3>
            <div class="detail-grid financial">
                <div class="detail-item">
                    <span class="detail-label">Monto Bruto:</span>
                    <span class="detail-value amount">$ {{ number_format($transaccion->amount, 0, ',', '.') }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Comisión:</span>
                    <span class="detail-value commission">$ {{ number_format($transaccion->commission ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Monto Neto:</span>
                    <span class="detail-value neto">$ {{ number_format($transaccion->net_amount ?? $transaccion->amount, 0, ',', '.') }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Método de Pago:</span>
                    <span class="detail-value">
                        @switch($transaccion->payment_method)
                            @case('nequi') 📱 Nequi @break
                            @case('daviplata') 💳 Daviplata @break
                            @case('bancolombia') 🏦 Bancolombia @break
                            @case('efecty') 💵 Efecty @break
                            @default {{ $transaccion->payment_method ?? 'N/A' }}
                        @endswitch
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="detail-footer">
        <button class="btn-close" onclick="closeModal()">Cerrar</button>
        @if($transaccion->status == 'pending' && auth()->user()->user_type === 'admin')
        <div class="footer-actions">
            <button class="btn-approve" onclick="aprobarTransaccion({{ $transaccion->id }})">✅ Aprobar</button>
            <button class="btn-reject" onclick="rechazarTransaccion({{ $transaccion->id }})">❌ Rechazar</button>
        </div>
        @endif
    </div>
</div>

<style>
.transaction-detail {
    color: #fff;
}

.detail-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 15px;
    margin-bottom: 20px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.detail-id {
    font-size: 1.1rem;
    font-weight: 600;
    color: #A601B3;
}

.detail-date {
    font-size: 0.9rem;
    color: rgba(255,255,255,0.5);
}

.detail-section {
    margin-bottom: 25px;
}

.detail-section h3 {
    font-size: 1rem;
    color: rgba(255,255,255,0.7);
    margin-bottom: 15px;
    padding-bottom: 5px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.detail-label {
    font-size: 0.8rem;
    color: rgba(255,255,255,0.5);
    text-transform: uppercase;
}

.detail-value {
    font-size: 0.95rem;
    color: #fff;
    font-weight: 500;
}

.detail-value.amount {
    color: #00ff88;
    font-weight: 600;
}

.detail-value.commission {
    color: #ffaa00;
    font-weight: 600;
}

.detail-value.neto {
    color: #A601B3;
    font-weight: 600;
}

.status-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 500;
}

.status-approved {
    background: rgba(0, 255, 136, 0.15);
    color: #00ff88;
    border: 1px solid rgba(0, 255, 136, 0.3);
}

.status-pending {
    background: rgba(255, 170, 0, 0.15);
    color: #ffaa00;
    border: 1px solid rgba(255, 170, 0, 0.3);
}

.status-rejected {
    background: rgba(255, 68, 68, 0.15);
    color: #ff4444;
    border: 1px solid rgba(255, 68, 68, 0.3);
}

.detail-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 25px;
    padding-top: 15px;
    border-top: 1px solid rgba(255,255,255,0.1);
}

.btn-close {
    padding: 10px 25px;
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: 10px;
    color: #fff;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-close:hover {
    background: rgba(255,255,255,0.2);
}

.footer-actions {
    display: flex;
    gap: 10px;
}

.btn-approve,
.btn-reject {
    padding: 10px 20px;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-approve {
    background: linear-gradient(135deg, #00b09b, #96c93d);
    color: white;
}

.btn-approve:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 176, 155, 0.3);
}

.btn-reject {
    background: linear-gradient(135deg, #ff416c, #ff4b2b);
    color: white;
}

.btn-reject:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(255, 65, 108, 0.3);
}
</style>