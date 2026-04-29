<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\Promotion;
use App\Models\DiscountActivation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class RumberoAIController extends Controller
{
    /**
     * Obtener o crear sesión de chat
     */
    protected function getSessionId(Request $request)
    {
        $sessionId = $request->header('X-Chat-Session');

        if (!$sessionId) {
            $sessionId = $request->input('session_id');
        }

        if (!$sessionId) {
            $sessionId = Str::random(32);
        }

        return $sessionId;
    }

    /**
     * Endpoint principal del chat - Usuario envía mensaje
     */
    public function chat(Request $request)
    {
        $request->validate([
            'mensaje' => 'required|string|max:1000',
        ]);

        try {
            $usuario = $request->user();
            $sessionId = $this->getSessionId($request);

            Log::info('💬 Chat - Mensaje recibido', [
                'usuario' => $usuario?->id,
                'session_id' => $sessionId,
                'mensaje' => $request->mensaje
            ]);

            // Guardar mensaje del usuario
            $userMessage = ChatMessage::create([
                'user_id' => $usuario?->id,
                'session_id' => $sessionId,
                'message' => $request->mensaje,
                'sender' => 'user',
                'status' => 'pending'
            ]);

            // Respuesta automática
            $respuestaAuto = "📱 *Mensaje recibido!*\n\nUn asesor Rumbero Extremo te responderá en breve.\n\n*Tu consulta:*\n\"" . substr($request->mensaje, 0, 100) . (strlen($request->mensaje) > 100 ? '...' : '') . "\"\n\n⏰ Tiempo estimado de respuesta: menos de 5 minutos.";

            return response()->json([
                'success' => true,
                'data' => [
                    'respuesta' => $respuestaAuto,
                    'message_id' => $userMessage->id,
                    'session_id' => $sessionId,
                    'status' => 'pending'
                ],
                'message' => 'Mensaje enviado. Un asesor te responderá pronto.'
            ]);
        } catch (\Exception $e) {
            Log::error('❌ Error en chat: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al enviar el mensaje. Intenta de nuevo.'
            ], 500);
        }
    }

    /**
     * ADMIN: Obtener TODAS las conversaciones (sin filtrar por estado)
     */
    public function mensajesPendientes(Request $request)
    {
        // Verificar admin
        if (!Auth::user() || Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        // Obtener TODOS los mensajes de usuarios, agrupados por session_id
        // Traer el último mensaje de cada conversación
        $conversaciones = ChatMessage::where('sender', 'user')
            ->select(
                'session_id',
                DB::raw('MAX(created_at) as last_message_time'),
                DB::raw('COUNT(*) as total_messages')
            )
            ->groupBy('session_id')
            ->orderBy('last_message_time', 'desc')
            ->get();

        $resultados = [];

        foreach ($conversaciones as $conv) {
            // Obtener el último mensaje de esta conversación
            $ultimoMensaje = ChatMessage::where('session_id', $conv->session_id)
                ->where('sender', 'user')
                ->latest()
                ->first();

            // Obtener información del usuario si existe
            $userInfo = ChatMessage::where('session_id', $conv->session_id)
                ->whereNotNull('user_id')
                ->first();

            $userName = 'Usuario invitado';
            $userEmail = 'No registrado';

            if ($userInfo && $userInfo->user) {
                $userName = $userInfo->user->name ?? 'Usuario invitado';
                $userEmail = $userInfo->user->email ?? 'No registrado';
            }

            $resultados[] = [
                'id' => $ultimoMensaje->id,
                'session_id' => $conv->session_id,
                'message' => $ultimoMensaje->message,
                'user_name' => $userName,
                'user_email' => $userEmail,
                'total_messages' => $conv->total_messages,
                'created_at' => $ultimoMensaje->created_at->format('Y-m-d H:i:s'),
                'time_ago' => $ultimoMensaje->created_at->diffForHumans(),
            ];
        }

        Log::info('📋 Admin consultó conversaciones', [
            'total_conversaciones' => count($resultados),
            'admin_id' => Auth::id()
        ]);

        return response()->json([
            'success' => true,
            'data' => $resultados
        ]);
    }

    /**
     * ADMIN: Responder mensaje
     */
    public function adminResponder(Request $request)
    {
        // Verificar admin
        if (!Auth::user() || Auth::user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }

        $request->validate([
            'session_id' => 'required|string',
            'respuesta' => 'required|string|max:2000'
        ]);

        try {
            // Buscar un mensaje de usuario de esta sesión para obtener user_id
            $userMessage = ChatMessage::where('session_id', $request->session_id)
                ->where('sender', 'user')
                ->first();

            if (!$userMessage) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró la conversación'
                ], 404);
            }

            // Guardar respuesta del admin
            $adminResponse = ChatMessage::create([
                'user_id' => $userMessage->user_id,
                'session_id' => $request->session_id,
                'message' => $request->respuesta,
                'sender' => 'admin',
                'status' => 'answered',
                'answered_by' => Auth::id(),
                'answered_at' => now()
            ]);

            Log::info('✅ Admin respondió mensaje', [
                'admin_id' => Auth::id(),
                'session_id' => $request->session_id,
                'response_id' => $adminResponse->id
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'response_id' => $adminResponse->id,
                    'message' => 'Respuesta enviada exitosamente'
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error al responder: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al enviar la respuesta'
            ], 500);
        }
    }

    /**
     * Obtener conversación completa por sesión
     */
    public function conversacion(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string'
        ]);

        $messages = ChatMessage::where('session_id', $request->session_id)
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $messages->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'message' => $msg->message,
                    'sender' => $msg->sender,
                    'status' => $msg->status,
                    'created_at' => $msg->created_at->format('Y-m-d H:i:s'),
                    'is_admin' => $msg->sender === 'admin',
                    'user_name' => $msg->user?->name ?? 'Usuario invitado'
                ];
            })
        ]);
    }

    /**
     * Mostrar la vista del panel de administración del chat
     */
    public function adminChatView()
    {
        if (!Auth::user() || Auth::user()->role !== 'admin') {
            abort(403, 'No autorizado');
        }

        return view('Admin.chat');  // ← Debe ser exactamente 'admin.chat'
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

            if (!$promocion->isAvailable()) {
                return response()->json([
                    'success' => false,
                    'message' => '¡Qué pena! Esta promoción ya no está disponible.'
                ], 400);
            }

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

            $codigo = 'RUM-' . strtoupper(substr(md5($usuario->id . $promocion->id . time()), 0, 8));

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
                ->where(function ($q) {
                    $q->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                });

            if ($request->has('categoria')) {
                $query->whereHas('ally', function ($q) use ($request) {
                    $q->where('category', $request->categoria);
                });
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%")
                        ->orWhere('description', 'LIKE', "%{$search}%")
                        ->orWhereHas('ally', function ($q2) use ($search) {
                            $q2->where('company_name', 'LIKE', "%{$search}%");
                        });
                });
            }

            $promociones = $query->latest()
                ->paginate($request->per_page ?? 10)
                ->through(function ($promocion) {
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
