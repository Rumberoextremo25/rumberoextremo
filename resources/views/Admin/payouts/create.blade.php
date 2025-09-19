@extends('layouts.admin')

@section('page_title_toolbar', 'Crear Pago Manual a Aliado')

@section('content')
<div class="main-content">
    <h2 class="page-title text-gray-900">
        <span class="text-gray-900">Crear Pago</span>
        <span style="color: #8a2be2;">Manual a Aliado</span>
    </h2>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Nuevo Pago Manual</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.payouts.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="ally_id">Aliado *</label>
                            <select name="ally_id" id="ally_id" class="form-control" required>
                                <option value="">Seleccionar Aliado</option>
                                @foreach($allies as $ally)
                                    <option value="{{ $ally->id }}" {{ old('ally_id') == $ally->id ? 'selected' : '' }}>
                                        {{ $ally->company_name }} - {{ $ally->company_rif }}
                                    </option>
                                @endforeach
                            </select>
                            @error('ally_id')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="sale_amount">Monto de Venta (Bs.) *</label>
                            <input type="number" step="0.01" name="sale_amount" id="sale_amount" 
                                   class="form-control" value="{{ old('sale_amount') }}" required>
                            @error('sale_amount')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="commission_percentage">Porcentaje de Comisión (%) *</label>
                            <input type="number" step="0.01" name="commission_percentage" id="commission_percentage" 
                                   class="form-control" value="{{ old('commission_percentage') }}" required>
                            @error('commission_percentage')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="commission_amount">Monto de Comisión (Bs.) *</label>
                            <input type="number" step="0.01" name="commission_amount" id="commission_amount" 
                                   class="form-control" value="{{ old('commission_amount') }}" required readonly>
                            @error('commission_amount')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="sale_reference">Referencia de Venta</label>
                            <input type="text" name="sale_reference" id="sale_reference" 
                                   class="form-control" value="{{ old('sale_reference') }}">
                            @error('sale_reference')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="status">Estado *</label>
                            <select name="status" id="status" class="form-control" required>
                                <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pendiente</option>
                                <option value="processing" {{ old('status') == 'processing' ? 'selected' : '' }}>Procesando</option>
                                <option value="paid" {{ old('status') == 'paid' ? 'selected' : '' }}>Pagado</option>
                            </select>
                            @error('status')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="notes">Notas</label>
                    <textarea name="notes" id="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                    @error('notes')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Crear Pago
                    </button>
                    <a href="{{ route('admin.payouts.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const saleAmountInput = document.getElementById('sale_amount');
    const commissionPercentageInput = document.getElementById('commission_percentage');
    const commissionAmountInput = document.getElementById('commission_amount');

    function calculateCommission() {
        const saleAmount = parseFloat(saleAmountInput.value) || 0;
        const commissionPercentage = parseFloat(commissionPercentageInput.value) || 0;
        
        const commissionAmount = (saleAmount * commissionPercentage) / 100;
        commissionAmountInput.value = commissionAmount.toFixed(2);
    }

    saleAmountInput.addEventListener('input', calculateCommission);
    commissionPercentageInput.addEventListener('input', calculateCommission);

    // Calcular comisión inicial si hay valores
    calculateCommission();
});
</script>

<style>
.form-group {
    margin-bottom: 1.5rem;
}
.form-group label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.5rem;
    display: block;
}
</style>
@endsection