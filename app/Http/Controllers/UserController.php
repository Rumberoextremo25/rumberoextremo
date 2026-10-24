<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Muestra una lista de todos los usuarios.
     */
    public function usersIndex(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $rol = $request->query('rol');
        $estado = $request->query('estado');

        $query = User::query();

        if ($q !== '') {
            $like = '%' . $q . '%';
            $operator = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

            $query->where(function ($sub) use ($like, $operator) {
                $sub->where('name', $operator, $like)
                    ->orWhere('firstname', $operator, $like)
                    ->orWhere('lastname', $operator, $like)
                    ->orWhere('email', $operator, $like)
                    ->orWhere('phone1', $operator, $like);
            });
        }

        if ($rol === 'comun') {
            $query->where(function ($sub) {
                $sub->whereIn('role', ['comun', 'user'])->orWhereNull('role');
            });
        } elseif (in_array($rol, ['admin', 'aliado', 'afiliado'], true)) {
            $query->where('role', $rol);
        }

        if (in_array($estado, ['activo', 'inactivo', 'pendiente'], true)) {
            $query->where('status', $estado);
        }

        $users = $query->orderByDesc('created_at')->paginate(12)->withQueryString();

        $stats = [
            'total' => User::count(),
            'active' => User::where('status', 'activo')->count(),
            'pending' => User::where('status', 'pendiente')->count(),
            'inactive' => User::where('status', 'inactivo')->count(),
        ];

        return view('Admin.usuario.users', compact('users', 'stats', 'q', 'rol', 'estado'));
    }

    /**
     * Muestra el formulario para crear un nuevo usuario.
     */
    public function create()
    {
        return view('Admin.usuario.add-user');
    }

    /**
     * Almacena un nuevo usuario en la base de datos.
     */
    public function store(Request $request)
    {
        // 1. Validación de los datos del formulario
        $request->validate([
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'user_type' => ['required', 'string', Rule::in(['comun', 'aliado', 'afiliado', 'admin'])],
            'phone1' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'string', Rule::in(['activo', 'inactivo', 'pendiente'])],
            'registrationDate' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            // Mensajes de error personalizados
            'firstname.required' => 'El campo Nombre es obligatorio.',
            'lastname.required' => 'El campo Apellido es obligatorio.',
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

        // 2. Crear nuevo usuario
        $user = new User();
        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->role = $request->user_type;
        $user->phone1 = $request->phone1;
        $user->status = $request->status;
        $user->registration_date = $request->registrationDate;
        $user->notes = $request->notes;
        $user->save();

        // 3. Redireccionar al índice con mensaje de éxito
        return redirect()->route('admin.users.index')->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Muestra los detalles de un usuario específico.
     */
    public function show(User $user)
    {
        return view('Admin.usuario.show', compact('user'));
    }

    /**
     * Muestra el formulario para editar un usuario existente.
     */
    public function edit(User $user)
    {
        return view('Admin.usuario.edit', compact('user'));
    }

    /**
     * Actualiza el usuario especificado en la base de datos.
     */
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
            'user_type' => ['required', 'string', Rule::in(['comun', 'aliado', 'afiliado', 'admin'])],
            'phone1' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'string', Rule::in(['activo', 'inactivo', 'pendiente'])],
            'registrationDate' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            // Mensajes de error personalizados
            'firstname.required' => 'El campo Nombre es obligatorio.',
            'lastname.required' => 'El campo Apellido es obligatorio.',
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

        // 2. Actualizar datos del usuario
        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->email = $request->email;

        // Solo actualizar contraseña si se proporcionó una nueva
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->role = $request->user_type;
        $user->phone1 = $request->phone1;
        $user->status = $request->status;
        $user->registration_date = $request->registrationDate;
        $user->notes = $request->notes;
        $user->save();

        // 3. Redireccionar al índice con mensaje de éxito
        return redirect()->route('admin.users.index')->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Elimina el usuario especificado del almacenamiento.
     */
    public function destroy(User $user)
    {
        // Eliminar el usuario
        $user->delete();

        // Redireccionar al índice con mensaje de éxito
        return redirect()->route('admin.users.index')->with('success', 'Usuario eliminado exitosamente.');
    }
}