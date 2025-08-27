@extends('layouts.admin')

@section('content')
<div class="main-content">
    <h2 class="page-title">Pagos Pendientes a Aliados</h2>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if($pendingPayouts->isEmpty())
        <p class="no-payouts">No hay pagos pendientes en este momento.</p>
    @else
        <div class="table-actions">
            <form action="{{ route('admin.payouts.generate_csv') }}" method="GET" class="form-csv">
                <button type="submit" class="btn btn-primary">Generar CSV Seleccionados</button>
            </form>
            
            <form action="{{ route('admin.payouts.mark_processed') }}" method="POST" class="form-process">
                @csrf
                <input type="hidden" name="payout_ids[]" id="selected-payout-ids">
                <input type="text" name="transaction_reference" placeholder="Referencia bancaria (opcional)" class="form-control">
                <button type="submit" class="btn btn-success" id="mark-processed-btn">Marcar Seleccionados como Procesados</button>
            </form>
        </div>
        
        <div class="table-responsive">
            <table class="payouts-table">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all"></th>
                        <th>ID Pago</th>
                        <th>Orden #</th>
                        <th>Aliado</th>
                        <th>Monto (Bs.)</th>
                        <th>Cuenta Destino</th>
                        <th>Fecha de Registro</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pendingPayouts as $payout)
                        <tr>
                            <td><input type="checkbox" name="payout_ids[]" value="{{ $payout->id }}"></td>
                            <td>{{ $payout->id }}</td>
                            <td>{{ $payout->order->id }}</td>
                            <td>{{ $payout->partner->name }}</td>
                            <td>{{ number_format($payout->amount, 2, ',', '.') }}</td>
                            <td>{{ $payout->partner->account_number }} ({{ $payout->partner->bank_name }})</td>
                            <td>{{ $payout->created_at->format('d/m/Y H:i') }}</td>
                            <td><span class="badge badge-warning">{{ $payout->status }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // JavaScript para manejar la selección de checkboxes y el formulario
        document.getElementById('select-all').addEventListener('change', function() {
            document.querySelectorAll('input[name="payout_ids[]"]').forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        document.getElementById('mark-processed-btn').addEventListener('click', function(event) {
            const selectedIds = Array.from(document.querySelectorAll('input[name="payout_ids[]"]:checked'))
                .map(checkbox => checkbox.value);
            
            if (selectedIds.length === 0) {
                // Aquí puedes usar un modal o una notificación en lugar de alert
                console.error('Por favor, selecciona al menos un pago para marcar como procesado.');
                event.preventDefault();
                return;
            }
            
            const form = this.closest('form');
            const hiddenInput = form.querySelector('#selected-payout-ids');
            hiddenInput.value = JSON.stringify(selectedIds);
        });
    });
</script>
@endsection