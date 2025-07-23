<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Ally;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\BusinessType;
use App\Models\Sale;
use App\Models\User;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Barryvdh\DomPDF\Facade\PDF;
use Dotenv\Exception\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

        $pageViews = 12345;
 
        $totalSales = Sale::sum('total');

        $latestActivities = [];
        $latestProfileActivities = [];

        $todaySalesSpecific = 0.00;
        $customerSatisfaction = 0;

        if ($role === 'admin') {

            // Puedes mantener todaySales si quieres mostrar un "Ventas de Hoy" adicional
            $todaySalesSpecific = Sale::whereDate('sale_date', Carbon::today())->sum('total');
            $customerSatisfaction = 92; // Valor dummy o calculado

            // Obtén las últimas 10 actividades para el Admin (todas las actividades del sistema)
            $latestActivities = ActivityLog::latest()
                ->limit(10)
                ->get()
                ->map(fn($activity) => [
                    'activity' => $activity->description,
                    'user' => $activity->performed_by ?? 'N/A', // Asegúrate de que 'performed_by' exista o usa un fallback
                    'date' => Carbon::parse($activity->created_at)->format('d/m/Y H:i'),
                    'status' => ucfirst($activity->status),
                    'status_class' => $this->getStatusClass($activity->status)
                ]);

        } elseif ($role === 'aliado') {


            // Ventas de hoy solo para este aliado
            $todaySalesSpecific = Sale::whereDate('sale_date', Carbon::today())
                ->whereHas('product', function ($query) use ($user) {
                    // Asegúrate de que tu modelo 'Sale' tenga una relación con 'Product'
                    // Y que 'Product' tenga una 'aliado_id' o 'user_id' para filtrar por el aliado
                    $query->where('user_id', $user->id); // Asumiendo que el campo 'aliado_id' en la tabla productos es 'user_id'
                })
                ->sum('total');

            $customerSatisfaction = 88; // Dummy o calculado para el aliado

            // Actividades relevantes para el Aliado (ej. sus propios productos, sus ventas)
            // Esto requiere que tu ActivityLog tenga una manera de vincular actividades a un 'aliado_id'
            $latestActivities = ActivityLog::where('user_id', $user->id) // Asume que ActivityLog tiene un campo 'user_id' que es el aliado_id
                ->latest()
                ->limit(10)
                ->get()
                ->map(fn($activity) => [
                    'activity' => $activity->description,
                    'user' => $user->firstname ?? $user->name, // Muestra el nombre del aliado
                    'date' => Carbon::parse($activity->created_at)->format('d/m/Y H:i'),
                    'status' => ucfirst($activity->status),
                    'status_class' => $this->getStatusClass($activity->status)
                ]);

        } elseif ($role === 'comun') {
            // USUARIO COMÚN: No tiene cards adicionales, solo actividades relacionadas a su perfil.
            // Las 3 cards principales ($totalUsers, $pageViews, $totalSales) se muestran igual.

            // Obtén las últimas 10 actividades relacionadas directamente con este usuario
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

        // Retorna la vista con todas las variables necesarias.
        // Las variables no usadas por un rol específico simplemente no serán renderizadas por Blade si no hay @if para ellas.
        return view('dashboard', compact(
            'totalUsers',
            'pageViews', // Nueva variable
            'totalSales', // Nueva variable
            'latestActivities',
            'latestProfileActivities',
            'todaySalesSpecific', // Puedes usarla si decides añadir una card de ventas del día específica para admin/aliado
            'customerSatisfaction' // Puedes usarla si decides añadir una card de satisfacción específica para admin/aliado
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
            return back()->withErrors($e->errors())->withInput();
        }

        // 2. Actualizar la contraseña
        $user->password = Hash::make($request->input('new_password'));
        $user->save();

        // 3. Opcional: Re-autenticar al usuario para invalidar sesiones antiguas
        // Auth::guard('web')->logoutOtherDevices($request->input('new_password'));

        return back()->with('success', '¡Tu contraseña ha sido cambiada exitosamente!');
    }

    //METODOS PARA LA CREACION DE ALIADOS
    public function indexAllies()
    {
        // Eager load relationships for displaying category, subcategory, business type names
        $allies = Ally::with('category', 'subCategory', 'businessType', 'user')->get();
        // Asegúrate de que la vista exista en resources/views/Admin/aliado/aliado.blade.php
        return view('Admin.aliado.aliado', compact('allies'));
    }

    /**
     * Muestra el formulario para crear un nuevo aliado.
     */
    public function aliadosCreate()
    {
        $categories = Category::all();
        $businessTypes = BusinessType::all();
        // Asegúrate de que la vista exista en resources/views/Admin/aliado/create.blade.php
        return view('Admin.aliado.create', compact('categories', 'businessTypes'));
    }

    /**
     * Almacena un nuevo aliado en la base de datos, creando también su usuario y asignando un rol.
     */
    public function storeAlly(Request $request)
    {
        // 1. Validar los datos del formulario (tanto para el usuario como para el aliado)
        $validatedData = $request->validate([
            // --- Datos del Usuario (nuevos campos del formulario) ---
            'user_name'     => 'required|string|max:255', // Nombre para la cuenta de usuario
            'user_email'    => 'required|string|email|max:255|unique:users,email', // Email para la cuenta, debe ser único en la tabla users
            'user_password' => 'required|string|min:8|confirmed', // Contraseña para la cuenta, con confirmación

            // --- Datos del Aliado (ya definidos) ---
            'company_name'          => 'required|string|max:255|unique:allies,company_name',
            'company_rif'           => 'nullable|string|max:255|unique:allies,company_rif',
            'category_id'           => 'required|exists:categories,id',
            'sub_category_id'       => 'nullable|exists:sub_categories,id',
            'business_type_id'      => 'required|exists:business_types,id',
            'status'                => ['required', Rule::in(['activo', 'pendiente', 'inactivo'])],
            'contact_person_name'   => 'required|string|max:255',
            'contact_email'         => 'required|email|max:255|unique:allies,contact_email', // Este email es el de contacto del aliado, distinto al de acceso
            'contact_phone'         => 'required|string|max:20',
            'contact_phone_alt'     => 'nullable|string|max:20',
            'company_address'       => 'nullable|string|max:500',
            'website_url'           => 'nullable|url|max:255',
            'discount'              => 'nullable|string|max:500',
            'notes'                 => 'nullable|string',
            'registered_at'         => 'required|date',
        ], [
            // --- Mensajes de Validación Personalizados ---
            // Mensajes para el usuario
            'user_name.required'        => 'El nombre de usuario es obligatorio.',
            'user_email.required'       => 'El correo electrónico para la cuenta es obligatorio.',
            'user_email.email'          => 'Ingrese un correo electrónico válido para la cuenta.',
            'user_email.unique'         => 'Este correo electrónico ya está en uso por otra cuenta.',
            'user_password.required'    => 'La contraseña es obligatoria.',
            'user_password.min'         => 'La contraseña debe tener al menos :min caracteres.',
            'user_password.confirmed'   => 'La confirmación de la contraseña no coincide.',

            // Mensajes para el aliado (los que ya tenías)
            'company_name.required'     => 'El nombre de la empresa es obligatorio.',
            'company_name.unique'       => 'Ya existe un aliado con este nombre.',
            'company_rif.unique'        => 'Ya existe un aliado con este RIF.',
            'category_id.required'      => 'Debe ingresar una categoría.',
            'category_id.exists'        => 'La categoría ingresada no es válida.',
            'sub_category_id.exists'    => 'La subcategoría ingresada no es válida.',
            'business_type_id.required' => 'Debe ingresar un tipo de negocio.',
            'business_type_id.exists'   => 'El tipo de negocio ingresado no es válido.',
            'status.required'           => 'Debe seleccionar un estado para el aliado.',
            'contact_person_name.required' => 'La persona de contacto es obligatoria.',
            'contact_email.required'    => 'El correo electrónico de contacto es obligatorio.',
            'contact_email.email'       => 'Ingrese un correo electrónico válido.',
            'contact_email.unique'      => 'Ya existe un aliado con este correo electrónico de contacto.',
            'contact_phone.required'    => 'El teléfono principal es obligatorio.',
            'registered_at.required'    => 'La fecha de registro es obligatoria.',
            'registered_at.date'        => 'Ingrese una fecha de registro válida.',
            'website_url.url'           => 'El sitio web debe ser una URL válida.',
        ]);

        // Iniciar una transacción de base de datos
        // Esto asegura que si falla la creación del usuario o del aliado, todo se revierta.
        DB::beginTransaction();

        try {
            // 2. Crear el nuevo usuario
            $user = User::create([
                'name'     => $validatedData['user_name'],
                'email'    => $validatedData['user_email'],
                'password' => Hash::make($validatedData['user_password']),
                // Puedes añadir 'email_verified_at' => now(), si quieres verificarlo al crearlo
            ]);

            // 3. Asignar el rol 'ally' al usuario
            // Asegúrate de que el rol 'ally' exista en tu base de datos (creado por un seeder de roles)
            $role = Role::where('name', 'ally')->first();
            if ($role) {
                $user->assignRole($role);
            } else {
                // Si el rol 'ally' no existe, lanza una excepción para revertir la transacción.
                throw new \Exception('El rol "ally" no está configurado en el sistema. Asegúrese de que existe.');
            }

            // 4. Asegurar que sub_category_id es null si no se proporcionó
            if (empty($validatedData['sub_category_id'])) {
                $validatedData['sub_category_id'] = null;
            }

            // 5. Crear el registro del aliado y asociarlo al usuario recién creado
            $ally = Ally::create([
                'user_id'             => $user->id, // ¡Asigna el ID del usuario recién creado!
                'company_name'        => $validatedData['company_name'],
                'company_rif'         => $validatedData['company_rif'],
                'category_id'         => $validatedData['category_id'],
                'sub_category_id'     => $validatedData['sub_category_id'],
                'business_type_id'    => $validatedData['business_type_id'],
                'contact_person_name' => $validatedData['contact_person_name'],
                'contact_email'       => $validatedData['contact_email'],
                'contact_phone'       => $validatedData['contact_phone'],
                'contact_phone_alt'   => $validatedData['contact_phone_alt'],
                'company_address'     => $validatedData['company_address'],
                'website_url'         => $validatedData['website_url'],
                'discount'            => $validatedData['discount'],
                'notes'               => $validatedData['notes'],
                'registered_at'       => $validatedData['registered_at'],
                'status'              => $validatedData['status'],
            ]);

            // Si todo fue bien, confirmar la transacción
            DB::commit();

            // Redirigir con mensaje de éxito
            return redirect()->route('aliados.index')->with('success', '¡Aliado y cuenta de usuario creados exitosamente!');

        } catch (\Exception $e) {
            // Si algo falla, revertir la transacción
            DB::rollBack();

            // Loggear el error para depuración
            Log::error('Error al crear aliado y usuario: ' . $e->getMessage());

            // Redirigir de vuelta con un mensaje de error y los datos antiguos
            return back()->withInput()->with('error', 'Hubo un error al crear el aliado y la cuenta de usuario. Por favor, intente de nuevo.')->withErrors(['general' => $e->getMessage()]);
        }
    }

    /**
     * Muestra el formulario para editar un aliado existente.
     */
    public function alliesEdit(Ally $ally) // Laravel's Route Model Binding
    {
        // Fetch all active categories and business types for the edit dropdowns
        $categories = Category::all();
        $businessTypes = BusinessType::all();

        // Also fetch subcategories for the *currently selected* category of the ally
        // Esto es necesario para pre-seleccionar la subcategoría correcta al cargar el formulario de edición
        $currentSubCategories = $ally->category_id ?
            SubCategory::where('category_id', $ally->category_id)->get() :
            collect(); // Retorna una colección vacía si no hay categoría seleccionada

        // Asegúrate de que la vista exista en resources/views/Admin/aliado/edit.blade.php
        return view('Admin.aliado.edit', compact('ally', 'categories', 'businessTypes', 'currentSubCategories'));
    }

    /**
     * Actualiza el aliado especificado en el almacenamiento.
     */
    public function updateAlly(Request $request, Ally $ally)
    {
        // 1. Validar los datos de entrada para la actualización
        $validatedData = $request->validate([
            // No se validan los datos del usuario aquí, ya que el enfoque es actualizar solo el aliado.
            // Si también quieres actualizar el usuario desde esta vista, deberías añadir esos campos
            // de validación y la lógica de actualización del usuario aquí.

            'company_name'          => 'required|string|max:255|unique:allies,company_name,' . $ally->id, // Excluir ID actual
            'company_rif'           => 'nullable|string|max:255|unique:allies,company_rif,' . $ally->id,
            'category_id'           => 'required|exists:categories,id',
            'sub_category_id'       => 'nullable|exists:sub_categories,id',
            'business_type_id'      => 'required|exists:business_types,id',
            'status'                => ['required', Rule::in(['activo', 'pendiente', 'inactivo'])],
            'contact_person_name'   => 'required|string|max:255',
            'contact_email'         => 'required|email|max:255|unique:allies,contact_email,' . $ally->id,
            'contact_phone'         => 'required|string|max:20',
            'contact_phone_alt'     => 'nullable|string|max:20',
            'company_address'       => 'nullable|string|max:500',
            'website_url'           => 'nullable|url|max:255',
            'discount'              => 'nullable|string|max:500',
            'notes'                 => 'nullable|string',
            'registered_at'         => 'required|date',
        ], [
            // Custom messages for update (similar to store)
            'company_name.unique'       => 'Ya existe un aliado con este nombre.',
            'company_rif.unique'        => 'Ya existe un aliado con este RIF.',
            'contact_email.unique'      => 'Ya existe un aliado con este correo electrónico de contacto.',
            'category_id.required'      => 'Debe seleccionar una categoría.',
            'business_type_id.required' => 'Debe seleccionar un tipo de negocio.',
            'category_id.exists'        => 'La categoría seleccionada no es válida.',
            'sub_category_id.exists'    => 'La subcategoría seleccionada no es válida.',
            'business_type_id.exists'   => 'El tipo de negocio seleccionado no es válido.',
            // ... otros mensajes de validación
        ]);

        // Asegurar que sub_category_id es null si no se proporcionó
        if (empty($validatedData['sub_category_id'])) {
            $validatedData['sub_category_id'] = null;
        }

        // Actualizar el registro del aliado
        $ally->update($validatedData);

        return redirect()->route('aliados.index')->with('success', 'Aliado actualizado exitosamente.');
    }

    /**
     * Elimina el aliado especificado del almacenamiento.
     */
    public function destroyAlly(Ally $ally)
    {
        // ¡Importante! Si el user_id está en onDelete('cascade') en la migración de allies,
        // al eliminar el aliado, el usuario asociado también será eliminado automáticamente.
        // Si no quieres esto, deberías cambiar el onDelete o desvincular el usuario primero.
        try {
            $ally->delete();
            return redirect()->route('aliados.index')->with('success', 'Aliado eliminado exitosamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar aliado: ' . $e->getMessage());
            return redirect()->route('aliados.index')->with('error', 'Hubo un error al eliminar el aliado.');
        }
    }

    /**
     * Obtiene subcategorías basadas en category_id para solicitudes AJAX.
     * Este método es crucial para la carga dinámica de subcategorías en los formularios.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSubcategories(Request $request)
    {
        $categoryId = $request->input('category_id');

        if (!$categoryId) {
            return response()->json([]); // Retorna un array vacío si no hay category_id
        }

        // Carga las subcategorías que pertenecen a la categoría seleccionada
        $subcategories = SubCategory::where('category_id', $categoryId)->get(['id', 'name']); // Solo id y name

        return response()->json($subcategories);
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
