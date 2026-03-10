// public/js/admin/qr-generator.js

document.addEventListener('DOMContentLoaded', function() {
    // Elementos del DOM
    const typeRadios = document.querySelectorAll('input[name="type"]');
    const c2pFields = document.getElementById('c2pFields');
    const cardFields = document.getElementById('cardFields');
    const p2pFields = document.getElementById('p2pFields');
    const form = document.getElementById('qrGeneratorForm');
    const btnGenerate = document.getElementById('btnGenerate');
    const btnReset = document.getElementById('btnReset');
    const btnNewQR = document.getElementById('btnNewQR');
    const qrResult = document.getElementById('qrResult');
    const qrImageContainer = document.getElementById('qrImageContainer');
    const qrExpiration = document.getElementById('qrExpiration');
    const jsonPreviewContent = document.getElementById('jsonPreviewContent');

    // Función para mostrar/ocultar campos según tipo
    function toggleFields(type) {
        c2pFields.style.display = type === 'c2p' ? 'block' : 'none';
        cardFields.style.display = type === 'card' ? 'block' : 'none';
        p2pFields.style.display = type === 'p2p' ? 'block' : 'none';

        // Actualizar required
        document.querySelectorAll('[name="c2p_bank_code"], [name="c2p_phone"], [name="c2p_terminal"]')
            .forEach(f => f.required = type === 'c2p');
        document.querySelectorAll('[name="card_affiliation"], [name="card_operation_ref"]')
            .forEach(f => f.required = type === 'card');
        document.querySelectorAll('[name="p2p_receptor_id"], [name="p2p_account"]')
            .forEach(f => f.required = type === 'p2p');
    }

    // Event listeners para los radios
    typeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            toggleFields(this.value);
        });
    });

    // Inicializar con C2P
    toggleFields('c2p');

    // Botón Reset
    btnReset.addEventListener('click', function(e) {
        e.preventDefault();
        form.reset();
        toggleFields('c2p');
        document.querySelector('input[name="type"][value="c2p"]').checked = true;
    });

    // Botón Nuevo QR
    if (btnNewQR) {
        btnNewQR.addEventListener('click', function() {
            qrResult.style.display = 'none';
            form.reset();
            toggleFields('c2p');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // Manejar envío del formulario
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        btnGenerate.disabled = true;
        btnGenerate.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generando...';

        try {
            const formData = new FormData(form);
            
            const response = await fetch('/admin/qr/generate', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                qrImageContainer.innerHTML = data.qr_code;
                qrExpiration.textContent = '⏰ Expira: ' + data.expires_at;
                
                if (jsonPreviewContent) {
                    jsonPreviewContent.textContent = JSON.stringify(JSON.parse(data.json_data), null, 2);
                }
                
                qrResult.style.display = 'block';
                
                // Guardar datos para descargas
                window.lastQRData = {
                    string: data.qr_data,
                    json: data.json_data
                };

                // Scroll suave al resultado
                qrResult.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else {
                alert('Error: ' + (data.message || 'Error desconocido'));
            }
        } catch (error) {
            alert('Error al generar el QR: ' + error.message);
        } finally {
            btnGenerate.disabled = false;
            btnGenerate.innerHTML = '<i class="fas fa-qrcode me-2"></i>Generar Código QR';
        }
    });

    // Manejar descargas
    document.getElementById('btnDownloadPNG')?.addEventListener('click', function() {
        if (window.lastQRData) {
            downloadQR(window.lastQRData.string, 'png');
        }
    });

    document.getElementById('btnDownloadSVG')?.addEventListener('click', function() {
        if (window.lastQRData) {
            downloadQR(window.lastQRData.string, 'svg');
        }
    });

    document.getElementById('btnCopy')?.addEventListener('click', function() {
        if (window.lastQRData) {
            navigator.clipboard.writeText(window.lastQRData.json).then(() => {
                alert('✅ JSON copiado al portapapeles');
            }).catch(() => {
                alert('❌ Error al copiar');
            });
        }
    });

    // Función para descargar QR
    async function downloadQR(qrString, format) {
        try {
            const response = await fetch('/admin/qr/download', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                },
                body: JSON.stringify({
                    qr_string: qrString,
                    format: format
                })
            });

            if (response.ok) {
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `qr_${Date.now()}.${format}`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);
            } else {
                alert('Error al descargar');
            }
        } catch (error) {
            alert('Error: ' + error.message);
        }
    }

    // Validación de teléfono en tiempo real
    const phoneInput = document.querySelector('input[name="c2p_phone"]');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    }

    // Validación de montos
    const amountBs = document.querySelector('input[name="amount_bs"]');
    const amountUsd = document.querySelector('input[name="amount_usd"]');

    if (amountBs) {
        amountBs.addEventListener('change', function() {
            if (this.value < 0.01) this.value = 0.01;
        });
    }

    if (amountUsd) {
        amountUsd.addEventListener('change', function() {
            if (this.value < 0.01) this.value = 0.01;
        });
    }
});