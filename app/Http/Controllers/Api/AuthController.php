<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Registra un nuevo usuario y devuelve un token de autenticación.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        try {
            // 1. Validar los datos de entrada
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'user_type' => ['required', 'string', 'in:user,partner'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'age' => ['required', 'integer', 'min:1'], // Nuevo campo 'age'
                'identification' => ['required', 'string', 'max:50', 'unique:users'],
                'address' => ['required', 'string', 'max:255'],
            ]);
        } catch (ValidationException $e) {
            // Devuelve un error 422 con los mensajes de validación
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        }

        // 2. Crear el usuario en la base de datos
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'user_type' => $request->user_type,
            'password' => Hash::make($request->password),
            'age' => $request->age, // Se agrega el campo 'age'
            'identification' => $request->identification,
            'address' => $request->address,
        ]);

        // 3. Generar token de autenticación para la API (Laravel Sanctum)
        $token = $user->createToken('authToken')->plainTextToken;

        // 4. Devolver una respuesta JSON exitosa
        return response()->json([
            'message' => 'Usuario registrado exitosamente',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    /**
     * Maneja el intento de login del usuario.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function login(Request $request): JsonResponse
    {
        // 1. Validar los datos de entrada
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. Intentar autenticar al usuario
        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        // 3. Obtener el usuario autenticado
        $user = Auth::user();

        // 4. Crear un token de acceso para el usuario
        $token = $user->createToken('auth_token')->plainTextToken;

        // 5. Devolver la respuesta JSON con el token y los datos del usuario
        return response()->json([
            'message' => 'Login exitoso',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    /**
     * Revoca el token de acceso del usuario autenticado (logout).
     * Requiere autenticación (middleware 'auth:sanctum').
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada exitosamente.'
        ]);
    }

    /**
     * Obtiene los datos del usuario autenticado.
     * Requiere autenticación (middleware 'auth:sanctum').
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function showProfile(Request $request): JsonResponse
    {
        $user = $request->user()->only([
            'name',
            'email',
            'identification',
            'address',
            'age', // Se agrega el campo 'age'
        ]);

        return response()->json([
            'message' => 'Perfil de usuario recuperado exitosamente.',
            'user' => $user,
        ]);
    }

    /**
     * Actualiza los datos del perfil del usuario autenticado.
     * Requiere autenticación (middleware 'auth:sanctum').
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        try {
            // Validar los campos que se pueden actualizar.
            $request->validate([
                'name' => ['sometimes', 'string', 'max:255'],
                'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
                'password' => ['sometimes', 'string', 'min:8', 'confirmed'],
                'identification' => ['sometimes', 'string', 'max:50', 'unique:users,identification,' . $user->id],
                'address' => ['sometimes', 'string'],
                'age' => ['sometimes', 'integer', 'min:1'], // Se agrega el campo 'age'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        }

        if ($request->has('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->fill($request->only('name', 'email', 'identification', 'address', 'age')); // Se agrega el campo 'age'
        $user->save();

        return response()->json([
            'message' => 'Perfil actualizado exitosamente.',
            'user' => $user,
        ]);
    }
}