<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Sale;
use App\Models\Ally;
use App\Models\ActivityLog;
use App\Models\CommercialAlly;
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
        $role = $user->role ?: 'comun';

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
        // Contar usuarios totales (sin contar administradores)
        $totalUsers = User::where('role', '!=', 'admin')->count();

        // Contar aliados (perfiles de negocio)
        $totalAllies = Ally::count();

        // Ventas totales (suma de ventas completadas)
        $totalSales = Sale::whereIn('status', ['completed', 'completado'])->sum('total_amount');

        // Visitas a la página web (registros en activity_log con acción específica)
        $pageViews = ActivityLog::where(function ($query) {
            $query->where('description', 'LIKE', '%visita%')
                ->orWhere('description', 'LIKE', '%página%')
                ->orWhere('description', 'LIKE', '%acceso%');
        })->count();

        // Valoración media de los aliados comerciales (0-5)
        $ratingPromedio = round((float) CommercialAlly::avg('rating'), 1);

        // Datos específicos según el rol
        if ($role === 'admin') {
            // El admin ve datos globales

            // Ventas de hoy
            $todaySales = Sale::whereDate('created_at', Carbon::today())
                ->whereIn('status', ['completed', 'completado'])
                ->sum('total_amount');

            // Usuarios registrados hoy (sin administradores)
            $todayUsers = User::where('role', '!=', 'admin')
                ->whereDate('created_at', Carbon::today())
                ->count();

            // Aliados registrados hoy (usuarios con rol aliado)
            $todayAllies = User::where('role', 'aliado')
                ->whereDate('created_at', Carbon::today())
                ->count();

            // Visitas de hoy
            $todayViews = ActivityLog::whereDate('created_at', Carbon::today())
                ->where('description', 'LIKE', '%visita%')
                ->count();

        } elseif ($role === 'aliado') {
            // El aliado ve datos relacionados a su negocio
            
            // Obtener el objeto Ally del usuario autenticado
            $ally = Ally::where('user_id', $user->id)->first();
            $allyId = $ally ? $ally->id : null;

            // Ventas del aliado (a través de la tabla sales con ally_id)
            $todaySales = Sale::where('ally_id', $allyId)
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

        } else {
            // Usuario normal
            $todaySales = 0;
            $todayUsers = 0;
            $todayAllies = 0;
            $todayViews = ActivityLog::where('user_id', $user->id)
                ->whereDate('created_at', Carbon::today())
                ->count();
        }

        $customerSatisfaction = $ratingPromedio > 0
            ? (int) round($ratingPromedio * 20)
            : 0;

        return [
            'totalUsers' => $totalUsers,
            'totalAllies' => $totalAllies,
            'totalSales' => $totalSales,
            'pageViews' => $pageViews,
            'todaySales' => $todaySales ?? 0,
            'todayUsers' => $todayUsers ?? 0,
            'todayAllies' => $todayAllies ?? 0,
            'todayViews' => $todayViews ?? 0,
            'customerSatisfaction' => $customerSatisfaction,
            'ratingPromedio' => $ratingPromedio,
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
                    'type' => $activity->activity_type ?? '',
                    'user' => $activity->user->name ?? 'Sistema',
                    'date' => Carbon::parse($activity->created_at)->format('d/m/Y H:i'),
                    'status' => ucfirst($activity->status ?? 'info'),
                    'status_class' => $statusClass,
                    'icon' => $this->getActivityIcon($activity->activity_type ?? '', $activity->description ?? ''),
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
                $query->where('role', 'aliado');
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
            : 0;

        // Distribución real de usuarios por rol
        $roleCounts = [
            'admin' => User::where('role', 'admin')->count(),
            'aliado' => User::where('role', 'aliado')->count(),
            'afiliado' => User::where('role', 'afiliado')->count(),
            'comun' => User::where(function ($query) {
                $query->whereIn('role', ['comun', 'user'])->orWhereNull('role');
            })->count(),
        ];

        return [
            'activeUsersToday' => $activeUsersToday,
            'alliesOnline' => $alliesOnline,
            'salesToday' => $salesToday,
            'salesGrowth' => $salesGrowth,
            'roleCounts' => $roleCounts,
            'ratingPromedio' => round((float) CommercialAlly::avg('rating'), 1),
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
    private function getActivityIcon($type, $description = '')
    {
        $haystack = mb_strtolower($type ?: $description);

        return match (true) {
            str_contains($haystack, 'login') => 'fa-right-to-bracket',
            str_contains($haystack, 'logout') => 'fa-right-from-bracket',
            str_contains($haystack, 'usuario') => str_contains($haystack, 'created')
                ? 'fa-user-plus'
                : (str_contains($haystack, 'deleted') ? 'fa-user-minus' : 'fa-user-pen'),
            str_contains($haystack, 'aliado') => 'fa-store',
            str_contains($haystack, 'promocion') => 'fa-tag',
            str_contains($haystack, 'banner') => 'fa-image',
            str_contains($haystack, 'pago') || str_contains($haystack, 'payout') => 'fa-money-bill-transfer',
            str_contains($haystack, 'solicitud') => 'fa-envelope-open-text',
            str_contains($haystack, 'settings') || str_contains($haystack, 'configuracion') => 'fa-gear',
            str_contains($haystack, 'visit') => 'fa-globe',
            default => 'fa-circle-info',
        };
    }

    /**
     * Obtener clase CSS para el estado
     */
    private function getStatusClass($status)
    {
        return match ($status) {
            'success', 'completed', 'confirmed', 'activo' => 'success',
            'pending', 'pending_manual_confirmation', 'awaiting_review', 'warning' => 'warning',
            'failed', 'rejected', 'error' => 'danger',
            default => 'secondary'
        };
    }
}
