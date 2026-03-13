{{-- resources/views/admin/qr-generator.blade.php --}}
@extends('layouts.admin')

@section('title', 'Generar Códigos QR')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/qr-generator.css') }}">
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Generar QR</li>
                </ol>
            </nav>

            <!-- Título de la página -->
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h3 class="mb-0">
                    <i class="fas fa-qrcode text-primary me-2"></i>Generar Códigos QR
                </h3>
                <span class="badge bg-primary">Admin</span>
            </div>

            <!-- Card principal -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cog me-2 text-primary"></i>Generar QR para Aliado
                    </h5>
                </div>
                
                <div class="card-body">
                    <form id="qrGeneratorForm">
                        @csrf
                        
                        <!-- Selector de Aliado -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-handshake me-2"></i>Seleccionar Aliado
                                </label>
                                <select class="form-select" name="ally_id" id="allySelect" required>
                                    <option value="">-- Seleccione un aliado --</option>
                                    @foreach($allies as $ally)
                                        <option value="{{ $ally->id }}" data-discount="{{ $ally->descuento_aliado }}">
                                            {{ $ally->name }} (ID: {{ $ally->id }}) - {{ $ally->descuento_aliado }}% descuento
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Información del aliado -->
                        <div id="allyInfo" class="alert alert-info" style="display: none;">
                            <i class="fas fa-info-circle me-2"></i>
                            <span id="allyInfoText"></span>
                        </div>

                        <!-- Botón Generar -->
                        <div class="row mb-4">
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-primary btn-lg px-5" id="btnGenerate">
                                    <i class="fas fa-qrcode me-2"></i>Generar Código QR
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Resultado QR -->
                    <div id="qrResult" class="card border-0 shadow-sm mt-4" style="display: none;">
                        <div class="card-header bg-success text-white py-3">
                            <h6 class="mb-0">
                                <i class="fas fa-check-circle me-2"></i>QR Generado Exitosamente
                            </h6>
                        </div>
                        <div class="card-body text-center py-4">
                            <div id="qrImageContainer" class="mb-4 p-4 bg-light rounded d-inline-block shadow-sm">
                                <!-- Aquí se insertará el QR -->
                            </div>
                            
                            <div class="mb-3">
                                <span class="badge bg-info p-2">
                                    <i class="fas fa-percentage me-1"></i>
                                    Descuento del aliado: <span id="resultDiscount">0%</span>
                                </span>
                            </div>
                            
                            <div class="d-flex justify-content-center gap-2 flex-wrap">
                                <button type="button" class="btn btn-success" id="btnDownloadPNG">
                                    <i class="fas fa-download me-2"></i>PNG
                                </button>
                                <button type="button" class="btn btn-success" id="btnDownloadSVG">
                                    <i class="fas fa-download me-2"></i>SVG
                                </button>
                            </div>

                            <div class="mt-3 text-muted small">
                                <i class="fas fa-info-circle me-1"></i>
                                El monto lo ingresará el cliente en la app móvil
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const allySelect = document.getElementById('allySelect');
    const allyInfo = document.getElementById('allyInfo');
    const allyInfoText = document.getElementById('allyInfoText');
    const form = document.getElementById('qrGeneratorForm');
    const btnGenerate = document.getElementById('btnGenerate');
    const qrResult = document.getElementById('qrResult');
    const qrImageContainer = document.getElementById('qrImageContainer');
    const resultDiscount = document.getElementById('resultDiscount');

    // Mostrar información cuando se selecciona aliado
    function updateAllyInfo() {
        if (allySelect.value) {
            const selectedOption = allySelect.options[allySelect.selectedIndex];
            const allyName = selectedOption.text.split(' - ')[0];
            const discount = selectedOption.dataset.discount;
            allyInfoText.innerHTML = `Generando QR para: <strong>${allyName}</strong> | Descuento: <strong>${discount}%</strong>`;
            allyInfo.style.display = 'block';
        } else {
            allyInfo.style.display = 'none';
        }
    }

    allySelect.addEventListener('change', updateAllyInfo);

    // Generar QR
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (!allySelect.value) {
            alert('Seleccione un aliado');
            return;
        }

        btnGenerate.disabled = true;
        btnGenerate.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generando...';

        try {
            const formData = new FormData(form);
            // Agregar tipo por defecto si no existe en el backend
            formData.append('type', 'c2p');
            
            const response = await fetch('{{ route("admin.qr.generate") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });

            const data = await response.json();

            if (data.success) {
                qrImageContainer.innerHTML = data.qr_code;
                if (data.ally_info && data.ally_info.discount) {
                    resultDiscount.textContent = data.ally_info.discount + '%';
                }
                qrResult.style.display = 'block';
                
                window.lastQRData = {
                    string: data.qr_data,
                    json: data.json_data
                };
            } else {
                alert('Error: ' + data.message);
            }
        } catch (error) {
            alert('Error al generar el QR: ' + error.message);
        } finally {
            btnGenerate.disabled = false;
            btnGenerate.innerHTML = '<i class="fas fa-qrcode me-2"></i>Generar Código QR';
        }
    });

    // Descargas
    document.getElementById('btnDownloadPNG')?.addEventListener('click', function() {
        if (window.lastQRData) downloadQR(window.lastQRData.string, 'png');
    });

    document.getElementById('btnDownloadSVG')?.addEventListener('click', function() {
        if (window.lastQRData) downloadQR(window.lastQRData.string, 'svg');
    });

    async function downloadQR(qrString, format) {
        try {
            const response = await fetch('{{ route("admin.qr.download") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ qr_string: qrString, format: format })
            });

            if (response.ok) {
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `qr_${Date.now()}.${format}`;
                a.click();
                window.URL.revokeObjectURL(url);
            }
        } catch (error) {
            alert('Error: ' + error.message);
        }
    }
});
</script>
@endpush