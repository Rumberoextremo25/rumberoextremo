<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Models\Ally;
use App\Models\Sale;
use App\Models\ActivityLog;
use App\Models\PageVisit;
use Carbon\Carbon;
use PragmaRX\Google2FA\Google2FA;

class AdminController extends Controller
{
    protected $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    public function index()
    {
        $user = Auth::user();
        $role = $user->user_type;

        // Datos del dashboard
        $dashboardData = $this->getDashboardData($user, $role);
        $latestActivities = $this->getLatestActivities($user, $role);

        return view('dashboard', array_merge($dashboardData, ['latestActivities' => $latestActivities]));
    }

    public function settings()
    {
        $user = Auth::user();
        
        // Generar secreto para 2FA si no existe
        if (!$user->two_factor_secret) {
            $user->two_factor_secret = $this->google2fa->generateSecretKey();
            $user->save();
        }

        // Generar QR Code
        $qrCodeSvg = $this->generateQRCodeSvg($user);

        return view('Admin.settings', compact('user', 'qrCodeSvg'));
    }

    public function changePassword(Request $request)
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
        ], [
            'current_password.required' => 'Debes ingresar tu contraseña actual.',
            'new_password.required' => 'Debes ingresar una nueva contraseña.',
            'new_password.min' => 'La nueva contraseña debe tener al menos :min caracteres.',
            'new_password.confirmed' => 'La confirmación de la nueva contraseña no coincide.',
            'new_password.different' => 'La nueva contraseña no puede ser igual a la actual.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return back()->with('success', '¡Tu contraseña ha sido cambiada exitosamente!');
    }

    /**
     * Activar/Desactivar autenticación en dos pasos
     */
    public function toggleTwoFactor(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'enabled' => 'required|boolean',
            'verification_code' => $request->enabled ? 'required|string' : 'nullable'
        ]);

        if ($request->enabled) {
            // Verificar el código antes de activar
            if (!$this->verifyTwoFactorCode($user, $request->verification_code)) {
                return $this->jsonError('El código de verificación es inválido.');
            }

            $user->two_factor_enabled = true;
            $user->save();

            $backupCodes = $this->generateBackupCodes($user);

            return $this->jsonSuccess(
                'Autenticación en dos pasos activada correctamente.',
                ['backup_codes' => $backupCodes]
            );
        } else {
            $user->two_factor_enabled = false;
            $user->save();

            return $this->jsonSuccess('Autenticación en dos pasos desactivada correctamente.');
        }
    }

    /**
     * Verificar código de autenticación en dos pasos
     */
    public function verifyTwoFactor(Request $request)
    {
        $request->validate([
            'verification_code' => 'required|string'
        ]);

        $user = Auth::user();
        $valid = $this->verifyTwoFactorCode($user, $request->verification_code);

        if ($valid) {
            return $this->jsonSuccess('Código verificado correctamente.');
        } else {
            return $this->jsonError('El código de verificación es inválido.');
        }
    }

    /**
     * Generar nuevos códigos de respaldo
     */
    public function generateNewBackupCodes(Request $request)
    {
        $user = Auth::user();

        if (!$user->two_factor_enabled) {
            return $this->jsonError('La autenticación en dos pasos no está activada.');
        }

        $backupCodes = $this->generateBackupCodes($user);

        return $this->jsonSuccess(
            'Nuevos códigos de respaldo generados correctamente.',
            ['backup_codes' => $backupCodes]
        );
    }

    /**
     * Actualizar preferencias de notificaciones
     */
    public function updateNotificationPreferences(Request $request)
    {
        $request->validate([
            'notifications_enabled' => 'required|boolean'
        ]);

        Auth::user()->update([
            'notifications_enabled' => $request->notifications_enabled
        ]);

        return $this->jsonSuccess('Preferencias de notificaciones actualizadas correctamente.');
    }

    /**
     * Actualizar modo oscuro
     */
    public function updateDarkMode(Request $request)
    {
        $request->validate([
            'dark_mode_enabled' => 'required|boolean'
        ]);

        Auth::user()->update([
            'dark_mode_enabled' => $request->dark_mode_enabled
        ]);

        return $this->jsonSuccess('Preferencia de modo oscuro actualizada correctamente.');
    }

    /**
     * ========== MÉTODOS PRIVADOS ==========
     */

    /**
     * Obtener datos del dashboard
     */
    private function getDashboardData($user, $role)
    {
        $data = [
            'totalUsers' => User::count(),
            'totalAllies' => Ally::count(),
            'totalSales' => Sale::sum('total_amount'),
            'pageViews' => PageVisit::sum('visits_count'),
            'todaySalesSpecific' => 0.00,
            'customerSatisfaction' => 0,
        ];

        if ($role === 'admin') {
            $data['todaySalesSpecific'] = Sale::whereDate('sale_date', Carbon::today())->sum('total');
            $data['customerSatisfaction'] = 92;
        } elseif ($role === 'aliado') {
            $data['todaySalesSpecific'] = Sale::whereDate('sale_date', Carbon::today())
                ->whereHas('product', fn($query) => $query->where('user_id', $user->id))
                ->sum('total');
            $data['customerSatisfaction'] = 88;
        }

        return $data;
    }

    /**
     * Obtener actividades recientes
     */
    private function getLatestActivities($user, $role)
    {
        $activityQuery = ActivityLog::latest()->limit(10);

        if ($role !== 'admin') {
            $activityQuery->where('user_id', $user->id);
        }

        return $activityQuery->get()
            ->map(fn($activity) => [
                'activity' => $activity->description,
                'user' => optional($activity->user)->name ?? 'N/A',
                'date' => Carbon::parse($activity->created_at)->format('d/m/Y H:i'),
                'status' => ucfirst($activity->status),
                'status_class' => $this->getStatusClass($activity->status)
            ]);
    }

    /**
     * Generar QR Code como SVG
     */
    private function generateQRCodeSvg($user)
    {
        $companyName = config('app.name', 'TuAplicación');
        
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            $companyName,
            $user->email,
            $user->two_factor_secret
        );

        // Usar un servicio online para generar el QR
        $qrCodeImageUrl = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($qrCodeUrl);
        
        return '<img src="' . $qrCodeImageUrl . '" alt="QR Code para 2FA" style="width: 200px; height: 200px;">';
    }

    /**
     * Verificar código 2FA
     */
    private function verifyTwoFactorCode($user, $code)
    {
        // Primero verificar si es un código de respaldo
        if ($this->verifyBackupCode($user, $code)) {
            return true;
        }

        // Luego verificar con Google Authenticator
        return $this->google2fa->verifyKey($user->two_factor_secret, $code);
    }

    /**
     * Verificar código de respaldo
     */
    private function verifyBackupCode($user, $code)
    {
        $backupCodes = json_decode($user->two_factor_recovery_codes ?? '[]', true);

        foreach ($backupCodes as $index => $backupCode) {
            if (hash_equals($backupCode['code'], $code) && !$backupCode['used']) {
                // Marcar código como usado
                $backupCodes[$index]['used'] = true;
                $user->two_factor_recovery_codes = json_encode($backupCodes);
                $user->save();
                return true;
            }
        }

        return false;
    }

    /**
     * Generar códigos de respaldo
     */
    private function generateBackupCodes($user)
    {
        $backupCodes = [];

        for ($i = 0; $i < 8; $i++) {
            $backupCodes[] = [
                'code' => strtoupper(bin2hex(random_bytes(5))), // Código de 10 caracteres
                'used' => false
            ];
        }

        $user->two_factor_recovery_codes = json_encode($backupCodes);
        $user->save();

        return array_column($backupCodes, 'code');
    }

    /**
     * Respuestas JSON estandarizadas
     */
    private function jsonSuccess($message, $data = [])
    {
        return response()->json(array_merge([
            'success' => true,
            'message' => $message
        ], $data));
    }

    private function jsonError($message, $errors = [], $code = 422)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $code);
    }

    /**
     * Obtener clase CSS para el estado
     */
    private function getStatusClass($status)
    {
        return match ($status) {
            'completed' => 'success',
            'pending' => 'warning',
            'failed' => 'danger',
            default => 'secondary'
        };
    }
}
