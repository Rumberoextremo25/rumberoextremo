{{-- resources/views/Admin/transacciones/comprobante.blade.php --}}
@extends('layouts.admin')

@section('title', 'Comprobante de Transacción - Rumbero Extremo')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    /* Estilos específicos para impresión */
    @media print {
        .no-print {
            display: none !important;
        }
        .comprobante-card {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
        }
    }
</style>
@endpush

@section('content')
<div class="comprobante-wrapper" style="max-width: 800px; margin: 0 auto; padding: 2rem;">
    {{-- Tarjeta de Comprobante --}}
    <div class="comprobante-card" style="background: white; border-radius: 24px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); overflow: hidden;">
        
        {{-- Header con Gradiente --}}
        <div style="background: linear-gradient(135deg, #A601B3, #3004E1); padding: 2rem; color: white; text-align: center;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-fire" style="font-size: 2rem; color: #FFB800;"></i>
                    <span style="font-size: 1.2rem; font-weight: 700;">RUMBERO EXTREMO</span>
                </div>
                <span style="background: rgba(255,255,255,0.2); padding: 0.3rem 1rem; border-radius: 50px; font-size: 0.8rem;">
                    COMPROBANTE
                </span>
            </div>
            <h1 style="font-size: 2rem; margin-bottom: 0.3rem;">Comprobante de Transacción</h1>
            <p style="font-size: 1.2rem; opacity: 0.9;">#{{ $transaccion->id }}</p>
        </div>

        {{-- Cuerpo del Comprobante --}}
        <div style="padding: 2rem;">
            {{-- Estado --}}
            <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 1.5rem; border-bottom: 2px dashed #e9eef2; margin-bottom: 1.5rem;">
                <span class="status-badge status-{{ $transaccion->status }}" style="padding: 0.5rem 1.5rem; border-radius: 50px; font-weight: 600;">
                    @switch($transaccion->status)
                        @case('confirmed') ✅ CONFIRMADA @break
                        @case('awaiting_review') ⏳ EN REVISIÓN @break
                        @case('pending_manual_confirmation') ⌛ PENDIENTE @break
                        @case('failed') ❌ FALLIDA @break
                        @default {{ strtoupper($transaccion->status) }}
                    @endswitch
                </span>
                <span style="color: #64748b;">
                    <i class="fa-regular fa-calendar"></i>
                    {{ $transaccion->created_at->format('d/m/Y H:i:s') }}
                </span>
            </div>

            {{-- Información de la Transacción --}}
            <div style="margin-bottom: 2rem;">
                <h3 style="font-size: 1.1rem; margin-bottom: 1rem; color: #A601B3;">
                    <i class="fa-solid fa-receipt"></i> Detalles de la Transacción
                </h3>
                <div style="display: grid; grid-template-columns: repeat(2,1fr); gap: 1rem; background: #f8fafc; padding: 1.5rem; border-radius: 16px;">
                    <div>
                        <div style="font-size: 0.7rem; color: #64748b;">ID Transacción</div>
                        <div style="font-weight: 600;">#{{ $transaccion->id }}</div>
                    </div>
                    <div>
                        <div style="font-size: 0.7rem; color: #64748b;">Código de Referencia</div>
                        <div><code>{{ $transaccion->reference_code }}</code></div>
                    </div>
                    <div>
                        <div style="font-size: 0.7rem; color: #64748b;">Método de Pago</div>
                        <div>
                            @if($transaccion->payment_method == 'pago_movil')
                                <i class="fa-solid fa-mobile"></i> Pago Móvil
                            @elseif($transaccion->payment_method == 'transferencia_bancaria')
                                <i class="fa-solid fa-building"></i> Transferencia
                            @else
                                {{ $transaccion->payment_method }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Montos --}}
            <div style="margin-bottom: 2rem;">
                <h3 style="font-size: 1.1rem; margin-bottom: 1rem; color: #A601B3;">
                    <i class="fa-solid fa-coins"></i> Resumen de Montos
                </h3>
                <div style="background: linear-gradient(135deg, #f8fafc, white); padding: 1.5rem; border-radius: 16px;">
                    <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px dashed #e9eef2;">
                        <span>Monto Original:</span>
                        <span style="font-weight: 600; color: #10b981;">${{ number_format($transaccion->original_amount, 0, ',', '.') }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px dashed #e9eef2;">
                        <span>Descuento:</span>
                        <span style="font-weight: 600; color: #f59e0b;">{{ $transaccion->discount_percentage }}%</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px dashed #e9eef2;">
                        <span>Comisión:</span>
                        <span style="font-weight: 600; color: #ef4444;">${{ number_format($transaccion->platform_commission, 0, ',', '.') }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; font-size: 1.2rem;">
                        <span>Neto a Transferir:</span>
                        <span style="font-weight: 700; color: #A601B3;">${{ number_format($transaccion->amount_to_ally, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            {{-- Participantes --}}
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 2rem;">
                {{-- Usuario --}}
                <div style="background: #f8fafc; border-radius: 16px; overflow: hidden;">
                    <div style="background: linear-gradient(135deg, #A601B3, #3004E1); padding: 0.8rem; color: white;">
                        <i class="fa-solid fa-user"></i> Usuario
                    </div>
                    <div style="padding: 1rem;">
                        <div style="font-weight: 600;">{{ $transaccion->user->name ?? 'N/A' }}</div>
                        <div style="font-size: 0.8rem; color: #64748b;">{{ $transaccion->user->email ?? 'N/A' }}</div>
                    </div>
                </div>

                {{-- Aliado --}}
                <div style="background: #f8fafc; border-radius: 16px; overflow: hidden;">
                    <div style="background: linear-gradient(135deg, #3004E1, #A601B3); padding: 0.8rem; color: white;">
                        <i class="fa-solid fa-handshake"></i> Aliado
                    </div>
                    <div style="padding: 1rem;">
                        <div style="font-weight: 600;">{{ $transaccion->ally->name ?? 'N/A' }}</div>
                        <div style="font-size: 0.8rem; color: #64748b;">{{ $transaccion->ally->email ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>

            {{-- Datos de Confirmación --}}
            @if($transaccion->confirmation_data)
            <div style="margin-bottom: 2rem;">
                <h3 style="font-size: 1.1rem; margin-bottom: 1rem; color: #A601B3;">
                    <i class="fa-solid fa-circle-check"></i> Datos de Confirmación
                </h3>
                <pre style="background: #1e293b; color: #e2e8f0; padding: 1.5rem; border-radius: 16px; font-size: 0.85rem; overflow-x: auto;">{{ json_encode($transaccion->confirmation_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </div>
            @endif

            {{-- Notas Legales --}}
            <div style="background: #f8fafc; padding: 1rem; border-radius: 12px; border-left: 4px solid #A601B3; font-size: 0.8rem; color: #64748b;">
                <p><i class="fa-regular fa-note-sticky"></i> Este comprobante es válido como constancia de la transacción realizada.</p>
                <p style="margin-top: 0.3rem;">Código de Verificación: <code>{{ $transaccion->reference_code }}</code></p>
            </div>
        </div>

        {{-- Footer --}}
        <div style="background: #f8fafc; padding: 1.5rem; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #e9eef2;">
            <div style="font-size: 0.8rem; color: #64748b;">
                <p>Rumbero Extremo - Todos los derechos reservados</p>
                <p>{{ now()->format('Y') }} ©</p>
            </div>
            <button class="no-print" onclick="window.print()" style="background: linear-gradient(135deg, #A601B3, #3004E1); color: white; border: none; padding: 0.7rem 1.5rem; border-radius: 12px; cursor: pointer; display: flex; align-items: center; gap: 0.5rem;">
                <i class="fa-solid fa-print"></i> Imprimir
            </button>
        </div>
    </div>
</div>

<style>
    .status-confirmed { background: #d1fae5; color: #065f46; }
    .status-awaiting_review, .status-pending_manual_confirmation { background: #fef3c7; color: #92400e; }
    .status-failed { background: #fee2e2; color: #991b1b; }
</style>
@endsection