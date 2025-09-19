<?php

namespace App\Services;

use App\Models\Payout;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PayoutService
{
    public function generarArchivoBNC($payouts, $tipoCuenta, $concepto = null)
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

    private function formatoBNC($payouts, $tipoCuenta, $concepto = null)
    {
        $contenido = "";
        $numeroRegistros = count($payouts);
        $montoTotal = $payouts->sum('commission_amount');
        
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
            $contenido .= str_pad($payout->ally_account_number, 20, ' ', STR_PAD_RIGHT); // Número de cuenta
            $contenido .= ($tipoCuenta == 'corriente') ? 'C' : 'A'; // Tipo de cuenta: C=Corriente, A=Ahorro
            
            // Información del beneficiario
            $contenido .= str_pad(substr($payout->ally->company_name, 0, 40), 40, ' ', STR_PAD_RIGHT); // Nombre
            $contenido .= str_pad($payout->ally->company_rif ?? '', 20, ' ', STR_PAD_RIGHT); // Cédula/RIF
            
            // Monto y referencia
            $contenido .= str_pad(number_format($payout->commission_amount, 2, '', ''), 15, '0', STR_PAD_LEFT); // Monto
            $contenido .= str_pad($payout->sale_reference, 20, ' ', STR_PAD_RIGHT); // Referencia
            
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

    public function confirmarPagos($payoutIds, $fechaPago, $referenciaPago, $comprobante = null)
    {
        $payouts = Payout::whereIn('id', $payoutIds)
            ->where('status', 'processing')
            ->get();

        if ($payouts->isEmpty()) {
            throw new \Exception('No hay pagos en estado procesando para confirmar');
        }

        $rutaComprobante = null;
        if ($comprobante) {
            $rutaComprobante = $comprobante->store('comprobantes_pagos', 'public');
        }

        // Actualizar estado a pagado
        Payout::whereIn('id', $payouts->pluck('id'))->update([
            'status' => 'paid',
            'payment_date' => $fechaPago,
            'payment_reference' => $referenciaPago,
            'payment_proof' => $rutaComprobante,
            'notes' => 'Pago confirmado el ' . now()->format('Y-m-d H:i:s')
        ]);

        Log::info('Pagos confirmados exitosamente', [
            'cantidad_pagos' => $payouts->count(),
            'monto_total' => $payouts->sum('commission_amount'),
            'referencia_pago' => $referenciaPago
        ]);

        return [
            'pagos_confirmados' => $payouts->count(),
            'monto_total' => $payouts->sum('commission_amount'),
            'fecha_pago' => $fechaPago
        ];
    }
}