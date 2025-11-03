<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Services\PayoutService; // Asegúrate de importar el servicio

class PayoutController extends Controller
{
    protected PayoutService $payoutService;

    public function __construct(PayoutService $payoutService)
    {
        $this->payoutService = $payoutService;
    }

    /**
     * Obtiene todos los payouts con filtros - RETORNA VISTA
     */
    public function index(Request $request): View
    {
        try {
            $payouts = $this->payoutService->obtenerPagosPorFiltro($request);
            $estadisticas = $this->payoutService->obtenerEstadisticasCompletas();

            return view('Admin.payouts.index', [ // Cambiado a minúsculas
                'payouts' => $payouts->items(),
                'pagination' => [
                    'total' => $payouts->total(),
                    'current_page' => $payouts->currentPage(),
                    'per_page' => $payouts->perPage(),
                    'last_page' => $payouts->lastPage()
                ],
                'estadisticas' => $estadisticas
            ]);

        } catch (\Exception $e) {
            Log::error('Error obteniendo payouts: ' . $e->getMessage());
            return view('Admin.payouts.index') // Cambiado a minúsculas
                ->with('error', 'Error al obtener los payouts');
        }
    }

    /**
     * Obtiene payouts pendientes - RETORNA VISTA
     */
    public function pendientes(): View
    {
        try {
            $payouts = $this->payoutService->obtenerPagosPendientesCompletos();
            $estadisticas = $this->payoutService->obtenerEstadisticasCompletas();

            return view('Admin.payouts.pending', [ // Cambiado a "pendientes" en lugar de "pending"
                'payouts' => $payouts,
                'estadisticas' => $estadisticas,
                'total' => count($payouts),
                'monto_total' => collect($payouts)->sum('montos.neto')
            ]);

        } catch (\Exception $e) {
            Log::error('Error obteniendo payouts pendientes: ' . $e->getMessage());
            return view('Admin.payouts.pending') // Cambiado a "pendientes"
                ->with('error', 'Error al obtener payouts pendientes');
        }
    }

    /**
     * Genera archivo de pagos BNC - MANTIENE JSON (para AJAX)
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
     * Descarga archivo BNC generado - MANTIENE DESCARGAS
     */
    public function descargarArchivoBNC($archivo): BinaryFileResponse
    {
        try {
            $archivoData = $this->payoutService->descargarArchivoBNC($archivo);
            return response()->make($archivoData['content'], 200, $archivoData['headers']);

        } catch (\Exception $e) {
            Log::error('Error descargando archivo BNC: ' . $e->getMessage());
            abort(404, $e->getMessage());
        }
    }

    /**
     * Confirma pagos procesados - REDIRIGE A VISTA
     */
    public function confirmarPagos(Request $request): RedirectResponse
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

