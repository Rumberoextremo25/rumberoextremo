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
        // ✅ Ventas por día usando groupBy con cast
        $salesByDay = Sale::where('created_at', '>=', Carbon::now()->subDays(30))
            ->whereIn('status', ['completed', 'completado'])
            ->get()
            ->groupBy(function ($sale) {
                return $sale->created_at->format('Y-m-d');
            })
            ->map(function ($items, $date) {
                return (object) [
                    'date' => $date,
                    'total' => $items->sum('total_amount')
                ];
            })
            ->values();

        // ✅ Ventas por aliado usando Eloquent
        $salesByAlly = Sale::whereIn('status', ['completed', 'completado'])
            ->with('ally')
            ->get()
            ->groupBy('ally_id')
            ->map(function ($items, $allyId) {
                $ally = $items->first()->ally;
                return (object) [
                    'ally_id' => $allyId,
                    'ally' => $ally,
                    'total' => $items->sum('total_amount'),
                    'count' => $items->count()
                ];
            })
            ->sortByDesc('total')
            ->take(10)
            ->values();

        // ✅ Resumen general
        $summary = [
            'total_sales' => Sale::whereIn('status', ['completed', 'completado'])->sum('total_amount'),
            'total_transactions' => Sale::whereIn('status', ['completed', 'completado'])->count(),
            'sales_today' => Sale::whereDate('created_at', Carbon::today())->whereIn('status', ['completed', 'completado'])->sum('total_amount'),
            'sales_this_month' => Sale::whereBetween('created_at', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth()
            ])->whereIn('status', ['completed', 'completado'])->sum('total_amount'),
            'average_ticket' => Sale::whereIn('status', ['completed', 'completado'])->avg('total_amount'),
        ];

        return view('Admin.sales-stats', compact('salesByDay', 'salesByAlly', 'summary'));
    }
}
