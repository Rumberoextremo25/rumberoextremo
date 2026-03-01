<?php

namespace App\Http\Controllers;

use App\Services\PayoutService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
    public function index(Request $request): View|RedirectResponse
    {
        try {
            $payoutsData = $this->payoutService->obtenerPagosPorFiltro($request);
            $estadisticas = $this->payoutService->obtenerEstadisticasCompletas();

            // Obtener lista de aliados para el filtro
            $aliados = $this->payoutService->obtenerResumenPorAliado();

            // Asegurar que $payouts sea un array y que cada elemento tenga los datos necesarios
            $payoutsArray = [];
            foreach ($payoutsData->items() as $item) {
                // Si es un objeto Eloquent o similar, convertirlo a array
                if (is_object($item) && method_exists($item, 'toArray')) {
                    $payoutsArray[] = $item->toArray();
                } elseif (is_array($item)) {
                    $payoutsArray[] = $item;
                }
            }

            return view('Admin.payouts.index', [
                'payouts' => $payoutsArray,
                'pagination' => [
                    'total' => $payoutsData->total(),
                    'current_page' => $payoutsData->currentPage(),
                    'per_page' => $payoutsData->perPage(),
                    'last_page' => $payoutsData->lastPage()
                ],
                'estadisticas' => $estadisticas,
                'aliados' => $aliados
            ]);
        } catch (\Exception $e) {
            Log::error('Error obteniendo payouts: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error al obtener los payouts: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene payouts pendientes - RETORNA VISTA
     */
    public function pendientes(): View|RedirectResponse
    {
        try {
            $payouts = $this->payoutService->obtenerPagosPendientesCompletos();
            $estadisticas = $this->payoutService->obtenerEstadisticasCompletas();

            $monto_total = 0;
            foreach ($payouts as $payout) {
                if (is_array($payout) && isset($payout['montos']['neto'])) {
                    $monto_total += $payout['montos']['neto'];
                }
            }

            return view('Admin.payouts.pendientes', [
                'payouts' => $payouts,
                'estadisticas' => $estadisticas,
                'total' => count($payouts),
                'monto_total' => $monto_total
            ]);
        } catch (\Exception $e) {
            Log::error('Error obteniendo payouts pendientes: ' . $e->getMessage());
            return redirect()->route('admin.payouts.index')
                ->with('error', 'Error al obtener payouts pendientes: ' . $e->getMessage());
        }
    }

    /**
     * Genera archivo de pagos BNC - PROCESA Y REDIRIGE
     */
    public function generarArchivoBNC(Request $request): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
                'tipo_cuenta' => 'required|in:corriente,ahorro',
                'concepto' => 'nullable|string|max:60',
            ]);

            $resultado = $this->payoutService->generarArchivoPagosBNC(
                $validated['fecha_inicio'],
                $validated['fecha_fin'],
                $validated['tipo_cuenta'],
                $validated['concepto'] ?? null
            );

            return redirect()->route('admin.payouts.archivos')
                ->with('success', 'Archivo BNC generado exitosamente: ' . $resultado['archivo']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Error generando archivo BNC: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error al generar archivo BNC: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Procesa y genera archivo de pagos (versión JSON para API)
     */
    public function procesarYGenerarArchivoPagos(Request $request): JsonResponse
    {
        try {
            $resultado = $this->payoutService->procesarYGenerarArchivoPagos($request);
            return $resultado;
        } catch (\Exception $e) {
            Log::error('Error procesando archivo de pagos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error procesando archivo de pagos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Descarga archivo BNC generado - DESCARGA ARCHIVO
     */
    public function descargarArchivoBNC(string $archivo): BinaryFileResponse
    {
        try {
            $archivoDecodificado = urldecode($archivo);

            $archivoData = $this->payoutService->descargarArchivoBNC($archivoDecodificado);

            return response()->download(
                $archivoData['ruta'],
                $archivoData['nombre'],
                $archivoData['headers']
            );
        } catch (\Exception $e) {
            Log::error('Error descargando archivo BNC: ' . $e->getMessage());
            abort(404, 'Archivo no encontrado: ' . $e->getMessage());
        }
    }

    /**
     * Confirma pagos procesados - REDIRIGE A VISTA
     */
    public function confirmarPagos(Request $request): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'payout_ids' => 'required|string',
                'fecha_pago' => 'required|date',
                'referencia_pago' => 'required|string|max:100',
                'archivo_comprobante' => 'nullable|file|mimes:pdf,jpg,png|max:5120',
            ]);

            $payoutIds = json_decode($validated['payout_ids'], true);

            if (!is_array($payoutIds) || empty($payoutIds)) {
                throw new \Exception('IDs de payout inválidos');
            }

            $result = $this->payoutService->confirmarPagosProcesados(
                $payoutIds,
                $validated['fecha_pago'],
                $validated['referencia_pago'],
                $request->file('archivo_comprobante')
            );

            return redirect()->route('admin.payouts.pendientes')
                ->with('success', count($payoutIds) . ' pagos confirmados exitosamente. Referencia: ' . $validated['referencia_pago']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
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
    public function revertirPago(Request $request, int $payoutId): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'motivo' => 'required|string|max:255'
            ]);

            $this->payoutService->revertirPago($payoutId, $validated['motivo']);

            return redirect()->route('admin.payouts.index')
                ->with('success', 'Pago #' . $payoutId . ' revertido exitosamente');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Error revirtiendo pago: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Obtiene estadísticas de payouts - RETORNA VISTA
     */
    public function estadisticas(): View|RedirectResponse
    {
        try {
            $estadisticas = $this->payoutService->obtenerEstadisticasCompletas();

            // Obtener top aliados
            $topAliados = $this->payoutService->obtenerResumenPorAliado();

            // Ordenar por monto y tomar los primeros 10
            usort($topAliados, function ($a, $b) {
                return $b['total_monto'] <=> $a['total_monto'];
            });
            $topAliados = array_slice($topAliados, 0, 10);

            return view('Admin.payouts.estadisticas', [
                'estadisticas' => $estadisticas,
                'topAliados' => $topAliados,
                'fechaActualizacion' => now()->format('d/m/Y H:i:s')
            ]);
        } catch (\Exception $e) {
            Log::error('Error obteniendo estadísticas: ' . $e->getMessage());
            return redirect()->route('admin.payouts.index')
                ->with('error', 'Error al obtener estadísticas: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene el historial de un payout específico - RETORNA VISTA
     */
    public function show(int $payoutId): View|RedirectResponse
    {
        try {
            $historial = $this->payoutService->obtenerHistorialPayout($payoutId);

            return view('Admin.payouts.show', [
                'historial' => $historial,
                'payout' => $historial['payout'] ?? null
            ]);
        } catch (\Exception $e) {
            Log::error('Error obteniendo historial de payout: ' . $e->getMessage());
            return redirect()->route('admin.payouts.index')
                ->with('error', 'Payout no encontrado');
        }
    }

    /**
     * Obtiene resumen por aliado - RETORNA VISTA
     */
    public function resumenPorAliado(): View|RedirectResponse
    {
        try {
            $resumen = $this->payoutService->obtenerResumenPorAliado();

            return view('Admin.payouts.resumen-aliado', [
                'resumen' => $resumen
            ]);
        } catch (\Exception $e) {
            Log::error('Error obteniendo resumen por aliado: ' . $e->getMessage());
            return redirect()->route('admin.payouts.index')
                ->with('error', 'Error al obtener resumen por aliado');
        }
    }

    public function detalleAliadoJson(int $aliadoId): JsonResponse
    {
        try {
            // Obtener datos del aliado
            $resumen = $this->payoutService->obtenerResumenPorAliado();
            $aliadoData = collect($resumen)->firstWhere('aliado_id', $aliadoId);

            if (!$aliadoData) {
                return response()->json(['error' => 'Aliado no encontrado'], 404);
            }

            // Obtener pagos recientes
            $request = new Request();
            $request->merge(['ally_id' => $aliadoId, 'per_page' => 5]);
            $pagosRecientes = $this->payoutService->obtenerPagosPorFiltro($request);

            // Preparar datos para gráficas
            $evolucionMensual = $this->payoutService->obtenerEvolucionMensualAliado($aliadoId, 6);

            return response()->json([
                'aliado_id' => $aliadoId,
                'aliado_nombre' => $aliadoData['aliado_nombre'],
                'total_pagos' => $aliadoData['total_payouts'],
                'monto_total' => $aliadoData['total_monto'],
                'estados' => $aliadoData['estados'],
                'ultimos_pagos' => $pagosRecientes->items(),
                'evolucion_mensual' => $evolucionMensual,
                'estados_grafica' => [
                    'labels' => ['Pendientes', 'Procesando', 'Completados', 'Revertidos'],
                    'data' => [
                        $aliadoData['estados']['pending'] ?? 0,
                        $aliadoData['estados']['processing'] ?? 0,
                        $aliadoData['estados']['completed'] ?? 0,
                        $aliadoData['estados']['reverted'] ?? 0
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error obteniendo detalle de aliado: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar detalles'], 500);
        }
    }

    /**
     * Lista archivos generados - RETORNA VISTA
     */
    public function listarArchivos(): View|RedirectResponse
    {
        try {
            $archivos = $this->payoutService->obtenerArchivosGenerados();

            return view('Admin.payouts.archivos', [
                'archivos' => $archivos
            ]);
        } catch (\Exception $e) {
            Log::error('Error listando archivos: ' . $e->getMessage());
            return redirect()->route('admin.payouts.index')
                ->with('error', 'Error al listar archivos');
        }
    }

    /**
     * Elimina archivo BNC - REDIRIGE A VISTA
     */
    public function eliminarArchivo(string $archivo): RedirectResponse
    {
        try {
            $archivoDecodificado = urldecode($archivo);

            $this->payoutService->eliminarArchivoBNC($archivoDecodificado);

            return redirect()->route('admin.payouts.archivos')
                ->with('success', 'Archivo "' . $archivoDecodificado . '" eliminado exitosamente');
        } catch (\Exception $e) {
            Log::error('Error eliminando archivo: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Muestra formulario para confirmar pago individual - RETORNA VISTA
     */
    public function confirmarIndividualForm(int $payoutId): View|RedirectResponse
    {
        try {
            $payout = $this->payoutService->obtenerPayoutCompleto($payoutId);

            // Verificar que el payout esté en estado processing
            if ($payout->status !== 'processing') {
                return redirect()->route('admin.payouts.show', $payoutId)
                    ->with('error', 'Este pago no está en estado de procesamiento');
            }

            return view('Admin.payouts.confirmar-individual', [
                'payout' => $payout
            ]);
        } catch (\Exception $e) {
            Log::error('Error cargando formulario de confirmación: ' . $e->getMessage());
            return redirect()->route('admin.payouts.index')
                ->with('error', 'Pago no encontrado');
        }
    }

    /**
     * Confirma un pago individual - REDIRIGE A VISTA
     */
    public function confirmarPagoIndividual(Request $request, int $payoutId): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'fecha_pago' => 'required|date',
                'referencia_pago' => 'required|string|max:100',
                'archivo_comprobante' => 'required|file|mimes:pdf,jpg,png|max:5120',
            ]);

            $result = $this->payoutService->confirmarPagosProcesados(
                [$payoutId],
                $validated['fecha_pago'],
                $validated['referencia_pago'],
                $request->file('archivo_comprobante')
            );

            return redirect()->route('admin.payouts.show', $payoutId)
                ->with('success', 'Pago #' . $payoutId . ' confirmado exitosamente. Referencia: ' . $validated['referencia_pago']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Error confirmando pago individual: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Simula confirmación de pagos (solo desarrollo) - REDIRIGE A VISTA
     */
    public function simularConfirmacion(Request $request): RedirectResponse
    {
        try {
            if (!app()->environment('local', 'development')) {
                throw new \Exception('Este método solo está disponible en entorno de desarrollo');
            }

            $validated = $request->validate([
                'payout_ids' => 'required|string',
            ]);

            $payoutIds = json_decode($validated['payout_ids'], true);

            if (!is_array($payoutIds) || empty($payoutIds)) {
                throw new \Exception('IDs de payout inválidos');
            }

            $result = $this->payoutService->simularConfirmacionPagos($payoutIds);

            return redirect()->route('admin.payouts.pendientes')
                ->with('success', count($payoutIds) . ' pagos simulados confirmados exitosamente');
        } catch (\Exception $e) {
            Log::error('Error simulando confirmación: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Muestra dashboard de payouts - RETORNA VISTA
     */
    public function dashboard(): View|RedirectResponse
    {
        try {
            $estadisticas = $this->payoutService->obtenerEstadisticasCompletas();

            // Obtener pagos recientes (últimos 10)
            $request = new Request();
            $request->merge(['per_page' => 10, 'sort_by' => 'created_at', 'sort_order' => 'desc']);
            $pagosRecientes = $this->payoutService->obtenerPagosPorFiltro($request);

            // Obtener top aliados
            $topAliados = $this->payoutService->obtenerResumenPorAliado();
            usort($topAliados, function ($a, $b) {
                return $b['total_monto'] <=> $a['total_monto'];
            });
            $topAliados = array_slice($topAliados, 0, 5);

            return view('Admin.payouts.dashboard', [
                'estadisticas' => $estadisticas,
                'pagosRecientes' => $pagosRecientes->items(),
                'topAliados' => $topAliados,
                'fechaActualizacion' => now()->format('d/m/Y H:i:s')
            ]);
        } catch (\Exception $e) {
            Log::error('Error cargando dashboard: ' . $e->getMessage());
            return redirect()->route('admin.payouts.index')
                ->with('error', 'Error al cargar el dashboard: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene payouts por aliado específico - RETORNA VISTA
     */
    public function porAliado(int $aliadoId): View|RedirectResponse
    {
        try {
            $request = new Request();
            $request->merge(['ally_id' => $aliadoId]);

            $payouts = $this->payoutService->obtenerPagosPorFiltro($request);

            // Obtener estadísticas específicas del aliado
            $resumen = $this->payoutService->obtenerResumenPorAliado();
            $estadisticasAliado = collect($resumen)->firstWhere('aliado_id', $aliadoId);

            return view('Admin.payouts.por-aliado', [
                'payouts' => $payouts->items(),
                'pagination' => [
                    'total' => $payouts->total(),
                    'current_page' => $payouts->currentPage(),
                    'per_page' => $payouts->perPage(),
                    'last_page' => $payouts->lastPage()
                ],
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
    public function edit(int $payoutId): View|RedirectResponse
    {
        try {
            $historial = $this->payoutService->obtenerHistorialPayout($payoutId);
            $payout = $historial['payout'];

            return view('Admin.payouts.edit', [
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
    public function update(Request $request, int $payoutId): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'commission_amount' => 'required|numeric|min:0',
                'notes' => 'nullable|string|max:500',
                'status' => 'required|in:pending,processing,completed,reverted'
            ]);

            // Nota: Este método necesitarías implementarlo en el servicio
            // $this->payoutService->actualizarPayout($payoutId, $validated);

            return redirect()->route('admin.payouts.show', $payoutId)
                ->with('success', 'Pago actualizado exitosamente');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
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
    public function auditoria(int $payoutId): View|RedirectResponse
    {
        try {
            $historial = $this->payoutService->obtenerHistorialPayout($payoutId);

            return view('Admin.payouts.auditoria', [
                'historial' => $historial,
                'payoutId' => $payoutId
            ]);
        } catch (\Exception $e) {
            Log::error('Error obteniendo auditoría: ' . $e->getMessage());
            return redirect()->route('admin.payouts.show', $payoutId)
                ->with('error', 'Error al obtener auditoría del pago');
        }
    }

    /**
     * Muestra lotes de pagos - RETORNA VISTA
     */
    public function lotes(): View|RedirectResponse
    {
        try {
            // Obtener archivos generados como "lotes"
            $archivos = $this->payoutService->obtenerArchivosGenerados();

            // Transformar para mostrar como lotes
            $lotes = collect($archivos)->map(function ($archivo) {
                return [
                    'id' => md5($archivo['nombre']),
                    'nombre' => $archivo['nombre'],
                    'fecha' => $archivo['fecha_modificacion'],
                    'tamaño' => $archivo['tamaño'],
                    'cantidad_pagos' => 0, // Esto deberías obtenerlo de alguna metadata
                    'monto_total' => 0, // Esto deberías obtenerlo de alguna metadata
                    'estado' => 'completado'
                ];
            })->toArray();

            return view('Admin.payouts.lotes', [
                'lotes' => $lotes
            ]);
        } catch (\Exception $e) {
            Log::error('Error obteniendo lotes: ' . $e->getMessage());
            return redirect()->route('admin.payouts.index')
                ->with('error', 'Error al obtener lotes de pagos');
        }
    }

    /**
     * Procesa lote de pagos - REDIRIGE A VISTA
     */
    public function procesarLote(Request $request): RedirectResponse
    {
        try {
            $validated = $request->validate([
                'lote_id' => 'required|string',
                'accion' => 'required|in:confirmar,revertir'
            ]);

            // Aquí implementarías la lógica de procesamiento de lote
            // Por ahora solo simulamos

            return redirect()->route('admin.payouts.lotes')
                ->with('success', 'Lote de pagos procesado exitosamente');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Error procesando lote: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Obtiene datos para gráficos - RETORNA JSON (AJAX)
     */
    public function datosGraficos(Request $request): JsonResponse
    {
        try {
            // Aquí implementarías la lógica para obtener datos de gráficos
            $datos = [
                'labels' => ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
                'datasets' => [
                    [
                        'label' => 'Pagos Realizados',
                        'data' => [100, 200, 150, 300, 250, 400],
                        'backgroundColor' => '#8a2be2'
                    ]
                ]
            ];

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
     * Búsqueda de payouts - RETORNA JSON (AJAX)
     */
    public function buscar(Request $request): JsonResponse
    {
        try {
            $resultados = $this->payoutService->obtenerPagosPorFiltro($request);

            return response()->json([
                'success' => true,
                'data' => $resultados->items(),
                'total' => $resultados->total(),
                'current_page' => $resultados->currentPage(),
                'per_page' => $resultados->perPage(),
                'last_page' => $resultados->lastPage()
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