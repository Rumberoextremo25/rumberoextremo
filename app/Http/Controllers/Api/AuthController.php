<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;

class AuthController extends Controller
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Register a new user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'user_type' => ['required', 'string', 'in:user,partner'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],

            // Datos que envía la app Android además de las credenciales.
            'firstname' => ['sometimes', 'nullable', 'string', 'max:120'],
            'lastname' => ['sometimes', 'nullable', 'string', 'max:120'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'identification' => ['sometimes', 'nullable', 'string', 'max:50', 'unique:users,identification'],
            'address' => ['sometimes', 'nullable', 'string', 'max:500'],
            'dob' => ['sometimes', 'nullable', 'date'],
        ]);

        $firstname = $validated['firstname'] ?? null;
        $lastname = $validated['lastname'] ?? null;
        $fullName = trim(($firstname ?? '') . ' ' . ($lastname ?? ''));

        $user = User::create([
            'name' => $validated['name'],
            'firstname' => $firstname,
            'lastname' => $lastname,
            'full_name' => $fullName !== '' ? $fullName : $validated['name'],
            'email' => $validated['email'],
            'user_type' => $validated['user_type'],
            'password' => Hash::make($validated['password']),
            'phone1' => $validated['phone'] ?? null,
            'identification' => $validated['identification'] ?? null,
            'address' => $validated['address'] ?? null,
            'dob' => $validated['dob'] ?? null,
            'status' => 'activo',
        ]);

        event(new Registered($user));

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Usuario registrado exitosamente',
            'data' => [
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
            ]
        ], 201);
    }

    /**
     * Login user and create token.
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        // Validar credenciales
        $credentials = $request->only('email', 'password');

        if (!Auth::validate($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
                'errors' => [
                    'email' => ['Las credenciales son incorrectas.']
                ]
            ], 401);
        }

        $user = User::where('email', $request->email)->first();

        // Verificar si tiene 2FA activado
        if ($user->two_factor_enabled) {
            // Generar token temporal para 2FA
            $tempToken = Str::random(64);
            cache()->put("2fa:token:{$tempToken}", [
                'user_id' => $user->id,
                'remember' => $request->boolean('remember')
            ], now()->addMinutes(10));

            return response()->json([
                'success' => true,
                'requires_2fa' => true,
                'message' => 'Se requiere autenticación de dos factores',
                'data' => [
                    'temp_token' => $tempToken,
                    'user_id' => $user->id
                ]
            ]);
        }

        // Login normal sin 2FA
        Auth::login($user, $request->boolean('remember'));
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Inicio de sesión exitoso',
            'data' => [
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
            ]
        ]);
    }

    /**
     * Verify 2FA code and complete login.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function verifyTwoFactor(Request $request): JsonResponse
    {
        $request->validate([
            'temp_token' => ['required', 'string'],
            'code' => ['required', 'string', 'size:6'],
        ]);

        // Obtener datos temporales
        $tempData = cache()->get("2fa:token:{$request->temp_token}");

        if (!$tempData) {
            return response()->json([
                'success' => false,
                'message' => 'Sesión expirada. Por favor, inicia sesión nuevamente.',
            ], 401);
        }

        $user = User::find($tempData['user_id']);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado.',
            ], 404);
        }

        // Verificar el código 2FA
        if (!$this->verifyTwoFactorCode($user, $request->code)) {
            return response()->json([
                'success' => false,
                'message' => 'El código ingresado es inválido.',
                'errors' => [
                    'code' => ['Código de verificación incorrecto.']
                ]
            ], 401);
        }

        // Autenticar al usuario
        Auth::login($user, $tempData['remember']);

        // Limpiar datos temporales
        cache()->forget("2fa:token:{$request->temp_token}");

        // Crear token de acceso
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Autenticación exitosa',
            'data' => [
                'user' => $user,
                'token' => $token,
                'token_type' => 'Bearer',
            ]
        ]);
    }

    /**
     * Verify 2FA code (TOTP or backup code).
     *
     * @param User $user
     * @param string $code
     * @return bool
     */
    private function verifyTwoFactorCode(User $user, string $code): bool
    {
        $code = trim($code);

        // Verificar código de respaldo primero
        if ($user->two_factor_recovery_codes) {
            $backupCodes = json_decode($user->two_factor_recovery_codes, true);
            
            if (is_array($backupCodes) && count($backupCodes) > 0) {
                foreach ($backupCodes as &$backupCode) {
                    if (isset($backupCode['used']) && !$backupCode['used'] && Hash::check($code, $backupCode['code'])) {
                        $backupCode['used'] = true;
                        $user->two_factor_recovery_codes = json_encode($backupCodes);
                        $user->save();
                        return true;
                    }
                }
            }
        }

        // Verificar código TOTP
        try {
            if (isset($user->two_factor_secret) && $user->two_factor_secret) {
                return $this->google2fa->verifyKey($user->two_factor_secret, $code, 4);
            }
            return false;
        } catch (\Exception $e) {
            \Log::error('Error verifying TOTP: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Enable 2FA for authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function enableTwoFactor(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no autenticado',
            ], 401);
        }

        // Generar secreto 2FA
        $secret = $this->google2fa->generateSecretKey();
        
        // Guardar secreto temporalmente hasta verificar
        $request->session()->put('2fa:temp_secret', $secret);
        
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        return response()->json([
            'success' => true,
            'message' => 'Código QR generado exitosamente',
            'data' => [
                'secret' => $secret,
                'qr_code_url' => $qrCodeUrl,
            ]
        ]);
    }

    /**
     * Confirm and enable 2FA with verification code.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function confirmTwoFactor(Request $request): JsonResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $user = $request->user();
        $secret = $request->session()->get('2fa:temp_secret');

        if (!$secret) {
            return response()->json([
                'success' => false,
                'message' => 'No hay un proceso de activación de 2FA en curso',
            ], 400);
        }

        // Verificar el código
        if (!$this->google2fa->verifyKey($secret, $request->code)) {
            return response()->json([
                'success' => false,
                'message' => 'Código de verificación inválido',
                'errors' => [
                    'code' => ['El código ingresado es incorrecto.']
                ]
            ], 422);
        }

        // Habilitar 2FA
        $user->two_factor_enabled = true;
        $user->two_factor_secret = $secret;
        
        // Generar códigos de respaldo
        $backupCodes = $this->generateBackupCodes();
        $user->two_factor_recovery_codes = json_encode($backupCodes);
        $user->save();

        // Limpiar sesión
        $request->session()->forget('2fa:temp_secret');

        return response()->json([
            'success' => true,
            'message' => 'Autenticación de dos factores activada exitosamente',
            'data' => [
                'backup_codes' => $backupCodes,
            ]
        ]);
    }

    /**
     * Disable 2FA for authenticated user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function disableTwoFactor(Request $request): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        
        $user->two_factor_enabled = false;
        $user->two_factor_secret = null;
        $user->two_factor_recovery_codes = null;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Autenticación de dos factores desactivada exitosamente',
        ]);
    }

    /**
     * Generate backup codes for 2FA.
     *
     * @return array
     */
    private function generateBackupCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = [
                'code' => Hash::make(Str::random(10)),
                'used' => false,
            ];
        }
        return $codes;
    }

    /**
     * Send password reset link.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function sendPasswordResetLink(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status == Password::RESET_LINK_SENT) {
            return response()->json([
                'success' => true,
                'message' => 'Enlace de restablecimiento enviado a tu correo electrónico',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No pudimos encontrar un usuario con ese correo electrónico',
            'errors' => [
                'email' => [__($status)]
            ]
        ], 422);
    }

    /**
     * Reset password.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return response()->json([
                'success' => true,
                'message' => 'Contraseña restablecida exitosamente',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No se pudo restablecer la contraseña',
            'errors' => [
                'email' => [__($status)]
            ]
        ], 422);
    }

    /**
     * Update user password.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updatePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Contraseña actualizada exitosamente',
        ]);
    }

    /**
     * Confirm password for sensitive actions.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function confirmPassword(Request $request): JsonResponse
    {
        $request->validate([
            'password' => ['required'],
        ]);

        if (!Auth::guard('web')->validate([
            'email' => $request->user()->email,
            'password' => $request->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => ['La contraseña es incorrecta.'],
            ]);
        }

        // Marcar como confirmado (válido por 5 minutos)
        $request->session()->put('auth.password_confirmed_at', time());

        return response()->json([
            'success' => true,
            'message' => 'Contraseña confirmada exitosamente',
            'data' => [
                'expires_at' => now()->addMinutes(5)->timestamp,
            ]
        ]);
    }

    /**
     * Verify email address.
     *
     * @param EmailVerificationRequest $request
     * @return JsonResponse
     */
    public function verifyEmail(EmailVerificationRequest $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'message' => 'Email ya verificado',
            ]);
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return response()->json([
            'success' => true,
            'message' => 'Email verificado exitosamente',
        ]);
    }

    /**
     * Resend email verification link.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function resendVerificationEmail(Request $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'message' => 'Email ya verificado',
            ]);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json([
            'success' => true,
            'message' => 'Enlace de verificación reenviado',
        ]);
    }

    /**
     * Get authenticated user profile.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function profile(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $request->user(),
        ]);
    }

    /**
     * Update user profile.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'firstname' => ['sometimes', 'nullable', 'string', 'max:120'],
            'lastname' => ['sometimes', 'nullable', 'string', 'max:120'],
            'email' => ['sometimes', 'email', 'unique:users,email,' . $user->id],
            'phone' => ['sometimes', 'string', 'max:20'],
            'identification' => ['sometimes', 'string', 'max:50', 'unique:users,identification,' . $user->id],
            'address' => ['sometimes', 'string', 'max:500'],
            'dob' => ['sometimes', 'nullable', 'date'],
            'birthdate' => ['sometimes', 'nullable', 'string', 'max:20'],

            // Métodos de pago que administra la app.
            'bank_code' => ['sometimes', 'nullable', 'integer'],
            'bank_name' => ['sometimes', 'nullable', 'string', 'max:120'],
            'card_number' => ['sometimes', 'nullable', 'string', 'max:30'],
            'card_expiry' => ['sometimes', 'nullable', 'string', 'max:10'],
            'card_holder_name' => ['sometimes', 'nullable', 'string', 'max:120'],
            'debtor_account' => ['sometimes', 'nullable', 'string', 'max:30'],
            'debtor_account_type' => ['sometimes', 'nullable', 'string', 'max:10'],
            'debtor_bank_code' => ['sometimes', 'nullable', 'integer'],
            'debtor_bank_name' => ['sometimes', 'nullable', 'string', 'max:120'],
        ]);

        $data = $request->only(['name', 'firstname', 'lastname', 'email']);

        if ($request->filled('firstname') || $request->filled('lastname')) {
            $firstname = $request->input('firstname', $user->firstname);
            $lastname = $request->input('lastname', $user->lastname);
            $data['full_name'] = trim(($firstname ?? '') . ' ' . ($lastname ?? ''));
        }

        // El panel y la app usan "phone"; la tabla guarda phone1.
        if ($request->filled('phone')) {
            $data['phone1'] = $request->input('phone');
        }
        if ($request->filled('identification')) {
            $data['identification'] = $request->input('identification');
            $data['debtor_id'] = $request->input('identification');
        }
        if ($request->filled('address')) {
            $data['address'] = $request->input('address');
        }
        if ($request->filled('dob')) {
            $data['dob'] = $request->input('dob');
        }

        // La app envía la fecha como dd/MM/yyyy.
        if (! $request->filled('dob') && $request->filled('birthdate')) {
            try {
                $data['dob'] = \Carbon\Carbon::createFromFormat('d/m/Y', $request->input('birthdate'))->toDateString();
            } catch (\Throwable $e) {
                // Formato inesperado: se ignora la fecha en lugar de romper el guardado.
            }
        }

        // Datos de pago (la tarjeta se guarda cifrada; el CVV nunca se almacena).
        if ($request->has('card_number')) {
            $cardNumber = preg_replace('/\D+/', '', (string) $request->input('card_number'));
            $data['card_number'] = $cardNumber;
            $data['payment_card_last4'] = $cardNumber !== '' ? substr($cardNumber, -4) : null;
        }
        if ($request->has('card_expiry')) {
            $data['card_expiration'] = $request->input('card_expiry');
            $data['payment_card_expiry'] = $request->input('card_expiry');
        }
        if ($request->has('card_holder_name')) {
            $data['card_holder_name'] = $request->input('card_holder_name');
            $data['payment_card_holder'] = $request->input('card_holder_name');
        }
        if ($request->has('bank_code')) {
            $data['payment_bank_code'] = $request->input('bank_code');
        }
        if ($request->has('bank_name')) {
            $data['payment_bank_name'] = $request->input('bank_name');
        }
        if ($request->has('debtor_account')) {
            $data['debtor_account'] = $request->input('debtor_account');
        }
        if ($request->has('debtor_account_type')) {
            $data['debtor_account_type'] = $request->input('debtor_account_type');
        }
        if ($request->has('debtor_bank_code')) {
            $data['payment_debit_bank_code'] = $request->input('debtor_bank_code');
        }
        if ($request->has('debtor_bank_name')) {
            $data['payment_debit_bank_name'] = $request->input('debtor_bank_name');
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Perfil actualizado exitosamente',
            'data' => $user,
        ]);
    }

    /**
     * Recuperar el nombre de usuario por correo (app Android).
     * Responde siempre igual para no revelar si el correo existe.
     */
    public function forgotUsername(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            try {
                \Illuminate\Support\Facades\Mail::raw(
                    "Hola {$user->name},\n\nTu usuario en Rumbero Extremo es: {$user->email}\n\nSi no solicitaste este correo, ignóralo.",
                    function ($message) use ($user) {
                        $message->to($user->email)->subject('Tu usuario de Rumbero Extremo');
                    }
                );
            } catch (\Throwable $e) {
                \Log::warning('No se pudo enviar el correo de usuario: ' . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Si el correo está registrado, te enviamos tu usuario.',
        ]);
    }

    /**
     * Eliminar la cuenta desde la app.
     *
     * No se borra la fila (rompería el historial de pagos y promociones):
     * se desactiva y se anonimizan los datos personales.
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = $request->user();

        if (! Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'La contraseña es incorrecta.',
                'errors' => ['password' => ['La contraseña es incorrecta.']],
            ], 422);
        }

        $user->tokens()->delete();
        $user->pushSubscriptions()->delete();

        $user->forceFill([
            'name' => 'Cuenta eliminada',
            'firstname' => null,
            'lastname' => null,
            'full_name' => 'Cuenta eliminada',
            'email' => 'eliminado+' . $user->id . '@rumberoextremo.local',
            'phone1' => null,
            'phone2' => null,
            'identification' => null,
            'address' => null,
            'dob' => null,
            'profile_photo_path' => null,
            'status' => 'inactivo',
            'notifications_enabled' => false,
            'push_enabled' => false,
            'password' => Hash::make(Str::random(40)),

            // Datos de pago: se borran por completo.
            'payment_bank_code' => null,
            'payment_bank_name' => null,
            'payment_phone' => null,
            'payment_holder_name' => null,
            'payment_holder_id' => null,
            'payment_debit_bank_code' => null,
            'payment_debit_bank_name' => null,
            'payment_debit_account' => null,
            'payment_debit_account_type' => 'CELE',
            'payment_card_last4' => null,
            'payment_card_expiry' => null,
            'payment_card_holder' => null,
            'debtor_account' => null,
            'debtor_account_type' => 'CELE',
            'debtor_cell_phone' => null,
            'debtor_id' => null,
            'card_number' => null,
            'card_expiration' => null,
            'card_holder_name' => null,
            'card_holder_id' => null,
            'card_type_id' => null,
            'card_account_type' => 20,
        ])->save();

        return response()->json([
            'success' => true,
            'message' => 'Cuenta eliminada exitosamente',
        ]);
    }

    /**
     * Logout user (revoke token).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada exitosamente',
        ]);
    }

    /**
     * Get user type (user/partner).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getUserType(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'user_type' => $request->user()->user_type,
                'is_partner' => $request->user()->user_type === 'partner',
            ]
        ]);
    }
}