<?php
// app/Helpers/JsonHelper.php

namespace App\Helpers;

class JsonHelper
{
    /**
     * Convierte cualquier valor a string JSON válido
     */
    public static function encode($data): string
    {
        if (is_string($data)) {
            // Si ya es string, verificar si es JSON válido
            if (self::isJson($data)) {
                return $data;
            }
            // Si es string pero no JSON, codificarlo
            return json_encode(['value' => $data]);
        }
        
        if ($data === null) {
            return json_encode([]);
        }
        
        $encoded = json_encode($data, JSON_UNESCAPED_UNICODE);
        
        if ($encoded === false) {
            // Si falla la codificación, devolver array vacío
            return json_encode(['error' => 'Error al codificar datos']);
        }
        
        return $encoded;
    }
    
    /**
     * Verifica si un string es JSON válido
     */
    public static function isJson($string): bool
    {
        if (!is_string($string)) {
            return false;
        }
        
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
    
    /**
     * Decodifica JSON de forma segura
     */
    public static function decode(string $json, bool $associative = true)
    {
        if (empty($json)) {
            return $associative ? [] : null;
        }
        
        $decoded = json_decode($json, $associative);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $associative ? [] : null;
        }
        
        return $decoded;
    }
}