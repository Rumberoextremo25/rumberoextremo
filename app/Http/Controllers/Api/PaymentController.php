<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\BncApiService;
use App\Models\Payout;
use App\Models\Sale;
use App\Models\Ally;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PaymentController extends Controller
{
    protected BncApiService $bncApiService;

    public function __construct(BncApiService $bncApiService)
    {
        $this->bncApiService = $bncApiService;
    }

    /**
     * Inicia un pago C2P (Pago Móvil) con la API del BNC.
     * El monto total va a la cuenta de Rumbero Extremo.
     * Luego, se registra el pago pendiente al aliado.
     */
    public function initiateC2PPayment(Request $request): JsonResponse
    {
        Log::info('Solicitud recibida para iniciar pago C2P.', ['request_data' => $request->all()]);

        DB::beginTransaction();
        try {
            // 1. ✅ Validar los datos de entrada
            $validatedData = $request->validate([
                'banco' => 'required|integer',
                'telefono' => 'required|string|regex:/^[0-9]{10,15}$/',
                'cedula' => 'required|string|min:6|max:20',
                'monto' => 'required|numeric|min:0.01',
                'token' => 'required|string|regex:/^[0-9]{6,7}$/',
                'terminal' => 'required|string|max:50',
                // Campos adicionales
                'cliente_id' => 'required|integer|exists:clientes,id',
                'sucursal_id' => 'required|integer|exists:sucursales,id',
                'aliado_id' => 'required|integer|exists:aliados,id',
                'descripcion' => 'nullable|string|max:255',
            ]);

            // 2. ✅ Obtener porcentaje del aliado
            $aliado = Ally::find($validatedData['aliado_id']);
            if (!$aliado) {
                throw new \Exception('Aliado no encontrado');
            }

            $porcentajeAliado = $aliado->porcentaje_comision;
            $montoAliado = ($validatedData['monto'] * $porcentajeAliado) / 100;

            // 3. ✅ Preparar datos para el BNC
            $bncC2pData = [
                'banco' => intval($validatedData['banco']),
                'telefono' => $validatedData['telefono'],
                'cedula' => $validatedData['cedula'],
                'monto' => floatval($validatedData['monto']),
                'token' => $validatedData['token'],
                'terminal' => $validatedData['terminal'],
            ];

            Log::debug('Datos preparados para BNC C2P', ['bncC2pData' => $bncC2pData]);

            // 4. ✅ Delegar la llamada al servicio BNC
            $bncResponse = $this->bncApiService->initiateC2PPayment($bncC2pData);

            // 5. ✅ Validar la respuesta del BNC
            if (is_null($bncResponse) || !isset($bncResponse['Status']) || $bncResponse['Status'] !== 'OK') {
                $errorMessage = $bncResponse['Message'] ?? 'Fallo al procesar el pago C2P con la pasarela de pagos.';
                Log::error('Fallo al procesar pago C2P con BNC.', [
                    'bnc_response' => $bncResponse,
                    'error_message' => $errorMessage
                ]);
                throw new \Exception($errorMessage);
            }

            // 6. ✅ GUARDAR EN LA TABLA VENTAS
            $venta = Sale::create([
                'cliente_id' => $validatedData['cliente_id'],
                'sucursal_id' => $validatedData['sucursal_id'],
                'aliado_id' => $validatedData['aliado_id'],
                'monto_total' => $validatedData['monto'],
                'monto_pagado' => $validatedData['monto'],
                'metodo_pago' => 'pago_movil',
                'referencia_banco' => $bncResponse['Reference'] ?? null,
                'transaction_id' => $bncResponse['TransactionIdentifier'] ?? null,
                'estado' => 'completado',
                'fecha_venta' => now(),
                'fecha_pago' => now(),
                'terminal' => $validatedData['terminal'],
                'banco_destino' => $validatedData['banco'],
                'telefono_cliente' => $validatedData['telefono'],
                'cedula_cliente' => $validatedData['cedula'],
                'descripcion' => $validatedData['descripcion'] ?? 'Pago móvil procesado',
                'codigo_autorizacion' => $bncResponse['AuthorizationCode'] ?? null,
                'respuesta_banco' => json_encode($bncResponse),
            ]);

            // 7. ✅ GUARDAR EN LA TABLA PAYOUTS (PAGO A ALIADOS)
            $payout = Payout::create([
                'venta_id' => $venta->id,
                'aliado_id' => $validatedData['aliado_id'],
                'monto_venta' => $validatedData['monto'],
                'porcentaje_comision' => $porcentajeAliado,
                'monto_comision' => $montoAliado,
                'estado' => 'pendiente',
                'fecha_generacion' => now(),
                'fecha_pago' => null,
                'referencia_venta' => $bncResponse['Reference'] ?? null,
                'metodo_pago_aliado' => $aliado->metodo_pago_default ?? 'transferencia',
                'cuenta_aliado' => $aliado->cuenta_bancaria,
                'banco_aliado' => $aliado->banco,
            ]);

            Log::info('Transacción guardada en base de datos', [
                'venta_id' => $venta->id,
                'payout_id' => $payout->id,
                'referencia_banco' => $venta->referencia_banco,
                'monto_aliado' => $montoAliado
            ]);

            DB::commit();

            Log::info('Pago C2P procesado exitosamente con registro en ventas and payouts');
            return response()->json([
                'message' => 'Pago C2P procesado exitosamente.',
                'data' => [
                    'bnc_reference' => $bncResponse['Reference'] ?? null,
                    'bnc_status' => $bncResponse['Status'] ?? null,
                    'transaction_id' => $bncResponse['TransactionIdentifier'] ?? null,
                    'amount_processed' => $validatedData['monto'],
                    'venta_id' => $venta->id,
                    'payout_id' => $payout->id,
                    'porcentaje_aliado' => $porcentajeAliado,
                    'monto_aliado' => $montoAliado,
                    'fecha_venta' => $venta->fecha_venta->format('Y-m-d H:i:s')
                ]
            ], 200);
        } catch (ValidationException $e) {
            DB::rollBack();
            Log::warning('Error de validación en pago C2P', ['errors' => $e->errors()]);
            return response()->json(['message' => 'Datos de entrada inválidos.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en pago C2P: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Procesa un pago con tarjeta (VPOS)
     */
    public function processCardPayment(Request $request): JsonResponse
    {
        Log::info('Solicitud recibida para procesar pago con tarjeta.', ['request_data' => $request->all()]);

        DB::beginTransaction();
        try {
            // 1. ✅ Validar los datos de entrada
            $validatedData = $request->validate([
                'identificador' => 'required|string|max:50',
                'monto' => 'required|numeric|min:0.01',
                'tipTarjeta' => 'required|integer',
                'tarjeta' => 'required|numeric',
                'fechExp' => 'required|numeric|digits:6',
                'nomTarjeta' => 'required|string|max:255',
                'tipCuenta' => 'required|integer',
                'cvv' => 'required|numeric|digits_between:3,4',
                'pin' => 'required|numeric',
                'identificacion' => 'required|numeric',
                'afiliacion' => 'required|numeric',
                // Campos adicionales
                'cliente_id' => 'required|integer|exists:clientes,id',
                'sucursal_id' => 'required|integer|exists:sucursales,id',
                'aliado_id' => 'required|integer|exists:aliados,id',
                'descripcion' => 'nullable|string|max:255',
            ]);

            // 2. ✅ Obtener porcentaje del aliado
            $aliado = Ally::find($validatedData['aliado_id']);
            if (!$aliado) {
                throw new \Exception('Aliado no encontrado');
            }

            $porcentajeAliado = $aliado->porcentaje_comision;
            $montoAliado = ($validatedData['monto'] * $porcentajeAliado) / 100;

            // 3. ✅ Preparar datos para el BNC
            $bncCardData = [
                'identificador' => $validatedData['identificador'],
                'monto' => floatval($validatedData['monto']),
                'tipTarjeta' => intval($validatedData['tipTarjeta']),
                'tarjeta' => intval($validatedData['tarjeta']),
                'fechExp' => intval($validatedData['fechExp']),
                'nomTarjeta' => $validatedData['nomTarjeta'],
                'tipCuenta' => intval($validatedData['tipCuenta']),
                'cvv' => intval($validatedData['cvv']),
                'pin' => intval($validatedData['pin']),
                'identificacion' => intval($validatedData['identificacion']),
                'afiliacion' => intval($validatedData['afiliacion'])
            ];

            Log::debug('Datos preparados para BNC VPOS', ['bncCardData' => $bncCardData]);

            // 4. Delegar la llamada al servicio BNC
            $bncResponse = $this->bncApiService->processCardPayment($bncCardData);

            // 5. Validar la respuesta del BNC
            if (is_null($bncResponse) || !isset($bncResponse['status']) || $bncResponse['status'] !== 'OK') {
                $errorMessage = $bncResponse['message'] ?? 'Fallo al procesar el pago con la pasarela VPOS.';
                Log::error('Fallo al procesar pago VPOS.', [
                    'bnc_response' => $bncResponse,
                    'error_message' => $errorMessage
                ]);
                throw new \Exception($errorMessage);
            }

            // 6. ✅ GUARDAR EN LA TABLA VENTAS
            $venta = Sale::create([
                'cliente_id' => $validatedData['cliente_id'],
                'sucursal_id' => $validatedData['sucursal_id'],
                'aliado_id' => $validatedData['aliado_id'],
                'monto_total' => $validatedData['monto'],
                'monto_pagado' => $validatedData['monto'],
                'metodo_pago' => 'tarjeta_credito',
                'referencia_banco' => $bncResponse['Reference'] ?? null,
                'transaction_id' => $bncResponse['TransactionIdentifier'] ?? $validatedData['identificador'],
                'estado' => 'completado',
                'fecha_venta' => now(),
                'fecha_pago' => now(),
                'terminal' => $validatedData['identificador'],
                'descripcion' => $validatedData['descripcion'] ?? 'Pago con tarjeta procesado',
                'codigo_autorizacion' => $bncResponse['AuthorizationCode'] ?? null,
                'respuesta_banco' => json_encode($bncResponse),
            ]);

            // 7. ✅ GUARDAR EN LA TABLA PAYOUTS (PAGO A ALIADOS)
            $payout = Payout::create([
                'venta_id' => $venta->id,
                'aliado_id' => $validatedData['aliado_id'],
                'monto_venta' => $validatedData['monto'],
                'porcentaje_comision' => $porcentajeAliado,
                'monto_comision' => $montoAliado,
                'estado' => 'pendiente',
                'fecha_generacion' => now(),
                'fecha_pago' => null,
                'referencia_venta' => $bncResponse['Reference'] ?? null,
                'metodo_pago_aliado' => $aliado->metodo_pago_default ?? 'transferencia',
                'cuenta_aliado' => $aliado->cuenta_bancaria,
                'banco_aliado' => $aliado->banco,
            ]);

            DB::commit();

            Log::info('Pago VPOS procesado exitosamente con registro en ventas and payouts');
            return response()->json([
                'message' => 'Pago con tarjeta procesado exitosamente.',
                'data' => [
                    'transaction_id' => $venta->transaction_id,
                    'bnc_status' => $bncResponse['status'] ?? null,
                    'bnc_reference' => $bncResponse['Reference'] ?? null,
                    'amount_processed' => $validatedData['monto'],
                    'venta_id' => $venta->id,
                    'payout_id' => $payout->id,
                    'porcentaje_aliado' => $porcentajeAliado,
                    'monto_aliado' => $montoAliado
                ]
            ], 200);
        } catch (ValidationException $e) {
            DB::rollBack();
            Log::warning('Error de validación en pago VPOS', ['errors' => $e->errors()]);
            return response()->json(['message' => 'Datos de entrada inválidos.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en pago VPOS: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Procesa un pago P2P
     */
    public function processP2PPayment(Request $request): JsonResponse
    {
        Log::info('Solicitud recibida para procesar pago P2P.', ['request_data' => $request->all()]);

        DB::beginTransaction();
        try {
            // 1. ✅ Validar los datos de entrada
            $validatedData = $request->validate([
                'banco' => 'required|integer',
                'telefono' => 'required|string|regex:/^[0-9]{10,15}$/',
                'cedula' => 'required|string|min:6|max:20',
                'beneficiario' => 'required|string|max:255',
                'monto' => 'required|numeric|min:0.01',
                'descripcion' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
                // Campos adicionales
                'cliente_id' => 'required|integer|exists:clientes,id',
                'sucursal_id' => 'required|integer|exists:sucursales,id',
                'aliado_id' => 'required|integer|exists:aliados,id',
            ]);

            // 2. ✅ Obtener porcentaje del aliado
            $aliado = Ally::find($validatedData['aliado_id']);
            if (!$aliado) {
                throw new \Exception('Aliado no encontrado');
            }

            $porcentajeAliado = $aliado->porcentaje_comision;
            $montoAliado = ($validatedData['monto'] * $porcentajeAliado) / 100;

            // 3. ✅ Preparar datos para el BNC
            $bncP2pData = [
                'banco' => intval($validatedData['banco']),
                'telefono' => $validatedData['telefono'],
                'cedula' => $validatedData['cedula'],
                'beneficiario' => $validatedData['beneficiario'],
                'monto' => floatval($validatedData['monto']),
                'descripcion' => $validatedData['descripcion'],
                'email' => $validatedData['email'] ?? null,
            ];

            Log::debug('Datos preparados para BNC P2P', ['bncP2pData' => $bncP2pData]);

            // 4. Delegar la llamada al servicio BNC
            $bncResponse = $this->bncApiService->initiateP2PPayment($bncP2pData);

            // 5. Validar la respuesta del BNC
            if (is_null($bncResponse) || !isset($bncResponse['Status']) || $bncResponse['Status'] !== 'OK') {
                $errorMessage = $bncResponse['Message'] ?? 'Fallo al procesar el pago P2P con la pasarela de pagos.';
                Log::error('Fallo al procesar pago P2P con BNC.', [
                    'bnc_response' => $bncResponse,
                    'error_message' => $errorMessage
                ]);
                throw new \Exception($errorMessage);
            }

            // 6. ✅ GUARDAR EN LA TABLA VENTAS
            $venta = Sale::create([
                'cliente_id' => $validatedData['cliente_id'],
                'sucursal_id' => $validatedData['sucursal_id'],
                'aliado_id' => $validatedData['aliado_id'],
                'monto_total' => $validatedData['monto'],
                'monto_pagado' => $validatedData['monto'],
                'metodo_pago' => 'p2p',
                'referencia_banco' => $bncResponse['Reference'] ?? null,
                'transaction_id' => $bncResponse['TransactionIdentifier'] ?? null,
                'estado' => 'completado',
                'fecha_venta' => now(),
                'fecha_pago' => now(),
                'banco_destino' => $validatedData['banco'],
                'telefono_cliente' => $validatedData['telefono'],
                'cedula_cliente' => $validatedData['cedula'],
                'descripcion' => $validatedData['descripcion'],
                'codigo_autorizacion' => $bncResponse['AuthorizationCode'] ?? null,
                'respuesta_banco' => json_encode($bncResponse),
            ]);

            // 7. ✅ GUARDAR EN LA TABLA PAYOUTS (PAGO A ALIADOS)
            $payout = Payout::create([
                'venta_id' => $venta->id,
                'aliado_id' => $validatedData['aliado_id'],
                'monto_venta' => $validatedData['monto'],
                'porcentaje_comision' => $porcentajeAliado,
                'monto_comision' => $montoAliado,
                'estado' => 'pendiente',
                'fecha_generacion' => now(),
                'fecha_pago' => null,
                'referencia_venta' => $bncResponse['Reference'] ?? null,
                'metodo_pago_aliado' => $aliado->metodo_pago_default ?? 'transferencia',
                'cuenta_aliado' => $aliado->cuenta_bancaria,
                'banco_aliado' => $aliado->banco,
            ]);

            DB::commit();

            Log::info('Pago P2P procesado exitosamente con registro en ventas and payouts');
            return response()->json([
                'message' => 'Pago P2P procesado exitosamente.',
                'data' => [
                    'transaction_id' => $bncResponse['TransactionIdentifier'] ?? null,
                    'bnc_status' => $bncResponse['Status'] ?? null,
                    'bnc_reference' => $bncResponse['Reference'] ?? null,
                    'amount_processed' => $validatedData['monto'],
                    'venta_id' => $venta->id,
                    'payout_id' => $payout->id,
                    'porcentaje_aliado' => $porcentajeAliado,
                    'monto_aliado' => $montoAliado
                ]
            ], 200);
        } catch (ValidationException $e) {
            DB::rollBack();
            Log::warning('Error de validación en pago P2P', ['errors' => $e->errors()]);
            return response()->json(['message' => 'Datos de entrada inválidos.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en pago P2P: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Genera archivo de pago a proveedores en formato BNC
     */
    public function generarArchivoPagosBNC(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
                'tipo_cuenta' => 'required|in:corriente,ahorro',
                'concepto' => 'nullable|string|max:100',
            ]);

            // Obtener payouts pendientes
            $payouts = Payout::with(['ally', 'sale'])
                ->where('status', 'pending')
                ->whereBetween('generation_date', [
                    $validated['fecha_inicio'],
                    $validated['fecha_fin']
                ])
                ->get();

            if ($payouts->isEmpty()) {
                return response()->json([
                    'message' => 'No hay pagos pendientes en el rango de fechas especificado'
                ], 404);
            }

            // Generar archivo en formato BNC
            $archivoNombre = $this->generarArchivoBNC($payouts, $validated['tipo_cuenta'], $validated['concepto']);

            // Actualizar estado a "processing"
            Payout::whereIn('id', $payouts->pluck('id'))
                ->update(['status' => 'processing']);

            Log::info('Archivo de pagos BNC generado', [
                'archivo' => $archivoNombre,
                'cantidad_pagos' => $payouts->count(),
                'monto_total' => $payouts->sum('commission_amount')
            ]);

            return response()->json([
                'message' => 'Archivo de pagos BNC generado exitosamente',
                'data' => [
                    'archivo' => $archivoNombre,
                    'ruta_descarga' => Storage::url('pagos/bnc/' . $archivoNombre),
                    'total_pagos' => $payouts->count(),
                    'monto_total' => $payouts->sum('commission_amount'),
                    'fecha_generacion' => now()->format('Y-m-d H:i:s'),
                    'formato' => 'BNC'
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error generando archivo de pagos BNC: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Confirma pagos procesados
     */
    public function confirmarPagosProcesados(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $validated = $request->validate([
                'payout_ids' => 'required|array',
                'payout_ids.*' => 'integer|exists:payouts,id',
                'fecha_pago' => 'required|date',
                'referencia_pago' => 'required|string|max:100',
                'archivo_comprobante' => 'nullable|file|mimes:pdf,jpg,png|max:5120',
            ]);

            $payouts = Payout::whereIn('id', $validated['payout_ids'])
                ->where('status', 'processing')
                ->get();

            if ($payouts->isEmpty()) {
                throw new \Exception('No hay pagos en estado processing para confirmar');
            }

            $rutaComprobante = null;
            if ($request->hasFile('archivo_comprobante')) {
                $rutaComprobante = $request->file('archivo_comprobante')
                    ->store('comprobantes_pagos', 'public');
            }

            Payout::whereIn('id', $payouts->pluck('id'))->update([
                'status' => 'paid',
                'payment_date' => $validated['fecha_pago'],
                'payment_reference' => $validated['referencia_pago'],
                'payment_proof' => $rutaComprobante,
                'notes' => 'Pago confirmado el ' . now()->format('Y-m-d H:i:s')
            ]);

            DB::commit();

            Log::info('Pagos confirmados exitosamente', [
                'cantidad_pagos' => $payouts->count(),
                'monto_total' => $payouts->sum('commission_amount'),
                'referencia_pago' => $validated['referencia_pago']
            ]);

            return response()->json([
                'message' => 'Pagos confirmados exitosamente',
                'data' => [
                    'pagos_confirmados' => $payouts->count(),
                    'monto_total' => $payouts->sum('commission_amount'),
                    'fecha_pago' => $validated['fecha_pago']
                ]
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error confirmando pagos: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Métodos públicos para generación de archivos BNC (ahora públicos)
     */
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

    public function formatoBNC($payouts, $tipoCuenta, $concepto = null)
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

    /**
     * Descarga archivo de pagos BNC
     */
    public function descargarArchivoBNC($archivo)
    {
        try {
            $filePath = 'pagos/bnc/' . $archivo;

            if (!Storage::exists($filePath)) {
                return response()->json(['message' => 'Archivo BNC no encontrado'], 404);
            }

            // Headers para forzar descarga
            $headers = [
                'Content-Type' => 'text/plain',
                'Content-Disposition' => 'attachment; filename="' . $archivo . '"',
            ];

            return response(Storage::get($filePath), 200, $headers);
        } catch (\Exception $e) {
            Log::error('Error descargando archivo BNC: ' . $e->getMessage());
            return response()->json(['message' => 'Error al descargar el archivo BNC'], 500);
        }
    }

    /**
     * Obtiene pagos pendientes
     */
    public function obtenerPagosPendientes(): JsonResponse
    {
        try {
            $pagosPendientes = Payout::with(['ally', 'sale'])
                ->where('status', 'pending')
                ->orderBy('generation_date', 'asc')
                ->get();

            return response()->json([
                'data' => $pagosPendientes,
                'total' => $pagosPendientes->count(),
                'monto_total' => $pagosPendientes->sum('commission_amount')
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error obteniendo pagos pendientes: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtiene pagos por filtros (para la vista web)
     */
    public function obtenerPagosPorFiltro(Request $request): JsonResponse
    {
        try {
            $query = Payout::with(['ally', 'sale']);

            // Filtrar por estado
            if ($request->has('status') && $request->status != 'all') {
                $query->where('status', $request->status);
            }

            // Filtrar por rango de fechas
            if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
                $query->whereBetween('generation_date', [
                    $request->fecha_inicio,
                    $request->fecha_fin
                ]);
            }

            // Filtrar por aliado
            if ($request->has('ally_id')) {
                $query->where('ally_id', $request->ally_id);
            }

            $payouts = $query->orderBy('generation_date', 'desc')->paginate(20);

            return response()->json([
                'data' => $payouts->items(),
                'total' => $payouts->total(),
                'current_page' => $payouts->currentPage(),
                'per_page' => $payouts->perPage(),
                'last_page' => $payouts->lastPage()
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error obteniendo pagos por filtro: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
