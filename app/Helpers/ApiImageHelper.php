<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ApiImageHelper
{
    private static $imageCache = null;
    
    /**
     * Obtener lista de imágenes disponibles
     */
    private static function getAvailableImages()
    {
        if (self::$imageCache !== null) {
            return self::$imageCache;
        }
        
        self::$imageCache = [];
        
        try {
            $files = Storage::disk('public')->files('allies');
            foreach ($files as $file) {
                $filename = basename($file);
                self::$imageCache[$filename] = $file;
                
                // También cachear sin extensión para búsqueda flexible
                $nameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);
                self::$imageCache[$nameWithoutExt] = $file;
            }
        } catch (\Exception $e) {
            Log::error('Error obteniendo imágenes: ' . $e->getMessage());
        }
        
        return self::$imageCache;
    }
    
    /**
     * Buscar una imagen por nombre aproximado
     */
    private static function findImage($requestedName)
    {
        $available = self::getAvailableImages();
        $requestedName = basename($requestedName);
        $requestedWithoutExt = pathinfo($requestedName, PATHINFO_FILENAME);
        
        // Búsqueda exacta
        if (isset($available[$requestedName])) {
            return $available[$requestedName];
        }
        
        // Búsqueda por nombre sin extensión
        if (isset($available[$requestedWithoutExt])) {
            return $available[$requestedWithoutExt];
        }
        
        // Búsqueda por coincidencia parcial
        foreach (array_keys($available) as $availableName) {
            if (strpos($availableName, $requestedWithoutExt) !== false) {
                return $available[$availableName];
            }
            if (strpos($requestedWithoutExt, $availableName) !== false) {
                return $available[$availableName];
            }
        }
        
        return null;
    }
    
    /**
     * Obtener URL de imagen principal
     */
    public static function getImageUrl($path)
    {
        if (empty($path)) {
            return null;
        }

        try {
            // Intentar encontrar la imagen
            $foundPath = self::findImage($path);
            
            if ($foundPath) {
                return url('storage/' . $foundPath);
            }
            
            Log::warning('Imagen no encontrada', ['path' => $path]);
            return null;
            
        } catch (\Exception $e) {
            Log::error('Error al obtener URL de imagen: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener URLs de galería de productos
     */
    public static function getProductImages($productImagesJson)
    {
        $images = [];
        
        if (empty($productImagesJson)) {
            return $images;
        }

        try {
            $paths = json_decode($productImagesJson, true);
            
            if (!is_array($paths)) {
                return $images;
            }

            foreach ($paths as $path) {
                $foundPath = self::findImage($path);
                if ($foundPath) {
                    $images[] = url('storage/' . $foundPath);
                } else {
                    Log::warning('Imagen de producto no encontrada', ['path' => $path]);
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Error decodificando imágenes: ' . $e->getMessage());
        }
        
        return $images;
    }
}