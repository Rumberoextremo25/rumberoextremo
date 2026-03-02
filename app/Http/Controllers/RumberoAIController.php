<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use App\Models\DiscountActivation;
use App\Services\AIService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class RumberoAIController extends Controller
{
    protected $aiService;

    /**
     * Constructor: Inyectamos el servicio de IA
     */
    public function __construct(AIService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Endpoint principal del chat - ¡AHORA CON RESPUESTAS REALES!
     */
    public function chat(Request $request)
    {
        $request->validate([
            'mensaje' => 'required|string|max:1000',
            'ubicacion' => 'sometimes|array',
            'ubicacion.lat' => 'required_with:ubicacion|numeric',
            'ubicacion.lng' => 'required_with:ubicacion|numeric',
            'categoria' => 'sometimes|string|nullable',
            'ia_preferida' => 'sometimes|in:deepseek,gemini'
        ]);

        try {
            $usuario = $request->user();
            $iaPreferida = $request->ia_preferida ?? 'gemini';

            Log::info('🎉 Chat RumberoAI - Mensaje recibido', [
                'usuario' => $usuario?->id,
                'ia_preferida' => $iaPreferida,
                'mensaje' => $request->mensaje
            ]);

            // Procesar mensaje con AIService
            $respuesta = $this->aiService->chat(
                $request->mensaje,
                $usuario ? $usuario->id : null,
                $request->ubicacion,
                $request->categoria,
                $iaPreferida
            );

            Log::info('✅ Respuesta generada', [
                'ia_utilizada' => $respuesta['ia_utilizada'] ?? 'gemini'
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'respuesta' => $respuesta['respuesta'] ?? $respuesta,
                    'ia_utilizada' => $respuesta['ia_utilizada'] ?? 'gemini',
                    'promociones' => $respuesta['promociones'] ?? null
                ],
                'message' => 'Respuesta generada correctamente'
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Error en chat: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => '¡Ay parce! Algo salió mal. Intenta de nuevo.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Endpoint para activar un descuento
     */
    public function activarDescuento(Request $request)
    {
        $request->validate([
            'promotion_id' => 'required|exists:promotions,id'
        ]);

        try {
            $usuario = $request->user();
            
            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => '¡Ojo parce! Debes iniciar sesión para activar descuentos.'
                ], 401);
            }

            $promocion = Promotion::with('ally')->findOrFail($request->promotion_id);
            
            // Verificar disponibilidad
            if (!$promocion->isAvailable()) {
                return response()->json([
                    'success' => false,
                    'message' => '¡Qué pena! Esta promoción ya no está disponible.'
                ], 400);
            }

            // Verificar si ya activó esta promoción
            $activacionExistente = DiscountActivation::where('user_id', $usuario->id)
                ->where('promotion_id', $promocion->id)
                ->where('status', 'active')
                ->first();

            if ($activacionExistente) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'codigo' => $activacionExistente->code,
                        'promocion' => $promocion->title,
                        'aliado' => $promocion->ally->company_name,
                        'descuento' => $promocion->discount,
                        'expira' => $activacionExistente->expires_at->format('d/m/Y'),
                        'mensaje' => "🔥 Ya tienes un código activo: {$activacionExistente->code}"
                    ]
                ]);
            }

            // Generar código único
            $codigo = 'RUM-' . strtoupper(substr(md5($usuario->id . $promocion->id . time()), 0, 8));

            // Registrar activación
            $activacion = DiscountActivation::create([
                'user_id' => $usuario->id,
                'ally_id' => $promocion->ally_id,
                'promotion_id' => $promocion->id,
                'code' => $codigo,
                'discount' => $promocion->discount,
                'title' => $promocion->title,
                'description' => $promocion->description,
                'status' => 'active',
                'expires_at' => $promocion->expires_at ?? now()->addDays(7),
                'ip_address' => $request->ip(),
                'device_info' => $request->userAgent()
            ]);

            $promocion->increment('current_uses');

            return response()->json([
                'success' => true,
                'data' => [
                    'codigo' => $codigo,
                    'promocion' => $promocion->title,
                    'aliado' => $promocion->ally->company_name,
                    'descuento' => $promocion->discount,
                    'expira' => $activacion->expires_at->format('d/m/Y'),
                    'mensaje' => "🎉 ¡Promoción activada! Tu código: {$codigo}"
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error activando descuento: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error activando el descuento. Intenta de nuevo.'
            ], 500);
        }
    }

    /**
     * Listar promociones activas
     */
    public function promocionesActivas(Request $request)
    {
        try {
            $query = Promotion::with('ally')
                ->where('status', 'active')
                ->where(function($q) {
                    $q->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
                });

            if ($request->has('categoria')) {
                $query->whereHas('ally', function($q) use ($request) {
                    $q->where('category', $request->categoria);
                });
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%")
                      ->orWhere('description', 'LIKE', "%{$search}%")
                      ->orWhereHas('ally', function($q2) use ($search) {
                          $q2->where('company_name', 'LIKE', "%{$search}%");
                      });
                });
            }

            $promociones = $query->latest()
                ->paginate($request->per_page ?? 10)
                ->through(function($promocion) {
                    return [
                        'id' => $promocion->id,
                        'title' => $promocion->title,
                        'discount' => $promocion->discount,
                        'description' => $promocion->description,
                        'image_url' => $promocion->image_url,
                        'expires_at' => $promocion->expires_at?->format('d/m/Y'),
                        'days_remaining' => $promocion->expires_at ? now()->diffInDays($promocion->expires_at, false) : null,
                        'ally' => [
                            'id' => $promocion->ally->id,
                            'name' => $promocion->ally->company_name,
                            'address' => $promocion->ally->company_address,
                            'phone' => $promocion->ally->contact_phone,
                            'image' => $promocion->ally->image_url
                        ]
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $promociones
            ]);

        } catch (\Exception $e) {
            Log::error('Error listando promociones: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error cargando promociones'
            ], 500);
        }
    }
}