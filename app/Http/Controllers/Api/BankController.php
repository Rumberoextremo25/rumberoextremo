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

        // Retorna la lista de bancos como una respuesta JSON
        return response()->json($banks);
    }
}