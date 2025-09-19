@extends('layouts.admin')

@section('page_title_toolbar', 'Detalles del Pago a Aliado')

@section('content')
<div class="main-content">
    <h2 class="page-title text-gray-900">
        <span class="text-gray-900">Detalles del</span>
        <span style="color: #8a2be2;">Pago a Aliado</span>
    </h2>

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">Pago #{{ $payout->id }}</h3>
                <a href="{{ route('admin.payouts.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver a la lista
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h4 class="section-title">Información del Pago</h4>
                    <div class="info-group">
                        <label>ID del Pago:</label>
                        <span>{{ $payout->id }}</span>
                    </div>
                    <div class="info-group">
                        <label>Venta Relacionada:</label>
                        <span>#{{ $payout->sale_id }}</span>
                    </div>
                    <div class="info-group">
                        <label>Monto de la Venta:</label>
                        <span>{{ number_format($payout->sale_amount, 2, ',', '.') }} Bs.</span>
                    </div>
                    <div class="info-group">
                        <label>Porcentaje de Comisión:</label>
                        <span>{{ number_format($payout->commission_percentage, 2) }}%</span>
                    </div>
                    <div class="info-group">
                        <label>Monto de Comisión:</label>
                        <span class="text-success">{{ number_format($payout->commission_amount, 2, ',', '.') }} Bs.</span>
                    </div>
                    <div class="info-group">
                        <label>Estado:</label>
                        <span class="badge 
                            @if($payout->status == 'pending') badge-warning
                            @elseif($payout->status == 'processing') badge-info
                            @elseif($payout->status == 'paid') badge-success
                            @else badge-danger @endif">
                            {{ $payout->status_text }}
                        </span>
                    </div>
                </div>

                <div class="col-md-6">
                    <h4 class="section-title">Información del Aliado</h4>
                    <div class="info-group">
                        <label>Aliado:</label>
                        <span>{{ $payout->ally->company_name }}</span>
                    </div>
                    <div class="info-group">
                        <label>RIF:</label>
                        <span>{{ $payout->ally->company_rif }}</span>
                    </div>
                    <div class="info-group">
                        <label>Contacto:</label>
                        <span>{{ $payout->ally->contact_person_name }}</span>
                    </div>
                    <div class="info-group">
                        <label>Teléfono:</label>
                        <span>{{ $payout->ally->contact_phone }}</span>
                    </div>
                    <div class="info-group">
                        <label>Email:</label>
                        <span>{{ $payout->ally->contact_email }}</span>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-6">
                    <h4 class="section-title">Información Bancaria</h4>
                    <div class="info-group">
                        <label>Banco:</label>
                        <span>{{ $payout->ally_bank }}</span>
                    </div>
                    <div class="info-group">
                        <label>Número de Cuenta:</label>
                        <span>{{ $payout->ally_account_number }}</span>
                    </div>
                    <div class="info-group">
                        <label>Tipo de Cuenta:</label>
                        <span>{{ $payout->ally->account_type == 'checking' ? 'Corriente' : 'Ahorro' }}</span>
                    </div>
                    <div class="info-group">
                        <label>Titular de la Cuenta:</label>
                        <span>{{ $payout->ally->account_holder_name }}</span>
                    </div>
                </div>

                <div class="col-md-6">
                    <h4 class="section-title">Información de Procesamiento</h4>
                    <div class="info-group">
                        <label>Fecha de Generación:</label>
                        <span>{{ $payout->generation_date->format('d/m/Y H:i:s') }}</span>
                    </div>
                    @if($payout->payment_date)
                    <div class="info-group">
                        <label>Fecha de Pago:</label>
                        <span>{{ $payout->payment_date->format('d/m/Y H:i:s') }}</span>
                    </div>
                    @endif
                    @if($payout->payment_reference)
                    <div class="info-group">
                        <label>Referencia de Pago:</label>
                        <span>{{ $payout->payment_reference }}</span>
                    </div>
                    @endif
                    @if($payout->sale_reference)
                    <div class="info-group">
                        <label>Referencia de Venta:</label>
                        <span>{{ $payout->sale_reference }}</span>
                    </div>
                    @endif
                </div>
            </div>

            @if($payout->notes)
            <div class="row mt-4">
                <div class="col-12">
                    <h4 class="section-title">Notas</h4>
                    <div class="info-group">
                        <p class="notes">{{ $payout->notes }}</p>
                    </div>
                </div>
            </div>
            @endif

            @if($payout->payment_proof)
            <div class="row mt-4">
                <div class="col-12">
                    <h4 class="section-title">Comprobante de Pago</h4>
                    <div class="info-group">
                        <a href="{{ Storage::url($payout->payment_proof) }}" target="_blank" class="btn btn-primary">
                            <i class="fas fa-download"></i> Descargar Comprobante
                        </a>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
.section-title {
    color: #8a2be2;
    border-bottom: 2px solid #8a2be2;
    padding-bottom: 0.5rem;
    margin-bottom: 1rem;
    font-weight: 600;
}
.info-group {
    margin-bottom: 1rem;
    padding: 0.5rem;
    border-radius: 0.25rem;
    background-color: #f8f9fa;
}
.info-group label {
    font-weight: 600;
    color: #495057;
    margin-right: 0.5rem;
}
.info-group span {
    color: #6c757d;
}
.notes {
    background-color: #f8f9fa;
    padding: 1rem;
    border-radius: 0.25rem;
    border-left: 4px solid #8a2be2;
}
</style>
@endsection