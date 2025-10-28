<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Payout;
use App\Models\Ally;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
                'ally_discount' => $aliadoData['discount'] ?? 0,
                'amount_after_discount' => $aliadoData['monto_despues_descuento'] ?? $venta->monto_total,

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
     * Procesa y genera archivo de pagos con validación mejorada
     */
    public function procesarYGenerarArchivoPagos(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            // Validar entrada
            $validated = $request->validate([
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
                'tipo_cuenta' => 'required|in:corriente,ahorro',
                'concepto' => 'nullable|string|max:60',
                'payout_ids' => 'sometimes|array',
                'payout_ids.*' => 'integer|exists:payouts,id'
            ]);

            // Obtener payouts según filtros
            $payouts = $this->obtenerPayoutsParaArchivo($validated);

            if ($payouts->isEmpty()) {
                throw new \Exception('No hay pagos pendientes para el rango de fechas seleccionado');
            }

            // Validar aliados
            $aliadosInvalidos = $this->validarAliadosParaPagoBNC($payouts);
            if (!empty($aliadosInvalidos)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Algunos aliados no tienen datos completos para pago',
                    'aliados_invalidos' => $aliadosInvalidos
                ], 422);
            }

            // Generar archivo
            $resultadoArchivo = $this->generarArchivoPagosBNC(
                $validated['fecha_inicio'],
                $validated['fecha_fin'],
                $validated['tipo_cuenta'],
                $validated['concepto'] ?? null,
                $payouts
            );

            // Actualizar estados
            Payout::whereIn('id', $payouts->pluck('id'))->update([
                'status' => 'processing',
                'batch_processed_at' => now(),
                'batch_reference' => 'BATCH-' . date('Ymd-His')
            ]);

            DB::commit();

            Log::info('Proceso de archivo de pagos completado', [
                'archivo' => $resultadoArchivo['archivo'],
                'payouts_procesados' => $payouts->count(),
                'monto_total' => $resultadoArchivo['monto_total']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Archivo de pagos generado exitosamente',
                'data' => $resultadoArchivo
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en procesarYGenerarArchivoPagos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene payouts para archivo con filtros
     */
    private function obtenerPayoutsParaArchivo(array $filtros)
    {
        $query = Payout::with(['ally', 'sale'])
            ->where('status', 'pending');

        // Filtro por fechas
        $query->whereBetween('created_at', [
            $filtros['fecha_inicio'] . ' 00:00:00',
            $filtros['fecha_fin'] . ' 23:59:59'
        ]);

        // Filtro por IDs específicos si se proporcionan
        if (!empty($filtros['payout_ids'])) {
            $query->whereIn('id', $filtros['payout_ids']);
        }

        return $query->orderBy('created_at', 'asc')->get();
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
     * Obtiene payouts pendientes con información completa
     */
    public function obtenerPagosPendientesCompletos()
    {
        return Payout::with(['ally', 'sale'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function($payout) {
                return [
                    'id' => $payout->id,
                    'aliado' => [
                        'id' => $payout->ally_id,
                        'nombre' => $payout->ally->name ?? 'N/A',
                        'email' => $payout->ally->email ?? 'N/A',
                        'cuenta_bancaria' => $payout->ally->bank_account_number ?? 'N/A',
                        'tipo_documento' => $payout->ally->document_type ?? 'N/A',
                        'documento' => $payout->ally->document_number ?? 'N/A',
                        'banco' => $payout->ally->bank_name ?? 'N/A',
                        'tipo_cuenta' => $payout->ally->account_type ?? 'N/A'
                    ],
                    'venta' => [
                        'id' => $payout->sale_id,
                        'referencia' => $payout->sale_reference,
                        'monto_total' => $payout->sale_amount,
                        'fecha_venta' => $payout->sale->fecha_venta ?? null
                    ],
                    'montos' => [
                        'comision_porcentaje' => $payout->commission_percentage,
                        'comision_monto' => $payout->commission_amount,
                        'neto' => $payout->net_amount,
                        'descuento_aliado' => $payout->ally_discount,
                        'monto_despues_descuento' => $payout->amount_after_discount
                    ],
                    'fechas' => [
                        'generacion' => $payout->generation_date,
                        'creacion' => $payout->created_at
                    ]
                ];
            });
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
     * Obtiene estadísticas completas de payouts
     */
    public function obtenerEstadisticasCompletas(): array
    {
        $estadisticasBasicas = $this->obtenerEstadisticasPayouts();
        
        // Estadísticas por aliado
        $estadisticasAliados = Payout::with('ally')
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
            });

        // Estadísticas mensuales
        $estadisticasMensuales = Payout::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as total_payouts'),
                DB::raw('SUM(net_amount) as total_monto')
            )
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        return array_merge($estadisticasBasicas, [
            'estadisticas_aliados' => $estadisticasAliados,
            'estadisticas_mensuales' => $estadisticasMensuales,
            'archivos_generados' => count($this->obtenerArchivosGenerados())
        ]);
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
    public function generarArchivoPagosBNC($fechaInicio, $fechaFin, $tipoCuenta, $concepto = null, $payouts = null): array
    {
        try {
            // Si no se pasan payouts, obtenerlos
            if (!$payouts) {
                $payouts = Payout::with(['ally', 'sale'])
                    ->where('status', 'pending')
                    ->whereBetween('created_at', [$fechaInicio, $fechaFin])
                    ->get();
            }

            if ($payouts->isEmpty()) {
                throw new \Exception('No hay pagos pendientes para generar archivo');
            }

            // Generar contenido del archivo BNC
            $contenidoArchivo = $this->formatearArchivoBNC($payouts, $tipoCuenta, $concepto);
            
            // Guardar archivo con nombre más descriptivo
            $timestamp = date('Ymd_His');
            $nombreArchivo = "pagos_bnc_{$timestamp}_{$payouts->count()}registros.txt";
            $rutaArchivo = storage_path('app/pagos_bnc/' . $nombreArchivo);
            
            File::ensureDirectoryExists(dirname($rutaArchivo));
            File::put($rutaArchivo, $contenidoArchivo);

            // Crear registro del archivo generado
            $this->registrarArchivoGenerado($nombreArchivo, $payouts, $concepto);

            Log::info('Archivo BNC generado exitosamente', [
                'archivo' => $nombreArchivo,
                'ruta' => $rutaArchivo,
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
                ],
                'tipo_cuenta' => $tipoCuenta,
                'concepto' => $concepto
            ];

        } catch (\Exception $e) {
            Log::error('Error generando archivo BNC: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Formatea el archivo BNC según especificaciones del banco
     */
    private function formatearArchivoBNC($payouts, $tipoCuenta, $concepto): string
    {
        $contenido = "";
        $fechaPago = now()->format('d/m/Y');
        $montoTotal = 0;
        
        // Obtener cuenta de débito directamente desde .env
        $cuentaDebitar = $this->obtenerCuentaDebito();
        
        foreach ($payouts as $payout) {
            $aliado = $payout->ally;
            $montoTotal += $payout->net_amount;
            
            // I. FECHA DE PAGO (DD/MM/AAAA)
            $fechaPago = $fechaPago;
            
            // II. CUENTA A DEBITAR (20 dígitos exactos desde .env)
            $cuentaDebitar = $cuentaDebitar;
            
            // III. CUENTA BENEFICIARIO (20 dígitos exactos)
            $cuentaBeneficiario = str_pad(
                $aliado->bank_account_number ?? '00000000000000000000', 
                20, '0', STR_PAD_LEFT
            );
            
            // IV. MONTO DEL PAGO (15 dígitos, 2 decimales con coma)
            $montoPago = $this->formatearMontoBNC($payout->net_amount);
            
            // V. DESCRIPCIÓN (60 caracteres máximo)
            $descripcion = $this->truncarTexto(
                $concepto ?? env('CONCEPTO_PAGO_DEFAULT', 'Pago comisiones'), 
                60
            );
            
            // VI. ID BENEFICIARIO (10 caracteres máximo)
            $idBeneficiario = $this->formatearIdBeneficiarioBNC(
                $aliado->document_type ?? 'V', 
                $aliado->document_number ?? '00000000'
            );
            
            // VII. NOMBRE BENEFICIARIO (80 caracteres máximo)
            $nombreBeneficiario = $this->truncarTexto(
                $aliado->name ?? 'BENEFICIARIO NO DEFINIDO', 
                80
            );
            
            // VIII. EMAIL BENEFICIARIO (100 caracteres máximo)
            $emailBeneficiario = $this->truncarTexto(
                $aliado->email ?? 'sin-email@dominio.com', 
                100
            );
            
            // IX. REFERENCIA DEL CLIENTE (10 dígitos máximo)
            $referenciaCliente = str_pad($payout->id, 10, '0', STR_PAD_LEFT);
            
            // Formar línea según formato del banco
            $linea = implode('|', [
                $fechaPago,
                $cuentaDebitar,
                $cuentaBeneficiario,
                $montoPago,
                $descripcion,
                $idBeneficiario,
                $nombreBeneficiario,
                $emailBeneficiario,
                $referenciaCliente
            ]);
            
            $contenido .= $linea . PHP_EOL;
        }
        
        Log::info('Archivo BNC generado', [
            'registros' => $payouts->count(),
            'monto_total' => $montoTotal,
            'cuenta_debito' => $cuentaDebitar
        ]);
        
        return $contenido;
    }

    /**
     * Obtiene la cuenta de débito directamente desde .env con validación
     */
    private function obtenerCuentaDebito(): string
    {
        $cuenta = env('CUENTA_DEBITO_BNC', '00000000000000000000');
        
        // Validar que tenga 20 dígitos
        if (!preg_match('/^\d{20}$/', $cuenta)) {
            Log::error('Cuenta de débito BNC no válida en .env', ['cuenta' => $cuenta]);
            throw new \Exception('La cuenta de débito configurada no tiene 20 dígitos. Verifique CUENTA_DEBITO_BNC en .env');
        }
        
        return $cuenta;
    }

    /**
     * Formatea monto según especificaciones BNC (15 dígitos, 2 decimales con coma)
     */
    private function formatearMontoBNC($monto): string
    {
        // Asegurar que tenga 2 decimales
        $montoFormateado = number_format($monto, 2, ',', '');
        
        // Remover cualquier separador de miles y dejar solo números y coma decimal
        $montoLimpio = str_replace('.', '', $montoFormateado);
        
        // Rellenar con ceros a la izquierda hasta 15 caracteres
        return str_pad($montoLimpio, 15, '0', STR_PAD_LEFT);
    }

    /**
     * Formatea ID del beneficiario según especificaciones BNC
     */
    private function formatearIdBeneficiarioBNC($tipoDocumento, $documento): string
    {
        $tipo = strtoupper($tipoDocumento);
        
        // Limpiar documento (solo números)
        $documentoLimpio = preg_replace('/[^0-9]/', '', $documento);
        
        // Combinar tipo + documento (ej: V123456789)
        $idCompleto = $tipo . $documentoLimpio;
        
        // Limitar a 10 caracteres máximo
        return substr($idCompleto, 0, 10);
    }

    /**
     * Trunca texto a longitud máxima
     */
    private function truncarTexto($texto, $longitudMaxima): string
    {
        return mb_substr($texto ?? '', 0, $longitudMaxima, 'UTF-8');
    }

    /**
     * Valida que todos los aliados tengan datos completos para pago BNC
     */
    private function validarAliadosParaPagoBNC($payouts): array
    {
        $errores = [];
        
        foreach ($payouts as $payout) {
            $aliado = $payout->ally;
            $validacion = $this->validarAliadoIndividualBNC($aliado);
            
            if (!$validacion['valido']) {
                $errores[] = "Aliado {$aliado->name} (ID: {$aliado->id}): " . implode(', ', $validacion['errores']);
            }
        }
        
        return $errores;
    }

    /**
     * Valida un aliado individual para pago BNC
     */
    private function validarAliadoIndividualBNC($aliado): array
    {
        $errores = [];
        
        // Validar cuenta bancaria (20 dígitos)
        if (empty($aliado->bank_account_number)) {
            $errores[] = 'Cuenta bancaria es requerida';
        } elseif (!preg_match('/^\d{20}$/', $aliado->bank_account_number)) {
            $errores[] = 'Cuenta bancaria debe tener 20 dígitos exactos';
        }
        
        // Validar tipo de documento
        $tiposPermitidos = ['V', 'E', 'J', 'G'];
        if (empty($aliado->document_type)) {
            $errores[] = 'Tipo de documento es requerido';
        } elseif (!in_array(strtoupper($aliado->document_type), $tiposPermitidos)) {
            $errores[] = 'Tipo de documento debe ser V, E, J o G';
        }
        
        // Validar número de documento
        if (empty($aliado->document_number)) {
            $errores[] = 'Número de documento es requerido';
        } elseif (!preg_match('/^[0-9]{6,9}$/', $aliado->document_number)) {
            $errores[] = 'Número de documento debe contener entre 6 y 9 dígitos';
        }
        
        // Validar nombre
        if (empty($aliado->name)) {
            $errores[] = 'Nombre es requerido';
        } elseif (strlen($aliado->name) > 80) {
            $errores[] = 'Nombre debe tener máximo 80 caracteres';
        }
        
        // Validar email
        if (empty($aliado->email)) {
            $errores[] = 'Email es requerido';
        } elseif (!filter_var($aliado->email, FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'Email debe ser válido';
        } elseif (strlen($aliado->email) > 100) {
            $errores[] = 'Email debe tener máximo 100 caracteres';
        }
        
        return [
            'valido' => empty($errores),
            'errores' => $errores
        ];
    }

    /**
     * Registra el archivo generado en la base de datos
     */
    private function registrarArchivoGenerado(string $nombreArchivo, $payouts, ?string $concepto): void
    {
        try {
            DB::table('payout_batch_files')->insert([
                'filename' => $nombreArchivo,
                'payout_count' => $payouts->count(),
                'total_amount' => $payouts->sum('net_amount'),
                'concept' => $concepto,
                'generated_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } catch (\Exception $e) {
            Log::error('Error registrando archivo generado: ' . $e->getMessage());
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
                'Content-Length' => File::size($rutaArchivo),
            ]
        ];
    }

    /**
     * Obtiene lista de archivos generados
     */
    public function obtenerArchivosGenerados(): array
    {
        $archivos = [];
        $directorio = storage_path('app/pagos_bnc/');
        
        if (File::exists($directorio)) {
            $archivos = array_map(function($archivo) {
                return [
                    'nombre' => $archivo->getFilename(),
                    'ruta' => $archivo->getPathname(),
                    'tamaño' => $archivo->getSize(),
                    'fecha_modificacion' => date('Y-m-d H:i:s', $archivo->getMTime())
                ];
            }, File::files($directorio));
        }

        // Ordenar por fecha de modificación (más reciente primero)
        usort($archivos, function($a, $b) {
            return strtotime($b['fecha_modificacion']) - strtotime($a['fecha_modificacion']);
        });

        return $archivos;
    }

    /**
     * Elimina archivo BNC
     */
    public function eliminarArchivoBNC($archivo): bool
    {
        $rutaArchivo = storage_path('app/pagos_bnc/' . $archivo);
        
        if (!File::exists($rutaArchivo)) {
            throw new \Exception('El archivo solicitado no existe');
        }

        return File::delete($rutaArchivo);
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
                'descuento_aliado' => $payout->ally_discount,
                'monto_despues_descuento' => $payout->amount_after_discount
            ]
        ];
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

    /**
     * Valida datos individuales según formato BNC
     */
    public function validarPagoIndividualBNC(array $datosPago): array
    {
        $errores = [];
        
        // Validar cuenta beneficiario (20 dígitos exactos)
        if (!preg_match('/^\d{20}$/', $datosPago['cuenta_beneficiario'] ?? '')) {
            $errores[] = 'Cuenta beneficiario debe tener exactamente 20 dígitos';
        }
        
        // Validar ID beneficiario (máximo 10 caracteres, formato V/E/J/G + números)
        if (!preg_match('/^[VEJG][-]?\d{1,9}$/i', $datosPago['id_beneficiario'] ?? '')) {
            $errores[] = 'ID beneficiario debe tener formato V/E/J/G seguido de números (máx 10 caracteres)';
        }
        
        // Validar monto (numérico, positivo)
        if (!is_numeric($datosPago['monto'] ?? 0) || $datosPago['monto'] <= 0) {
            $errores[] = 'Monto debe ser un valor numérico positivo';
        }
        
        // Validar nombre beneficiario (máximo 80 caracteres)
        if (empty($datosPago['nombre_beneficiario']) || strlen($datosPago['nombre_beneficiario']) > 80) {
            $errores[] = 'Nombre beneficiario es requerido y máximo 80 caracteres';
        }
        
        // Validar email (formato válido, máximo 100 caracteres)
        if (!filter_var($datosPago['email_beneficiario'] ?? '', FILTER_VALIDATE_EMAIL) || 
            strlen($datosPago['email_beneficiario']) > 100) {
            $errores[] = 'Email debe ser válido y tener máximo 100 caracteres';
        }
        
        return [
            'valido' => empty($errores),
            'errores' => $errores
        ];
    }
}