<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AffiliateApplication;
use App\Models\Ally;
use App\Models\AllyContact;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Solicitudes que llegan desde la web: locales (aliados comerciales) y
 * afiliados (rumberos). Permite revisarlas, cambiar su estado y decidir a qué
 * correo se envían los avisos de ventas.
 */
class RequestController extends Controller
{
    public const STATUSES = [
        'pendiente' => 'Pendiente',
        'en_revision' => 'En revisión',
        'contactado' => 'Contactado',
        'aceptado' => 'Aceptado',
        'descartado' => 'Descartado',
    ];

    public function index(Request $request): View
    {
        $type = $request->query('tipo', 'locales'); // locales | afiliados
        $status = $request->query('estado');
        $search = trim((string) $request->query('buscar'));

        if ($type === 'afiliados') {
            $query = AffiliateApplication::query();
            $searchColumn = 'full_name';
            $emailColumn = 'email';
        } else {
            $type = 'locales';
            $query = AllyContact::query();
            $searchColumn = 'company_name';
            $emailColumn = 'email';
        }

        if ($status && array_key_exists($status, self::STATUSES)) {
            $query->where('status', $status);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search, $searchColumn, $emailColumn) {
                $q->where($searchColumn, 'like', "%{$search}%")
                    ->orWhere($emailColumn, 'like', "%{$search}%");
            });
        }

        $requests = $query->latest()->paginate(15)->withQueryString();

        return view('Admin.requests.index', [
            'type' => $type,
            'requests' => $requests,
            'statuses' => self::STATUSES,
            'salesEmail' => Setting::get('sales_email', 'soporteitsolutech@gmail.com'),
            'stats' => [
                'locales' => AllyContact::count(),
                'locales_pendientes' => AllyContact::where('status', 'pendiente')->count(),
                'afiliados' => AffiliateApplication::count(),
                'afiliados_pendientes' => AffiliateApplication::where('status', 'pendiente')->count(),
            ],
            'status' => $status,
            'search' => $search,
            'canManage' => $this->canManage(),
        ]);
    }

    /**
     * ¿Puede el usuario actual modificar solicitudes?
     *
     * Solo con rol `admin` estricto: tener `user_type = admin` (o ser aliado)
     * permite consultar, pero nunca aprobar ni cambiar el correo de ventas.
     */
    private function canManage(): bool
    {
        $user = Auth::user();

        return $user !== null && ($user->role ?? null) === 'admin';
    }

    /**
     * Corta la petición si el usuario no es administrador.
     */
    private function ensureAdmin(): void
    {
        if (! $this->canManage()) {
            Log::warning('security.requests_modify_denied', [
                'user_id' => Auth::id(),
                'role' => Auth::user()->role ?? null,
                'path' => request()->path(),
            ]);

            abort(403, 'Solo un administrador puede modificar las solicitudes.');
        }
    }

    /**
     * Cambia el estado de una solicitud (solo rol admin).
     */
    public function updateStatus(Request $request, string $type, int $id): RedirectResponse
    {
        $this->ensureAdmin();

        $data = $request->validate([
            'status' => ['required', 'string', array_key_exists($request->input('status'), self::STATUSES) ? 'in:' . implode(',', array_keys(self::STATUSES)) : 'in:pendiente'],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $model = $type === 'afiliados'
            ? AffiliateApplication::findOrFail($id)
            : AllyContact::findOrFail($id);

        $wasApproved = ($model->status ?? null) === 'aceptado';
        $message = 'Solicitud marcada como "' . self::STATUSES[$data['status']] . '".';

        try {
            DB::transaction(function () use ($model, $data, $type, $wasApproved, &$message) {
                $model->forceFill([
                    'status' => $data['status'],
                    'admin_notes' => $data['admin_notes'] ?? $model->admin_notes,
                    'reviewed_at' => now(),
                    'reviewed_by' => Auth::id(),
                ])->save();

                // Al aprobar se crea el aliado o el usuario correspondiente
                if ($data['status'] === 'aceptado' && ! $wasApproved) {
                    $message = $type === 'afiliados'
                        ? $this->approveAffiliate($model)
                        : $this->approveAlly($model);
                }
            });
        } catch (\Throwable $e) {
            Log::error('requests.approve_failed', [
                'type' => $type,
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'No se pudo aprobar la solicitud: ' . $e->getMessage());
        }

        return back()->with('success', $message);
    }

    /**
     * Aprueba una solicitud de local: crea el usuario (rol aliado) y su ficha
     * en `allies`. Si ya existen, los reutiliza en vez de duplicar.
     */
    private function approveAlly(AllyContact $contact): string
    {
        $email = mb_strtolower(trim($contact->email));

        // 1) Usuario
        $user = User::where('email', $email)->first();
        $userCreated = false;

        if (! $user) {
            $user = User::create([
                'name' => $contact->contact_person,
                'firstname' => $contact->contact_person,
                'email' => $email,
                'password' => Hash::make(Str::random(24)),
                'role' => 'aliado',
                'user_type' => 'aliado',
                'status' => 'pendiente',
                'is_ally' => true,
                'phone1' => $contact->phone,
                'website_url' => $contact->website,
                'allied_company_name' => $contact->company_name,
                'allied_registered_at' => now(),
                'registration_date' => now()->toDateString(),
                'notes' => 'Creado al aprobar la solicitud de local #' . $contact->id,
            ]);
            $userCreated = true;
        }

        // 2) Ficha de aliado
        $ally = Ally::where('user_id', $user->id)->first();
        $allyCreated = false;

        if (! $ally) {
            Ally::create([
                'user_id' => $user->id,
                'company_name' => $contact->company_name,
                'contact_person_name' => $contact->contact_person,
                'contact_email' => $email,
                'contact_phone' => $contact->phone,
                'website_url' => $contact->website,
                'description' => $contact->message,
                'status' => 'pendiente',
                'registered_at' => now(),
                'notes' => 'Origen: formulario de aliados (solicitud #' . $contact->id . '). Tipo de alianza: ' . $contact->partnership_type,
            ]);
            $allyCreated = true;
        }

        if (! $allyCreated) {
            return 'La solicitud fue aprobada. El aliado ya existía, no se duplicó.';
        }

        $parts = ['Solicitud aprobada. Aliado creado: ' . $contact->company_name];
        $parts[] = $userCreated ? 'con su usuario (' . $email . ')' : 'vinculado al usuario existente (' . $email . ')';
        $parts[] = 'Queda en estado pendiente hasta que actives su cuenta en Usuarios.';

        return implode(' ', $parts);
    }

    /**
     * Aprueba una solicitud de rumbero/afiliado: crea la cuenta en `users`.
     */
    private function approveAffiliate(AffiliateApplication $application): string
    {
        $email = mb_strtolower(trim($application->email));

        if ($user = User::where('email', $email)->first()) {
            $user->forceFill([
                'status' => 'activo',
                'notes' => trim(($user->notes ?? '') . "\nAprobado desde solicitud de afiliado #" . $application->id),
            ])->save();

            return 'Solicitud aprobada. El usuario ya existía (' . $email . ') y se activó.';
        }

        User::create([
            'name' => $application->full_name,
            'firstname' => $application->full_name,
            'email' => $email,
            'password' => Hash::make(Str::random(24)),
            'role' => 'comun',
            'user_type' => 'comun',
            'status' => 'activo',
            'phone1' => $application->phone,
            'registration_date' => now()->toDateString(),
            'notes' => 'Creado al aprobar la solicitud de afiliado #' . $application->id
                . '. Red: ' . $application->social_media_link
                . ' (' . number_format((int) $application->followers_count) . ' seguidores).',
        ]);

        return 'Solicitud aprobada. Usuario creado: ' . $application->full_name . ' (' . $email . ').';
    }

    /**
     * Guarda el correo que recibe los avisos de ventas.
     */
    public function updateSalesEmail(Request $request): RedirectResponse
    {
        $this->ensureAdmin();

        $data = $request->validate([
            'sales_email' => ['required', 'email', 'max:180'],
        ]);

        Setting::put('sales_email', $data['sales_email']);

        return back()->with('success', 'Correo de ventas actualizado a ' . $data['sales_email'] . '.');
    }
}
