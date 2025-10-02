<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BncApiService; // Importa el servicio BncApiService
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BankController extends Controller
{
    protected BncApiService $bncApiService;

    public function __construct(BncApiService $bncApiService)
    {
        $this->bncApiService = $bncApiService;
    }

    /**
     * Obtiene y retorna la lista de bancos disponibles.
     */
    public function index(Request $request): JsonResponse
    {
        Log::info('Solicitud recibida para obtener la lista de bancos.');

        try {
            $banks = $this->bncApiService->getBanksFromBnc();

            if (is_null($banks)) {
                Log::error('No se pudo obtener la lista de bancos del BNC. Retornando error 500.');
                return response()->json([
                    'message' => 'No se pudo obtener la lista de bancos en este momento.',
                    'error' => 'Hubo un problema al consultar la lista de bancos. Por favor, intenta de nuevo más tarde.'
                ], 500);
            }

            if ($this->isSimulatedResponse($banks)) {
                Log::warning('Se están retornando bancos simulados debido a problemas de conexión con BNC');
            } else {
                Log::info('Lista de bancos obtenida exitosamente del BNC', [
                    'total_banks' => count($banks),
                    'first_bank' => $banks[0]['name'] ?? 'N/A'
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $banks,
                'metadata' => [
                    'total' => count($banks),
                    'source' => $this->isSimulatedResponse($banks) ? 'simulated' : 'bnc_api',
                    'timestamp' => now()->toISOString()
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error inesperado al obtener bancos: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            return response()->json([
                'message' => 'Error interno del servidor al procesar la solicitud.',
                'error' => 'Por favor, contacte al soporte técnico si el problema persiste.'
            ], 500);
        }
    }

    /**
     * Obtiene la tasa diaria del dólar desde el BNC
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getDailyDollarRate(Request $request): JsonResponse
    {
        Log::info('Solicitud recibida para obtener la tasa diaria del dólar.');

        try {
            // Utiliza el servicio para obtener la tasa del dólar
            $dailyRate = $this->bncApiService->getDailyRateFromBnc();

            // Verifica si la obtención de la tasa fue exitosa
            if (is_null($dailyRate)) {
                Log::error('No se pudo obtener la tasa del dólar del BNC. Retornando error 500.');
                return response()->json([
                    'message' => 'No se pudo obtener la tasa del dólar en este momento.',
                    'error' => 'Hubo un problema al consultar la tasa del dólar. Por favor, intenta de nuevo más tarde.'
                ], 500);
            }

            Log::info('Tasa del dólar obtenida exitosamente del BNC', [
                'tasa_data' => $dailyRate
            ]);

            // Retorna la tasa del dólar como una respuesta JSON
            return response()->json([
                'success' => true,
                'data' => $dailyRate,
                'metadata' => [
                    'source' => 'bnc_api',
                    'timestamp' => now()->toISOString(),
                    'currency' => 'USD',
                    'description' => 'Tasa oficial del dólar Americano'
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error inesperado al obtener la tasa del dólar: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor al obtener la tasa del dólar.',
                'error' => 'Por favor, contacte al soporte técnico si el problema persiste.'
            ], 500);
        }
    }

    /**
     * Verifica si la respuesta contiene datos simulados
     */
    private function isSimulatedResponse(array $banks): bool
    {
        $simulatedPatterns = ['simulated', 'mock', 'demo', 'test'];

        foreach ($banks as $bank) {
            $bankName = strtolower($bank['name'] ?? '');
            foreach ($simulatedPatterns as $pattern) {
                if (str_contains($bankName, $pattern)) {
                    return true;
                }
            }
        }

        return false;
    }
}
