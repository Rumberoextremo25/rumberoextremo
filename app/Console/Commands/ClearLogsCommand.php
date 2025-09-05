<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ClearLogsCommand extends Command
{
    protected $signature = 'logs:clean {--keep-latest : Mantener el archivo más reciente}';
    protected $description = 'Limpiar todos los archivos de log';

    public function handle()
    {
        $logPath = storage_path('logs');
        
        if ($this->option('keep-latest')) {
            // Mantener solo el archivo más reciente
            $files = glob($logPath . '/*.log');
            $latestFile = null;
            $latestTime = 0;
            
            foreach ($files as $file) {
                $fileTime = filemtime($file);
                if ($fileTime > $latestTime) {
                    $latestTime = $fileTime;
                    $latestFile = $file;
                }
            }
            
            foreach ($files as $file) {
                if ($file !== $latestFile) {
                    unlink($file);
                    $this->info("Eliminado: " . basename($file));
                }
            }
            
            // Vaciar el archivo más reciente
            file_put_contents($latestFile, '');
            $this->info("Vaciar: " . basename($latestFile));
            
        } else {
            // Eliminar todos los archivos de log
            $files = glob($logPath . '/*.log');
            foreach ($files as $file) {
                unlink($file);
                $this->info("Eliminado: " . basename($file));
            }
            
            // Crear un nuevo archivo vacío
            touch($logPath . '/laravel.log');
            $this->info("Nuevo archivo de log creado");
        }
        
        $this->info('¡Logs limpiados exitosamente!');
    }
}