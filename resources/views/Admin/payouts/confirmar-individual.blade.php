@extends('layouts.admin')

@section('page_title_toolbar', 'Confirmar Pago #' . $payout->id)

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/payout.css') }}">
    <style>
        .confirm-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .confirm-card {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e5e7eb;
        }

        .confirm-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .confirm-header-icon {
            width: 3rem;
            height: 3rem;
            background: linear-gradient(135deg, #f3e8ff, #ffffff);
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #8a2be2;
            font-size: 1.5rem;
        }

        .confirm-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #111827;
            margin: 0;
            flex: 1;
        }

        .confirm-header .badge {
            background: #fef3c7;
            color: #92400e;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-weight: 600;
            font-size: 0.875rem;
        }

        /* Resumen del pago */
        .payout-summary {
            background: #f9fafb;
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid #e5e7eb;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }

        .summary-item {
            text-align: center;
        }

        .summary-label {
            font-size: 0.75rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }

        .summary-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #111827;
        }

        .summary-value.success {
            color: #10b981;
        }

        .summary-sub {
            font-size: 0.75rem;
            color: #9ca3af;
            margin-top: 0.25rem;
        }

        /* Información del aliado */
        .info-section {
            background: #f9fafb;
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid #e5e7eb;
        }

        .info-section h3 {
            font-size: 1rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-section h3 i {
            color: #8a2be2;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .info-row {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .info-label {
            font-size: 0.75rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .info-value {
            font-size: 0.875rem;
            font-weight: 600;
            color: #111827;
        }

        .info-value code {
            background: #f3f4f6;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            color: #1f2937;
        }

        /* Formulario */
        .form-section {
            background: white;
            border-radius: 0.75rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .form-section h3 {
            font-size: 1rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-section h3 i {
            color: #8a2be2;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .form-group label i {
            color: #8a2be2;
            margin-right: 0.5rem;
            font-size: 0.875rem;
        }

        .form-group .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            transition: all 0.2s;
            font-family: 'Inter', sans-serif;
        }

        .form-group .form-control:focus {
            outline: none;
            border-color: #8a2be2;
            box-shadow: 0 0 0 3px rgba(138, 43, 226, 0.1);
        }

        .form-group .form-control[type="file"] {
            padding: 0.5rem;
            background: #f9fafb;
            cursor: pointer;
        }

        .form-group .form-control[type="file"]:hover {
            background: #f3f4f6;
        }

        .file-info {
            margin-top: 0.5rem;
            font-size: 0.75rem;
            color: #6b7280;
        }

        .file-info i {
            color: #10b981;
            margin-right: 0.25rem;
        }

        /* Botones */
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-secondary {
            background: white;
            border: 1px solid #e5e7eb;
            color: #4b5563;
        }

        .btn-secondary:hover {
            background: #f9fafb;
            border-color: #d1d5db;
        }

        .btn-primary {
            background: #8a2be2;
            border: 1px solid #8a2be2;
            color: white;
        }

        .btn-primary:hover {
            background: #9f4fef;
            border-color: #9f4fef;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(138, 43, 226, 0.3);
        }

        .btn-primary i {
            color: white;
        }

        /* Alertas */
        .alert {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.5rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            animation: slideIn 0.3s ease;
        }

        .alert-success {
            background: #ecfdf5;
            border-left: 4px solid #10b981;
            color: #065f46;
        }

        .alert-error {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
            color: #991b1b;
        }

        @keyframes slideIn {
            from {
                transform: translateX(-100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Responsive */
        @media (max-width: 640px) {
            .confirm-container {
                padding: 1rem;
            }

            .confirm-card {
                padding: 1.5rem;
            }

            .summary-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
@endpush

@section('content')
    <div class="main-content confirm-container">
        <div class="confirm-card">
            <div class="confirm-header">
                <div class="confirm-header-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h2>Confirmar Pago #{{ $payout->id }}</h2>
                <span class="badge">Pendiente de Confirmación</span>
            </div>

            {{-- Alertas --}}
            @if (session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            {{-- Resumen del pago --}}
            <div class="payout-summary">
                <div class="summary-grid">
                    <div class="summary-item">
                        <div class="summary-label">Monto a Pagar</div>
                        <div class="summary-value success">Bs. {{ number_format($payout->net_amount ?? 0, 2, ',', '.') }}</div>
                        <div class="summary-sub">Neto para aliado</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-label">Comisión</div>
                        <div class="summary-value">{{ $payout->commission_percentage ?? 0 }}%</div>
                        <div class="summary-sub">Bs. {{ number_format($payout->commission_amount ?? 0, 2, ',', '.') }}</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-label">Descuento</div>
                        <div class="summary-value">{{ $payout->ally_discount ?? 0 }}%</div>
                        <div class="summary-sub">Bs. {{ number_format($payout->amount_after_discount ?? 0, 2, ',', '.') }}</div>
                    </div>
                </div>
            </div>

            {{-- Información del aliado --}}
            <div class="info-section">
                <h3><i class="fas fa-handshake"></i> Datos del Aliado</h3>
                <div class="info-grid">
                    <div class="info-row">
                        <span class="info-label">Nombre / Empresa</span>
                        <span class="info-value">{{ $payout->ally->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email</span>
                        <span class="info-value">{{ $payout->ally->email ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Teléfono</span>
                        <span class="info-value">{{ $payout->ally->phone ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Documento</span>
                        <span class="info-value">{{ $payout->ally->document_type ?? 'V' }} - {{ $payout->ally->document_number ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>

            {{-- Información bancaria --}}
            <div class="info-section">
                <h3><i class="fas fa-university"></i> Datos Bancarios</h3>
                <div class="info-grid">
                    <div class="info-row">
                        <span class="info-label">Banco</span>
                        <span class="info-value">{{ $payout->ally->bank_name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Tipo de Cuenta</span>
                        <span class="info-value">{{ $payout->ally->account_type ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Número de Cuenta</span>
                        <span class="info-value"><code>{{ $payout->ally->bank_account_number ?? 'N/A' }}</code></span>
                    </div>
                </div>
            </div>

            {{-- Formulario de confirmación --}}
            <form action="{{ route('admin.payouts.confirmar-individual', $payout->id) }}" method="POST">
                @csrf

                <h3><i class="fas fa-file-invoice"></i> Datos de Confirmación</h3>

                <div class="form-group">
                    <label for="fecha_pago">
                        <i class="fas fa-calendar"></i> Fecha de Pago
                    </label>
                    <input type="date" name="fecha_pago" id="fecha_pago" class="form-control" 
                           value="{{ old('fecha_pago', date('Y-m-d')) }}" required>
                    @error('fecha_pago')
                        <span style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="referencia_pago">
                        <i class="fas fa-hashtag"></i> Referencia Bancaria
                    </label>
                    <input type="text" name="referencia_pago" id="referencia_pago" class="form-control" 
                           placeholder="Ej: TRF-123456, PAG-7890, etc." 
                           value="{{ old('referencia_pago') }}" required>
                    @error('referencia_pago')
                        <span style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="archivo_comprobante">
                        <i class="fas fa-file-pdf"></i> Comprobante de Pago
                    </label>
                    <input type="file" name="archivo_comprobante" id="archivo_comprobante" class="form-control" 
                           accept=".pdf,.jpg,.png" required>
                    <div class="file-info">
                        <i class="fas fa-info-circle"></i>
                        Formatos permitidos: PDF, JPG, PNG (Máx: 5MB)
                    </div>
                    @error('archivo_comprobante')
                        <span style="color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-actions">
                    <a href="{{ route('admin.payouts.show', $payout->id) }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check-circle"></i> Confirmar Pago
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection