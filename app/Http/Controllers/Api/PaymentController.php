<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ally;
use App\Models\Payout;
use App\Models\Sale;
use App\Services\BncApiService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    protected BncApiService $bncApiService;

    public function __construct(BncApiService $bncApiService)
    {
        $this->bncApiService = $bncApiService;
    }

    /**
     * Inicia un pago C2P (Pago Móvil) con la API del BNC.
     */
    public function initiateC2PPayment(Request $request): JsonResponse
    {
        Log::info('Solicitud recibida para iniciar pago C2P.', ['request_data' => $request->all()]);

        DB::beginTransaction();
        try {
            // 1. Validar campos esenciales
            $bankValidatedData = $request->validate([
                'DebtorBankCode' => 'required|integer',
                'DebtorCellPhone' => 'required|string|regex:/^[0-9]{10,15}$/',
                'DebtorID' => 'required|string|min:6|max:20',
                'Amount' => 'required|numeric|min:0.01',
                'Token' => 'required|string|regex:/^[0-9]{6,7}$/',
                'Terminal' => 'required|string|max:50',
            ]);

            // 2. Campos adicionales opcionales
            $systemValidatedData = $request->validate([
                'ChildClientID' => 'nullable|string|max:50',
                'BranchID' => 'nullable|string|max:50',
                'cliente_id' => 'nullable|integer|exists:clientes,id',
                'sucursal_id' => 'nullable|integer|exists:sucursales,id',
                'aliado_id' => 'nullable|integer|exists:aliados,id',
                'descripcion' => 'nullable|string|max:255',
            ]);

            $validatedData = array_merge($bankValidatedData, $systemValidatedData);

            // 3. Preparar datos para BNC
            $bncC2pData = [
                'DebtorBankCode' => (int)$validatedData['DebtorBankCode'],
                'DebtorCellPhone' => $validatedData['DebtorCellPhone'],
                'DebtorID' => $validatedData['DebtorID'],
                'Amount' => (float)$validatedData['Amount'],
                'Token' => $validatedData['Token'],
                'Terminal' => $validatedData['Terminal'],
                'ChildClientID' => $validatedData['ChildClientID'] ?? '',
                'BranchID' => $validatedData['BranchID'] ?? ''
            ];

            Log::debug('Datos preparados para BNC C2P', ['bncC2pData' => $bncC2pData]);

            // 4. Ejecutar pago C2P
            $bncResponse = $this->bncApiService->initiateC2PPayment($bncC2pData);

            // 5. ✅ CORRECCIÓN: Validar respuesta del BNC
            if (!$this->isBncResponseSuccessful($bncResponse)) {
                $errorMessage = $bncResponse['Message'] ?? $bncResponse['message'] ?? 'Fallo al procesar el pago C2P.';
                Log::error('Fallo al procesar pago C2P', ['bnc_response' => $bncResponse]);
                throw new \Exception($errorMessage);
            }

            // 6. Procesar aliado y comisiones
            $aliadoData = $this->processAliadoData($validatedData);

            // 7. Guardar venta
            $venta = Sale::create([
                'cliente_id' => $validatedData['cliente_id'] ?? null,
                'sucursal_id' => $validatedData['sucursal_id'] ?? null,
                'aliado_id' => $validatedData['aliado_id'] ?? null,
                'monto_total' => $validatedData['Amount'],
                'monto_pagado' => $validatedData['Amount'],
                'metodo_pago' => 'pago_movil',
                'referencia_banco' => $bncResponse['Reference'] ?? null,
                'transaction_id' => $bncResponse['TransactionIdentifier'] ?? null,
                'estado' => 'completado',
                'fecha_venta' => now(),
                'fecha_pago' => now(),
                'terminal' => $validatedData['Terminal'],
                'banco_destino' => $validatedData['DebtorBankCode'],
                'telefono_cliente' => $validatedData['DebtorCellPhone'],
                'cedula_cliente' => $validatedData['DebtorID'],
                'descripcion' => $validatedData['descripcion'] ?? 'Pago móvil procesado',
                'codigo_autorizacion' => $bncResponse['AuthorizationCode'] ?? $bncResponse['Code'] ?? null,
                'respuesta_banco' => json_encode($bncResponse),
            ]);

            // 8. Guardar payout si hay aliado
            $payout = $this->createPayout($venta, $aliadoData, $bncResponse);

            DB::commit();

            Log::info('Pago C2P procesado exitosamente');
            return response()->json([
                'success' => true,
                'message' => 'Pago C2P procesado exitosamente.',
                'data' => $this->buildSuccessResponse($venta, $payout, $bncResponse, $validatedData, $aliadoData)
            ], 200);
        } catch (ValidationException $e) {
            DB::rollBack();
            Log::warning('Error de validación en pago C2P', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Datos de entrada inválidos.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en pago C2P: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'bnc_response' => $bncResponse ?? null
            ], 500);
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
            // 1. Validar campos esenciales
            $bankValidatedData = $request->validate([
                'TransactionIdentifier' => 'required|string|max:50',
                'Amount' => 'required|numeric|min:0.01',
                'idCardType' => 'required|integer',
                'CardNumber' => 'required|string|max:20',
                'dtExpiration' => 'required|numeric|digits:6',
                'CardHolderName' => 'required|string|max:255',
                'AccountType' => 'required|integer',
                'CVV' => 'required|numeric|digits_between:3,4',
                'CardPIN' => 'required|numeric',
                'CardHolderID' => 'required|numeric',
                'AffiliationNumber' => 'required|numeric',
                'OperationRef' => 'required|string|max:100',
            ]);

            // 2. Campos adicionales opcionales
            $systemValidatedData = $request->validate([
                'ChildClientID' => 'nullable|string|max:50',
                'BranchID' => 'nullable|string|max:50',
                'cliente_id' => 'nullable|integer|exists:clientes,id',
                'sucursal_id' => 'nullable|integer|exists:sucursales,id',
                'aliado_id' => 'nullable|integer|exists:aliados,id',
                'descripcion' => 'nullable|string|max:255',
            ]);

            $validatedData = array_merge($bankValidatedData, $systemValidatedData);

            // 3. Preparar datos para BNC
            $bncCardData = [
                'TransactionIdentifier' => $validatedData['TransactionIdentifier'],
                'Amount' => (float)$validatedData['Amount'],
                'idCardType' => (int)$validatedData['idCardType'],
                'CardNumber' => $validatedData['CardNumber'],
                'dtExpiration' => (int)$validatedData['dtExpiration'],
                'CardHolderName' => $validatedData['CardHolderName'],
                'AccountType' => (int)$validatedData['AccountType'],
                'CVV' => (int)$validatedData['CVV'],
                'CardPIN' => (int)$validatedData['CardPIN'],
                'CardHolderID' => (int)$validatedData['CardHolderID'],
                'AffiliationNumber' => (int)$validatedData['AffiliationNumber'],
                'OperationRef' => $validatedData['OperationRef'],
                'ChildClientID' => $validatedData['ChildClientID'] ?? '',
                'BranchID' => $validatedData['BranchID'] ?? ''
            ];

            Log::debug('Datos preparados para BNC VPOS', ['bncCardData' => $bncCardData]);

            // 4. Ejecutar pago VPOS
            $bncResponse = $this->bncApiService->processCardPayment($bncCardData);

            // 5. ✅ CORRECCIÓN: Validar respuesta del BNC
            if (!$this->isBncResponseSuccessful($bncResponse)) {
                $errorMessage = $bncResponse['Message'] ?? $bncResponse['message'] ?? 'Fallo al procesar el pago con tarjeta.';
                Log::error('Fallo al procesar pago VPOS', ['bnc_response' => $bncResponse]);
                throw new \Exception($errorMessage);
            }

            // 6. Procesar aliado y comisiones
            $aliadoData = $this->processAliadoData($validatedData);

            // 7. Guardar venta
            $venta = Sale::create([
                'cliente_id' => $validatedData['cliente_id'] ?? null,
                'sucursal_id' => $validatedData['sucursal_id'] ?? null,
                'aliado_id' => $validatedData['aliado_id'] ?? null,
                'monto_total' => $validatedData['Amount'],
                'monto_pagado' => $validatedData['Amount'],
                'metodo_pago' => 'tarjeta_credito',
                'referencia_banco' => $bncResponse['Reference'] ?? null,
                'transaction_id' => $bncResponse['TransactionIdentifier'] ?? $validatedData['TransactionIdentifier'],
                'estado' => 'completado',
                'fecha_venta' => now(),
                'fecha_pago' => now(),
                'terminal' => $validatedData['TransactionIdentifier'],
                'descripcion' => $validatedData['descripcion'] ?? 'Pago con tarjeta procesado',
                'codigo_autorizacion' => $bncResponse['AuthorizationCode'] ?? $bncResponse['Code'] ?? null,
                'respuesta_banco' => json_encode($bncResponse),
                'numero_tarjeta' => substr($validatedData['CardNumber'], -4),
                'nombre_titular' => $validatedData['CardHolderName'],
                'tipo_tarjeta' => $validatedData['idCardType'],
            ]);

            // 8. Guardar payout si hay aliado
            $payout = $this->createPayout($venta, $aliadoData, $bncResponse);

            DB::commit();

            Log::info('Pago VPOS procesado exitosamente');
            return response()->json([
                'success' => true,
                'message' => 'Pago con tarjeta procesado exitosamente.',
                'data' => $this->buildSuccessResponse($venta, $payout, $bncResponse, $validatedData, $aliadoData)
            ], 200);
        } catch (ValidationException $e) {
            DB::rollBack();
            Log::warning('Error de validación en pago VPOS', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Datos de entrada inválidos.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en pago VPOS: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'bnc_response' => $bncResponse ?? null
            ], 500);
        }
    }

    /**
     * Procesa un pago P2P (Transferencia)
     */
    public function processP2PPayment(Request $request): JsonResponse
    {
        Log::info('Solicitud recibida para procesar pago P2P.', ['request_data' => $request->all()]);

        DB::beginTransaction();
        try {
            // 1. Validar campos esenciales
            $bankValidatedData = $request->validate([
                'Amount' => 'required|numeric|min:0.01',
                'BeneficiaryBankCode' => 'required|integer',
                'BeneficiaryCellPhone' => 'required|string|regex:/^[0-9]{10,15}$/',
                'BeneficiaryID' => 'required|string|min:6|max:20',
                'BeneficiaryName' => 'required|string|max:255',
                'Description' => 'required|string|max:255',
                'OperationRef' => 'required|string|max:100',
            ]);

            // 2. Campos adicionales opcionales
            $systemValidatedData = $request->validate([
                'BeneficiaryEmail' => 'nullable|email|max:255',
                'ChildClientID' => 'nullable|string|max:50',
                'BranchID' => 'nullable|string|max:50',
                'cliente_id' => 'nullable|integer|exists:clientes,id',
                'sucursal_id' => 'nullable|integer|exists:sucursales,id',
                'aliado_id' => 'nullable|integer|exists:aliados,id',
            ]);

            $validatedData = array_merge($bankValidatedData, $systemValidatedData);

            // 3. Preparar datos para BNC
            $bncP2pData = [
                'Amount' => (float)$validatedData['Amount'],
                'BeneficiaryBankCode' => (int)$validatedData['BeneficiaryBankCode'],
                'BeneficiaryCellPhone' => $validatedData['BeneficiaryCellPhone'],
                'BeneficiaryID' => $validatedData['BeneficiaryID'],
                'BeneficiaryName' => $validatedData['BeneficiaryName'],
                'Description' => $validatedData['Description'],
                'OperationRef' => $validatedData['OperationRef'],
                'BeneficiaryEmail' => $validatedData['BeneficiaryEmail'] ?? '',
                'ChildClientID' => $validatedData['ChildClientID'] ?? '',
                'BranchID' => $validatedData['BranchID'] ?? ''
            ];

            Log::debug('Datos preparados para BNC P2P', ['bncP2pData' => $bncP2pData]);

            // 4. Ejecutar pago P2P
            $bncResponse = $this->bncApiService->initiateP2PPayment($bncP2pData);

            // 5. ✅ CORRECCIÓN: Validar respuesta del BNC
            if (!$this->isBncResponseSuccessful($bncResponse)) {
                $errorMessage = $bncResponse['Message'] ?? $bncResponse['message'] ?? 'Fallo al procesar el pago P2P.';
                Log::error('Fallo al procesar pago P2P', ['bnc_response' => $bncResponse]);
                throw new \Exception($errorMessage);
            }

            // 6. Procesar aliado y comisiones
            $aliadoData = $this->processAliadoData($validatedData);

            // 7. Guardar venta
            $venta = Sale::create([
                'cliente_id' => $validatedData['cliente_id'] ?? null,
                'sucursal_id' => $validatedData['sucursal_id'] ?? null,
                'aliado_id' => $validatedData['aliado_id'] ?? null,
                'monto_total' => $validatedData['Amount'],
                'monto_pagado' => $validatedData['Amount'],
                'metodo_pago' => 'p2p',
                'referencia_banco' => $bncResponse['Reference'] ?? null,
                'transaction_id' => $bncResponse['TransactionIdentifier'] ?? null,
                'estado' => 'completado',
                'fecha_venta' => now(),
                'fecha_pago' => now(),
                'banco_destino' => $validatedData['BeneficiaryBankCode'],
                'telefono_cliente' => $validatedData['BeneficiaryCellPhone'],
                'cedula_cliente' => $validatedData['BeneficiaryID'],
                'descripcion' => $validatedData['Description'],
                'codigo_autorizacion' => $bncResponse['AuthorizationCode'] ?? null,
                'respuesta_banco' => json_encode($bncResponse),
                'beneficiario' => $validatedData['BeneficiaryName'],
                'email_beneficiario' => $validatedData['BeneficiaryEmail'] ?? null,
            ]);

            // 8. Guardar payout si hay aliado
            $payout = $this->createPayout($venta, $aliadoData, $bncResponse);

            DB::commit();

            Log::info('Pago P2P procesado exitosamente');
            return response()->json([
                'success' => true,
                'message' => 'Pago P2P procesado exitosamente.',
                'data' => $this->buildSuccessResponse($venta, $payout, $bncResponse, $validatedData, $aliadoData)
            ], 200);
        } catch (ValidationException $e) {
            DB::rollBack();
            Log::warning('Error de validación en pago P2P', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Datos de entrada inválidos.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en pago P2P: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'bnc_response' => $bncResponse ?? null
            ], 500);
        }
    }

    /**
     * ==================== MÉTODOS AUXILIARES OPTIMIZADOS ====================
     */

    /**
     * Valida si la respuesta del BNC es exitosa
     */
    private function isBncResponseSuccessful(?array $bncResponse): bool
    {
        if (is_null($bncResponse)) {
            return false;
        }

        $status = $bncResponse['Status'] ?? $bncResponse['status'] ?? null;
        $reference = $bncResponse['Reference'] ?? $bncResponse['reference'] ?? null;

        return $status === 'OK' && !empty($reference);
    }

    /**
     * Procesa datos del aliado y calcula comisiones
     */
    private function processAliadoData(array $validatedData): array
    {
        $aliadoId = $validatedData['aliado_id'] ?? null;
        $porcentajeAliado = 0;
        $montoAliado = 0;
        $aliado = null;

        if ($aliadoId) {
            $aliado = Ally::find($aliadoId);
            if ($aliado) {
                $porcentajeAliado = $aliado->porcentaje_comision;
                $montoAliado = ($validatedData['Amount'] * $porcentajeAliado) / 100;
            }
        }

        return [
            'aliado_id' => $aliadoId,
            'aliado' => $aliado,
            'porcentaje_comision' => $porcentajeAliado,
            'monto_comision' => $montoAliado
        ];
    }

    /**
     * Crea un registro de payout si hay aliado
     */
    private function createPayout(Sale $venta, array $aliadoData, array $bncResponse): ?Payout
    {
        if (!$aliadoData['aliado_id'] || !$aliadoData['aliado']) {
            return null;
        }

        return Payout::create([
            'venta_id' => $venta->id,
            'aliado_id' => $aliadoData['aliado_id'],
            'monto_venta' => $venta->monto_total,
            'porcentaje_comision' => $aliadoData['porcentaje_comision'],
            'monto_comision' => $aliadoData['monto_comision'],
            'estado' => 'pendiente',
            'fecha_generacion' => now(),
            'fecha_pago' => null,
            'referencia_venta' => $bncResponse['Reference'] ?? null,
            'metodo_pago_aliado' => $aliadoData['aliado']->metodo_pago_default ?? 'transferencia',
            'cuenta_aliado' => $aliadoData['aliado']->cuenta_bancaria,
            'banco_aliado' => $aliadoData['aliado']->banco,
        ]);
    }

    /**
     * Construye respuesta de éxito estandarizada
     */
    private function buildSuccessResponse(Sale $venta, ?Payout $payout, array $bncResponse, array $validatedData, array $aliadoData): array
    {
        $clienteId = $validatedData['cliente_id'] ?? null;
        $sucursalId = $validatedData['sucursal_id'] ?? null;
        $aliadoId = $validatedData['aliado_id'] ?? null;

        return [
            'transaction_id' => $venta->transaction_id,
            'bnc_status' => $bncResponse['Status'] ?? $bncResponse['status'] ?? null,
            'bnc_reference' => $bncResponse['Reference'] ?? $bncResponse['reference'] ?? null,
            'amount_processed' => $validatedData['Amount'],
            'venta_id' => $venta->id,
            'payout_id' => $payout?->id,
            'porcentaje_aliado' => $aliadoData['porcentaje_comision'],
            'monto_aliado' => $aliadoData['monto_comision'],
            'fecha_venta' => $venta->fecha_venta->format('Y-m-d H:i:s'),
            'registro_completo' => !is_null($clienteId) && !is_null($sucursalId) && !is_null($aliadoId),
            'operation_ref' => $validatedData['OperationRef'] ?? null,
            'authorization_code' => $bncResponse['AuthorizationCode'] ?? $bncResponse['Code'] ?? null
        ];
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
