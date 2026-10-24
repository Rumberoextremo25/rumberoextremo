<?php

// Ejemplo de un ProfileController simple en Laravel
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Services\BncApiService;

class ProfileController extends Controller
{
    public function show()
    {
        return view('Admin.profile.profile', [
            'user' => Auth::user(),
            'banks' => $this->bncBanks(),
        ]);
    }

    /**
     * Lista de bancos del BNC, cacheada para no consultar en cada carga.
     */
    private function bncBanks(): array
    {
        return Cache::remember('bnc_banks_list', now()->addHour(), function () {
            try {
                return app(BncApiService::class)->getBanksFromBnc() ?: [];
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('No se pudieron cargar los bancos del BNC', [
                    'error' => $e->getMessage(),
                ]);

                return [];
            }
        });
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
            'profile_photo_upload' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:15360',
            'current_password' => ['required', 'string'],
            'new_password' => ['nullable', 'string', 'min:8', 'confirmed', 'different:current_password'],
            'new_password_confirmation' => ['nullable', 'string'],
        ];

        $messages = [
            'new_password.different' => 'La nueva contraseña no puede ser la misma que la actual.',
            'profile_photo_upload.max' => 'La imagen de perfil no debe superar los 15MB.',
            'profile_photo_upload.image' => 'El archivo debe ser una imagen.',
            'profile_photo_upload.mimes' => 'La imagen debe ser de tipo: jpeg, png, jpg, gif o webp.',
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

    /**
     * Guarda los métodos de pago que la app consume desde el backend.
     */
    public function updatePaymentMethods(Request $request)
    {
        $data = $request->validate([
            'debtor_bank_code' => ['nullable', 'string', 'regex:/^[0-9]{4}$/'],
            'debtor_bank_name' => ['nullable', 'string', 'max:100'],
            'debtor_cell_phone' => ['nullable', 'string', 'regex:/^[0-9]{10,15}$/'],
            'debtor_id' => ['nullable', 'string', 'min:6', 'max:20'],
            'debtor_account' => ['nullable', 'string', 'max:20'],
            'debtor_account_type' => ['nullable', 'in:CNTA,CELE,CTAH,CTAV,AHORROS,CORRIENTE,TLF'],
            'card_number' => ['nullable', 'string', 'max:25'],
            'card_expiration' => ['nullable', 'regex:/^(0[1-9]|1[0-2])[0-9]{4}$/'],
            'card_holder_name' => ['nullable', 'string', 'max:255'],
            'card_holder_id' => ['nullable', 'string', 'max:20'],
            'card_type_id' => ['nullable', 'integer', 'between:1,3'],
            'card_account_type' => ['nullable', 'integer', 'between:1,99'],
        ], [
            'debtor_cell_phone.regex' => 'El teléfono debe tener entre 10 y 15 dígitos.',
            'card_expiration.regex' => 'El vencimiento debe tener formato MMAAAA, por ejemplo 122030.',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $cardNumber = preg_replace('/\D/', '', (string) $request->input('card_number', ''));

        if ($cardNumber !== '' && (strlen($cardNumber) < 13 || strlen($cardNumber) > 19)) {
            return back()->withInput()->withErrors([
                'card_number' => 'El número de tarjeta debe tener entre 13 y 19 dígitos.',
            ]);
        }

        $bankName = $data['debtor_bank_name'] ?? null;

        if (!$bankName && !empty($data['debtor_bank_code'])) {
            foreach ($this->bncBanks() as $bank) {
                if (($bank['Code'] ?? null) === $data['debtor_bank_code']) {
                    $bankName = $bank['Name'] ?? null;
                    break;
                }
            }
        }

        $user->debtor_bank_code = $data['debtor_bank_code'] ?? null;
        $user->debtor_bank_name = $bankName;
        $user->debtor_cell_phone = $data['debtor_cell_phone'] ?? null;
        $user->debtor_id = $data['debtor_id'] ?? null;
        // El débito inmediato usa el mismo teléfono del Pago Móvil.
        $user->debtor_account = $data['debtor_cell_phone'] ?? $user->debtor_cell_phone;
        $user->debtor_account_type = $data['debtor_account_type'] ?? 'CELE';

        $user->card_number = $cardNumber !== '' ? $cardNumber : null;
        $user->card_expiration = $data['card_expiration'] ?? null;
        $user->card_holder_name = $data['card_holder_name'] ?? null;
        $user->card_holder_id = $data['card_holder_id'] ?? null;
        $user->card_type_id = $data['card_type_id'] ?? 1;
        $user->card_account_type = $data['card_account_type'] ?? 20;

        $user->payment_updated_at = now();

        $user->save();

        return redirect()->route('profile')->with('success', 'Métodos de pago actualizados.');
    }

    /**
     * Subida rápida de la foto de perfil desde la vista de perfil.
     * Acepta imágenes de hasta 15MB.
     */
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'profile_photo' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:15360', // 15 MB en kilobytes
            ],
        ], [
            'profile_photo.required' => 'Selecciona una imagen para subir.',
            'profile_photo.image' => 'El archivo debe ser una imagen.',
            'profile_photo.mimes' => 'Formatos permitidos: JPG, PNG, GIF o WEBP.',
            'profile_photo.max' => 'La imagen no debe superar los 15MB.',
            'profile_photo.uploaded' => 'La imagen no se pudo subir. El servidor admite como máximo 15MB.',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        try {
            // Borra la anterior para no dejar archivos huérfanos
            if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            $path = $request->file('profile_photo')->store('profile-photos', 'public');

            $user->profile_photo_path = $path;
            $user->save();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('profile.photo_upload_failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'No se pudo guardar la imagen: ' . $e->getMessage());
        }

        return redirect()->route('profile')->with('success', '¡Foto de perfil actualizada!');
    }

    /**
     * Elimina la foto de perfil y vuelve al avatar por defecto.
     */
    public function destroyPhoto()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        $user->profile_photo_path = null;
        $user->save();

        return redirect()->route('profile')->with('success', 'Foto de perfil eliminada.');
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