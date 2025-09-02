<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BncApiService;

//php artisan bnc:test

class TestBnCConnection extends Command
{
    protected $signature = 'bnc:test';
    protected $description = 'Test BNC API connection';

    public function handle()
    {
        $service = new BncApiService();
        
        $this->info('Testing BNC encryption...');
        
        // Test 1: Encryption básica
        $this->info('1. Testing encryption...');
        $testResult = $service->testEncryptionManual();
        print_r($testResult);
        
        // Test 2: Intentar obtener token
        $this->info('2. Attempting to get session token...');
        try {
            $token = $service->getSessionToken();
            if ($token) {
                $this->info('SUCCESS: Token obtained: ' . substr($token, 0, 50) . '...');
            } else {
                $this->error('FAILED: No token received');
            }
        } catch (\Exception $e) {
            $this->error('ERROR: ' . $e->getMessage());
        }
        
        return 0;
    }
}
