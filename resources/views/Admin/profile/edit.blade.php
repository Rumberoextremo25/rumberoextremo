{{-- resources/views/Admin/profile/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Editar perfil')

@section('page_title_toolbar', 'Editar perfil')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/pages.css') }}">
@endpush

@section('content')
    @php
        $u = Auth::user();
        $hasPhoto = $u->profile_photo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($u->profile_photo_path);
    @endphp

    <div class="pf-page">

        {{-- ============================ CABECERA ============================ --}}
        <div class="page-header-block">
            <div>
                <h1 class="title-main">Editar <span class="title-accent">perfil</span></h1>
                <p class="page-subtitle">Actualiza tus datos, tu foto y tu contraseña.</p>
            </div>

            <a href="{{ route('profile') }}" class="btn btn-secondary">
                <i class="fa-solid fa-arrow-left"></i> Volver al perfil
            </a>
        </div>

        {{-- ============================== AVISOS ============================== --}}
        @if (session('success'))
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i>
                <div class="alert-content">{{ session('success') }}</div>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <div class="alert-content">{{ session('error') }}</div>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">
                <i class="fa-solid fa-circle-exclamation"></i>
                <div class="alert-content">
                    <strong>Revisa los siguientes campos:</strong>
                    <ul style="margin:.35rem 0 0;padding-left:1.1rem;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- ============================== FORM ============================== --}}
        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="pf-edit">
            @csrf
            @method('PUT')

            <div class="pf-edit__grid">

                {{-- ---------------- Columna: foto + resumen ---------------- --}}
                <aside class="pf-edit__aside">
                    <div class="pf-card">
                        <div class="pf-card__head">
                            <i class="fa-solid fa-camera"></i>
                            <h3>Foto de perfil</h3>
                        </div>

                        <div class="pf-edit__photo">
                            <div class="pf-avatar pf-avatar--edit">
                                @if ($hasPhoto)
                                    <img id="avatar_preview"
                                        src="{{ \Illuminate\Support\Facades\Storage::url($u->profile_photo_path) }}"
                                        alt="Foto de {{ $u->name }}">
                                @else
                                    <img id="avatar_preview"
                                        src="{{ asset('assets/img/dashboard/logo_perfil.png') }}"
                                        alt="Avatar de {{ $u->name }}">
                                @endif
                            </div>

                            <label for="profile_photo_upload" class="btn btn-secondary pf-edit__photo-btn">
                                <i class="fa-solid fa-upload"></i> Seleccionar imagen
                            </label>
                            <input type="file" id="profile_photo_upload" name="profile_photo_upload" accept="image/*"
                                hidden>

                            <span class="pf-upload__hint">JPG, PNG, GIF o WEBP · máx. 15 MB</span>

                            @error('profile_photo_upload')
                                <span class="px-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="pf-card">
                        <div class="pf-card__head">
                            <i class="fa-solid fa-shield-halved"></i>
                            <h3>Seguridad</h3>
                        </div>

                        <div class="pf-edit__body">
                            <p class="pf-edit__note">
                                Para guardar cualquier cambio debes escribir tu
                                <strong>contraseña actual</strong>.
                            </p>

                            <div class="form-group" style="margin-bottom:.9rem;">
                                <label for="current_password">Contraseña actual <span
                                        class="required-star">*</span></label>
                                <div class="pf-pass">
                                    <input type="password" id="current_password" name="current_password"
                                        class="form-control" placeholder="Tu contraseña actual" required
                                        autocomplete="current-password">
                                    <button type="button" class="pf-pass__toggle toggle-password"
                                        data-target="current_password" aria-label="Mostrar contraseña">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </div>
                                @error('current_password')
                                    <span class="px-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="pf-pass__divider">Cambiar contraseña (opcional)</div>

                            <div class="form-group" style="margin-bottom:.9rem;">
                                <label for="new_password">Nueva contraseña</label>
                                <div class="pf-pass">
                                    <input type="password" id="new_password" name="new_password" class="form-control"
                                        placeholder="Déjala vacía para no cambiarla" autocomplete="new-password">
                                    <button type="button" class="pf-pass__toggle toggle-password"
                                        data-target="new_password" aria-label="Mostrar contraseña">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </div>
                                <span class="field-hint">Mínimo 8 caracteres.</span>
                                @error('new_password')
                                    <span class="px-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group" style="margin-bottom:0;">
                                <label for="new_password_confirmation">Repetir nueva contraseña</label>
                                <div class="pf-pass">
                                    <input type="password" id="new_password_confirmation" name="new_password_confirmation"
                                        class="form-control" placeholder="Repite la contraseña"
                                        autocomplete="new-password">
                                    <button type="button" class="pf-pass__toggle toggle-password"
                                        data-target="new_password_confirmation" aria-label="Mostrar contraseña">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                </div>
                                <div class="pf-pass__match" id="passwordMatchFeedback"></div>
                                @error('new_password_confirmation')
                                    <span class="px-error">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </aside>

                {{-- ---------------- Columna: datos ---------------- --}}
                <div class="pf-edit__main">

                    {{-- Datos personales --}}
                    <section class="pf-card">
                        <div class="pf-card__head">
                            <i class="fa-solid fa-user"></i>
                            <h3>Información personal</h3>
                        </div>

                        <div class="pf-edit__body">
                            <div class="pf-form-grid">
                                <div class="form-group">
                                    <label for="name">Nombre completo <span class="required-star">*</span></label>
                                    <input type="text" id="name" name="name" class="form-control"
                                        value="{{ old('name', $u->name) }}" placeholder="Ej: Juan Pérez" required>
                                    @error('name')
                                        <span class="px-error">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="email">Correo electrónico <span class="required-star">*</span></label>
                                    <input type="email" id="email" name="email" class="form-control"
                                        value="{{ old('email', $u->email) }}" placeholder="ejemplo@correo.com" required>
                                    @error('email')
                                        <span class="px-error">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="identification">Cédula / RIF <span class="required-star">*</span></label>
                                    <input type="text" id="identification" name="identification" class="form-control"
                                        value="{{ old('identification', $u->identification) }}"
                                        placeholder="V-12345678 o J-123456789" required>
                                    @error('identification')
                                        <span class="px-error">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="dob">Fecha de nacimiento</label>
                                    <input type="date" id="dob" name="dob" class="form-control"
                                        value="{{ old('dob', $u->dob ? \Illuminate\Support\Carbon::parse($u->dob)->format('Y-m-d') : '') }}">
                                    @error('dob')
                                        <span class="px-error">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- Contacto --}}
                    <section class="pf-card">
                        <div class="pf-card__head">
                            <i class="fa-solid fa-address-book"></i>
                            <h3>Contacto</h3>
                        </div>

                        <div class="pf-edit__body">
                            <div class="pf-form-grid">
                                <div class="form-group">
                                    <label for="phone1">Teléfono principal <span class="required-star">*</span></label>
                                    <input type="tel" id="phone1" name="phone1" class="form-control"
                                        value="{{ old('phone1', $u->phone1) }}" placeholder="+58 412 1234567" required>
                                    @error('phone1')
                                        <span class="px-error">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="phone2">Teléfono adicional</label>
                                    <input type="tel" id="phone2" name="phone2" class="form-control"
                                        value="{{ old('phone2', $u->phone2) }}" placeholder="+58 414 7654321">
                                    @error('phone2')
                                        <span class="px-error">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group pf-form-grid__full">
                                    <label for="address">Dirección <span class="required-star">*</span></label>
                                    <textarea id="address" name="address" rows="3" class="form-control"
                                        placeholder="Calle, número, ciudad, estado" required>{{ old('address', $u->address) }}</textarea>
                                    @error('address')
                                        <span class="px-error">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- Datos de aliado --}}
                    @if ($u->is_ally)
                        <section class="pf-card">
                            <div class="pf-card__head">
                                <i class="fa-solid fa-store"></i>
                                <h3>Datos del local</h3>
                            </div>

                            <div class="pf-edit__body">
                                <div class="pf-form-grid">
                                    <div class="form-group">
                                        <label for="allied_company_name">Empresa / razón social</label>
                                        <input type="text" id="allied_company_name" name="allied_company_name"
                                            class="form-control"
                                            value="{{ old('allied_company_name', $u->allied_company_name) }}">
                                        @error('allied_company_name')
                                            <span class="px-error">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="allied_company_rif">RIF</label>
                                        <input type="text" id="allied_company_rif" name="allied_company_rif"
                                            class="form-control"
                                            value="{{ old('allied_company_rif', $u->allied_company_rif) }}">
                                        @error('allied_company_rif')
                                            <span class="px-error">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="service_category">Categoría de servicio</label>
                                        <input type="text" id="service_category" name="service_category"
                                            class="form-control"
                                            value="{{ old('service_category', $u->service_category) }}">
                                        @error('service_category')
                                            <span class="px-error">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="website_url">Sitio web</label>
                                        <input type="url" id="website_url" name="website_url" class="form-control"
                                            value="{{ old('website_url', $u->website_url) }}" placeholder="https://…">
                                        @error('website_url')
                                            <span class="px-error">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="discount">Descuento (%)</label>
                                        <input type="number" id="discount" name="discount" class="form-control"
                                            min="0" max="100" value="{{ old('discount', $u->discount) }}">
                                        @error('discount')
                                            <span class="px-error">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </section>
                    @endif

                    {{-- Acciones --}}
                    <div class="pf-edit__actions">
                        <a href="{{ route('profile') }}" class="btn btn-secondary">
                            <i class="fa-solid fa-xmark"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary pf-edit__save" data-no-guard>
                            <i class="fa-solid fa-floppy-disk"></i> Guardar cambios
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const MAX = 15 * 1024 * 1024;

            // ---------- Vista previa de la foto ----------
            const upload = document.getElementById('profile_photo_upload');
            const preview = document.getElementById('avatar_preview');

            upload?.addEventListener('change', function() {
                const file = this.files && this.files[0];
                if (!file) return;

                if (file.size > MAX) {
                    alert('La imagen pesa ' + (file.size / 1048576).toFixed(1) + ' MB. El máximo es 15 MB.');
                    this.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = (e) => {
                    if (preview) preview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            });

            // ---------- Mostrar / ocultar contraseña ----------
            document.querySelectorAll('.toggle-password').forEach(btn => {
                btn.addEventListener('click', function() {
                    const input = document.getElementById(this.dataset.target);
                    if (!input) return;
                    const icon = this.querySelector('i');
                    const show = input.type === 'password';
                    input.type = show ? 'text' : 'password';
                    if (icon) icon.className = show ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
                });
            });

            // ---------- Coincidencia de contraseñas ----------
            const np = document.getElementById('new_password');
            const cp = document.getElementById('new_password_confirmation');
            const feedback = document.getElementById('passwordMatchFeedback');

            const check = () => {
                if (!feedback || !np || !cp) return;
                if (!np.value && !cp.value) {
                    feedback.textContent = '';
                    feedback.className = 'pf-pass__match';
                    return;
                }
                const ok = np.value === cp.value;
                feedback.innerHTML = ok ?
                    '<i class="fa-solid fa-circle-check"></i> Las contraseñas coinciden' :
                    '<i class="fa-solid fa-circle-xmark"></i> Las contraseñas no coinciden';
                feedback.className = 'pf-pass__match ' + (ok ? 'is-ok' : 'is-error');
            };

            np?.addEventListener('input', check);
            cp?.addEventListener('input', check);
        });
    </script>
@endpush
