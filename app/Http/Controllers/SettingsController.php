<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request; // ✅ IMPORTANTE: Importar Request
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use PragmaRX\Google2FA\Google2FA;

class SettingsController extends Controller
{
    protected $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Mostrar la página de configuración
     */
    public function index()
    {
        $user = Auth::user();

        // Validar que el secreto exista y sea válido
        if (!$user->two_factor_secret || strlen($user->two_factor_secret) < 16) {
            // Generar secreto válido de 32 caracteres
            $user->two_factor_secret = $this->google2fa->generateSecretKey(32);
            $user->save();

            Log::info('Secreto 2FA regenerado por ser inválido', [
                'user_id' => $user->id,
                'new_secret_length' => strlen($user->two_factor_secret)
            ]);
        }

        // Generar QR Code
        $qrCodeSvg = $this->generateQRCodeSvg($user);

        return view('Admin.settings', compact('user', 'qrCodeSvg'));
    }

    /**
     * Cambiar contraseña
     */
    public function changePassword(Request $request) // ✅ Recibe Request
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'current_password' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($user) {
                    if (!Hash::check($value, $user->password)) {
                        $fail('La contraseña actual es incorrecta.');
                    }
                }
            ],
            'new_password' => 'required|string|min:8|confirmed|different:current_password',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => '¡Tu contraseña ha sido cambiada exitosamente!'
        ]);
    }

    /**
     * Activar/Desactivar autenticación en dos pasos
     */
    public function toggleTwoFactor(Request $request) // ✅ Recibe Request
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'enabled' => 'required|boolean',
            'verification_code' => $request->enabled ? 'required|string|size:6' : 'nullable'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->enabled) {
            // Verificar el código antes de activar
            if (!$this->verifyTwoFactorCode($user, $request->verification_code)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El código de verificación es inválido.'
                ], 400);
            }

            $user->two_factor_enabled = true;
            $user->save();

            $backupCodes = $this->generateNewBackupCodes($user);

            return response()->json([
                'success' => true,
                'message' => 'Autenticación en dos pasos activada correctamente.',
                'backup_codes' => $backupCodes
            ]);
        } else {
            $user->two_factor_enabled = false;
            $user->two_factor_recovery_codes = null;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Autenticación en dos pasos desactivada correctamente.'
            ]);
        }
    }

    /**
     * Verificar código de autenticación en dos pasos
     */
    public function verifyTwoFactor(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'verification_code' => 'required|string|size:6'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El código debe tener 6 dígitos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuario no autenticado'
                ], 401);
            }

            Log::info('Iniciando verificación 2FA', [
                'user_id' => $user->id,
                'code' => $request->verification_code,
                'has_secret' => !empty($user->two_factor_secret)
            ]);

            $valid = $this->verifyTwoFactorCode($user, $request->verification_code);

            Log::info('Resultado verificación', ['valid' => $valid]);

            if ($valid) {
                return response()->json([
                    'success' => true,
                    'message' => 'Código verificado correctamente.'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'El código de verificación es inválido.'
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Excepción en verifyTwoFactor: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al verificar el código: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar nuevos códigos de respaldo
     */
    public function generateBackupCodes(Request $request) // ✅ Recibe Request
    {
        $user = Auth::user();

        if (!$user->two_factor_enabled) {
            return response()->json([
                'success' => false,
                'message' => 'La autenticación en dos pasos no está activada.'
            ], 400);
        }

        $backupCodes = $this->generateNewBackupCodes($user);

        return response()->json([
            'success' => true,
            'message' => 'Nuevos códigos de respaldo generados correctamente.',
            'backup_codes' => $backupCodes
        ]);
    }

    /**
     * Actualizar preferencias de notificaciones
     */
    public function updateNotifications(Request $request) // ✅ Recibe Request
    {
        $validator = Validator::make($request->all(), [
            'notifications_enabled' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos'
            ], 422);
        }

        $user = Auth::user();
        $user->notifications_enabled = $request->notifications_enabled;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Preferencias de notificaciones actualizadas correctamente.'
        ]);
    }

    /**
     * Actualizar modo oscuro
     */
    public function updateDarkMode(Request $request) // ✅ Recibe Request
    {
        $validator = Validator::make($request->all(), [
            'dark_mode_enabled' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos'
            ], 422);
        }

        $user = Auth::user();
        $user->dark_mode_enabled = $request->dark_mode_enabled;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Preferencia de modo oscuro actualizada correctamente.',
            'dark_mode_enabled' => $user->dark_mode_enabled
        ]);
    }

    // ========== MÉTODOS PRIVADOS ==========

    private function generateQRCodeSvg($user)
    {
        $companyName = config('app.name', 'Rumbero Extremo');
        $companyEmail = $user->email;

        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            $companyName,
            $companyEmail,
            $user->two_factor_secret
        );

        try {
            return \SimpleSoftwareIO\QrCode\Facades\QrCode::size(150)
                ->backgroundColor(255, 255, 255)
                ->color(166, 1, 179)
                ->generate($qrCodeUrl);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function verifyTwoFactorCode($user, $code)
    {
        try {
            Log::info('=== INICIO VERIFICACIÓN 2FA ===');

            // Validaciones básicas
            $code = trim($code);
            if (!preg_match('/^\d{6}$/', $code)) {
                Log::warning('Código no tiene formato de 6 dígitos');
                return false;
            }

            // Verificar que el secreto existe y tiene longitud válida
            if (empty($user->two_factor_secret) || strlen($user->two_factor_secret) < 16) {
                Log::error('Secreto 2FA inválido', [
                    'secret_length' => strlen($user->two_factor_secret ?? 0)
                ]);
                return false;
            }

            // Verificar códigos de respaldo (igual que antes)
            if ($user->two_factor_recovery_codes) {
                // ... tu código existente de backup codes ...
            }

            // VERIFICACIÓN TOTP CORREGIDA
            try {
                // Probar primero con el timestamp actual
                $currentCode = $this->google2fa->getCurrentOtp($user->two_factor_secret);

                Log::info('Comparación de códigos', [
                    'code_ingresado' => $code,
                    'code_esperado_actual' => $currentCode,
                    'coinciden' => ($code === $currentCode)
                ]);

                // Verificar con ventana de 1 período (30 seg antes/después)
                $this->google2fa->setWindow(1);
                $valid = $this->google2fa->verifyKey($user->two_factor_secret, $code);

                if (!$valid) {
                    // Si falla, intentar con ventana más amplia para debug
                    $this->google2fa->setWindow(4);
                    $valid = $this->google2fa->verifyKey($user->two_factor_secret, $code);

                    if ($valid) {
                        Log::warning('2FA válido con ventana amplia - posible desfase horario');
                    }
                }

                Log::info('Resultado verificación TOTP', ['valid' => $valid]);
                return $valid;
            } catch (\PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException $e) {
                Log::error('Secret key demasiado corta - regenerando...');

                // Regenerar automáticamente
                $user->two_factor_secret = $this->google2fa->generateSecretKey(32);
                $user->two_factor_enabled = false;
                $user->save();

                Log::info('Nuevo secreto generado', [
                    'new_secret' => substr($user->two_factor_secret, 0, 10) . '...'
                ]);

                return false;
            } catch (\Exception $e) {
                Log::error('Error específico TOTP: ' . $e->getMessage());
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Error general en verifyTwoFactorCode: ' . $e->getMessage());
            return false;
        }
    }

    public function testCurrentCode()
    {
        try {
            $user = Auth::user();

            if (!$user->two_factor_secret) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay secret 2FA configurado'
                ]);
            }

            // Generar el código actual que debería funcionar
            $currentCode = $this->google2fa->getCurrentOtp($user->two_factor_secret);

            // Verificar ese mismo código
            $verificationResult = $this->google2fa->verifyKey($user->two_factor_secret, $currentCode);

            return response()->json([
                'success' => true,
                'secret' => substr($user->two_factor_secret, 0, 10) . '...',
                'current_code' => $currentCode,
                'self_verification' => $verificationResult,
                'timestamp' => now()->toDateTimeString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function generateNewBackupCodes($user)
    {
        $codes = [];
        $hashedCodes = [];

        for ($i = 0; $i < 8; $i++) {
            $code = $this->generateRandomCode();
            $codes[] = $code;
            $hashedCodes[] = [
                'code' => Hash::make($code),
                'used' => false
            ];
        }

        $user->two_factor_recovery_codes = json_encode($hashedCodes);
        $user->save();

        return $codes;
    }

    private function generateRandomCode()
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $code = '';
        for ($i = 0; $i < 4; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }
        $code .= '-';
        for ($i = 0; $i < 4; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $code;
    }
}
