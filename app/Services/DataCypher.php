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
        $salt = "4976616e204d65647665646576"; // "Ivan Medvedev" en hex

        $keyAndIv = hash_pbkdf2(
            'sha1',
            $this->encryptionKey,
            hex2bin($salt),
            1000,
            48,
            true
        );

        $this->key = substr($keyAndIv, 0, 32);
        $this->iv = substr($keyAndIv, 32, 16);
    }

    /**
     * ✅ ENCRYPTACIÓN COMPATIBLE CON LEGACY
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
     * ✅ DECRYPTACIÓN COMPATIBLE (MÉTODO AGREGADO)
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

    /**
     * ✅ HASH SHA256
     */
    public function encryptSHA256(string $data): string
    {
        return hash('sha256', $data);
    }

    /**
     * ✅ MÉTODO PRINCIPAL para el formato legacy
     */
    public function encryptLegacyFormat(array $data): array
    {
        // ❌ Viejo (problema con caracteres especiales):
        // $jsonData = json_encode($data);

        // ✅ Nuevo (corrige caracteres especiales):
        $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return [
            'value' => $this->encryptAES($jsonData),
            'validation' => $this->encryptSHA256($jsonData)
        ];
    }

    /**
     * ✅ MÉTODO PARA DESENCRIPTAR RESPUESTAS DEL BNC
     */
    public function decryptResponse(string $encryptedResponse): ?array
    {
        try {
            $decryptedData = $this->decryptAES($encryptedResponse);
            $result = json_decode($decryptedData, true);

            return is_array($result) ? $result : null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * ✅ MÉTODO DE PRUEBA
     */
    public function testCompatibilityWithLegacy(): array
    {
        $testData = '{"ClientGUID":"test-guid"}';

        return [
            'test_data' => $testData,
            'encrypted_value' => $this->encryptAES($testData),
            'encrypted_length' => strlen($this->encryptAES($testData)),
            'validation_hash' => $this->encryptSHA256($testData),
            'key_hex' => bin2hex($this->key),
            'iv_hex' => bin2hex($this->iv)
        ];
    }
}
