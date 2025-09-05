<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\BncApiService;
use App\Models\Order;
use App\Models\PartnerPayout;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function initiateC2PPayment(Request $request): JsonResponse
    {
        Log::info('Solicitud recibida para iniciar pago C2P.', ['request_data' => $request->all()]);

        DB::beginTransaction();
        try {
            // 1. ✅ Validar los datos de entrada (SOLO campos del legacy)
            $validatedData = $request->validate([
                'banco' => 'required|integer',                    // ← Cambiado a integer
                'telefono' => 'required|string|regex:/^[0-9]{10,15}$/',
                'cedula' => 'required|string|min:6|max:20',      // ← Permitir "V" y números
                'monto' => 'required|numeric|min:0.01',
                'token' => 'required|string|regex:/^[0-9]{6,7}$/',
                'terminal' => 'required|string|max:50',
                // ← REMOVER child_client_id y branch_id (no se usan en legacy)
            ]);

            // 2. ✅ Preparar datos para el BNC (EXACTAMENTE como el legacy - SIN CAMPOS EXTRA)
            $bncC2pData = [
                'banco' => intval($validatedData['banco']),      // ← intval() como legacy
                'telefono' => $validatedData['telefono'],        // ← Raw, sin formato
                'cedula' => $validatedData['cedula'],            // ← Raw, mantener "V" si existe
                'monto' => floatval($validatedData['monto']),    // ← floatval() como legacy
                'token' => $validatedData['token'],              // ← Raw
                'terminal' => $validatedData['terminal'],        // ← Raw
                // ← NO incluir child_client_id, branch_id, transaction_id
            ];

            Log::debug('Datos preparados para BNC C2P (legacy compatible)', ['bncC2pData' => $bncC2pData]);

            // 3. Delegar la llamada al servicio BNC
            $bncResponse = $this->bncApiService->initiateC2PPayment($bncC2pData);

            // 4. Validar la respuesta del BNC
            if (is_null($bncResponse) || !isset($bncResponse['Status']) || $bncResponse['Status'] !== 'OK') {
                $errorMessage = $bncResponse['Message'] ?? 'Fallo al procesar el pago C2P con la pasarela de pagos.';
                Log::error('Fallo al procesar pago C2P con BNC.', [
                    'bnc_response' => $bncResponse,
                    'error_message' => $errorMessage
                ]);
                throw new \Exception($errorMessage);
            }

            DB::commit();

            Log::info('Pago C2P procesado exitosamente en BNC');
            return response()->json([
                'message' => 'Pago C2P procesado exitosamente.',
                'data' => [
                    'bnc_reference' => $bncResponse['Reference'] ?? null,
                    'bnc_status' => $bncResponse['Status'] ?? null,
                    'transaction_id' => $bncResponse['TransactionIdentifier'] ?? null,
                    'amount_processed' => $validatedData['monto']
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
     * Procesa un pago con tarjeta (VPOS) con la API del BNC.
     * El monto total va a la cuenta de Rumbero Extremo.
     * Luego, se registra el pago pendiente al aliado.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function processCardPayment(Request $request): JsonResponse
    {
        Log::info('Solicitud recibida para procesar pago con tarjeta.', ['request_data' => $request->all()]);

        DB::beginTransaction();
        try {
            // 1. ✅ Validar los datos de entrada (EXACTAMENTE como el legacy VPOS)
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
                'afiliacion' => 'required|numeric'
            ]);

            // 2. ✅ Preparar datos para el BNC (EXACTAMENTE como el legacy)
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

            // 3. Delegar la llamada al servicio BNC
            $bncResponse = $this->bncApiService->processCardPayment($bncCardData);

            // 4. Validar la respuesta del BNC - Adaptado al nuevo formato
            if (is_null($bncResponse) || !isset($bncResponse['status']) || $bncResponse['status'] !== 'OK') {
                $errorMessage = $bncResponse['message'] ?? 'Fallo al procesar el pago con la pasarela VPOS.';
                Log::error('Fallo al procesar pago VPOS.', [
                    'bnc_response' => $bncResponse,
                    'error_message' => $errorMessage
                ]);
                throw new \Exception($errorMessage);
            }

            // 5. ✅ Pago exitoso: Aquí puedes agregar tu lógica de negocio
            // (Actualizar orden, registrar pago, etc.)
            $transactionId = $bncResponse['TransactionIdentifier'] ?? $validatedData['identificador'];

            Log::info('Pago VPOS procesado exitosamente en BNC', [
                'transaction_id' => $transactionId,
                'bnc_status' => $bncResponse['status'] ?? null
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Pago con tarjeta procesado exitosamente.',
                'data' => [
                    'transaction_id' => $transactionId,
                    'bnc_status' => $bncResponse['status'] ?? null,
                    'bnc_reference' => $bncResponse['Reference'] ?? null,
                    'amount_processed' => $validatedData['monto']
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

    public function processP2PPayment(Request $request): JsonResponse
    {
        Log::info('Solicitud recibida para procesar pago P2P.', ['request_data' => $request->all()]);

        DB::beginTransaction();
        try {
            // 1. ✅ Validar los datos de entrada (EXACTAMENTE como el legacy P2P)
            $validatedData = $request->validate([
                'banco' => 'required|integer',                    // ← Cambiado a integer
                'telefono' => 'required|string|regex:/^[0-9]{10,15}$/', // ← Mantener string
                'cedula' => 'required|string|min:6|max:20',      // ← Permitir "V" y números
                'beneficiario' => 'required|string|max:255',
                'monto' => 'required|numeric|min:0.01',
                'descripcion' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',             // ← Cambiado a nullable
                // ← order_id removido (no es parte del payload BNC)
            ]);

            // 2. ✅ Cargar la orden (si es necesario para tu lógica de negocio)
            $orderId = $request->input('order_id');
            $order = null;
            $ally = null;

            if ($orderId) {
                $order = Order::with('ally')->find($orderId);
                if (!$order) {
                    throw new \Exception('Orden no encontrada para el pago P2P.');
                }
                $ally = $order->ally;
                if (!$ally) {
                    throw new \Exception('Aliado no asociado a la orden para el pago P2P.');
                }
            }

            // 3. ✅ Preparar datos para el BNC (EXACTAMENTE como el legacy)
            $bncP2pData = [
                'banco' => intval($validatedData['banco']),      // ← intval() como legacy
                'telefono' => $validatedData['telefono'],        // ← Raw, sin formato
                'cedula' => $validatedData['cedula'],            // ← Raw, mantener "V" si existe
                'beneficiario' => $validatedData['beneficiario'], // ← Raw
                'monto' => floatval($validatedData['monto']),    // ← floatval() como legacy
                'descripcion' => $validatedData['descripcion'],  // ← Raw
                'email' => $validatedData['email'] ?? null,      // ← null en lugar de string vacío
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

            // 6. ✅ Pago exitoso: Actualizar orden si existe
            if ($order) {
                $order->status = 'completed';
                $order->payment_method = 'P2P';
                $order->transaction_id = $bncResponse['TransactionIdentifier'] ?? 'N/A';
                $order->paid_amount = $validatedData['monto'];
                $order->save();

                $rumberoCommissionAmount = $order->discount_amount ?? 0;
                $amountToPayToAlly = $validatedData['monto'] - $rumberoCommissionAmount;

                PartnerPayout::create([
                    'order_id' => $order->id,
                    'partner_id' => $ally->id,
                    'amount' => $amountToPayToAlly,
                    'commission_amount' => $rumberoCommissionAmount,
                    'status' => 'pending',
                    'bank_name' => $ally->bank_name,
                    'account_number' => $ally->account_number,
                    'account_type' => $ally->account_type,
                    'id_document' => $ally->id_document,
                    'notes' => "Pago P2P de orden #{$order->id}.",
                ]);
            }

            DB::commit();

            Log::info('Pago P2P procesado exitosamente en BNC', [
                'order_id' => $order?->id,
                'transaction_id' => $bncResponse['TransactionIdentifier'] ?? null
            ]);

            return response()->json([
                'message' => 'Pago P2P procesado exitosamente.',
                'data' => [
                    'order_id' => $order?->id,
                    'transaction_id' => $bncResponse['TransactionIdentifier'] ?? null,
                    'bnc_status' => $bncResponse['Status'] ?? null,
                    'bnc_reference' => $bncResponse['Reference'] ?? null,
                    'amount_processed' => $validatedData['monto']
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
}
