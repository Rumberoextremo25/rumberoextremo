<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use App\Models\User; // Asegúrate de importar tu modelo User

class AuthController extends Controller
{
    /**
     * Maneja el intento de login del usuario.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // 1. Validar los datos de entrada
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. Intentar autenticar al usuario
        if (!Auth::attempt($request->only('email', 'password'))) {
            // Si las credenciales son incorrectas, lanza una excepción de validación.
            // Esto es bueno para que el frontend pueda manejar el error de forma consistente.
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }

        // 3. Obtener el usuario autenticado
        $user = Auth::user();

        // 4. Crear un token de acceso para el usuario
        // 'auth_token' es el nombre del token, puedes cambiarlo si quieres.
        // plainTextToken devuelve el token que necesitas enviar al cliente (app Android).
        $token = $user->createToken('auth_token')->plainTextToken;

        // 5. Devolver la respuesta JSON con el token y los datos del usuario
        return response()->json([
            'message' => 'Login exitoso',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user, // Puedes personalizar qué datos del usuario devolver
        ]);
    }

    /**
     * Revoca el token de acceso del usuario autenticado (logout).
     * Requiere autenticación (middleware 'auth:sanctum').
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Revoca el token actual que se usó para la petición.
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada exitosamente.'
        ]);
    }

    /**
     * Obtiene los datos del usuario autenticado.
     * Requiere autenticación (middleware 'auth:sanctum').
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function user(Request $request)
    {
        // Simplemente devuelve el usuario autenticado.
        return response()->json($request->user());
    }
}