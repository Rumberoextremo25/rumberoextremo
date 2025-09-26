<?php

namespace App\Services;

use Exception;

class DataCypher
{
    private $key;
    private $iv;
    private $encryptionKey;

    public function __construct(string $encryptionKey)
    {
        $this->encryptionKey = $encryptionKey;
        $this->deriveKeyAndIV();
    }

    private function deriveKeyAndIV(): void
    {
        // ✅ EXACTAMENTE IGUAL que la función legacy
        $sSalt = chr(0x49) . chr(0x76) . chr(0x61) . chr(0x6e) . chr(0x20) . 
                 chr(0x4d) . chr(0x65) . chr(0x64) . chr(0x76) . chr(0x65) . 
                 chr(0x64) . chr(0x65) . chr(0x76);

        $keyAndIv = hash_pbkdf2(
            'sha1', // ✅ SHA1 como el legacy
            $this->encryptionKey,
            $sSalt,
            1000,
            48,
            true
        );

        // ✅ CORREGIDO: Ahora igual al legacy (IV de 16 bytes, no más)
        $this->key = substr($keyAndIv, 0, 32);
        $this->iv = substr($keyAndIv, 32, 16); // ✅ 16 bytes exactos para AES
    }

    /**
     * ✅ ENCRYPTACIÓN 100% COMPATIBLE con función legacy
     */
    public function encryptAES(string $text): string
    {
        try {
            $utf16le = mb_convert_encoding($text, 'UTF-16LE', 'UTF-8');

            $encrypted = openssl_encrypt(
                $utf16le,
                'aes-256-cbc',
                $this->key,
                OPENSSL_RAW_DATA,
                $this->iv
            );

            return base64_encode($encrypted);
        } catch (Exception $e) {
            throw new Exception("Error en encryptAES: " . $e->getMessage());
        }
    }

    /**
     * ✅ DECRYPTACIÓN 100% COMPATIBLE con función legacy
     */
    public function decryptAES(string $encryptedText): string
    {
        try {
            $decrypted = openssl_decrypt(
                base64_decode($encryptedText),
                'aes-256-cbc',
                $this->key,
                OPENSSL_RAW_DATA,
                $this->iv
            );

            return mb_convert_encoding($decrypted, 'UTF-8', 'UTF-16LE');
        } catch (Exception $e) {
            throw new Exception("Error en decryptAES: " . $e->getMessage());
        }
    }

    // === NUEVOS MÉTODOS AGREGADOS ===

    /**
     * ✅ ENCRYPTACIÓN CON CLAVE ESPECÍFICA (para WorkingKey)
     * Réplica exacta de encrypt($data, $Masterkey) pero con clave específica
     */
    public function encryptWithKey(string $data, string $specificKey): string
    {
        try {
            // ✅ RÉPLICA EXACTA de la función encrypt() legacy
            $method = 'aes-256-cbc';
            $sSalt = chr(0x49) . chr(0x76) . chr(0x61) . chr(0x6e) . chr(0x20) . 
                     chr(0x4d) . chr(0x65) . chr(0x64) . chr(0x76) . chr(0x65) . 
                     chr(0x64) . chr(0x65) . chr(0x76);

            $pbkdf2 = hash_pbkdf2('SHA1', $specificKey, $sSalt, 1000, 48, true);
            $key = substr($pbkdf2, 0, 32);
            $iv = substr($pbkdf2, 32, 16); // ✅ 16 bytes exactos

            $string = mb_convert_encoding($data, 'UTF-16LE', 'UTF-8');
            $encrypted = base64_encode(openssl_encrypt($string, $method, $key, OPENSSL_RAW_DATA, $iv));
            
            return $encrypted;
        } catch (Exception $e) {
            throw new Exception("Error en encryptWithKey: " . $e->getMessage());
        }
    }

