/* ==========================================================================
   RUMBERO EXTREMO · PANEL ADMIN
   admin-forms.js · Comportamiento de formularios y componentes del panel
   --------------------------------------------------------------------------
   · Banners y Promociones: subida con drag & drop, validación de imagen,
     previsualización en vivo y anti doble envío.
   · Usuarios: previsualización, medidor de contraseña y coincidencia.
   · Listados: filtros GET con envío automático y modal de borrado.
   · Imágenes: oculta las que no cargan para dejar el placeholder.
   ========================================================================== */
(function () {
    'use strict';

    var MAX_BYTES = 2 * 1024 * 1024;
    var TODAY = new Date().toISOString().slice(0, 10);

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-cf-form]').forEach(initForm);
        document.querySelectorAll('[data-uf-form]').forEach(initUserForm);
        initFilters();
        initDeleteModal();
        initBrokenImages();
    });

    /* ---------------------------------------------------------------------
       Imágenes que no cargan: se oculta el <img> y queda el placeholder.
       --------------------------------------------------------------------- */
    function initBrokenImages() {
        function handleBroken(img) {
            img.hidden = true;

            var media = img.closest('.cf-preview__media');
            if (media) {
                var empty = media.querySelector('[data-cf-preview-empty]');
                if (empty) empty.hidden = false;
            }
        }

        document.querySelectorAll('img[data-img]').forEach(function (img) {
            img.addEventListener('error', function () {
                handleBroken(img);
            });

            if (img.complete && img.naturalWidth === 0) {
                handleBroken(img);
            }
        });
    }

    /* ---------------------------------------------------------------------
       Formulario de contenido
       --------------------------------------------------------------------- */
    function initForm(form) {
        var kind = form.getAttribute('data-cf-form') || 'banner';
        var preview = form.querySelector('[data-cf-preview]');

        var title = form.querySelector('[name="title"]');
        var description = form.querySelector('[name="description"]');
        var targetUrl = form.querySelector('[name="target_url"]');
        var order = form.querySelector('[name="display_order"]');
        var discount = form.querySelector('[name="discount"]');
        var price = form.querySelector('[name="price"]');
        var expires = form.querySelector('[name="expires_at"]');
        var active = form.querySelector('input[type="checkbox"][name="is_active"]');
        var featured = form.querySelector('input[type="checkbox"][name="is_featured"]');

        var imageInput = form.querySelector('[data-cf-image-input]');
        var upload = form.querySelector('[data-cf-upload]');
        var thumb = form.querySelector('[data-cf-thumb]');
        var thumbImg = form.querySelector('[data-cf-thumb-img]');
        var fileName = form.querySelector('[data-cf-file-name]');
        var fileHint = form.querySelector('[data-cf-file-hint]');
        var removeBtn = form.querySelector('[data-cf-image-remove]');
        var fileError = form.querySelector('[data-cf-file-error]');

        var previewImg = preview ? preview.querySelector('[data-cf-preview-image]') : null;
        var previewEmpty = preview ? preview.querySelector('[data-cf-preview-empty]') : null;

        var initialSrc = previewImg ? previewImg.getAttribute('src') || '' : '';
        var initialName = fileName ? fileName.textContent.trim() : '';
        var initialHint = fileHint ? fileHint.textContent.trim() : '';
        var initialThumb = thumbImg ? thumbImg.getAttribute('src') || '' : '';
        var objectUrl = null;

        /* -------------------- Previsualización en vivo -------------------- */
        function textOf(el) {
            return (el && el.value ? el.value : '').trim();
        }

        function setText(name, value, fallback) {
            if (!preview) return;
            var el = preview.querySelector('[data-cf-preview="' + name + '"]');
            if (!el) return;

            if (value) {
                el.textContent = value;
                el.removeAttribute('data-empty-state');
            } else {
                el.textContent = el.getAttribute('data-empty') || fallback || '';
                el.setAttribute('data-empty-state', '1');
            }
        }

        function sync() {
            if (preview) {
                setText('title', textOf(title), kind === 'promotion' ? 'Título de la promoción' : 'Título del banner');
                setText('desc', textOf(description), 'Aquí se verá la descripción que escribas.');

                var orderEl = preview.querySelector('[data-cf-preview="order"]');
                if (orderEl) {
                    orderEl.textContent = 'Orden ' + (textOf(order) || '0');
                }

                var statusEl = preview.querySelector('[data-cf-preview="status"]');
                if (statusEl) {
                    var isOn = active ? active.checked : true;
                    var statusLabel = statusEl.querySelector('span') || statusEl;
                    var statusIcon = statusEl.querySelector('i');

                    statusLabel.textContent = isOn
                        ? (kind === 'promotion' ? 'Activa' : 'Activo')
                        : (kind === 'promotion' ? 'Inactiva' : 'Inactivo');

                    if (statusIcon) {
                        statusIcon.className = 'fa-solid ' + (isOn ? 'fa-circle-check' : 'fa-circle-pause');
                    }

                    statusEl.classList.toggle('is-on', isOn);
                    statusEl.classList.toggle('is-off', !isOn);
                }

                var featuredEl = preview.querySelector('[data-cf-preview="featured"]');
                if (featuredEl) {
                    var isFeatured = featured ? featured.checked : false;
                    featuredEl.hidden = !isFeatured;
                }

                var discountEl = preview.querySelector('[data-cf-preview="discount"]');
                if (discountEl) {
                    var raw = textOf(discount);
                    var label = raw.replace(/\s+/g, '');
                    if (raw) {
                        discountEl.textContent = /^\d+([.,]\d+)?$/.test(label) ? '-' + label + '%' : raw;
                        discountEl.hidden = false;
                    } else {
                        discountEl.hidden = true;
                    }
                }

                var priceEl = preview.querySelector('[data-cf-preview="price"]');
                if (priceEl) {
                    var priceValue = textOf(price);
                    if (priceValue) {
                        priceEl.textContent = '$ ' + priceValue;
                        priceEl.hidden = false;
                    } else {
                        priceEl.hidden = true;
                    }
                }

                var expiresEl = preview.querySelector('[data-cf-preview="expires"]');
                if (expiresEl) {
                    var dateValue = textOf(expires);
                    if (dateValue) {
                        var parts = dateValue.split('-');
                        expiresEl.textContent = 'Hasta ' + parts[2] + '/' + parts[1] + '/' + parts[0];
                    } else {
                        expiresEl.textContent = 'Sin caducidad';
                    }
                }

                var cta = preview.querySelector('[data-cf-preview="cta"]');
                if (cta) {
                    var ctaText = kind === 'promotion' ? 'Ver promoción' : 'Ver más';
                    var url = textOf(targetUrl);
                    if (url) {
                        try {
                            ctaText = 'Ir a ' + new URL(url).hostname.replace(/^www\./, '');
                        } catch (error) {
                            ctaText = 'Ver más';
                        }
                    }
                    cta.textContent = ctaText;
                }
            }
        }

        /* --------------------------- Imagen ------------------------------- */
        function showFileError(message) {
            if (!fileError) return;
            if (message) {
                fileError.innerHTML = '<i class="fa-solid fa-circle-exclamation"></i> ' + message;
                fileError.classList.add('is-visible');
            } else {
                fileError.textContent = '';
                fileError.classList.remove('is-visible');
            }
        }

        function setImage(src, name) {
            if (upload) upload.classList.add('has-file');

            if (thumbImg) {
                thumbImg.src = src;
                thumbImg.hidden = false;
            }
            if (thumb) thumb.classList.add('is-filled');

            if (fileName && name) fileName.textContent = name;
            if (fileHint) fileHint.textContent = 'Vista previa lista · se guardará al enviar el formulario.';
            if (removeBtn) removeBtn.hidden = false;

            if (previewImg) {
                previewImg.src = src;
                previewImg.hidden = false;
            }
            if (previewEmpty) previewEmpty.hidden = true;
        }

        function syncImage() {
            /* Mantiene coherencia si el navegador no disparó change (autofill). */
            if (!imageInput || !imageInput.files || !imageInput.files.length) return;
        }

        function resetImage() {
            if (objectUrl) {
                URL.revokeObjectURL(objectUrl);
                objectUrl = null;
            }

            if (upload) upload.classList.toggle('has-file', !!initialThumb);
            if (removeBtn) removeBtn.hidden = true;
            showFileError('');

            if (fileName) fileName.textContent = initialName;
            if (fileHint) fileHint.textContent = initialHint;

            if (thumbImg) {
                if (initialThumb) {
                    thumbImg.src = initialThumb;
                    thumbImg.hidden = false;
                } else {
                    thumbImg.removeAttribute('src');
                    thumbImg.hidden = true;
                }
            }

            if (previewImg) {
                if (initialSrc) {
                    previewImg.src = initialSrc;
                    previewImg.hidden = false;
                } else {
                    previewImg.removeAttribute('src');
                    previewImg.hidden = true;
                }
            }
            if (previewEmpty) previewEmpty.hidden = !!initialSrc;
        }

        function validate(file) {
            if (!file) return false;

            if (!file.type || file.type.indexOf('image/') !== 0) {
                showFileError('Selecciona un archivo de imagen válido (JPG, PNG, GIF o WEBP).');
                return false;
            }

            if (file.size > MAX_BYTES) {
                var mb = (file.size / 1048576).toFixed(1);
                showFileError('La imagen pesa ' + mb + ' MB y el máximo es 2 MB.');
                return false;
            }

            showFileError('');
            return true;
        }

        if (imageInput) {
            imageInput.addEventListener('change', function () {
                var file = imageInput.files && imageInput.files[0];
                if (!file) {
                    resetImage();
                    sync();
                    return;
                }

                if (!validate(file)) {
                    imageInput.value = '';
                    resetImage();
                    sync();
                    return;
                }

                if (objectUrl) URL.revokeObjectURL(objectUrl);
                objectUrl = URL.createObjectURL(file);
                setImage(objectUrl, file.name);
                sync();
            });
        }

        if (removeBtn) {
            removeBtn.addEventListener('click', function () {
                if (imageInput) imageInput.value = '';
                resetImage();
                sync();
                if (imageInput) imageInput.focus();
            });
        }

        /* ------------------------ Arrastrar y soltar ---------------------- */
        if (upload && imageInput) {
            ['dragenter', 'dragover'].forEach(function (eventName) {
                upload.addEventListener(eventName, function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    upload.classList.add('is-drag');
                });
            });

            ['dragleave', 'drop'].forEach(function (eventName) {
                upload.addEventListener(eventName, function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    upload.classList.remove('is-drag');
                });
            });

            upload.addEventListener('drop', function (event) {
                var files = event.dataTransfer && event.dataTransfer.files;
                if (!files || !files.length) return;

                try {
                    imageInput.files = files;
                } catch (error) {
                    var transfer = new DataTransfer();
                    transfer.items.add(files[0]);
                    imageInput.files = transfer.files;
                }

                imageInput.dispatchEvent(new Event('change', { bubbles: true }));
            });
        }

        /* ------------------------- Validaciones --------------------------- */
        if (order) {
            order.addEventListener('input', function () {
                if (order.value === '') return;
                var value = parseInt(order.value, 10);
                if (isNaN(value) || value < 0) order.value = 0;
            });
        }

        if (expires) {
            expires.addEventListener('change', function () {
                if (expires.value && expires.value < TODAY) {
                    expires.setCustomValidity('La fecha de expiración no puede ser anterior a hoy.');
                    expires.reportValidity();
                    expires.value = '';
                } else {
                    expires.setCustomValidity('');
                }
                sync();
            });
            expires.addEventListener('input', function () {
                expires.setCustomValidity('');
            });
        }

        /* --------------------------- Envío -------------------------------- */
        form.addEventListener('submit', function (event) {
            var requiresImage = imageInput && imageInput.getAttribute('data-cf-required') === '1';
            var file = imageInput && imageInput.files ? imageInput.files[0] : null;

            if (requiresImage && !file) {
                event.preventDefault();
                showFileError('Selecciona una imagen para continuar.');
                if (upload) upload.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            if (file && !validate(file)) {
                event.preventDefault();
                return;
            }

            var submit = form.querySelector('[data-cf-submit]');
            if (submit && !submit.disabled) {
                submit.classList.add('is-loading');
            }
        });

        form.addEventListener('input', sync);
        form.addEventListener('change', sync);
        sync();
    }

    /* ---------------------------------------------------------------------
       Formulario de usuario (crear / editar)
       --------------------------------------------------------------------- */
    function initUserForm(form) {
        var firstname = form.querySelector('[name="firstname"]');
        var lastname = form.querySelector('[name="lastname"]');
        var email = form.querySelector('[name="email"]');
        var phone = form.querySelector('[name="phone1"]');
        var role = form.querySelector('[name="user_type"]');
        var status = form.querySelector('[name="status"]');
        var regDate = form.querySelector('[name="registrationDate"]');
        var password = form.querySelector('[name="password"]');
        var confirm = form.querySelector('[name="password_confirmation"]');
        var submit = form.querySelector('[data-uf-submit]');

        var preview = form.querySelector('[data-uf-preview]');
        var pvInitial = preview ? preview.querySelector('[data-uf-initial]') : null;
        var pvName = preview ? preview.querySelector('[data-uf-name]') : null;
        var pvEmail = preview ? preview.querySelector('[data-uf-email]') : null;
        var pvPhone = preview ? preview.querySelector('[data-uf-phone]') : null;
        var pvDate = preview ? preview.querySelector('[data-uf-date]') : null;
        var pvRole = preview ? preview.querySelector('[data-uf-role]') : null;
        var pvStatus = preview ? preview.querySelector('[data-uf-status]') : null;

        var roleLabels = { admin: 'Administrador', aliado: 'Aliado', afiliado: 'Afiliado', comun: 'Común' };
        var statusLabels = { activo: 'Activo', pendiente: 'Pendiente', inactivo: 'Inactivo' };
        var roleBadges = { admin: 'badge-primary', aliado: 'badge-info', afiliado: 'badge-warning', comun: 'badge-neutral' };
        var statusBadges = { activo: 'badge-success', pendiente: 'badge-warning', inactivo: 'badge-neutral' };

        function textOf(el) {
            return el && el.value ? el.value.trim() : '';
        }

        function setText(el, value, fallback) {
            if (!el) return;
            el.textContent = value || fallback || '';
        }

        function setBadge(el, label, className) {
            if (!el) return;
            el.textContent = label;
            el.className = 'badge ' + className;
        }

        function formatDate(value) {
            if (!value) return '';
            var parts = value.split('-');
            return parts.length === 3 ? parts[2] + '/' + parts[1] + '/' + parts[0] : value;
        }

        function syncPreview() {
            var name = [textOf(firstname), textOf(lastname)].filter(Boolean).join(' ');
            setText(pvName, name, 'Nombre del usuario');
            setText(pvEmail, textOf(email), 'correo@ejemplo.com');
            setText(pvPhone, textOf(phone), 'Sin teléfono');
            setText(
                pvDate,
                regDate && regDate.value ? 'Registro: ' + formatDate(regDate.value) : '',
                'Sin fecha de registro'
            );

            if (pvInitial) {
                pvInitial.textContent =
                    ((textOf(firstname).charAt(0) || '') + (textOf(lastname).charAt(0) || '')).toUpperCase() || '?';
            }

            var roleValue = role ? role.value : '';
            setBadge(pvRole, roleLabels[roleValue] || 'Sin rol', roleBadges[roleValue] || 'badge-neutral');

            var statusValue = status ? status.value : '';
            setBadge(pvStatus, statusLabels[statusValue] || 'Sin estado', statusBadges[statusValue] || 'badge-neutral');
        }

        /* --------------------- Medidor de contraseña --------------------- */
        var strengthBar = form.querySelector('[data-uf-strength]');
        var strengthLabel = form.querySelector('[data-uf-strength-label]');
        var matchEl = form.querySelector('[data-uf-match]');
        var ruleLength = form.querySelector('[data-uf-rule="length"]');
        var ruleUpper = form.querySelector('[data-uf-rule="upper"]');
        var ruleLower = form.querySelector('[data-uf-rule="lower"]');
        var ruleNumber = form.querySelector('[data-uf-rule="number"]');
        var ruleSpecial = form.querySelector('[data-uf-rule="special"]');

        function setRule(el, ok) {
            if (!el) return;
            el.classList.toggle('is-ok', ok);
            var icon = el.querySelector('i');
            if (icon) icon.className = 'fa-solid ' + (ok ? 'fa-check' : 'fa-circle');
        }

        function updatePassword() {
            var value = password ? password.value : '';
            var checks = {
                length: value.length >= 8,
                upper: /[A-ZÁÉÍÓÚÜÑ]/.test(value),
                lower: /[a-záéíóúüñ]/.test(value),
                number: /[0-9]/.test(value),
                special: /[^A-Za-z0-9]/.test(value),
            };

            setRule(ruleLength, checks.length);
            setRule(ruleUpper, checks.upper);
            setRule(ruleLower, checks.lower);
            setRule(ruleNumber, checks.number);
            setRule(ruleSpecial, checks.special);

            var score = 0;
            Object.keys(checks).forEach(function (key) {
                if (checks[key]) score++;
            });

            var percent = value ? Math.round((score / 5) * 100) : 0;

            if (strengthBar) {
                strengthBar.style.width = percent + '%';
                strengthBar.className =
                    'uf-strength__bar' +
                    (percent > 80 ? ' is-strong' : percent > 60 ? ' is-good' : percent > 40 ? ' is-fair' : '');
            }

            if (strengthLabel) {
                strengthLabel.textContent = !value
                    ? 'Sin contraseña'
                    : percent > 80
                      ? 'Muy fuerte'
                      : percent > 60
                        ? 'Fuerte'
                        : percent > 40
                          ? 'Buena'
                          : 'Débil';
            }
        }

        function updateMatch() {
            if (!matchEl) return;

            var value = password ? password.value : '';
            var repeated = confirm ? confirm.value : '';

            if (!value && !repeated) {
                matchEl.className = 'uf-match';
                matchEl.innerHTML = '<i class="fa-solid fa-circle"></i> Repite la contraseña';
                return;
            }

            var ok = value === repeated && value.length > 0;
            matchEl.className = 'uf-match ' + (ok ? 'is-ok' : 'is-error');
            matchEl.innerHTML =
                '<i class="fa-solid ' +
                (ok ? 'fa-circle-check' : 'fa-circle-xmark') +
                '"></i> ' +
                (ok ? 'Las contraseñas coinciden' : 'Las contraseñas no coinciden');
        }

        form.querySelectorAll('.uf-pass__toggle').forEach(function (button) {
            button.addEventListener('click', function () {
                var input = document.getElementById(button.getAttribute('data-target'));
                if (!input) return;

                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';

                var icon = button.querySelector('i');
                if (icon) icon.className = 'fa-solid ' + (show ? 'fa-eye-slash' : 'fa-eye');
                button.setAttribute('aria-label', show ? 'Ocultar contraseña' : 'Mostrar contraseña');
            });
        });

        if (password) {
            password.addEventListener('input', function () {
                password.setCustomValidity('');
                updatePassword();
                updateMatch();
            });
        }

        if (confirm) {
            confirm.addEventListener('input', function () {
                confirm.setCustomValidity('');
                updateMatch();
            });
        }

        form.addEventListener('submit', function (event) {
            var value = password ? password.value : '';
            var required = password && password.hasAttribute('required');

            if ((required || value) && value.length < 8) {
                event.preventDefault();
                password.setCustomValidity('La contraseña debe tener al menos 8 caracteres.');
                password.reportValidity();
                return;
            }

            if ((required || value) && value !== (confirm ? confirm.value : '')) {
                event.preventDefault();
                if (confirm) {
                    confirm.setCustomValidity('Las contraseñas no coinciden.');
                    confirm.reportValidity();
                }
                return;
            }

            if (password) password.setCustomValidity('');
            if (confirm) confirm.setCustomValidity('');

            if (submit) submit.classList.add('is-loading');
        });

        form.addEventListener('input', syncPreview);
        form.addEventListener('change', syncPreview);

        syncPreview();
        updatePassword();
        updateMatch();
    }

    /* ---------------------------------------------------------------------
       Filtros del listado de aliados (GET): búsqueda con retardo y select
       que se envía solo.
       --------------------------------------------------------------------- */
    function initFilters() {
        var form = document.querySelector('[data-cm-filters]');
        if (!form) return;

        var search = form.querySelector('input[name="q"]');
        var timer = null;

        if (search) {
            search.addEventListener('input', function () {
                clearTimeout(timer);
                timer = setTimeout(function () {
                    form.submit();
                }, 450);
            });
        }

        form.querySelectorAll('select').forEach(function (select) {
            select.addEventListener('change', function () {
                form.submit();
            });
        });
    }

    /* ---------------------------------------------------------------------
       Modal de confirmación de borrado
       --------------------------------------------------------------------- */
    function initDeleteModal() {
        var modal = document.getElementById('deleteModal');
        if (!modal) return;

        var nameEl = modal.querySelector('[data-delete-name]');
        var confirmBtn = modal.querySelector('[data-delete-confirm]');
        var pendingForm = null;
        var lastTrigger = null;

        function open(trigger) {
            lastTrigger = trigger;
            pendingForm = document.getElementById('delete-form-' + trigger.getAttribute('data-delete-id'));

            if (nameEl) nameEl.textContent = trigger.getAttribute('data-delete-name') || '';
            modal.hidden = false;
            document.documentElement.classList.add('rx-scroll-locked');
            if (confirmBtn) confirmBtn.focus();
        }

        function close() {
            modal.hidden = true;
            document.documentElement.classList.remove('rx-scroll-locked');
            pendingForm = null;
            if (lastTrigger) lastTrigger.focus();
        }

        document.querySelectorAll('[data-delete-trigger]').forEach(function (trigger) {
            trigger.addEventListener('click', function () {
                open(trigger);
            });
        });

        modal.querySelectorAll('[data-delete-cancel]').forEach(function (btn) {
            btn.addEventListener('click', close);
        });

        if (confirmBtn) {
            confirmBtn.addEventListener('click', function () {
                if (!pendingForm) return;
                confirmBtn.disabled = true;
                confirmBtn.classList.add('is-loading');
                pendingForm.submit();
            });
        }

        modal.addEventListener('click', function (event) {
            if (event.target === modal) close();
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !modal.hidden) close();
        });
    }
})();
