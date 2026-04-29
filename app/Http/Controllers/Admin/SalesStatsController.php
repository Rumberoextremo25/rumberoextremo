<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Ally;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesStatsController extends Controller
{
    public function index()
    {
        // Ventas por día (últimos 30 días)
        $salesByDay = Sale::where('created_at', '>=', Carbon::now()->subDays(30))
            ->whereIn('status', ['completed', 'completado'])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_amount) as total'))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Ventas por aliado
        $salesByAlly = Sale::whereIn('status', ['completed', 'completado'])
            ->with('ally')
            ->select('ally_id', DB::raw('SUM(total_amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('ally_id')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        // Resumen general
        $summary = [
            'total_sales' => Sale::whereIn('status', ['completed', 'completado'])->sum('total_amount'),
            'total_transactions' => Sale::whereIn('status', ['completed', 'completado'])->count(),
            'sales_today' => Sale::whereDate('created_at', Carbon::today())->whereIn('status', ['completed', 'completado'])->sum('total_amount'),
            'sales_this_month' => Sale::whereMonth('created_at', Carbon::now()->month)->whereIn('status', ['completed', 'completado'])->sum('total_amount'),
            'average_ticket' => Sale::whereIn('status', ['completed', 'completado'])->avg('total_amount'),
        ];

        return view('Admin.sales-stats', compact('salesByDay', 'salesByAlly', 'summary'));
    }
}
