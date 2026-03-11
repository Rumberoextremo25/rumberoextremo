<?php

namespace App\Utils;

use Illuminate\Support\Facades\Log;

class JsonHelper
{
    /**
     * Codifica un array a JSON de forma segura
     */
    public static function encode($data, $options = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES): ?string
    {
        try {
            if (is_null($data)) {
                return null;
            }
            
            $json = json_encode($data, $options);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Error codificando JSON: ' . json_last_error_msg());
                return null;
            }
            
            return $json;
        } catch (\Exception $e) {
            Log::error('Excepción codificando JSON: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Decodifica un JSON a array de forma segura
     */
    public static function decode($json, $associative = true)
    {
        try {
            if (is_null($json) || empty($json)) {
                return [];
            }
            
            $data = json_decode($json, $associative);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Error decodificando JSON: ' . json_last_error_msg());
                return [];
            }
            
            return $data;
        } catch (\Exception $e) {
            Log::error('Excepción decodificando JSON: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Valida si un string es JSON válido
     */
    public static function isValidJson($string): bool
    {
        if (!is_string($string)) {
            return false;
        }
        
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Convierte un modelo o colección a JSON de forma segura
     */
    public static function fromModel($model, $options = JSON_UNESCAPED_UNICODE): ?string
    {
        try {
            if (is_null($model)) {
                return null;
            }
            
            if (method_exists($model, 'toArray')) {
                return self::encode($model->toArray(), $options);
            }
            
            if (is_array($model)) {
                return self::encode($model, $options);
            }
            
            return self::encode((array)$model, $options);
        } catch (\Exception $e) {
            Log::error('Error convirtiendo modelo a JSON: ' . $e->getMessage());
            return null;
        }
    }
}