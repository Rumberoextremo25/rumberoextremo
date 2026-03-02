<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ally;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class FixImagePaths extends Command
{
    protected $signature = 'images:fix';
    protected $description = 'Corregir rutas de imágenes de aliados';

    public function handle()
    {
        $this->info('🔍 Buscando imágenes en el sistema...');
        
        // Buscar archivos reales
        $files = [];
        $searchPaths = [
            'public/allies',
            'allies',
            'storage/app/public/allies',
        ];
        
        foreach ($searchPaths as $path) {
            $fullPath = storage_path('app/' . str_replace('storage/', '', $path));
            if (is_dir($fullPath)) {
                $found = glob($fullPath . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
                foreach ($found as $file) {
                    $filename = basename($file);
                    $files[$filename] = $path . '/' . $filename;
                }
            }
        }
        
        $this->info('📸 Archivos encontrados: ' . count($files));
        foreach ($files as $filename => $path) {
            $this->line("   - {$filename} → {$path}");
        }
        
        // Actualizar aliados
        $allies = Ally::all();
        $this->newLine();
        $this->info('📝 Actualizando rutas en base de datos...');
        
        foreach ($allies as $ally) {
            $updated = false;
            
            // Corregir imagen principal
            if ($ally->image_url) {
                $oldPath = $ally->image_url;
                $filename = basename($oldPath);
                
                if (isset($files[$filename])) {
                    $newPath = str_replace('public/', '', $files[$filename]);
                    $ally->image_url = $newPath;
                    $updated = true;
                    $this->line("✅ {$ally->company_name}: {$oldPath} → {$newPath}");
                } else {
                    $this->warn("⚠️ {$ally->company_name}: No se encontró {$filename}");
                }
            }
            
            // Corregir galería
            if ($ally->product_images) {
                $images = json_decode($ally->product_images, true);
                $newImages = [];
                
                foreach ($images as $imagePath) {
                    $filename = basename($imagePath);
                    if (isset($files[$filename])) {
                        $newPath = str_replace('public/', '', $files[$filename]);
                        $newImages[] = $newPath;
                        $this->line("   📸 Galería: {$imagePath} → {$newPath}");
                    } else {
                        $this->warn("   ⚠️ Galería: No se encontró {$filename}");
                    }
                }
                
                if (!empty($newImages)) {
                    $ally->product_images = json_encode($newImages);
                    $updated = true;
                }
            }
            
            if ($updated) {
                $ally->save();
            }
        }
        
        $this->newLine();
        $this->info('✅ Proceso completado');
        
        return 0;
    }
}
