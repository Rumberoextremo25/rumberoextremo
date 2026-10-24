<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SystemAuditService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SystemAuditController extends Controller
{
    public function __construct(private readonly SystemAuditService $audit)
    {
    }

    public function index()
    {
        $this->authorizeAuditor();

        // Se ejecuta en cada visita para que el panel muestre el estado
        // actual y no un informe viejo guardado en disco.
        $result = $this->audit->run();
        $history = $this->audit->history();
        $user = auth()->user();

        return view('Admin.system-audit.index', [
            'audit' => $result,
            'history' => array_slice($history, 0, 6),
            'actions' => SystemAuditService::FIX_ACTIONS,
            'allowedEmails' => $this->audit->allowedEmails(),
            'isOwnerRestricted' => $this->audit->allowedEmails() !== [],
            'currentEmail' => $user?->email,
            'criticalChecks' => collect($result['checks'] ?? [])->where('status', 'critical')->count(),
            'warningChecks' => collect($result['checks'] ?? [])->where('status', 'warning')->count(),
        ]);
    }

    public function run()
    {
        $this->authorizeAuditor();

        // El index vuelve a ejecutar la auditoría al cargar; no hace falta
        // ejecutarla dos veces (una aquí y otra al redirigir).
        return redirect()
            ->route('admin.system-audit.index')
            ->with('audit_success', 'Auditoría ejecutada. Revisa el estado actual del sistema.');
    }

    public function fix(Request $request)
    {
        $this->authorizeAuditor();

        $data = $request->validate([
            'action' => ['required', 'string', Rule::in(array_keys(SystemAuditService::FIX_ACTIONS))],
        ]);

        $result = $this->audit->fix($data['action']);

        // El index refresca el informe al cargar después del redirect.
        return redirect()
            ->route('admin.system-audit.index')
            ->with(
                $result['ok'] ? 'audit_success' : 'audit_error',
                $result['message']
            )
            ->with('audit_output', $result['output'] ?? null);
    }

    public function updateAccess(Request $request)
    {
        $this->authorizeAuditor();

        $data = $request->validate([
            'emails' => ['nullable', 'string', 'max:1000'],
        ]);

        $raw = trim((string) ($data['emails'] ?? ''));

        if ($raw === '') {
            \App\Models\Setting::put('system_audit_emails', '');

            return redirect()
                ->route('admin.system-audit.index')
                ->with('audit_success', 'Restricción eliminada: cualquier administrador puede ver la auditoría.');
        }

        $emails = collect(explode(',', $raw))
            ->map(static fn ($email) => strtolower(trim($email)))
            ->filter(static fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL) !== false)
            ->unique()
            ->values();

        if ($emails->isEmpty()) {
            return redirect()
                ->route('admin.system-audit.index')
                ->with('audit_error', 'No hay correos válidos en la lista.');
        }

        $current = strtolower((string) auth()->user()?->email);

        // Nunca dejar fuera al usuario que está configurando el acceso.
        if ($current !== '' && ! $emails->contains($current)) {
            $emails->push($current);
        }

        \App\Models\Setting::put('system_audit_emails', $emails->implode(','));

        return redirect()
            ->route('admin.system-audit.index')
            ->with('audit_success', 'Acceso actualizado. Solo estos correos pueden abrir la auditoría: ' . $emails->implode(', '));
    }

    public function export()
    {
        $this->authorizeAuditor();

        // Se ejecuta en cada visita para que el panel muestre el estado
        // actual y no un informe viejo guardado en disco.
        $result = $this->audit->run();
        $filename = 'auditoria-sistema-' . now()->format('Y-m-d-His') . '.json';

        return response()->streamDownload(function () use ($result) {
            echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
        }, $filename, [
            'Content-Type' => 'application/json; charset=UTF-8',
        ]);
    }

    private function authorizeAuditor(): void
    {
        $allowed = $this->audit->allowedEmails();

        if ($allowed === []) {
            return;
        }

        $email = strtolower((string) auth()->user()?->email);

        if (! in_array($email, $allowed, true)) {
            abort(403, 'La auditoría del sistema está restringida a los correos autorizados.');
        }
    }
}
