<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Ally;
use App\Models\AllyType;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Models\Order;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\PDF;
use Dotenv\Exception\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Constructor del controlador.
     * Puedes aplicar middleware aquí también si lo prefieres para todo el controlador.
     */
    public function __construct()
    {
        //$this->middleware('auth'); // Puedes aplicar el middleware a todo el controlador
    }

    // Métodos para el apartado de Admin general
    public function index()
    {
        $user = Auth::user(); // Obtiene el usuario autenticado
        $role = $user->role; // Obtiene el rol del usuario

        // Inicializa todas las variables que tu vista espera.
        // Esto es crucial para evitar errores "Undefined variable" en Blade.
        $totalUsers = 0;
        $totalActiveProducts = 0;
        $todaySales = 0.00;
        $customerSatisfaction = 0;
        $latestActivities = [];       // Actividades generales (para admin y aliado)
        $latestProfileActivities = []; // Actividades solo para el usuario 'comun'

        // --- Lógica condicional basada en el rol del usuario ---
        if ($role === 'admin') {
            // **ADMIN:** Ve todas las cards y todas las actividades
            $totalUsers = User::count();
            // Asume que tienes un modelo Product y una columna 'status'
            $totalActiveProducts = Product::where('status', 'active')->count();
            // ASIGNACIÓN CORRECTA: Obtiene las ventas de hoy desde la tabla 'ventas' (modelo Sale)
            $todaySales = Sale::whereDate('sale_date', Carbon::today())->sum('total');
            // La satisfacción del cliente podría ser un cálculo más complejo o un valor fijo
            $customerSatisfaction = 92; // Ejemplo: Podría venir de una tabla de configuración o promediar valoraciones

            // Obtén las últimas 10 actividades para el Admin (todas las actividades del sistema)
            $latestActivities = ActivityLog::latest()
                ->limit(10)
                ->get()
                ->map(fn($activity) => [
                    'activity' => $activity->description,
                    'user' => $activity->performed_by, // Asume que ActivityLog tiene un campo 'performed_by'
                    'date' => Carbon::parse($activity->created_at)->format('Y-m-d H:i'),
                    'status' => ucfirst($activity->status),
                    'status_class' => $this->getStatusClass($activity->status) // Usa un helper
                ]);

        } elseif ($role === 'aliado') {
            // **ALIADO:** Ve solo 'Ventas Hoy' y 'Satisfacción Cliente' en las cards, y actividades generales
            // Asume que puedes filtrar órdenes por un 'aliado_id' o similar
            // Puedes ajustar esta lógica para que muestre ventas y satisfacción SOLO para los productos o eventos de ESE aliado
            // ASIGNACIÓN CORRECTA: Obtiene las ventas de hoy para el aliado desde la tabla 'ventas' (modelo Sale)
            $todaySales = Sale::whereDate('sale_date', Carbon::today())
                                ->whereHas('product', function ($query) use ($user) {
                                    $query->where('aliado_id', $user->id); // Asume que hay una relación Product->Aliado en el modelo Sale
                                })
                                ->sum('total');

            // La satisfacción del cliente para este aliado
            $customerSatisfaction = 88; // Ejemplo: Podría venir de las valoraciones de sus productos/eventos

            // Obtén las últimas 10 actividades relevantes para el Aliado (ej. sus propios productos, sus ventas)
            $latestActivities = ActivityLog::where('aliado_id', $user->id) // Asume que ActivityLog tiene un campo 'aliado_id'
                ->latest()
                ->limit(10)
                ->get()
                ->map(fn($activity) => [
                    'activity' => $activity->description,
                    'user' => $activity->performed_by,
                    'date' => Carbon::parse($activity->created_at)->format('Y-m-d H:i'),
                    'status' => ucfirst($activity->status),
                    'status_class' => $this->getStatusClass($activity->status)
                ]);

        } elseif ($role === 'comun') {
            // **USUARIO COMÚN:** No ve las cards, solo actividades relacionadas a su perfil
            // Obtén las últimas 10 actividades relacionadas directamente con este usuario
            // Asegúrate de que tu modelo ActivityLog tenga una relación o una forma de filtrar por usuario
            $latestProfileActivities = ActivityLog::where('user_id', $user->id) // Asume que ActivityLog tiene un campo 'user_id'
                ->latest()
                ->limit(10)
                ->get()
                ->map(fn($activity) => [
                    'activity' => $activity->description,
                    'user' => $user->name, // El usuario es él mismo
                    'date' => Carbon::parse($activity->created_at)->format('Y-m-d H:i'),
                    'status' => ucfirst($activity->status),
                    'status_class' => $this->getStatusClass($activity->status)
                ]);
            // Asegúrate de que $latestActivities esté vacío para el usuario común, ya que no se usará
            $latestActivities = [];
        }

        // Retorna la vista con todas las variables necesarias.
        // Las variables no usadas por un rol específico simplemente serán nulas o 0,
        // lo cual es manejado por los @if en la vista.
        return view('dashboard', compact(
            'totalUsers',
            'totalActiveProducts',
            'todaySales',
            'customerSatisfaction',
            'latestActivities',
            'latestProfileActivities' // Nueva variable para usuarios comunes
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

        $notificationsEnabled = $user->notifications_enabled ?? true; // Las notificaciones suelen estar activas por defecto
        $darkModeEnabled = $user->dark_mode_enabled ?? false;

        // 3. Pasar estas variables a la vista.
        return view('Admin.settings', compact( 'notificationsEnabled', 'darkModeEnabled'));
    }

    public function updateDarkMode(Request $request)
    {
        $user = Auth::user();

        // Valida que el 'enabled' sea un booleano (true/false)
        $request->validate([
            'enabled' => 'required|boolean',
        ]);

        // Actualiza la columna en la tabla de usuarios
        $user->dark_mode_enabled = $request->input('enabled');
        $user->save();

        return response()->json(['message' => 'Preferencia de modo oscuro actualizada.', 'dark_mode_enabled' => $user->dark_mode_enabled]);
    }

    public function updateNotificationsPreference(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'enabled' => 'required|boolean',
        ]);

        $user->notifications_enabled = $request->input('enabled');
        $user->save();

        return response()->json(['message' => 'Preferencia de notificaciones actualizada.', 'notifications_enabled' => $user->notifications_enabled]);
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
            return back()->withErrors($e->errors())->withInput();
        }

        // 2. Actualizar la contraseña
        $user->password = Hash::make($request->input('new_password'));
        $user->save();

        // 3. Opcional: Re-autenticar al usuario para invalidar sesiones antiguas
        // Auth::guard('web')->logoutOtherDevices($request->input('new_password'));

        return back()->with('success', '¡Tu contraseña ha sido cambiada exitosamente!');
    }

    // Métodos para Aliados
    public function aliadosIndex()
    {
        $allies = Ally::all();
        return view('Admin.aliado.aliado', compact('allies'));
    }

    public function aliadosCreate()
    {
        $allyTypes = AllyType::where('is_active', true)->orderBy('name')->get();

        return view('Admin.aliado.create', compact('allyTypes'));
    }

    public function storeAlly(Request $request)
    {
        // 1. Valida los datos de la solicitud
        $validatedData = $request->validate([
            'company_name'      => 'required|string|max:255|unique:allies,company_name',
            'company_rif'       => 'nullable|string|max:255|unique:allies,company_rif',
            'service_category'  => 'required|string|max:255',
            'status'            => ['required', Rule::in(['activo', 'pendiente', 'inactivo'])],
            'contact_person_name' => 'required|string|max:255',
            'contact_email'     => 'required|email|max:255|unique:allies,contact_email',
            'contact_phone'     => 'required|string|max:20',
            'contact_phone_alt' => 'nullable|string|max:20',
            'company_address'   => 'nullable|string|max:500',
            'website_url'       => 'nullable|url|max:255',
            'discount'          => 'nullable|numeric|max:500', // Agregado 'numeric' para coincidir con el cast en el modelo
            'notes'             => 'nullable|string',
            'registered_at'     => 'required|date',
            // Si user_id se pasa desde un campo oculto o seleccionable en el formulario, validarlo:
            // 'user_id'           => 'nullable|exists:users,id',
        ], [
            // Mensajes de validación personalizados
            'company_name.required'             => 'El nombre de la empresa es obligatorio.',
            'company_name.unique'               => 'Ya existe un aliado con este nombre.',
            'company_rif.unique'                => 'Ya existe un aliado con este RIF.',
            'service_category.required'         => 'Debe seleccionar un tipo de aliado.',
            'status.required'                   => 'Debe seleccionar un estado para el aliado.',
            'contact_person_name.required'      => 'La persona de contacto es obligatoria.',
            'contact_email.required'            => 'El correo electrónico de contacto es obligatorio.',
            'contact_email.email'               => 'Ingrese un correo electrónico válido.',
            'contact_email.unique'              => 'Ya existe un aliado con este correo electrónico.',
            'contact_phone.required'            => 'El teléfono principal es obligatorio.',
            'registered_at.required'            => 'La fecha de registro es obligatoria.',
            'registered_at.date'                => 'Ingrese una fecha de registro válida.',
            'website_url.url'                   => 'El sitio web debe ser una URL válida.',
            'discount.numeric'                  => 'El descuento debe ser un valor numérico.',
        ]);

        $validatedData['user_id'] = auth()->id(); // Asigna el ID del usuario autenticado

        $ally = Ally::create($validatedData);

        // 3. Redirige al usuario a la vista de listado de aliados con un mensaje de éxito
        return redirect()->route('aliado')->with('success', '¡Aliado añadido exitosamente!');
    }

    public function alliesEdit(Ally $ally) // Laravel's Route Model Binding
    {
        $allyTypes = AllyType::where('is_active', true)->orderBy('name')->get();

        return view('Admin.aliado.edit', compact('ally', 'allyTypes'));
    }

    public function updateAlly(Request $request, Ally $ally)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'status' => 'required|string|in:activo,inactivo,pendiente',
        ]);

        $ally->update($validatedData);

        return redirect()->route('aliado')->with('success', 'Aliado actualizado exitosamente.');
    }

    public function destroyAlly(Ally $ally)
    {
        $ally->delete();
        return redirect()->route('aliado')->with('success', 'Aliado eliminado exitosamente.');
    }

    // Métodos para Usuarios
    public function usersIndex()
    {
        $users = User::all();

        return view('Admin.usuario.users', compact('users'));;
    }

    public function create()
    {
        return view('Admin.usuario.add-user'); // Vista del formulario para añadir un nuevo usuario
    }

    public function store(Request $request)
    {
        // 1. Validación de los datos del formulario
        $request->validate([
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            // 'userType' en el formulario se mapeará a la columna 'role' en la DB
            'user_type' => ['required', 'string', Rule::in(['comun', 'aliado', 'afiliado', 'admin'])],
            // 'phone' en el formulario se mapeará a la columna 'phone1' en la DB
            'phone1' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'string', Rule::in(['activo', 'inactivo', 'pendiente'])],
            'registrationDate' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            // Mensajes de error personalizados (¡están perfectos!)
            'firstName.required' => 'El campo Nombre es obligatorio.',
            'lastName.required' => 'El campo Apellido es obligatorio.',
            'email.required' => 'El campo Correo Electrónico es obligatorio.',
            'email.email' => 'Por favor, introduce un correo electrónico válido.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'password.required' => 'El campo Contraseña es obligatorio.',
            'password.min' => 'La contraseña debe tener al menos :min caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'user_type.required' => 'El campo Tipo de Usuario es obligatorio.',
            'user_type.in' => 'El Tipo de Usuario seleccionado no es válido.',
            'phone1.max' => 'El teléfono no puede exceder los :max caracteres.',
            'status.required' => 'El campo Estado es obligatorio.',
            'status.in' => 'El Estado seleccionado no es válido.',
            'registrationDate.required' => 'La Fecha de Registro es obligatoria.',
            'registrationDate.date' => 'La Fecha de Registro no tiene un formato válido.',
            'notes.max' => 'Las notas no pueden exceder los :max caracteres.',
        ]);

        $user = new User();
        $user->firstname = $request->firstName;
        $user->lastname = $request->lastName;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);

        $user->role = $request->userType;

        $user->phone1 = $request->phone1;

        $user->status = $request->status;
        $user->registration_date = $request->registrationDate;
        $user->notes = $request->notes;

        $user->save();

        return redirect()->route('users')->with('success', 'Usuario creado exitosamente.');
    }

    public function show(User $user)
    {
        return view('Admin.usuario.show', compact('user')); // Vista para ver detalles de un usuario
    }

    public function edit(User $user)
    {
        return view('Admin.usuario.edit', compact('user')); // Vista del formulario para editar un usuario
    }

    public function update(Request $request, User $user)
    {
        // 1. Validación de los datos del formulario
        $request->validate([
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            // 'userType' en el formulario se mapeará a la columna 'role' en la DB
            'user_type' => ['required', 'string', Rule::in(['comun', 'aliado', 'afiliado', 'admin'])],
            // 'phone' en el formulario se mapeará a la columna 'phone1' en la DB
            'phone1' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'string', Rule::in(['activo', 'inactivo', 'pendiente'])],
            'registrationDate' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            // Mensajes de error personalizados (están correctos)
            'firstName.required' => 'El campo Nombre es obligatorio.',
            'lastName.required' => 'El campo Apellido es obligatorio.',
            'email.required' => 'El campo Correo Electrónico es obligatorio.',
            'email.email' => 'Por favor, introduce un correo electrónico válido.',
            'email.unique' => 'Este correo electrónico ya está registrado por otro usuario.',
            'password.min' => 'La nueva contraseña debe tener al menos :min caracteres.',
            'password.confirmed' => 'La confirmación de la nueva contraseña no coincide.',
            'user_type.required' => 'El campo Tipo de Usuario es obligatorio.',
            'user_type.in' => 'El Tipo de Usuario seleccionado no es válido.',
            'phone1.max' => 'El teléfono no puede exceder los :max caracteres.',
            'status.required' => 'El campo Estado es obligatorio.',
            'status.in' => 'El Estado seleccionado no es válido.',
            'registrationDate.required' => 'La Fecha de Registro es obligatoria.',
            'registrationDate.date' => 'La Fecha de Registro no tiene un formato válido.',
            'notes.max' => 'Las notas no pueden exceder los :max caracteres.',
        ]);

        $user->firstname = $request->firstName;
        $user->lastname = $request->lastName;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->role = $request->userType;

        $user->phone1 = $request->phone1;

        $user->status = $request->status;
        $user->registration_date = $request->registrationDate;
        $user->notes = $request->notes;

        $user->save();

        return redirect()->route('users')->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users')->with('success', 'Usuario eliminado exitosamente.');
    }

    // Métodos para Productos
    public function productsIndex()
    {
        $products = Product::all(); // Obtiene todos los productos de la BD

        return view('Admin.producto.product', compact('products'));
    }

    /**
     * Muestra el formulario para crear un nuevo producto.
     */
    public function productsCreate()
    {
        // Si tienes aliados, podrías pasarlos a la vista para un select
        $allies = Ally::all();

        return view('Admin.producto.create', compact('allies'));
    }

    /**
     * Almacena un nuevo producto en la base de datos.
     */
    public function storeProduct(Request $request)
    {
        // 1. Validar los datos de entrada
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'ally_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|integer|min:0|max:100',
            'status' => 'required|string|in:Disponible,No Disponible,Agotado',
            // 'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Para carga de imagen
        ]);

        // 2. Calcular el precio final
        $basePrice = $validatedData['base_price'];
        $discount = $validatedData['discount_percentage'] ?? 0;
        $finalPrice = $basePrice * (1 - ($discount / 100));

        // 3. Crear el producto en la base de datos
        Product::create([
            'name' => $validatedData['name'],
            'ally_name' => $validatedData['ally_name'],
            'description' => $validatedData['description'],
            'base_price' => $basePrice,
            'discount_percentage' => $discount,
            'final_price' => $finalPrice,
            'status' => $validatedData['status'],
            // 'image_path' => $imagePath, // Si manejas carga de imagen
        ]);

        // 4. Redirigir con un mensaje de éxito
        return redirect()->route('products')->with('success', 'Producto añadido exitosamente.');
    }
    public function editProductForm(Product $product)
    {
        // Si tienes aliados, podrías pasarlos a la vista
        $allies = Ally::all();
        //return view('admin.products.edit', compact('product', 'allies'));
        return view('Admin.producto.edit', compact('product', 'allies'));
    }

    /**
     * Actualiza un producto existente en la base de datos.
     * @param Request $request
     * @param Product $product Laravel automáticamente inyectará la instancia del producto.
     */
    public function updateProduct(Request $request, Product $product)
    {
        // 1. Validar los datos de entrada
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'ally_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'base_price' => 'required|numeric|min:0',
            'discount_percentage' => 'nullable|integer|min:0|max:100',
            'status' => 'required|string|in:Disponible,No Disponible,Agotado',
            // 'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Para carga de imagen
        ]);

        // 2. Calcular el precio final
        $basePrice = $validatedData['base_price'];
        $discount = $validatedData['discount_percentage'] ?? 0;
        $finalPrice = $basePrice * (1 - ($discount / 100));

        // 3. Actualizar el producto en la base de datos
        $product->update([
            'name' => $validatedData['name'],
            'ally_name' => $validatedData['ally_name'],
            'description' => $validatedData['description'],
            'base_price' => $basePrice,
            'discount_percentage' => $discount,
            'final_price' => $finalPrice,
            'status' => $validatedData['status'],
            // 'image_path' => $imagePath, // Si manejas carga de imagen
        ]);

        // 4. Redirigir con un mensaje de éxito
        return redirect()->route('products')->with('success', 'Producto actualizado exitosamente.');
    }

    /**
     * Elimina un producto de la base de datos.
     * @param Product $product Laravel automáticamente inyectará la instancia del producto.
     */
    public function destroyProduct(Product $product)
    {
        $product->delete();
        return redirect()->route('products')->with('success', 'Producto eliminado exitosamente.');
    }

    // Métodos para Perfil
    public function profileIndex()
    {
        return view('Admin.profile.profile');
    }

    public function profileUpdate()
    {
        // public function profileUpdate($id) { return view('Admin.profile.update', ['profileId' => $id]); }
        return view('Admin.profile.edit');
    }
}
