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

    // Inyecta el servicio BncApiService en el constructor.
    // Laravel automáticamente proveerá una instancia.
    public function __construct(BncApiService $bncApiService)
    {
        $this->bncApiService = $bncApiService;
    }

    /**
     * Obtiene y retorna la lista de bancos disponibles.
     * Esta lista se obtiene a través del BncApiService.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        Log::info('Solicitud recibida para obtener la lista de bancos.');

        try {
            // Delega la lógica de obtener los bancos al BncApiService
            $banks = $this->bncApiService->getBanksFromBnc();

            // Verifica si la obtención de bancos fue exitosa
            if (is_null($banks)) {
                Log::error('No se pudo obtener la lista de bancos del BNC. Retornando error 500.');
                return response()->json([
                    'message' => 'No se pudo obtener la lista de bancos en este momento.',
                    'error' => 'Hubo un problema al consultar la lista de bancos. Por favor, intenta de nuevo más tarde.'
                ], 500); // Error interno del servidor
            }

            // Verifica si se están usando bancos simulados (para logging informativo)
            if ($this->isSimulatedResponse($banks)) {
                Log::warning('Se están retornando bancos simulados debido a problemas de conexión con BNC');
            } else {
                Log::info('Lista de bancos obtenida exitosamente del BNC', [
                    'total_banks' => count($banks),
                    'first_bank' => $banks[0]['name'] ?? 'N/A'
                ]);
            }

            // Retorna la lista de bancos como una respuesta JSON
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
     * Verifica si la respuesta contiene datos simulados
     */
    private function isSimulatedResponse(array $banks): bool
    {
        // Verifica patrones comunes de datos simulados
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
