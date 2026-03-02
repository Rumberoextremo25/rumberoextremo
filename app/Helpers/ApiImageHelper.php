<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ApiImageHelper
{
    private static $imageCache = null;
    
    // Definir las carpetas donde buscar imágenes
    private static $folders = ['allies', 'logos', 'banners', 'promotions', 'products'];
    
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
            // Buscar en todas las carpetas definidas
            foreach (self::$folders as $folder) {
                $files = Storage::disk('public')->files($folder);
                foreach ($files as $file) {
                    $filename = basename($file);
                    self::$imageCache[$filename] = $file;
                    
                    // También cachear sin extensión para búsqueda flexible
                    $nameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);
                    self::$imageCache[$nameWithoutExt] = $file;
                }
            }
            
            Log::info('ApiImageHelper: Imágenes disponibles', [
                'count' => count(self::$imageCache),
                'folders' => self::$folders
            ]);
            
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
        
        // Log para depuración
        Log::info('ApiImageHelper: Buscando imagen', [
            'requested' => $requestedName,
            'without_ext' => $requestedWithoutExt,
            'available_count' => count($available)
        ]);
        
        // Búsqueda exacta
        if (isset($available[$requestedName])) {
            Log::info('ApiImageHelper: Encontrada por nombre exacto', ['file' => $available[$requestedName]]);
            return $available[$requestedName];
        }
        
        // Búsqueda por nombre sin extensión
        if (isset($available[$requestedWithoutExt])) {
            Log::info('ApiImageHelper: Encontrada por nombre sin extensión', ['file' => $available[$requestedWithoutExt]]);
            return $available[$requestedWithoutExt];
        }
        
        // Búsqueda por coincidencia parcial
        foreach (array_keys($available) as $availableName) {
            if (strpos($availableName, $requestedWithoutExt) !== false) {
                Log::info('ApiImageHelper: Encontrada por coincidencia parcial', [
                    'available' => $availableName,
                    'file' => $available[$availableName]
                ]);
                return $available[$availableName];
            }
            if (strpos($requestedWithoutExt, $availableName) !== false) {
                Log::info('ApiImageHelper: Encontrada por coincidencia parcial inversa', [
                    'available' => $availableName,
                    'file' => $available[$availableName]
                ]);
                return $available[$availableName];
            }
        }
        
        Log::warning('ApiImageHelper: Imagen no encontrada', ['requested' => $requestedName]);
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
                $url = url('storage/' . $foundPath);
                Log::info('ApiImageHelper: URL generada', ['url' => $url]);
                return $url;
            }
            
            // Si no encuentra, intentar con asset directo (como fallback)
            if (strpos($path, '/') !== false) {
                $directUrl = asset('storage/' . $path);
                Log::info('ApiImageHelper: Usando URL directa como fallback', ['url' => $directUrl]);
                return $directUrl;
            }
            
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
                    // Fallback a URL directa
                    $images[] = asset('storage/' . $path);
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Error decodificando imágenes: ' . $e->getMessage());
        }
        
        return $images;
    }
}