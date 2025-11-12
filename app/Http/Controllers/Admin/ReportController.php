<?php

namespace App\Http\Controllers\Admin;

use App\Models\Sale;
use App\Models\Ally;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Controller;

class ReportController extends Controller
{
    /**
     * Determina si el usuario actual es administrador
     */
    private function isAdmin($userRole)
    {
        return $userRole === 'admin' || $userRole === 'administrador';
    }

    /**
     * Determina si el usuario actual es aliado
     */
    private function isAlly($user)
    {
        return $user->is_ally && $user->ally;
    }

    /**
     * Obtiene el ID del aliado del usuario actual
     */
    private function getUserAllyId($user)
    {
        return $this->isAlly($user) ? $user->ally->id : null;
    }

    /**
     * Obtiene tasa de cambio desde el endpoint interno /api/banks/daily-dollar-rate
     */
    private function getExchangeRate()
    {
        try {
            $response = Http::timeout(10)->get(url('/api/banks/daily-dollar-rate'));
            
            if ($response->successful()) {
                $data = $response->json();
                
                // Extraer tasa del response
                if (isset($data['success']) && $data['success'] === true && isset($data['data']['PriceRateBCV'])) {
                    $rate = (float) $data['data']['PriceRateBCV'];
                    
                    // Si la tasa es válida, retornarla
                    if ($rate > 100 && $rate < 1000) {
                        return $rate;
                    }
                }
            }
            
            // Si el API falla, usar tasa actualizada manualmente
            return $this->getCurrentRate();
            
        } catch (\Exception $e) {
            Log::error('Error obteniendo tasa del dólar: ' . $e->getMessage());
            return $this->getCurrentRate();
        }
    }

    /**
     * Obtiene la tasa actual (actualizada manualmente según el mercado)
     */
    private function getCurrentRate()
    {
        // Tasas actualizadas para Noviembre 2025 (basado en tasas reales del mercado)
        $currentRates = [
            '2025-11' => 233.05,  // Tasa actual del BCV
            '2025-12' => 235.50,  // Proyección
        ];
        
        $currentMonth = now()->format('Y-m');
        return $currentRates[$currentMonth] ?? 233.05;
    }

