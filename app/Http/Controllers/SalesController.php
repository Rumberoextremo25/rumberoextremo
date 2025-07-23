<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;
use App\Models\Sale; // Make sure you import your Sale model
use Barryvdh\DomPDF\Facade\Pdf; // Make sure you import the PDF facade if using barryvdh/laravel-dompdf

class SalesController extends Controller
{
    /**
     * Muestra el reporte de ventas con datos agrupados por día, semana o mes.
     *
     * @param Request $request
     * @return View|RedirectResponse
     */
    public function index(Request $request): View|RedirectResponse
    {
        // --- 1. Tasa de Cambio (Ejemplo - DEBES IMPLEMENTAR CÓMO OBTENERLA REALMENTE) ---
        // ESTA ES UNA TASA FIJA DE EJEMPLO. EN UN ENTORNO REAL EN VENEZUELA,
        // DEBES OBTENERLA DE UNA API (como la del BCV si es accesible, o un proveedor de datos financiero)
        // O DE TU BASE DE DATOS PARA MAYOR PRECISIÓN Y ACTUALIZACIÓN CONSTANTE.
        // Considerando la fecha actual (July 18, 2025) y la inflación, una tasa más realista
        // podría ser mucho más alta. Ajusta según tus fuentes.
        // Aquí puedes obtener la tasa de:
        // 1. Una configuración (config('app.exchange_rate_usd_ves'))
        // 2. La base de datos (e.g., Setting::where('key', 'exchange_rate_usd_ves')->value('value'))
        // 3. Una API externa (requeriría un cliente HTTP como Guzzle)
        $exchangeRateVes = 45.75; // Tasa de ejemplo: 1 USD = 45.75 VES (ajustada para el 18/07/2025)

        // Obtener fechas de inicio y fin del request o establecer valores por defecto
        $startDate = $request->input('startDate', Carbon::now()->subMonths(6)->toDateString());
        $endDate = $request->input('endDate', Carbon::now()->toDateString());
        // Obtener el tipo de reporte del request o establecer 'monthly' como por defecto
        $reportType = $request->input('reportType', 'monthly');

        // Validar y normalizar las fechas
        $dateValidationResult = $this->validateAndNormalizeDates($startDate, $endDate, $reportType);

        if ($dateValidationResult instanceof RedirectResponse) {
            return $dateValidationResult; // Redirige si hay errores de fecha
        }

        [$startCarbon, $endCarbon, $startDate, $endDate] = $dateValidationResult;

        // Obtener los datos del reporte usando el método helper
        [$labels, $chartData] = $this->getSalesData($startCarbon, $endCarbon, $reportType);

        // Pasar los datos a la vista. Aquí ya estás usando 'compact',
        // solo necesitas asegurarte de que 'exchangeRateVes' esté incluido.
        return view('Admin.reportes', compact('startDate', 'endDate', 'reportType', 'labels', 'chartData', 'exchangeRateVes'));
    }

