{{-- resources/views/transacciones/partials/transaction-row.blade.php --}}
<tr class="transaction-row" data-id="{{ $transaccion->id }}">
    <td>
        <span class="transaction-id">#{{ $transaccion->id }}</span>
    </td>
    <td>
        {{ $transaccion->created_at->format('d/m/Y H:i') }}
    </td>
    <td>
        <span class="transaction-reference">{{ $transaccion->reference_code }}</span>
    </td>
    
    {{-- Mostrar columna de aliado solo para admin --}}
    @if(auth()->user()->user_type === 'admin')
    <td>
        <div class="aliado-info">
            <span class="aliado-name">{{ $transaccion->ally->name ?? 'N/A' }}</span>
            <span class="aliado-email">{{ $transaccion->ally->email ?? '' }}</span>
        </div>
    </td>
    @endif
    
    <td>
        <div class="user-info">
            <span class="user-name">{{ $transaccion->user->name ?? 'N/A' }}</span>
            <span class="user-email">{{ $transaccion->user->email ?? '' }}</span>
        </div>
    </td>
    <td>
        <span class="amount">$ {{ number_format($transaccion->original_amount, 0, ',', '.') }}</span>
    </td>
    <td>
        <span class="commission">{{ $transaccion->discount_percentage }}%</span>
    </td>
    <td>
        <span class="neto">$ {{ number_format($transaccion->amount_to_ally, 0, ',', '.') }}</span>
    </td>
    <td>
        <span class="payment-method">
            @if($transaccion->payment_method == 'pago_movil') 📱 Pago Móvil
            @elseif($transaccion->payment_method == 'transferencia_bancaria') 🏦 Transferencia
            @else {{ $transaccion->payment_method }}
            @endif
        </span>
    </td>
    <td>
        <span class="status-badge status-{{ $transaccion->status }}">
            @switch($transaccion->status)
                @case('confirmed') ✅ Confirmada @break
                @case('awaiting_review') ⏳ En Revisión @break
                @case('pending_manual_confirmation') ⌛ Pendiente @break
                @case('failed') ❌ Fallida @break
                @default {{ $transaccion->status }}
            @endswitch
        </span>
    </td>
    <td>
        <div class="action-buttons">
            <button class="btn-action" onclick="verDetalle({{ $transaccion->id }})" title="Ver detalles">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
            </button>
            
            @if(in_array($transaccion->status, ['pending_manual_confirmation', 'awaiting_review']) && auth()->user()->user_type === 'admin')
                <button class="btn-action approve" onclick="aprobarTransaccion({{ $transaccion->id }})" title="Confirmar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M5 13l4 4L19 7"/>
                    </svg>
                </button>
                <button class="btn-action reject" onclick="rechazarTransaccion({{ $transaccion->id }})" title="Rechazar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            @endif
            
            <button class="btn-action" onclick="imprimirComprobante({{ $transaccion->id }})" title="Imprimir comprobante">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
            </button>
        </div>
    </td>
</tr>