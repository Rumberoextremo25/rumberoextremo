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
            // 1. Validar los datos de entrada (EXACTAMENTE como el payload del BNC)
            $validatedData = $request->validate([
                'DebtorBankCode' => 'required|integer',
                'DebtorCellPhone' => 'required|string|regex:/^[0-9]{10,15}$/',
                'DebtorID' => 'required|string|min:6|max:20',
                'Amount' => 'required|numeric|min:0.01',
                'Token' => 'required|string|regex:/^[0-9]{6,7}$/', // ← Token de 6-7 dígitos
                'Terminal' => 'required|string|max:50',
                'ChildClientID' => 'nullable|string|max:50',
                'BranchID' => 'nullable|string|max:50',
            ]);

            // 2. Preparar datos para el BNC (EXACTAMENTE como el payload esperado)
            $bncC2pData = [
                'banco' => $validatedData['DebtorBankCode'],
                'telefono' => $validatedData['DebtorCellPhone'],
                'cedula' => $validatedData['DebtorID'],
                'monto' => $validatedData['Amount'],
                'token' => $validatedData['Token'], // ← Token de 6-7 dígitos
                'terminal' => $validatedData['Terminal'],
                'child_client_id' => $validatedData['ChildClientID'] ?? null,
                'branch_id' => $validatedData['BranchID'] ?? null,
            ];

            Log::debug('Datos preparados para BNC C2P', ['bncC2pData' => $bncC2pData]);

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
                    'amount_processed' => $validatedData['Amount']
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

        DB::beginTransaction(); // Inicia una transacción de base de datos
        try {
            // 1. Validar los datos de entrada
            $validatedData = $request->validate([
                'order_id' => 'required|exists:orders,id',
                'CardNumber' => 'required|string',
                'dtExpiration' => 'required|string|regex:/^(0[1-9]|1[0-2])\/?([0-9]{4}|[0-9]{2})$/',
                'CardHolderName' => 'nullable|string|max:255',
                'CVV' => 'required|string|digits_between:3,4',
                'Amount' => 'required|numeric|min:0.01',
                'Terminal' => 'required|string|max:50',
            ]);

            // Cargar la orden y su aliado asociado
            $order = Order::with('ally')->find($validatedData['order_id']);
            if (!$order) {
                throw new \Exception('Orden no encontrada para el pago con tarjeta.');
            }
            $ally = $order->ally;
            if (!$ally) {
                throw new \Exception('Aliado no asociado a la orden para el pago con tarjeta.');
            }

            // 2. Preparar los datos para la API de BNC VPOS
            $expiration = str_replace('/', '', $validatedData['dtExpiration']);
            if (strlen($expiration) === 4) {
                $expiration = substr($expiration, 0, 2) . '20' . substr($expiration, 2, 2);
            }

            // Adaptar las claves del array a las que el método processCardPayment del servicio espera
            $bncCardData = [
                "TransactionIdentifier" => "ORDER-{$order->id}-" . uniqid(), // Genera un ID de transacción único si no lo tienes
                "Amount" => floatval($validatedData['Amount']),
                "idCardType" => 0, // Debes determinar esto desde la data o la lógica de negocio
                "CardNumber" => $validatedData['CardNumber'],
                "dtExpiration" => (int) $expiration,
                "CardHolderName" => $validatedData['CardHolderName'],
                "AccountType" => 0, // Debes determinar esto desde la data o la lógica de negocio
                "CVV" => $validatedData['CVV'],
                "CardPIN" => 0, // El PIN no se envía en transacciones no presenciales. Ajusta según los requisitos de BNC
                "CardHolderID" => 0, // El ID del tarjetahabiente. Debes obtenerlo de la data de la orden
                "AffiliationNumber" => 0, // El número de afiliación lo obtienes de tu configuración o del aliado
            ];

            // 3. Delegar la llamada a la API del BNC al servicio
            $bncResponse = $this->bncApiService->processCardPayment($bncCardData);

            // 4. Validar la respuesta del BNC
            // Nota: El servicio ya maneja el logging de errores, aquí solo validamos si la respuesta es exitosa.
            if (is_null($bncResponse) || !isset($bncResponse['Status']) || $bncResponse['Status'] !== 'OK') {
                $errorMessage = $bncResponse['Message'] ?? 'Fallo al procesar el pago con la pasarela VPOS.';
                Log::error('Fallo al procesar pago VPOS.', ['bnc_response' => $bncResponse, 'order_id' => $order->id]);
                throw new \Exception($errorMessage);
            }

            // 5. Pago exitoso con el BNC: Actualizar la orden y registrar el pago pendiente al aliado
            $order->status = 'completed';
            $order->payment_method = 'Card';
            $order->transaction_id = $bncResponse['TransactionIdentifier'] ?? 'N/A';
            $order->paid_amount = $validatedData['Amount'];
            $order->save();

            // Calcular el monto para el aliado y la comisión de la plataforma
            $rumberoCommissionAmount = $order->discount_amount ?? 0;
            $amountToPayToAlly = $validatedData['Amount'] - $rumberoCommissionAmount;

            // 6. Registrar el pago pendiente al aliado en la base de datos
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
                'notes' => "Pago con tarjeta de orden #{$order->id}.",
            ]);

            DB::commit();

            Log::info('Pago con tarjeta procesado y pago a aliado registrado como pendiente.', ['order_id' => $order->id]);
            return response()->json([
                'message' => 'Pago con tarjeta procesado exitosamente. El pago a su aliado ha sido registrado y se procesará pronto.',
                'data' => [
                    'order_id' => $order->id,
                    'transaction_id' => $order->transaction_id,
                    'amount_paid_by_customer' => $order->paid_amount,
                ]
            ], 200);
        } catch (ValidationException $e) {
            DB::rollBack();
            Log::warning('Error de validación al procesar pago con tarjeta.', ['errors' => $e->errors()]);
            return response()->json(['message' => 'Datos de entrada inválidos.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Excepción inesperada al procesar pago con tarjeta: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Ha ocurrido un error inesperado al procesar tu solicitud.', 'error' => $e->getMessage()], 500);
        }
    }

    public function processP2PPayment(Request $request): JsonResponse
    {
        Log::info('Solicitud recibida para procesar pago P2P.', ['request_data' => $request->all()]);

        DB::beginTransaction();
        try {
            // 1. Validar los datos de entrada
            $validatedData = $request->validate([
                'order_id' => 'required|exists:orders,id',
                'beneficiario' => 'required|string',
                'banco' => 'required|string',
                'monto' => 'required|numeric|min:0.01',
                'telefono' => 'required|string|regex:/^[0-9]{11}$/',
                'cedula' => 'required|string|min:6|max:15',
                'email' => 'required|email',
                'descripcion' => 'required|string|max:255',
            ]);

            // Cargar la orden y su aliado
            $order = Order::with('ally')->find($validatedData['order_id']);
            if (!$order) {
                throw new \Exception('Orden no encontrada para el pago P2P.');
            }
            $ally = $order->ally;
            if (!$ally) {
                throw new \Exception('Aliado no asociado a la orden para el pago P2P.');
            }

            // 2. Preparar los datos para la API de BNC
            $bncP2pData = [
                'beneficiario' => $validatedData['beneficiario'],
                'banco' => $validatedData['banco'],
                'monto' => $validatedData['monto'],
                'telefono' => $validatedData['telefono'],
                'cedula' => $validatedData['cedula'],
                'email' => $validatedData['email'],
                'descripcion' => $validatedData['descripcion'],
            ];

            // 3. Delegar la llamada a la API del BNC al servicio
            $bncResponse = $this->bncApiService->initiateP2PPayment($bncP2pData);

            // 4. Validar la respuesta del BNC
            if (is_null($bncResponse) || !isset($bncResponse['Status']) || $bncResponse['Status'] !== 'OK') {
                $errorMessage = $bncResponse['Message'] ?? 'Fallo al procesar el pago P2P con la pasarela de pagos.';
                Log::error('Fallo al procesar pago P2P con BNC.', [
                    'bnc_response' => $bncResponse,
                    'error_message' => $errorMessage
                ]);
                throw new \Exception($errorMessage);
            }

            // 5. Pago exitoso con el BNC: Actualizar la orden y registrar el pago
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

            DB::commit();

            Log::info('Pago P2P procesado y pago a aliado registrado como pendiente.', ['order_id' => $order->id]);
            return response()->json([
                'message' => 'Pago P2P procesado exitosamente. El pago a su aliado ha sido registrado.',
                'data' => [
                    'order_id' => $order->id,
                    'transaction_id' => $order->transaction_id,
                    'amount_paid_by_customer' => $order->paid_amount,
                ]
            ], 200);
        } catch (ValidationException $e) {
            DB::rollBack();
            Log::warning('Error de validación al iniciar pago P2P.', ['errors' => $e->errors()]);
            return response()->json(['message' => 'Datos de entrada inválidos.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Excepción inesperada al iniciar pago P2P: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Ha ocurrido un error inesperado al procesar tu solicitud.', 'error' => $e->getMessage()], 500);
        }
    }
}
