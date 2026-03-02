<?php
// app/Services/DeepSeekService.php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use App\Models\Ally;
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
        try {
            $query = Ally::active();
            
            // Solo aliados con descuento
            $query->whereNotNull('discount');
            
            // Cargar relaciones
            $query->with(['category', 'businessType']);
            
            // Si se especifica categoría
            if ($categoria) {
                $query->whereHas('category', function($q) use ($categoria) {
                    $q->where('name', 'LIKE', "%{$categoria}%")
                      ->orWhere('id', $categoria);
                });
            }
            
            // Si hay ubicación, ordenar por proximidad
            if ($ubicacion && isset($ubicacion['lat']) && isset($ubicacion['lng'])) {
                // Fórmula de Haversine para distancia
                $query->selectRaw("allies.*, 
                    (6371 * acos(cos(radians(?)) * cos(radians(latitud)) * 
                    cos(radians(longitud) - radians(?)) + sin(radians(?)) * 
                    sin(radians(latitud)))) AS distancia", 
                    [$ubicacion['lat'], $ubicacion['lng'], $ubicacion['lat']])
                    ->having('distancia', '<', 20)
                    ->orderBy('distancia');
            }
            
            $resultados = $query->limit(30)->get();
            $aliados = [];
            
            foreach ($resultados as $ally) {
                $aliados[] = [
                    'id' => $ally->id,
                    'company_name' => $ally->company_name,
                    'business_type' => isset($ally->businessType) ? $ally->businessType->name : 'General',
                    'category' => isset($ally->category) ? $ally->category->name : 'Sin categoría',
                    'discount' => $ally->discount,
                    'address' => $ally->company_address,
                    'phone' => $ally->contact_phone,
                    'description' => $ally->description,
                    'image_url' => $ally->image_url,
                    'status' => $ally->status,
                    'distance' => isset($ally->distancia) ? $ally->distancia : null,
                    'commission' => $ally->commission_percentage,
                ];
            }
            
            return $aliados;
            
        } catch (\Exception $e) {
            Log::error('Error obteniendo aliados cercanos: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Construye el system prompt usando tus categorías y datos reales
     */
    private function construirSystemPrompt($usuario, $aliados, $categoriasDisponibles)
    {
        $nombre = $usuario ? $usuario->name : 'Rumbero';
        $preferencias = $usuario && isset($usuario->preferencias) ? $usuario->preferencias : 'no específicas';
        
        // Agrupar aliados por categoría para mejor contexto
        $aliadosPorCategoria = [];
        foreach ($aliados as $aliado) {
            $cat = isset($aliado['category']) ? $aliado['category'] : 'General';
            if (!isset($aliadosPorCategoria[$cat])) {
                $aliadosPorCategoria[$cat] = [];
            }
            $aliadosPorCategoria[$cat][] = $aliado;
        }
        
        $categoriasJSON = json_encode($categoriasDisponibles, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $aliadosJSON = json_encode($aliadosPorCategoria, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        $prompt = "Eres 'RumberoAI', el asistente oficial de Rumbero Extremo.\n\n"
            . "INFORMACIÓN DEL USUARIO:\n"
            . "- Nombre: {$nombre}\n"
            . "- Preferencias: {$preferencias}\n"
            . "- Fecha: " . now()->format('d/m/Y H:i') . "\n\n"
            . "CATEGORÍAS DISPONIBLES EN LA PLATAFORMA:\n"
            . "{$categoriasJSON}\n\n"
            . "ALIADOS ACTIVOS CON DESCUENTOS (ORGANIZADOS POR CATEGORÍA):\n"
            . "{$aliadosJSON}\n\n"
            . "TU PERSONALIDAD:\n"
            . "- Eres un amigo experto en Venezuela, siempre positivo y con energía\n"
            . "- Usas un tono juvenil, cercano y divertido\n"
            . "- Te emocionas con los planes y descuentos\n"
            . "- Llamas al usuario 'Rumbero' o 'Rumbera'\n\n"
            . "REGLAS DE ORO:\n"
            . "1. Siempre recomienda aliados con descuento activo\n"
            . "2. Menciona el porcentaje de descuento exacto de cada aliado\n"
            . "3. Si preguntan por algo sin aliados, sé honesto pero sugiere alternativas cercanas\n"
            . "4. Puedes recomendar múltiples opciones y preguntar preferencias\n"
            . "5. Detecta cuándo el usuario quiere ACTIVAR un descuento\n"
            . "6. Usa emojis relacionados (🎉 para rumba, 🍔 para comida, 💊 para farmacia)\n\n"
            . "FORMATO IDEAL DE RESPUESTA:\n"
            . "- Saludo con energía\n"
            . "- Recomendación principal (nombre del aliado, descuento, ubicación)\n"
            . "- 1-2 alternativas\n"
            . "- Pregunta para continuar o acción a tomar";

        return $prompt;
    }

    /**
     * Método principal para el chat
     */
    public function chat($mensajeUsuario, $usuarioId = null, $ubicacion = null, $categoriaFiltro = null)
    {
        try {
            // Obtener usuario si existe
            $usuario = null;
            if ($usuarioId) {
                $usuario = User::find($usuarioId);
            }
            
            // Obtener aliados relevantes
            $aliadosCercanos = $this->obtenerAliadosCercanos($ubicacion, $categoriaFiltro);
            
            // Obtener categorías únicas de los aliados para contexto
            $categoriasDisponibles = [];
            try {
                $categorias = Ally::active()
                    ->with('category')
                    ->get()
                    ->pluck('category.name')
                    ->unique()
                    ->filter()
                    ->values()
                    ->toArray();
                $categoriasDisponibles = $categorias;
            } catch (\Exception $e) {
                Log::warning('Error obteniendo categorías: ' . $e->getMessage());
                $categoriasDisponibles = [];
            }
            
            // Obtener historial del usuario
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
            if (!empty($historial)) {
                foreach ($historial as $msg) {
                    $messages[] = ['role' => 'user', 'content' => $msg->user_message];
                    $messages[] = ['role' => 'assistant', 'content' => $msg->ai_response];
                }
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
            $respuestaIA = '';
            if (isset($resultado['choices'][0]['message']['content'])) {
                $respuestaIA = $resultado['choices'][0]['message']['content'];
            }
            
            // Guardar conversación
            $this->guardarConversacion($usuarioId, $mensajeUsuario, $respuestaIA);
            
            // Detectar si el usuario quiere activar un descuento
            $accion = $this->detectarIntencionActivarDescuento($mensajeUsuario, $respuestaIA);
            
            // Obtener top 5 aliados relevantes
            $aliadosRelevantes = array_slice($aliadosCercanos, 0, 5);
            
            return [
                'success' => true,
                'respuesta' => $respuestaIA,
                'accion' => $accion,
                'aliados_relevantes' => $aliadosRelevantes,
                'categorias' => $categoriasDisponibles
            ];
            
        } catch (\Exception $e) {
            Log::error('Error en DeepSeek: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
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
            if (strpos($mensajeLower, $keyword) !== false) {
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
        if (!$usuarioId) {
            return;
        }
        
        try {
            DB::table('ia_conversations')->insert([
                'user_id' => $usuarioId,
                'user_message' => $mensaje,
                'ai_response' => $respuesta,
                'created_at' => now()
            ]);
        } catch (\Exception $e) {
            Log::error('Error guardando conversación: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene historial de conversación
     */
    private function obtenerHistorialConversacion($usuarioId)
    {
        if (!$usuarioId) {
            return [];
        }
        
        try {
            $conversaciones = DB::table('ia_conversations')
                ->where('user_id', $usuarioId)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
            
            // Revertir el orden para tener cronología correcta
            return array_reverse($conversaciones->toArray());
            
        } catch (\Exception $e) {
            Log::error('Error obteniendo historial: ' . $e->getMessage());
            return [];
        }
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
            
            // Registrar la activación
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