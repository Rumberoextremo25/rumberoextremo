<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;

/**
 * Genera el par de claves VAPID y las guarda en el .env.
 *
 * En Windows, `openssl_pkey_new()` falla al crear claves EC si no se le pasa
 * explícitamente el archivo openssl.cnf, así que se intenta primero por esa vía
 * y, si no, se delega en la librería web-push (funciona en Linux/macOS).
 *
 * Uso: php artisan webpush:vapid [--force]
 */
class GenerateVapidKeys extends Command
{
    protected $signature = 'webpush:vapid {--force : Sobrescribe las claves existentes}';

    protected $description = 'Genera las claves VAPID para las notificaciones Web Push';

    public function handle(): int
    {
        $envPath = base_path('.env');

        if (! is_file($envPath)) {
            $this->error('No se encontró el archivo .env');

            return self::FAILURE;
        }

        $env = file_get_contents($envPath) ?: '';

        if (! $this->option('force') && preg_match('/^VAPID_PUBLIC_KEY=\S+/m', $env)) {
            $this->warn('Ya existen claves VAPID. Usa --force para regenerarlas.');

            return self::SUCCESS;
        }

        $keys = $this->generateKeys();

        if ($keys === null) {
            $this->error('No se pudieron generar las claves VAPID. Revisa la extensión openssl de PHP.');

            return self::FAILURE;
        }

        $env = preg_replace('/^VAPID_(PUBLIC|PRIVATE)_KEY=.*\R?/m', '', $env);

        $block = PHP_EOL . '# ============================================' . PHP_EOL
            . '# WEB PUSH (VAPID) — generado por webpush:vapid' . PHP_EOL
            . '# ============================================' . PHP_EOL
            . 'VAPID_SUBJECT=' . config('webpush.vapid.subject') . PHP_EOL
            . 'VAPID_PUBLIC_KEY=' . $keys['publicKey'] . PHP_EOL
            . 'VAPID_PRIVATE_KEY=' . $keys['privateKey'] . PHP_EOL;

        file_put_contents($envPath, rtrim($env) . PHP_EOL . $block);

        $this->info('Claves VAPID generadas y guardadas en .env');
        $this->line('Pública : ' . $keys['publicKey']);
        $this->line('Privada : (oculta, solo en .env)');
        $this->newLine();
        $this->comment('Ejecuta "php artisan config:clear" si tienes la configuración cacheada.');

        return self::SUCCESS;
    }

    /**
     * @return array{publicKey: string, privateKey: string}|null
     */
    private function generateKeys(): ?array
    {
        $config = $this->opensslConfig();

        if ($config !== null) {
            $resource = @openssl_pkey_new([
                'private_key_type' => OPENSSL_KEYTYPE_EC,
                'curve_name' => 'prime256v1',
                'config' => $config,
            ]);

            if ($resource !== false) {
                $details = openssl_pkey_get_details($resource);

                if (isset($details['ec']['x'], $details['ec']['y'], $details['ec']['d'])) {
                    // Clave pública: punto sin comprimir 0x04 || X || Y (65 bytes)
                    $public = "\x04" . str_pad($details['ec']['x'], 32, "\0", STR_PAD_LEFT)
                        . str_pad($details['ec']['y'], 32, "\0", STR_PAD_LEFT);

                    return [
                        'publicKey' => $this->base64Url($public),
                        'privateKey' => $this->base64Url(str_pad($details['ec']['d'], 32, "\0", STR_PAD_LEFT)),
                    ];
                }
            }
        }

        // Alternativa: la propia librería (Linux/macOS o Windows con cnf válido)
        try {
            return VAPID::createVapidKeys();
        } catch (\Throwable) {
            return null;
        }
    }

    private function opensslConfig(): ?string
    {
        $candidates = array_filter([
            getenv('OPENSSL_CONF') ?: null,
            dirname(PHP_BINARY) . DIRECTORY_SEPARATOR . 'extras' . DIRECTORY_SEPARATOR . 'ssl' . DIRECTORY_SEPARATOR . 'openssl.cnf',
            'C:' . DIRECTORY_SEPARATOR . 'laragon' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'apache'
                . DIRECTORY_SEPARATOR . 'httpd-2.4.68-260617-Win64-VS18' . DIRECTORY_SEPARATOR . 'conf' . DIRECTORY_SEPARATOR . 'openssl.cnf',
        ]);

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function base64Url(string $raw): string
    {
        return rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
    }
}
