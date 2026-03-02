<div class="transaction-detail-modal">
    {{-- Información básica - Siempre visible --}}
    <div class="detail-section">
        <h4><i class="fa-solid fa-circle-info"></i> Información Básica</h4>
        <div class="info-grid">
            <div class="info-item">
                <span class="label">ID:</span>
                <span class="value">#{{ $transaccion->id }}</span>
            </div>
            <div class="info-item">
                <span class="label">Referencia:</span>
                <span class="value"><code>{{ $transaccion->reference_code }}</code></span>
            </div>
            <div class="info-item">
                <span class="label">Fecha:</span>
                <span class="value">{{ $transaccion->created_at->format('d/m/Y H:i') }}</span>
            </div>
            <div class="info-item">
                <span class="label">Estado:</span>
                <span class="value">
                    <span class="status-badge status-{{ $transaccion->status }}">
                        @switch($transaccion->status)
                            @case('confirmed') ✅ Confirmada @break
                            @case('awaiting_review') ⏳ En Revisión @break
                            @case('pending_manual_confirmation') ⌛ Pendiente @break
                            @case('failed') ❌ Fallida @break
                            @default {{ $transaccion->status }}
                        @endswitch
                    </span>
                </span>
            </div>
        </div>
    </div>

    {{-- Montos - Siempre visible --}}
    <div class="detail-section">
        <h4><i class="fa-solid fa-coins"></i> Montos</h4>
        <div class="amounts-grid">
            <div class="amount-item">
                <span class="label">Original:</span>
                <span class="value original">${{ number_format($transaccion->original_amount, 0, ',', '.') }}</span>
            </div>
            <div class="amount-item">
                <span class="label">Descuento:</span>
                <span class="value">{{ $transaccion->discount_percentage }}%</span>
            </div>
            <div class="amount-item">
                <span class="label">Comisión:</span>
                <span class="value commission">${{ number_format($transaccion->platform_commission, 0, ',', '.') }}</span>
            </div>
            <div class="amount-item">
                <span class="label">Neto:</span>
                <span class="value net">${{ number_format($transaccion->amount_to_ally, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    {{-- Usuarios - Siempre visible --}}
    <div class="detail-section">
        <h4><i class="fa-solid fa-users"></i> Participantes</h4>
        <div class="participants-grid">
            <div class="participant">
                <span class="participant-label">Usuario:</span>
                <span class="participant-name">{{ $transaccion->user->name ?? 'N/A' }}</span>
                <span class="participant-email">{{ $transaccion->user->email ?? '' }}</span>
            </div>
            <div class="participant">
                <span class="participant-label">Aliado:</span>
                <span class="participant-name">{{ $transaccion->ally->name ?? 'N/A' }}</span>
                <span class="participant-email">{{ $transaccion->ally->email ?? '' }}</span>
            </div>
        </div>
    </div>

    {{-- Datos de confirmación - Ocultos por defecto --}}
    @if($transaccion->confirmation_data)
    <div class="detail-section">
        <div class="section-header" onclick="toggleConfirmationData()">
            <h4><i class="fa-solid fa-circle-check"></i> Datos de Confirmación</h4>
            <i class="fa-solid fa-chevron-down" id="toggleIcon"></i>
        </div>
        <div id="confirmationData" style="display: none;">
            <pre class="json-data">{{ json_encode($transaccion->confirmation_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
    </div>
    @endif
</div>

<style>
.transaction-detail-modal {
    padding: 0.5rem;
}

.detail-section {
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e9eef2;
}

.detail-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.detail-section h4 {
    font-size: 0.95rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.detail-section h4 i {
    color: #A601B3;
    font-size: 1rem;
}

.info-grid, .amounts-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
}

.info-item, .amount-item {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.info-item .label, .amount-item .label {
    font-size: 0.75rem;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.info-item .value {
    font-size: 0.9rem;
    color: #1e293b;
    font-weight: 500;
}

.amount-item .value.original {
    color: #10b981;
    font-weight: 600;
}

.amount-item .value.commission {
    color: #f59e0b;
    font-weight: 600;
}

.amount-item .value.net {
    color: #A601B3;
    font-weight: 700;
}

.participants-grid {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.participant {
    background: #f8fafc;
    padding: 0.8rem;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
}

.participant-label {
    font-size: 0.7rem;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.participant-name {
    font-size: 0.95rem;
    font-weight: 600;
    color: #1e293b;
}

.participant-email {
    font-size: 0.8rem;
    color: #64748b;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    padding: 0.5rem 0;
}

.section-header:hover h4 {
    color: #A601B3;
}

.section-header:hover i {
    color: #A601B3;
}

.json-data {
    background: #1e293b;
    color: #e2e8f0;
    padding: 1rem;
    border-radius: 8px;
    font-family: 'Courier New', monospace;
    font-size: 0.75rem;
    line-height: 1.4;
    overflow-x: auto;
    white-space: pre-wrap;
    margin: 0.5rem 0 0;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.3rem 0.8rem;
    border-radius: 30px;
    font-size: 0.7rem;
    font-weight: 600;
}

.status-confirmed {
    background: #d1fae5;
    color: #065f46;
}

.status-awaiting_review,
.status-pending_manual_confirmation {
    background: #fef3c7;
    color: #92400e;
}

.status-failed {
    background: #fee2e2;
    color: #991b1b;
}
</style>

<script>
function toggleConfirmationData() {
    const content = document.getElementById('confirmationData');
    const icon = document.getElementById('toggleIcon');
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-up');
    } else {
        content.style.display = 'none';
        icon.classList.remove('fa-chevron-up');
        icon.classList.add('fa-chevron-down');
    }
}
</script>