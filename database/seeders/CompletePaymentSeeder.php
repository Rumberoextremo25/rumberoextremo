<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Ally;
use App\Models\Sale;
use App\Models\Payment;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\Payout;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CompletePaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Preguntar si quiere limpiar datos
        if ($this->command->confirm('¿Deseas ELIMINAR todos los datos existentes antes de crear nuevos?', false)) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            PaymentTransaction::truncate();
            Payout::truncate();
            Sale::truncate();
            Order::truncate();
            Payment::truncate();
            Ally::truncate();
            User::where('user_type', 'user')->delete();
            User::where('user_type', 'aliado')->delete();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            $this->command->info('🗑️ Todas las tablas limpiadas');
        }

        // ===========================================
        // 1. CREAR USUARIOS (clientes)
        // ===========================================
        $this->command->info('⏳ Creando usuarios clientes...');
        $users = collect();
        $nombres = ['Carlos', 'María', 'José', 'Ana', 'Luis', 'Carmen', 'Jorge', 'Laura', 'Pedro', 'Sofía'];
        $apellidos = ['González', 'Rodríguez', 'Pérez', 'Martínez', 'Sánchez', 'García', 'Díaz', 'López', 'Fernández', 'Torres'];

        for ($i = 0; $i < 15; $i++) {
            $nombre = $nombres[array_rand($nombres)] . ' ' . $apellidos[array_rand($apellidos)];
            $email = strtolower(str_replace(' ', '.', $nombre)) . $i . '@test.com';
            
            $user = User::create([
                'name' => $nombre,
                'email' => $email,
                'password' => Hash::make('password'),
                'user_type' => 'user',
                'email_verified_at' => now(),
                'created_at' => Carbon::now()->subDays(rand(30, 180)),
                'updated_at' => now(),
            ]);
            $users->push($user);
        }
        $this->command->info('✅ ' . $users->count() . ' usuarios clientes creados');

        // ===========================================
        // 2. CREAR DOS ALIADOS (comercios)
        // ===========================================
        $this->command->info('⏳ Creando aliados...');
        $aliados = collect();

        // ALIADO 1: Restaurante La Esquina
        $userAliado1 = User::create([
            'name' => 'Restaurante La Esquina',
            'email' => 'contacto@laesquina.com',
            'password' => Hash::make('password'),
            'user_type' => 'aliado',
            'email_verified_at' => now(),
            'created_at' => Carbon::now()->subMonths(6),
            'updated_at' => now(),
        ]);

        $aliado1 = Ally::create([
            'user_id' => $userAliado1->id,
            'company_name' => 'Restaurante La Esquina C.A.',
            'company_rif' => 'J-12345678-9',
            'description' => 'Restaurante de comida tradicional venezolana con los mejores sabores',
            'image_url' => '/storage/allies/la-esquina.jpg',
            'product_images' => json_encode([
                '/storage/products/pabellon.jpg',
                '/storage/products/arepas.jpg',
                '/storage/products/cachapa.jpg'
            ]),
            'category_id' => 1,
            'sub_category_id' => 2,
            'business_type_id' => 1,
            'website_url' => 'https://laesquina.com',
            'discount' => '15% en todos los platos',
            'contact_person_name' => 'Juan Pérez',
            'contact_email' => 'juan@laesquina.com',
            'contact_phone' => '04121234567',
            'contact_phone_alt' => '04241234567',
            'company_address' => 'Av. Principal, Centro Comercial Plaza, Local 5, Caracas',
            'notes' => 'Aliado preferencial, buen volumen de ventas',
            'registered_at' => Carbon::now()->subMonths(6),
            'status' => 'activo',
            'created_at' => Carbon::now()->subMonths(6),
            'updated_at' => now(),
        ]);
        $aliados->push($aliado1);
        $this->command->info('✅ Aliado 1 creado: ' . $aliado1->company_name);

        // ALIADO 2: Tienda Deportiva El Gol
        $userAliado2 = User::create([
            'name' => 'Tienda Deportiva El Gol',
            'email' => 'ventas@elgol.com',
            'password' => Hash::make('password'),
            'user_type' => 'aliado',
            'email_verified_at' => now(),
            'created_at' => Carbon::now()->subMonths(4),
            'updated_at' => now(),
        ]);

        $aliado2 = Ally::create([
            'user_id' => $userAliado2->id,
            'company_name' => 'El Gol Sports S.A.S.',
            'company_rif' => 'J-87654321-0',
            'description' => 'Tienda especializada en artículos deportivos de las mejores marcas',
            'image_url' => '/storage/allies/el-gol.jpg',
            'product_images' => json_encode([
                '/storage/products/balon.jpg',
                '/storage/products/camiseta.jpg',
                '/storage/products/zapatos.jpg'
            ]),
            'category_id' => 2,
            'sub_category_id' => 5,
            'business_type_id' => 2,
            'website_url' => 'https://elgol.com',
            'discount' => '10% en compras mayores a 100$',
            'contact_person_name' => 'María Rodríguez',
            'contact_email' => 'maria@elgol.com',
            'contact_phone' => '04131234567',
            'contact_phone_alt' => '04241234568',
            'company_address' => 'C.C. Sambil, Nivel 3, Local 45, Caracas',
            'notes' => 'Aliado nuevo con buen potencial',
            'registered_at' => Carbon::now()->subMonths(4),
            'status' => 'activo',
            'created_at' => Carbon::now()->subMonths(4),
            'updated_at' => now(),
        ]);
        $aliados->push($aliado2);
        $this->command->info('✅ Aliado 2 creado: ' . $aliado2->company_name);

        $this->command->info('✅ Total: ' . $aliados->count() . ' aliados creados');

        // ===========================================
        // 3. CREAR PAYMENTS Y ORDERS
        // ===========================================
        $this->command->info('⏳ Creando payments y orders...');
        $payments = [];
        $orders = [];

        for ($i = 0; $i < 30; $i++) {
            $fecha = Carbon::now()->subDays(rand(0, 90))->subHours(rand(0, 23));
            
            $payment = Payment::create([
                'created_at' => $fecha,
                'updated_at' => $fecha,
            ]);
            $payments[] = $payment;

            $order = Order::create([
                'total' => $this->getRandomAmount(),
                'status' => 'completed',
                'created_at' => $fecha,
                'updated_at' => $fecha,
            ]);
            $orders[] = $order;
        }
        $this->command->info('✅ ' . count($payments) . ' payments creados');
        $this->command->info('✅ ' . count($orders) . ' orders creados');

        // ===========================================
        // 4. CREAR PAYMENT TRANSACTIONS
        // ===========================================
        $this->command->info('⏳ Creando payment_transactions...');
        $metodosPago = ['pago_movil', 'transferencia_bancaria', 'tarjeta_credito'];
        
        $transactions = [];

        for ($i = 0; $i < 50; $i++) {
            $user = $users->random();
            $aliado = $aliados->random();
            $payment = $payments[array_rand($payments)];
            $order = $orders[array_rand($orders)];
            
            $montoOriginal = $this->getRandomAmount();
            
            // Parsear descuento del aliado (ej: "15% en alquiler" -> 15)
            $descuentoString = $aliado->discount ?? '0';
            preg_match('/(\d+)/', $descuentoString, $matches);
            $descuentoPorcentaje = isset($matches[1]) ? (float) $matches[1] : 0;
            
            // Calcular montos
            $comisionPorcentaje = $this->getCommissionByDiscount($descuentoPorcentaje);
            $comisionMonto = round($montoOriginal * ($comisionPorcentaje / 100), 2);
            $montoNeto = $montoOriginal - $comisionMonto;
            
            $fecha = Carbon::now()->subDays(rand(0, 90))->subHours(rand(0, 23));
            
            // Estado ponderado
            $rand = rand(1, 100);
            if ($rand <= 60) {
                $estado = 'confirmed';
            } elseif ($rand <= 80) {
                $estado = 'pending_manual_confirmation';
            } elseif ($rand <= 95) {
                $estado = 'awaiting_review';
            } else {
                $estado = 'failed';
            }

            $confirmationData = [
                'request_data' => ['Amount' => $montoOriginal],
                'payment_type' => 'test',
                'payment_id' => $payment->id,
                'order_id' => $order->id,
                'aliado_id' => $aliado->id,
                'aliado_company' => $aliado->company_name,
                'discount' => $descuentoPorcentaje,
                'created_at' => $fecha->toDateTimeString(),
            ];

            $transaction = PaymentTransaction::create([
                'user_id' => $user->id,
                'ally_id' => $aliado->id,
                'original_amount' => $montoOriginal,
                'discount_percentage' => $descuentoPorcentaje,
                'amount_to_ally' => $montoNeto,
                'platform_commission' => $comisionMonto,
                'payment_method' => $metodosPago[array_rand($metodosPago)],
                'status' => $estado,
                'reference_code' => 'TXN-' . strtoupper(substr(md5(uniqid()), 0, 12)),
                'confirmation_data' => json_encode($confirmationData, JSON_PRETTY_PRINT),
                'created_at' => $fecha,
                'updated_at' => $fecha,
            ]);
            
            $transactions[] = $transaction;
        }
        $this->command->info('✅ ' . count($transactions) . ' payment_transactions creadas');

        // ===========================================
        // 5. CREAR VENTAS (SALES) - CORREGIDO
        // ===========================================
        $this->command->info('⏳ Creando ventas...');
        $ventas = 0;
        
        foreach ($transactions as $transaction) {
            if ($transaction->status !== 'confirmed') {
                continue;
            }

            $fecha = $transaction->created_at;
            $monto = $transaction->original_amount;
            
            // Crear venta con los campos correctos de la tabla sales
            Sale::create([
                'ally_id' => $transaction->ally_id,           // ← CORREGIDO: ally_id (no aliado_id)
                'branch_id' => null,                          // ← NUEVO: branch_id nullable
                'client_id' => $transaction->user_id,         // ← NUEVO: client_id
                'total_amount' => $monto,                     // ← CORREGIDO: total_amount
                'paid_amount' => $monto,                      // ← CORREGIDO: paid_amount
                'payment_method' => $transaction->payment_method, // ← CORREGIDO: payment_method
                'bank_reference' => 'REF-' . strtoupper(uniqid()),
                'transaction_id' => 'TRX-' . strtoupper(uniqid()),
                'status' => 'completed',
                'sale_date' => $fecha,                        // ← CORREGIDO: sale_date
                'payment_date' => $fecha,                      // ← CORREGIDO: payment_date
                'terminal' => 'TERM-' . rand(100, 999),
                'destination_bank' => rand(1, 10),
                'client_phone' => '0412' . rand(1000000, 9999999),
                'client_id_number' => 'V-' . rand(1000000, 99999999),
                'description' => 'Venta desde seeder',
                'authorization_code' => 'AUTH-' . rand(100000, 999999),
                'bank_response' => json_encode(['success' => true, 'code' => '00']),
                'created_at' => $fecha,
                'updated_at' => $fecha,
            ]);
            
            $ventas++;
        }
        $this->command->info('✅ ' . $ventas . ' ventas creadas');

        // ===========================================
        // 6. CREAR PAYOUTS (solo para confirmadas)
        // ===========================================
        $this->command->info('⏳ Creando payouts...');
        $payouts = 0;
        $ventasConfirmadas = Sale::where('status', 'completed')->get();

        foreach ($ventasConfirmadas as $sale) {
            if (rand(1, 100) <= 70) { // 70% probabilidad
                $transaction = PaymentTransaction::find($sale->payment_transaction_id ?? 0);
                if (!$transaction) {
                    // Buscar por referencia si no hay relación directa
                    $transaction = PaymentTransaction::where('original_amount', $sale->total_amount)
                        ->where('ally_id', $sale->ally_id)
                        ->first();
                }
                
                if (!$transaction) continue;

                // Estado de payout
                $randEstado = rand(1, 100);
                if ($randEstado <= 50) {
                    $estadoPayout = 'completed';
                } elseif ($randEstado <= 75) {
                    $estadoPayout = 'pending';
                } elseif ($randEstado <= 90) {
                    $estadoPayout = 'processing';
                } else {
                    $estadoPayout = 'reverted';
                }

                $payoutData = [
                    'sale_id' => $sale->id,
                    'ally_id' => $sale->ally_id,
                    'sale_amount' => $sale->total_amount,
                    'commission_percentage' => $this->getCommissionByDiscount($transaction->discount_percentage ?? 0),
                    'commission_amount' => $transaction->platform_commission ?? 0,
                    'net_amount' => $transaction->amount_to_ally ?? $sale->total_amount,
                    'ally_discount' => $transaction->discount_percentage ?? 0,
                    'amount_after_discount' => $sale->total_amount * (1 - (($transaction->discount_percentage ?? 0) / 100)),
                    'company_transfer_amount' => $transaction->amount_to_ally ?? $sale->total_amount,
                    'company_commission' => $transaction->platform_commission ?? 0,
                    'company_account' => 'CUENTA-EMPRESA',
                    'company_bank' => 'BANCO NACIONAL',
                    'company_transfer_reference' => 'TRF-' . strtoupper(uniqid()),
                    'company_transfer_status' => $estadoPayout === 'completed' ? 'completed' : 'pending',
                    'company_transfer_date' => $estadoPayout === 'completed' ? now() : null,
                    'status' => $estadoPayout,
                    'generation_date' => now()->subDays(rand(1, 5)),
                    'sale_reference' => $sale->bank_reference ?? 'SALE-' . $sale->id,
                    'ally_payment_method' => 'transfer',
                    'created_at' => $sale->created_at->addDays(rand(1, 3)),
                    'updated_at' => now(),
                ];

                if ($estadoPayout === 'completed') {
                    $payoutData['payment_date'] = now()->subDays(rand(1, 2));
                    $payoutData['payment_reference'] = 'PAY-' . strtoupper(uniqid());
                    $payoutData['confirmed_at'] = now()->subDays(rand(1, 2));
                } elseif ($estadoPayout === 'reverted') {
                    $payoutData['reversion_reason'] = 'Error en datos bancarios';
                    $payoutData['reverted_at'] = now();
                }

                Payout::create($payoutData);
                $payouts++;
            }
        }
        $this->command->info('✅ ' . $payouts . ' payouts creados');

        // ===========================================
        // 7. MOSTRAR RESUMEN
        // ===========================================
        $this->command->info("\n📊 ========== RESUMEN DE DATOS ==========");
        $this->command->table(
            ['Tabla', 'Registros'],
            [
                ['users (clientes)', User::where('user_type', 'user')->count()],
                ['users (aliados)', User::where('user_type', 'aliado')->count()],
                ['allies', Ally::count()],
                ['payments', Payment::count()],
                ['orders', Order::count()],
                ['payment_transactions', PaymentTransaction::count()],
                ['sales', Sale::count()],
                ['payouts', Payout::count()],
            ]
        );

        // Mostrar los aliados creados
        $this->command->info("\n🏢 ========== ALIADOS CREADOS ==========");
        $this->command->table(
            ['ID', 'Compañía', 'Email', 'Descuento', 'Teléfono'],
            Ally::all()->map(function($ally) {
                return [
                    $ally->id,
                    $ally->company_name,
                    $ally->contact_email ?? $ally->user->email,
                    $ally->discount,
                    $ally->contact_phone,
                ];
            })->toArray()
        );

        // Mostrar algunas transacciones de ejemplo
        $this->command->info("\n📝 ========== EJEMPLOS DE TRANSACCIONES ==========");
        $ejemplos = PaymentTransaction::with(['user', 'ally'])->latest()->take(5)->get();
        
        foreach ($ejemplos as $ejemplo) {
            $this->command->line("ID: {$ejemplo->id} | Ref: {$ejemplo->reference_code}");
            $this->command->line("Usuario: {$ejemplo->user->name}");
            $this->command->line("Aliado: {$ejemplo->ally->company_name}");
            $this->command->line("Monto: $" . number_format($ejemplo->original_amount, 0, ',', '.'));
            $this->command->line("Estado: {$ejemplo->status}");
            $this->command->line("-------------------");
        }
    }

    /**
     * Obtiene un monto aleatorio realista
     */
    private function getRandomAmount(): float
    {
        $montos = [
            50000, 75000, 100000, 150000, 200000, 250000, 300000, 350000, 400000, 450000,
            500000, 600000, 700000, 800000, 900000, 1000000, 1200000, 1500000, 1800000,
            2000000, 2500000, 3000000
        ];
        return $montos[array_rand($montos)];
    }

    /**
     * Obtiene comisión según descuento
     */
    private function getCommissionByDiscount(float $discount): float
    {
        if ($discount >= 0 && $discount <= 10) {
            return 15.0;
        } elseif ($discount > 10 && $discount <= 20) {
            return 12.0;
        } elseif ($discount > 20 && $discount <= 30) {
            return 10.0;
        } elseif ($discount > 30) {
            return 8.0;
        }
        return 15.0;
    }
}