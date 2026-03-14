<?php

namespace App\Http\Controllers\Admin;

use App\Models\Sale;
use App\Models\Ally;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        return $user->role === 'aliado' && $user->ally;
    }

    /**
     * Obtiene el ID del aliado del usuario actual
     */
    private function getUserAllyId($user)
    {
        return $this->isAlly($user) ? $user->ally->id : null;
    }

    /**
     * Obtiene las zonas únicas de los aliados desde company_address
     */
    private function getZonesFromAllies()
    {
        return Ally::whereNotNull('company_address')
            ->where('company_address', '!=', '')
            ->select('company_address as zone')
            ->distinct()
            ->orderBy('company_address')
            ->get()
            ->map(function ($item) {
                return (object) [
                    'id' => $item->zone,
                    'name' => $item->zone
                ];
            });
    }

    /**
     * Muestra el dashboard de reportes de ventas
     */
    public function sales(Request $request)
    {
        $startDate = $request->input('startDate', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('endDate', now()->format('Y-m-d'));
        $reportType = $request->input('reportType', 'monthly');
        $selectedAllyId = $request->input('ally_id');
        $selectedZone = $request->input('zone_id');

        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        if ($endDate->gt(now())) {
            $endDate = now();
        }

        $user = auth()->user();
        $userRole = $user->role;
        $allyId = $this->getUserAllyId($user);

        $chartData = $this->getChartData($startDate, $endDate, $reportType, $userRole, $allyId, $selectedAllyId, $selectedZone);
        $stats = $this->getSalesStats($startDate, $endDate, $userRole, $allyId, $selectedAllyId, $selectedZone);
        $metrics = $this->getAdditionalMetrics($startDate, $endDate, $userRole, $allyId, $selectedAllyId, $selectedZone);

        // Datos para filtros
        $allies = $this->isAdmin($userRole) ? Ally::orderBy('company_name')->get() : collect();
        $zones = $this->getZonesFromAllies();

        $selectedAllyName = $selectedAllyId ? Ally::find($selectedAllyId)->company_name ?? null : null;
        $selectedZoneName = $selectedZone;

        return view('Admin.reportes.sales', compact(
            'startDate',
            'endDate',
            'reportType',
            'chartData',
            'stats',
            'metrics',
            'userRole',
            'allyId',
            'allies',
            'zones',
            'selectedAllyId',
            'selectedZone',
            'selectedAllyName',
            'selectedZoneName'
        ));
    }

    /**
     * Obtiene datos para el gráfico según el tipo de reporte
     */
    private function getChartData($startDate, $endDate, $reportType, $userRole = null, $allyId = null, $selectedAllyId = null, $selectedZone = null)
    {
        $query = Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->where('status', 'completed');

        // Aplicar filtros
        $query = $this->applyFilters($query, $userRole, $allyId, $selectedAllyId, $selectedZone);

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
     * Aplica filtros a las consultas
     */
    private function applyFilters($query, $userRole, $allyId, $selectedAllyId = null, $selectedZone = null)
    {
        // Si es aliado, solo puede ver sus propias ventas
        if (!$this->isAdmin($userRole) && $allyId) {
            $query->where('ally_id', $allyId);
        }
        // Si es admin y seleccionó un aliado específico
        elseif ($this->isAdmin($userRole) && $selectedAllyId) {
            $query->where('ally_id', $selectedAllyId);
        }

        // Aplicar filtro por zona
        if ($selectedZone) {
            $query->whereHas('ally', function ($q) use ($selectedZone) {
                $q->where('company_address', $selectedZone);
            });
        }

        return $query;
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
     * Obtiene estadísticas de ventas
     */
    private function getSalesStats($startDate, $endDate, $userRole = null, $allyId = null, $selectedAllyId = null, $selectedZone = null)
    {
        $query = Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->where('status', 'completed');

        // Aplicar filtros
        $query = $this->applyFilters($query, $userRole, $allyId, $selectedAllyId, $selectedZone);

        $totalSales = $query->sum('total_amount');
        $totalOrders = $query->count();
        $averageOrderValue = $totalOrders > 0 ? $totalSales / $totalOrders : 0;
        $uniqueClients = $query->distinct('client_id')->count('client_id');

        // Métodos de pago más usados
        $paymentMethods = $query->select('payment_method', DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->orderBy('count', 'desc')
            ->get();

        // Comparación con período anterior
        $previousPeriodDays = $endDate->diffInDays($startDate);
        $previousStartDate = $startDate->copy()->subDays($previousPeriodDays);
        $previousEndDate = $startDate->copy()->subDay();

        $previousSalesQuery = Sale::whereBetween('sale_date', [$previousStartDate, $previousEndDate])
            ->where('status', 'completed');

        $previousSalesQuery = $this->applyFilters($previousSalesQuery, $userRole, $allyId, $selectedAllyId, $selectedZone);

        $previousSales = $previousSalesQuery->sum('total_amount');

        $growth = $previousSales > 0 ?
            (($totalSales - $previousSales) / $previousSales) * 100 : ($totalSales > 0 ? 100 : 0);

        return [
            'total_sales' => $totalSales,
            'total_orders' => $totalOrders,
            'average_order_value' => $averageOrderValue,
            'unique_clients' => $uniqueClients,
            'payment_methods' => $paymentMethods,
            'growth' => $growth,
            'previous_sales' => $previousSales,
        ];
    }

    /**
     * Obtiene métricas adicionales
     */
    private function getAdditionalMetrics($startDate, $endDate, $userRole = null, $allyId = null, $selectedAllyId = null, $selectedZone = null)
    {
        $isAdmin = $this->isAdmin($userRole);

        // Top aliado por volumen de ventas
        $topAlly = null;
        if ($isAdmin) {
            $topAllyQuery = Sale::with('ally')
                ->whereBetween('sale_date', [$startDate, $endDate])
                ->where('status', 'completed');

            $topAllyQuery = $this->applyFilters($topAllyQuery, $userRole, $allyId, $selectedAllyId, $selectedZone);

            $topAlly = $topAllyQuery
                ->select('ally_id', DB::raw('SUM(total_amount) as total_sales'), DB::raw('COUNT(*) as total_orders'))
                ->groupBy('ally_id')
                ->orderByDesc('total_sales')
                ->first();

            if ($topAlly) {
                $ally = Ally::find($topAlly->ally_id);
                $topAlly->name = $ally->company_name ?? 'N/A';
            }
        }

        // Top sucursal
        $branchQuery = Sale::with('branch')
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->where('status', 'completed');

        $branchQuery = $this->applyFilters($branchQuery, $userRole, $allyId, $selectedAllyId, $selectedZone);

        $topBranch = $branchQuery
            ->select('branch_id', DB::raw('SUM(total_amount) as total_sales'), DB::raw('COUNT(*) as total_orders'))
            ->groupBy('branch_id')
            ->orderByDesc('total_sales')
            ->first();

        // Venta más grande
        $largestSaleQuery = Sale::with(['client', 'ally', 'branch'])
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->where('status', 'completed');

        $largestSaleQuery = $this->applyFilters($largestSaleQuery, $userRole, $allyId, $selectedAllyId, $selectedZone);

        $largestSale = $largestSaleQuery->orderByDesc('total_amount')->first();

        // Mejor día
        $bestDayQuery = Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->where('status', 'completed');

        $bestDayQuery = $this->applyFilters($bestDayQuery, $userRole, $allyId, $selectedAllyId, $selectedZone);

        $bestDay = $bestDayQuery
            ->select(
                DB::raw('DATE(sale_date) as date'),
                DB::raw('SUM(total_amount) as daily_sales'),
                DB::raw('COUNT(*) as daily_orders')
            )
            ->groupBy('date')
            ->orderByDesc('daily_sales')
            ->first();

        // Método de pago más usado
        $paymentMethodQuery = Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->where('status', 'completed');

        $paymentMethodQuery = $this->applyFilters($paymentMethodQuery, $userRole, $allyId, $selectedAllyId, $selectedZone);

        $topPaymentMethod = $paymentMethodQuery
            ->select('payment_method', DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->orderByDesc('count')
            ->first();

        // Información del aliado actual (para vista de aliado)
        $currentAllyInfo = null;
        if (!$isAdmin && $allyId) {
            $currentAllyInfo = Ally::find($allyId);
        }

        return [
            'top_ally' => $topAlly,
            'top_branch' => $topBranch,
            'largest_sale' => $largestSale,
            'best_day' => $bestDay,
            'top_payment_method' => $topPaymentMethod,
            'current_ally_info' => $currentAllyInfo,
            'is_admin' => $isAdmin,
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
        $selectedAllyId = $request->input('ally_id');
        $selectedZone = $request->input('zone_id');

        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        $user = auth()->user();
        $userRole = $user->role;
        $allyId = $this->getUserAllyId($user);

        $chartData = $this->getChartData($startDate, $endDate, $reportType, $userRole, $allyId, $selectedAllyId, $selectedZone);
        $stats = $this->getSalesStats($startDate, $endDate, $userRole, $allyId, $selectedAllyId, $selectedZone);

        return response()->json([
            'success' => true,
            'chart' => [
                'labels' => $chartData['labels'],
                'data' => $chartData['data'],
                'orders' => $chartData['orders'],
                'total_orders' => $chartData['total_orders']
            ],
            'stats' => $stats,
            'date_range' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
                'report_type' => $reportType
            ]
        ]);
    }

    /**
     * Exporta reporte a PDF
     */
    public function exportSales(Request $request)
    {
        try {
            $startDate = $request->input('startDate', now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('endDate', now()->format('Y-m-d'));
            $reportType = $request->input('reportType', 'monthly');
            $selectedAllyId = $request->input('ally_id');
            $selectedZone = $request->input('zone_id');

            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();

            if ($endDate->gt(now())) {
                $endDate = now();
            }

            $user = auth()->user();
            $userRole = $user->role;
            $allyId = $this->getUserAllyId($user);

            $chartData = $this->getChartData($startDate, $endDate, $reportType, $userRole, $allyId, $selectedAllyId, $selectedZone);
            $stats = $this->getSalesStats($startDate, $endDate, $userRole, $allyId, $selectedAllyId, $selectedZone);
            $metrics = $this->getAdditionalMetrics($startDate, $endDate, $userRole, $allyId, $selectedAllyId, $selectedZone);
            $dailySales = $this->getDailySalesForPdf($startDate, $endDate, $userRole, $allyId, $selectedAllyId, $selectedZone);

            $pdf = app('dompdf.wrapper');
            $pdf->setPaper('A4', 'landscape');

            $pdf->loadView('Admin.reportes.pdf.sales-pdf', compact(
                'startDate',
                'endDate',
                'reportType',
                'chartData',
                'stats',
                'metrics',
                'dailySales',
                'userRole',
                'allyId',
                'selectedAllyId',
                'selectedZone'
            ));

            $filename = "reporte_ventas_{$startDate->format('Y-m-d')}_a_{$endDate->format('Y-m-d')}.pdf";

            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Error generando PDF: ' . $e->getMessage());
            return response()->json(['error' => 'Error al generar el PDF'], 500);
        }
    }

    /**
     * Vista previa del PDF
     */
    public function exportSalesPreview(Request $request)
    {
        $startDate = $request->input('startDate', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('endDate', now()->format('Y-m-d'));
        $reportType = $request->input('reportType', 'monthly');
        $selectedAllyId = $request->input('ally_id');
        $selectedZone = $request->input('zone_id');

        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        $user = auth()->user();
        $userRole = $user->role;
        $allyId = $this->getUserAllyId($user);

        $chartData = $this->getChartData($startDate, $endDate, $reportType, $userRole, $allyId, $selectedAllyId, $selectedZone);
        $stats = $this->getSalesStats($startDate, $endDate, $userRole, $allyId, $selectedAllyId, $selectedZone);
        $metrics = $this->getAdditionalMetrics($startDate, $endDate, $userRole, $allyId, $selectedAllyId, $selectedZone);
        $dailySales = $this->getDailySalesForPdf($startDate, $endDate, $userRole, $allyId, $selectedAllyId, $selectedZone);

        $pdf = app('dompdf.wrapper');
        $pdf->setPaper('A4', 'landscape');
        $pdf->loadView('Admin.reportes.pdf.sales-pdf', compact(
            'startDate',
            'endDate',
            'reportType',
            'chartData',
            'stats',
            'metrics',
            'dailySales',
            'userRole',
            'allyId',
            'selectedAllyId',
            'selectedZone'
        ));

        return $pdf->stream();
    }

    /**
     * Obtiene datos diarios para el PDF
     */
    private function getDailySalesForPdf($startDate, $endDate, $userRole = null, $allyId = null, $selectedAllyId = null, $selectedZone = null)
    {
        $query = Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->where('status', 'completed');

        $query = $this->applyFilters($query, $userRole, $allyId, $selectedAllyId, $selectedZone);

        return $query
            ->select(
                DB::raw('DATE(sale_date) as date'),
                DB::raw('SUM(total_amount) as sales'),
                DB::raw('COUNT(*) as orders'),
                DB::raw('AVG(total_amount) as average')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->date),
                    'sales' => (float) $item->sales,
                    'orders' => $item->orders,
                    'average' => (float) $item->average,
                ];
            });
    }

    /**
     * Endpoint para obtener datos en tiempo real (últimas ventas)
     */
    public function recentSales(Request $request)
    {
        $limit = $request->input('limit', 10);
        $selectedAllyId = $request->input('ally_id');
        $selectedZone = $request->input('zone_id');

        $user = auth()->user();
        $userRole = $user->role;
        $allyId = $this->getUserAllyId($user);

        $query = Sale::with(['client', 'ally', 'branch'])
            ->where('status', 'completed')
            ->orderBy('sale_date', 'desc')
            ->limit($limit);

        // Aplicar filtros
        if (!$this->isAdmin($userRole) && $allyId) {
            $query->where('ally_id', $allyId);
        } elseif ($this->isAdmin($userRole) && $selectedAllyId) {
            $query->where('ally_id', $selectedAllyId);
        }

        if ($selectedZone) {
            $query->whereHas('ally', function ($q) use ($selectedZone) {
                $q->where('company_address', $selectedZone);
            });
        }

        $recentSales = $query->get()
            ->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'client' => $sale->client->name ?? 'Cliente no identificado',
                    'ally' => $sale->ally->company_name ?? 'Aliado no identificado',
                    'branch' => $sale->branch->name ?? 'Sucursal no identificada',
                    'amount' => $sale->total_amount,
                    'date' => $sale->sale_date->format('d/m/Y H:i'),
                    'payment_method' => $sale->payment_method,
                ];
            });

        return response()->json([
            'success' => true,
            'recent_sales' => $recentSales,
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
        $selectedAllyId = $request->input('ally_id');
        $selectedZone = $request->input('zone_id');

        $user = auth()->user();
        $userRole = $user->role;
        $allyId = $this->getUserAllyId($user);

        // Función para aplicar filtros
        $applyFilters = function ($query) use ($userRole, $allyId, $selectedAllyId, $selectedZone) {
            if (!$this->isAdmin($userRole) && $allyId) {
                $query->where('ally_id', $allyId);
            } elseif ($this->isAdmin($userRole) && $selectedAllyId) {
                $query->where('ally_id', $selectedAllyId);
            }

            if ($selectedZone) {
                $query->whereHas('ally', function ($q) use ($selectedZone) {
                    $q->where('company_address', $selectedZone);
                });
            }

            return $query;
        };

        // Ventas hoy
        $todaySales = $applyFilters(Sale::whereDate('sale_date', $today)
            ->where('status', 'completed'))->sum('total_amount');

        // Ventas ayer
        $yesterdaySales = $applyFilters(Sale::whereDate('sale_date', $yesterday)
            ->where('status', 'completed'))->sum('total_amount');

        // Crecimiento diario
        $dailyGrowth = $yesterdaySales > 0 ?
            (($todaySales - $yesterdaySales) / $yesterdaySales) * 100 : 0;

        // Ventas del mes
        $monthSales = $applyFilters(Sale::whereBetween('sale_date', [$thisMonth, $today])
            ->where('status', 'completed'))->sum('total_amount');

        // Ventas mes anterior
        $lastMonthSales = $applyFilters(Sale::whereBetween('sale_date', [
            $lastMonth,
            $thisMonth->copy()->subDay()
        ])->where('status', 'completed'))->sum('total_amount');

        // Crecimiento mensual
        $monthlyGrowth = $lastMonthSales > 0 ?
            (($monthSales - $lastMonthSales) / $lastMonthSales) * 100 : 0;

        return response()->json([
            'success' => true,
            'metrics' => [
                'today_sales' => $todaySales,
                'yesterday_sales' => $yesterdaySales,
                'daily_growth' => $dailyGrowth,
                'month_sales' => $monthSales,
                'last_month_sales' => $lastMonthSales,
                'monthly_growth' => $monthlyGrowth,
                'user_role' => $userRole
            ]
        ]);
    }
}
