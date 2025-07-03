<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale; // Importa tu modelo Sale
use Carbon\Carbon; // Para trabajar con fechas
use Barryvdh\DomPDF\Facade\Pdf; // Para generar PDFs, asegúrate de haberlo instalado
use Illuminate\View\View; // Importa la clase View
use Illuminate\Http\RedirectResponse; // Importa la clase RedirectResponse
use Illuminate\Http\Response; // Importa la clase Response para downloadPdf

class SalesController extends Controller
{
    /**
     * Muestra el reporte de ventas con datos agrupados por día, semana o mes.
     *
     * @param Request $request
     * @return View|RedirectResponse // Tipo de retorno ajustado
     */
    public function index(Request $request): View|RedirectResponse
    {
        // Obtener fechas de inicio y fin del request o establecer valores por defecto
        $startDate = $request->input('startDate', Carbon::now()->subMonths(6)->toDateString());
        $endDate = $request->input('endDate', Carbon::now()->toDateString());
        // Obtener el tipo de reporte del request o establecer 'monthly' como por defecto
        $reportType = $request->input('reportType', 'monthly');

        // Validar las fechas (opcional pero recomendado)
        try {
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);
        } catch (\Exception $e) {
            // Manejar error de fecha inválida, por ejemplo, redirigir con un mensaje
            return redirect()->back()->withErrors(['date' => 'Fechas inválidas proporcionadas. Por favor, asegúrate de que el formato sea correcto.']);
        }

        // Asegurarse de que la fecha de inicio no sea posterior a la de fin
        if ($start->greaterThan($end)) {
            // Intercambiar si están al revés y mostrar un mensaje
            [$start, $end] = [$end, $start];
            $startDate = $start->toDateString();
            $endDate = $end->toDateString();
            return redirect()->back()->withErrors(['date' => 'La fecha de inicio era posterior a la fecha de fin. Se han intercambiado automáticamente.'])
                                     ->withInput($request->except(['startDate', 'endDate'])) // Mantener otros inputs
                                     ->with([
                                         'startDate' => $startDate,
                                         'endDate' => $endDate,
                                         'reportType' => $reportType
                                     ]);
        }

        // Consulta base para las ventas dentro del rango de fechas
        $salesQuery = Sale::whereBetween('sale_date', [$start->startOfDay(), $end->endOfDay()]);

        $labels = [];
        $chartData = [];

        switch ($reportType) {
            case 'daily':
                // Agrupar por día
                $sales = $salesQuery->selectRaw('DATE(sale_date) as period, SUM(total) as total_sales')
                                    ->groupBy('period')
                                    ->orderBy('period')
                                    ->get();

                // Generar un rango de fechas para asegurar que todos los días estén presentes, incluso si no hay ventas
                $period = Carbon::parse($start);
                while ($period->lte($end)) {
                    $dateString = $period->toDateString();
                    $labels[] = $dateString;
                    $foundSale = $sales->firstWhere('period', $dateString);
                    $chartData[] = $foundSale ? (float)$foundSale->total_sales : 0;
                    $period->addDay();
                }
                break;

            case 'weekly':
                // Agrupar por semana (año y número de semana)
                // Usamos WEEKOFYEAR para coincidir con la función WEEK de MySQL con el modo 1 (domingo como primer día)
                $sales = $salesQuery->selectRaw('YEAR(sale_date) as year, WEEKOFYEAR(sale_date) as week, SUM(total) as total_sales')
                                    ->groupBy('year', 'week')
                                    ->orderBy('year')
                                    ->orderBy('week')
                                    ->get();

                // Generar etiquetas de semanas (Ej: 2024-W1, 2024-W2)
                $period = Carbon::parse($start)->startOfWeek(Carbon::SUNDAY); // Asume semana empieza en domingo para WEEKOFYEAR
                $endOfWeekForLoop = Carbon::parse($end)->endOfWeek(Carbon::SUNDAY);

                while ($period->lte($endOfWeekForLoop)) {
                    $weekNumber = $period->weekOfYear; // Obtiene el número de semana ISO para Carbon
                    $year = $period->year;

                    // Ajuste para el número de semana si Carbon y MySQL difieren en los primeros días del año
                    // Carbon::weekOfYear puede devolver 52 o 53 para los primeros días si la primera semana completa no ha pasado.
                    // MySQL WEEKOFYEAR es más directo. Si hay discrepancia, se podría necesitar una lógica de ajuste.
                    // Para simplificar, asumimos que coinciden suficientemente.
                    $weekLabel = $year . '-W' . sprintf('%02d', $weekNumber);
                    $labels[] = $weekLabel;

                    $foundSale = $sales->first(function ($sale) use ($year, $weekNumber) {
                        return $sale->year == $year && $sale->week == $weekNumber;
                    });
                    $chartData[] = $foundSale ? (float)$foundSale->total_sales : 0;
                    $period->addWeek();
                }
                break;

            case 'monthly':
            default:
                // Agrupar por mes (año y mes)
                $sales = $salesQuery->selectRaw('YEAR(sale_date) as year, MONTH(sale_date) as month, SUM(total) as total_sales')
                                    ->groupBy('year', 'month')
                                    ->orderBy('year')
                                    ->orderBy('month')
                                    ->get();

                // Generar etiquetas de meses (Ej: Ene 2024, Feb 2024)
                $period = Carbon::parse($start)->startOfMonth();
                $endOfMonthForLoop = Carbon::parse($end)->endOfMonth();

                while ($period->lte($endOfMonthForLoop)) {
                    $monthLabel = $period->translatedFormat('M Y'); // E.g., 'Ene 2024'
                    $labels[] = $monthLabel;
                    $foundSale = $sales->first(function ($sale) use ($period) {
                        return $sale->year == $period->year && $sale->month == $period->month;
                    });
                    $chartData[] = $foundSale ? (float)$foundSale->total_sales : 0;
                    $period->addMonth();
                }
                break;
        }

