<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\PaymentTransaction;
use App\Models\PageVisit;
use App\Models\Ally;
use Carbon\Carbon;
use PragmaRX\Google2FA\Google2FA;

class AdminController extends Controller
{
    protected $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * ========== CONFIGURACIÓN Y 2FA ==========
     */
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
     * ========== MÉTODOS PARA TRANSACCIONES ==========
     */

    /**
     * Mostrar listado de transacciones
     * - ADMIN: ve todas las transacciones
     * - ALIADO: ve las transacciones donde él es el beneficiario (ally_id)
     * - USUARIO NORMAL: ve las transacciones que él realizó (user_id)
     */
    public function transaccionesIndex(Request $request)
    {
        $user = Auth::user();

        // Query base con relaciones
        $query = PaymentTransaction::with(['user', 'ally']);

        // Filtrar según el tipo de usuario
        if ($user->user_type === 'admin') {
            // ADMIN: puede filtrar por aliado si lo desea
            if ($request->has('ally_id') && $request->ally_id) {
                $query->where('ally_id', $request->ally_id);
            }
            // Si no hay filtro, ve todas
        } elseif ($user->user_type === 'aliado') {
            // ALIADO: ver transacciones donde él es el aliado beneficiario
            // Necesitamos encontrar el registro del aliado en la tabla allies
            $ally = Ally::where('user_id', $user->id)->first();

            if ($ally) {
                // Filtrar por el ally_id de la tabla allies
                $query->where('ally_id', $ally->id);
            } else {
                // Si no hay registro como aliado, no mostrar nada
                $query->whereRaw('1 = 0');
            }
        } else {
            // USUARIO NORMAL: ver transacciones que él realizó
            $query->where('user_id', $user->id);
        }

        // Aplicar filtros adicionales
        if ($request->has('fecha') && $request->fecha != 'all') {
            $query = $this->applyDateFilter($query, $request->fecha);
        }

        if ($request->has('estado') && $request->estado != 'all') {
            $query->where('status', $request->estado);
        }

        // Obtener transacciones paginadas
        $transacciones = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        // Calcular resúmenes según el rol
        if ($user->user_type === 'admin') {
            // Admin: resúmenes globales o por aliado seleccionado
            $resumenQuery = PaymentTransaction::query();

            if ($request->has('ally_id') && $request->ally_id) {
                $resumenQuery->where('ally_id', $request->ally_id);
            }

            $totalRecibido = (clone $resumenQuery)
                ->whereIn('status', ['confirmed', 'awaiting_review'])
                ->sum('amount_to_ally');

            $pendienteCobrar = (clone $resumenQuery)
                ->where('status', 'pending_manual_confirmation')
                ->sum('amount_to_ally');

            $totalTransacciones = (clone $resumenQuery)->count();

            // Lista de aliados para el selector (solo para admin)
            $aliados = User::where('user_type', 'aliado')
                ->orderBy('name')
                ->get(['id', 'name', 'email']);
        } elseif ($user->user_type === 'aliado') {
            // Aliado: resúmenes de sus transacciones
            $ally = Ally::where('user_id', $user->id)->first();

            if ($ally) {
                $totalRecibido = PaymentTransaction::where('ally_id', $ally->id)
                    ->whereIn('status', ['confirmed', 'awaiting_review'])
                    ->sum('amount_to_ally');

                $pendienteCobrar = PaymentTransaction::where('ally_id', $ally->id)
                    ->where('status', 'pending_manual_confirmation')
                    ->sum('amount_to_ally');

                $totalTransacciones = PaymentTransaction::where('ally_id', $ally->id)->count();
            } else {
                $totalRecibido = 0;
                $pendienteCobrar = 0;
                $totalTransacciones = 0;
            }

            $aliados = collect(); // Vacío para aliados
        } else {
            // Usuario normal: resúmenes de sus transacciones
            $totalRecibido = 0; // Los usuarios no reciben dinero, solo pagan
            $pendienteCobrar = 0;
            $totalTransacciones = PaymentTransaction::where('user_id', $user->id)->count();

            $aliados = collect();
        }

        return view('Admin.transacciones.aliado', compact(
            'transacciones',
            'totalRecibido',
            'pendienteCobrar',
            'totalTransacciones',
            'aliados'
        ));
    }

