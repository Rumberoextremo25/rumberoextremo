<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Todavía se necesita para el tipo, pero su retorno será null
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log; // Importar la clase Log
use App\Models\UserC2PDetail; // Importa tu modelo

class PaymentSettingsController extends Controller
{
    /**
     * Guarda o actualiza los detalles de Pago Móvil (C2P).
     * POST /api/user/c2p-settings
     *
     * NOTA: Este método asume que el 'userId' se envía en el cuerpo de la solicitud
     * cuando se usa sin middleware de autenticación. ¡NO RECOMENDADO PARA PRODUCCIÓN!
     */
    public function saveC2PDetails(Request $request)
    {
        // Si no hay autenticación, obtenemos el userId directamente del request.
        // En un entorno de producción, este userId vendría de Auth::user()->id.
        $userIdFromRequest = $request->input('userId');

        // Puedes añadir una validación básica aquí para asegurar que userId existe si es obligatorio
        if (empty($userIdFromRequest)) {
            return response()->json(['status' => 'ERROR', 'message' => 'El userId es requerido en el cuerpo de la solicitud.'], 400);
        }

        // 2. Validación de Datos del Lado del Servidor ✅
        $validator = Validator::make($request->all(), [
            // 'userId' ya no se valida con strlen($user->id) porque $user puede ser null
            'userId' => 'required|string', // Solo verificamos que venga como string
            'phoneNumber' => ['required', 'string', 'regex:/^\d{11}$/'],
            'idCard' => ['required', 'string', 'regex:/^[VE]-\d{7,9}$/i'],
            'bankCode' => 'required|string|size:4|in:0191', // Solo BNC
            'accountType' => 'required|string|in:CELE,CCOR,CAHO', // Tipos de cuenta permitidos
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'ERROR', 'message' => 'Datos de Pago Móvil inválidos.', 'errors' => $validator->errors()], 400);
        }

        // Ya no podemos usar $user->id para validar si el userId enviado coincide,
        // porque $user puede ser null. Esta validación es clave para la seguridad
        // y DEBE ser reintroducida con un sistema de autenticación robusto.
        // Para pruebas, simplemente asumimos que el userId del request es el que queremos.
        // Si quisieras una validación básica sin auth, podrías verificar si el userId existe en tu tabla de 'users'.
        /*
        // Ejemplo de validación SI TUVIERAS ACCESO A UN MODELO User Y QUISIERAS VERIFICAR SU EXISTENCIA
        $userExists = \App\Models\User::find($userIdFromRequest);
        if (!$userExists) {
            return response()->json(['status' => 'ERROR', 'message' => 'Usuario no encontrado.'], 404);
        }
        */

        // 3. Obtener Datos del Request
        $phoneNumber = $request->input('phoneNumber');
        $idCard = $request->input('idCard');
        $bankCode = $request->input('bankCode');
        $accountType = strtoupper($request->input('accountType')); // Asegurar mayúsculas

        try {
            // 4. Guardar/Actualizar en MySQL usando el Modelo Eloquent 💾
            // Usamos $userIdFromRequest para la operación de la base de datos
            UserC2PDetail::updateOrCreate(
                ['user_id' => $userIdFromRequest], // Criterio de búsqueda: usa el userId del request
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
            // Se usa $userIdFromRequest porque $user podría ser null
            Log::error("Error en saveC2PDetails (sin Auth): " . $e->getMessage(), ['user_id_from_request' => $userIdFromRequest, 'exception' => $e]);
            return response()->json(['status' => 'ERROR', 'message' => 'Error interno del servidor al guardar los detalles de pago.'], 500);
        }
    }

    /**
     * Obtiene los detalles de Pago Móvil (C2P).
     * GET /api/user/c2p-settings
     *
     * NOTA: Este método asume que el 'userId' se envía como parámetro de consulta o en el cuerpo de la solicitud
     * cuando se usa sin middleware de autenticación. ¡NO RECOMENDADO PARA PRODUCCIÓN!
     */
    public function getC2PDetails(Request $request)
    {
        // Si no hay autenticación, obtenemos el userId de la URL (query parameter) o del cuerpo
        $userIdFromRequest = $request->input('userId'); // O $request->query('userId') si esperas un query param

        if (empty($userIdFromRequest)) {
            return response()->json(['status' => 'ERROR', 'message' => 'El userId es requerido para obtener los detalles.'], 400);
        }

        try {
            // Obtener detalles del usuario usando Eloquent
            // Usamos $userIdFromRequest para la consulta
            $userC2PDetails = UserC2PDetail::where('user_id', $userIdFromRequest)->first();

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
            // Se usa $userIdFromRequest porque $user podría ser null
            Log::error("Error en getC2PDetails (sin Auth): " . $e->getMessage(), ['user_id_from_request' => $userIdFromRequest, 'exception' => $e]);
            return response()->json(['status' => 'ERROR', 'message' => 'Error interno del servidor al obtener los detalles de pago.'], 500);
        }
    }
}