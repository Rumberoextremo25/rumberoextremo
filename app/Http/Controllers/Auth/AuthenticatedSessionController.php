<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

class AuthenticatedSessionController extends Controller
{
    protected $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA;
    }

    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // ========== DEBUGGING ==========
        Log::emergency('=== WEB LOGIN CONTROLLER CALLED ===');
        Log::emergency('Request URL: '.$request->fullUrl());
        Log::emergency('Request Method: '.$request->method());
        Log::emergency('Expected JSON: '.($request->expectsJson() ? 'YES' : 'NO'));
        Log::emergency('Has web_request: '.($request->has('web_request') ? 'YES' : 'NO'));

        // Si la petición espera JSON, algo está mal
        if ($request->expectsJson()) {
            Log::warning('JSON request detected in web controller - redirecting to home');

            return redirect('/')->with('error', 'Petición no válida');
        }
        // ========== FIN DEBUGGING ==========

        // Validar credenciales
        $credentials = $request->only('email', 'password');

        if (! Auth::validate($credentials)) {
            Log::warning('Failed login attempt for email: '.$request->email);

            return back()->withErrors([
                'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
            ])->onlyInput('email');
        }

        $user = User::where('email', $request->email)->first();

        // Verificar si tiene 2FA activado
        if ($user->two_factor_enabled) {
            Log::info('2FA enabled for user: '.$user->email);
            session()->put('2fa:user:id', $user->id);
            session()->put('2fa:remember', $request->boolean('remember'));

            return redirect()->route('2fa.verify');
        }

        // Login normal sin 2FA
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        Log::info('Successful login for user: '.$user->email);
        Log::info('User type/role: '.($user->user_type ?? $user->role ?? 'unknown'));

        // 🔥 REDIRECCIÓN SIMPLE Y DIRECTA - ELIMINA LAS CONDICIONES COMPLEJAS
        // Siempre redirige a dashboard sin importar el rol
        return redirect('/dashboard');
    }

    /**
     * Show 2FA verification form.
     */
    public function showTwoFactorForm()
    {
        // Verificar que haya un usuario pendiente de 2FA
        if (! session()->has('2fa:user:id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor');
    }

    /**
     * Verify 2FA code and complete login.
     */
    public function verifyTwoFactor(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $userId = session()->get('2fa:user:id');

        if (! $userId) {
            return redirect()->route('login')->withErrors(['error' => 'Sesión expirada. Por favor, inicia sesión nuevamente.']);
        }

        $user = User::find($userId);

        if (! $user) {
            return redirect()->route('login')->withErrors(['error' => 'Usuario no encontrado.']);
        }

        // Verificar el código 2FA
        if (! $this->verifyTwoFactorCode($user, $request->code)) {
            return back()->withErrors(['code' => 'El código ingresado es inválido.']);
        }

        // Autenticar al usuario
        Auth::login($user, session()->get('2fa:remember', false));

        // Limpiar datos de sesión de 2FA
        session()->forget(['2fa:user:id', '2fa:remember']);

        // Regenerar sesión por seguridad
        $request->session()->regenerate();

        Log::info('2FA verified for user: '.$user->email);

        // Redirigir según el rol o a dashboard por defecto
        if ($user->role === 'admin') {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Verify 2FA code
     */
    private function verifyTwoFactorCode($user, $code)
    {
        $code = trim($code);
        $code = (string) $code;

        Log::info('Verifying 2FA code:', [
            'user' => $user->email,
            'code' => $code,
            'secret' => isset($user->two_factor_secret) ? substr($user->two_factor_secret, 0, 10).'...' : 'null',
        ]);

        // Verificar código de respaldo primero
        if ($user->two_factor_recovery_codes) {
            $backupCodes = json_decode($user->two_factor_recovery_codes, true);

            // Verificar que $backupCodes sea un array
            if (is_array($backupCodes) && count($backupCodes) > 0) {
                foreach ($backupCodes as &$backupCode) {
                    if (isset($backupCode['used']) && ! $backupCode['used'] && Hash::check($code, $backupCode['code'])) {
                        $backupCode['used'] = true;
                        $user->two_factor_recovery_codes = json_encode($backupCodes);
                        $user->save();
                        Log::info('Valid backup code used for user: '.$user->email);

                        return true;
                    }
                }
            }
        }

        // Verificar código TOTP
        try {
            if (isset($user->two_factor_secret) && $user->two_factor_secret) {
                $valid = $this->google2fa->verifyKey($user->two_factor_secret, $code, 4);
                Log::info('TOTP verification result for user '.$user->email.': '.($valid ? 'valid' : 'invalid'));

                return $valid;
            }
            Log::warning('No 2FA secret found for user: '.$user->email);

            return false;
        } catch (\Exception $e) {
            Log::error('Error verifying TOTP for user '.$user->email.': '.$e->getMessage());

            return false;
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();
        Log::info('User logged out: '.($user ? $user->email : 'Unknown'));

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