    /**
     * Ver detalle de una transacción específica
     * - ADMIN: puede ver cualquier transacción
     * - ALIADO: solo puede ver sus propias transacciones
     */
    public function transaccionDetalle($id)
    {
        $user = Auth::user();

        $query = PaymentTransaction::with(['user', 'ally']);

        // Aplicar el mismo filtro de seguridad
        if ($user->user_type === 'admin') {
            // Admin puede ver cualquier transacción
        } elseif ($user->user_type === 'aliado') {
            // Aliado solo puede ver transacciones donde él es el aliado
            $ally = Ally::where('user_id', $user->id)->first();
            if ($ally) {
                $query->where('ally_id', $ally->id);
            } else {
                abort(403, 'No tienes permiso para ver esta transacción');
            }
        } else {
            // Usuario normal solo puede ver sus propias transacciones
            $query->where('user_id', $user->id);
        }

        $transaccion = $query->findOrFail($id);

        if (request()->wantsJson()) {
            $html = view('Admin.transacciones.partials.detalle-modal', compact('transaccion'))->render();
            return response()->json([
                'success' => true,
                'transaccion' => $transaccion,
                'html' => $html
            ]);
        }

        return view('Admin.transacciones.detalle', compact('transaccion'));
    }

    /**
     * Aprobar/confirmar transacción (solo admin)
     */
    public function transaccionAprobar($id)
    {
        if (Auth::user()->user_type !== 'admin') {
            return $this->jsonError('No tienes permisos para realizar esta acción.', [], 403);
        }

        $transaccion = PaymentTransaction::findOrFail($id);

        // Verificar que la transacción esté en estado pendiente
        if (!in_array($transaccion->status, ['pending_manual_confirmation', 'awaiting_review'])) {
            return $this->jsonError('Solo se pueden confirmar transacciones pendientes.');
        }

        // Actualizar estado
        $transaccion->status = 'confirmed';
        $transaccion->save();

        // Registrar actividad
        ActivityLog::create([
            'user_id' => Auth::id(),
            'description' => 'Transacción confirmada',
            'details' => "Transacción #{$transaccion->id} confirmada - Monto para aliado: $" . number_format($transaccion->amount_to_ally, 0, ',', '.'),
            'status' => 'completed'
        ]);

        return $this->jsonSuccess('Transacción confirmada correctamente.', [
            'transaccion' => $transaccion
        ]);
    }

    /**
     * Rechazar transacción (solo admin)
     */
    public function transaccionRechazar($id)
    {
        if (Auth::user()->user_type !== 'admin') {
            return $this->jsonError('No tienes permisos para realizar esta acción.', [], 403);
        }

        $transaccion = PaymentTransaction::findOrFail($id);

        // Verificar que la transacción esté en estado pendiente
        if (!in_array($transaccion->status, ['pending_manual_confirmation', 'awaiting_review'])) {
            return $this->jsonError('Solo se pueden rechazar transacciones pendientes.');
        }

        // Actualizar estado
        $transaccion->status = 'failed';
        $transaccion->save();

        // Registrar actividad
        ActivityLog::create([
            'user_id' => Auth::id(),
            'description' => 'Transacción rechazada',
            'details' => "Transacción #{$transaccion->id} rechazada",
            'status' => 'failed'
        ]);

        return $this->jsonSuccess('Transacción rechazada correctamente.', [
            'transaccion' => $transaccion
        ]);
    }

    /**
     * Generar comprobante de transacción
     * - ADMIN: puede ver comprobante de cualquier transacción
     * - ALIADO: solo puede ver comprobante de sus propias transacciones
     */
    public function transaccionComprobante($id)
    {
        $user = Auth::user();

        $query = PaymentTransaction::with(['user', 'ally']);

        // Si NO es admin, asegurar que sea su transacción
        if ($user->user_type !== 'admin') {
            $query->where('ally_id', $user->id);
        }

        $transaccion = $query->findOrFail($id);

        return view('Admin.transacciones.comprobante', compact('transaccion'));
    }