    /**
     * ✅ DECRYPTACIÓN CON CLAVE ESPECÍFICA (para WorkingKey)
     * Réplica exacta de decrypt($data, $Masterkey) pero con clave específica
     */
    public function decryptWithKey(string $encryptedData, string $specificKey): string
    {
        try {
            $method = 'aes-256-cbc';
            $sSalt = chr(0x49) . chr(0x76) . chr(0x61) . chr(0x6e) . chr(0x20) . 
                     chr(0x4d) . chr(0x65) . chr(0x64) . chr(0x76) . chr(0x65) . 
                     chr(0x64) . chr(0x65) . chr(0x76);

            $pbkdf2 = hash_pbkdf2('SHA1', $specificKey, $sSalt, 1000, 48, true);
            $key = substr($pbkdf2, 0, 32);
            $iv = substr($pbkdf2, 32, 16); // ✅ 16 bytes exactos

            $string = openssl_decrypt(base64_decode($encryptedData), $method, $key, OPENSSL_RAW_DATA, $iv);
            $decrypted = mb_convert_encoding($string, 'UTF-8', 'UTF-16LE');

            return $decrypted;
        } catch (Exception $e) {
            throw new Exception("Error en decryptWithKey: " . $e->getMessage());
        }
    }

    // === MÉTODOS EXISTENTES (SE MANTIENEN) ===

    /**
     * ✅ MÉTODO COMPATIBLE EXACTO con función legacy encrypt()
     */
    public function encryptLegacy(string $data): string
    {
        return $this->encryptAES($data);
    }

    /**
     * ✅ MÉTODO COMPATIBLE EXACTO con función legacy decrypt()
     */
    public function decryptLegacy(string $data): string
    {
        return $this->decryptAES($data);
    }

    /**
     * ✅ MÉTODO PARA VALIDACIÓN (SHA256) - Separado de la encriptación
     */
    public function encryptSHA256(string $data): string
    {
        return hash('sha256', $data);
    }

    /**
     * ✅ VERIFICACIÓN DE COMPATIBILIDAD CON LEGACY
     */
    public function verifyCompatibility($testData, $masterKey): bool
    {
        // Tu nueva implementación
        $yourEncrypted = $this->encryptAES($testData);
        
        // Función legacy (deberías tenerla disponible)
        $legacyEncrypted = $this->callLegacyEncrypt($testData, $masterKey);
        
        return $yourEncrypted === $legacyEncrypted;
    }

    /**
     * ✅ MÉTODO AUXILIAR para llamar a la función legacy si existe
     */
    private function callLegacyEncrypt($data, $masterKey): string
    {
        // Si tienes la función legacy disponible globalmente
        if (function_exists('encrypt')) {
            return encrypt($data, $masterKey);
        }
        
        // Si no, simular el comportamiento legacy
        return $this->simulateLegacyEncrypt($data, $masterKey);
    }

    /**
     * ✅ SIMULACIÓN de la función legacy (para testing)
     */
    private function simulateLegacyEncrypt($data, $masterKey): string
    {
        $method = 'aes-256-cbc';
        $sSalt = chr(0x49) . chr(0x76) . chr(0x61) . chr(0x6e) . chr(0x20) . 
                 chr(0x4d) . chr(0x65) . chr(0x64) . chr(0x76) . chr(0x65) . 
                 chr(0x64) . chr(0x65) . chr(0x76);

        $pbkdf2 = hash_pbkdf2('SHA1', $masterKey, $sSalt, 1000, 48, true);
        $key = substr($pbkdf2, 0, 32);
        $iv = substr($pbkdf2, 32, 16); // ✅ 16 bytes, no más!

        $string = mb_convert_encoding($data, 'UTF-16LE', 'UTF-8');
        $encrypted = base64_encode(openssl_encrypt($string, $method, $key, OPENSSL_RAW_DATA, $iv));
        
        return $encrypted;
    }

    /**
     * ✅ MÉTODO DE UTILIDAD para obtener resumen de configuración
     */
    public function getConfigSummary(): array
    {
        return [
            'key_length' => strlen($this->key),
            'iv_length' => strlen($this->iv),
            'encryption_key' => substr($this->encryptionKey, 0, 8) . '...',
            'algorithm' => 'AES-256-CBC',
            'has_encryptWithKey' => true,
            'has_decryptWithKey' => true
        ];
    }
}
