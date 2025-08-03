<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Sale;
use App\Models\User;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\PageView; // Import the PageView model

class AdminController extends Controller
{
    public function __construct()
    {
        //$this->middleware('auth'); // Puedes aplicar el middleware a todo el controlador
    }

    public function index()
    {
        $user = Auth::user(); // Obtiene el usuario autenticado
        $role = $user->user_type; // Obtiene el rol del usuario

        // --- 1. Conteo de Visitas (PageViews) ---
        // Incrementa la vista para la página del dashboard.
        // firstOrCreate encontrará el registro por 'path' o lo creará si no existe.
        PageView::firstOrCreate(['path' => '/dashboard'])
                ->increment('views');

        // Obtiene el total de visitas de la página del dashboard.
        // Si quisieras el total de *todas* las vistas en el sistema, usarías PageView::sum('views') ?? 0;
        $pageViews = PageView::where('path', '/dashboard')->value('views') ?? 0;

        // --- Métricas Globales (visibles para todos los roles, si tu vista lo permite) ---
        $totalUsers = User::count();
        $totalSales = Sale::sum('total'); // Suma total de todas las ventas en el sistema

        // --- Inicialización de variables específicas de rol ---
        $latestActivities = [];          // Para actividades generales del sistema o del aliado
        $latestProfileActivities = [];   // Para actividades específicas del usuario común
        $todaySalesSpecific = 0.00;      // Ventas de hoy (filtradas por rol)
        $customerSatisfaction = 0;       // Satisfacción del cliente (dummy o calculada por rol)


        // --- Lógica basada en el Rol del Usuario ---
        if ($role === 'admin') {
            // Métricas específicas para el Administrador
            $todaySalesSpecific = Sale::whereDate('sale_date', Carbon::today())->sum('total');
            $customerSatisfaction = 92; // Valor de ejemplo o calculado para el admin

            // Obtén las últimas 10 actividades para el Admin (todas las actividades del sistema)
            $latestActivities = ActivityLog::latest()
                ->limit(10)
                ->get()
                ->map(fn($activity) => [
                    'activity' => $activity->description,
                    // Intenta obtener el nombre del usuario relacionado, o usa 'performed_by' como fallback
                    'user' => $activity->user->name ?? $activity->performed_by ?? 'N/A',
                    'date' => Carbon::parse($activity->created_at)->format('d/m/Y H:i'),
                    'status' => ucfirst($activity->status),
                    'status_class' => $this->getStatusClass($activity->status)
                ]);

        } elseif ($role === 'aliado') {
            // Métricas específicas para el Aliado
            $todaySalesSpecific = Sale::whereDate('sale_date', Carbon::today())
                ->whereHas('product', function ($query) use ($user) {
                    // Filtra por productos que pertenecen a este aliado
                    // Asume que tu modelo Product tiene un 'user_id' que es el ID del aliado
                    $query->where('user_id', $user->id);
                })
                ->sum('total');
            $customerSatisfaction = 88; // Valor de ejemplo o calculado para el aliado

            // Últimas 10 actividades relevantes SOLO para este Aliado (filtrando por su user_id)
            $latestActivities = ActivityLog::where('user_id', $user->id)
                ->latest()
                ->limit(10)
                ->get()
                ->map(fn($activity) => [
                    'activity' => $activity->description,
                    'user' => $user->firstname ?? $user->name, // El usuario es el propio aliado
                    'date' => Carbon::parse($activity->created_at)->format('d/m/Y H:i'),
                    'status' => ucfirst($activity->status),
                    'status_class' => $this->getStatusClass($activity->status)
                ]);

        } elseif ($role === 'comun') {
            // USUARIO COMÚN: Generalmente, no tiene cards de "Ventas de Hoy" o "Satisfacción del Cliente".
            // Las métricas globales ($totalUsers, $pageViews, $totalSales) se pueden mostrar si aplica.

            // Obtén las últimas 10 actividades relacionadas directamente con este Usuario Común (su propio perfil)
            $latestProfileActivities = ActivityLog::where('user_id', $user->id)
                ->latest()
                ->limit(10)
                ->get()
                ->map(fn($activity) => [
                    'activity' => $activity->description,
                    'user' => $user->firstname ?? $user->name, // El usuario es él mismo
                    'date' => Carbon::parse($activity->created_at)->format('d/m/Y H:i'),
                    'status' => ucfirst($activity->status),
                    'status_class' => $this->getStatusClass($activity->status)
                ]);
        }

        // --- Retorna la vista con todas las variables necesarias ---
        // Blade se encargará de mostrar solo las variables que existan y estén diseñadas para cada rol.
        return view('dashboard', compact(
            'totalUsers',
            'pageViews',
            'totalSales',
            'latestActivities',
            'latestProfileActivities',
            'todaySalesSpecific',
            'customerSatisfaction'
        ));
    }

