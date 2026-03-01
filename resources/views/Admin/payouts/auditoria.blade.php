@extends('layouts.admin')

@section('page_title_toolbar', 'Auditoría de Pago #' . ($payoutId ?? ''))

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/auditoria.css') }}">
@endpush

@section('content')
    @php
        // Asegurar que $historial sea un array accesible
        $historialData = $historial ?? [];
        
        // Datos del payout
        $payoutData = $historialData['payout'] ?? null;
        $payoutObj = $payoutData;
        
        // Si es un objeto Eloquent, convertirlo a array para acceso uniforme
        $payoutArray = [];
        if (is_object($payoutObj) && method_exists($payoutObj, 'toArray')) {
            $payoutArray = $payoutObj->toArray();
        } elseif (is_array($payoutObj)) {
            $payoutArray = $payoutObj;
        }
        
        // Datos básicos del payout
        $payoutId = $payoutObj->id ?? $payoutArray['id'] ?? $payoutId ?? null;
        
        // Estado
        $status = $payoutObj->status ?? $payoutArray['status'] ?? 'pending';
        $estadoClase = match($status) {
            'completed' => 'success',
            'processing' => 'warning',
            'pending' => 'warning',
            'reverted' => 'danger',
            default => ''
        };
        $estadoTexto = match($status) {
            'completed' => 'Completado',
            'processing' => 'En Proceso',
            'pending' => 'Pendiente',
            'reverted' => 'Revertido',
            default => $status
        };
        
        // Montos
        $saleAmount = $payoutObj->sale_amount ?? $payoutArray['sale_amount'] ?? 0;
        $commissionPercentage = $payoutObj->commission_percentage ?? $payoutArray['commission_percentage'] ?? 0;
        $commissionAmount = $payoutObj->commission_amount ?? $payoutArray['commission_amount'] ?? 0;
        $netAmount = $payoutObj->net_amount ?? $payoutArray['net_amount'] ?? 0;
        $saleId = $payoutObj->sale_id ?? $payoutArray['sale_id'] ?? null;
        
        // Fechas
        $createdAt = $payoutObj->created_at ?? $payoutArray['created_at'] ?? null;
        $updatedAt = $payoutObj->updated_at ?? $payoutArray['updated_at'] ?? null;
        
        // Datos del aliado (si existe)
        $allyName = 'N/A';
        if (isset($payoutObj->ally) && $payoutObj->ally) {
            $allyName = $payoutObj->ally->name ?? $payoutObj->ally->company_name ?? 'Aliado';
        } elseif (isset($payoutArray['ally']) && is_array($payoutArray['ally'])) {
            $allyName = $payoutArray['ally']['name'] ?? $payoutArray['ally']['company_name'] ?? 'Aliado';
        }
        
        // Timeline y cambios
        $timeline = $historialData['timeline'] ?? [];
        $cambios = $historialData['cambios'] ?? [];
        $metadata = $historialData['metadata'] ?? [];
        $transacciones = $historialData['transacciones'] ?? [];
    @endphp

    <div class="main-content auditoria-container">
        {{-- Header --}}
        <div class="page-header">
            <div class="header-left">
                <a href="{{ route('admin.payouts.show', $payoutId) }}" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Volver al Pago
                </a>
                <h1>
                    <span class="text-gray-900">Auditoría de Pago</span>
                    <span class="text-purple">#{{ $payoutId }}</span>
                </h1>
            </div>
            <div class="header-actions">
                <a href="{{ route('admin.payouts.exportar-reporte', ['formato' => 'pdf', 'tipo' => 'auditoria', 'payout_id' => $payoutId]) }}" 
                   class="btn-export">
                    <i class="fas fa-file-pdf"></i> Exportar PDF
                </a>
                <a href="{{ route('admin.payouts.exportar-reporte', ['formato' => 'excel', 'tipo' => 'auditoria', 'payout_id' => $payoutId]) }}" 
                   class="btn-export">
                    <i class="fas fa-file-excel"></i> Exportar Excel
                </a>
            </div>
        </div>

        {{-- Alertas --}}
        @if (session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
                <button type="button" class="close-alert">&times;</button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ session('error') }}</span>
                <button type="button" class="close-alert">&times;</button>
            </div>
        @endif

        {{-- Información del Pago --}}
        @if($payoutObj)
            <div class="payout-info-card">
                <div class="info-header">
                    <h3>
                        <i class="fas fa-info-circle"></i>
                        Información General del Pago
                    </h3>
                    <span class="info-badge">ID: {{ $payoutId }}</span>
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Aliado</span>
                        <span class="info-value">{{ $allyName }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Venta ID</span>
                        <span class="info-value">#{{ $saleId ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Monto Venta</span>
                        <span class="info-value success">Bs. {{ number_format($saleAmount, 2, ',', '.') }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Comisión</span>
                        <span class="info-value">{{ $commissionPercentage }}%</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Neto Pagado</span>
                        <span class="info-value success">Bs. {{ number_format($netAmount, 2, ',', '.') }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Estado Actual</span>
                        <span class="info-value {{ $estadoClase }}">{{ $estadoTexto }}</span>
                    </div>
                </div>
            </div>
        @endif

        {{-- Timeline de Auditoría --}}
        <div class="timeline-section">
            <div class="timeline-header">
                <h3>
                    <i class="fas fa-history"></i>
                    Línea de Tiempo de Eventos
                </h3>
                <span class="badge">{{ count($timeline) }} eventos</span>
            </div>

            @if(empty($timeline))
                <div class="no-data-message" style="text-align: center; padding: 3rem;">
                    <i class="fas fa-history" style="font-size: 3rem; color: #d1d5db; margin-bottom: 1rem;"></i>
                    <p style="color: #6b7280;">No hay eventos de auditoría registrados</p>
                </div>
            @else
                <div class="timeline">
                    @foreach($timeline as $evento)
                        @php
                            $eventoFecha = $evento['fecha'] ?? null;
                            $eventoTipo = $evento['tipo'] ?? 'info';
                            $eventoClase = match($eventoTipo) {
                                'created' => 'created',
                                'updated' => 'updated',
                                'processed' => 'processed',
                                'completed' => 'completed',
                                'reverted' => 'reverted',
                                default => 'info'
                            };
                            $eventoEstado = $evento['estado'] ?? '';
                            $badgeClase = match($eventoEstado) {
                                'completed' => 'badge-completed',
                                'processing' => 'badge-processing',
                                'pending' => 'badge-pending',
                                'reverted' => 'badge-reverted',
                                default => 'badge-pending'
                            };
                        @endphp
                        @if($eventoFecha)
                            <div class="timeline-item {{ $eventoClase }}">
                                <div class="timeline-date">
                                    {{ \Carbon\Carbon::parse($eventoFecha)->format('d/m/Y H:i:s') }}
                                </div>
                                <div class="timeline-action">
                                    {{ $evento['accion'] ?? 'Evento' }}
                                    @if($eventoEstado)
                                        <span class="badge {{ $badgeClase }}">{{ $eventoEstado }}</span>
                                    @endif
                                </div>
                                @if(isset($evento['descripcion']))
                                    <div class="timeline-description">{{ $evento['descripcion'] }}</div>
                                @endif
                                <div class="timeline-user">
                                    <i class="fas fa-user"></i>
                                    {{ $evento['usuario'] ?? 'Sistema' }}
                                </div>
                                @if(!empty($evento['detalles']))
                                    <div class="timeline-details">
                                        <div class="details-grid">
                                            @foreach($evento['detalles'] as $key => $value)
                                                <div class="detail-row">
                                                    <span class="label">{{ ucfirst(str_replace('_', ' ', $key)) }}</span>
                                                    @if(is_numeric($value) && str_contains($key, 'monto'))
                                                        <span class="value success">Bs. {{ number_format($value, 2, ',', '.') }}</span>
                                                    @else
                                                        <span class="value">{{ $value }}</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Tabla de Cambios --}}
        <div class="changes-section">
            <div class="changes-header">
                <h3>
                    <i class="fas fa-exchange-alt"></i>
                    Historial de Cambios Detallado
                </h3>
                <span class="badge">{{ count($cambios) }} cambios</span>
            </div>

            @if(empty($cambios))
                <div class="no-data-message" style="text-align: center; padding: 2rem;">
                    <p class="text-muted">No hay cambios registrados</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="changes-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Usuario</th>
                                <th>Campo</th>
                                <th>Valor Anterior</th>
                                <th>Valor Nuevo</th>
                                <th>Diferencia</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cambios as $cambio)
                                @php
                                    $cambioFecha = $cambio['fecha'] ?? null;
                                    $cambioCampo = $cambio['campo'] ?? '';
                                    $cambioAnterior = $cambio['anterior'] ?? '';
                                    $cambioNuevo = $cambio['nuevo'] ?? '';
                                    $cambioUsuario = $cambio['usuario'] ?? 'Sistema';
                                @endphp
                                @if($cambioFecha)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($cambioFecha)->format('d/m/Y H:i:s') }}</td>
                                        <td>{{ $cambioUsuario }}</td>
                                        <td>
                                            <span class="campo-nombre">{{ $cambioCampo }}</span>
                                        </td>
                                        <td>
                                            @if(is_numeric($cambioAnterior) && str_contains($cambioCampo, 'monto'))
                                                <span class="valor-anterior">Bs. {{ number_format($cambioAnterior, 2, ',', '.') }}</span>
                                            @else
                                                <span class="valor-anterior">{{ $cambioAnterior }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(is_numeric($cambioNuevo) && str_contains($cambioCampo, 'monto'))
                                                <span class="valor-nuevo">Bs. {{ number_format($cambioNuevo, 2, ',', '.') }}</span>
                                            @else
                                                <span class="valor-nuevo">{{ $cambioNuevo }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(is_numeric($cambioAnterior) && is_numeric($cambioNuevo))
                                                @php
                                                    $diferencia = $cambioNuevo - $cambioAnterior;
                                                    $diferenciaClase = $diferencia > 0 ? 'increase' : ($diferencia < 0 ? 'decrease' : '');
                                                @endphp
                                                @if($diferencia != 0)
                                                    <span class="diff-badge {{ $diferenciaClase }}">
                                                        {{ $diferencia > 0 ? '+' : '' }}{{ number_format($diferencia, 2, ',', '.') }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Metadata --}}
        <div class="metadata-section">
            <div class="changes-header">
                <h3>
                    <i class="fas fa-database"></i>
                    Metadatos del Registro
                </h3>
            </div>
            <div class="metadata-grid">
                <div class="metadata-item">
                    <span class="metadata-label">Fecha de Creación</span>
                    <span class="metadata-value">
                        {{ $createdAt ? \Carbon\Carbon::parse($createdAt)->format('d/m/Y H:i:s') : 'N/A' }}
                    </span>
                </div>
                <div class="metadata-item">
                    <span class="metadata-label">Última Actualización</span>
                    <span class="metadata-value">
                        {{ $updatedAt ? \Carbon\Carbon::parse($updatedAt)->format('d/m/Y H:i:s') : 'N/A' }}
                    </span>
                </div>
                <div class="metadata-item">
                    <span class="metadata-label">Creado por</span>
                    <span class="metadata-value">{{ $metadata['creado_por'] ?? 'Sistema' }}</span>
                </div>
                <div class="metadata-item">
                    <span class="metadata-label">IP de Creación</span>
                    <span class="metadata-value"><code>{{ $metadata['ip_creacion'] ?? 'N/A' }}</code></span>
                </div>
                <div class="metadata-item">
                    <span class="metadata-label">Última Modificación por</span>
                    <span class="metadata-value">{{ $metadata['modificado_por'] ?? 'Sistema' }}</span>
                </div>
                <div class="metadata-item">
                    <span class="metadata-label">IP Última Modificación</span>
                    <span class="metadata-value"><code>{{ $metadata['ip_modificacion'] ?? 'N/A' }}</code></span>
                </div>
            </div>
        </div>

        {{-- Información Adicional --}}
        @if(!empty($transacciones))
            <div class="metadata-section">
                <div class="changes-header">
                    <h3>
                        <i class="fas fa-chart-pie"></i>
                        Información de Transacciones
                    </h3>
                </div>
                <div class="metadata-grid">
                    <div class="metadata-item">
                        <span class="metadata-label">Monto Venta</span>
                        <span class="metadata-value success">Bs. {{ number_format($transacciones['monto_venta'] ?? 0, 2, ',', '.') }}</span>
                    </div>
                    <div class="metadata-item">
                        <span class="metadata-label">Comisión %</span>
                        <span class="metadata-value">{{ $transacciones['comision_porcentaje'] ?? 0 }}%</span>
                    </div>
                    <div class="metadata-item">
                        <span class="metadata-label">Comisión Monto</span>
                        <span class="metadata-value warning">Bs. {{ number_format($transacciones['comision_monto'] ?? 0, 2, ',', '.') }}</span>
                    </div>
                    <div class="metadata-item">
                        <span class="metadata-label">Monto Neto</span>
                        <span class="metadata-value success">Bs. {{ number_format($transacciones['monto_neto'] ?? 0, 2, ',', '.') }}</span>
                    </div>
                    <div class="metadata-item">
                        <span class="metadata-label">Descuento Aliado</span>
                        <span class="metadata-value">{{ $transacciones['descuento_aliado'] ?? 0 }}%</span>
                    </div>
                    <div class="metadata-item">
                        <span class="metadata-label">Monto con Descuento</span>
                        <span class="metadata-value">Bs. {{ number_format($transacciones['monto_despues_descuento'] ?? 0, 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Cerrar alertas
        document.querySelectorAll('.close-alert').forEach(button => {
            button.addEventListener('click', function() {
                this.parentElement.style.display = 'none';
            });
        });
    });
</script>
@endpush