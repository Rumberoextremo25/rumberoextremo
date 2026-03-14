<?php

namespace App\Http\Controllers;

use App\Models\Ally;
use App\Models\PaymentTransaction;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AdminController extends Controller
{
    protected $google2fa;

    public function __construct()
    {
        //
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
        $query = PaymentTransaction::with([
            'user:id,name,email',
            'ally:id,company_name,contact_email,user_id',
            'ally.user:id,name,email'
        ]);

        // Filtrar según el rol del usuario
        if ($user->role === 'admin') {
            if ($request->has('ally_id') && $request->ally_id) {
                $query->where('ally_id', $request->ally_id);
            }
        } elseif ($user->role === 'aliado') {
            // Buscar el registro del aliado asociado a este usuario
            $ally = Ally::where('user_id', $user->id)->first();
            if ($ally) {
                // Filtrar por ally_id (ID en tabla allies)
                $query->where('ally_id', $ally->id);
            } else {
                // Si no tiene perfil de aliado, no mostrar nada
                $query->whereRaw('1 = 0');
            }
        } else {
            // Usuario normal: ver solo sus transacciones como cliente
            $query->where('user_id', $user->id);
        }

        // Aplicar filtros adicionales
        if ($request->has('fecha') && $request->fecha != 'all') {
            $query = $this->applyDateFilter($query, $request->fecha);
        }

        if ($request->has('estado') && $request->estado != 'all') {
            $query->where('status', $request->estado);
        }

        // PAGINACIÓN: 6 items por página
        $transacciones = $query->orderBy('created_at', 'desc')
            ->paginate(6)
            ->withQueryString();

        // Calcular resúmenes
        $resumen = $this->calcularResumenTransacciones($user, $request);

        return view('Admin.transacciones.aliado', [
            'transacciones' => $transacciones,
            'totalRecibido' => $resumen['totalRecibido'],
            'pendienteCobrar' => $resumen['pendienteCobrar'],
            'totalTransacciones' => $resumen['totalTransacciones'],
            'aliados' => $resumen['aliados'],
            'comisiones' => $resumen['comisiones'] ?? null,
            'totalComisiones' => $resumen['totalComisiones'] ?? 0
        ]);
    }

    /**
     * Calcular resúmenes de transacciones según rol
     */
    private function calcularResumenTransacciones($user, $request)
    {
        $result = [
            'totalRecibido' => 0,
            'pendienteCobrar' => 0,
            'totalTransacciones' => 0,
            'aliados' => collect(),
            'comisiones' => null,
            'totalComisiones' => 0
        ];

        if ($user->role === 'admin') {
            $query = PaymentTransaction::query();
            
            if ($request->has('ally_id') && $request->ally_id) {
                $query->where('ally_id', $request->ally_id);
            }

            $result['totalRecibido'] = (clone $query)
                ->whereIn('status', ['confirmed'])
                ->sum('amount_to_ally');

            $result['pendienteCobrar'] = (clone $query)
                ->whereIn('status', ['pending_manual_confirmation', 'awaiting_review'])
                ->sum('amount_to_ally');

            $result['totalTransacciones'] = (clone $query)->count();

            // Lista de aliados para el filtro (solo para admin)
            $result['aliados'] = User::where('role', 'aliado')
                ->orderBy('name')
                ->get(['id', 'name', 'email']);

            // Calcular total de comisiones (plataforma)
            $result['totalComisiones'] = (clone $query)
                ->where('status', 'confirmed')
                ->sum('platform_commission');

        } elseif ($user->role === 'aliado') {
            $ally = Ally::where('user_id', $user->id)->first();
            
            if ($ally) {
                $query = PaymentTransaction::where('ally_id', $ally->id);

                $result['totalRecibido'] = (clone $query)
                    ->whereIn('status', ['confirmed'])
                    ->sum('amount_to_ally');

                $result['pendienteCobrar'] = (clone $query)
                    ->whereIn('status', ['pending_manual_confirmation', 'awaiting_review'])
                    ->sum('amount_to_ally');

                $result['totalTransacciones'] = (clone $query)->count();

                // Calcular comisiones generadas por este aliado
                $result['totalComisiones'] = (clone $query)
                    ->where('status', 'confirmed')
                    ->sum('platform_commission');
            }

        } else {
            // Usuario normal
            $result['totalTransacciones'] = PaymentTransaction::where('user_id', $user->id)->count();
        }

        return $result;
    }

    /**
     * Ver detalle de una transacción específica
     */
    public function transaccionDetalle($id)
    {
        $user = Auth::user();

        // Query con relaciones necesarias
        $query = PaymentTransaction::with([
            'user:id,name,email,phone1',
            'ally:id,company_name,contact_email,contact_phone',
            'ally.user:id,name,email'
        ]);

        // Aplicar filtros de seguridad
        if ($user->role === 'admin') {
            // Admin puede ver cualquier transacción
        } elseif ($user->role === 'aliado') {
            $ally = Ally::where('user_id', $user->id)->first(['id']);
            if (!$ally) {
                abort(403, 'No tienes permiso para ver esta transacción');
            }
            $query->where('ally_id', $ally->id);
        } else {
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
        if (Auth::user()->role !== 'admin') {
            return $this->jsonError('No tienes permisos para realizar esta acción.', [], 403);
        }

        $transaccion = PaymentTransaction::findOrFail($id);

        if (!in_array($transaccion->status, ['pending_manual_confirmation', 'awaiting_review'])) {
            return $this->jsonError('Solo se pueden confirmar transacciones pendientes.');
        }

        $transaccion->status = 'confirmed';
        $transaccion->save();

        $this->registrarActividad(
            'Transacción confirmada',
            "Transacción #{$transaccion->id} confirmada - Monto para aliado: Bs. " . number_format($transaccion->amount_to_ally, 2, ',', '.'),
            'completed',
            'transaction_confirmed'
        );

        return $this->jsonSuccess('Transacción confirmada correctamente.', [
            'transaccion' => $transaccion
        ]);
    }

    /**
     * Rechazar transacción (solo admin)
     */
    public function transaccionRechazar($id)
    {
        if (Auth::user()->role !== 'admin') {
            return $this->jsonError('No tienes permisos para realizar esta acción.', [], 403);
        }

        $transaccion = PaymentTransaction::findOrFail($id);

        if (!in_array($transaccion->status, ['pending_manual_confirmation', 'awaiting_review'])) {
            return $this->jsonError('Solo se pueden rechazar transacciones pendientes.');
        }

        $transaccion->status = 'failed';
        $transaccion->save();

        $this->registrarActividad(
            'Transacción rechazada',
            "Transacción #{$transaccion->id} rechazada",
            'failed',
            'transaction_rejected'
        );

        return $this->jsonSuccess('Transacción rechazada correctamente.', [
            'transaccion' => $transaccion
        ]);
    }

    /**
     * Aprobar múltiples transacciones
     */
    public function aprobarMasivas(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'No hay transacciones seleccionadas']);
        }

        $transacciones = PaymentTransaction::whereIn('id', $ids)
            ->whereIn('status', ['pending_manual_confirmation', 'awaiting_review'])
            ->get();

        $count = 0;
        foreach ($transacciones as $transaccion) {
            $transaccion->status = 'confirmed';
            $transaccion->save();
            $count++;

            $this->registrarActividad(
                'Transacción confirmada (masiva)',
                "Transacción #{$transaccion->id} confirmada - Monto: Bs. " . number_format($transaccion->amount_to_ally, 2, ',', '.'),
                'completed',
                'transaction_confirmed_bulk'
            );
        }

        return response()->json([
            'success' => true,
            'message' => $count . ' transacciones aprobadas correctamente'
        ]);
    }

    /**
     * Rechazar múltiples transacciones
     */
    public function rechazarMasivas(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'No autorizado'], 403);
        }

        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'No hay transacciones seleccionadas']);
        }

        $transacciones = PaymentTransaction::whereIn('id', $ids)
            ->whereIn('status', ['pending_manual_confirmation', 'awaiting_review'])
            ->get();

        $count = 0;
        foreach ($transacciones as $transaccion) {
            $transaccion->status = 'failed';
            $transaccion->save();
            $count++;

            $this->registrarActividad(
                'Transacción rechazada (masiva)',
                "Transacción #{$transaccion->id} rechazada",
                'failed',
                'transaction_rejected_bulk'
            );
        }

        return response()->json([
            'success' => true,
            'message' => $count . ' transacciones rechazadas correctamente'
        ]);
    }

    /**
     * Generar comprobante de transacción
     */
    public function transaccionComprobante($id)
    {
        $user = Auth::user();

        $query = PaymentTransaction::with([
            'user:id,name,email,phone1',
            'ally:id,company_name,contact_email,contact_phone'
        ]);

        // Filtros de seguridad
        if ($user->role !== 'admin') {
            if ($user->role === 'aliado') {
                $ally = Ally::where('user_id', $user->id)->first(['id']);
                if (!$ally) {
                    abort(403, 'No tienes permiso para ver este comprobante');
                }
                $query->where('ally_id', $ally->id);
            } else {
                $query->where('user_id', $user->id);
            }
        }

        $transaccion = $query->findOrFail($id);

        Log::info('Comprobante generado', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'transaction_id' => $transaccion->id
        ]);

        return view('Admin.transacciones.comprobante', compact('transaccion'));
    }

    /**
     * Exportar transacciones a CSV
     */
    public function transaccionesExportar(Request $request)
    {
        $user = Auth::user();

        $query = PaymentTransaction::with(['user', 'ally', 'ally.user']);

        // Filtrar según rol
        if ($user->role === 'admin') {
            if ($request->has('ally_id') && $request->ally_id) {
                $query->where('ally_id', $request->ally_id);
            }
        } elseif ($user->role === 'aliado') {
            $ally = Ally::where('user_id', $user->id)->first();
            if ($ally) {
                $query->where('ally_id', $ally->id);
            } else {
                $query->whereRaw('1 = 0');
            }
        } else {
            $query->where('user_id', $user->id);
        }

        // Aplicar filtros
        if ($request->has('fecha') && $request->fecha != 'all') {
            $query = $this->applyDateFilter($query, $request->fecha);
        }

        if ($request->has('estado') && $request->estado != 'all') {
            $query->where('status', $request->estado);
        }

        $transacciones = $query->orderBy('created_at', 'desc')->get();

        if ($transacciones->isEmpty()) {
            return redirect()->back()->with('error', 'No hay transacciones para exportar');
        }

        return $this->generarCSV($transacciones, $user);
    }

    /**
     * Generar archivo CSV
     */
    private function generarCSV($transacciones, $user)
    {
        $filename = "transacciones_" . date('Y-m-d_His') . ".csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8

        // Encabezados
        $headers = [
            'ID',
            'Fecha',
            'Hora',
            'Código Referencia',
            'Cliente',
            'Email Cliente',
            'Aliado',
            'Email Aliado',
            'Monto Original (Bs.)',
            'Descuento %',
            'Comisión Plataforma (Bs.)',
            'Monto para Aliado (Bs.)',
            'Método de Pago',
            'Estado',
            'Fecha Creación'
        ];
        fputcsv($output, $headers);

        // Datos
        foreach ($transacciones as $t) {
            $nombreAliado = $t->ally->company_name ?? ($t->ally->user->name ?? 'N/A');
            $emailAliado = $t->ally->contact_email ?? ($t->ally->user->email ?? 'N/A');
            
            $row = [
                $t->id,
                $t->created_at->format('d/m/Y'),
                $t->created_at->format('H:i:s'),
                $t->reference_code,
                $t->user->name ?? 'N/A',
                $t->user->email ?? 'N/A',
                $nombreAliado,
                $emailAliado,
                number_format($t->original_amount, 2, ',', '.'),
                $t->discount_percentage . '%',
                number_format($t->platform_commission, 2, ',', '.'),
                number_format($t->amount_to_ally, 2, ',', '.'),
                $this->formatPaymentMethod($t->payment_method),
                $this->formatStatus($t->status),
                $t->created_at->format('d/m/Y H:i:s')
            ];
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
        return match ($filter) {
            'today' => $query->whereDate('created_at', Carbon::today()),
            'yesterday' => $query->whereDate('created_at', Carbon::yesterday()),
            'week' => $query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]),
            'month' => $query->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year),
            default => $query,
        };
    }

    /**
     * Registrar actividad
     */
    private function registrarActividad($descripcion, $detalles, $estado, $tipo)
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'performed_by' => Auth::id(),
            'description' => $descripcion,
            'details' => $detalles,
            'status' => $estado,
            'activity_type' => $tipo,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
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
            'tarjeta_credito' => '💳 Tarjeta de Crédito',
            'debito_inmediato' => '🏦 Débito Inmediato',
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
            'pending_manual_confirmation' => '⌛ Pendiente',
            'pending_sms' => '📱 Esperando SMS',
            'pending_confirmation' => '⏰ Por Confirmar',
            'failed' => '❌ Fallida',
            default => $status ?? 'N/A'
        };
    }
}