    /**
     * Genera y descarga un reporte de ventas en PDF.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response|RedirectResponse
     */
    public function downloadPdf(Request $request): \Symfony\Component\HttpFoundation\Response|RedirectResponse
    {
        // --- 1. Tasa de Cambio (Ejemplo - DEBES IMPLEMENTAR CÓMO OBTENERLA REALMENTE) ---
        // DE NUEVO, ESTA ES UNA TASA FIJA DE EJEMPLO.
        // DEBE SER LA MISMA LÓGICA DE OBTENCIÓN QUE EN EL MÉTODO 'index'.
        $exchangeRateVes = 45.75; // Tasa de ejemplo (misma que en el método index para consistencia)

        $startDate = $request->input('startDate', Carbon::now()->subMonths(6)->toDateString());
        $endDate = $request->input('endDate', Carbon::now()->toDateString());
        $reportType = $request->input('reportType', 'monthly');

        // Validar y normalizar las fechas
        $dateValidationResult = $this->validateAndNormalizeDates($startDate, $endDate, $reportType);

        if ($dateValidationResult instanceof RedirectResponse) {
            return $dateValidationResult; // Redirige si hay errores de fecha
        }

        [$startCarbon, $endCarbon, $startDate, $endDate] = $dateValidationResult; // Usar las fechas normalizadas

        // Obtener los datos del reporte usando el método helper
        [$labels, $chartData] = $this->getSalesData($startCarbon, $endCarbon, $reportType);

        // Cargar la vista específica para el PDF
        // Asegúrate de que la vista 'Admin.reportes.sales_pdf' exista y maneje los datos correctamente.
        $pdf = Pdf::loadView('Admin.reportes.sales_pdf', compact('labels', 'chartData', 'startDate', 'endDate', 'reportType', 'exchangeRateVes'));

        // Opcional: Establecer el tamaño del papel y la orientación (si no lo tienes ya en config/dompdf.php)
        // $pdf->setPaper('a4', 'portrait'); // o 'landscape'

        // Descargar el PDF con un nombre de archivo significativo
        return $pdf->download("reporte_ventas_{$reportType}_{$startDate}_a_{$endDate}.pdf");
    }

    /**
     * Valida y normaliza las fechas de inicio y fin.
     *
     * @param string $startDate
     * @param string $endDate
     * @param string $reportType
     * @return array|RedirectResponse Retorna [Carbon $start, Carbon $end, string $startDateStr, string $endDateStr] o RedirectResponse en caso de error.
     */
    private function validateAndNormalizeDates(string $startDate, string $endDate, string $reportType): array|RedirectResponse
    {
        try {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['date' => 'Fechas inválidas proporcionadas. Por favor, asegúrate de que el formato sea correcto.']);
        }

        // Asegurarse de que la fecha de inicio no sea posterior a la de fin
        if ($start->greaterThan($end)) {
            // Intercambiar si están al revés y preparar para redirigir
            [$start, $end] = [$end, $start];
            $tempStartDate = $start->toDateString();
            $tempEndDate = $end->toDateString();

            // Esto se usa en el index para mostrar un error y corregir las fechas
            return redirect()->back()->withErrors(['date' => 'La fecha de inicio era posterior a la fecha de fin. Se han intercambiado automáticamente.'])
                                     ->withInput(['startDate' => $tempStartDate, 'endDate' => $tempEndDate, 'reportType' => $reportType]);
        }

