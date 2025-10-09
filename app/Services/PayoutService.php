<?php

namespace App\Services;

use App\Models\Payout;
use App\Models\Sale;
use App\Models\Ally;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\LengthAwarePaginator;

class PayoutService
{
    /**
     * Crea un registro de payout
     */
    public function createPayout(Sale $venta, array $aliadoData, array $bncResponse): ?Payout
    {
        // Si no hay aliado, no crear payout
        if (!$aliadoData['has_aliado'] || empty($aliadoData['aliado_id'])) {
            Log::info('No se crea payout - No hay aliado asociado', [
                'venta_id' => $venta->id,
                'has_aliado' => $aliadoData['has_aliado'],
                'aliado_id' => $aliadoData['aliado_id'] ?? null
            ]);
            return null;
        }

        try {
            // Calcular comisión para la empresa
            $companyCommission = $this->calculateCompanyCommission($venta->monto_total);
            $companyTransferAmount = $venta->monto_total - $companyCommission;

            // Validar que tenemos todos los datos requeridos
            if (empty($venta->id) || empty($aliadoData['aliado_id'])) {
                Log::error('Datos incompletos para crear payout', [
                    'venta_id' => $venta->id,
                    'aliado_id' => $aliadoData['aliado_id']
                ]);
                return null;
            }

            $payoutData = [
                // Foreign keys - OBLIGATORIOS
                'sale_id' => $venta->id,
                'ally_id' => $aliadoData['aliado_id'],

                // Amount fields - Pago al aliado
                'sale_amount' => $venta->monto_total,
                'commission_percentage' => $aliadoData['comision_porcentaje'],
                'commission_amount' => $aliadoData['monto_comision'],
                'net_amount' => $aliadoData['monto_neto'],

                // Transferencia a la empresa
                'company_transfer_amount' => $companyTransferAmount,
                'company_commission' => $companyCommission,
                'company_account' => env('COMPANY_BS_ACCOUNT', '987654321'),
                'company_bank' => env('COMPANY_BS_BANK_NAME', 'Banco Nacional de Crédito'),
                'company_transfer_reference' => 'TRF-' . ($venta->transaction_id ?? $venta->id ?? uniqid()),
                'company_transfer_status' => 'completed',
                'company_transfer_date' => now(),

                // Status and dates - OBLIGATORIOS
                'status' => 'pending',
                'generation_date' => now(),
                'sale_reference' => $venta->transaction_id ?? 'SALE-' . $venta->id,

                // Payment details - Aliado
                'ally_payment_method' => 'transfer',

                // Response data
                'company_transfer_response' => json_encode([
                    'transfer_id' => uniqid('TRF_'),
                    'status' => 'completed',
                    'timestamp' => now()->toISOString(),
                    'sale_id' => $venta->id
                ])
            ];

            Log::info('Creando payout con datos:', $payoutData);

            $payout = Payout::create($payoutData);

            Log::info('Payout creado exitosamente', [
                'payout_id' => $payout->id,
                'sale_id' => $venta->id,
                'ally_id' => $aliadoData['aliado_id']
            ]);

            return $payout;
        } catch (\Exception $e) {
            Log::error('Error al crear payout: ' . $e->getMessage(), [
                'venta_id' => $venta->id,
                'aliado_id' => $aliadoData['aliado_id'] ?? null,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    private function calculateCompanyCommission(float $amount): float
    {
        $commissionRate = env('COMPANY_COMMISSION_RATE', 3.5) / 100;
        $fixedFee = env('COMPANY_FIXED_FEE', 2.00);

        return round(($amount * $commissionRate) + $fixedFee, 2);
    }

    /**
     * Genera archivo de pago a proveedores en formato BNC
     */
    public function generarArchivoPagosBNC(string $fechaInicio, string $fechaFin, string $tipoCuenta, ?string $concepto = null): array
    {
        // Obtener payouts pendientes
        $payouts = Payout::with(['ally', 'sale'])
            ->where('estado', 'pendiente')
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->get();

        if ($payouts->isEmpty()) {
            throw new \Exception('No hay pagos pendientes en el rango de fechas especificado');
        }

        // Generar archivo en formato BNC
        $archivoNombre = $this->generarArchivoBNC($payouts, $tipoCuenta, $concepto);

        // Actualizar estado a "procesando"
        Payout::whereIn('id', $payouts->pluck('id'))
            ->update(['estado' => 'procesando']);

        Log::info('Archivo de pagos BNC generado', [
            'archivo' => $archivoNombre,
            'cantidad_pagos' => $payouts->count(),
            'monto_total' => $payouts->sum('monto_comision')
        ]);

        return [
            'archivo' => $archivoNombre,
            'ruta_descarga' => Storage::url('pagos/bnc/' . $archivoNombre),
            'total_pagos' => $payouts->count(),
            'monto_total' => $payouts->sum('monto_comision'),
            'fecha_generacion' => now()->format('Y-m-d H:i:s'),
            'formato' => 'BNC'
        ];
    }

    /**
     * Genera el archivo BNC
     */
    public function generarArchivoBNC($payouts, $tipoCuenta, $concepto = null): string
    {
        $fecha = now()->format('Ymd_His');
        $nombreArchivo = "pagos_bnc_{$fecha}.txt";

        // Crear directorio si no existe
        if (!Storage::exists('pagos/bnc')) {
            Storage::makeDirectory('pagos/bnc');
        }

        $contenido = $this->formatoBNC($payouts, $tipoCuenta, $concepto);
        Storage::put('pagos/bnc/' . $nombreArchivo, $contenido);

        return $nombreArchivo;
    }

    /**
     * Formato BNC para el archivo de pagos
     */
    public function formatoBNC($payouts, $tipoCuenta, $concepto = null): string
    {
        $contenido = "";
        $numeroRegistros = count($payouts);
        $montoTotal = $payouts->sum('monto_comision');

        // Header del archivo
        $contenido .= "H"; // Tipo de registro: Header
        $contenido .= now()->format('Ymd'); // Fecha de procesamiento
        $contenido .= str_pad($numeroRegistros, 6, '0', STR_PAD_LEFT); // Número de transacciones
        $contenido .= str_pad(number_format($montoTotal, 2, '', ''), 15, '0', STR_PAD_LEFT); // Monto total
        $contenido .= "\n";

        // Detalle de cada pago
        foreach ($payouts as $index => $payout) {
            $consecutivo = $index + 1;

            $contenido .= "D"; // Tipo de registro: Detalle
            $contenido .= str_pad($consecutivo, 6, '0', STR_PAD_LEFT); // Número consecutivo

            // Información de la cuenta destino (BNC)
            // NOTA: Ajusta estos campos según tu estructura real de aliados
            $numeroCuenta = $payout->ally->cuenta_bancaria ?? '00000000000000000000';
            $contenido .= str_pad($numeroCuenta, 20, ' ', STR_PAD_RIGHT); // Número de cuenta
            $contenido .= ($tipoCuenta == 'corriente') ? 'C' : 'A'; // Tipo de cuenta: C=Corriente, A=Ahorro

            // Información del beneficiario
            $nombreBeneficiario = $payout->ally->company_name ?? $payout->ally->user->name ?? 'Beneficiario';
            $contenido .= str_pad(substr($nombreBeneficiario, 0, 40), 40, ' ', STR_PAD_RIGHT); // Nombre
            $contenido .= str_pad($payout->ally->company_rif ?? '', 20, ' ', STR_PAD_RIGHT); // Cédula/RIF

            // Monto y referencia
            $contenido .= str_pad(number_format($payout->monto_comision, 2, '', ''), 15, '0', STR_PAD_LEFT); // Monto
            $contenido .= str_pad($payout->referencia_banco ?? $payout->id, 20, ' ', STR_PAD_RIGHT); // Referencia

            // Concepto del pago
            $conceptoPago = $concepto ?? 'PAGO COMISION ' . now()->format('Ym');
            $contenido .= str_pad(substr($conceptoPago, 0, 30), 30, ' ', STR_PAD_RIGHT); // Concepto

            $contenido .= "\n";
        }

        // Footer del archivo
        $contenido .= "T"; // Tipo de registro: Trailer
        $contenido .= str_pad($numeroRegistros, 6, '0', STR_PAD_LEFT); // Total de registros
        $contenido .= str_pad(number_format($montoTotal, 2, '', ''), 15, '0', STR_PAD_LEFT); // Monto total
        $contenido .= "\n";

        return $contenido;
    }

    /**
     * Confirma pagos procesados
     */
    public function confirmarPagosProcesados(array $payoutIds, string $fechaPago, string $referenciaPago, $archivoComprobante = null): array
    {
        DB::beginTransaction();
        try {
            $payouts = Payout::whereIn('id', $payoutIds)
                ->where('estado', 'procesando')
                ->get();

            if ($payouts->isEmpty()) {
                throw new \Exception('No hay pagos en estado procesando para confirmar');
            }

            $rutaComprobante = null;
            if ($archivoComprobante) {
                $rutaComprobante = $archivoComprobante->store('comprobantes_pagos', 'public');
            }

            Payout::whereIn('id', $payouts->pluck('id'))->update([
                'estado' => 'pagado',
                'fecha_pago' => $fechaPago,
                'referencia_pago' => $referenciaPago,
                'comprobante_pago' => $rutaComprobante,
                'notas' => 'Pago confirmado el ' . now()->format('Y-m-d H:i:s')
            ]);

            DB::commit();

            Log::info('Pagos confirmados exitosamente', [
                'cantidad_pagos' => $payouts->count(),
                'monto_total' => $payouts->sum('monto_comision'),
                'referencia_pago' => $referenciaPago
            ]);

            return [
                'pagos_confirmados' => $payouts->count(),
                'monto_total' => $payouts->sum('monto_comision'),
                'fecha_pago' => $fechaPago
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error confirmando pagos: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Descarga archivo de pagos BNC
     */
    public function descargarArchivoBNC(string $archivo): array
    {
        $filePath = 'pagos/bnc/' . $archivo;

        if (!Storage::exists($filePath)) {
            throw new \Exception('Archivo BNC no encontrado');
        }

        return [
            'content' => Storage::get($filePath),
            'headers' => [
                'Content-Type' => 'text/plain',
                'Content-Disposition' => 'attachment; filename="' . $archivo . '"',
            ]
        ];
    }

    /**
     * Obtiene pagos pendientes
     */
    public function obtenerPagosPendientes()
    {
        return Payout::with(['ally', 'sale'])
            ->where('estado', 'pendiente')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Obtiene pagos por filtros
     */
    public function obtenerPagosPorFiltro(Request $request): LengthAwarePaginator
    {
        $query = Payout::with(['ally', 'sale']);

        // Filtrar por estado
        if ($request->has('status') && $request->status != 'all') {
            $query->where('estado', $request->status);
        }

        // Filtrar por rango de fechas
        if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
            $query->whereBetween('created_at', [
                $request->fecha_inicio,
                $request->fecha_fin
            ]);
        }

        // Filtrar por aliado
        if ($request->has('aliado_id')) {
            $query->where('aliado_id', $request->aliado_id);
        }

        return $query->orderBy('created_at', 'desc')->paginate(20);
    }

    /**
     * Obtiene estadísticas de payouts
     */
    public function obtenerEstadisticasPayouts(): array
    {
        $totalPendiente = Payout::where('estado', 'pendiente')->sum('monto_comision');
        $totalProcesando = Payout::where('estado', 'procesando')->sum('monto_comision');
        $totalPagado = Payout::where('estado', 'pagado')->sum('monto_comision');
        $totalAliados = Payout::distinct('aliado_id')->count('aliado_id');

        return [
            'total_pendiente' => $totalPendiente,
            'total_procesando' => $totalProcesando,
            'total_pagado' => $totalPagado,
            'total_aliados' => $totalAliados,
            'total_general' => $totalPendiente + $totalProcesando + $totalPagado
        ];
    }

    /**
     * Revertir pago a estado pendiente
     */
    public function revertirPago(int $payoutId, string $motivo): bool
    {
        DB::beginTransaction();
        try {
            $payout = Payout::where('id', $payoutId)
                ->where('estado', 'procesando')
                ->first();

            if (!$payout) {
                throw new \Exception('Pago no encontrado o no está en estado procesando');
            }

            $payout->update([
                'estado' => 'pendiente',
                'notas' => ($payout->notas ?? '') . " | Revertido: {$motivo}"
            ]);

            DB::commit();
            Log::info('Pago revertido exitosamente', ['payout_id' => $payoutId]);
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error revirtiendo pago: ' . $e->getMessage());
            throw $e;
        }
    }
}
