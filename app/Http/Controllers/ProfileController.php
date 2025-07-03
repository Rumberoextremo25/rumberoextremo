<?php

// Ejemplo de un ProfileController simple en Laravel
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class ProfileController extends Controller
{
    public function show()
    {
        return view('Admin.profile.profile', ['user' => Auth::user()]); // Esta es tu vista actual
    }

    public function edit()
    {
        return view('Admin.profile.edit', ['user' => Auth::user()]); // Vista para el formulario de edición
    }

    public function update(Request $request)
    {
        /** @var \App\Models\User $user */ // <-- ¡Añade esta línea!
        $user = Auth::user();

        // 1. Reglas de Validación
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'identification' => 'required|string|max:50',
            'phone1' => 'required|string|max:20',
            'phone2' => 'nullable|string|max:20',
            'address' => 'required|string|max:500',
            'dob' => 'nullable|date',
            'profile_photo_upload' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'current_password' => ['required', 'string'],
            'new_password' => ['nullable', 'string', 'min:8', 'confirmed', 'different:current_password'],
            'new_password_confirmation' => ['nullable', 'string'],
        ];

        $messages = [
            'new_password.different' => 'La nueva contraseña no puede ser la misma que la actual.',
            'profile_photo_upload.max' => 'La imagen de perfil no debe superar los 2MB.',
            'profile_photo_upload.image' => 'El archivo debe ser una imagen.',
            'profile_photo_upload.mimes' => 'La imagen debe ser de tipo: jpeg, png, jpg, gif, svg.',
        ];

        if (isset($user->is_ally) && $user->is_ally) {
            $rules = array_merge($rules, [
                'allied_company_name' => 'nullable|string|max:255',
                'allied_company_rif' => 'nullable|string|max:50',
                'service_category' => 'nullable|string|max:255',
                'website_url' => 'nullable|url|max:255',
                'discount' => 'nullable|integer|min:0|max:100',
            ]);
        }

        $request->validate($rules, $messages);

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['La contraseña actual es incorrecta. Por favor, inténtalo de nuevo.'],
            ]);
        }

        if ($request->hasFile('profile_photo_upload')) {
            if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $path = $request->file('profile_photo_upload')->store('profile-photos', 'public');
            $user->profile_photo_path = $path;
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->identification = $request->identification;
        $user->phone1 = $request->phone1;
        $user->phone2 = $request->phone2;
        $user->address = $request->address;
        $user->dob = $request->dob;

        if (isset($user->is_ally) && $user->is_ally) {
            $user->allied_company_name = $request->allied_company_name;
            $user->allied_company_rif = $request->allied_company_rif;
            $user->service_category = $request->service_category;
            $user->website_url = $request->website_url;
            $user->discount = $request->discount;
        }

        if ($request->filled('new_password')) {
            $user->password = Hash::make($request->new_password);
        }

        $user->save(); // Intelephense ahora debería reconocer 'save()'

        return redirect()->route('profile')->with('success', '¡Tu perfil ha sido actualizado exitosamente!');
    }

    public function changePassword()
    {
        // Lógica para cambiar contraseña
        return view('Admin.profile.change-password');
        
    }

    public function updatePassword(Request $request)
    {
        // 1. Validación de los datos
        $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
            // 'confirmed' verifica que 'new_password' y 'new_password_confirmation' coincidan
            // 'different:current_password' asegura que la nueva contraseña no sea igual a la actual
        ], [
            'new_password.different' => 'La nueva contraseña no puede ser la misma que la actual.',
        ]);

        // 2. Verificar que la contraseña actual sea correcta
        if (!Hash::check($request->current_password, Auth::user()->password)) {
            // Si la contraseña actual no coincide, lanza una excepción de validación
            throw ValidationException::withMessages([
                'current_password' => ['La contraseña actual es incorrecta.'],
            ]);
            // O puedes redirigir con un mensaje de error:
            // return back()->withErrors(['current_password' => 'La contraseña actual es incorrecta.'])->withInput();
        }

        // 3. Actualizar la contraseña en la base de datos
        $user = Auth::user();
        $user->password = Hash::make($request->new_password); // Encriptar la nueva contraseña
        $user->save();

        // 4. Invalidar sesiones antiguas (opcional, pero buena práctica de seguridad)
        // Esto deslogueará al usuario de otros dispositivos.
        // Auth::logoutOtherDevices($request->new_password); // Requiere confirmación de contraseña, Laravel 8+

        // 5. Redireccionar con mensaje de éxito
        return redirect()->route('profile')->with('success', '¡Tu contraseña ha sido actualizada exitosamente!');
    }
}