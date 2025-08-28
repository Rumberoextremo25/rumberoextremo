<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Sale;
use App\Models\User;
use App\Models\Order;
use App\Models\Ally;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\PageVisit;

class AdminController extends Controller
{
    public function __construct()
    {
        //$this->middleware('auth'); // Puedes aplicar el middleware a todo el controlador
    }

    public function index()
    {
        $user = Auth::user();
        $role = $user->user_type;

        $totalUsers = User::count();
        $totalAllies = Ally::count();
        $totalSales = Sale::sum('total');

        // Obtener el número total de visitas desde tu tabla 'page_visits'
        $pageViews = PageVisit::sum('visits_count');

        // Lógica para obtener las últimas actividades según el rol del usuario
        $activityQuery = ActivityLog::latest()->limit(10);

        if ($role !== 'admin') {
            $activityQuery->where('user_id', $user->id);
        }

        $latestActivities = $activityQuery->get()
            ->map(fn($activity) => [
                'activity' => $activity->description,
                'user' => optional($activity->user)->name ?? 'N/A',
                'date' => Carbon::parse($activity->created_at)->format('d/m/Y H:i'),
                'status' => ucfirst($activity->status),
                'status_class' => $this->getStatusClass($activity->status)
            ]);

        $todaySalesSpecific = 0.00;
        $customerSatisfaction = 0;

        if ($role === 'admin') {
            $todaySalesSpecific = Sale::whereDate('sale_date', Carbon::today())->sum('total');
            $customerSatisfaction = 92;
        } elseif ($role === 'aliado') {
            $todaySalesSpecific = Sale::whereDate('sale_date', Carbon::today())
                ->whereHas('product', fn($query) => $query->where('user_id', $user->id))
                ->sum('total');
            $customerSatisfaction = 88;
        }

        return view('dashboard', compact(
            'totalUsers',
            'pageViews',
            'totalSales',
            'latestActivities',
            'todaySalesSpecific',
            'customerSatisfaction',
            'totalAllies'
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
