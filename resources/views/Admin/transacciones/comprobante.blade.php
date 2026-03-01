{{-- resources/views/transacciones/comprobante.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Pago - Rumbero Extremo</title>
    <style>
        /* (mantén los mismos estilos que tenías) */
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
        </svg>
        Imprimir Comprobante
    </button>
    
    <div class="voucher">
        <div class="voucher-header">
            <h1>RUMBERO EXTREMO</h1>
            <p>Comprobante de Transacción</p>
        </div>
        
        <div class="voucher-body">
            <div class="voucher-section">
                <h2>Información de la Transacción</h2>
                <div class="voucher-grid">
                    <div class="voucher-item">
                        <div class="voucher-label">ID Transacción</div>
                        <div class="voucher-value">#{{ $transaccion->id }}</div>
                    </div>
                    <div class="voucher-item">
                        <div class="voucher-label">Código de Referencia</div>
                        <div class="voucher-value">{{ $transaccion->reference_code }}</div>
                    </div>
                    <div class="voucher-item full-width">
                        <div class="voucher-label">Fecha y Hora</div>
                        <div class="voucher-value">{{ $transaccion->created_at->format('d/m/Y H:i:s') }}</div>
                    </div>
                    <div class="voucher-item full-width">
                        <div class="voucher-label">Estado</div>
                        <div class="voucher-value">
                            <span class="status-badge status-{{ $transaccion->status }}">
                                @switch($transaccion->status)
                                    @case('confirmed') Confirmada @break
                                    @case('awaiting_review') En Revisión @break
                                    @case('pending_manual_confirmation') Pendiente de Confirmación @break
                                    @case('failed') Fallida @break
                                    @default {{ $transaccion->status }}
                                @endswitch
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="voucher-section">
                <h2>Información del Usuario</h2>
                <div class="voucher-grid">
                    <div class="voucher-item full-width">
                        <div class="voucher-label">Nombre</div>
                        <div class="voucher-value">{{ $transaccion->user->name ?? 'N/A' }}</div>
                    </div>
                    <div class="voucher-item">
                        <div class="voucher-label">Email</div>
                        <div class="voucher-value">{{ $transaccion->user->email ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
            
            <div class="voucher-section">
                <h2>Información del Aliado</h2>
                <div class="voucher-grid">
                    <div class="voucher-item full-width">
                        <div class="voucher-label">Nombre del Local</div>
                        <div class="voucher-value">{{ $transaccion->ally->name ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
            
            <div class="voucher-section">
                <h2>Detalles del Pago</h2>
                <div class="voucher-grid">
                    <div class="voucher-item">
                        <div class="voucher-label">Monto Original</div>
                        <div class="voucher-value">$ {{ number_format($transaccion->original_amount, 0, ',', '.') }}</div>
                    </div>
                    <div class="voucher-item">
                        <div class="voucher-label">Descuento</div>
                        <div class="voucher-value">{{ $transaccion->discount_percentage }}%</div>
                    </div>
                    <div class="voucher-item">
                        <div class="voucher-label">Comisión Plataforma</div>
                        <div class="voucher-value">$ {{ number_format($transaccion->platform_commission, 0, ',', '.') }}</div>
                    </div>
                    <div class="voucher-item">
                        <div class="voucher-label">Monto para Aliado</div>
                        <div class="voucher-value amount">$ {{ number_format($transaccion->amount_to_ally, 0, ',', '.') }}</div>
                    </div>
                    <div class="voucher-item full-width">
                        <div class="voucher-label">Método de Pago</div>
                        <div class="voucher-value">
                            @if($transaccion->payment_method == 'pago_movil') 📱 Pago Móvil
                            @elseif($transaccion->payment_method == 'transferencia_bancaria') 🏦 Transferencia Bancaria
                            @else {{ $transaccion->payment_method }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            @if($transaccion->confirmation_data)
            <div class="voucher-section">
                <h2>Datos de Confirmación</h2>
                <pre class="confirmation-data">{{ json_encode($transaccion->confirmation_data, JSON_PRETTY_PRINT) }}</pre>
            </div>
            @endif
        </div>
        
        <div class="voucher-footer">
            <p>Este comprobante es válido únicamente para la transacción descrita.</p>
            <p>RUMBERO EXTREMO - La noche es tuya</p>
            <p>Generado el: {{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>