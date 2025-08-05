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

    // Inyectamos el servicio BncApiService en el constructor
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

        DB::beginTransaction(); // Iniciar transacción de base de datos
        try {
            // 1. Validar los datos de entrada
            $validatedData = $request->validate([
                'order_id' => 'required|exists:orders,id', // Necesitamos el ID de la orden
                'DebtorBankCode' => 'required|string',
                'DebtorCellPhone' => 'required|string|regex:/^[0-9]{11}$/',
                'DebtorID' => 'required|string|min:6|max:15',
                'Amount' => 'required|numeric|min:0.01', // Este es el monto TOTAL que paga el cliente
                'Token' => 'required|string|digits:6',
                'Terminal' => 'required|string|max:50',
            ]);

            // Cargar la orden y su aliado asociado
            $order = Order::with('ally')->find($validatedData['order_id']);
            if (!$order) {
                throw new \Exception('Orden no encontrada para el pago C2P.');
            }
            $ally = $order->ally;
            if (!$ally) {
                throw new \Exception('Aliado no asociado a la orden para el pago C2P.');
            }

            // 2. Preparar los datos para la API de BNC
            // Asumo que BNC espera DebtorBankCode como INT, ajusta si es string
            $validatedData['DebtorBankCode'] = (int) $validatedData['DebtorBankCode'];

            // 3. Delegar la llamada a la API del BNC al servicio para procesar el PAGO TOTAL
            $bncResponse = $this->bncApiService->initiateC2PPayment($validatedData);

            if (is_null($bncResponse) || !isset($bncResponse['success']) || !$bncResponse['success']) {
                // Si la respuesta del BNC indica fallo o es nula/inválida
                Log::error('Fallo al procesar pago C2P con BNC.', ['bnc_response' => $bncResponse]);
                throw new \Exception($bncResponse['message'] ?? 'Fallo al conectar o procesar el pago C2P con el proveedor de pagos.');
            }

            // 4. Pago exitoso a Rumbero Extremo. Actualizar la orden y registrar el pago pendiente al aliado.
            $order->status = 'completed'; // O un estado intermedio como 'paid_to_platform'
            $order->payment_method = 'C2P';
            $order->transaction_id = $bncResponse['data']['transactionID'] ?? 'N/A'; // Usa el ID real del BNC
            $order->paid_amount = $validatedData['Amount']; // Monto total pagado por el cliente
            $order->save();

            // Calcular el monto para el aliado y la comisión de Rumbero Extremo
            $rumberoCommissionAmount = $order->discount_amount ?? 0; // Asumo que el descuento es la comisión
            $amountToPayToAlly = $validatedData['Amount'] - $rumberoCommissionAmount;

            // 5. Registrar el pago pendiente al Aliado
            PartnerPayout::create([
                'order_id' => $order->id,
                'partner_id' => $ally->id, // Usa 'ally_id' si tu columna es así
                'amount' => $amountToPayToAlly,
                'commission_amount' => $rumberoCommissionAmount,
                'status' => 'pending', // Marcar como pendiente para el proceso semi-automático
                'bank_name' => $ally->bank_name,
                'account_number' => $ally->account_number,
                'account_type' => $ally->account_type,
                'id_document' => $ally->id_document,
                'notes' => "Pago C2P de orden #{$order->id}. Comisión RE: {$rumberoCommissionAmount} Bs.",
            ]);

            DB::commit(); // Confirmar todos los cambios en la base de datos

            Log::info('Pago C2P procesado y pago a aliado registrado como pendiente.', ['order_id' => $order->id]);
            return response()->json([
                'message' => 'Pago C2P procesado exitosamente. El pago a su aliado ha sido registrado y se procesará pronto.',
                'data' => [
                    'order_id' => $order->id,
                    'transaction_id' => $order->transaction_id,
                    'amount_paid_by_customer' => $order->paid_amount,
                ]
            ], 200);

        } catch (ValidationException $e) {
            DB::rollBack(); // Revertir si hay error de validación
            Log::warning('Error de validación al iniciar pago C2P.', ['errors' => $e->errors()]);
            return response()->json([
                'message' => 'Datos de entrada inválidos.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack(); // Revertir si hay cualquier otra excepción
            Log::error('Excepción inesperada al iniciar pago C2P: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Ha ocurrido un error inesperado al procesar tu solicitud.',
                'error' => $e->getMessage()
            ], 500);
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

        DB::beginTransaction(); // Iniciar transacción de base de datos
        try {
            // 1. Validar los datos de entrada
            $validatedData = $request->validate([
                'order_id' => 'required|exists:orders,id', // Necesitamos el ID de la orden
                'CardNumber' => 'required|string',
                'dtExpiration' => 'required|integer', // MMYYYY como entero
                'CardHolderName' => 'nullable|string',
                'CVV' => 'required|integer|digits_between:3,4', // CVV como entero
                'Amount' => 'required|numeric|min:0.01', // Monto TOTAL que paga el cliente
                'discount' => 'nullable|integer|min:0|max:100', // Descuento aplicado (si viene en la request)
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

            // 2. Preparar los datos para la API de BNC
            // Mapeamos los nombres de los campos de la request a los que BNC VPOS espera
            $bncCardData = [
                'CardNumber' => $validatedData['CardNumber'],
                'dtExpiration' => $validatedData['dtExpiration'],
                'CardHolderName' => $validatedData['CardHolderName'],
                'CVV' => $validatedData['CVV'],
                'Amount' => $validatedData['Amount'],
                'Terminal' => $validatedData['Terminal'],
                // Agrega aquí cualquier otro campo que BNC VPOS requiera y que no esté en tu request original
                // 'TransactionType' => 'Venta', // Ejemplo
            ];

            // 3. Delegar la llamada a la API del BNC al servicio para procesar el PAGO TOTAL
            $bncResponse = $this->bncApiService->processCardPayment($bncCardData);

            if (is_null($bncResponse) || !isset($bncResponse['success']) || !$bncResponse['success']) {
                // Si la respuesta del BNC indica fallo o es nula/inválida
                Log::error('Fallo al procesar pago con tarjeta con BNC.', ['bnc_response' => $bncResponse]);
                throw new \Exception($bncResponse['message'] ?? 'Fallo al conectar o procesar el pago con tarjeta con el proveedor de pagos.');
            }

            // 4. Pago exitoso a Rumbero Extremo. Actualizar la orden y registrar el pago pendiente al aliado.
            $order->status = 'completed'; // O un estado intermedio como 'paid_to_platform'
            $order->payment_method = 'Card';
            $order->transaction_id = $bncResponse['data']['transactionID'] ?? 'N/A'; // Usa el ID real del BNC
            $order->paid_amount = $validatedData['Amount']; // Monto total pagado por el cliente
            $order->save();

            // Calcular el monto para el aliado y la comisión de Rumbero Extremo
            $rumberoCommissionAmount = $order->discount_amount ?? 0; // Asumo que el descuento es la comisión
            $amountToPayToAlly = $validatedData['Amount'] - $rumberoCommissionAmount;

            // 5. Registrar el pago pendiente al Aliado
            PartnerPayout::create([
                'order_id' => $order->id,
                'partner_id' => $ally->id, // Usa 'ally_id' si tu columna es así
                'amount' => $amountToPayToAlly,
                'commission_amount' => $rumberoCommissionAmount,
                'status' => 'pending', // Marcar como pendiente para el proceso semi-automático
                'bank_name' => $ally->bank_name,
                'account_number' => $ally->account_number,
                'account_type' => $ally->account_type,
                'id_document' => $ally->id_document,
                'notes' => "Pago con tarjeta de orden #{$order->id}. Comisión RE: {$rumberoCommissionAmount} Bs.",
            ]);

            DB::commit(); // Confirmar todos los cambios en la base de datos

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
            DB::rollBack(); // Revertir si hay error de validación
            Log::warning('Error de validación al procesar pago con tarjeta.', ['errors' => $e->errors()]);
            return response()->json([
                'message' => 'Datos de entrada inválidos.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack(); // Revertir si hay cualquier otra excepción
            Log::error('Excepción inesperada al procesar pago con tarjeta: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Ha ocurrido un error inesperado al procesar tu solicitud.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}