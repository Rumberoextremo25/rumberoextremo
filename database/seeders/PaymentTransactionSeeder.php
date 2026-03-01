<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentTransaction;
use App\Models\User;
use App\Models\Ally;
use Carbon\Carbon;

class PaymentTransactionSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener usuarios
        $users = User::where('user_type', 'user')->get();
        
        // Obtener el primer aliado
        $aliado = Ally::first();

        if ($users->isEmpty()) {
            $this->command->error('❌ No hay usuarios. Crea usuarios primero.');
            return;
        }

        if (!$aliado) {
            $this->command->error('❌ No hay aliados. Crea un aliado primero.');
            return;
        }

        $this->command->info("✅ Generando transacciones para: {$aliado->name}");

        // Transacciones específicas para demostración
        $transacciones = [
            // Confirmadas (varias fechas)
            ['monto' => 500000, 'descuento' => 10, 'estado' => 'confirmed', 'metodo' => 'pago_movil', 'dias' => 2],
            ['monto' => 750000, 'descuento' => 15, 'estado' => 'confirmed', 'metodo' => 'transferencia_bancaria', 'dias' => 3],
            ['monto' => 1200000, 'descuento' => 20, 'estado' => 'confirmed', 'metodo' => 'pago_movil', 'dias' => 5],
            ['monto' => 250000, 'descuento' => 5, 'estado' => 'confirmed', 'metodo' => 'pago_movil', 'dias' => 10],
            ['monto' => 1800000, 'descuento' => 12, 'estado' => 'confirmed', 'metodo' => 'transferencia_bancaria', 'dias' => 15],
            
            // Pendientes
            ['monto' => 300000, 'descuento' => 5, 'estado' => 'pending_manual_confirmation', 'metodo' => 'transferencia_bancaria', 'dias' => 0],
            ['monto' => 450000, 'descuento' => 12, 'estado' => 'pending_manual_confirmation', 'metodo' => 'pago_movil', 'dias' => 1],
            ['monto' => 600000, 'descuento' => 8, 'estado' => 'pending_manual_confirmation', 'metodo' => 'transferencia_bancaria', 'dias' => 0],
            
            // En revisión
            ['monto' => 800000, 'descuento' => 8, 'estado' => 'awaiting_review', 'metodo' => 'transferencia_bancaria', 'dias' => 1],
            ['monto' => 950000, 'descuento' => 15, 'estado' => 'awaiting_review', 'metodo' => 'pago_movil', 'dias' => 2],
            
            // Fallidas
            ['monto' => 250000, 'descuento' => 0, 'estado' => 'failed', 'metodo' => 'pago_movil', 'dias' => 7],
            ['monto' => 150000, 'descuento' => 5, 'estado' => 'failed', 'metodo' => 'transferencia_bancaria', 'dias' => 14],
        ];

        foreach ($transacciones as $t) {
            $user = $users->random();
            $comision = round($t['monto'] * 0.1);
            $neto = $t['monto'] - $comision;
            
            PaymentTransaction::create([
                'user_id' => $user->id,
                'ally_id' => $aliado->id,
                'original_amount' => $t['monto'],
                'discount_percentage' => $t['descuento'],
                'amount_to_ally' => $neto,
                'platform_commission' => $comision,
                'payment_method' => $t['metodo'],
                'status' => $t['estado'],
                'reference_code' => 'REF-' . strtoupper(uniqid()),
                'created_at' => Carbon::now()->subDays($t['dias']),
                'updated_at' => Carbon::now()->subDays($t['dias']),
            ]);
        }

        $this->command->info('✅ ' . count($transacciones) . ' transacciones creadas para el aliado.');
    }
}