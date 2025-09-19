<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function sales(Request $request)
    {
        // Obtener parámetros de filtro con valores por defecto
        $startDate = $request->input('startDate', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('endDate', now()->format('Y-m-d'));
        $reportType = $request->input('reportType', 'monthly');
        
        // Validar fechas
        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();
        
        // Obtener datos para el gráfico
        $chartData = $this->getChartData($startDate, $endDate, $reportType);
        
        // Estadísticas generales
        $stats = $this->getSalesStats($startDate, $endDate);
        
        // Tasa de cambio
        $exchangeRateVes = 36.50;
        
        return view('Admin.reportes.sales', compact(
            'startDate',
            'endDate',
            'reportType',
            'chartData',
            'stats',
            'exchangeRateVes'
        ));
    }
    
    private function getChartData($startDate, $endDate, $reportType)
    {
        $query = Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->where('status', 'completed');
        
        switch ($reportType) {
            case 'daily':
                return $this->getDailyData($query);
            case 'weekly':
                return $this->getWeeklyData($query);
            case 'monthly':
            default:
                return $this->getMonthlyData($query);
        }
    }
    
    private function getDailyData($query)
    {
        $data = $query->select(
                DB::raw('DATE(sale_date) as date'),
                DB::raw('SUM(total_amount) as total_sales'),
                DB::raw('COUNT(*) as total_orders')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        
        $labels = $data->map(function ($item) {
            return Carbon::parse($item->date)->format('d M');
        });
        
        $salesData = $data->pluck('total_sales');
        
        return [
            'labels' => $labels,
            'data' => $salesData,
            'total_orders' => $data->sum('total_orders')
        ];
    }
    
    private function getWeeklyData($query)
    {
        $data = $query->select(
                DB::raw('YEAR(sale_date) as year'),
                DB::raw('WEEK(sale_date, 1) as week'),
                DB::raw('SUM(total_amount) as total_sales'),
                DB::raw('COUNT(*) as total_orders')
            )
            ->groupBy('year', 'week')
            ->orderBy('year')
            ->orderBy('week')
            ->get();
        
        $labels = $data->map(function ($item) {
            return "Sem {$item->week}";
        });
        
        $salesData = $data->pluck('total_sales');
        
        return [
            'labels' => $labels,
            'data' => $salesData,
            'total_orders' => $data->sum('total_orders')
        ];
    }
    
    private function getMonthlyData($query)
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
        
        $salesData = $data->pluck('total_sales');
        
        return [
            'labels' => $labels,
            'data' => $salesData,
            'total_orders' => $data->sum('total_orders')
        ];
    }
    
    private function getSalesStats($startDate, $endDate)
    {
        // Ventas totales
        $totalSales = Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->sum('total_amount');
        
        // Total de órdenes
        $totalOrders = Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->count();
        
        // Ventas promedio por orden
        $averageOrderValue = $totalOrders > 0 ? $totalSales / $totalOrders : 0;
        
        // Clientes únicos
        $uniqueClients = Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->distinct('client_id')
            ->count('client_id');
        
        // Métodos de pago más populares
        $paymentMethods = Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->select('payment_method', DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->orderBy('count', 'desc')
            ->get();
        
        return [
            'total_sales' => $totalSales,
            'total_orders' => $totalOrders,
            'average_order_value' => $averageOrderValue,
            'unique_clients' => $uniqueClients,
            'payment_methods' => $paymentMethods
        ];
    }
    
    public function salesData(Request $request)
    {
        $startDate = $request->input('startDate', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('endDate', now()->format('Y-m-d'));
        $reportType = $request->input('reportType', 'monthly');
        
        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();
        
        $chartData = $this->getChartData($startDate, $endDate, $reportType);
        
        return response()->json([
            'labels' => $chartData['labels'],
            'data' => $chartData['data'],
            'total_orders' => $chartData['total_orders']
        ]);
    }
    
    public function exportSales(Request $request)
    {
        $startDate = $request->input('startDate', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('endDate', now()->format('Y-m-d'));
        $reportType = $request->input('reportType', 'monthly');
        
        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();
        
        $chartData = $this->getChartData($startDate, $endDate, $reportType);
        $stats = $this->getSalesStats($startDate, $endDate);
        
        // Aquí iría la lógica para generar el PDF
        // Por ahora devolvemos un JSON de éxito
        
        return response()->json([
            'success' => true,
            'message' => 'Reporte exportado exitosamente',
            'data' => [
                'chart' => $chartData,
                'stats' => $stats,
                'date_range' => [
                    'start' => $startDate->format('Y-m-d'),
                    'end' => $endDate->format('Y-m-d')
                ]
            ]
        ]);
    }
}