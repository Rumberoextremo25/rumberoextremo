<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\UserC2PDetail;
use Illuminate\Support\Facades\Log;

class PaymentSettingsController extends Controller
{
    /**
     * Guarda o actualiza los detalles de Pago Móvil (C2P) para el usuario autenticado.
     * POST /api/user/c2p-settings
     */
    public function saveC2PDetails(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['status' => 'ERROR', 'message' => 'No autorizado. Por favor, inicie sesión.'], 401);
        }

        // 2. Validación de Datos del Lado del Servidor ✅
        $validator = Validator::make($request->all(), [
            'userId' => 'required|string|size:' . strlen($user->id),
            'phoneNumber' => ['required', 'string', 'regex:/^\d{11}$/'],
            'idCard' => ['required', 'string', 'regex:/^[VE]-\d{7,9}$/i'],
            'bankCode' => 'required|string|size:4|in:0191',
            'accountType' => 'required|string|in:CELE,CCOR,CAHO',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'ERROR', 'message' => 'Datos de Pago Móvil inválidos.', 'errors' => $validator->errors()], 400);
        }

        // Asegurarse que el userId enviado por el cliente coincide con el autenticado
        if ($user->id != $request->input('userId')) {
            return response()->json(['status' => 'ERROR', 'message' => 'Acceso denegado: Intento de modificar datos de otro usuario.'], 403);
        }

        // 3. Obtener Datos del Request
        $phoneNumber = $request->input('phoneNumber');
        $idCard = $request->input('idCard');
        $bankCode = $request->input('bankCode');
        $accountType = strtoupper($request->input('accountType')); // Asegurar mayúsculas

        try {
            UserC2PDetail::updateOrCreate(
                ['user_id' => $user->id], // Criterio de búsqueda: busca por user_id
                [
                    'phone_number' => $phoneNumber, // Asigna al 'setter' virtual
                    'id_card' => $idCard,           // Asigna al 'setter' virtual
                    'bank_code' => $bankCode,
                    'account_type' => $accountType,
                ]
            );

            // 5. Responder a la Aplicación Cliente
            return response()->json(['status' => 'OK', 'message' => 'Configuración de Pago Móvil guardada exitosamente.']);

        } catch (\Exception $e) {
            Log::error("Error en saveC2PDetails: " . $e->getMessage(), ['user_id' => $user->id, 'exception' => $e]);
            return response()->json(['status' => 'ERROR', 'message' => 'Error interno del servidor al guardar los detalles de pago.'], 500);
        }
    }

    public function getC2PDetails(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['status' => 'ERROR', 'message' => 'No autorizado. Por favor, inicie sesión.'], 401);
        }

        try {
            // Obtener detalles del usuario usando Eloquent
            $userC2PDetails = UserC2PDetail::where('user_id', $user->id)->first();

            if (!$userC2PDetails) {
                return response()->json(['status' => 'NOT_FOUND', 'message' => 'No se encontraron detalles de Pago Móvil para este usuario.'], 404);
            }

            // Los accesores del modelo se encargarán de la desencriptación automáticamente
            return response()->json([
                'status' => 'OK',
                'message' => 'Detalles de Pago Móvil obtenidos exitosamente.',
                'data' => [
                    'phoneNumber' => $userC2PDetails->phone_number, // Accede al 'getter' virtual
                    'idCard' => $userC2PDetails->id_card,           // Accede al 'getter' virtual
                    'bankCode' => $userC2PDetails->bank_code,
                    'accountType' => $userC2PDetails->account_type,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("Error en getC2PDetails: " . $e->getMessage(), ['user_id' => $user->id, 'exception' => $e]);
            return response()->json(['status' => 'ERROR', 'message' => 'Error interno del servidor al obtener los detalles de pago.'], 500);
        }
    }
}