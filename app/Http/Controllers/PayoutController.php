<?php

namespace App\Http\Controllers;

use App\Services\PayoutService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class PayoutController extends Controller
{
    protected PayoutService $payoutService;

    public function __construct(PayoutService $payoutService)
    {
        $this->payoutService = $payoutService;
    }

    /**
     * Obtiene todos los payouts con filtros
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $payouts = $this->payoutService->obtenerPagosPorFiltro($request);

            return response()->json([
                'success' => true,
                'data' => $payouts->items(),
                'pagination' => [
                    'total' => $payouts->total(),
                    'current_page' => $payouts->currentPage(),
                    'per_page' => $payouts->perPage(),
                    'last_page' => $payouts->lastPage()
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error obteniendo payouts: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los payouts'
            ], 500);
        }
    }

    /**
     * Obtiene payouts pendientes
     */
    public function pendientes(): JsonResponse
    {
        try {
            $payouts = $this->payoutService->obtenerPagosPendientesCompletos();

            return response()->json([
                'success' => true,
                'data' => $payouts,
                'total' => count($payouts),
                'monto_total' => collect($payouts)->sum('montos.neto')
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error obteniendo payouts pendientes: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener payouts pendientes'
            ], 500);
        }
    }

    /**
     * Genera archivo de pagos BNC
     */
    public function generarArchivoBNC(Request $request): JsonResponse
    {
        try {
            $resultado = $this->payoutService->procesarYGenerarArchivoPagos($request);

            return $resultado;

        } catch (\Exception $e) {
            Log::error('Error generando archivo BNC: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Descarga archivo BNC generado
     */
    public function descargarArchivoBNC($archivo)
    {
        try {
            $archivoData = $this->payoutService->descargarArchivoBNC($archivo);

            return response()->make($archivoData['content'], 200, $archivoData['headers']);

        } catch (\Exception $e) {
            Log::error('Error descargando archivo BNC: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Confirma pagos procesados
     */
    public function confirmarPagos(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'payout_ids' => 'required|array',
                'payout_ids.*' => 'integer|exists:payouts,id',
                'fecha_pago' => 'required|date',
                'referencia_pago' => 'required|string|max:100',
                'archivo_comprobante' => 'nullable|file|mimes:pdf,jpg,png|max:5120',
            ]);

            $result = $this->payoutService->confirmarPagosProcesados(
                $validated['payout_ids'],
                $validated['fecha_pago'],
                $validated['referencia_pago'],
                $request->file('archivo_comprobante')
            );

            return response()->json([
                'success' => true,
                'message' => 'Pagos confirmados exitosamente',
                'data' => $result
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error confirmando pagos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Revierte un pago
     */
    public function revertirPago(Request $request, $payoutId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'motivo' => 'required|string|max:255'
            ]);

            $this->payoutService->revertirPago($payoutId, $validated['motivo']);

            return response()->json([
                'success' => true,
                'message' => 'Pago revertido exitosamente'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error revirtiendo pago: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene estadísticas de payouts
     */
    public function estadisticas(): JsonResponse
    {
        try {
            $estadisticas = $this->payoutService->obtenerEstadisticasCompletas();

            return response()->json([
                'success' => true,
                'data' => $estadisticas
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error obteniendo estadísticas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas'
            ], 500);
        }
    }

    /**
     * Obtiene el historial de un payout específico
     */
    public function show($payoutId): JsonResponse
    {
        try {
            $historial = $this->payoutService->obtenerHistorialPayout($payoutId);

            return response()->json([
                'success' => true,
                'data' => $historial
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error obteniendo historial de payout: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Payout no encontrado'
            ], 404);
        }
    }

    /**
     * Obtiene resumen por aliado
     */
    public function resumenPorAliado(): JsonResponse
    {
        try {
            $resumen = $this->payoutService->obtenerResumenPorAliado();

            return response()->json([
                'success' => true,
                'data' => $resumen
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error obteniendo resumen por aliado: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener resumen'
            ], 500);
        }
    }

    /**
     * Lista archivos generados
     */
    public function listarArchivos(): JsonResponse
    {
        try {
            $archivos = $this->payoutService->obtenerArchivosGenerados();

            return response()->json([
                'success' => true,
                'data' => $archivos
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error listando archivos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al listar archivos'
            ], 500);
        }
    }

    /**
     * Elimina archivo BNC
     */
    public function eliminarArchivo($archivo): JsonResponse
    {
        try {
            $this->payoutService->eliminarArchivoBNC($archivo);

            return response()->json([
                'success' => true,
                'message' => 'Archivo eliminado exitosamente'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error eliminando archivo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Simula confirmación de pagos (solo desarrollo)
     */
    public function simularConfirmacion(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'payout_ids' => 'required|array',
                'payout_ids.*' => 'integer|exists:payouts,id'
            ]);

            if (!app()->environment('local', 'development')) {
                throw new \Exception('Este método solo está disponible en entorno de desarrollo');
            }

            $result = $this->payoutService->simularConfirmacionPagos($validated['payout_ids']);

            return response()->json([
                'success' => true,
                'message' => 'Pagos simulados confirmados',
                'data' => $result
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error simulando confirmación: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
