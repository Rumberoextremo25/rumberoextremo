<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'user_type' => ['required', 'string', 'in:user,partner'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'user_type.in' => 'Selecciona si te registras como Rumbero o como Aliado.',
        ]);

        $isAlly = $request->user_type === 'partner';

        // ------------------------------------------------------------------
        // ALIADO: no se crea la cuenta todavía. Se envía la solicitud a
        // Solicitudes (la crea ventas al aprobarla) y se confirma por correo.
        // ------------------------------------------------------------------
        if ($isAlly) {
            return $this->registerAllyRequest($request);
        }

        // ------------------------------------------------------------------
        // RUMBERO: cuenta de usuario activa de inmediato.
        // ------------------------------------------------------------------
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'user_type' => 'comun',
            'role' => 'comun',
            'status' => 'activo',
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false))
            ->with('success', '¡Bienvenido a Rumbero Extremo! Tu cuenta está activa.');
    }

    /**
     * Registro de un local: crea la solicitud, avisa a ventas y confirma al
     * usuario por correo. No se crea cuenta hasta que ventas la apruebe.
     */
    private function registerAllyRequest(Request $request): RedirectResponse
    {
        $email = mb_strtolower(trim((string) $request->email));
        $name = (string) $request->name;

        // La solicitud entra en la bandeja de Solicitudes como "pendiente"
        $contact = \App\Models\AllyContact::where('email', $email)->first();

        $payload = [
            'company_name' => $contact->company_name ?? $name,
            'contact_person' => $name,
            'email' => $email,
            'partnership_type' => 'other',
            'message' => 'Registro desde el formulario de registro (tipo: Aliado).',
            'status' => 'pendiente',
        ];

        if ($contact) {
            $contact->forceFill($payload + ['reviewed_at' => null, 'reviewed_by' => null])->save();
        } else {
            $contact = \App\Models\AllyContact::create($payload);
        }

        // Aviso interno: correo a ventas + notificación en la campana
        app(\App\Services\AllyRequestNotifier::class)->send('aliado', [
            'Empresa' => $contact->company_name,
            'Contacto' => $name,
            'Correo' => $email,
            'Origen' => 'Formulario de registro (/register)',
            'Mensaje' => $contact->message,
        ], $email, $name);

        // Correo de confirmación al usuario
        $thanks = function () use ($email, $name) {
            try {
                \Illuminate\Support\Facades\Mail::to($email)
                    ->send(new \App\Mail\AllyRegisterThanksMail($name));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('register.ally_thanks_failed', [
                    'error' => $e->getMessage(),
                ]);
            }
        };

        function_exists('defer') ? defer($thanks) : $thanks();

        return redirect()->route('login')->with(
            'status',
            '¡Gracias por registrarte, ' . $name . '! Enviamos tu solicitud al equipo de ventas, que te contactará pronto. También te escribimos a ' . $email . '.'
        );
    }
}
