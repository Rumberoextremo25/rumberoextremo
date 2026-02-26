<?php
// app/Services/GeminiService.php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Ally;
use App\Models\User;

class GeminiService
{
    protected $client;
    protected $apiKey;
    protected $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('gemini.api_key');
        // Usar el modelo correcto de la lista: gemini-2.5-flash
        $this->apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

        $this->client = new Client([
            'timeout' => 30,
            'verify' => false,
        ]);
    }

    public function chat($mensajeUsuario, $usuarioId = null, $ubicacion = null, $categoriaFiltro = null)
    {
        try {
            if (empty($this->apiKey)) {
                Log::error('❌ Gemini API Key no configurada');
                return [
                    'success' => true,
                    'respuesta' => $this->respuestaLocal($mensajeUsuario),
                    'accion' => null,
                    'aliados_relevantes' => []
                ];
            }

            Log::info('Enviando a Gemini', [
                'key_prefix' => substr($this->apiKey, 0, 10),
                'modelo' => 'gemini-2.5-flash'
            ]);

            // Obtener usuario si existe
            $usuario = $usuarioId ? User::find($usuarioId) : null;
            $nombre = $usuario ? $usuario->name : 'Rumbero';

            // Obtener aliados para contexto
            $aliados = $this->obtenerAliadosContexto($ubicacion, $categoriaFiltro);

            // Construir prompt con contexto
            $prompt = $this->construirPrompt($mensajeUsuario, $nombre, $aliados);

            $response = $this->client->post($this->apiUrl . '?key=' . $this->apiKey, [
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 800,
                    ]
                ]
            ]);

            $resultado = json_decode($response->getBody(), true);
            $respuestaIA = $resultado['candidates'][0]['content']['parts'][0]['text'] ?? '';

            Log::info('✅ Gemini OK', ['respuesta' => substr($respuestaIA, 0, 50)]);

            // Guardar conversación
            $this->guardarConversacion($usuarioId, $mensajeUsuario, $respuestaIA);

            return [
                'success' => true,
                'respuesta' => $respuestaIA,
                'accion' => null,
                'aliados_relevantes' => array_slice($aliados, 0, 5)
            ];
        } catch (\Exception $e) {
            Log::error('❌ Error Gemini: ' . $e->getMessage());

            return [
                'success' => true,
                'respuesta' => $this->respuestaLocal($mensajeUsuario),
                'accion' => null,
                'aliados_relevantes' => []
            ];
        }
    }

    private function obtenerAliadosContexto($ubicacion = null, $categoriaId = null)
    {
        try {
            $query = Ally::where('status', 'activo')
                ->whereHas('promotions', function ($q) {
                    $q->where('status', 'active')
                        ->where(function ($q2) {
                            $q2->whereNull('expires_at')
                                ->orWhere('expires_at', '>', now());
                        });
                })
                ->with(['promotions' => function ($q) {
                    $q->where('status', 'active')
                        ->where(function ($q2) {
                            $q2->whereNull('expires_at')
                                ->orWhere('expires_at', '>', now());
                        });
                }, 'category']);

            if ($categoriaId) {
                $query->where('category_id', $categoriaId);
            }

            return $query->limit(10)->get()->map(function ($ally) {
                $promociones = $ally->promotions->map(function ($promo) {
                    $expira = $promo->expires_at ? " (vence " . $promo->expires_at->format('d/m/Y') . ")" : "";
                    return "- {$promo->title}: {$promo->discount} de descuento{$expira}";
                })->toArray();

                return [
                    'nombre' => $ally->company_name,
                    'categoria' => $ally->category->name ?? 'General',
                    'promociones' => $promociones,
                    'promociones_texto' => implode("\n", $promociones)
                ];
            })->toArray();
        } catch (\Exception $e) {
            Log::error('Error obteniendo aliados: ' . $e->getMessage());
            return [];
        }
    }

    private function construirPrompt($mensajeUsuario, $nombre, $aliados)
    {
        $fecha = now()->format('d/m/Y H:i');
        $hora = now()->format('H');
        $saludo = $this->obtenerSaludo($hora);

        // 👇 CONSTRUIR TEXTO CON ALIADOS REALES
        $aliadosTexto = "";
        $mejoresPorCategoria = [];

        if (empty($aliados)) {
            $aliadosTexto = "No hay aliados con promociones activas en este momento.";
            $recomendacionesTexto = "No hay recomendaciones disponibles en este momento.";
        } else {
            foreach ($aliados as $index => $ally) {
                $categoria = $ally['categoria'] ?? 'General';

                // Agrupar por categoría para recomendaciones
                if (!isset($mejoresPorCategoria[$categoria])) {
                    $mejoresPorCategoria[$categoria] = [];
                }
                $mejoresPorCategoria[$categoria][] = $ally;

                // Lista completa de aliados
                $aliadosTexto .= "\n" . ($index + 1) . ". **{$ally['nombre']}** - {$categoria}";

                if (!empty($ally['promociones_texto'])) {
                    $aliadosTexto .= "\n   🔥 Promociones:\n   {$ally['promociones_texto']}\n";
                }
            }

            // 👇 CONSTRUIR RECOMENDACIONES POR CATEGORÍA CON ALIADOS REALES
            $recomendacionesTexto = "";
            foreach ($mejoresPorCategoria as $categoria => $items) {
                $recomendacionesTexto .= "\n**{$categoria}:**\n";
                foreach (array_slice($items, 0, 3) as $ally) {
                    $recomendacionesTexto .= "- **{$ally['nombre']}**: {$ally['promociones_texto']}\n";
                }
            }
        }

        Log::info('Aliados enviados al prompt:', ['cantidad' => count($aliados)]);

        $prompt = <<<EOT
🎉 **¡EPA RUMBERO!** 🎉 Soy **RumberoAI**, tu pana de confianza en **Rumbero Extremo**. 

═══════════════════════════════════════════
🔥 **MI PERSONALIDAD (SÉ ASÍ):**
═══════════════════════════════════════════
- Soy **VENEZOLANO 100%**: hablo con flow, con calle, como la gente
- Expresiones que uso natural: "pana", "chévere", "epa", "ash", "vaina", "de pinga", "brutal", "pan comido"
- Soy **cercano** como un amigo de toda la vida, no un robot aburrido
- Tengo **energía positiva**, soy echador pa'lante
- Si algo está bueno, digo: "¡Esto está de pinga!" 
- Si algo no me cuadra: "Mano, eso no te lo recomiendo, mejor mira esto otro"

═══════════════════════════════════════════
📱 **GUÍA COMPLETA DE RUMBERO EXTREMO:**
═══════════════════════════════════════════

**1️⃣ CÓMO REGISTRARSE:**
- **App Android:** "Bájate la app desde Google Play, busca 'Rumbero Extremo'"
- **Web:** "Entra a www.rumberoextremo.com y dale a 'Registrarse'"
- **Redes sociales:** "Regístrate con Google o Facebook, es más rápido"

**2️⃣ CÓMO DESCARGAR LA APP:**
- **Android:** [Descargar App](https://play.google.com/store/apps/details?id=com.rumberoextremo)
- **Web (PWA):** "Desde el navegador, agrega a pantalla principal"

**3️⃣ CÓMO PAGAR DESDE LA APP:**
- **Métodos:** Pago móvil, transferencia, tarjetas
- **Pasos:** "Ve a tu perfil > 'Métodos de pago' > Agrega tu método"

**4️⃣ UBICACIONES Y MAPAS:**
- Cuando te recomiende un aliado, te daré su ubicación
- Si me das tu ubicación, te muestro los más cercanos

═══════════════════════════════════════════
🏆 **RECOMENDACIONES DE LUGARES POR CATEGORÍA (DATOS REALES):**
═══════════════════════════════════════════
{$recomendacionesTexto}

═══════════════════════════════════════════
📍 **INFORMACIÓN DE CONTACTO Y SOPORTE:**
═══════════════════════════════════════════
- **Email soporte:** soporteitsolutech@gmail.com
- **Instagram:** @rumberoextremo
- **Facebook:** rumberoextremo

═══════════════════════════════════════════
🔥 **ALIADOS CON DESCUENTOS ACTIVOS AHORA (DATOS REALES):**
═══════════════════════════════════════════
{$aliadosTexto}

═══════════════════════════════════════════
🎯 **RESPONDE AHORA CON ACTITUD:**
═══════════════════════════════════════════
**Usuario:** {$nombre}
**Pregunta:** {$mensajeUsuario}

**INSTRUCCIONES ESTRICTAS:**
1. **USA SIEMPRE los aliados reales listados arriba** - NO inventes aliados
2. Si el usuario pregunta por un lugar, búscalo en la lista de aliados
3. Menciona las promociones específicas de cada aliado
4. Da direcciones si están disponibles
5. Sé auténtico y venezolano

{$saludo}
EOT;

        return $prompt;
    }

    private function obtenerSaludo($hora)
    {
        if ($hora >= 5 && $hora < 12) {
            return "¡Buenos días Rumbero! 🌅 ¿En qué te puedo ayudar hoy?";
        } elseif ($hora >= 12 && $hora < 18) {
            return "¡Buenas tardes Rumbero! ☀️ ¿Qué necesitas saber?";
        } elseif ($hora >= 18 && $hora < 22) {
            return "¡Buenas noches Rumbero! 🌆 ¿Listo para la rumba?";
        } else {
            return "¡Epa Rumbero! 🌙 ¿Trasnochando o con planes nocturnos?";
        }
    }

    private function guardarConversacion($usuarioId, $mensaje, $respuesta)
    {
        if (!$usuarioId) return;

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

    private function respuestaLocal($mensaje)
    {
        $mensajeLower = strtolower($mensaje);

        $respuestas = [
            'farmacia' => "💊 ¡Claro Rumbero! Aquí tienes algunas farmacias con descuentos activos:\n\n• Farmatodo: 20% en medicamentos\n• Locatel: 15% en productos de cuidado personal\n\n¿Quieres conocer la dirección de la más cercana?",

            'restaurante' => "🍔 ¡Qué hambre! Estos restaurantes tienen promociones:\n\n• La Casa del Chef: 2x1 en pastas\n• Sabor Peruano: 15% en platos principales\n• Pizzería Don Luigi: €5 de descuento\n\n¿Cuál te llama más la atención?",

            'discoteca' => "🎉 ¡La rumba te espera!\n\n• Mandala Lounge: Cover gratis antes 1am\n• Pacha Club: 2x1 en tragos\n• Tequila Bar: 20% en mesas\n\n¿Para cuántas personas?",

            'posada' => "🏨 ¿Buscando hospedaje?\n\n• Posada El Encanto: 15% fin de semana\n• Hotel Boutique Casa Blanca: Noche gratis al reservar 3\n\n¿Qué fechas?",

            'default' => "🎯 ¡Hola Rumbero! Soy RumberoAI. ¿En qué puedo ayudarte?\n\nPuedo recomendarte:\n• 💊 Farmacias\n• 🍔 Restaurantes\n• 🎉 Discotecas\n• 🏨 Posadas"
        ];

        foreach ($respuestas as $key => $respuesta) {
            if ($key !== 'default' && str_contains($mensajeLower, $key)) {
                return $respuesta;
            }
        }

        return $respuestas['default'];
    }
}
