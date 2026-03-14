<?php

namespace App\Http\Controllers\Admin;

use App\Models\Sale;
use App\Models\PaymentTransaction;
use App\Models\Payout;
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
     * Obtiene tasa de cambio desde el endpoint interno
     */
    private function getExchangeRate()
    {
        try {
            $response = Http::timeout(10)->get(url('/api/banks/daily-dollar-rate'));
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['success']) && $data['success'] === true && isset($data['data']['PriceRateBCV'])) {
                    $rate = (float) $data['data']['PriceRateBCV'];
                    
                    if ($rate > 100 && $rate < 1000) {
                        return $rate;
                    }
                }
            }
            
            return $this->getCurrentRate();
            
        } catch (\Exception $e) {
            Log::error('Error obteniendo tasa del dólar: ' . $e->getMessage());
            return $this->getCurrentRate();
        }
    }

    /**
     * Obtiene la tasa actual (fallback)
     */
    private function getCurrentRate()
    {
        return 233.05;
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
     * Muestra el dashboard de reportes de transacciones
     */
    public function transactions(Request $request)
    {
        $startDate = $request->input('startDate', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('endDate', now()->format('Y-m-d'));
        $reportType = $request->input('reportType', 'monthly');
        $selectedAllyId = $request->input('ally_id');
        $selectedZone = $request->input('zone_id');
        $selectedStatus = $request->input('status', 'all');
        $selectedPaymentMethod = $request->input('payment_method', 'all');

        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        if ($endDate->gt(now())) {
            $endDate = now();
        }

        $user = auth()->user();
        $userRole = $user->role;
        $allyId = $this->getUserAllyId($user);

        $exchangeRateVes = $this->getExchangeRate();
        
        // Obtener datos de PaymentTransaction
        $transactionChartData = $this->getTransactionChartData($startDate, $endDate, $reportType, $userRole, $allyId, $selectedAllyId, $selectedZone, $selectedStatus, $selectedPaymentMethod);
        $transactionStats = $this->getTransactionStats($startDate, $endDate, $userRole, $allyId, $exchangeRateVes, $selectedAllyId, $selectedZone, $selectedStatus, $selectedPaymentMethod);
        
        // Obtener lista de transacciones
        $transactions = $this->getTransactionsList($startDate, $endDate, $userRole, $allyId, $selectedAllyId, $selectedZone, $selectedStatus, $selectedPaymentMethod);
        
        // Obtener datos de ventas (Sales)
        $salesStats = $this->getSalesStats($startDate, $endDate, $userRole, $allyId, $exchangeRateVes, $selectedAllyId, $selectedZone);
        $salesChartData = $this->getSalesChartData($startDate, $endDate, $reportType, $userRole, $allyId, $selectedAllyId, $selectedZone);
        
        // Obtener datos de payouts
        $payoutStats = $this->getPayoutStats($startDate, $endDate, $userRole, $allyId, $exchangeRateVes, $selectedAllyId, $selectedZone);
        $pendingPayouts = $this->getPendingPayouts($userRole, $allyId, $selectedAllyId, $selectedZone);

        // Datos para filtros
        $allies = $this->isAdmin($userRole) ? Ally::orderBy('company_name')->get() : collect();
        $zones = $this->getZonesFromAllies();
        $statuses = [
            'confirmed' => 'Confirmada',
            'pending_manual_confirmation' => 'Pendiente',
            'pending_sms' => 'Esperando SMS',
            'pending_confirmation' => 'Por Confirmar',
            'failed' => 'Fallida',
            'awaiting_review' => 'En Revisión'
        ];
        $paymentMethods = [
            'pago_movil' => 'Pago Móvil',
            'tarjeta_credito' => 'Tarjeta de Crédito',
            'debito_inmediato' => 'Débito Inmediato'
        ];

        $selectedAllyName = $selectedAllyId ? Ally::find($selectedAllyId)->company_name ?? null : null;

        return view('Admin.reportes.sales', compact(
            'startDate',
            'endDate',
            'reportType',
            'transactionChartData',
            'transactionStats',
            'salesStats',
            'salesChartData',
            'payoutStats',
            'pendingPayouts',
            'exchangeRateVes',
            'transactions',
            'userRole',
            'allyId',
            'allies',
            'zones',
            'statuses',
            'paymentMethods',
            'selectedAllyId',
            'selectedZone',
            'selectedStatus',
            'selectedPaymentMethod',
            'selectedAllyName'
        ));
    }

    /**
     * Alias para transactions() - Mantiene compatibilidad con rutas existentes
     */
    public function sales(Request $request)
    {
        return $this->transactions($request);
    }

    /**
     * Obtiene lista de transacciones
     */
    private function getTransactionsList($startDate, $endDate, $userRole, $allyId, $selectedAllyId, $selectedZone, $selectedStatus, $selectedPaymentMethod)
    {
        $query = PaymentTransaction::with(['user', 'ally'])
            ->whereBetween('created_at', [$startDate, $endDate]);

        // Aplicar filtros
        $query = $this->applyTransactionFilters($query, $userRole, $allyId, $selectedAllyId, $selectedZone, $selectedStatus, $selectedPaymentMethod);

        return $query->orderBy('created_at', 'desc')
            ->limit(100)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'reference' => $transaction->reference_code,
                    'date' => $transaction->created_at->format('d/m/Y H:i'),
                    'user' => $transaction->user->name ?? 'N/A',
                    'user_email' => $transaction->user->email ?? 'N/A',
                    'ally' => $transaction->ally->company_name ?? 'Pago Directo',
                    'ally_email' => $transaction->ally->contact_email ?? 'N/A',
                    'original_amount' => $transaction->original_amount,
                    'discount' => $transaction->discount_percentage,
                    'commission' => $transaction->platform_commission,
                    'net_amount' => $transaction->amount_to_ally,
                    'payment_method' => $this->formatPaymentMethod($transaction->payment_method),
                    'status' => $this->formatStatus($transaction->status),
                    'status_code' => $transaction->status
                ];
            });
    }

    /**
     * Aplica filtros a las consultas de transacciones
     */
    private function applyTransactionFilters($query, $userRole, $allyId, $selectedAllyId = null, $selectedZone = null, $selectedStatus = null, $selectedPaymentMethod = null)
    {
        // Si es aliado, solo puede ver sus propias transacciones
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

        // Aplicar filtro por estado
        if ($selectedStatus && $selectedStatus !== 'all') {
            $query->where('status', $selectedStatus);
        }

        // Aplicar filtro por método de pago
        if ($selectedPaymentMethod && $selectedPaymentMethod !== 'all') {
            $query->where('payment_method', $selectedPaymentMethod);
        }

        return $query;
    }

    /**
     * Aplica filtros a las consultas de ventas
     */
    private function applySaleFilters($query, $userRole, $allyId, $selectedAllyId = null, $selectedZone = null)
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
     * Obtiene datos para el gráfico de transacciones
     */
    private function getTransactionChartData($startDate, $endDate, $reportType, $userRole = null, $allyId = null, $selectedAllyId = null, $selectedZone = null, $selectedStatus = null, $selectedPaymentMethod = null)
    {
        $query = PaymentTransaction::whereBetween('created_at', [$startDate, $endDate]);

        // Aplicar filtros
        $query = $this->applyTransactionFilters($query, $userRole, $allyId, $selectedAllyId, $selectedZone, $selectedStatus, $selectedPaymentMethod);

        return $this->formatChartData($query, $reportType, $startDate, $endDate, 'amount_to_ally');
    }

    /**
     * Obtiene datos para el gráfico de ventas
     */
    private function getSalesChartData($startDate, $endDate, $reportType, $userRole = null, $allyId = null, $selectedAllyId = null, $selectedZone = null)
    {
        $query = Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->where('status', 'completed');

        // Aplicar filtros
        $query = $this->applySaleFilters($query, $userRole, $allyId, $selectedAllyId, $selectedZone);

        return $this->formatChartData($query, $reportType, $startDate, $endDate, 'total_amount');
    }

    /**
     * Formatea datos para gráficos
     */
    private function formatChartData($query, $reportType, $startDate, $endDate, $amountField)
    {
        switch ($reportType) {
            case 'daily':
                $period = new \DatePeriod($startDate, new \DateInterval('P1D'), $endDate);
                
                $data = $query->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(' . $amountField . ') as total'),
                    DB::raw('COUNT(*) as count')
                )
                    ->groupBy('date')
                    ->get()
                    ->keyBy('date');

                $labels = [];
                $values = [];
                $counts = [];

                foreach ($period as $date) {
                    $dateString = $date->format('Y-m-d');
                    $labels[] = $date->format('d/m');
                    $values[] = isset($data[$dateString]) ? (float) $data[$dateString]->total : 0;
                    $counts[] = isset($data[$dateString]) ? $data[$dateString]->count : 0;
                }

                return [
                    'labels' => $labels,
                    'values' => $values,
                    'counts' => $counts,
                    'total_count' => array_sum($counts)
                ];

            case 'weekly':
                $data = $query->select(
                    DB::raw('YEAR(created_at) as year'),
                    DB::raw('WEEK(created_at, 1) as week'),
                    DB::raw('MIN(created_at) as week_start'),
                    DB::raw('SUM(' . $amountField . ') as total'),
                    DB::raw('COUNT(*) as count')
                )
                    ->groupBy('year', 'week')
                    ->orderBy('year')
                    ->orderBy('week')
                    ->get();

                $labels = $data->map(function ($item) {
                    $weekStart = Carbon::parse($item->week_start);
                    return "Sem {$item->week} (" . $weekStart->format('d/m') . ")";
                });

                return [
                    'labels' => $labels,
                    'values' => $data->pluck('total')->map(fn($v) => (float) $v),
                    'counts' => $data->pluck('count'),
                    'total_count' => $data->sum('count')
                ];

            case 'monthly':
            default:
                $data = $query->select(
                    DB::raw('YEAR(created_at) as year'),
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw('SUM(' . $amountField . ') as total'),
                    DB::raw('COUNT(*) as count')
                )
                    ->groupBy('year', 'month')
                    ->orderBy('year')
                    ->orderBy('month')
                    ->get();

                $labels = $data->map(function ($item) {
                    $date = Carbon::createFromDate($item->year, $item->month, 1);
                    return $date->format('M Y');
                });

                return [
                    'labels' => $labels,
                    'values' => $data->pluck('total')->map(fn($v) => (float) $v),
                    'counts' => $data->pluck('count'),
                    'total_count' => $data->sum('count')
                ];
        }
    }

    /**
     * Obtiene estadísticas de transacciones
     */
    private function getTransactionStats($startDate, $endDate, $userRole, $allyId, $exchangeRate, $selectedAllyId = null, $selectedZone = null, $selectedStatus = null, $selectedPaymentMethod = null)
    {
        $query = PaymentTransaction::whereBetween('created_at', [$startDate, $endDate]);

        // Aplicar filtros
        $query = $this->applyTransactionFilters($query, $userRole, $allyId, $selectedAllyId, $selectedZone, $selectedStatus, $selectedPaymentMethod);

        $totalAmount = $query->sum('amount_to_ally');
        $totalCommission = $query->sum('platform_commission');
        $totalCount = $query->count();
        
        $confirmedQuery = clone $query;
        $confirmedAmount = $confirmedQuery->where('status', 'confirmed')->sum('amount_to_ally');
        $confirmedCount = $confirmedQuery->where('status', 'confirmed')->count();

        $pendingQuery = clone $query;
        $pendingAmount = $pendingQuery->whereIn('status', ['pending_manual_confirmation', 'awaiting_review', 'pending_sms'])->sum('amount_to_ally');
        $pendingCount = $pendingQuery->whereIn('status', ['pending_manual_confirmation', 'awaiting_review', 'pending_sms'])->count();

        return [
            'total_amount' => $totalAmount,
            'total_amount_ves' => $totalAmount * $exchangeRate,
            'total_commission' => $totalCommission,
            'total_commission_ves' => $totalCommission * $exchangeRate,
            'total_count' => $totalCount,
            'confirmed_amount' => $confirmedAmount,
            'confirmed_amount_ves' => $confirmedAmount * $exchangeRate,
            'confirmed_count' => $confirmedCount,
            'pending_amount' => $pendingAmount,
            'pending_amount_ves' => $pendingAmount * $exchangeRate,
            'pending_count' => $pendingCount,
            'exchange_rate' => $exchangeRate
        ];
    }

    /**
     * Obtiene estadísticas de ventas (Sales)
     */
    private function getSalesStats($startDate, $endDate, $userRole, $allyId, $exchangeRate, $selectedAllyId = null, $selectedZone = null)
    {
        $query = Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->where('status', 'completed');

        // Aplicar filtros
        $query = $this->applySaleFilters($query, $userRole, $allyId, $selectedAllyId, $selectedZone);

        $totalAmount = $query->sum('total_amount');
        $totalCount = $query->count();
        $averageAmount = $totalCount > 0 ? $totalAmount / $totalCount : 0;

        // Clientes únicos
        $uniqueClients = $query->distinct('client_id')->count('client_id');

        // Métodos de pago más usados
        $paymentMethods = $query->select('payment_method', DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->orderBy('count', 'desc')
            ->get();

        return [
            'total_amount' => $totalAmount,
            'total_amount_ves' => $totalAmount * $exchangeRate,
            'total_count' => $totalCount,
            'average_amount' => $averageAmount,
            'average_amount_ves' => $averageAmount * $exchangeRate,
            'unique_clients' => $uniqueClients,
            'payment_methods' => $paymentMethods,
            'exchange_rate' => $exchangeRate
        ];
    }

    /**
     * Obtiene estadísticas de payouts
     */
    private function getPayoutStats($startDate, $endDate, $userRole, $allyId, $exchangeRate, $selectedAllyId = null, $selectedZone = null)
    {
        $query = Payout::whereBetween('created_at', [$startDate, $endDate]);

        // Aplicar filtros por aliado
        if (!$this->isAdmin($userRole) && $allyId) {
            $query->where('ally_id', $allyId);
        } elseif ($this->isAdmin($userRole) && $selectedAllyId) {
            $query->where('ally_id', $selectedAllyId);
        }

        // Filtro por zona
        if ($selectedZone) {
            $query->whereHas('ally', function ($q) use ($selectedZone) {
                $q->where('company_address', $selectedZone);
            });
        }

        $totalPayouts = $query->sum('net_amount');
        $totalCount = $query->count();
        
        $pendingAmount = (clone $query)->where('status', 'pending')->sum('net_amount');
        $pendingCount = (clone $query)->where('status', 'pending')->count();
        
        $completedAmount = (clone $query)->where('status', 'completed')->sum('net_amount');
        $completedCount = (clone $query)->where('status', 'completed')->count();

        return [
            'total_amount' => $totalPayouts,
            'total_amount_ves' => $totalPayouts * $exchangeRate,
            'total_count' => $totalCount,
            'pending_amount' => $pendingAmount,
            'pending_amount_ves' => $pendingAmount * $exchangeRate,
            'pending_count' => $pendingCount,
            'completed_amount' => $completedAmount,
            'completed_amount_ves' => $completedAmount * $exchangeRate,
            'completed_count' => $completedCount,
            'exchange_rate' => $exchangeRate
        ];
    }

    /**
     * Obtiene payouts pendientes
     */
    private function getPendingPayouts($userRole, $allyId, $selectedAllyId = null, $selectedZone = null)
    {
        $query = Payout::with(['ally', 'sale'])
            ->where('status', 'pending');

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

        return $query->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($payout) {
                return [
                    'id' => $payout->id,
                    'ally' => $payout->ally->company_name ?? 'N/A',
                    'amount' => $payout->net_amount,
                    'date' => $payout->created_at->format('d/m/Y'),
                    'sale_reference' => $payout->sale_reference
                ];
            });
    }

    /**
     * Obtiene métricas adicionales
     */
    private function getAdditionalMetrics($startDate, $endDate, $userRole, $allyId, $exchangeRate, $selectedAllyId = null, $selectedZone = null, $selectedStatus = null, $selectedPaymentMethod = null)
    {
        $isAdmin = $this->isAdmin($userRole);

        // Top aliado por volumen de transacciones
        $topAlly = null;
        if ($isAdmin) {
            $topAllyQuery = PaymentTransaction::whereBetween('created_at', [$startDate, $endDate])
                ->where('status', 'confirmed');

            if ($selectedZone) {
                $topAllyQuery->whereHas('ally', function ($q) use ($selectedZone) {
                    $q->where('company_address', $selectedZone);
                });
            }

            $topAlly = $topAllyQuery->select('ally_id', DB::raw('SUM(amount_to_ally) as total'))
                ->groupBy('ally_id')
                ->orderByDesc('total')
                ->first();

            if ($topAlly) {
                $ally = Ally::find($topAlly->ally_id);
                $topAlly->name = $ally->company_name ?? 'N/A';
                $topAlly->total_ves = $topAlly->total * $exchangeRate;
            }
        }

        // Transacción más grande
        $largestTransactionQuery = PaymentTransaction::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'confirmed');

        $largestTransactionQuery = $this->applyTransactionFilters($largestTransactionQuery, $userRole, $allyId, $selectedAllyId, $selectedZone, $selectedStatus, $selectedPaymentMethod);

        $largestTransaction = $largestTransactionQuery->orderByDesc('amount_to_ally')->first();
        $largestTransactionVes = $largestTransaction ? $largestTransaction->amount_to_ally * $exchangeRate : null;

        // Mejor día
        $bestDayQuery = PaymentTransaction::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'confirmed');

        $bestDayQuery = $this->applyTransactionFilters($bestDayQuery, $userRole, $allyId, $selectedAllyId, $selectedZone, $selectedStatus, $selectedPaymentMethod);

        $bestDay = $bestDayQuery->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(amount_to_ally) as daily_total')
            )
            ->groupBy('date')
            ->orderByDesc('daily_total')
            ->first();

        $bestDayVes = $bestDay ? $bestDay->daily_total * $exchangeRate : null;

        return [
            'top_ally' => $topAlly,
            'largest_transaction' => $largestTransaction,
            'largest_transaction_ves' => $largestTransactionVes,
            'best_day' => $bestDay,
            'best_day_ves' => $bestDayVes,
            'exchange_rate' => $exchangeRate,
            'is_admin' => $isAdmin
        ];
    }

    /**
     * Endpoint para datos AJAX del gráfico de transacciones
     */
    public function transactionsData(Request $request)
    {
        $startDate = $request->input('startDate', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('endDate', now()->format('Y-m-d'));
        $reportType = $request->input('reportType', 'monthly');
        $selectedAllyId = $request->input('ally_id');
        $selectedZone = $request->input('zone_id');
        $selectedStatus = $request->input('status', 'all');
        $selectedPaymentMethod = $request->input('payment_method', 'all');

        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        $user = auth()->user();
        $userRole = $user->role;
        $allyId = $this->getUserAllyId($user);

        $exchangeRateVes = $this->getExchangeRate();
        
        $transactionChartData = $this->getTransactionChartData($startDate, $endDate, $reportType, $userRole, $allyId, $selectedAllyId, $selectedZone, $selectedStatus, $selectedPaymentMethod);
        $transactionStats = $this->getTransactionStats($startDate, $endDate, $userRole, $allyId, $exchangeRateVes, $selectedAllyId, $selectedZone, $selectedStatus, $selectedPaymentMethod);

        return response()->json([
            'success' => true,
            'chart' => $transactionChartData,
            'stats' => $transactionStats,
            'exchange_rate' => $exchangeRateVes,
            'date_range' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
                'report_type' => $reportType
            ]
        ]);
    }

    /**
     * Endpoint para datos de ventas (Sales)
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

        $exchangeRateVes = $this->getExchangeRate();
        
        $salesChartData = $this->getSalesChartData($startDate, $endDate, $reportType, $userRole, $allyId, $selectedAllyId, $selectedZone);
        $salesStats = $this->getSalesStats($startDate, $endDate, $userRole, $allyId, $exchangeRateVes, $selectedAllyId, $selectedZone);

        return response()->json([
            'success' => true,
            'chart' => $salesChartData,
            'stats' => $salesStats,
            'exchange_rate' => $exchangeRateVes
        ]);
    }

    /**
     * Endpoint para datos de payouts
     */
    public function payoutsData(Request $request)
    {
        $startDate = $request->input('startDate', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('endDate', now()->format('Y-m-d'));
        $selectedAllyId = $request->input('ally_id');
        $selectedZone = $request->input('zone_id');

        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        $user = auth()->user();
        $userRole = $user->role;
        $allyId = $this->getUserAllyId($user);

        $exchangeRateVes = $this->getExchangeRate();
        
        $payoutStats = $this->getPayoutStats($startDate, $endDate, $userRole, $allyId, $exchangeRateVes, $selectedAllyId, $selectedZone);
        $pendingPayouts = $this->getPendingPayouts($userRole, $allyId, $selectedAllyId, $selectedZone);

        return response()->json([
            'success' => true,
            'stats' => $payoutStats,
            'pending' => $pendingPayouts,
            'exchange_rate' => $exchangeRateVes
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

        $exchangeRate = $this->getExchangeRate();

        // Función para aplicar filtros
        $applyFilters = function($query) use ($userRole, $allyId, $selectedAllyId, $selectedZone) {
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
        $todaySalesVes = $todaySales * $exchangeRate;

        // Ventas ayer
        $yesterdaySales = $applyFilters(Sale::whereDate('sale_date', $yesterday)
            ->where('status', 'completed'))->sum('total_amount');
        $yesterdaySalesVes = $yesterdaySales * $exchangeRate;

        // Crecimiento diario
        $dailyGrowth = $yesterdaySales > 0 ?
            (($todaySales - $yesterdaySales) / $yesterdaySales) * 100 : 0;

        // Ventas del mes
        $monthSales = $applyFilters(Sale::whereBetween('sale_date', [$thisMonth, $today])
            ->where('status', 'completed'))->sum('total_amount');
        $monthSalesVes = $monthSales * $exchangeRate;

        // Ventas mes anterior
        $lastMonthSales = $applyFilters(Sale::whereBetween('sale_date', [
            $lastMonth,
            $thisMonth->copy()->subDay()
        ])->where('status', 'completed'))->sum('total_amount');
        $lastMonthSalesVes = $lastMonthSales * $exchangeRate;

        // Crecimiento mensual
        $monthlyGrowth = $lastMonthSales > 0 ?
            (($monthSales - $lastMonthSales) / $lastMonthSales) * 100 : 0;

        // Transacciones hoy
        $todayTransactions = $applyFilters(PaymentTransaction::whereDate('created_at', $today))->count();
        
        // Payouts pendientes
        $pendingPayouts = $applyFilters(Payout::where('status', 'pending'))->sum('net_amount');
        $pendingPayoutsVes = $pendingPayouts * $exchangeRate;

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
                'today_transactions' => $todayTransactions,
                'pending_payouts' => $pendingPayouts,
                'pending_payouts_ves' => $pendingPayoutsVes,
                'exchange_rate' => $exchangeRate,
                'user_role' => $userRole
            ]
        ]);
    }

    /**
     * Exporta reporte a PDF
     */
    public function exportTransactions(Request $request)
    {
        try {
            $startDate = $request->input('startDate', now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('endDate', now()->format('Y-m-d'));
            $reportType = $request->input('reportType', 'monthly');
            $selectedAllyId = $request->input('ally_id');
            $selectedZone = $request->input('zone_id');
            $selectedStatus = $request->input('status', 'all');
            $selectedPaymentMethod = $request->input('payment_method', 'all');

            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();

            $user = auth()->user();
            $userRole = $user->role;
            $allyId = $this->getUserAllyId($user);

            $exchangeRateVes = $this->getExchangeRate();
            
            $transactionChartData = $this->getTransactionChartData($startDate, $endDate, $reportType, $userRole, $allyId, $selectedAllyId, $selectedZone, $selectedStatus, $selectedPaymentMethod);
            $transactionStats = $this->getTransactionStats($startDate, $endDate, $userRole, $allyId, $exchangeRateVes, $selectedAllyId, $selectedZone, $selectedStatus, $selectedPaymentMethod);
            $salesStats = $this->getSalesStats($startDate, $endDate, $userRole, $allyId, $exchangeRateVes, $selectedAllyId, $selectedZone);
            $transactions = $this->getTransactionsList($startDate, $endDate, $userRole, $allyId, $selectedAllyId, $selectedZone, $selectedStatus, $selectedPaymentMethod);

            $pdf = app('dompdf.wrapper');
            $pdf->setPaper('A4', 'landscape');

            $pdf->loadView('Admin.reportes.pdf.transactions-pdf', compact(
                'startDate',
                'endDate',
                'reportType',
                'transactionChartData',
                'transactionStats',
                'salesStats',
                'exchangeRateVes',
                'transactions',
                'userRole',
                'allyId',
                'selectedAllyId',
                'selectedZone',
                'selectedStatus',
                'selectedPaymentMethod'
            ));

            $filename = "reporte_transacciones_{$startDate->format('Y-m-d')}_a_{$endDate->format('Y-m-d')}.pdf";

            return $pdf->download($filename);
        } catch (\Exception $e) {
            Log::error('Error generando PDF: ' . $e->getMessage());
            return response()->json(['error' => 'Error al generar el PDF'], 500);
        }
    }

    /**
     * Vista previa del PDF
     */
    public function previewTransactions(Request $request)
    {
        try {
            $startDate = $request->input('startDate', now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('endDate', now()->format('Y-m-d'));
            $reportType = $request->input('reportType', 'monthly');
            $selectedAllyId = $request->input('ally_id');
            $selectedZone = $request->input('zone_id');
            $selectedStatus = $request->input('status', 'all');
            $selectedPaymentMethod = $request->input('payment_method', 'all');

            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();

            $user = auth()->user();
            $userRole = $user->role;
            $allyId = $this->getUserAllyId($user);

            $exchangeRateVes = $this->getExchangeRate();
            
            $transactionChartData = $this->getTransactionChartData($startDate, $endDate, $reportType, $userRole, $allyId, $selectedAllyId, $selectedZone, $selectedStatus, $selectedPaymentMethod);
            $transactionStats = $this->getTransactionStats($startDate, $endDate, $userRole, $allyId, $exchangeRateVes, $selectedAllyId, $selectedZone, $selectedStatus, $selectedPaymentMethod);
            $salesStats = $this->getSalesStats($startDate, $endDate, $userRole, $allyId, $exchangeRateVes, $selectedAllyId, $selectedZone);
            $transactions = $this->getTransactionsList($startDate, $endDate, $userRole, $allyId, $selectedAllyId, $selectedZone, $selectedStatus, $selectedPaymentMethod);

            $pdf = app('dompdf.wrapper');
            $pdf->setPaper('A4', 'landscape');

            $pdf->loadView('Admin.reportes.pdf.transactions-pdf', compact(
                'startDate',
                'endDate',
                'reportType',
                'transactionChartData',
                'transactionStats',
                'salesStats',
                'exchangeRateVes',
                'transactions',
                'userRole',
                'allyId',
                'selectedAllyId',
                'selectedZone',
                'selectedStatus',
                'selectedPaymentMethod'
            ));

            return $pdf->stream();
        } catch (\Exception $e) {
            Log::error('Error generando vista previa PDF: ' . $e->getMessage());
            return response()->json(['error' => 'Error al generar la vista previa'], 500);
        }
    }

    /**
     * Endpoint para obtener datos en tiempo real (últimas transacciones)
     */
    public function recentTransactions(Request $request)
    {
        $limit = $request->input('limit', 10);
        $selectedAllyId = $request->input('ally_id');
        $selectedZone = $request->input('zone_id');

        $user = auth()->user();
        $userRole = $user->role;
        $allyId = $this->getUserAllyId($user);

        $query = PaymentTransaction::with(['user', 'ally'])
            ->orderBy('created_at', 'desc')
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

        $exchangeRate = $this->getExchangeRate();

        $recentTransactions = $query->get()
            ->map(function ($transaction) use ($exchangeRate) {
                return [
                    'id' => $transaction->id,
                    'reference' => $transaction->reference_code,
                    'user' => $transaction->user->name ?? 'N/A',
                    'ally' => $transaction->ally->company_name ?? 'Pago Directo',
                    'amount' => $transaction->amount_to_ally,
                    'amount_ves' => $transaction->amount_to_ally * $exchangeRate,
                    'date' => $transaction->created_at->format('d/m/Y H:i'),
                    'payment_method' => $this->formatPaymentMethod($transaction->payment_method),
                    'status' => $this->formatStatus($transaction->status),
                    'exchange_rate' => $exchangeRate
                ];
            });

        return response()->json([
            'success' => true,
            'recent_transactions' => $recentTransactions,
            'exchange_rate' => $exchangeRate
        ]);
    }

    /**
     * Formatea método de pago
     */
    private function formatPaymentMethod($method)
    {
        return match ($method) {
            'pago_movil' => '📱 Pago Móvil',
            'tarjeta_credito' => '💳 Tarjeta de Crédito',
            'debito_inmediato' => '🏦 Débito Inmediato',
            default => $method ?? 'N/A'
        };
    }

    /**
     * Formatea estado
     */
    private function formatStatus($status)
    {
        return match ($status) {
            'confirmed' => '✅ Confirmada',
            'awaiting_review' => '⏳ En Revisión',
            'pending_manual_confirmation' => '⌛ Pendiente',
            'pending_sms' => '📱 Esperando SMS',
            'pending_confirmation' => '⏰ Por Confirmar',
            'failed' => '❌ Fallida',
            default => $status ?? 'N/A'
        };
    }
}
