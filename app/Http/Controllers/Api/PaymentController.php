<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BncApiService; // Importamos el servicio BncApiService
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

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
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function initiateC2PPayment(Request $request): JsonResponse
    {
        Log::info('Solicitud recibida para iniciar pago C2P.', ['request_data' => $request->all()]);

        try {
            // 1. Validar los datos de entrada
            $validatedData = $request->validate([
                'DebtorBankCode' => 'required|string', // Asegúrate que sea un string si BNC lo espera así
                'DebtorCellPhone' => 'required|string|regex:/^[0-9]{11}$/', // Ej: 04121234567
                'DebtorID' => 'required|string|min:6|max:15', // Cédula o RIF (ej: V12345678, J123456789)
                'Amount' => 'required|numeric|min:0.01',
                'Token' => 'required|string|digits:6', // Token de seguridad de 6 dígitos
                'Terminal' => 'required|string|max:50', // Identificador del terminal/aplicación
                // 'ChildClientID' => 'nullable|string', // Si tu implementación lo requiere
                // 'BranchID' => 'nullable|string',      // Si tu implementación lo requiere
            ]);

            // 2. Preparar los datos para la API de BNC
            // Convertimos el código del banco a int si BNC lo requiere como int
            $validatedData['DebtorBankCode'] = (int) $validatedData['DebtorBankCode'];

            // 3. Delegar la llamada a la API del BNC al servicio
            $bncResponse = $this->bncApiService->initiateC2PPayment($validatedData);

            if (is_null($bncResponse)) {
                return response()->json([
                    'message' => 'Fallo al conectar o procesar el pago C2P con el proveedor de pagos.',
                    'error' => 'No se pudo completar la transacción. Por favor, intenta de nuevo.'
                ], 500);
            }

            // 4. Procesar la respuesta del BNC
            // Aquí deberías guardar el estado de la transacción en tu base de datos
            // Por ejemplo: Payment::create([... $bncResponse, 'status' => 'pending' ...]);

            return response()->json([
                'message' => 'Pago C2P iniciado exitosamente.',
                'data' => $bncResponse // Devuelve la respuesta completa del BNC
            ], 200);

        } catch (ValidationException $e) {
            Log::warning('Error de validación al iniciar pago C2P.', ['errors' => $e->errors()]);
            return response()->json([
                'message' => 'Datos de entrada inválidos.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Excepción inesperada al iniciar pago C2P: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Ha ocurrido un error inesperado al procesar tu solicitud.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Procesa un pago con tarjeta (VPOS) con la API del BNC.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function processCardPayment(Request $request): JsonResponse
    {
        Log::info('Solicitud recibida para procesar pago con tarjeta.', ['request_data' => $request->all()]);

        try {
            // 1. Validar los datos de entrada
            $validatedData = $request->validate([
                'card_token' => 'required|string', // Token de la tarjeta (si usas un proveedor de tokenización)
                'amount' => 'required|numeric|min:0.01',
                'description' => 'nullable|string|max:255',
                // Agrega aquí cualquier otro campo que BNC VPOS requiera
                // 'TransactionType' => 'required|string', // Ej: 'Venta'
                // 'CardHolderName' => 'required|string',
                // 'ExpirationDate' => 'required|string|date_format:m/y',
                // 'CVV' => 'required|string|digits:3',
            ]);

            // 2. Preparar los datos para la API de BNC
            // Si necesitas mapear tus campos a los nombres que BNC VPOS espera, hazlo aquí.
            $bncCardData = [
                // 'TransactionType' => $validatedData['TransactionType'],
                // 'CardNumber' => $this->decryptCardNumber($validatedData['card_token']), // Si card_token es número encriptado
                // 'Amount' => $validatedData['amount'],
                // etc.
            ];

            // 3. Delegar la llamada a la API del BNC al servicio
            $bncResponse = $this->bncApiService->processCardPayment($bncCardData);

            if (is_null($bncResponse)) {
                return response()->json([
                    'message' => 'Fallo al conectar o procesar el pago con tarjeta con el proveedor de pagos.',
                    'error' => 'No se pudo completar la transacción. Por favor, intenta de nuevo.'
                ], 500);
            }

            // 4. Procesar la respuesta del BNC
            // Aquí deberías guardar el estado de la transacción en tu base de datos
            // Por ejemplo: Payment::create([... $bncResponse, 'status' => 'approved' ...]);

            return response()->json([
                'message' => 'Pago con tarjeta procesado exitosamente.',
                'data' => $bncResponse
            ], 200);

        } catch (ValidationException $e) {
            Log::warning('Error de validación al procesar pago con tarjeta.', ['errors' => $e->errors()]);
            return response()->json([
                'message' => 'Datos de entrada inválidos.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Excepción inesperada al procesar pago con tarjeta: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Ha ocurrido un error inesperado al procesar tu solicitud.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}