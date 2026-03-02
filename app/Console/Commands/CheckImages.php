<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ally;
use Illuminate\Support\Facades\Storage;

class CheckImages extends Command
{
    protected $signature = 'images:check';
    protected $description = 'Verificar imágenes de aliados';

    public function handle()
    {
        $this->info('Verificando imágenes de aliados...');
        
        $allies = Ally::all();
        $publicPath = public_path('storage');
        
        $this->info('Ruta public/storage: ' . $publicPath);
        $this->info('Existe: ' . (file_exists($publicPath) ? 'SÍ' : 'NO'));
        
        if (file_exists($publicPath)) {
            $this->info('Es enlace: ' . (is_link($publicPath) ? 'SÍ' : 'NO'));
        }
        
        $this->newLine();
        $this->table(['ID', 'Empresa', 'Imagen Principal', 'Existe?', 'Galería'], 
            $allies->map(function($ally) {
                $mainExists = $ally->image_url 
                    ? (Storage::disk('public')->exists($ally->image_url) ? '✅' : '❌')
                    : 'N/A';
                    
                $gallery = $ally->product_images 
                    ? count(json_decode($ally->product_images, true) ?? [])
                    : 0;
                    
                return [
                    $ally->id,
                    substr($ally->company_name, 0, 20),
                    $ally->image_url ?? 'No tiene',
                    $mainExists,
                    $gallery . ' imágenes',
                ];
            })->toArray()
        );
        
        return 0;
    }
}
