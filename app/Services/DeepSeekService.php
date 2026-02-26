<?php
namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use App\Models\Ally; // Tu modelo existente
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeepSeekService
{
    protected $client;
    protected $apiKey;
    protected $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('deepseek.api_key');
        $this->apiUrl = config('deepseek.api_url');
        $this->client = new Client([
            'timeout' => config('deepseek.timeout', 30),
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ]
        ]);
    }

    /**
     * Obtiene aliados cercanos usando tu modelo Ally
     */
    private function obtenerAliadosCercanos($ubicacion = null, $categoria = null)
    {
        $query = Ally::active() // Usando tu scope active()
                    ->whereNotNull('discount') // Solo aliados con descuento
                    ->with(['category', 'businessType']); // Cargar relaciones
        
        // Si se especifica categoría
        if ($categoria) {
            $query->whereHas('category', function($q) use ($categoria) {
                $q->where('name', 'LIKE', "%{$categoria}%")
                  ->orWhere('id', $categoria);
            });
        }
        
        // Si hay ubicación, ordenar por proximidad (asumiendo que tienes lat/lng)
        if ($ubicacion && isset($ubicacion['lat']) && isset($ubicacion['lng'])) {
            // Fórmula de Haversine para distancia
            $query->selectRaw("allies.*, 
                (6371 * acos(cos(radians(?)) * cos(radians(latitud)) * 
                cos(radians(longitud) - radians(?)) + sin(radians(?)) * 
                sin(radians(latitud)))) AS distancia", 
                [$ubicacion['lat'], $ubicacion['lng'], $ubicacion['lat']])
                ->having('distancia', '<', 20) // Radio de 20km
                ->orderBy('distancia');
        }
        
        return $query->limit(30)->get()->map(function($ally) {
            return [
                'id' => $ally->id,
                'company_name' => $ally->company_name,
                'business_type' => $ally->businessType->name ?? 'General',
                'category' => $ally->category->name ?? 'Sin categoría',
                'discount' => $ally->discount,
                'address' => $ally->company_address,
                'phone' => $ally->contact_phone,
                'description' => $ally->description,
                'image_url' => $ally->image_url,
                'status' => $ally->status,
                'distance' => $ally->distancia ?? null,
                'commission' => $ally->commission_percentage,
            ];
        })->toArray();
    }

    /**
     * Construye el system prompt usando tus categorías y datos reales
     */
    private function construirSystemPrompt($usuario, $aliados, $categoriasDisponibles)
    {
        $nombre = $usuario ? $usuario->name : 'Rumbero';
        $preferencias = $usuario->preferencias ?? 'no específicas';
        
        // Agrupar aliados por categoría para mejor contexto
        $aliadosPorCategoria = [];
        foreach ($aliados as $aliado) {
            $cat = $aliado['category'];
            if (!isset($aliadosPorCategoria[$cat])) {
                $aliadosPorCategoria[$cat] = [];
            }
            $aliadosPorCategoria[$cat][] = $aliado;
        }
        
        $prompt = "Eres 'RumberoAI', el asistente oficial de Rumbero Extremo.

INFORMACIÓN DEL USUARIO:
- Nombre: {$nombre}
- Preferencias: {$preferencias}
- Fecha: " . now()->format('d/m/Y H:i') . "

CATEGORÍAS DISPONIBLES EN LA PLATAFORMA:
" . json_encode($categoriasDisponibles, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "

ALIADOS ACTIVOS CON DESCUENTOS (ORGANIZADOS POR CATEGORÍA):
" . json_encode($aliadosPorCategoria, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "

TU PERSONALIDAD:
- Eres un amigo experto en Venezuela, siempre positivo y con energía
- Usas un tono juvenil, cercano y divertido
- Te emocionas con los planes y descuentos
- Llamas al usuario 'Rumbero' o 'Rumbera'

REGLAS DE ORO:
1. Siempre recomienda aliados con descuento activo
2. Menciona el porcentaje de descuento exacto de cada aliado
3. Si preguntan por algo sin aliados, sé honesto pero sugiere alternativas cercanas
4. Puedes recomendar múltiples opciones y preguntar preferencias
5. Detecta cuándo el usuario quiere ACTIVAR un descuento
6. Usa emojis relacionados (🎉 para rumba, 🍔 para comida, 💊 para farmacia)

FORMATO IDEAL DE RESPUESTA:
- Saludo con energía
- Recomendación principal (nombre del aliado, descuento, ubicación)
- 1-2 alternativas
- Pregunta para continuar o acción a tomar";

        return $prompt;
    }

    /**
     * Método principal para el chat
     */
    public function chat($mensajeUsuario, $usuarioId = null, $ubicacion = null, $categoriaFiltro = null)
    {
        try {
            // Obtener usuario si existe
            $usuario = $usuarioId ? User::find($usuarioId) : null;
            
            // Obtener aliados relevantes
            $aliadosCercanos = $this->obtenerAliadosCercanos($ubicacion, $categoriaFiltro);
            
            // Obtener categorías únicas de los aliados para contexto
            $categoriasDisponibles = Ally::active()
                ->with('category')
                ->get()
                ->pluck('category.name')
                ->unique()
                ->filter()
                ->values()
                ->toArray();
            
            // Obtener historial del usuario (si implementamos tabla conversaciones)
            $historial = $this->obtenerHistorialConversacion($usuarioId);
            
            // Construir mensajes para DeepSeek
            $messages = [
                ['role' => 'system', 'content' => $this->construirSystemPrompt(
                    $usuario, 
                    $aliadosCercanos,
                    $categoriasDisponibles
                )]
            ];
            
            // Añadir historial si existe
            foreach ($historial as $msg) {
                $messages[] = ['role' => 'user', 'content' => $msg->user_message];
                $messages[] = ['role' => 'assistant', 'content' => $msg->ai_response];
            }
            
            // Añadir mensaje actual
            $messages[] = ['role' => 'user', 'content' => $mensajeUsuario];
            
            // Llamar a DeepSeek
            $response = $this->client->post($this->apiUrl, [
                'json' => [
                    'model' => config('deepseek.model', 'deepseek-chat'),
                    'messages' => $messages,
                    'temperature' => 0.7,
                    'max_tokens' => 800,
                ]
            ]);
            
            $resultado = json_decode($response->getBody(), true);
            $respuestaIA = $resultado['choices'][0]['message']['content'] ?? '';
            
            // Guardar conversación
            $this->guardarConversacion($usuarioId, $mensajeUsuario, $respuestaIA);
            
            // Detectar si el usuario quiere activar un descuento
            $accion = $this->detectarIntencionActivarDescuento($mensajeUsuario, $respuestaIA);
            
            return [
                'success' => true,
                'respuesta' => $respuestaIA,
                'accion' => $accion,
                'aliados_relevantes' => array_slice($aliadosCercanos, 0, 5), // Top 5
                'categorias' => $categoriasDisponibles
            ];
            
        } catch (\Exception $e) {
            Log::error('Error en DeepSeek: ' . $e->getMessage());
            return [
                'success' => false,
                'respuesta' => '¡Ey Rumbero! Estoy teniendo problemas técnicos. ¡Intenta de nuevo en un momento! 🎉',
                'accion' => null,
                'aliados_relevantes' => []
            ];
        }
    }

    /**
     * Detecta si el usuario quiere activar un descuento
     */
    private function detectarIntencionActivarDescuento($mensajeUsuario, $respuestaIA)
    {
        $palabrasClave = [
            'activar', 'quiero ese', 'dame el', 'cómo activo', 
            'usar descuento', 'tomar descuento', 'acepto', 'me interesa'
        ];
        
        $mensajeLower = strtolower($mensajeUsuario);
        
        foreach ($palabrasClave as $keyword) {
            if (str_contains($mensajeLower, $keyword)) {
                // Extraer posible aliado mencionado
                return [
                    'tipo' => 'activar_descuento',
                    'mensaje' => 'El usuario quiere activar un descuento'
                ];
            }
        }
        
        return null;
    }

    /**
     * Guarda la conversación en BD
     */
    private function guardarConversacion($usuarioId, $mensaje, $respuesta)
    {
        if (!$usuarioId) return;
        
        // Crear modelo ConversacionIA (lo crearemos después)
        DB::table('ia_conversations')->insert([
            'user_id' => $usuarioId,
            'user_message' => $mensaje,
            'ai_response' => $respuesta,
            'created_at' => now()
        ]);
    }

    /**
     * Obtiene historial de conversación
     */
    private function obtenerHistorialConversacion($usuarioId)
    {
        if (!$usuarioId) return [];
        
        return DB::table('ia_conversations')
                 ->where('user_id', $usuarioId)
                 ->orderBy('created_at', 'desc')
                 ->limit(5)
                 ->get()
                 ->reverse();
    }

    /**
     * Activa un descuento para un aliado específico
     */
    public function activarDescuento($usuarioId, $allyId)
    {
        try {
            $ally = Ally::findOrFail($allyId);
            $usuario = User::findOrFail($usuarioId);
            
            // Generar código único
            $codigo = 'RUM-' . strtoupper(substr(md5($usuarioId . $allyId . time()), 0, 8));
            
            // Registrar la activación (necesitarás crear esta tabla)
            $activacion = DB::table('discount_activations')->insert([
                'user_id' => $usuarioId,
                'ally_id' => $allyId,
                'code' => $codigo,
                'discount' => $ally->discount,
                'status' => 'active',
                'expires_at' => now()->addDays(7),
                'created_at' => now()
            ]);
            
            return [
                'success' => true,
                'codigo' => $codigo,
                'ally_name' => $ally->company_name,
                'discount' => $ally->discount,
                'message' => "¡Descuento activado! Presenta este código en {$ally->company_name}: {$codigo}"
            ];
            
        } catch (\Exception $e) {
            Log::error('Error activando descuento: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error activando el descuento. Intenta de nuevo.'
            ];
        }
    }
}