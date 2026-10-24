@extends('layouts.admin')

@section('title', 'Auditoría del sistema')

@section('page_title_toolbar', 'Auditoría del sistema')

@section('content')
    @php
        $summary = $audit['summary'] ?? [];
        $checks = $audit['checks'] ?? [];
        $checksByGroup = collect($checks)->groupBy('group');
        $recommendations = $audit['recommendations'] ?? [];
        $overall = $summary['overall'] ?? 'warning';
        $score = (int) ($summary['score'] ?? 0);

        $statusLabels = [
            'ok' => 'Correcto',
            'warning' => 'Atención',
            'critical' => 'Crítico',
            'info' => 'Informativo',
        ];
        $statusIcons = [
            'ok' => 'fa-circle-check',
            'warning' => 'fa-triangle-exclamation',
            'critical' => 'fa-circle-xmark',
            'info' => 'fa-circle-info',
        ];
        $groupIcons = [
            'Entorno' => 'fa-server',
            'Base de datos' => 'fa-database',
            'Almacenamiento' => 'fa-hard-drive',
            'Caché y colas' => 'fa-bolt',
            'Seguridad' => 'fa-shield-halved',
            'Integraciones' => 'fa-plug',
            'Registros' => 'fa-file-lines',
            'Rendimiento' => 'fa-gauge-high',
            'Sistema' => 'fa-microchip',
        ];
        $overallLabels = [
            'ok' => 'Sistema saludable',
            'warning' => 'Requiere atención',
            'critical' => 'Hay fallos críticos',
        ];
        $overallText = [
            'ok' => 'No se detectaron problemas críticos en esta revisión.',
            'warning' => 'Hay puntos que conviene corregir para evitar fallos futuros.',
            'critical' => 'Hay fallos que pueden afectar a usuarios, pagos o seguridad.',
        ];
    @endphp

    <div class="cm-page sa-page">

        {{-- ============================ CABECERA ============================ --}}
        <div class="page-header-block">
            <div>
                <h1 class="title-main">Auditoría del <span class="title-accent">sistema</span></h1>
                <p class="page-subtitle">
                    Estado técnico de la aplicación, recomendaciones priorizadas y correcciones seguras desde el backoffice.
                </p>
            </div>

            <div class="header-actions">
                <span class="cm-chip">
                    <i class="fa-solid fa-user-shield" aria-hidden="true"></i>
                    {{ $isOwnerRestricted ? 'Acceso restringido' : 'Solo administradores' }}
                </span>

                <form method="POST" action="{{ route('admin.system-audit.run') }}">
                    @csrf
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-play" aria-hidden="true"></i>
                        Ejecutar auditoría
                    </button>
                </form>

                <a href="{{ route('admin.system-audit.export') }}" class="btn btn-secondary">
                    <i class="fa-solid fa-download" aria-hidden="true"></i>
                    Exportar JSON
                </a>
            </div>
        </div>

        @if (session('audit_success'))
            <div class="sa-flash sa-flash--success" role="status">
                <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                <div>
                    <strong>{{ session('audit_success') }}</strong>
                    @if (session('audit_output'))
                        <pre class="sa-flash__output">{{ session('audit_output') }}</pre>
                    @endif
                </div>
                <button type="button" class="sa-flash__close" aria-label="Cerrar"
                    onclick="this.closest('.sa-flash').remove()">×</button>
            </div>
        @endif

        @if (session('audit_error'))
            <div class="sa-flash sa-flash--error" role="alert">
                <i class="fa-solid fa-circle-exclamation" aria-hidden="true"></i>
                <div>
                    <strong>{{ session('audit_error') }}</strong>
                    @if (session('audit_output'))
                        <pre class="sa-flash__output">{{ session('audit_output') }}</pre>
                    @endif
                </div>
                <button type="button" class="sa-flash__close" aria-label="Cerrar"
                    onclick="this.closest('.sa-flash').remove()">×</button>
            </div>
        @endif

        {{-- ============================ RESUMEN ============================ --}}
        <div class="sa-summary">
            <div class="sa-score sa-score--{{ $overall }}">
                <div class="sa-score__ring" style="--score: {{ $score }}%;">
                    <div class="sa-score__inner">
                        <strong>{{ $score }}</strong>
                        <span>/100</span>
                    </div>
                </div>
                <div class="sa-score__text">
                    <span class="sa-eyebrow">Estado general</span>
                    <h2>{{ $overallLabels[$overall] ?? 'Revisión' }}</h2>
                    <p>{{ $overallText[$overall] ?? '' }}</p>
                    <span class="sa-muted">
                        <i class="fa-regular fa-clock" aria-hidden="true"></i>
                        {{ \Carbon\Carbon::parse($audit['generated_at'] ?? now())->diffForHumans() }}
                        @if (!empty($audit['generated_by']))
                            · por {{ $audit['generated_by'] }}
                        @endif
                    </span>
                </div>
            </div>

            <div class="sa-stat sa-stat--ok">
                <span class="sa-stat__icon"><i class="fa-solid fa-circle-check" aria-hidden="true"></i></span>
                <div><strong>{{ $summary['ok'] ?? 0 }}</strong><span>Correctas</span></div>
            </div>
            <div class="sa-stat sa-stat--warning">
                <span class="sa-stat__icon"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i></span>
                <div><strong>{{ $summary['warning'] ?? 0 }}</strong><span>Advertencias</span></div>
            </div>
            <div class="sa-stat sa-stat--critical">
                <span class="sa-stat__icon"><i class="fa-solid fa-circle-xmark" aria-hidden="true"></i></span>
                <div><strong>{{ $summary['critical'] ?? 0 }}</strong><span>Críticas</span></div>
            </div>
        </div>

        <div class="sa-meta-strip">
            <span><i class="fa-brands fa-laravel" aria-hidden="true"></i> Laravel {{ $audit['environment']['laravel'] ?? '' }}</span>
            <span><i class="fa-brands fa-php" aria-hidden="true"></i> PHP {{ $audit['environment']['php'] ?? '' }}</span>
            <span><i class="fa-solid fa-database" aria-hidden="true"></i> {{ $audit['environment']['database'] ?? '' }}</span>
            <span><i class="fa-solid fa-bolt" aria-hidden="true"></i> Caché: {{ $audit['environment']['cache'] ?? '' }}</span>
            <span><i class="fa-solid fa-list-check" aria-hidden="true"></i> Cola: {{ $audit['environment']['queue'] ?? '' }}</span>
            <span><i class="fa-solid fa-key" aria-hidden="true"></i> Sesión: {{ $audit['environment']['session'] ?? '' }}</span>
        </div>

        @if ($isOwnerRestricted)
            <div class="sa-access-note">
                <i class="fa-solid fa-lock" aria-hidden="true"></i>
                <div>
                    <strong>Acceso restringido.</strong>
                    Solo estos correos pueden abrir la auditoría:
                    <span>{{ implode(', ', $allowedEmails) }}</span>
                </div>
            </div>
        @else
            <div class="sa-access-note sa-access-note--soft">
                <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                <div>
                    <strong>Visible para administradores.</strong>
                    Para limitarla a tu correo, define
                    <code>SYSTEM_AUDIT_EMAILS</code> en el <code>.env</code> de producción.
                </div>
            </div>
        @endif

        {{-- ============================ CONTENIDO ============================ --}}
        <div class="sa-layout">
            <aside class="sa-side">
                <div class="sa-side-card">
                    <h3><i class="fa-solid fa-layer-group" aria-hidden="true"></i> Grupos</h3>
                    <nav class="sa-group-nav" aria-label="Grupos de la auditoría">
                        @foreach ($checksByGroup as $group => $groupChecks)
                            @php
                                $groupCritical = $groupChecks->where('status', 'critical')->count();
                                $groupWarning = $groupChecks->where('status', 'warning')->count();
                            @endphp
                            <a href="#group-{{ \Illuminate\Support\Str::slug($group) }}">
                                <span>
                                    <i class="fa-solid {{ $groupIcons[$group] ?? 'fa-folder-tree' }}" aria-hidden="true"></i>
                                    {{ $group }}
                                </span>
                                <span class="sa-group-nav__counts">
                                    @if ($groupCritical)
                                        <em class="sa-dot sa-dot--critical">{{ $groupCritical }}</em>
                                    @elseif ($groupWarning)
                                        <em class="sa-dot sa-dot--warning">{{ $groupWarning }}</em>
                                    @else
                                        <em class="sa-dot sa-dot--ok"><i class="fa-solid fa-check" aria-hidden="true"></i></em>
                                    @endif
                                </span>
                            </a>
                        @endforeach
                    </nav>
                </div>

                <div class="sa-side-card">
                    <h3><i class="fa-solid fa-user-lock" aria-hidden="true"></i> Acceso</h3>
                    <p class="sa-side-help">Correos autorizados, separados por coma. Si lo dejas vacío, cualquier administrador podrá entrar.</p>

                    <form method="POST" action="{{ route('admin.system-audit.access') }}" class="sa-access-form">
                        @csrf
                        <label for="auditEmails" class="rx-sr-only">Correos autorizados</label>
                        <input type="text" id="auditEmails" name="emails" class="sa-access-input"
                            value="{{ implode(', ', $allowedEmails) }}"
                            placeholder="{{ $currentEmail ?: 'tu-correo@dominio.com' }}">
                        @error('emails')
                            <span class="sa-access-error">{{ $message }}</span>
                        @enderror
                        <button type="submit" class="sa-action-btn">
                            <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i>
                            Guardar acceso
                        </button>
                    </form>

                    <p class="sa-side-help" style="margin-top:.45rem;margin-bottom:0;">
                        Tu correo actual: <strong>{{ $currentEmail }}</strong>
                    </p>
                </div>

                <div class="sa-side-card">
                    <h3><i class="fa-solid fa-wand-magic-sparkles" aria-hidden="true"></i> Acciones seguras</h3>
                    <p class="sa-side-help">Solo mantenimiento. Las migraciones y cambios de <code>.env</code> se muestran como comando para revisarlos antes.</p>

                    @foreach ($actions as $actionKey => $action)
                        <form method="POST" action="{{ route('admin.system-audit.fix') }}" class="sa-action-form"
                            data-confirm="{{ $action['confirm'] }}">
                            @csrf
                            <input type="hidden" name="action" value="{{ $actionKey }}">
                            <button type="submit" class="sa-action-btn">
                                <i class="fa-solid {{ $actionKey === 'test_ai' ? 'fa-vial' : ($actionKey === 'storage_link' ? 'fa-link' : ($actionKey === 'queue_restart' ? 'fa-rotate' : 'fa-broom')) }}"
                                    aria-hidden="true"></i>
                                {{ $action['label'] }}
                            </button>
                        </form>
                    @endforeach
                </div>

                <div class="sa-side-card">
                    <h3><i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i> Historial</h3>
                    @forelse ($history as $entry)
                        <div class="sa-history-item">
                            <span class="sa-dot sa-dot--{{ $entry['summary']['overall'] ?? 'warning' }}"></span>
                            <div>
                                <strong>{{ $entry['summary']['score'] ?? 0 }}/100</strong>
                                <span>{{ \Carbon\Carbon::parse($entry['generated_at'] ?? now())->diffForHumans() }}</span>
                                @if (!empty($entry['generated_by']))
                                    <small>{{ $entry['generated_by'] }}</small>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="sa-side-help">Aún no hay ejecuciones guardadas.</p>
                    @endforelse
                </div>
            </aside>

            <div class="sa-content">
                @forelse ($checksByGroup as $group => $groupChecks)
                    <section class="sa-group" id="group-{{ \Illuminate\Support\Str::slug($group) }}">
                        <header class="sa-group__head">
                            <span class="sa-group__icon"><i class="fa-solid {{ $groupIcons[$group] ?? 'fa-folder-tree' }}" aria-hidden="true"></i></span>
                            <div>
                                <h2>{{ $group }}</h2>
                                <p>{{ $groupChecks->count() }} comprobación(es)</p>
                            </div>
                            <span class="sa-group__badge">
                                {{ $groupChecks->where('status', 'ok')->count() }} OK
                                @if ($groupChecks->where('status', 'critical')->count())
                                    · {{ $groupChecks->where('status', 'critical')->count() }} críticas
                                @elseif ($groupChecks->where('status', 'warning')->count())
                                    · {{ $groupChecks->where('status', 'warning')->count() }} avisos
                                @endif
                            </span>
                        </header>

                        <div class="sa-checks">
                            @foreach ($groupChecks as $check)
                                @php
                                    $status = $check['status'] ?? 'info';
                                    $fix = $check['fix'] ?? null;
                                    $fixAction = $fix ? ($actions[$fix] ?? null) : null;
                                @endphp
                                <article class="sa-check sa-check--{{ $status }}">
                                    <span class="sa-check__icon">
                                        <i class="fa-solid {{ $statusIcons[$status] ?? 'fa-circle-info' }}" aria-hidden="true"></i>
                                    </span>
                                    <div class="sa-check__body">
                                        <div class="sa-check__top">
                                            <h3>{{ $check['title'] ?? 'Comprobación' }}</h3>
                                            <span class="sa-badge sa-badge--{{ $status }}">{{ $statusLabels[$status] ?? $status }}</span>
                                        </div>

                                        <p class="sa-check__detail">{{ $check['detail'] ?? '' }}</p>

                                        <div class="sa-check__meta">
                                            <span class="sa-value">{{ $check['value'] ?? '' }}</span>
                                            @if (!empty($check['command']))
                                                <span class="sa-command">
                                                    <code>{{ $check['command'] }}</code>
                                                    <button type="button" class="sa-copy" data-copy="{{ $check['command'] }}"
                                                        aria-label="Copiar comando">
                                                        <i class="fa-regular fa-copy" aria-hidden="true"></i>
                                                    </button>
                                                </span>
                                            @endif
                                        </div>

                                        @if (!empty($check['recommendation']))
                                            <div class="sa-recommendation">
                                                <i class="fa-solid fa-lightbulb" aria-hidden="true"></i>
                                                <div>
                                                    <strong>Recomendación</strong>
                                                    <p>{{ $check['recommendation'] }}</p>
                                                </div>
                                            </div>
                                        @endif

                                        @if ($fixAction)
                                            <form method="POST" action="{{ route('admin.system-audit.fix') }}"
                                                class="sa-check__action"
                                                data-confirm="{{ $fixAction['confirm'] }}">
                                                @csrf
                                                <input type="hidden" name="action" value="{{ $fix }}">
                                                <button type="submit" class="btn btn-secondary btn-sm">
                                                    <i class="fa-solid fa-screwdriver-wrench" aria-hidden="true"></i>
                                                    {{ $fixAction['label'] }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @empty
                    <div class="sa-empty">
                        <i class="fa-solid fa-clipboard-check" aria-hidden="true"></i>
                        <h2>Sin comprobaciones</h2>
                        <p>Ejecuta la auditoría para obtener el estado del sistema.</p>
                    </div>
                @endforelse

                {{-- Plan de acción --}}
                <section class="sa-group sa-group--plan" id="plan-de-accion">
                    <header class="sa-group__head">
                        <span class="sa-group__icon"><i class="fa-solid fa-list-check" aria-hidden="true"></i></span>
                        <div>
                            <h2>Plan de acción</h2>
                            <p>Ordenado por prioridad: primero lo crítico.</p>
                        </div>
                        <span class="sa-group__badge">{{ count($recommendations) }} pendiente(s)</span>
                    </header>

                    @forelse ($recommendations as $recommendation)
                        <article class="sa-plan-item sa-plan-item--{{ $recommendation['status'] ?? 'warning' }}">
                            <span class="sa-plan-item__num">{{ $loop->iteration }}</span>
                            <div class="sa-plan-item__body">
                                <div class="sa-check__top">
                                    <h3>{{ $recommendation['title'] }}</h3>
                                    <span class="sa-badge sa-badge--{{ $recommendation['status'] ?? 'warning' }}">
                                        {{ $statusLabels[$recommendation['status'] ?? 'warning'] ?? 'Revisar' }}
                                    </span>
                                </div>
                                <p>{{ $recommendation['text'] }}</p>
                                @if (!empty($recommendation['command']))
                                    <div class="sa-command">
                                        <code>{{ $recommendation['command'] }}</code>
                                        <button type="button" class="sa-copy" data-copy="{{ $recommendation['command'] }}"
                                            aria-label="Copiar comando">
                                            <i class="fa-regular fa-copy" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="sa-plan-empty">
                            <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
                            No hay recomendaciones pendientes. El sistema está al día.
                        </div>
                    @endforelse
                </section>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            document.querySelectorAll('form[data-confirm]').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    var message = form.getAttribute('data-confirm') || '¿Continuar?';
                    if (!window.confirm(message)) {
                        event.preventDefault();
                    }
                });
            });

            document.querySelectorAll('[data-copy]').forEach(function (button) {
                button.addEventListener('click', function () {
                    var text = button.getAttribute('data-copy') || '';
                    var done = function () {
                        button.classList.add('is-copied');
                        button.innerHTML = '<i class="fa-solid fa-check" aria-hidden="true"></i>';
                        setTimeout(function () {
                            button.classList.remove('is-copied');
                            button.innerHTML = '<i class="fa-regular fa-copy" aria-hidden="true"></i>';
                        }, 1600);
                    };

                    if (navigator.clipboard && window.isSecureContext) {
                        navigator.clipboard.writeText(text).then(done);
                    } else {
                        var area = document.createElement('textarea');
                        area.value = text;
                        area.style.position = 'fixed';
                        area.style.opacity = '0';
                        document.body.appendChild(area);
                        area.select();
                        try { document.execCommand('copy'); } catch (e) {}
                        document.body.removeChild(area);
                        done();
                    }
                });
            });
        }());
    </script>
@endpush