        // Pasar los datos a la vista
        return view('Admin.reportes.sales_pdf', compact('startDate', 'endDate', 'reportType', 'labels', 'chartData'));
    }

    /**
     * Genera y descarga un reporte de ventas en PDF.
     *
     * @param Request $request
     * @return Response|RedirectResponse // Tipo de retorno ajustado
     */
    public function downloadPdf(Request $request): Response|RedirectResponse
    {
        $startDate = $request->input('startDate', Carbon::now()->subMonths(6)->toDateString());
        $endDate = $request->input('endDate', Carbon::now()->toDateString());
        $reportType = $request->input('reportType', 'monthly');

        // La lógica para obtener los datos es la misma que en el método index
        try {
            $start = Carbon::parse($startDate);
            $end = Carbon::parse($endDate);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['date' => 'Fechas inválidas proporcionadas para el PDF.']);
        }

        if ($start->greaterThan($end)) {
            [$start, $end] = [$end, $start];
            $startDate = $start->toDateString(); // Actualizar para la vista PDF
            $endDate = $end->toDateString(); // Actualizar para la vista PDF
        }

        $salesQuery = Sale::whereBetween('sale_date', [$start->startOfDay(), $end->endOfDay()]);

        $labels = [];
        $chartData = [];
        $sales = collect(); // Inicializar como colección vacía para evitar errores si no hay ventas

        switch ($reportType) {
            case 'daily':
                $sales = $salesQuery->selectRaw('DATE(sale_date) as period, SUM(total) as total_sales')
                                    ->groupBy('period')
                                    ->orderBy('period')
                                    ->get();
                $period = Carbon::parse($start);
                while ($period->lte($end)) {
                    $dateString = $period->toDateString();
                    $labels[] = $dateString;
                    $foundSale = $sales->firstWhere('period', $dateString);
                    $chartData[] = $foundSale ? (float)$foundSale->total_sales : 0;
                    $period->addDay();
                }
                break;

            case 'weekly':
                $sales = $salesQuery->selectRaw('YEAR(sale_date) as year, WEEKOFYEAR(sale_date) as week, SUM(total) as total_sales')
                                    ->groupBy('year', 'week')
                                    ->orderBy('year')
                                    ->orderBy('week')
                                    ->get();
                $period = Carbon::parse($start)->startOfWeek(Carbon::SUNDAY);
                $endOfWeekForLoop = Carbon::parse($end)->endOfWeek(Carbon::SUNDAY);
                while ($period->lte($endOfWeekForLoop)) {
                    $weekNumber = $period->weekOfYear;
                    $year = $period->year;
                    $weekLabel = $year . '-W' . sprintf('%02d', $weekNumber);
                    $labels[] = $weekLabel;
                    $foundSale = $sales->first(function ($sale) use ($year, $weekNumber) {
                        return $sale->year == $year && $sale->week == $weekNumber;
                    });
                    $chartData[] = $foundSale ? (float)$foundSale->total_sales : 0;
                    $period->addWeek();
                }
                break;

            case 'monthly':
            default:
                $sales = $salesQuery->selectRaw('YEAR(sale_date) as year, MONTH(sale_date) as month, SUM(total) as total_sales')
                                    ->groupBy('year', 'month')
                                    ->orderBy('year')
                                    ->orderBy('month')
                                    ->get();
                $period = Carbon::parse($start)->startOfMonth();
                $endOfMonthForLoop = Carbon::parse($end)->endOfMonth();
                while ($period->lte($endOfMonthForLoop)) {
                    $monthLabel = $period->translatedFormat('M Y');
                    $labels[] = $monthLabel;
                    $foundSale = $sales->first(function ($sale) use ($period) {
                        return $sale->year == $period->year && $sale->month == $period->month;
                    });
                    $chartData[] = $foundSale ? (float)$foundSale->total_sales : 0;
                    $period->addMonth();
                }
                break;
        }

        // Cargar una vista específica para el PDF (puedes crear 'admin.reports.sales_pdf')
        // Esta vista debe ser simple, sin scripts ni CSS externo que complique la generación de PDF
        $pdf = Pdf::loadView('Admin.reportes.sales_pdf', compact('labels', 'chartData', 'startDate', 'endDate', 'reportType'));

        // Opcional: Establecer el tamaño del papel y la orientación
        // $pdf->setPaper('a4', 'landscape');

        // Descargar el PDF con un nombre de archivo significativo
        return $pdf->download("reporte_ventas_{$reportType}_{$startDate}_a_{$endDate}.pdf");
    }
}
