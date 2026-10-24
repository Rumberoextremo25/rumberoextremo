<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SalesStatsController extends Controller
{
    public function index(Request $request)
    {
        $periodo = $request->query('periodo', '30d');

        [$start, $end, $periodLabel] = $this->rangeFor($periodo);

        $salesQuery = Sale::whereBetween('created_at', [$start, $end])
            ->whereIn('status', ['completed', 'completado']);

        // Ventas por día del periodo
        $salesByDay = (clone $salesQuery)
            ->get()
            ->groupBy(function ($sale) {
                return $sale->created_at->format('Y-m-d');
            })
            ->map(function ($items, $date) {
                return (object) [
                    'date' => $date,
                    'total' => (float) $items->sum('total_amount'),
                ];
            })
            ->sortKeys()
            ->values();

        // Ventas por aliado del periodo
        $salesByAlly = (clone $salesQuery)
            ->with('ally')
            ->get()
            ->groupBy('ally_id')
            ->map(function ($items, $allyId) {
                return (object) [
                    'ally_id' => $allyId,
                    'ally' => $items->first()->ally,
                    'total' => (float) $items->sum('total_amount'),
                    'count' => $items->count(),
                ];
            })
            ->sortByDesc('total')
            ->take(10)
            ->values();

        $totalSales = (float) (clone $salesQuery)->sum('total_amount');
        $totalTransactions = (clone $salesQuery)->count();

        // Periodo anterior para comparar
        $days = max(1, (int) $start->diffInDays($end) + 1);
        $previousStart = $start->copy()->subDays($days);
        $previousSales = (float) Sale::whereBetween('created_at', [$previousStart, $start->copy()->subSecond()])
            ->whereIn('status', ['completed', 'completado'])
            ->sum('total_amount');

        $growth = $previousSales > 0
            ? round((($totalSales - $previousSales) / $previousSales) * 100, 1)
            : 0;

        $summary = [
            'total_sales' => $totalSales,
            'total_transactions' => $totalTransactions,
            'sales_today' => (float) Sale::whereDate('created_at', Carbon::today())
                ->whereIn('status', ['completed', 'completado'])
                ->sum('total_amount'),
            'sales_this_month' => (float) Sale::whereBetween('created_at', [
                Carbon::now()->startOfMonth(),
                Carbon::now()->endOfMonth(),
            ])->whereIn('status', ['completed', 'completado'])->sum('total_amount'),
            'average_ticket' => (float) (clone $salesQuery)->avg('total_amount'),
            'growth' => $growth,
            'previous_sales' => $previousSales,
        ];

        return view('Admin.sales-stats', compact(
            'salesByDay',
            'salesByAlly',
            'summary',
            'periodo',
            'periodLabel'
        ));
    }

    /**
     * Devuelve [inicio, fin, etiqueta] para el periodo solicitado.
     */
    private function rangeFor(string $periodo): array
    {
        $now = Carbon::now();

        return match ($periodo) {
            'hoy' => [$now->copy()->startOfDay(), $now->copy()->endOfDay(), 'Hoy'],
            '7d' => [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay(), 'Últimos 7 días'],
            'mes' => [$now->copy()->startOfMonth(), $now->copy()->endOfDay(), 'Este mes'],
            'anio' => [$now->copy()->startOfYear(), $now->copy()->endOfDay(), 'Este año'],
            default => [$now->copy()->subDays(29)->startOfDay(), $now->copy()->endOfDay(), 'Últimos 30 días'],
        };
    }
}
