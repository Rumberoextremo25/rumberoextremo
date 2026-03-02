<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\Ally;
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
        $query = PaymentTransaction::with(['user', 'ally']);

        // Filtrar según el rol del usuario
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

        // Aplicar filtros adicionales
        if ($request->has('fecha') && $request->fecha != 'all') {
            $query = $this->applyDateFilter($query, $request->fecha);
        }

        if ($request->has('estado') && $request->estado != 'all') {
            $query->where('status', $request->estado);
        }

        // PAGINACIÓN: 6 items por página
        $transacciones = $query->orderBy('created_at', 'desc')
            ->paginate(6)  // ← CAMBIADO A 6
            ->withQueryString();

        // Calcular resúmenes (igual que antes)
        if ($user->role === 'admin') {
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

            $aliados = User::where('role', 'aliado')
                ->orderBy('name')
                ->get(['id', 'name', 'email']);
        } elseif ($user->role === 'aliado') {
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

            $aliados = collect();
        } else {
            $totalRecibido = 0;
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

        // Optimización 1: Cargar solo los campos necesarios de las relaciones
        $query = PaymentTransaction::with([
            'user:id,name,email,phone1',  // Solo campos necesarios
            'ally:id,company_name,contact_email,contact_phone'    // Solo campos necesarios
        ]);

        // Aplicar el mismo filtro de seguridad
        if ($user->role === 'admin') {
            // Admin puede ver cualquier transacción
        } elseif ($user->role === 'aliado') {
            // Aliado solo puede ver transacciones donde él es el aliado
            $ally = Ally::where('user_id', $user->id)->first(['id']); // Solo necesitamos el ID
            if ($ally) {
                $query->where('ally_id', $ally->id);
            } else {
                abort(403, 'No tienes permiso para ver esta transacción');
            }
        } else {
            // Usuario normal solo puede ver sus propias transacciones
            $query->where('user_id', $user->id);
        }

        // Optimización 2: Usar caché para transacciones que no cambian frecuentemente
        $transaccion = Cache::remember("transaccion.detalle.{$id}", 300, function () use ($query, $id) {
            return $query->findOrFail($id);
        });

        if (request()->wantsJson()) {
            // Optimización 3: Cache también para el modal
            $cacheKey = "transaccion.modal.{$id}";
            $html = Cache::remember($cacheKey, 300, function () use ($transaccion) {
                return view('Admin.transacciones.partials.detalle-modal', compact('transaccion'))->render();
            });

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

        // Verificar que la transacción esté en estado pendiente
        if (!in_array($transaccion->status, ['pending_manual_confirmation', 'awaiting_review'])) {
            return $this->jsonError('Solo se pueden confirmar transacciones pendientes.');
        }

        // Actualizar estado
        $transaccion->status = 'confirmed';
        $transaccion->save();

        // Registrar actividad - CON TODOS LOS CAMPOS REQUERIDOS
        ActivityLog::create([
            'user_id' => Auth::id(),
            'performed_by' => Auth::id(), // ← AGREGAR performed_by (generalmente el mismo que user_id)
            'description' => 'Transacción confirmada',
            'details' => "Transacción #{$transaccion->id} confirmada - Monto para aliado: $" . number_format($transaccion->amount_to_ally, 0, ',', '.'),
            'status' => 'completed',
            'activity_type' => 'transaction_confirmed',
            // Si hay más campos como 'ip_address', 'user_agent', etc. también hay que agregarlos
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
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
        if (Auth::user()->role !== 'admin') {
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

        // Registrar actividad - CON TODOS LOS CAMPOS REQUERIDOS
        ActivityLog::create([
            'user_id' => Auth::id(),
            'performed_by' => Auth::id(), // ← AGREGAR performed_by
            'description' => 'Transacción rechazada',
            'details' => "Transacción #{$transaccion->id} rechazada",
            'status' => 'failed',
            'activity_type' => 'transaction_rejected',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return $this->jsonSuccess('Transacción rechazada correctamente.', [
            'transaccion' => $transaccion
        ]);
    }

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

        foreach ($transacciones as $transaccion) {
            $transaccion->status = 'confirmed';
            $transaccion->save();

            ActivityLog::create([
                'user_id' => Auth::id(),
                'performed_by' => Auth::id(),
                'description' => 'Transacción confirmada (masiva)',
                'details' => "Transacción #{$transaccion->id} confirmada - Monto: $" . number_format($transaccion->amount_to_ally, 0, ',', '.'),
                'status' => 'completed',
                'activity_type' => 'transaction_confirmed_bulk',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => count($transacciones) . ' transacciones aprobadas correctamente'
        ]);
    }

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

        foreach ($transacciones as $transaccion) {
            $transaccion->status = 'failed';
            $transaccion->save();

            ActivityLog::create([
                'user_id' => Auth::id(),
                'performed_by' => Auth::id(),
                'description' => 'Transacción rechazada (masiva)',
                'details' => "Transacción #{$transaccion->id} rechazada",
                'status' => 'failed',
                'activity_type' => 'transaction_rejected_bulk',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => count($transacciones) . ' transacciones rechazadas correctamente'
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

        // Optimización: Cargar solo los campos necesarios
        $query = PaymentTransaction::with([
            'user:id,name,email,phone1',
            'ally:id,company_name,contact_email,contact_phone'
        ]);

        // Aplicar filtros de seguridad según el rol
        if ($user->role === 'admin') {
            // Admin puede ver cualquier comprobante
            // No se aplica filtro adicional
        } elseif ($user->role === 'aliado') {
            // Aliado solo puede ver comprobantes de sus transacciones
            $ally = Ally::where('user_id', $user->id)->first(['id']);

            if (!$ally) {
                abort(403, 'No tienes permiso para ver este comprobante');
            }

            $query->where('ally_id', $ally->id);
        } else {
            // Usuario normal solo puede ver comprobantes de sus transacciones
            $query->where('user_id', $user->id);
        }

        // Buscar la transacción
        $transaccion = $query->findOrFail($id);

        // Verificar permisos específicos (doble verificación)
        if ($user->role !== 'admin') {
            $ally = Ally::where('user_id', $user->id)->first();

            // Para aliados, verificar que la transacción les pertenece
            if ($user->role === 'aliado' && (!$ally || $transaccion->ally_id !== $ally->id)) {
                abort(403, 'No tienes permiso para ver este comprobante');
            }

            // Para usuarios normales, verificar que la transacción les pertenece
            if ($user->role !== 'aliado' && $transaccion->user_id !== $user->id) {
                abort(403, 'No tienes permiso para ver este comprobante');
            }
        }

        // Registrar en logs que se generó un comprobante (opcional)
        Log::info('Comprobante generado', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'transaction_id' => $transaccion->id,
            'ip' => request()->ip()
        ]);

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
        if ($user->role !== 'admin') {
            $query->where('ally_id', $user->id);
        }

        // Si es admin y seleccionó un aliado específico
        if ($user->role === 'admin' && $request->has('ally_id') && $request->ally_id) {
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
        if ($user->role === 'admin') {
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
            if ($user->role === 'admin') {
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
