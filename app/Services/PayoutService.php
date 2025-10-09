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
     * Obtiene payouts pendientes (método básico)
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

        return [
            'total_pendiente' => $totalPendiente,
            'total_pagado' => $totalPagado,
            'total_payouts' => Payout::count(),
            'total_aliados' => Payout::distinct('ally_id')->count('ally_id')
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
}