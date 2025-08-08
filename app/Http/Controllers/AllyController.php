<?php

namespace App\Http\Controllers;

use App\Models\Ally;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\BusinessType;
use App\Models\User;
use Spatie\Permission\Models\Role; // Assuming you're using Spatie's Laravel Permission
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AllyController extends Controller
{
    /**
     * Muestra una lista de todos los aliados.
     */
    public function index()
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
        // $categories = Category::all();
        // $businessTypes = BusinessType::all();

        // Asegúrate de que la vista exista en resources/views/Admin/aliado/create.blade.php
        return view('Admin.aliado.create'); // No need to compact anything specific for text inputs
    }

    /**
     * Almacena un nuevo aliado en la base de datos, creando también su usuario y asignando un rol.
     */
    public function storeAlly(Request $request)
    {
        // 1. Validar los datos del formulario
        $validatedData = $request->validate([
            // --- Datos del Usuario ---
            'user_name'         => 'required|string|max:255',
            'user_email'        => 'required|string|email|max:255|unique:users,email',
            'user_password'     => 'required|string|min:8|confirmed',

            // --- Datos del Aliado ---
            'company_name'      => 'required|string|max:255|unique:allies,company_name',
            'company_rif'       => 'nullable|string|max:255|unique:allies,company_rif',
            'description'       => 'nullable|string|max:1000', // Nuevo campo de descripción
            'image_url'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Nuevo campo de imagen
            'category_name'     => 'required|string|max:255',
            'sub_category_name' => 'nullable|string|max:255',
            'business_type_name' => 'required|string|max:255',

            'status'            => ['required', Rule::in(['activo', 'pendiente', 'inactivo'])],
            'contact_person_name' => 'required|string|max:255',
            'contact_email'     => 'required|email|max:255|unique:allies,contact_email',
            'contact_phone'     => 'required|string|max:20',
            'contact_phone_alt' => 'nullable|string|max:20',
            'company_address'   => 'nullable|string|max:500',
            'website_url'       => 'nullable|url|max:255',
            'discount'          => 'nullable|string|max:500',
            'notes'             => 'nullable|string|max:500',
            'registered_at'     => 'required|date',
        ], [
            // --- Mensajes de Validación Personalizados ---
            'user_name.required'        => 'El nombre de usuario es obligatorio.',
            'user_email.required'       => 'El correo electrónico para la cuenta es obligatorio.',
            'user_email.email'          => 'Ingrese un correo electrónico válido para la cuenta.',
            'user_email.unique'         => 'Este correo electrónico ya está en uso por otra cuenta.',
            'user_password.required'    => 'La contraseña es obligatoria.',
            'user_password.min'         => 'La contraseña debe tener al menos :min caracteres.',
            'user_password.confirmed'   => 'La confirmación de la contraseña no coincide.',

            'company_name.required'     => 'El nombre de la empresa es obligatorio.',
            'company_name.unique'       => 'Ya existe un aliado con este nombre.',
            'company_rif.unique'        => 'Ya existe un aliado con este RIF.',
            'description.max'           => 'La descripción no puede exceder los :max caracteres.',
            'image_url.image'           => 'El archivo debe ser una imagen.',
            'image_url.mimes'           => 'La imagen debe ser de tipo: jpeg, png, jpg, gif, svg.',
            'image_url.max'             => 'La imagen no puede pesar más de 2MB.',
            'category_name.required'    => 'La categoría de negocio es obligatoria.',
            'business_type_name.required' => 'El tipo de negocio es obligatorio.',

            'status.required'           => 'Debe seleccionar un estado para el aliado.',
            'contact_person_name.required' => 'La persona de contacto es obligatoria.',
            'contact_email.required'    => 'El correo electrónico de contacto es obligatorio.',
            'contact_email.email'       => 'Ingrese un correo electrónico de contacto válido.',
            'contact_email.unique'      => 'Ya existe un aliado con este correo electrónico de contacto.',
            'contact_phone.required'    => 'El teléfono principal es obligatorio.',
            'registered_at.required'    => 'La fecha de registro es obligatoria.',
            'registered_at.date'        => 'Ingrese una fecha de registro válida.',
        ]);

        // Iniciar una transacción de base de datos
        DB::beginTransaction();

        try {
            // 2. Crear o encontrar la Categoría
            $category = Category::firstOrCreate(
                ['name' => $validatedData['category_name']],
                ['slug' => Str::slug($validatedData['category_name'])]
            );

            // 3. Crear o encontrar la Subcategoría (si se proporciona)
            $subCategory = null;
            if (!empty($validatedData['sub_category_name'])) {
                $subCategory = SubCategory::firstOrCreate(
                    ['name' => $validatedData['sub_category_name']],
                    [
                        'category_id' => $category->id,
                        'slug'        => Str::slug($validatedData['sub_category_name'])
                    ]
                );
            }

            // 4. Crear o encontrar el Tipo de Negocio
            $businessType = BusinessType::firstOrCreate(
                ['name' => $validatedData['business_type_name']],
                ['slug' => Str::slug($validatedData['business_type_name'])]
            );

            // 5. Crear el nuevo usuario
            $user = User::create([
                'name'      => $validatedData['user_name'],
                'email'     => $validatedData['user_email'],
                'password'  => Hash::make($validatedData['user_password']),
            ]);

            // 6. Asignar el rol 'ally' al usuario
            $role = Role::where('name', 'ally')->first();
            if ($role) {
                $user->assignRole($role);
            } else {
                throw new \Exception('El rol "ally" no está configurado en el sistema.');
            }

            // 7. Manejar la subida de la imagen
            $imagePath = null;
            if ($request->hasFile('image_url')) {
                // Guarda la imagen en el directorio 'public/allies' y obtiene la ruta.
                // Es importante configurar el filesystem de Laravel correctamente para esto.
                $imagePath = $request->file('image_url')->store('allies', 'public');
            }

            // 8. Crear el registro del aliado y asociarlo al usuario y a las categorías/tipos
            $ally = Ally::create([
                'user_id'           => $user->id,
                'company_name'      => $validatedData['company_name'],
                'company_rif'       => $validatedData['company_rif'],
                'description'       => $validatedData['description'] ?? null, // Asigna el valor o null
                'image_url'         => $imagePath, // Asigna la ruta de la imagen o null
                'category_id'       => $category->id,
                'sub_category_id'   => $subCategory ? $subCategory->id : null,
                'business_type_id'  => $businessType->id,
                'contact_person_name' => $validatedData['contact_person_name'],
                'contact_email'     => $validatedData['contact_email'],
                'contact_phone'     => $validatedData['contact_phone'],
                'contact_phone_alt' => $validatedData['contact_phone_alt'] ?? null,
                'company_address'   => $validatedData['company_address'] ?? null,
                'website_url'       => $validatedData['website_url'] ?? null,
                'discount'          => $validatedData['discount'] ?? null,
                'notes'             => $validatedData['notes'] ?? null,
                'registered_at'     => $validatedData['registered_at'],
                'status'            => $validatedData['status'],
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
        return view('Admin.aliado.edit', compact('ally'));
    }

    /**
     * Actualiza el aliado especificado en el almacenamiento.
     */
    public function updateAlly(Request $request, Ally $ally)
    {
        // 1. Validar los datos de entrada para la actualización
        $validatedData = $request->validate([
            'company_name'      => 'required|string|max:255|unique:allies,company_name,' . $ally->id, // Excluir ID actual
            'company_rif'       => 'nullable|string|max:255|unique:allies,company_rif,' . $ally->id,
            'description'       => 'nullable|string|max:1000',
            'image_url'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'category_name'     => 'required|string|max:255', // Ahora es un nombre, no un ID
            'sub_category_name' => 'nullable|string|max:255', // Ahora es un nombre, no un ID
            'business_type_name' => 'required|string|max:255', // Ahora es un nombre, no un ID

            'status'            => ['required', Rule::in(['activo', 'pendiente', 'inactivo'])],
            'contact_person_name' => 'required|string|max:255',
            'contact_email'     => 'required|email|max:255|unique:allies,contact_email,' . $ally->id,
            'contact_phone'     => 'required|string|max:20',
            'contact_phone_alt' => 'nullable|string|max:20',
            'company_address'   => 'nullable|string|max:500',
            'website_url'       => 'nullable|url|max:255',
            'discount'          => 'nullable|string|max:500',
            'notes'             => 'nullable|string|max:500',
            'registered_at'     => 'required|date',
        ], [
            // Mensajes de validación personalizados
            'company_name.unique'       => 'Ya existe un aliado con este nombre.',
            'company_rif.unique'        => 'Ya existe un aliado con este RIF.',
            'contact_email.unique'      => 'Ya existe un aliado con este correo electrónico de contacto.',
            'description.max'           => 'La descripción no puede exceder los :max caracteres.',
            'image_url.image'           => 'El archivo debe ser una imagen.',
            'image_url.mimes'           => 'La imagen debe ser de tipo: jpeg, png, jpg, gif, svg.',
            'image_url.max'             => 'La imagen no puede pesar más de 2MB.',
            'category_name.required'    => 'La categoría de negocio es obligatoria.',
            'business_type_name.required' => 'El tipo de negocio es obligatorio.',
            'registered_at.required'    => 'La fecha de registro es obligatoria.',
            'registered_at.date'        => 'Ingrese una fecha de registro válida.',
        ]);

        // Iniciar una transacción de base de datos para la actualización
        DB::beginTransaction();

        try {
            // 2. Crear o encontrar la Categoría
            $category = Category::firstOrCreate(
                ['name' => $validatedData['category_name']],
                ['slug' => Str::slug($validatedData['category_name'])]
            );

            // 3. Crear o encontrar la Subcategoría (si se proporciona)
            $subCategory = null;
            if (!empty($validatedData['sub_category_name'])) {
                $subCategory = SubCategory::firstOrCreate(
                    ['name' => $validatedData['sub_category_name']],
                    [
                        'category_id' => $category->id,
                        'slug'        => Str::slug($validatedData['sub_category_name'])
                    ]
                );
            }

            // 4. Crear o encontrar el Tipo de Negocio
            $businessType = BusinessType::firstOrCreate(
                ['name' => $validatedData['business_type_name']],
                ['slug' => Str::slug($validatedData['business_type_name'])]
            );

            // 5. Manejar la subida de la nueva imagen
            $imagePath = $ally->image_url; // Mantener la imagen existente por defecto
            if ($request->hasFile('image_url')) {
                // Eliminar la imagen anterior si existe
                if ($ally->image_url) {
                    Storage::disk('public')->delete($ally->image_url);
                }
                // Guardar la nueva imagen
                $imagePath = $request->file('image_url')->store('allies', 'public');
            }

            // 6. Preparar los datos para la actualización del aliado
            $ally->update([
                'company_name'      => $validatedData['company_name'],
                'company_rif'       => $validatedData['company_rif'],
                'description'       => $validatedData['description'] ?? null,
                'image_url'         => $imagePath,
                'category_id'       => $category->id,
                'sub_category_id'   => $subCategory ? $subCategory->id : null,
                'business_type_id'  => $businessType->id,
                'contact_person_name' => $validatedData['contact_person_name'],
                'contact_email'     => $validatedData['contact_email'],
                'contact_phone'     => $validatedData['contact_phone'],
                'contact_phone_alt' => $validatedData['contact_phone_alt'] ?? null,
                'company_address'   => $validatedData['company_address'] ?? null,
                'website_url'       => $validatedData['website_url'] ?? null,
                'discount'          => $validatedData['discount'] ?? null,
                'notes'             => $validatedData['notes'] ?? null,
                'registered_at'     => $validatedData['registered_at'],
                'status'            => $validatedData['status'],
            ]);

            // Si todo fue bien, confirmar la transacción
            DB::commit();

            return redirect()->route('aliados.index')->with('success', '¡Aliado actualizado exitosamente!');

        } catch (\Exception $e) {
            // Si algo falla, revertir la transacción
            DB::rollBack();

            // Loggear el error para depuración
            Log::error('Error al actualizar aliado: ' . $e->getMessage());

            // Redirigir de vuelta con un mensaje de error y los datos antiguos
            return back()->withInput()->with('error', 'Hubo un error al actualizar el aliado. Por favor, intente de nuevo.')->withErrors(['general' => $e->getMessage()]);
        }
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
}