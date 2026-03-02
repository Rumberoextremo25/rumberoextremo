<?php
// app/Services/GeminiService.php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Ally;
use App\Models\User;
use App\Models\Promotion;
use App\Models\DiscountActivation;

class GeminiService
{
    protected $client;
    protected $apiKey;
    protected $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('gemini.api_key');
        // ✅ MODELO CONFIABLE - PROBADO Y FUNCIONA
        $this->apiUrl = 'https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent';

        // Alternativas comentadas:
        // $this->apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';
        // $this->apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';
        // $this->apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent';

        $this->client = new Client([
            'timeout' => 30,
            'verify' => false,
        ]);
    }

    /**
     * Chat principal con múltiples roles
     */
    public function chat($mensajeUsuario, $usuarioId = null, $ubicacion = null, $categoriaFiltro = null, $rol = 'ventas')
    {
        try {
            if (empty($this->apiKey)) {
                Log::error('❌ Gemini API Key no configurada');
                return $this->respuestaError('Configuración de IA no disponible');
            }

            Log::info('🤖 RumberoAI - Consulta recibida', [
                'rol' => $rol,
                'usuario_id' => $usuarioId,
                'mensaje' => substr($mensajeUsuario, 0, 100)
            ]);

            // Obtener datos del usuario
            $usuario = null;
            if ($usuarioId) {
                $usuario = User::with(['ally', 'activations'])->find($usuarioId);
            }
            $nombre = $usuario ? $usuario->name : 'Rumbero';
            $email = $usuario ? $usuario->email : null;

            // Obtener datos relevantes según el rol
            $datosContexto = $this->obtenerDatosContexto($rol, $usuario, $ubicacion, $categoriaFiltro);

            // Construir prompt según el rol
            $prompt = $this->construirPromptPorRol(
                $rol,
                $mensajeUsuario,
                $nombre,
                $email,
                $datosContexto,
                $usuario
            );

            // Llamar a Gemini
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
                        'maxOutputTokens' => 1000,
                        'topP' => 0.95,
                        'topK' => 40
                    ]
                ]
            ]);

            $resultado = json_decode($response->getBody(), true);
            $respuestaIA = '';
            if (isset($resultado['candidates'][0]['content']['parts'][0]['text'])) {
                $respuestaIA = $resultado['candidates'][0]['content']['parts'][0]['text'];
            }

            Log::info('✅ Respuesta generada', [
                'rol' => $rol,
                'respuesta' => substr($respuestaIA, 0, 50)
            ]);

            // Procesar respuesta para detectar acciones
            $accion = $this->detectarAccion($respuestaIA, $rol);

            // Guardar conversación
            $this->guardarConversacion($usuarioId, $mensajeUsuario, $respuestaIA, $rol, $accion);

            return [
                'success' => true,
                'respuesta' => $respuestaIA,
                'accion' => $accion,
                'rol' => $rol,
                'datos_adicionales' => isset($datosContexto['resumen']) ? $datosContexto['resumen'] : null
            ];
        } catch (\Exception $e) {
            Log::error('❌ Error Gemini: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return $this->respuestaError('Error en el servicio. Por favor intenta de nuevo.');
        }
    }

    /**
     * Obtener datos según el rol
     */
    private function obtenerDatosContexto($rol, $usuario, $ubicacion, $categoriaFiltro)
    {
        $datos = [
            'aliados' => [],
            'promociones' => [],
            'transacciones' => [],
            'estadisticas' => [],
            'resumen' => null
        ];

        switch ($rol) {
            case 'ventas':
                $datos['aliados'] = $this->obtenerAliadosContexto($ubicacion, $categoriaFiltro);
                $datos['promociones'] = $this->obtenerPromocionesDestacadas();
                $datos['resumen'] = "🎯 " . count($datos['aliados']) . " aliados con ofertas activas";
                break;

            case 'negocios':
                if ($usuario && $usuario->ally) {
                    $datos['mis_promociones'] = $usuario->ally->promotions()
                        ->where('status', 'active')
                        ->count();
                    $datos['mis_ventas'] = $usuario->ally->transactions()
                        ->whereMonth('created_at', now()->month)
                        ->sum('amount');

                    $activaciones = DiscountActivation::where('ally_id', $usuario->ally->id)
                        ->whereMonth('created_at', now()->month)
                        ->count();
                    $datos['activaciones'] = $activaciones;
                }
                $datos['estadisticas'] = $this->obtenerEstadisticasPlataforma();
                break;

            case 'soporte':
                $datos['faq'] = $this->obtenerFAQ();
                if ($usuario) {
                    $datos['mis_activaciones'] = DiscountActivation::where('user_id', $usuario->id)
                        ->with('ally')
                        ->latest()
                        ->limit(5)
                        ->get();
                }
                break;

            case 'administrativo':
                $datos['total_usuarios'] = User::count();
                $datos['total_aliados'] = Ally::count();
                $datos['total_promociones'] = Promotion::count();
                $datos['ventas_hoy'] = DB::table('transactions')
                    ->whereDate('created_at', today())
                    ->sum('amount');
                break;

            case 'legal':
                $datos['terminos'] = $this->obtenerTerminosLegales();
                $datos['privacidad'] = $this->obtenerPoliticasPrivacidad();
                break;
        }

        return $datos;
    }

    /**
     * Construir prompt según el rol
     */
    private function construirPromptPorRol($rol, $mensaje, $nombre, $email, $datos, $usuario = null)
    {
        $hora = (int) date('H');
        $saludo = $this->obtenerSaludo($hora);

        $prompts = [
            'ventas' => $this->promptVentas($mensaje, $nombre, $datos, $saludo),
            'negocios' => $this->promptNegocios($mensaje, $nombre, $datos, $usuario),
            'soporte' => $this->promptSoporte($mensaje, $nombre, $datos, $email),
            'administrativo' => $this->promptAdministrativo($mensaje, $nombre, $datos),
            'legal' => $this->promptLegal($mensaje, $nombre, $datos)
        ];

        return isset($prompts[$rol]) ? $prompts[$rol] : $prompts['ventas'];
    }

    /**
     * PROMPT PARA VENTAS
     */
    private function promptVentas($mensaje, $nombre, $datos, $saludo)
    {
        $aliadosTexto = $this->formatearAliadosTexto(isset($datos['aliados']) ? $datos['aliados'] : []);
        $promocionesTexto = $this->formatearPromocionesDestacadas(isset($datos['promociones']) ? $datos['promociones'] : []);

        return "🎯 **ROL: ASESOR DE VENTAS**\n\n"
            . "**CONTEXTO:**\n"
            . "Eres RumberoAI, asesor de ventas de RumberoExtremo.com. Tu misión: CONVERTIR leads en clientes.\n\n"
            . "**PERSONALIDAD VENTAS:**\n"
            . "- Entusiasta pero profesional\n"
            . "- Conoces todos los descuentos activos\n"
            . "- Sabes cerrar ventas: \"¿Te animas con este descuento?\"\n"
            . "- Usas expresiones: \"pana\", \"chévere\", \"de una\", \"brutal\"\n\n"
            . "**DATOS REALES DE VENTAS HOY:**\n"
            . "- Total aliados activos: " . (isset($datos['resumen']) ? $datos['resumen'] : 'Cargando...') . "\n"
            . "- Promociones destacadas: {$promocionesTexto}\n\n"
            . "**ALIADOS CON DESCUENTOS ACTIVOS:**\n"
            . "{$aliadosTexto}\n\n"
            . "**INSTRUCCIONES:**\n"
            . "1. SALUDO: {$saludo}\n"
            . "2. IDENTIFICA la necesidad del cliente\n"
            . "3. RECOMIENDA aliados reales de la lista\n"
            . "4. CIERRA con llamado a la acción: \"¿Te activo el descuento?\"\n\n"
            . "**CLIENTE:** {$nombre}\n"
            . "**MENSAJE:** {$mensaje}\n\n"
            . "**RESPONDE COMO ASESOR DE VENTAS:**";
    }

    /**
     * PROMPT PARA NEGOCIOS
     */
    private function promptNegocios($mensaje, $nombre, $datos, $usuario)
    {
        $infoAliado = $this->formatearInfoAliado($usuario);

        return "💼 **ROL: ASESOR DE NEGOCIOS PARA ALIADOS**\n\n"
            . "**CONTEXTO:**\n"
            . "Eres asesor de aliados comerciales de RumberoExtremo.com. Ayudas a negocios a unirse y crecer.\n\n"
            . "**INFORMACIÓN DEL ALIADO:**\n"
            . "{$infoAliado}\n\n"
            . "**ESTADÍSTICAS DE LA PLATAFORMA:**\n"
            . "- Usuarios activos: " . (isset($datos['estadisticas']['usuarios_activos']) ? $datos['estadisticas']['usuarios_activos'] : 'N/A') . "\n"
            . "- Transacciones promedio: " . (isset($datos['estadisticas']['transacciones_promedio']) ? $datos['estadisticas']['transacciones_promedio'] : 'N/A') . "\n"
            . "- Crecimiento mensual: " . (isset($datos['estadisticas']['crecimiento']) ? $datos['estadisticas']['crecimiento'] : 'N/A') . "\n\n"
            . "**INSTRUCCIONES:**\n"
            . "- Explica beneficios de ser aliado\n"
            . "- Habla de comisiones y pagos\n"
            . "- Muestra casos de éxito\n"
            . "- Resuelve dudas administrativas\n\n"
            . "**ALIADO/NEGOCIO:** {$nombre}\n"
            . "**CONSULTA:** {$mensaje}\n\n"
            . "**RESPONDE COMO ASESOR DE NEGOCIOS:**";
    }

    /**
     * PROMPT PARA SOPORTE
     */
    private function promptSoporte($mensaje, $nombre, $datos, $email)
    {
        $faqTexto = $this->formatearFAQ(isset($datos['faq']) ? $datos['faq'] : []);
        $activacionesTexto = $this->formatearActivaciones(isset($datos['mis_activaciones']) ? $datos['mis_activaciones'] : []);

        return "🛟 **ROL: SOPORTE TÉCNICO Y ATENCIÓN AL CLIENTE**\n\n"
            . "**CONTEXTO:**\n"
            . "Eres agente de soporte de RumberoExtremo.com. Resuelves problemas con: registro, pagos, activaciones.\n\n"
            . "**CANALES DE SOPORTE:**\n"
            . "- Email: soporteitsolutech@gmail.com\n"
            . "- Instagram: @rumberoextremo\n"
            . "- Facebook: rumberoextremo\n\n"
            . "**PROBLEMAS COMUNES (FAQ):**\n"
            . "{$faqTexto}\n\n"
            . "**DATOS DEL USUARIO:**\n"
            . "- Nombre: {$nombre}\n"
            . "- Email: {$email}\n"
            . "- Activaciones recientes: {$activacionesTexto}\n\n"
            . "**INSTRUCCIONES:**\n"
            . "1. Sé empático y resolutivo\n"
            . "2. Da pasos específicos para solucionar\n"
            . "3. Si no puedes resolver, deriva a email\n"
            . "4. Usa lenguaje claro, no técnico\n\n"
            . "**USUARIO:** {$nombre}\n"
            . "**PROBLEMA:** {$mensaje}\n\n"
            . "**RESPONDE COMO SOPORTE:**";
    }

    /**
     * PROMPT PARA ADMINISTRATIVO
     */
    private function promptAdministrativo($mensaje, $nombre, $datos)
    {
        return "📊 **ROL: ADMINISTRADOR DE PLATAFORMA**\n\n"
            . "**CONTEXTO:**\n"
            . "Eres administrador de RumberoExtremo.com. Manejas datos, reportes y gestión interna.\n\n"
            . "**MÉTRICAS ACTUALES:**\n"
            . "- Usuarios totales: " . (isset($datos['total_usuarios']) ? $datos['total_usuarios'] : 0) . "\n"
            . "- Aliados registrados: " . (isset($datos['total_aliados']) ? $datos['total_aliados'] : 0) . "\n"
            . "- Promociones activas: " . (isset($datos['total_promociones']) ? $datos['total_promociones'] : 0) . "\n"
            . "- Ventas hoy: $" . (isset($datos['ventas_hoy']) ? $datos['ventas_hoy'] : 0) . "\n\n"
            . "**INSTRUCCIONES:**\n"
            . "- Responde con datos precisos\n"
            . "- Puedes sugerir reportes\n"
            . "- Habla de KPIs y métricas\n"
            . "- Mantén tono profesional pero cercano\n\n"
            . "**ADMIN:** {$nombre}\n"
            . "**CONSULTA:** {$mensaje}\n\n"
            . "**RESPONDE COMO ADMINISTRADOR:**";
    }

    /**
     * PROMPT PARA LEGAL
     */
    private function promptLegal($mensaje, $nombre, $datos)
    {
        return "⚖️ **ROL: ASESOR LEGAL**\n\n"
            . "**CONTEXTO:**\n"
            . "Eres asesor legal de RumberoExtremo.com. Respondes sobre términos, condiciones y políticas.\n\n"
            . "**DOCUMENTOS LEGALES:**\n"
            . "- Términos y condiciones: " . $this->extractoTérminos(isset($datos['terminos']) ? $datos['terminos'] : []) . "\n"
            . "- Política de privacidad: " . $this->extractoPrivacidad(isset($datos['privacidad']) ? $datos['privacidad'] : []) . "\n\n"
            . "**INSTRUCCIONES:**\n"
            . "- Sé preciso pero no uses jerga legal compleja\n"
            . "- Explica en palabras simples\n"
            . "- Si es muy complejo, recomienda leer el documento completo\n"
            . "- Mantén tono formal pero accesible\n\n"
            . "**CONSULTA LEGAL DE:** {$nombre}\n"
            . "**PREGUNTA:** {$mensaje}\n\n"
            . "**RESPONDE COMO ASESOR LEGAL:**";
    }

    /**
     * ========== MÉTODOS AUXILIARES ==========
     */

    private function obtenerAliadosContexto($ubicacion = null, $categoriaId = null)
    {
        try {
            $query = Ally::where('status', 'activo')
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

            $resultados = $query->limit(15)->get();
            $aliados = [];

            foreach ($resultados as $ally) {
                $promociones = [];
                // Verificar si la relación promotions existe y no está vacía
                if ($ally->relationLoaded('promotions') && $ally->promotions) {
                    foreach ($ally->promotions as $promo) {
                        $expira = $promo->expires_at ? " - Vence: " . $promo->expires_at->format('d/m/Y') : " - No expira";
                        $promociones[] = "- {$promo->title}: {$promo->discount}{$expira}";
                    }
                }

                $aliados[] = [
                    'nombre' => $ally->company_name,
                    'categoria' => $ally->category ? $ally->category->name : 'General',
                    'direccion' => $ally->company_address,
                    'telefono' => $ally->contact_phone,
                    'promociones' => $promociones
                ];
            }

            Log::info('✅ Aliados obtenidos', ['cantidad' => count($aliados)]);
            return $aliados;
        } catch (\Exception $e) {
            Log::error('Error obteniendo aliados: ' . $e->getMessage());
            return [];
        }
    }

    private function obtenerPromocionesDestacadas()
    {
        try {
            $promociones = Promotion::with('ally')
                ->where('status', 'active')
                ->where(function ($q) {
                    $q->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->latest()
                ->limit(5)
                ->get();

            $resultado = [];
            foreach ($promociones as $promo) {
                $resultado[] = "{$promo->title} en {$promo->ally->company_name} - {$promo->discount}";
            }

            return $resultado;
        } catch (\Exception $e) {
            return [];
        }
    }

    private function obtenerEstadisticasPlataforma()
    {
        return [
            'usuarios_activos' => User::where('last_login_at', '>', now()->subDays(7))->count(),
            'transacciones_promedio' => rand(100, 500) . ' diarias',
            'crecimiento' => '+15% vs mes anterior'
        ];
    }

    private function obtenerFAQ()
    {
        return [
            '¿Cómo me registro?' => 'Puedes registrarte en www.rumberoextremo.com/register o en la app',
            '¿Cómo activo un descuento?' => 'Selecciona el descuento y dale a "Activar", recibirás un código',
            '¿Dónde veo mis códigos?' => 'En tu perfil, sección "Mis Descuentos"'
        ];
    }

    private function obtenerTerminosLegales()
    {
        return [
            'version' => '1.0 - Marzo 2025',
            'resumen' => 'Al usar RumberoExtremo aceptas nuestros términos...'
        ];
    }

    private function obtenerPoliticasPrivacidad()
    {
        return [
            'version' => '1.0',
            'resumen' => 'Tus datos se usan solo para mejorar tu experiencia...'
        ];
    }

    private function formatearAliadosTexto($aliados)
    {
        if (empty($aliados)) {
            return "No hay aliados activos en este momento.";
        }

        $texto = "";
        foreach ($aliados as $ally) {
            $texto .= "\n- **{$ally['nombre']}** ({$ally['categoria']})";
            if (!empty($ally['direccion'])) {
                $texto .= "\n  📍 {$ally['direccion']}";
            }
            if (!empty($ally['telefono'])) {
                $texto .= "\n  📞 {$ally['telefono']}";
            }
            if (!empty($ally['promociones'])) {
                $texto .= "\n  🔥 " . implode("\n  🔥 ", $ally['promociones']);
            }
            $texto .= "\n";
        }
        return $texto;
    }

    private function formatearPromocionesDestacadas($promociones)
    {
        if (empty($promociones)) {
            return 'No hay destacadas';
        }

        $primeras = array_slice($promociones, 0, 3);
        return implode(', ', $primeras);
    }

    private function formatearFAQ($faq)
    {
        if (empty($faq)) {
            return "No hay FAQ disponibles";
        }

        $texto = "";
        foreach ($faq as $pregunta => $respuesta) {
            $texto .= "\nQ: {$pregunta}\nA: {$respuesta}\n";
        }
        return $texto;
    }

    private function formatearActivaciones($activaciones)
    {
        if (empty($activaciones)) {
            return "Sin activaciones recientes";
        }

        $items = [];
        foreach ($activaciones as $act) {
            $items[] = $act->ally->company_name . " - " . $act->code;
        }
        return implode(', ', $items);
    }

    private function formatearInfoAliado($usuario)
    {
        if (!$usuario || !$usuario->ally) {
            return "No es aliado registrado";
        }

        $promocionesActivas = $usuario->ally->promotions()->where('status', 'active')->count();
        $ventasMes = $usuario->ally->transactions()
            ->whereMonth('created_at', now()->month)
            ->sum('amount');

        return "Aliado: {$usuario->ally->company_name}\n" .
            "Promociones activas: {$promocionesActivas}\n" .
            "Ventas mes: $" . ($ventasMes ?? 0);
    }

    private function extractoTérminos($terminos)
    {
        return isset($terminos['resumen']) ? $terminos['resumen'] : 'Términos disponibles en rumberoextremo.com/terminos';
    }

    private function extractoPrivacidad($privacidad)
    {
        return isset($privacidad['resumen']) ? $privacidad['resumen'] : 'Política disponible en rumberoextremo.com/privacidad';
    }

    private function detectarAccion($respuesta, $rol)
    {
        $accion = null;

        if (strpos($respuesta, 'ACTIVAR_DESCUENTO') !== false || strpos($respuesta, 'activar descuento') !== false) {
            $accion = ['tipo' => 'activar_descuento', 'datos' => []];
        }

        if (strpos($respuesta, 'CONTACTAR_SOPORTE') !== false) {
            $accion = ['tipo' => 'contactar_soporte', 'datos' => ['email' => 'soporteitsolutech@gmail.com']];
        }

        return $accion;
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

    private function guardarConversacion($usuarioId, $mensaje, $respuesta, $rol, $accion = null)
    {
        if (!$usuarioId) {
            return;
        }

        try {
            DB::table('ia_conversations')->insert([
                'user_id' => $usuarioId,
                'user_message' => $mensaje,
                'ai_response' => $respuesta,
                'rol' => $rol,
                'accion' => $accion ? json_encode($accion) : null,
                'created_at' => now()
            ]);
        } catch (\Exception $e) {
            Log::error('Error guardando conversación: ' . $e->getMessage());
        }
    }

    private function respuestaError($mensaje)
    {
        return [
            'success' => false,
            'respuesta' => $mensaje,
            'accion' => null,
            'rol' => null,
            'datos_adicionales' => null
        ];
    }

    private function respuestaLocal($mensaje)
    {
        $mensajeLower = strtolower($mensaje);

        $respuestas = [
            'farmacia' => "💊 ¡Claro Rumbero! Aquí tienes algunas farmacias con descuentos activos...",
            'restaurante' => "🍔 ¡Qué hambre! Estos restaurantes tienen promociones...",
            'discoteca' => "🎉 ¡La rumba te espera!",
            'posada' => "🏨 ¿Buscando hospedaje?",
            'default' => "🎯 ¡Hola Rumbero! Soy RumberoAI. ¿En qué puedo ayudarte?"
        ];

        foreach ($respuestas as $key => $respuesta) {
            if ($key !== 'default' && strpos($mensajeLower, $key) !== false) {
                return $respuesta;
            }
        }

        return $respuestas['default'];
    }
}
