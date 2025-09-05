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

        // Salt fijo (igual que en JavaScript)
        $saltBytes = [0x49, 0x76, 0x61, 0x6e, 0x20, 0x4d, 0x65, 0x64, 0x76, 0x65, 0x64, 0x65, 0x76];
        $salt = $this->bytesToHex($saltBytes);

        // Derivar clave e IV usando PBKDF2 con SHA-1 (48 bytes = 32 key + 16 IV)
        $keyAndIv = $this->pbkdf2($encryptionKey, $salt, 1000, 48, 'sha1');

        // Key: primeros 32 bytes
        $this->key = substr($keyAndIv, 0, 32);

        // IV: siguientes 16 bytes  
        $this->iv = substr($keyAndIv, 32, 16);
    }

    private function bytesToHex(array $bytes): string
    {
        return implode('', array_map(function ($byte) {
            return str_pad(dechex($byte), 2, '0', STR_PAD_LEFT);
        }, $bytes));
    }

    private function pbkdf2(string $password, string $salt, int $iterations, int $length, string $algorithm = 'sha1'): string
    {
        // ✅ ALTERNATIVA: Usar hash_pbkdf2 nativo de PHP
        return hash_pbkdf2(
            $algorithm,
            $password,
            hex2bin($salt),
            $iterations,
            $length,
            true
        );
    }

    /**
     * ✅ VERSIÓN SIMPLE - Sin formato OpenSSL complejo
     */
    public function encryptAES(string $text): string
    {
        // 1. Convertir a UTF-16LE de forma MANUAL (como CryptoJS)
        $utf16le = '';
        for ($i = 0; $i < strlen($text); $i++) {
            $utf16le .= $text[$i] . "\x00";
        }

        // 2. Encryptar
        $encrypted = openssl_encrypt(
            $utf16le,
            'aes-256-cbc',
            $this->key,
            OPENSSL_RAW_DATA,
            $this->iv
        );

        return base64_encode($encrypted);
    }

    public function decryptAES(string $encryptedText): string
    {
        $decrypted = openssl_decrypt(
            base64_decode($encryptedText),
            'aes-256-cbc',
            $this->key,
            OPENSSL_RAW_DATA,
            $this->iv
        );

        // Convertir de UTF-16LE a UTF-8
        $result = '';
        for ($i = 0; $i < strlen($decrypted); $i += 2) {
            $result .= $decrypted[$i];
        }
        return rtrim($result, "\x00");
    }

    public function encryptSHA256(string $data): string
    {
        return hash('sha256', $data);
    }

    public function testEncryption(): array
    {
        $testData = '{"ClientGUID":"test"}';

        $encrypted = $this->encryptAES($testData);
        $decrypted = $this->decryptAES($encrypted);

        return [
            'success' => $testData === $decrypted,
            'original' => $testData,
            'encrypted' => $encrypted,
            'decrypted' => $decrypted,
            'key_hex' => bin2hex($this->key),
            'iv_hex' => bin2hex($this->iv)
        ];
    }

    /**
     * ✅ MÉTODO CRÍTICO: Probar con el ejemplo del banco
     */
    public function compareWithBankExample(): array
    {
        $bankExample = [
            'ClientGUID' => '4A074C46-DD4E-4E54-8010-B80A6A8758F4',
            'Value' => 'V+aTwhmz9NrCwyFFb6w52Lw+CDFZBqpB3lyCzWIxsVFsnx2ShTrB3rPqR4d+egRNirfBjm6tAuys4ziO5XItfVNlPtYeyjKOUPAdtgxDnVSjNjJxySIIeLhkBXjPZ2dvIYsB8v3I8qEoWhIx+EAalQ==',
            'Validation' => 'fb8443f34045bdba97a174776205f7fee4e8dd59ccf15cc915d5bf2d2c61841b'
        ];

        $testData = '{"ClientGUID":"4A074C46-DD4E-4E54-8010-B80A6A8758F4"}';
        $yourValue = $this->encryptAES($testData);
        $yourValidation = $this->encryptSHA256($testData);

        return [
            'bank_value' => $bankExample['Value'],
            'your_value' => $yourValue,
            'value_match' => $yourValue === $bankExample['Value'],
            'value_length_match' => strlen($yourValue) === strlen($bankExample['Value']),

            'bank_validation' => $bankExample['Validation'],
            'your_validation' => $yourValidation,
            'validation_match' => $yourValidation === $bankExample['Validation'],

            'key_hex' => bin2hex($this->key),
            'iv_hex' => bin2hex($this->iv)
        ];
    }
}
