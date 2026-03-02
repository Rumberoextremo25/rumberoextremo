<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ally;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class MapImagesToDatabase extends Command
{
    protected $signature = 'images:map';
    protected $description = 'Mapear imágenes de la BD con archivos reales';

    public function handle()
    {
        $this->info('🔍 Mapeando imágenes...');
        
        // Obtener archivos reales
        $files = Storage::disk('public')->files('allies');
        $fileMap = [];
        
        foreach ($files as $file) {
            $fileMap[basename($file)] = $file;
        }
        
        $this->info('📸 Archivos disponibles:');
        foreach ($fileMap as $name => $path) {
            $this->line("   - {$name}");
        }
        
        // Mapeo de aliados
        $mappings = [
            1 => [ // Restaurante La Esquina
                'image' => '3BqyWYPgnTB3YKHbE5M51J6YFoIfq4D22QpzCkLp.jpg',
                'gallery' => [
                    'pabellon' => '3BqyWYPgnTB3YKHbE5M51J6YFoIfq4D22QpzCkLp.jpg', // Mismo archivo
                    'arepas' => 'qGxC96DOhD9gXg5lLJuMqRuKqcsRDW030jwwRI1H.jpg',
                    'cachapa' => '88MCeDkrUSpJzuBIL2msW04nA1Did3zztulfnQsL.png',
                ]
            ],
            2 => [ // El Gol Sports
                'image' => 'qGxC96DOhD9gXg5lLJuMqRuKqcsRDW030jwwRI1H.jpg',
                'gallery' => [
                    'balon' => 'qGxC96DOhD9gXg5lLJuMqRuKqcsRDW030jwwRI1H.jpg',
                    'camiseta' => '3BqyWYPgnTB3YKHbE5M51J6YFoIfq4D22QpzCkLp.jpg',
                    'zapatos' => '88MCeDkrUSpJzuBIL2msW04nA1Did3zztulfnQsL.png',
                ]
            ],
        ];
        
        foreach ($mappings as $id => $map) {
            $ally = Ally::find($id);
            if (!$ally) {
                $this->warn("Aliado ID {$id} no encontrado");
                continue;
            }
            
            // Actualizar imagen principal
            $oldImage = $ally->image_url;
            $ally->image_url = 'allies/' . $map['image'];
            $this->info("✅ {$ally->company_name}: {$oldImage} → {$ally->image_url}");
            
            // Actualizar galería
            $gallery = [];
            foreach ($map['gallery'] as $desc => $filename) {
                $gallery[] = 'allies/' . $filename;
            }
            $ally->product_images = json_encode($gallery);
            
            $ally->save();
        }
        
        $this->newLine();
        $this->info('✅ Base de datos actualizada');
        
        return 0;
    }
}
