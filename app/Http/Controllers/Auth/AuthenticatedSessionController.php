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
        $this->google2fa = new Google2FA();
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
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Validar credenciales
        $credentials = $request->only('email', 'password');

        if (!Auth::validate($credentials)) {
            return back()->withErrors([
                'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
            ])->onlyInput('email');
        }

        $user = User::where('email', $request->email)->first();

        // Verificar si tiene 2FA activado
        if ($user->two_factor_enabled) {
            session()->put('2fa:user:id', $user->id);
            session()->put('2fa:remember', $request->boolean('remember'));

            return redirect()->route('2fa.verify');
        }

        // Login normal sin 2FA
        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Show 2FA verification form.
     */
    public function showTwoFactorForm()
    {
        // Verificar que haya un usuario pendiente de 2FA
        if (!session()->has('2fa:user:id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor');
    }

    /**
     * Verify 2FA code and complete login.
     */
    /**
     * Verify 2FA code and complete login.
     */
    public function verifyTwoFactor(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $userId = session()->get('2fa:user:id');

        if (!$userId) {
            return redirect()->route('login')->withErrors(['error' => 'Sesión expirada. Por favor, inicia sesión nuevamente.']);
        }

        $user = User::find($userId);

        if (!$user) {
            return redirect()->route('login')->withErrors(['error' => 'Usuario no encontrado.']);
        }

        // Verificar el código 2FA
        if (!$this->verifyTwoFactorCode($user, $request->code)) {
            return back()->withErrors(['code' => 'El código ingresado es inválido.']);
        }

        // Autenticar al usuario
        Auth::login($user, session()->get('2fa:remember', false));

        // Limpiar datos de sesión de 2FA
        session()->forget(['2fa:user:id', '2fa:remember']);

        // Regenerar sesión por seguridad
        $request->session()->regenerate();

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
            'code' => $code,
            'secret' => substr($user->two_factor_secret, 0, 10) . '...'
        ]);

        // Verificar código de respaldo primero
        if ($user->two_factor_recovery_codes) {
            $backupCodes = json_decode($user->two_factor_recovery_codes, true);

            foreach ($backupCodes as &$backupCode) {
                if (!$backupCode['used'] && Hash::check($code, $backupCode['code'])) {
                    $backupCode['used'] = true;
                    $user->two_factor_recovery_codes = json_encode($backupCodes);
                    $user->save();
                    Log::info('Valid backup code used');
                    return true;
                }
            }
        }

        // Verificar código TOTP
        try {
            $valid = $this->google2fa->verifyKey($user->two_factor_secret, $code, 4);
            Log::info('TOTP verification result: ' . ($valid ? 'valid' : 'invalid'));
            return $valid;
        } catch (\Exception $e) {
            Log::error('Error verifying TOTP: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