            return redirect()->route('admin.payouts.pendientes')
                ->with('success', 'Pagos confirmados exitosamente');

        } catch (\Exception $e) {
            Log::error('Error confirmando pagos: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Revierte un pago - REDIRIGE A VISTA
     */
    public function revertirPago(Request $request, $payoutId): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'motivo' => 'required|string|max:255'
            ]);

            $this->payoutService->revertirPago($payoutId, $validated['motivo']);

            return redirect()->route('admin.payouts.index')
                ->with('success', 'Pago revertido exitosamente');

        } catch (\Exception $e) {
            Log::error('Error revirtiendo pago: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Obtiene estadísticas de payouts - RETORNA VISTA
     */
    public function estadisticas(): View
    {
        try {
            $estadisticas = $this->payoutService->obtenerEstadisticasCompletas();

            return view('Admin.payouts.estadisticas', [ // Cambiado a minúsculas
                'estadisticas' => $estadisticas
            ]);

        } catch (\Exception $e) {
            Log::error('Error obteniendo estadísticas: ' . $e->getMessage());
            return view('Admin.payouts.estadisticas') // Cambiado a minúsculas
                ->with('error', 'Error al obtener estadísticas');
        }
    }

    /**
     * Obtiene el historial de un payout específico - RETORNA VISTA
     */
    public function show($payoutId): View
    {
        try {
            $historial = $this->payoutService->obtenerHistorialPayout($payoutId);

            return view('Admin.payouts.show', [ // Cambiado a minúsculas
                'historial' => $historial
            ]);

        } catch (\Exception $e) {
            Log::error('Error obteniendo historial de payout: ' . $e->getMessage());
            abort(404, 'Payout no encontrado');
        }
    }

    /**
     * Obtiene resumen por aliado - RETORNA VISTA
     */
    public function resumenPorAliado(): View
    {
        try {
            $resumen = $this->payoutService->obtenerResumenPorAliado();

            return view('Admin.payouts.resumen-aliado', [ // Cambiado a minúsculas
                'resumen' => $resumen
            ]);

        } catch (\Exception $e) {
            Log::error('Error obteniendo resumen por aliado: ' . $e->getMessage());
            return view('Admin.payouts.resumen-aliado') // Cambiado a minúsculas
                ->with('error', 'Error al obtener resumen');
        }
    }

    /**
     * Lista archivos generados - RETORNA VISTA
     */
    public function listarArchivos(): View
    {
        try {
            $archivos = $this->payoutService->obtenerArchivosGenerados();

            return view('Admin.payouts.archivos', [ // Cambiado a minúsculas
                'archivos' => $archivos
            ]);

        } catch (\Exception $e) {
            Log::error('Error listando archivos: ' . $e->getMessage());
            return view('Admin.payouts.archivos') // Cambiado a minúsculas
                ->with('error', 'Error al listar archivos');
        }
    }

    /**
     * Elimina archivo BNC - REDIRIGE A VISTA
     */
    public function eliminarArchivo($archivo): RedirectResponse
    {
        try {
            $this->payoutService->eliminarArchivoBNC($archivo);

            return redirect()->route('admin.payouts.archivos')
                ->with('success', 'Archivo eliminado exitosamente');

        } catch (\Exception $e) {
            Log::error('Error eliminando archivo: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Simula confirmación de pagos (solo desarrollo) - REDIRIGE A VISTA
     */
    public function simularConfirmacion(Request $request): RedirectResponse
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

            return redirect()->route('admin.payouts.pendientes')
                ->with('success', 'Pagos simulados confirmados exitosamente');

        } catch (\Exception $e) {
            Log::error('Error simulando confirmación: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Exporta reporte de payouts - RETORNA VISTA O DESCARGA
     */
    public function exportarReporte(Request $request)
    {
        try {
            $validated = $request->validate([
                'formato' => 'required|in:pdf,excel,csv',
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date'
            ]);

            $reporte = $this->payoutService->generarReportePagos(
                $validated['fecha_inicio'],
                $validated['fecha_fin'],
                $validated['formato']
            );

            if ($validated['formato'] === 'pdf') {
                return view('Admin.payouts.reporte-pdf', [ // Cambiado a minúsculas
                    'reporte' => $reporte,
                    'fecha_inicio' => $validated['fecha_inicio'],
                    'fecha_fin' => $validated['fecha_fin']
                ]);
            }

            // Para Excel y CSV, retornar descarga
            return $reporte;

        } catch (\Exception $e) {
            Log::error('Error exportando reporte: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Muestra dashboard de payouts - RETORNA VISTA
     */
    public function dashboard(): View
    {
        try {
            $estadisticas = $this->payoutService->obtenerEstadisticasCompletas();
            $payoutsRecientes = $this->payoutService->obtenerPagosRecientes(10);
            $resumenMensual = $this->payoutService->obtenerResumenMensual();

            return view('Admin.payouts.dashboard', [ // Cambiado a minúsculas
                'estadisticas' => $estadisticas,
                'payoutsRecientes' => $payoutsRecientes,
                'resumenMensual' => $resumenMensual
            ]);

        } catch (\Exception $e) {
            Log::error('Error cargando dashboard: ' . $e->getMessage());
            return view('Admin.payouts.dashboard') // Cambiado a minúsculas
                ->with('error', 'Error al cargar el dashboard');
        }
    }

    /**
     * Obtiene payouts por aliado específico - RETORNA VISTA
     */
    public function porAliado($aliadoId): View
    {
        try {
            $payouts = $this->payoutService->obtenerPagosPorAliado($aliadoId);
            $estadisticasAliado = $this->payoutService->obtenerEstadisticasAliado($aliadoId);

            return view('Admin.payouts.por-aliado', [ // Cambiado a minúsculas
                'payouts' => $payouts,
                'estadisticasAliado' => $estadisticasAliado,
                'aliadoId' => $aliadoId
            ]);

        } catch (\Exception $e) {
            Log::error('Error obteniendo payouts por aliado: ' . $e->getMessage());
            return redirect()->route('admin.payouts.index')
                ->with('error', 'Error al obtener payouts del aliado');
        }
    }

    /**
     * Muestra formulario para editar pago - RETORNA VISTA
     */
    public function edit($payoutId): View
    {
        try {
            $payout = $this->payoutService->obtenerPayoutCompleto($payoutId);

            return view('Admin.payouts.edit', [ // Cambiado a minúsculas
                'payout' => $payout
            ]);

        } catch (\Exception $e) {
            Log::error('Error cargando edición de pago: ' . $e->getMessage());
            return redirect()->route('admin.payouts.index')
                ->with('error', 'Pago no encontrado');
        }
    }

    /**
     * Actualiza pago específico - REDIRIGE A VISTA
     */
    public function update(Request $request, $payoutId): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'monto_comision' => 'required|numeric|min:0',
                'observaciones' => 'nullable|string|max:500',
                'estado' => 'required|in:pending,processed,failed,reverted'
            ]);

            $this->payoutService->actualizarPayout(
                $payoutId,
                $validated['monto_comision'],
                $validated['observaciones'],
                $validated['estado']
            );

            return redirect()->route('admin.payouts.show', $payoutId)
                ->with('success', 'Pago actualizado exitosamente');

        } catch (\Exception $e) {
            Log::error('Error actualizando pago: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Muestra auditoría de cambios - RETORNA VISTA
     */
    public function auditoria($payoutId): View
    {
        try {
            $auditoria = $this->payoutService->obtenerAuditoriaPayout($payoutId);

            return view('Admin.payouts.auditoria', [ // Cambiado a minúsculas
                'auditoria' => $auditoria,
                'payoutId' => $payoutId
            ]);

        } catch (\Exception $e) {
            Log::error('Error obteniendo auditoría: ' . $e->getMessage());
            return redirect()->route('admin.payouts.show', $payoutId)
                ->with('error', 'Error al obtener auditoría del pago');
        }
    }

    /**
     * Procesa lote de pagos - REDIRIGE A VISTA
     */
    public function procesarLote(Request $request): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'lote_id' => 'required|exists:lotes_pagos,id',
                'accion' => 'required|in:confirmar,revertir'
            ]);

            if ($validated['accion'] === 'confirmar') {
                $this->payoutService->confirmarLotePagos($validated['lote_id']);
                $mensaje = 'Lote de pagos confirmado exitosamente';
            } else {
                $this->payoutService->revertirLotePagos($validated['lote_id']);
                $mensaje = 'Lote de pagos revertido exitosamente';
            }

            return redirect()->route('admin.payouts.lotes')
                ->with('success', $mensaje);

        } catch (\Exception $e) {
            Log::error('Error procesando lote: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Muestra lotes de pagos - RETORNA VISTA
     */
    public function lotes(): View
    {
        try {
            $lotes = $this->payoutService->obtenerLotesPagos();

            return view('Admin.payouts.lotes', [ // Cambiado a minúsculas
                'lotes' => $lotes
            ]);

        } catch (\Exception $e) {
            Log::error('Error obteniendo lotes: ' . $e->getMessage());
            return view('Admin.payouts.lotes') // Cambiado a minúsculas
                ->with('error', 'Error al obtener lotes de pagos');
        }
    }

    /**
     * Obtiene datos para gráficos - RETORNA JSON (AJAX)
     */
    public function datosGraficos(Request $request): JsonResponse
    {
        try {
            $datos = $this->payoutService->obtenerDatosParaGraficos($request->all());

            return response()->json([
                'success' => true,
                'data' => $datos
            ]);

        } catch (\Exception $e) {
            Log::error('Error obteniendo datos gráficos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener datos para gráficos'
            ], 500);
        }
    }

    /**
     * Busqueda de payouts - RETORNA JSON (AJAX)
     */
    public function buscar(Request $request): JsonResponse
    {
        try {
            $resultados = $this->payoutService->buscarPayouts($request->get('q'));

            return response()->json([
                'success' => true,
                'data' => $resultados
            ]);

        } catch (\Exception $e) {
            Log::error('Error buscando payouts: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error en la búsqueda'
            ], 500);
        }
    }
}