@extends('layouts.admin')

@section('title', 'Generar códigos QR')

@section('page_title_toolbar', 'Generar QR')

@section('content')
    <div class="cm-page qr-page">

        {{-- ============================ CABECERA ============================ --}}
        <div class="page-header-block">
            <div>
                <h1 class="title-main">Generar <span class="title-accent">códigos QR</span></h1>
                <p class="page-subtitle">
                    Crea el QR de pago de un aliado para que el cliente lo escanee desde la app.
                </p>
            </div>

            <div class="header-actions">
                <a href="{{ route('admin.aliados.index') }}" class="btn btn-secondary">
                    <i class="fa-solid fa-store"></i> Aliados
                </a>
            </div>
        </div>

        <div class="qr-grid">

            {{-- ============================ FORMULARIO ============================ --}}
            <section class="cf-card">
                <div class="cf-card__head">
                    <span class="cf-card__icon"><i class="fa-solid fa-qrcode"></i></span>
                    <div class="cf-card__head-text">
                        <h2>Nuevo código QR</h2>
                        <p>Selecciona el aliado y el tipo de pago.</p>
                    </div>
                </div>

                <div class="cf-card__body">
                    <form id="qrGeneratorForm" autocomplete="off">
                        @csrf

                        <div class="cf-grid">
                            <div class="form-group cf-span">
                                <label for="allySelect">Aliado <span class="required-star">*</span></label>
                                <select class="form-control" name="ally_id" id="allySelect" required>
                                    <option value="">Selecciona un aliado</option>
                                    @foreach ($allies as $ally)
                                        <option value="{{ $ally->id }}" data-discount="{{ $ally->discount }}"
                                            data-name="{{ $ally->name }}">
                                            {{ $ally->name }} · {{ $ally->discount_label ?: 'Sin descuento' }}
                                        </option>
                                    @endforeach
                                </select>
                                <span class="field-hint">
                                    <i class="fa-solid fa-circle-info"></i> Solo aparecen aliados activos.
                                </span>
                            </div>

                            <div class="form-group">
                                <label for="qrType">Tipo de pago</label>
                                <select class="form-control" name="type" id="qrType" required>
                                    <option value="c2p">Cliente a proveedor (C2P)</option>
                                    <option value="card">Tarjeta (Card)</option>
                                    <option value="p2p">Persona a persona (P2P)</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Tipo de código</label>
                                <input type="text" class="form-control" value="QR dinámico con firma HMAC" disabled>
                            </div>
                        </div>

                        <div id="allyInfo" class="alert alert-info" style="display: none;">
                            <i class="fa-solid fa-circle-info"></i>
                            <div class="alert-content" id="allyInfoText"></div>
                        </div>

                        <div id="qrAlert" class="alert alert-error" style="display: none;">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            <div class="alert-content" id="qrAlertText"></div>
                        </div>

                        <div class="cf-actions" style="justify-content:flex-end;">
                            <button type="submit" class="btn btn-primary" id="btnGenerate">
                                <i class="fa-solid fa-qrcode"></i> Generar código QR
                            </button>
                        </div>
                    </form>
                </div>
            </section>

            {{-- ============================ ASIDE ============================ --}}
            <aside class="qr-aside">
                <section class="cf-card">
                    <div class="cf-card__head">
                        <span class="cf-card__icon"><i class="fa-solid fa-store"></i></span>
                        <div class="cf-card__head-text">
                            <h2>Aliado seleccionado</h2>
                        </div>
                    </div>

                    <div class="qr-ally">
                        <div class="qr-ally__avatar" id="qrAllyInitial">?</div>
                        <h3 id="qrAllyName">Ningún aliado</h3>
                        <p id="qrAllyDiscount">Selecciona uno para ver su descuento.</p>
                    </div>
                </section>

                <section class="cf-card">
                    <div class="cf-card__head">
                        <span class="cf-card__icon"><i class="fa-solid fa-circle-info"></i></span>
                        <div class="cf-card__head-text">
                            <h2>Cómo funciona</h2>
                        </div>
                    </div>

                    <div class="cf-tips">
                        <ul>
                            <li><i class="fa-solid fa-check"></i> El QR contiene el aliado, su descuento y una firma de seguridad.</li>
                            <li><i class="fa-solid fa-check"></i> El cliente ingresa el monto desde la app al escanearlo.</li>
                            <li><i class="fa-solid fa-check"></i> Puedes descargarlo en PNG o SVG para imprimirlo.</li>
                            <li><i class="fa-solid fa-check"></i> Si cambias el descuento del aliado, genera un QR nuevo.</li>
                        </ul>
                    </div>
                </section>
            </aside>
        </div>

        {{-- ============================ RESULTADO ============================ --}}
        <section class="cf-card qr-result" id="qrResult" style="display: none;">
            <div class="cf-card__head">
                <span class="cf-card__icon cm-stat__icon--green"><i class="fa-solid fa-circle-check"></i></span>
                <div class="cf-card__head-text">
                    <h2>Código QR generado</h2>
                    <p>Listo para descargar o imprimir.</p>
                </div>
            </div>

            <div class="cf-card__body">
                <div class="qr-result__grid">
                    <div class="qr-result__image">
                        <div id="qrImageContainer"></div>
                    </div>

                    <div class="qr-result__info">
                        <div class="qr-result__row">
                            <span>Aliado</span>
                            <strong id="qrResultAlly">—</strong>
                        </div>
                        <div class="qr-result__row">
                            <span>Tipo</span>
                            <strong id="qrResultType">—</strong>
                        </div>
                        <div class="qr-result__row">
                            <span>Descuento</span>
                            <strong id="resultDiscount">0%</strong>
                        </div>
                        <div class="qr-result__row">
                            <span>Generado</span>
                            <strong id="qrResultDate">—</strong>
                        </div>

                        <div class="cf-actions" style="justify-content:flex-start;margin-top:.75rem;">
                            <button type="button" class="btn btn-primary" id="btnDownloadPNG">
                                <i class="fa-solid fa-download"></i> PNG
                            </button>
                            <button type="button" class="btn btn-secondary" id="btnDownloadSVG">
                                <i class="fa-solid fa-download"></i> SVG
                            </button>
                        </div>

                        <p class="field-hint" style="margin-top:.6rem;">
                            <i class="fa-solid fa-circle-info"></i>
                            El monto lo ingresa el cliente en la app móvil.
                        </p>
                    </div>
                </div>
            </div>
        </section>
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
            const qrAllyName = document.getElementById('qrAllyName');
            const qrAllyDiscount = document.getElementById('qrAllyDiscount');
            const qrAllyInitial = document.getElementById('qrAllyInitial');
            const qrAlert = document.getElementById('qrAlert');
            const qrAlertText = document.getElementById('qrAlertText');

            const typeLabels = {
                c2p: 'Cliente a proveedor',
                card: 'Tarjeta',
                p2p: 'Persona a persona',
            };

            function selectedOption() {
                return allySelect.options[allySelect.selectedIndex];
            }

            function updateAllyInfo() {
                const option = selectedOption();
                const hasValue = allySelect.value !== '';

                if (!hasValue) {
                    allyInfo.style.display = 'none';
                    qrAllyName.textContent = 'Ningún aliado';
                    qrAllyDiscount.textContent = 'Selecciona uno para ver su descuento.';
                    qrAllyInitial.textContent = '?';
                    return;
                }

                const name = option.dataset.name || option.text.split('·')[0].trim();
                const discount = option.dataset.discount || '';
                const label = /^\d+([.,]\d+)?$/.test(discount) ? discount + '%' : (discount || 'Sin descuento');

                allyInfoText.innerHTML = '<span>Generando QR para <strong>' + name + '</strong> · Descuento: <strong>' + label + '</strong></span>';
                allyInfo.style.display = 'flex';

                qrAllyName.textContent = name;
                qrAllyDiscount.textContent = 'Descuento: ' + label;
                qrAllyInitial.textContent = (name.charAt(0) || '?').toUpperCase();
            }

            allySelect.addEventListener('change', updateAllyInfo);

            form.addEventListener('submit', async function(event) {
                event.preventDefault();
                qrAlert.style.display = 'none';

                if (!allySelect.value) {
                    qrAlertText.textContent = 'Selecciona un aliado antes de generar el QR.';
                    qrAlert.style.display = 'flex';
                    return;
                }

                btnGenerate.disabled = true;
                btnGenerate.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Generando…';

                try {
                    const response = await fetch('{{ route('admin.qr.generate') }}', {
                        method: 'POST',
                        body: new FormData(form),
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        }
                    });

                    const data = await response.json();

                    if (!data.success) {
                        throw new Error(data.message || 'No se pudo generar el QR.');
                    }

                    qrImageContainer.innerHTML = data.qr_code;
                    resultDiscount.textContent = data.ally_info.discount
                        ? (/^\d+([.,]\d+)?$/.test(data.ally_info.discount) ? data.ally_info.discount + '%' : data.ally_info.discount)
                        : 'Sin descuento';
                    document.getElementById('qrResultAlly').textContent = data.ally_info.name;
                    document.getElementById('qrResultType').textContent = typeLabels[data.ally_info.type] || data.ally_info.type;
                    document.getElementById('qrResultDate').textContent = new Date().toLocaleString('es-ES');

                    window.lastQRData = {
                        string: data.qr_data,
                        json: data.json_data
                    };

                    qrResult.style.display = 'block';
                    qrResult.scrollIntoView({ behavior: 'smooth', block: 'center' });
                } catch (error) {
                    qrAlertText.textContent = error.message;
                    qrAlert.style.display = 'flex';
                } finally {
                    btnGenerate.disabled = false;
                    btnGenerate.innerHTML = '<i class="fa-solid fa-qrcode"></i> Generar código QR';
                }
            });

            document.getElementById('btnDownloadPNG')?.addEventListener('click', function() {
                downloadQrPng();
            });

            document.getElementById('btnDownloadSVG')?.addEventListener('click', function() {
                if (window.lastQRData) downloadQR(window.lastQRData.string, 'svg');
            });

            async function downloadQrPng() {
                const svg = qrImageContainer.querySelector('svg');

                if (!svg) {
                    qrAlertText.textContent = 'Genera un código QR antes de descargarlo.';
                    qrAlert.style.display = 'flex';
                    return;
                }

                try {
                    const clone = svg.cloneNode(true);
                    clone.setAttribute('xmlns', 'http://www.w3.org/2000/svg');

                    const source = new XMLSerializer().serializeToString(clone);
                    const svgUrl = window.URL.createObjectURL(new Blob([source], {
                        type: 'image/svg+xml;charset=utf-8'
                    }));

                    const image = new Image();
                    image.onload = function() {
                        const size = 1000;
                        const margin = 60;
                        const canvas = document.createElement('canvas');
                        canvas.width = size;
                        canvas.height = size;

                        const context = canvas.getContext('2d');
                        context.fillStyle = '#ffffff';
                        context.fillRect(0, 0, size, size);
                        context.drawImage(image, margin, margin, size - margin * 2, size - margin * 2);

                        canvas.toBlob(function(blob) {
                            const url = window.URL.createObjectURL(blob);
                            const link = document.createElement('a');
                            link.href = url;
                            link.download = 'qr_' + Date.now() + '.png';
                            link.click();
                            window.URL.revokeObjectURL(url);
                        }, 'image/png');

                        window.URL.revokeObjectURL(svgUrl);
                    };

                    image.onerror = function() {
                        window.URL.revokeObjectURL(svgUrl);
                        qrAlertText.textContent = 'No se pudo convertir el QR a PNG.';
                        qrAlert.style.display = 'flex';
                    };

                    image.src = svgUrl;
                } catch (error) {
                    qrAlertText.textContent = error.message;
                    qrAlert.style.display = 'flex';
                }
            }

            async function downloadQR(qrString, format) {
                try {
                    const response = await fetch('{{ route('admin.qr.download') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            qr_string: qrString,
                            format: format
                        })
                    });

                    if (!response.ok) {
                        throw new Error('No se pudo descargar el QR.');
                    }

                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = 'qr_' + Date.now() + '.' + format;
                    link.click();
                    window.URL.revokeObjectURL(url);
                } catch (error) {
                    qrAlertText.textContent = error.message;
                    qrAlert.style.display = 'flex';
                }
            }
        });
    </script>
@endpush
