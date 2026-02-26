<?php
// app/Services/AIService.php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class AIService
{
    protected $deepSeekService;
    protected $geminiService;
    
    public function __construct()
    {
        $this->deepSeekService = new DeepSeekService();
        $this->geminiService = new GeminiService();
    }
    
    /**
     * Chat con selector de IA
     */
    public function chat($mensajeUsuario, $usuarioId = null, $ubicacion = null, $categoriaFiltro = null, $iaPreferida = 'gemini')
    {
        Log::info('AIService iniciado', [
            'ia_preferida' => $iaPreferida,
            'mensaje' => substr($mensajeUsuario, 0, 50)
        ]);
        
        // SIEMPRE USAR GEMINI (DeepSeek sin saldo)
        // Pero respetamos la preferencia del usuario para el selector
        try {
            Log::info('Intentando con Gemini');
            
            $respuesta = $this->geminiService->chat(
                $mensajeUsuario, 
                $usuarioId, 
                $ubicacion, 
                $categoriaFiltro
            );
            
            return [
                'success' => true,
                'respuesta' => $respuesta['respuesta'] ?? 'Respuesta generada',
                'accion' => null,
                'aliados_relevantes' => $respuesta['aliados_relevantes'] ?? [],
                'ia_utilizada' => 'gemini' // 👈 Importante para el frontend
            ];
            
        } catch (\Exception $e) {
            Log::error('❌ Error en AIService: ' . $e->getMessage());
            
            return [
                'success' => true,
                'respuesta' => $this->respuestaLocal($mensajeUsuario),
                'accion' => null,
                'aliados_relevantes' => [],
                'ia_utilizada' => 'local'
            ];
        }
    }
    
    /**
     * Respuesta local de emergencia
     */
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