    /**
     * Muestra el dashboard de reportes de ventas
     */
    public function sales(Request $request)
    {
        $startDate = $request->input('startDate', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('endDate', now()->format('Y-m-d'));
        $reportType = $request->input('reportType', 'monthly');

        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        if ($endDate->gt(now())) {
            $endDate = now();
        }

        $user = auth()->user();
        $userRole = $user->role;
        $allyId = $this->getUserAllyId($user);

        $exchangeRateVes = $this->getExchangeRate();
        $chartData = $this->getChartData($startDate, $endDate, $reportType, $userRole, $allyId);
        $stats = $this->getSalesStats($startDate, $endDate, $userRole, $allyId, $exchangeRateVes);
        $metrics = $this->getAdditionalMetrics($startDate, $endDate, $userRole, $allyId, $exchangeRateVes);

        return view('Admin.reportes.sales', compact(
            'startDate',
            'endDate',
            'reportType',
            'chartData',
            'stats',
            'exchangeRateVes',
            'metrics',
            'userRole',
            'allyId'
        ));
    }

    /**
     * Obtiene datos para el gráfico según el tipo de reporte
     */
    private function getChartData($startDate, $endDate, $reportType, $userRole = null, $allyId = null)
    {
        $query = Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->where('status', 'completed');

        if (!$this->isAdmin($userRole) && $allyId) {
            $query->where('ally_id', $allyId);
        }

        switch ($reportType) {
            case 'daily':
                return $this->getDailyData($query, $startDate, $endDate);
            case 'weekly':
                return $this->getWeeklyData($query, $startDate, $endDate);
            case 'monthly':
            default:
                return $this->getMonthlyData($query, $startDate, $endDate);
        }
    }

    /**
     * Datos para vista diaria
     */
    private function getDailyData($query, $startDate, $endDate)
    {
        $period = new \DatePeriod($startDate, new \DateInterval('P1D'), $endDate);

        $data = $query->select(
            DB::raw('DATE(sale_date) as date'),
            DB::raw('SUM(total_amount) as total_sales'),
            DB::raw('COUNT(*) as total_orders')
        )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->date)->format('Y-m-d');
            });

        $labels = [];
        $salesData = [];
        $ordersData = [];

        foreach ($period as $date) {
            $dateString = $date->format('Y-m-d');
            $formattedDate = $date->format('d M');

            $labels[] = $formattedDate;

            if (isset($data[$dateString])) {
                $salesData[] = (float) $data[$dateString]->total_sales;
                $ordersData[] = $data[$dateString]->total_orders;
            } else {
                $salesData[] = 0;
                $ordersData[] = 0;
            }
        }

        return [
            'labels' => $labels,
            'data' => $salesData,
            'orders' => $ordersData,
            'total_orders' => array_sum($ordersData)
        ];
    }

    /**
     * Datos para vista semanal
     */
    private function getWeeklyData($query, $startDate, $endDate)
    {
        $data = $query->select(
            DB::raw('YEAR(sale_date) as year'),
            DB::raw('WEEK(sale_date, 1) as week'),
            DB::raw('MIN(sale_date) as week_start'),
            DB::raw('SUM(total_amount) as total_sales'),
            DB::raw('COUNT(*) as total_orders')
        )
            ->groupBy('year', 'week')
            ->orderBy('year')
            ->orderBy('week')
            ->get();

        $labels = $data->map(function ($item) {
            $weekStart = Carbon::parse($item->week_start);
            return "Sem {$item->week} (" . $weekStart->format('d/m') . ")";
        });

        $salesData = $data->pluck('total_sales')->map(function ($value) {
            return (float) $value;
        });

        $ordersData = $data->pluck('total_orders');

        return [
            'labels' => $labels,
            'data' => $salesData,
            'orders' => $ordersData,
            'total_orders' => $data->sum('total_orders')
        ];
    }

    /**
     * Datos para vista mensual
     */
    private function getMonthlyData($query, $startDate, $endDate)
    {
        $data = $query->select(
            DB::raw('YEAR(sale_date) as year'),
            DB::raw('MONTH(sale_date) as month'),
            DB::raw('SUM(total_amount) as total_sales'),
            DB::raw('COUNT(*) as total_orders')
        )
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $labels = $data->map(function ($item) {
            $date = Carbon::createFromDate($item->year, $item->month, 1);
            return $date->format('M Y');
        });

        $salesData = $data->pluck('total_sales')->map(function ($value) {
            return (float) $value;
        });

        $ordersData = $data->pluck('total_orders');

        return [
            'labels' => $labels,
            'data' => $salesData,
            'orders' => $ordersData,
            'total_orders' => $data->sum('total_orders')
        ];
    }

    /**
     * Obtiene estadísticas generales de ventas con conversión a VES
     */
    private function getSalesStats($startDate, $endDate, $userRole = null, $allyId = null, $exchangeRate = null)
    {
        $query = Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->where('status', 'completed');

        if (!$this->isAdmin($userRole) && $allyId) {
            $query->where('ally_id', $allyId);
        }

        $totalSales = $query->sum('total_amount');
        $totalSalesVes = $exchangeRate ? $totalSales * $exchangeRate : null;
        $totalOrders = $query->count();
        $averageOrderValue = $totalOrders > 0 ? $totalSales / $totalOrders : 0;
        $averageOrderValueVes = $exchangeRate ? $averageOrderValue * $exchangeRate : null;
        $uniqueClients = $query->distinct('client_id')->count('client_id');

        $paymentMethodsQuery = Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->where('status', 'completed');

        if (!$this->isAdmin($userRole) && $allyId) {
            $paymentMethodsQuery->where('ally_id', $allyId);
        }

        $paymentMethods = $paymentMethodsQuery
            ->select('payment_method', DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->orderBy('count', 'desc')
            ->get();

        $previousPeriodDays = $endDate->diffInDays($startDate);
        $previousStartDate = $startDate->copy()->subDays($previousPeriodDays);
        $previousEndDate = $startDate->copy()->subDay();

        $previousSalesQuery = Sale::whereBetween('sale_date', [$previousStartDate, $previousEndDate])
            ->where('status', 'completed');

        if (!$this->isAdmin($userRole) && $allyId) {
            $previousSalesQuery->where('ally_id', $allyId);
        }

        $previousSales = $previousSalesQuery->sum('total_amount');
        $previousSalesVes = $exchangeRate ? $previousSales * $exchangeRate : null;

        $growth = $previousSales > 0 ?
            (($totalSales - $previousSales) / $previousSales) * 100 : ($totalSales > 0 ? 100 : 0);

        return [
            'total_sales' => $totalSales,
            'total_sales_ves' => $totalSalesVes,
            'total_orders' => $totalOrders,
            'average_order_value' => $averageOrderValue,
            'average_order_value_ves' => $averageOrderValueVes,
            'unique_clients' => $uniqueClients,
            'payment_methods' => $paymentMethods,
            'growth' => $growth,
            'previous_sales' => $previousSales,
            'previous_sales_ves' => $previousSalesVes,
            'exchange_rate' => $exchangeRate
        ];
    }

    /**
     * Obtiene métricas adicionales adaptadas a tu estructura
     */
    private function getAdditionalMetrics($startDate, $endDate, $userRole = null, $allyId = null, $exchangeRate = null)
    {
        $isAdmin = $this->isAdmin($userRole);

        $topAlly = null;
        if ($isAdmin) {
            $topAlly = Sale::with('ally')
                ->whereBetween('sale_date', [$startDate, $endDate])
                ->where('status', 'completed')
                ->select('ally_id', DB::raw('SUM(total_amount) as total_sales'), DB::raw('COUNT(*) as total_orders'))
                ->groupBy('ally_id')
                ->orderByDesc('total_sales')
                ->first();

            if ($topAlly && $exchangeRate) {
                $topAlly->total_sales_ves = $topAlly->total_sales * $exchangeRate;
            }
        }

        $branchQuery = Sale::with('branch')
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->where('status', 'completed');

        if (!$isAdmin && $allyId) {
            $branchQuery->where('ally_id', $allyId);
        }

        $topBranch = $branchQuery
            ->select('branch_id', DB::raw('SUM(total_amount) as total_sales'), DB::raw('COUNT(*) as total_orders'))
            ->groupBy('branch_id')
            ->orderByDesc('total_sales')
            ->first();

        if ($topBranch && $exchangeRate) {
            $topBranch->total_sales_ves = $topBranch->total_sales * $exchangeRate;
        }

        $largestSaleQuery = Sale::with(['client', 'ally', 'branch'])
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->where('status', 'completed');

        if (!$isAdmin && $allyId) {
            $largestSaleQuery->where('ally_id', $allyId);
        }

        $largestSale = $largestSaleQuery->orderByDesc('total_amount')->first();
        $largestSaleVes = $exchangeRate && $largestSale ? $largestSale->total_amount * $exchangeRate : null;

        $bestDayQuery = Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->where('status', 'completed');

        if (!$isAdmin && $allyId) {
            $bestDayQuery->where('ally_id', $allyId);
        }

        $bestDay = $bestDayQuery
            ->select(
                DB::raw('DATE(sale_date) as date'),
                DB::raw('SUM(total_amount) as daily_sales'),
                DB::raw('COUNT(*) as daily_orders')
            )
            ->groupBy('date')
            ->orderByDesc('daily_sales')
            ->first();

        $bestDayVes = $exchangeRate && $bestDay ? $bestDay->daily_sales * $exchangeRate : null;

        $paymentMethodQuery = Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->where('status', 'completed');

        if (!$isAdmin && $allyId) {
            $paymentMethodQuery->where('ally_id', $allyId);
        }

        $topPaymentMethod = $paymentMethodQuery
            ->select('payment_method', DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->orderByDesc('count')
            ->first();

        $currentAllyInfo = null;
        if (!$isAdmin && $allyId) {
            $currentAllyInfo = Ally::find($allyId);
        }

        return [
            'top_ally' => $topAlly,
            'top_branch' => $topBranch,
            'largest_sale' => $largestSale,
            'largest_sale_ves' => $largestSaleVes,
            'best_day' => $bestDay,
            'best_day_ves' => $bestDayVes,
            'top_payment_method' => $topPaymentMethod,
            'current_ally_info' => $currentAllyInfo,
            'is_admin' => $isAdmin,
            'exchange_rate' => $exchangeRate
        ];
    }

    /**
     * Endpoint para datos AJAX del gráfico
     */
    public function salesData(Request $request)
    {
        $startDate = $request->input('startDate', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('endDate', now()->format('Y-m-d'));
        $reportType = $request->input('reportType', 'monthly');

        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        $user = auth()->user();
        $userRole = $user->role;
        $allyId = $this->getUserAllyId($user);

        $exchangeRateVes = $this->getExchangeRate();
        $chartData = $this->getChartData($startDate, $endDate, $reportType, $userRole, $allyId);
        $stats = $this->getSalesStats($startDate, $endDate, $userRole, $allyId, $exchangeRateVes);

        return response()->json([
            'success' => true,
            'chart' => [
                'labels' => $chartData['labels'],
                'data' => $chartData['data'],
                'orders' => $chartData['orders'],
                'total_orders' => $chartData['total_orders']
            ],
            'stats' => $stats,
            'exchange_rate' => $exchangeRateVes,
            'date_range' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
                'report_type' => $reportType
            ]
        ]);
    }

    /**
     * Exporta reporte a PDF usando DomPDF
     */
    public function exportSales(Request $request)
    {
        try {
            $startDate = $request->input('startDate', now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('endDate', now()->format('Y-m-d'));
            $reportType = $request->input('reportType', 'monthly');

            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();

            if ($endDate->gt(now())) {
                $endDate = now();
            }

            $user = auth()->user();
            $userRole = $user->role;
            $allyId = $this->getUserAllyId($user);

            $exchangeRateVes = $this->getExchangeRate();
            $chartData = $this->getChartData($startDate, $endDate, $reportType, $userRole, $allyId);
            $stats = $this->getSalesStats($startDate, $endDate, $userRole, $allyId, $exchangeRateVes);
            $metrics = $this->getAdditionalMetrics($startDate, $endDate, $userRole, $allyId, $exchangeRateVes);
            $dailySales = $this->getDailySalesForPdf($startDate, $endDate, $userRole, $allyId, $exchangeRateVes);

            $pdf = app('dompdf.wrapper');
            $pdf->setPaper('A4', 'landscape');

            $pdf->loadView('Admin.reportes.pdf.sales-pdf', compact(
                'startDate',
                'endDate',
                'reportType',
                'chartData',
                'stats',
                'exchangeRateVes',
                'metrics',
                'dailySales',
                'userRole',
                'allyId'
            ));

            if ($this->isAdmin($userRole)) {
                $filename = "reporte_ventas_{$startDate->format('Y-m-d')}_a_{$endDate->format('Y-m-d')}.pdf";
            } else {
                $allyName = $user->ally ? str_replace(' ', '_', $user->ally->company_name) : 'aliado';
                $filename = "reporte_ventas_{$allyName}_{$startDate->format('Y-m-d')}_a_{$endDate->format('Y-m-d')}.pdf";
            }

            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Error generando PDF: ' . $e->getMessage());
            return response()->json(['error' => 'Error al generar el PDF'], 500);
        }
    }

    /**
     * Exporta reporte a PDF con opción de vista previa
     */
    public function exportSalesPreview(Request $request)
    {
        $startDate = $request->input('startDate', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('endDate', now()->format('Y-m-d'));
        $reportType = $request->input('reportType', 'monthly');

        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        $user = auth()->user();
        $userRole = $user->role;
        $allyId = $this->getUserAllyId($user);

        $exchangeRateVes = $this->getExchangeRate();
        $chartData = $this->getChartData($startDate, $endDate, $reportType, $userRole, $allyId);
        $stats = $this->getSalesStats($startDate, $endDate, $userRole, $allyId, $exchangeRateVes);
        $metrics = $this->getAdditionalMetrics($startDate, $endDate, $userRole, $allyId, $exchangeRateVes);
        $dailySales = $this->getDailySalesForPdf($startDate, $endDate, $userRole, $allyId, $exchangeRateVes);

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('Admin.reportes.pdf.sales-pdf', compact(
            'startDate',
            'endDate',
            'reportType',
            'chartData',
            'stats',
            'exchangeRateVes',
            'metrics',
            'dailySales',
            'userRole',
            'allyId'
        ));

        return $pdf->stream();
    }

    /**
     * Obtiene datos diarios para el PDF
     */
    private function getDailySalesForPdf($startDate, $endDate, $userRole = null, $allyId = null, $exchangeRate = null)
    {
        $query = Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->where('status', 'completed');

        if (!$this->isAdmin($userRole) && $allyId) {
            $query->where('ally_id', $allyId);
        }

        return $query
            ->select(
                DB::raw('DATE(sale_date) as date'),
                DB::raw('SUM(total_amount) as sales_usd'),
                DB::raw('COUNT(*) as orders'),
                DB::raw('AVG(total_amount) as average')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) use ($exchangeRate) {
                return [
                    'date' => Carbon::parse($item->date),
                    'sales_usd' => (float) $item->sales_usd,
                    'sales_ves' => (float) $item->sales_usd * $exchangeRate,
                    'orders' => $item->orders,
                    'average' => (float) $item->average,
                    'average_ves' => (float) $item->average * $exchangeRate
                ];
            });
    }

    /**
     * Endpoint para obtener datos en tiempo real (últimas ventas)
     */
    public function recentSales(Request $request)
    {
        $limit = $request->input('limit', 10);

        $user = auth()->user();
        $userRole = $user->role;
        $allyId = $this->getUserAllyId($user);

        $query = Sale::with(['client', 'ally', 'branch'])
            ->where('status', 'completed')
            ->orderBy('sale_date', 'desc')
            ->limit($limit);

        if (!$this->isAdmin($userRole) && $allyId) {
            $query->where('ally_id', $allyId);
        }

        $exchangeRate = $this->getExchangeRate();

        $recentSales = $query->get()
            ->map(function ($sale) use ($exchangeRate) {
                return [
                    'id' => $sale->id,
                    'client' => $sale->client->name ?? 'Cliente no identificado',
                    'ally' => $sale->ally->company_name ?? 'Aliado no identificado',
                    'branch' => $sale->branch->name ?? 'Sucursal no identificada',
                    'amount' => $sale->total_amount,
                    'amount_ves' => $sale->total_amount * $exchangeRate,
                    'date' => $sale->sale_date->format('d/m/Y H:i'),
                    'payment_method' => $sale->payment_method,
                    'exchange_rate' => $exchangeRate
                ];
            });

        return response()->json([
            'success' => true,
            'recent_sales' => $recentSales,
            'exchange_rate' => $exchangeRate
        ]);
    }

    /**
     * Endpoint para métricas del dashboard
     */
    public function dashboardMetrics(Request $request)
    {
        $today = now();
        $yesterday = $today->copy()->subDay();
        $thisMonth = $today->copy()->startOfMonth();
        $lastMonth = $thisMonth->copy()->subMonth();

        $user = auth()->user();
        $userRole = $user->role;
        $allyId = $this->getUserAllyId($user);

        $applyFilters = function($query) use ($userRole, $allyId) {
            if (!$this->isAdmin($userRole) && $allyId) {
                $query->where('ally_id', $allyId);
            }
            return $query;
        };

        $exchangeRate = $this->getExchangeRate();

        $todaySales = $applyFilters(Sale::whereDate('sale_date', $today)
            ->where('status', 'completed'))->sum('total_amount');
        $todaySalesVes = $todaySales * $exchangeRate;

        $yesterdaySales = $applyFilters(Sale::whereDate('sale_date', $yesterday)
            ->where('status', 'completed'))->sum('total_amount');
        $yesterdaySalesVes = $yesterdaySales * $exchangeRate;

        $dailyGrowth = $yesterdaySales > 0 ?
            (($todaySales - $yesterdaySales) / $yesterdaySales) * 100 : 0;

        $monthSales = $applyFilters(Sale::whereBetween('sale_date', [$thisMonth, $today])
            ->where('status', 'completed'))->sum('total_amount');
        $monthSalesVes = $monthSales * $exchangeRate;

        $lastMonthSales = $applyFilters(Sale::whereBetween('sale_date', [
            $lastMonth,
            $thisMonth->copy()->subDay()
        ])->where('status', 'completed'))->sum('total_amount');
        $lastMonthSalesVes = $lastMonthSales * $exchangeRate;

        $monthlyGrowth = $lastMonthSales > 0 ?
            (($monthSales - $lastMonthSales) / $lastMonthSales) * 100 : 0;

        return response()->json([
            'success' => true,
            'metrics' => [
                'today_sales' => $todaySales,
                'today_sales_ves' => $todaySalesVes,
                'yesterday_sales' => $yesterdaySales,
                'yesterday_sales_ves' => $yesterdaySalesVes,
                'daily_growth' => $dailyGrowth,
                'month_sales' => $monthSales,
                'month_sales_ves' => $monthSalesVes,
                'last_month_sales' => $lastMonthSales,
                'last_month_sales_ves' => $lastMonthSalesVes,
                'monthly_growth' => $monthlyGrowth,
                'exchange_rate' => $exchangeRate,
                'user_role' => $userRole
            ]
        ]);
    }
}