    /**
     * Helper method to get status class for activities.
     * This method would typically be defined within the controller or a trait.
     */
    private function getStatusClass(string $status): string
    {
        return match ($status) {
            'success' => 'bg-green-100 text-green-800',
            'pending' => 'bg-yellow-100 text-yellow-800',
            'failed' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
    public function reports(Request $request)
    {
        // Obtener fechas del request o usar valores por defecto
        $startDate = $request->input('startDate', Carbon::now()->startOfYear()->toDateString());
        $endDate = $request->input('endDate', Carbon::now()->endOfYear()->toDateString());

        // Obtener datos de ventas por mes
        $salesDataRaw = Order::selectRaw('
                DATE_FORMAT(created_at, "%Y-%m") as month,
                SUM(total) as total_sales
            ')
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $labels = [];
        $data = [];

        // Generar un rango de meses para asegurar que todos los meses en el rango estén en las etiquetas,
        // incluso si no hay ventas en ellos.
        $start = Carbon::parse($startDate)->startOfMonth();
        $end = Carbon::parse($endDate)->endOfMonth();

        while ($start->lte($end)) {
            $monthKey = $start->format('Y-m');
            $labels[] = $start->translatedFormat('M Y'); // Ene 2024, Feb 2024
            $data[$monthKey] = 0; // Inicializar en 0
            $start->addMonth();
        }

        // Llenar los datos con las ventas reales
        foreach ($salesDataRaw as $sale) {
            $data[$sale->month] = $sale->total_sales;
        }

        // Convertir el array asociativo a un array indexado para Chart.js
        $chartData = array_values($data);

        return view('Admin.reportes', compact('labels', 'chartData', 'startDate', 'endDate'));
    }

    public function settings()
    {
        $user = Auth::user();

        $darkModeEnabled = $user->dark_mode_enabled ?? false; 
        
        return view('Admin.settings', compact('user', 'darkModeEnabled'));
    }
    public function changePassword(Request $request)
    {
        $user = Auth::user();

        // 1. Validar las credenciales
        try {
            $request->validate([
                'current_password' => ['required', 'string', function ($attribute, $value, $fail) use ($user) {
                    if (!Hash::check($value, $user->password)) {
                        $fail('La contraseña actual es incorrecta.');
                    }
                }],
                'new_password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
            ], [
                'current_password.required' => 'Debes ingresar tu contraseña actual.',
                'new_password.required' => 'Debes ingresar una nueva contraseña.',
                'new_password.min' => 'La nueva contraseña debe tener al menos :min caracteres.',
                'new_password.confirmed' => 'La confirmación de la nueva contraseña no coincide.',
                'new_password.different' => 'La nueva contraseña no puede ser igual a la actual.',
            ]);
        } catch (ValidationException $e) {
            return back()->withErrors($e->validator->errors())->withInput();
        }

        // 2. Actualizar la contraseña
        $user->password = Hash::make($request->input('new_password'));
        $user->save();

        // 3. Opcional: Re-autenticar al usuario para invalidar sesiones antiguas
        // Auth::guard('web')->logoutOtherDevices($request->input('new_password'));

        return back()->with('success', '¡Tu contraseña ha sido cambiada exitosamente!');
    }
}
