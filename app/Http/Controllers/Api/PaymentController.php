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

        DB::beginTransaction(); // Inicia una transacción de base de datos para asegurar la atomicidad
        try {
            // 1. Validar los datos de entrada de la solicitud
            $validatedData = $request->validate([
                'order_id' => 'required|exists:orders,id', // Se valida que la orden exista
                'DebtorBankCode' => 'required|string', // Se valida como string, el casting a int se hace antes de enviar a BNC
                'DebtorCellPhone' => 'required|string|regex:/^[0-9]{11}$/',
                'DebtorID' => 'required|string|min:6|max:15',
                'Amount' => 'required|numeric|min:0.01',
                'Token' => 'required|string|digits:6',
                'Terminal' => 'required|string|max:50',
            ]);

            // Cargar la orden y su aliado asociado, incluyendo el manejo de errores si no se encuentran
            $order = Order::with('ally')->find($validatedData['order_id']);
            if (!$order) {
                throw new \Exception('Orden no encontrada para el pago C2P.');
            }
            $ally = $order->ally;
            if (!$ally) {
                throw new \Exception('Aliado no asociado a la orden para el pago C2P.');
            }

            // 2. Preparar los datos para la API de BNC
            // Se asegura que DebtorBankCode sea un entero si la API del BNC lo espera así.
            // Si la API del BNC espera un string numérico, se puede eliminar el casting.
            $validatedData['DebtorBankCode'] = (int) $validatedData['DebtorBankCode'];

            // 3. Delegar la llamada a la API del BNC al servicio
            $bncResponse = $this->bncApiService->initiateC2PPayment($validatedData);

            // 4. Validar la respuesta del BNC
            // Se verifica que la respuesta no sea nula y que el 'status' sea 'OK'.
            if (is_null($bncResponse) || !isset($bncResponse['status']) || $bncResponse['status'] !== 'OK') {
                $errorMessage = $bncResponse['message'] ?? 'Fallo al conectar o procesar el pago C2P con el proveedor de pagos.';
                Log::error('Fallo al procesar pago C2P con BNC. La API devolvió un estado no OK.', [
                    'bnc_response' => $bncResponse,
                    'error_message' => $errorMessage
                ]);
                throw new \Exception($errorMessage);
            }

            // 5. Pago exitoso con el BNC: Actualizar la orden y registrar el pago pendiente al aliado
            $order->status = 'completed'; // O el estado final que corresponda
            $order->payment_method = 'C2P';
            // Se usa el campo 'value' de la respuesta del BNC como ID de transacción si es aplicable.
            // Si el BNC tiene otro campo para el Transaction ID, ajusta aquí.
            $order->transaction_id = $bncResponse['value'] ?? 'N/A';
            $order->paid_amount = $validatedData['Amount'];
            $order->save();

            // Calcular el monto para el aliado y la comisión de la plataforma
            $rumberoCommissionAmount = $order->discount_amount ?? 0; // Asunción: el descuento es la comisión. Ajusta si tu lógica es diferente.
            $amountToPayToAlly = $validatedData['Amount'] - $rumberoCommissionAmount;

            // 6. Registrar el pago pendiente al aliado en la base de datos
            PartnerPayout::create([
                'order_id' => $order->id,
                'partner_id' => $ally->id,
                'amount' => $amountToPayToAlly,
                'commission_amount' => $rumberoCommissionAmount,
                'status' => 'pending', // Indica que el pago al aliado está pendiente
                'bank_name' => $ally->bank_name,
                'account_number' => $ally->account_number,
                'account_type' => $ally->account_type,
                'id_document' => $ally->id_document,
                'notes' => "Pago C2P de orden #{$order->id}. Comisión de Rumbero Extremo: {$rumberoCommissionAmount} Bs.",
            ]);

            DB::commit(); // Confirma todas las operaciones de la base de datos

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
            DB::rollBack(); // Revierte los cambios si falla la validación
            Log::warning('Error de validación al iniciar pago C2P.', ['errors' => $e->errors()]);
            return response()->json([
                'message' => 'Datos de entrada inválidos.',
                'errors' => $e->errors()
            ], 422); // Código de estado 422 para errores de validación
        } catch (\Exception $e) {
            DB::rollBack(); // Revierte los cambios si ocurre cualquier otra excepción
            Log::error('Excepción inesperada al iniciar pago C2P: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Ha ocurrido un error inesperado al procesar tu solicitud.',
                'error' => $e->getMessage()
            ], 500); // Código de estado 500 para errores internos del servidor
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
            // IMPORTANTE: Idealmente, los datos sensibles de la tarjeta (CardNumber, CVV, dtExpiration)
            // NO deberían ser manejados directamente por tu backend por razones de seguridad (PCI DSS).
            // En su lugar, usa un formulario de tokenización provisto por BNC/pasarela en el frontend,
            // que envíe estos datos directamente a ellos y te devuelva un token para usar aquí.
            $validatedData = $request->validate([
                'order_id' => 'required|exists:orders,id',
                'CardNumber' => 'required|string', // Considera validar formato de tarjeta (Luhn) si lo procesas directamente
                'dtExpiration' => 'required|string|regex:/^(0[1-9]|1[0-2])\/?([0-9]{4}|[0-9]{2})$/', // MM/YY o MM/YYYY
                'CardHolderName' => 'nullable|string|max:255',
                'CVV' => 'required|string|digits_between:3,4', // CVV como string para evitar problemas con ceros iniciales
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
            // Si dtExpiration viene como MM/YY, ajústalo a MMYYYY si BNC lo espera así.
            // Ejemplo: '12/25' -> '122025'
            $expiration = str_replace('/', '', $validatedData['dtExpiration']); // Elimina la barra si existe
            if (strlen($expiration) === 4) { // Si es MMYY, asume 20YY
                $expiration = substr($expiration, 0, 2) . '20' . substr($expiration, 2, 2);
            }

            $bncCardData = [
                'CardNumber' => $validatedData['CardNumber'],
                'dtExpiration' => (int) $expiration, // Convierte a entero después del formato
                'CardHolderName' => $validatedData['CardHolderName'],
                'CVV' => (int) $validatedData['CVV'], // Convierte a entero si BNC lo espera así
                'Amount' => $validatedData['Amount'],
                'Terminal' => $validatedData['Terminal'],
                // Agrega aquí cualquier otro campo que BNC VPOS requiera (ej. 'TransactionType', 'Currency')
                // 'TransactionType' => 'VENTA',
                // 'Currency' => 'VES',
            ];

            // 3. Delegar la llamada a la API del BNC al servicio para procesar el PAGO TOTAL
            $bncResponse = $this->bncApiService->processCardPayment($bncCardData);

            // 4. Validar la respuesta del BNC
            // Se verifica que la respuesta no sea nula y que el 'status' sea 'OK'.
            if (is_null($bncResponse) || !isset($bncResponse['status']) || $bncResponse['status'] !== 'OK') {
                $errorMessage = $bncResponse['message'] ?? 'Fallo al conectar o procesar el pago con tarjeta con el proveedor de pagos.';
                Log::error('Fallo al procesar pago con tarjeta con BNC. La API devolvió un estado no OK.', [
                    'bnc_response' => $bncResponse,
                    'error_message' => $errorMessage
                ]);
                throw new \Exception($errorMessage);
            }

            // 5. Pago exitoso con el BNC: Actualizar la orden y registrar el pago pendiente al aliado
            $order->status = 'completed'; // O el estado final que corresponda
            $order->payment_method = 'Card';
            // Se usa el campo 'value' de la respuesta del BNC como ID de transacción si es aplicable.
            // Si el BNC tiene otro campo para el Transaction ID, ajusta aquí.
            $order->transaction_id = $bncResponse['value'] ?? 'N/A';
            $order->paid_amount = $validatedData['Amount'];
            $order->save();

            // Calcular el monto para el aliado y la comisión de la plataforma
            $rumberoCommissionAmount = $order->discount_amount ?? 0; // Asunción: el descuento es la comisión. Ajusta si tu lógica es diferente.
            $amountToPayToAlly = $validatedData['Amount'] - $rumberoCommissionAmount;

            // 6. Registrar el pago pendiente al aliado en la base de datos
            PartnerPayout::create([
                'order_id' => $order->id,
                'partner_id' => $ally->id,
                'amount' => $amountToPayToAlly,
                'commission_amount' => $rumberoCommissionAmount,
                'status' => 'pending', // Indica que el pago al aliado está pendiente
                'bank_name' => $ally->bank_name,
                'account_number' => $ally->account_number,
                'account_type' => $ally->account_type,
                'id_document' => $ally->id_document,
                'notes' => "Pago con tarjeta de orden #{$order->id}. Comisión de Rumbero Extremo: {$rumberoCommissionAmount} Bs.",
            ]);

            DB::commit(); // Confirma todas las operaciones de la base de datos

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
            DB::rollBack(); // Revierte los cambios si falla la validación
            Log::warning('Error de validación al procesar pago con tarjeta.', ['errors' => $e->errors()]);
            return response()->json([
                'message' => 'Datos de entrada inválidos.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack(); // Revierte los cambios si ocurre cualquier otra excepción
            Log::error('Excepción inesperada al procesar pago con tarjeta: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Ha ocurrido un error inesperado al procesar tu solicitud.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}