    /**
     * Exportar transacciones a CSV
     * - ADMIN: exporta todas las transacciones (o filtradas por aliado)
     * - ALIADO: exporta solo sus propias transacciones
     */
    public function transaccionesExportar(Request $request)
    {
        $user = Auth::user();

        // Query base con relaciones
        $query = PaymentTransaction::with(['user', 'ally']);

        // Si NO es admin, filtrar solo sus transacciones
        if ($user->user_type !== 'admin') {
            $query->where('ally_id', $user->id);
        }

        // Si es admin y seleccionó un aliado específico
        if ($user->user_type === 'admin' && $request->has('ally_id') && $request->ally_id) {
            $query->where('ally_id', $request->ally_id);
        }

        // Aplicar filtros
        if ($request->has('fecha') && $request->fecha != 'all') {
            $query = $this->applyDateFilter($query, $request->fecha);
        }

        if ($request->has('estado') && $request->estado != 'all') {
            $query->where('status', $request->estado);
        }

        // Obtener transacciones
        $transacciones = $query->orderBy('created_at', 'desc')->get();

        // Si no hay transacciones
        if ($transacciones->isEmpty()) {
            return redirect()->back()->with('error', 'No hay transacciones para exportar');
        }

        // Generar CSV
        $filename = "transacciones_" . date('Y-m-d_His') . ".csv";

        // Configurar headers para descarga
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Crear archivo CSV en memoria
        $output = fopen('php://output', 'w');

        // Agregar BOM para UTF-8 (soporte para caracteres especiales)
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Definir encabezados según el rol
        if ($user->user_type === 'admin') {
            $headers = [
                'ID',
                'Fecha',
                'Hora',
                'Código de Referencia',
                'Aliado',
                'Email Aliado',
                'Usuario',
                'Email Usuario',
                'Monto Original',
                'Descuento %',
                'Comisión Plataforma',
                'Monto para Aliado',
                'Método de Pago',
                'Estado',
                'Fecha Creación'
            ];
        } else {
            $headers = [
                'ID',
                'Fecha',
                'Hora',
                'Código de Referencia',
                'Usuario',
                'Email Usuario',
                'Monto Original',
                'Descuento %',
                'Comisión Plataforma',
                'Monto para Aliado',
                'Método de Pago',
                'Estado',
                'Fecha Creación'
            ];
        }

        // Escribir encabezados
        fputcsv($output, $headers);

        // Escribir datos
        foreach ($transacciones as $t) {
            if ($user->user_type === 'admin') {
                $row = [
                    $t->id,
                    $t->created_at->format('d/m/Y'),
                    $t->created_at->format('H:i:s'),
                    $t->reference_code,
                    $t->ally->name ?? 'N/A',
                    $t->ally->email ?? 'N/A',
                    $t->user->name ?? 'N/A',
                    $t->user->email ?? 'N/A',
                    '$ ' . number_format($t->original_amount, 0, ',', '.'),
                    $t->discount_percentage . '%',
                    '$ ' . number_format($t->platform_commission, 0, ',', '.'),
                    '$ ' . number_format($t->amount_to_ally, 0, ',', '.'),
                    $this->formatPaymentMethod($t->payment_method),
                    $this->formatStatus($t->status),
                    $t->created_at->format('d/m/Y H:i:s')
                ];
            } else {
                $row = [
                    $t->id,
                    $t->created_at->format('d/m/Y'),
                    $t->created_at->format('H:i:s'),
                    $t->reference_code,
                    $t->user->name ?? 'N/A',
                    $t->user->email ?? 'N/A',
                    '$ ' . number_format($t->original_amount, 0, ',', '.'),
                    $t->discount_percentage . '%',
                    '$ ' . number_format($t->platform_commission, 0, ',', '.'),
                    '$ ' . number_format($t->amount_to_ally, 0, ',', '.'),
                    $this->formatPaymentMethod($t->payment_method),
                    $this->formatStatus($t->status),
                    $t->created_at->format('d/m/Y H:i:s')
                ];
            }

            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }

    /**
     * ========== MÉTODOS PRIVADOS ==========
     */

    /**
     * Aplicar filtro de fecha
     */
    private function applyDateFilter($query, $filter)
    {
        switch ($filter) {
            case 'today':
                return $query->whereDate('created_at', Carbon::today());
            case 'yesterday':
                return $query->whereDate('created_at', Carbon::yesterday());
            case 'week':
                return $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
            case 'month':
                return $query->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year);
            default:
                return $query;
        }
    }

    /**
     * Generar QR Code como SVG
     */
    private function generateQRCodeSvg($user)
    {
        $companyName = config('app.name', 'Rumbero Extremo');

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
     * Formatear método de pago
     */
    private function formatPaymentMethod($method)
    {
        return match ($method) {
            'pago_movil' => '📱 Pago Móvil',
            'transferencia_bancaria' => '🏦 Transferencia',
            default => $method ?? 'N/A'
        };
    }

    /**
     * Formatear estado
     */
    private function formatStatus($status)
    {
        return match ($status) {
            'confirmed' => '✅ Confirmada',
            'awaiting_review' => '⏳ En Revisión',
            'pending_manual_confirmation' => '⌛ Pendiente Confirmación',
            'failed' => '❌ Fallida',
            default => $status
        };
    }
}