        return [$start, $end, $start->toDateString(), $end->toDateString()];
    }

    /**
     * Obtiene los datos de ventas agrupados según el tipo de reporte.
     *
     * @param Carbon $startCarbon
     * @param Carbon $endCarbon
     * @param string $reportType
     * @return array Retorna [array $labels, array $chartData]
     */
    private function getSalesData(Carbon $startCarbon, Carbon $endCarbon, string $reportType): array
    {
        $salesQuery = Sale::whereBetween('sale_date', [$startCarbon, $endCarbon]);

        $labels = [];
        $dataMap = []; // Usaremos un mapa para facilitar la asignación de ventas a los períodos

        switch ($reportType) {
            case 'daily':
                $sales = $salesQuery->selectRaw('DATE(sale_date) as period_key, SUM(total) as total_sales')
                                    ->groupBy('period_key')
                                    ->orderBy('period_key')
                                    ->get();

                $period = clone $startCarbon;
                while ($period->lte($endCarbon)) {
                    $key = $period->toDateString();
                    $label = $period->format('d/m/Y'); // Formato para la etiqueta del día
                    $dataMap[$key] = 0;
                    $labels[$key] = $label;
                    $period->addDay();
                }
                break;

            case 'weekly':
                // Nota: WEEKOFYEAR() en MySQL considera el Domingo como el primer día de la semana (modo 1).
                // Carbon::weekOfYear() usa ISO 8601 (Lunes como primer día).
                // Para consistencia, aquí ajustamos Carbon al inicio de semana en Domingo para MySQL WEEKOFYEAR.
                // Si tu base de datos es PostgreSQL o SQLite, la función para semana del año puede variar.
                // Para compatibilidad total, podrías calcular la semana del año en PHP con Carbon después de obtener los datos brutos.
                $sales = $salesQuery->selectRaw('YEAR(sale_date) as year, WEEKOFYEAR(sale_date) as week_num, SUM(total) as total_sales')
                                    ->groupBy('year', 'week_num')
                                    ->orderBy('year')
                                    ->orderBy('week_num')
                                    ->get();

                // Asegúrate de que el inicio de la semana para Carbon sea el mismo que usa MySQL (Domingo)
                Carbon::setWeekStartsAt(Carbon::SUNDAY);
                Carbon::setWeekEndsAt(Carbon::SATURDAY);

                $period = clone $startCarbon->startOfWeek(); // Iniciar en el domingo de la primera semana
                $endLoop = clone $endCarbon->endOfWeek();     // Finalizar en el sábado de la última semana

                while ($period->lte($endLoop)) {
                    // Reconstruir la clave de la semana para que coincida con MySQL WEEKOFYEAR
                    // que retorna 1-53 y el año.
                    $weekKey = $period->year . '-' . sprintf('%02d', $period->weekOfYear);
                    $label = 'Semana ' . $period->weekOfYear . ' (' . $period->copy()->startOfWeek()->format('d/m') . ' - ' . $period->copy()->endOfWeek()->format('d/m') . ')';

                    $dataMap[$weekKey] = 0;
                    $labels[$weekKey] = $label;
                    $period->addWeek();
                }

                // Asignar los totales de ventas a los períodos correctos
                foreach ($sales as $sale) {
                    // Reconstruir la clave usando los datos de la consulta
                    $key = $sale->year . '-' . sprintf('%02d', $sale->week_num);
                    if (isset($dataMap[$key])) {
                        $dataMap[$key] = (float)$sale->total_sales;
                    }
                }

                // Restablecer la configuración de la semana de Carbon si es necesario para otras partes de la aplicación
                // Carbon::setWeekStartsAt(Carbon::MONDAY); // O tu valor por defecto
                // Carbon::setWeekEndsAt(Carbon::SUNDAY);   // O tu valor por defecto

                break;

            case 'monthly':
            default:
                $sales = $salesQuery->selectRaw('YEAR(sale_date) as year, MONTH(sale_date) as month, SUM(total) as total_sales')
                                    ->groupBy('year', 'month')
                                    ->orderBy('year')
                                    ->orderBy('month')
                                    ->get();

                $period = clone $startCarbon->startOfMonth();
                $endLoop = clone $endCarbon->endOfMonth();

                while ($period->lte($endLoop)) {
                    $key = $period->format('Y-m');
                    // Usar translatedFormat para meses en español si tienes las traducciones de Carbon
                    $label = $period->translatedFormat('F Y'); // E.g., 'Julio 2025'
                    $dataMap[$key] = 0;
                    $labels[$key] = $label;
                    $period->addMonth();
                }
                break;
        }

        // Llenar el dataMap con los resultados de la consulta para daily y monthly
        if ($reportType === 'daily' || $reportType === 'monthly') {
            foreach ($sales as $sale) {
                $key = $reportType === 'daily' ? $sale->period_key : $sale->year . '-' . sprintf('%02d', $sale->month);
                if (isset($dataMap[$key])) {
                    $dataMap[$key] = (float)$sale->total_sales;
                }
            }
        }
        
        // Asegurarse de que labels y chartData estén en el orden correcto y sean arrays indexados
        ksort($labels); // Ordenar las etiquetas por la clave (que es la fecha/periodo)
        $finalLabels = array_values($labels); // Convertir a array indexado para Chart.js

        $finalChartData = [];
        foreach ($labels as $key => $label) {
            $finalChartData[] = $dataMap[$key];
        }
        
        return [$finalLabels, $finalChartData];
    }
}