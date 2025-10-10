<?php

namespace App\Services;

use App\Models\Payout;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class PayoutService
{
    /**
     * Crea un registro de payout básico para desarrollo
     */
    public function createPayout(Sale $venta, array $aliadoData, array $bncResponse): ?Payout
    {
        // Si no hay aliado, no crear payout
        if (!$aliadoData['has_aliado'] || empty($aliadoData['aliado_id'])) {
            Log::info('No se crea payout - No hay aliado asociado', ['venta_id' => $venta->id]);
            return null;
        }

        try {
            $payoutData = [
                // Foreign keys - OBLIGATORIOS
                'sale_id' => $venta->id,
                'ally_id' => $aliadoData['aliado_id'],

                // Amount fields - Pago al aliado
                'sale_amount' => $venta->monto_total,
                'commission_percentage' => $aliadoData['comision_porcentaje'],
                'commission_amount' => $aliadoData['monto_comision'],
                'net_amount' => $aliadoData['monto_neto'],

                // Campos básicos de transferencia a empresa (simulados para desarrollo)
                'company_transfer_amount' => $venta->monto_total * 0.90, // 90% del monto
                'company_commission' => $venta->monto_total * 0.10, // 10% comisión empresa
                'company_account' => 'CUENTA_EMPRESA_DEV',
                'company_bank' => 'BANCO_DE_DESARROLLO',
                'company_transfer_reference' => 'DEV-TRF-' . $venta->id,
                'company_transfer_status' => 'completed',
                'company_transfer_date' => now(),

                // Status and dates
                'status' => 'pending',
                'generation_date' => now(),
                'sale_reference' => $venta->referencia_banco ?? 'SALE-' . $venta->id,

                // Payment details - Aliado
                'ally_payment_method' => 'transfer',

                // Response data simulada
                'company_transfer_response' => json_encode([
                    'transfer_id' => 'DEV-' . uniqid(),
                    'status' => 'completed',
                    'timestamp' => now()->toISOString(),
                    'environment' => 'development'
                ])
            ];

            Log::info('Creando payout para desarrollo:', [
                'sale_id' => $venta->id,
                'ally_id' => $aliadoData['aliado_id'],
                'net_amount' => $payoutData['net_amount']
            ]);

            $payout = Payout::create($payoutData);

            Log::info('Payout creado exitosamente', [
                'payout_id' => $payout->id,
                'sale_id' => $venta->id
            ]);

            return $payout;

        } catch (\Exception $e) {
            Log::error('Error al crear payout: ' . $e->getMessage(), [
                'venta_id' => $venta->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Obtiene payouts pendientes
     */
    public function obtenerPagosPendientes()
    {
        return Payout::with(['ally', 'sale'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Obtiene estadísticas básicas de payouts
     */
    public function obtenerEstadisticasPayouts(): array
    {
        $totalPendiente = Payout::where('status', 'pending')->sum('net_amount');
        $totalPagado = Payout::where('status', 'completed')->sum('net_amount');
        $totalProcesando = Payout::where('status', 'processing')->sum('net_amount');
        $totalRevertido = Payout::where('status', 'reverted')->sum('net_amount');

        return [
            'total_pendiente' => $totalPendiente,
            'total_pagado' => $totalPagado,
            'total_procesando' => $totalProcesando,
            'total_revertido' => $totalRevertido,
            'total_payouts' => Payout::count(),
            'total_aliados' => Payout::distinct('ally_id')->count('ally_id'),
            'payouts_por_estado' => [
                'pending' => Payout::where('status', 'pending')->count(),
                'processing' => Payout::where('status', 'processing')->count(),
                'completed' => Payout::where('status', 'completed')->count(),
                'reverted' => Payout::where('status', 'reverted')->count(),
            ]
        ];
    }

    /**
     * Simula la confirmación de pagos (para desarrollo)
     */
    public function simularConfirmacionPagos(array $payoutIds): array
    {
        DB::beginTransaction();
        try {
            $payouts = Payout::whereIn('id', $payoutIds)
                ->where('status', 'pending')
                ->get();

            if ($payouts->isEmpty()) {
                throw new \Exception('No hay payouts pendientes para confirmar');
            }

            Payout::whereIn('id', $payouts->pluck('id'))->update([
                'status' => 'completed',
                'payment_date' => now(),
                'payment_reference' => 'DEV-PAY-' . uniqid()
            ]);

            DB::commit();

            Log::info('Pagos simulados confirmados', [
                'cantidad_pagos' => $payouts->count(),
                'monto_total' => $payouts->sum('net_amount')
            ]);

            return [
                'pagos_confirmados' => $payouts->count(),
                'monto_total' => $payouts->sum('net_amount'),
                'referencia_pago' => 'DEV-PAY-' . uniqid()
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error simulando confirmación: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Genera archivo de pago a proveedores en formato BNC
     */
    public function generarArchivoPagosBNC($fechaInicio, $fechaFin, $tipoCuenta, $concepto = null): array
    {
        try {
            // Lógica para generar archivo BNC
            $payouts = Payout::with(['ally', 'sale'])
                ->where('status', 'pending')
                ->whereBetween('created_at', [$fechaInicio, $fechaFin])
                ->get();

            if ($payouts->isEmpty()) {
                throw new \Exception('No hay pagos pendientes para el rango de fechas seleccionado');
            }

            // Generar contenido del archivo BNC
            $contenidoArchivo = $this->formatearArchivoBNC($payouts, $tipoCuenta, $concepto);
            
            // Guardar archivo
            $nombreArchivo = 'pagos_bnc_' . date('Ymd_His') . '.txt';
            $rutaArchivo = storage_path('app/pagos_bnc/' . $nombreArchivo);
            
            File::ensureDirectoryExists(dirname($rutaArchivo));
            File::put($rutaArchivo, $contenidoArchivo);

            // Actualizar payouts como "en_proceso"
            Payout::whereIn('id', $payouts->pluck('id'))->update(['status' => 'processing']);

            Log::info('Archivo BNC generado exitosamente', [
                'archivo' => $nombreArchivo,
                'cantidad_pagos' => $payouts->count(),
                'monto_total' => $payouts->sum('net_amount')
            ]);

            return [
                'archivo' => $nombreArchivo,
                'ruta' => $rutaArchivo,
                'cantidad_pagos' => $payouts->count(),
                'monto_total' => $payouts->sum('net_amount'),
                'fecha_generacion' => now()->toDateTimeString(),
                'rango_fechas' => [
                    'inicio' => $fechaInicio,
                    'fin' => $fechaFin
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Error generando archivo BNC: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Confirma pagos procesados
     */
    public function confirmarPagosProcesados(array $payoutIds, $fechaPago, $referenciaPago, $archivoComprobante = null): array
    {
        DB::beginTransaction();
        try {
            $payouts = Payout::with(['ally'])
                ->whereIn('id', $payoutIds)
                ->where('status', 'processing')
                ->get();

            if ($payouts->isEmpty()) {
                throw new \Exception('No hay pagos en proceso para confirmar');
            }

            // Guardar archivo de comprobante si existe
            $rutaComprobante = null;
            if ($archivoComprobante) {
                $rutaComprobante = $archivoComprobante->store('comprobantes_pagos', 'public');
            }

            // Actualizar payouts como completados
            Payout::whereIn('id', $payouts->pluck('id'))->update([
                'status' => 'completed',
                'payment_date' => $fechaPago,
                'payment_reference' => $referenciaPago,
                'payment_proof_path' => $rutaComprobante,
                'confirmed_at' => now()
            ]);

            DB::commit();

            Log::info('Pagos confirmados exitosamente', [
                'cantidad_pagos' => $payouts->count(),
                'monto_total' => $payouts->sum('net_amount'),
                'referencia_pago' => $referenciaPago
            ]);

            return [
                'pagos_confirmados' => $payouts->count(),
                'monto_total' => $payouts->sum('net_amount'),
                'referencia_pago' => $referenciaPago,
                'fecha_pago' => $fechaPago,
                'comprobante' => $rutaComprobante
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error confirmando pagos: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Descarga archivo BNC generado
     */
    public function descargarArchivoBNC($archivo): array
    {
        $rutaArchivo = storage_path('app/pagos_bnc/' . $archivo);
        
        if (!File::exists($rutaArchivo)) {
            throw new \Exception('El archivo solicitado no existe');
        }

        return [
            'content' => File::get($rutaArchivo),
            'headers' => [
                'Content-Type' => 'text/plain',
                'Content-Disposition' => 'attachment; filename="' . $archivo . '"',
            ]
        ];
    }

    /**
     * Obtiene pagos por filtros
     */
    public function obtenerPagosPorFiltro(Request $request)
    {
        $query = Payout::with(['ally', 'sale']);

        // Filtro por estado
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filtro por aliado
        if ($request->has('ally_id') && $request->ally_id) {
            $query->where('ally_id', $request->ally_id);
        }

        // Filtro por fecha
        if ($request->has('fecha_inicio') && $request->fecha_inicio) {
            $query->whereDate('created_at', '>=', $request->fecha_inicio);
        }

        if ($request->has('fecha_fin') && $request->fecha_fin) {
            $query->whereDate('created_at', '<=', $request->fecha_fin);
        }

        // Filtro por monto
        if ($request->has('monto_min') && $request->monto_min) {
            $query->where('net_amount', '>=', $request->monto_min);
        }

        if ($request->has('monto_max') && $request->monto_max) {
            $query->where('net_amount', '<=', $request->monto_max);
        }

        // Búsqueda por referencia
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('payment_reference', 'like', "%{$search}%")
                  ->orWhere('sale_reference', 'like', "%{$search}%")
                  ->orWhereHas('ally', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($request->get('per_page', 15));
    }

    /**
     * Revierte un pago
     */
    public function revertirPago($payoutId, $motivo): void
    {
        DB::beginTransaction();
        try {
            $payout = Payout::findOrFail($payoutId);

            if ($payout->status !== 'completed') {
                throw new \Exception('Solo se pueden revertir pagos completados');
            }

            $payout->update([
                'status' => 'reverted',
                'reversion_reason' => $motivo,
                'reverted_at' => now()
            ]);

            DB::commit();

            Log::info('Pago revertido exitosamente', [
                'payout_id' => $payoutId,
                'motivo' => $motivo
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error revirtiendo pago: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Obtiene el historial de un payout específico
     */
    public function obtenerHistorialPayout($payoutId): array
    {
        $payout = Payout::with(['ally', 'sale'])->findOrFail($payoutId);

        return [
            'payout' => $payout,
            'historial' => [
                'creacion' => $payout->created_at,
                'generacion' => $payout->generation_date,
                'procesamiento' => $payout->updated_at,
                'confirmacion' => $payout->confirmed_at,
                'reversion' => $payout->reverted_at,
            ],
            'transacciones' => [
                'monto_venta' => $payout->sale_amount,
                'comision_porcentaje' => $payout->commission_percentage,
                'comision_monto' => $payout->commission_amount,
                'monto_neto' => $payout->net_amount,
            ]
        ];
    }

    /**
     * Formatea el archivo BNC (debes adaptar según especificaciones del banco)
     */
    private function formatearArchivoBNC($payouts, $tipoCuenta, $concepto): string
    {
        $contenido = "";
        $numeroRegistro = 1;
        $montoTotal = 0;
        
        // Cabecera del archivo
        $contenido .= "HEADER|" . date('Ymd') . "|" . $payouts->count() . "|" . now()->format('His') . "\n";
        
        foreach ($payouts as $payout) {
            $aliado = $payout->ally;
            $montoTotal += $payout->net_amount;
            
            // Formato básico BNC - AJUSTAR SEGÚN ESPECIFICACIONES REALES DEL BANCO
            $linea = sprintf(
                "DETALLE|%04d|%s|%s|%s|%.2f|%s|%s|%s\n",
                $numeroRegistro,
                $aliado->bank_account_number ?? 'SIN_CUENTA',
                $aliado->bank_identification ?? 'SIN_ID',
                strtoupper($tipoCuenta),
                $payout->net_amount,
                $this->limpiarTexto($aliado->name),
                $concepto ?? 'PAGO COMISION',
                $payout->sale_reference
            );
            
            $contenido .= $linea;
            $numeroRegistro++;
        }

        // Pie del archivo
        $contenido .= "TRAILER|" . $payouts->count() . "|" . number_format($montoTotal, 2, '.', '') . "\n";

        return $contenido;
    }

    /**
     * Limpia texto para formato BNC (remueve caracteres especiales)
     */
    private function limpiarTexto($texto): string
    {
        return preg_replace('/[^a-zA-Z0-9\s]/', '', $texto);
    }

    /**
     * Obtiene resumen de payouts por aliado
     */
    public function obtenerResumenPorAliado(): array
    {
        return Payout::with('ally')
            ->select('ally_id', DB::raw('COUNT(*) as total_payouts'), DB::raw('SUM(net_amount) as total_monto'))
            ->groupBy('ally_id')
            ->get()
            ->map(function($item) {
                return [
                    'aliado_id' => $item->ally_id,
                    'aliado_nombre' => $item->ally->name ?? 'N/A',
                    'total_payouts' => $item->total_payouts,
                    'total_monto' => $item->total_monto,
                    'estados' => Payout::where('ally_id', $item->ally_id)
                        ->select('status', DB::raw('COUNT(*) as count'))
                        ->groupBy('status')
                        ->get()
                        ->pluck('count', 'status')
                ];
            })
            ->toArray();
    }
}