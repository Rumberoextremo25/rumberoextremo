<?php

namespace App\Http\Controllers;

use App\Models\User; // Make sure to import your User model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule; // Don't forget this for the Rule::in and Rule::unique

class UserController extends Controller
{
    /**
     * Muestra una lista de todos los usuarios.
     */
    public function usersIndex()
    {
        $users = User::all();
        // Asegúrate de que la vista exista en resources/views/Admin/usuario/users.blade.php
        return view('Admin.usuario.users', compact('users'));
    }

    /**
     * Muestra el formulario para crear un nuevo usuario.
     */
    public function create()
    {
        // Asegúrate de que la vista exista en resources/views/Admin/usuario/add-user.blade.php
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
            'phone2' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'string', Rule::in(['activo', 'inactivo', 'pendiente'])],
            'registrationDate' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            // AGREGANDO VALIDACIÓN PARA EL CAMPO EDAD
            'age' => ['nullable', 'integer', 'min:1', 'max:150'], // Edad como un entero entre 1 y 150
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
            'phone1.max' => 'El teléfono principal no puede exceder los :max caracteres.',
            'phone2.max' => 'El teléfono adicional no puede exceder los :max caracteres.',
            'address.max' => 'La dirección no puede exceder los :max caracteres.',
            'status.required' => 'El campo Estado es obligatorio.',
            'status.in' => 'El Estado seleccionado no es válido.',
            'registrationDate.required' => 'La Fecha de Registro es obligatoria.',
            'registrationDate.date' => 'La Fecha de Registro no tiene un formato válido.',
            'notes.max' => 'Las notas no pueden exceder los :max caracteres.',
            // Mensajes para EDAD
            'age.integer' => 'La edad debe ser un número entero.',
            'age.min' => 'La edad mínima permitida es :min.',
            'age.max' => 'La edad máxima permitida es :max.',
        ]);

        $user = new User();
        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->role = $request->user_type;
        $user->phone1 = $request->phone1;
        $user->phone2 = $request->phone2;
        $user->address = $request->address;
        $user->status = $request->status;
        $user->registration_date = $request->registrationDate;
        $user->notes = $request->notes;
        // ASIGNAR EL CAMPO EDAD DIRECTAMENTE
        $user->age = $request->age; // Esto asume que tienes una columna 'age' en tu tabla 'users'

        $user->save();

        return redirect()->route('users')->with('success', 'Usuario creado exitosamente.');
    }

    // ... (Métodos show y edit)

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
            'phone2' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'status' => ['required', 'string', Rule::in(['activo', 'inactivo', 'pendiente'])],
            'registrationDate' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            // AGREGANDO VALIDACIÓN PARA EL CAMPO EDAD
            'age' => ['nullable', 'integer', 'min:1', 'max:150'], // Edad como un entero entre 1 y 150
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
            'phone1.max' => 'El teléfono principal no puede exceder los :max caracteres.',
            'phone2.max' => 'El teléfono adicional no puede exceder los :max caracteres.',
            'address.max' => 'La dirección no puede exceder los :max caracteres.',
            'status.required' => 'El campo Estado es obligatorio.',
            'status.in' => 'El Estado seleccionado no es válido.',
            'registrationDate.required' => 'La Fecha de Registro es obligatoria.',
            'registrationDate.date' => 'La Fecha de Registro no tiene un formato válido.',
            'notes.max' => 'Las notas no pueden exceder los :max caracteres.',
            // Mensajes para EDAD
            'age.integer' => 'La edad debe ser un número entero.',
            'age.min' => 'La edad mínima permitida es :min.',
            'age.max' => 'La edad máxima permitida es :max.',
        ]);

        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->role = $request->user_type;
        $user->phone1 = $request->phone1;
        $user->phone2 = $request->phone2;
        $user->address = $request->address;
        $user->status = $request->status;
        $user->registration_date = $request->registrationDate;
        $user->notes = $request->notes;
        // ASIGNAR EL CAMPO EDAD DIRECTAMENTE
        $user->age = $request->age; // Esto asume que tienes una columna 'age' en tu tabla 'users'

        $user->save();

        return redirect()->route('users')->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Elimina el usuario especificado del almacenamiento.
     */
    public function destroy(User $user)
    {
        $user->delete();
        // Redirect to the users index page, using a named route
        return redirect()->route('users')->with('success', 'Usuario eliminado exitosamente.');
    }
}