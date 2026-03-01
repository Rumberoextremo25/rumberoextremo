<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Sale;
use App\Models\ActivityLog;
use Carbon\Carbon;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    /**
     * ========== MÉTODOS DEL DASHBOARD ==========
     */
    public function index()
    {
        $user = Auth::user();
        $role = $user->user_type;

        // Datos del dashboard
        $dashboardData = $this->getDashboardData($user, $role);
        $latestActivities = $this->getLatestActivities($user, $role);

        // Estadísticas adicionales para el resumen rápido
        $additionalStats = $this->getAdditionalStats();

        return view('dashboard', array_merge(
            $dashboardData,
            [
                'latestActivities' => $latestActivities,
                'additionalStats' => $additionalStats
            ]
        ));
    }

    /**
     * Obtener datos del dashboard
     */
    private function getDashboardData($user, $role)
    {
        // Contar usuarios totales (excluyendo admin si es necesario)
        $totalUsers = User::where('user_type', '!=', 'admin')->count();

        // Contar aliados (usuarios con user_type = 'aliado' o registros en tabla allies)
        $totalAllies = User::where('user_type', 'aliado')->count();

        // Ventas totales (suma de ventas completadas)
        $totalSales = Sale::whereIn('status', ['completed', 'completado'])->sum('total_amount');

        // Visitas a la página web (registros en activity_log con acción específica)
        $pageViews = ActivityLog::where('description', 'LIKE', '%visita%')
            ->orWhere('description', 'LIKE', '%página%')
            ->orWhere('description', 'LIKE', '%acceso%')
            ->count();

        // Datos específicos según el rol
        if ($role === 'admin') {
            // El admin ve datos globales

            // Ventas de hoy
            $todaySales = Sale::whereDate('created_at', Carbon::today())
                ->whereIn('status', ['completed', 'completado'])
                ->sum('total_amount');

            // Usuarios registrados hoy
            $todayUsers = User::whereDate('created_at', Carbon::today())->count();

            // Aliados registrados hoy
            $todayAllies = User::where('user_type', 'aliado')
                ->whereDate('created_at', Carbon::today())
                ->count();

            // Visitas de hoy
            $todayViews = ActivityLog::whereDate('created_at', Carbon::today())
                ->where('description', 'LIKE', '%visita%')
                ->count();

            $customerSatisfaction = 92; // Ejemplo, puedes calcularlo de otra forma

        } elseif ($role === 'aliado') {
            // El aliado ve datos relacionados a su negocio

            // Ventas del aliado (a través de la tabla sales con ally_id)
            $todaySales = Sale::where('ally_id', $user->id)
                ->whereDate('created_at', Carbon::today())
                ->whereIn('status', ['completed', 'completado'])
                ->sum('total_amount');

            // Visitas a su perfil o productos
            $todayViews = ActivityLog::where('user_id', $user->id)
                ->whereDate('created_at', Carbon::today())
                ->where('description', 'LIKE', '%perfil%')
                ->count();

            $todayUsers = 0; // Los aliados no ven usuarios registrados
            $todayAllies = 0;
            $customerSatisfaction = 88; // Valor de ejemplo

        } else {
            // Usuario normal
            $todaySales = 0;
            $todayUsers = 0;
            $todayAllies = 0;
            $todayViews = ActivityLog::where('user_id', $user->id)
                ->whereDate('created_at', Carbon::today())
                ->count();
            $customerSatisfaction = 0;
        }

        return [
            'totalUsers' => $totalUsers,
            'totalAllies' => $totalAllies,
            'totalSales' => $totalSales,
            'pageViews' => $pageViews,
            'todaySales' => $todaySales ?? 0,
            'todayUsers' => $todayUsers ?? 0,
            'todayAllies' => $todayAllies ?? 0,
            'todayViews' => $todayViews ?? 0,
            'customerSatisfaction' => $customerSatisfaction ?? 0,
        ];
    }

    /**
     * Obtener actividades recientes
     */
    private function getLatestActivities($user, $role)
    {
        // Query base para actividades
        $activityQuery = ActivityLog::with('user')
            ->latest()
            ->limit(15);

        // Si no es admin, filtrar por usuario
        if ($role !== 'admin') {
            $activityQuery->where('user_id', $user->id);
        }

        return $activityQuery->get()
            ->map(function ($activity) {
                // Determinar el tipo de actividad para el icono
                $statusClass = $this->getStatusClass($activity->status ?? 'info');

                // Formatear la descripción para mostrarla más amigable
                $description = $this->formatActivityDescription($activity->description, $activity->details);

                return [
                    'activity' => $description,
                    'user' => $activity->user->name ?? 'Sistema',
                    'date' => Carbon::parse($activity->created_at)->format('d/m/Y H:i'),
                    'status' => ucfirst($activity->status ?? 'info'),
                    'status_class' => $statusClass,
                    'icon' => $this->getActivityIcon($activity->description),
                ];
            });
    }

    /**
     * Obtener estadísticas adicionales para el resumen rápido
     */
    private function getAdditionalStats()
    {
        // Usuarios activos hoy (últimos 30 minutos)
        $activeUsersToday = ActivityLog::whereDate('created_at', Carbon::today())
            ->where('created_at', '>=', Carbon::now()->subMinutes(30))
            ->distinct('user_id')
            ->count('user_id');

        // Aliados en línea (últimos 30 minutos)
        $alliesOnline = ActivityLog::whereDate('created_at', Carbon::today())
            ->where('created_at', '>=', Carbon::now()->subMinutes(30))
            ->whereHas('user', function ($query) {
                $query->where('user_type', 'aliado');
            })
            ->distinct('user_id')
            ->count('user_id');

        // Ventas de hoy
        $salesToday = Sale::whereDate('created_at', Carbon::today())
            ->whereIn('status', ['completed', 'completado'])
            ->sum('total_amount');

        // Comparativas con el mes anterior
        $lastMonthSales = Sale::whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->whereIn('status', ['completed', 'completado'])
            ->sum('total_amount');

        $salesGrowth = $lastMonthSales > 0
            ? round((($salesToday * 30) - $lastMonthSales) / $lastMonthSales * 100, 1)
            : 100;

        // Progreso de metas (ejemplo)
        $userGoal = 1000;
        $allyGoal = 100;
        $salesGoal = 1000000;

        $userProgress = min(round(($this->getDashboardData(Auth::user(), Auth::user()->user_type)['totalUsers'] / $userGoal) * 100, 0), 100);
        $allyProgress = min(round(($this->getDashboardData(Auth::user(), Auth::user()->user_type)['totalAllies'] / $allyGoal) * 100, 0), 100);
        $salesProgress = min(round(($this->getDashboardData(Auth::user(), Auth::user()->user_type)['totalSales'] / $salesGoal) * 100, 0), 100);

        return [
            'activeUsersToday' => $activeUsersToday,
            'alliesOnline' => $alliesOnline,
            'salesToday' => $salesToday,
            'salesGrowth' => $salesGrowth,
            'userGoal' => $userGoal,
            'allyGoal' => $allyGoal,
            'salesGoal' => $salesGoal,
            'userProgress' => $userProgress,
            'allyProgress' => $allyProgress,
            'salesProgress' => $salesProgress,
        ];
    }

    /**
     * Formatear descripción de actividad para hacerla más amigable
     */
    private function formatActivityDescription($description, $details = null)
    {
        $commonPhrases = [
            'visit' => 'Visitó la página',
            'login' => 'Inició sesión',
            'logout' => 'Cerró sesión',
            'create' => 'Creó un nuevo',
            'update' => 'Actualizó',
            'delete' => 'Eliminó',
            'view' => 'Visualizó',
            'purchase' => 'Realizó una compra',
            'payment' => 'Realizó un pago',
            'register' => 'Se registró en',
        ];

        foreach ($commonPhrases as $key => $phrase) {
            if (stripos($description, $key) !== false) {
                return $phrase . ' ' . str_replace($key, '', $description);
            }
        }

        return $description;
    }

    /**
     * Obtener icono según el tipo de actividad
     */
    private function getActivityIcon($description)
    {
        if (stripos($description, 'login') !== false) return 'fa-sign-in-alt';
        if (stripos($description, 'logout') !== false) return 'fa-sign-out-alt';
        if (stripos($description, 'create') !== false) return 'fa-plus-circle';
        if (stripos($description, 'update') !== false) return 'fa-edit';
        if (stripos($description, 'delete') !== false) return 'fa-trash';
        if (stripos($description, 'view') !== false) return 'fa-eye';
        if (stripos($description, 'purchase') !== false) return 'fa-shopping-cart';
        if (stripos($description, 'payment') !== false) return 'fa-credit-card';
        if (stripos($description, 'visit') !== false) return 'fa-globe';
        return 'fa-info-circle';
    }

    /**
     * Obtener clase CSS para el estado
     */
    private function getStatusClass($status)
    {
        return match ($status) {
            'completed', 'confirmed' => 'success',
            'pending', 'pending_manual_confirmation', 'awaiting_review' => 'warning',
            'failed', 'rejected' => 'danger',
            default => 'secondary'
        };
    }
}
