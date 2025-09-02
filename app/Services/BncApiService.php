<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class BncApiService
{
    // Credenciales y URLs para la API de BNC
    private string $authApiUrl;
    private string $clientGuid;
    private string $masterKey;
    private string $merchantId;
    private string $c2pApiUrl;
    private string $vposApiUrl;
    private string $p2pApiUrl;
    private string $banksApiUrl;

    // URL y configuración para la API de tasas del BCV
    private string $bcvRatesApiUrl;
    private int $bcvCacheDuration;

    protected DataCypher $dataCypher;

    public function __construct()
    {
        // Carga las variables directamente del archivo .env usando env()
        $this->authApiUrl = env('BNC_AUTH_API_URL');
        $this->clientGuid = env('BNC_CLIENT_GUID');
        $this->masterKey = env('BNC_MASTER_KEY');
        $this->merchantId = env('BNC_MERCHANT_ID');
        $this->c2pApiUrl = env('BNC_C2P_API_URL');
        $this->vposApiUrl = env('BNC_VPOS_API_URL');
        $this->p2pApiUrl = env('BNC_P2P_API_URL');
        $this->banksApiUrl = env('BNC_BANKS_API_URL');

        // Y las variables del BCV de la misma manera
        $this->bcvRatesApiUrl = env('BCV_RATES_API_URL');
        $this->bcvCacheDuration = env('BCV_RATES_CACHE_MINUTES', 60);

        // ✅ Inicializar DataCypher con la master key
        $this->dataCypher = new DataCypher($this->masterKey);
    }

    /**
     * Genera un reference único por día
     */
    private function generateDailyReference(): string
    {
        return 'APP_' . date('Ymd') . '_' . substr(uniqid(), -6);
    }

    public function getSessionToken(): ?string
    {
        return Cache::remember('bnc_session_token', now()->addMinutes(59), function () {
            try {
                // 1. Validar configuración básica
                if (empty($this->authApiUrl) || empty($this->masterKey) || empty($this->clientGuid)) {
                    Log::error('Configuración incompleta');
                    return null;
                }

                if (!filter_var($this->authApiUrl, FILTER_VALIDATE_URL)) {
                    Log::error('URL de API inválida', ['url' => $this->authApiUrl]);
                    return null;
                }

                // 2. Validar GUID
                if (!preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $this->clientGuid)) {
                    Log::error('ClientGUID con formato inválido', ['guid' => $this->clientGuid]);
                    return null;
                }

                // 3. Testear encryptación (CRÍTICO)
                $testResult = $this->dataCypher->testEncryption();
                if (!$testResult['success']) {
                    Log::error('Test de encryptación falló', $testResult);
                    return null;
                }

                // 4. Construir JSON de forma segura
                $cliente = json_encode(['ClientGUID' => $this->clientGuid]);
                if ($cliente === false) {
                    Log::error('Error al codificar JSON');
                    return null;
                }

                // ✅ USAR DATACYPHER PARA ENCRYPTACIÓN Y HASH
                $value = $this->dataCypher->encryptAES($cliente);
                $validation = $this->dataCypher->encryptSHA256($cliente);

                // ✅ SOLICITUD CON FORMATO CORRECTO (mayúsculas) y MODO PRUEBA
                $solicitud = [
                    "ClientGUID" => $this->clientGuid,
                    "Value" => $value,
                    "Validation" => $validation,
                    "Reference" => $this->generateDailyReference(),
                    "swTestOperation" => true  // ← MODO PRUEBA ACTIVADO
                ];

                Log::debug('DEBUG - Request completo a BNC', [
                    'url' => $this->authApiUrl,
                    'cliente_json' => $cliente,
                    'cliente_hex' => bin2hex($cliente),
                    'value_encrypted' => $value,
                    'validation_hash' => $validation,
                    'master_key_first10' => substr($this->masterKey, 0, 10),
                    'full_request' => $solicitud
                ]);

                Log::info('Enviando solicitud a la API de BNC', ['request' => $solicitud]);

                $response = Http::timeout(30)
                    ->retry(3, 100)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'User-Agent' => 'Laravel-BNC-Client/1.0'
                    ])
                    ->post($this->authApiUrl, $solicitud);

                // ✅ MEJOR MANEJO DE RESPUESTA - Verificar status "OK"
                if ($response->successful()) {
                    $responseData = $response->json();

                    // Verificar si la respuesta tiene status OK
                    if (isset($responseData['status']) && $responseData['status'] === 'OK') {
                        if (isset($responseData['value']) && !empty($responseData['value'])) {
                            $token = $responseData['value'];
                            Log::info('BNC Session Token obtenido exitosamente.');
                            return $token;
                        } else {
                            Log::error('Campo "value" vacío en respuesta OK', [
                                'response' => $responseData
                            ]);
                            return null;
                        }
                    } else {
                        Log::error('Error en la API BNC - Status no es OK', [
                            'status_response' => $responseData['status'] ?? 'NO_STATUS',
                            'message' => $responseData['message'] ?? 'NO_MESSAGE',
                            'full_response' => $responseData
                        ]);
                        return null;
                    }
                } else {
                    Log::error('Error HTTP en la API BNC', [
                        'http_status' => $response->status(),
                        'response' => $response->json() ?? $response->body(),
                    ]);
                    return null;
                }
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                Log::error('Error de conexión: ' . $e->getMessage());
                return null;
            } catch (\Illuminate\Http\Client\RequestException $e) {
                Log::error('Error en la petición HTTP: ' . $e->getMessage());
                Log::error('Response body: ' . ($e->response->body() ?? 'No response'));
                return null;
            } catch (Exception $e) {
                Log::error('Excepción inesperada: ' . $e->getMessage());
                Log::error('File: ' . $e->getFile() . ' Line: ' . $e->getLine());
                return null;
            }
        });
    }

    /**
     * Método para testing manual
     */
    public function testEncryptionManual(): array
    {
        return $this->dataCypher->testEncryption();
    }

    /**
     * Limpia el cache del token
     */
    public function clearTokenCache(): void
    {
        Cache::forget('bnc_session_token');
        Log::info('Cache de token BNC limpiado');
    }

    /**
     * Inicia un pago C2P (Pago Móvil) a través de la API del BNC.
     *
     * @param array $data Los datos necesarios para el pago C2P.
     * @return array|null La respuesta del BNC si el pago se inicia con éxito, o null en caso de error.
     */
    public function initiateC2PPayment(array $data): ?array
    {
        try {
            // Estructura el array de datos con las claves que la API del BNC espera
            $soliC2p = [
                "DebtorBankCode" => intval($data['banco']),
                "DebtorCellPhone" => $data['telefono'],
                "DebtorID" => $data['cedula'],
                "Amount" => floatval($data['monto']),
                "Token" => $data['token'],
                "Terminal" => $data['terminal'],
            ];

            // Agrega el MerchantID al payload de la solicitud.
            // Esto es un requisito común para las APIs de pago.
            $soliC2p['MerchantID'] = $this->merchantId;

            // El método sendEncryptedPostRequest se encarga de toda la lógica
            // de encriptación, hashing y el envío de la petición HTTP.
            return $this->sendEncryptedPostRequest($this->c2pApiUrl, $soliC2p);
        } catch (\Throwable $e) {
            Log::error('Excepción al procesar el pago C2P: ' . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Procesa un pago con tarjeta a través de la API del BNC (VPOS).
     *
     * @param array $data Los datos para el pago con tarjeta.
     * @return array|null La respuesta si el pago se procesa con éxito, o null en caso de error.
     */
    public function processCardPayment(array $data): ?array
    {
        try {
            // Asegúrate de que los datos de entrada coincidan con lo que la API espera.
            // Aquí se crea el payload con las claves que la API requiere.
            $soliVpos = [
                "TransactionIdentifier" => $data['identificador'],
                "Amount" => floatval($data['monto']),
                "idCardType" => intval($data['tipTarjeta']),
                "CardNumber" => intval($data['tarjeta']),
                "dtExpiration" => intval($data['fechExp']),
                "CardHolderName" => $data['nomTarjeta'],
                "AccountType" => intval($data['tipCuenta']),
                "CVV" => intval($data['cvv']),
                "CardPIN" => intval($data['pin']),
                "CardHolderID" => intval($data['identificacion']),
                "AffiliationNumber" => intval($data['afiliacion']),
                // Agrega el MerchantID, que es un requisito común para VPOS.
                "MerchantID" => $this->merchantId,
            ];

            // El método sendEncryptedPostRequest encapsula toda la lógica de encriptación,
            // hashing y envío.
            $response = $this->sendEncryptedPostRequest($this->vposApiUrl, $soliVpos);

            // Retorna la respuesta del BNC. El controlador decidirá cómo mostrarla.
            return $response;
        } catch (\Throwable $e) {
            Log::error('Excepción al procesar el pago VPOS: ' . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Procesa un pago P2P (Pago Móvil) a través de la API del BNC.
     *
     * @param array $data Los datos necesarios para el pago P2P.
     * @return array|null La respuesta del BNC si el pago se procesa con éxito, o null en caso de error.
     */
    public function initiateP2PPayment(array $data): ?array
    {
        try {
            // Estructura el array de datos con las claves que la API del BNC espera
            $soliP2p = [
                "BeneficiaryBankCode" => intval($data['banco']),
                "BeneficiaryCellPhone" => $data['telefono'],
                "BeneficiaryID" => $data['cedula'],
                "BeneficiaryName" => $data['beneficiario'],
                "Amount" => floatval($data['monto']),
                "Description" => $data['descripcion'],
                "BeneficiaryEmail" => $data['email'],
            ];

            // Agrega el MerchantID al payload de la solicitud
            $soliP2p['MerchantID'] = $this->merchantId;

            // El método sendEncryptedPostRequest se encarga de toda la lógica
            // de encriptación, hashing y el envío de la petición HTTP.
            return $this->sendEncryptedPostRequest($this->p2pApiUrl, $soliP2p);
        } catch (\Throwable $e) {
            Log::error('Excepción al procesar el pago P2P: ' . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Obtiene la lista de bancos de la API del BNC.
     *
     * @return array|null Un array de bancos (ej: [{'code': '0102', 'name': 'Banco de Venezuela'}, ...]) o null si falla.
     */
    public function getBanksFromBnc(): ?array
    {
        try {
            if (empty($this->banksApiUrl)) {
                Log::warning('BNC_BANKS_API_URL no está configurada. No se puede obtener la lista de bancos del BNC.');
                return null;
            }

            $response = Http::get($this->banksApiUrl);

            if ($response->successful()) {
                $banks = $response->json();
                Log::info('Lista de bancos del BNC obtenida exitosamente.', ['count' => count($banks)]);
                return $banks;
            } else {
                Log::error('Fallo al obtener la lista de bancos del BNC.', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return null;
            }
        } catch (Exception $e) {
            Log::error('Excepción al obtener la lista de bancos del BNC: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return null;
        }
    }

    /**
     * Obtiene y almacena en caché las tasas de cambio del BCV.
     *
     * @return array|null Un array de tasas (ej: [{'currency': 'USD', 'rate': 36.5}, ...]) o null si falla.
     */
    public function getBcvExchangeRates(): ?array
    {
        return Cache::remember('bcv_exchange_rates', now()->addMinutes($this->bcvCacheDuration), function () {
            Log::info('Obteniendo tasas de cambio del BCV desde fuente externa (o simulando).');
            try {
                if (empty($this->bcvRatesApiUrl)) {
                    Log::warning('BCV_RATES_API_URL no está configurada. Retornando tasas de BCV simuladas.');
                    return $this->simulateBcvRates();
                }
                $response = Http::timeout(10)->get($this->bcvRatesApiUrl);
                if ($response->successful()) {
                    $rates = $response->json();
                    Log::info('Tasas de cambio del BCV obtenidas exitosamente.', ['rates' => $rates]);
                    return $rates;
                } else {
                    Log::error('Fallo al obtener las tasas de cambio del BCV desde la API externa.', [
                        'status' => $response->status(),
                        'response' => $response->body()
                    ]);
                    return null;
                }
            } catch (Exception $e) {
                Log::error('Excepción al obtener las tasas de cambio del BCV: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
                return null;
            }
        });
    }

    /**
     * Simula las tasas de cambio del BCV para desarrollo/pruebas.
     *
     * @return array
     */
    private function simulateBcvRates(): array
    {
        $usdRate = round(36.00 + (mt_rand(-50, 50) / 100.0), 2);
        $eurRate = round(38.00 + (mt_rand(-50, 50) / 100.0), 2);
        return [
            ['currency' => 'USD', 'rate' => $usdRate],
            ['currency' => 'EUR', 'rate' => $eurRate],
        ];
    }

    // --- NUEVO MÉTODO PRIVADO PARA ENCAPSULAR LÓGICA REPETIDA ---

    /**
     * Método privado que gestiona la lógica de encriptación, hashing y envío de peticiones POST a la API de BNC.
     *
     * @param string $url El endpoint de la API.
     * @param array $payload Los datos a encriptar y enviar.
     * @return array|null La respuesta decodificada del BNC o null si falla.
     */
    private function sendEncryptedPostRequest(string $url, array $payload): ?array
    {
        try {
            $jsonPayload = json_encode($payload);

            $workingKey = $this->getWorkingKey();
            $value = $this->encrypt($jsonPayload, $workingKey);
            $validation = $this->createHash($jsonPayload);
            $reference = $this->refere();

            $requestData = [
                "ClientGUID" => $this->clientGuid,
                "value" => $value,
                "Validation" => $validation,
                "Reference" => $reference,
                "swTestOperation" => false
            ];

            $response = Http::post($url, $requestData);

            if ($response->successful()) {
                Log::info('Petición exitosa a la API BNC.', ['url' => $url, 'response' => $response->json()]);
                return $response->json();
            } else {
                Log::error('Fallo en la petición a la API BNC.', [
                    'url' => $url,
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'request_data' => $payload
                ]);
                return null;
            }
        } catch (Exception $e) {
            Log::error('Excepción al enviar petición a la API BNC: ' . $e->getMessage(), ['url' => $url, 'trace' => $e->getTraceAsString()]);
            return null;
        }
    }
    /**
     * Implementa la lógica de creación de hash que la API BNC requiere.
     *
     * @param string $data
     * @return string
     */
    private function createHash(string $data): string
    {
        // Utiliza hash_hmac con la MasterKey
        return hash_hmac('sha256', $data, $this->masterKey);
    }

    /**
     * Obtiene la "Working Key" si es necesaria para la encriptación.
     *
     * @return string
     */
    private function getWorkingKey(): string
    {
        // En este caso, asumimos que la MasterKey es la Working Key
        return $this->masterKey;
    }

    /**
     * Genera un identificador de referencia único para la transacción.
     *
     * @return string
     */
    private function refere(): string
    {
        return uniqid('ref_', true);
    }

    private function encrypt(string $data, string $key): string
    {
        // 1. Preparar clave de 32 bytes
        $key = substr(hash('sha256', $key, true), 0, 32);

        // 2. IV FIJO (usar primeros 16 bytes de la clave)
        $iv = substr($key, 0, 16);

        // 3. Encryptar
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);

        // 4. Devolver SOLO los datos encryptados en base64
        return base64_encode($encrypted);
    }

    private function decrypt(string $encryptedData, string $key): string
    {
        // 1. Preparar clave (EXACTAMENTE igual que en encrypt)
        $key = substr(hash('sha256', $key, true), 0, 32);

        // 2. MISMO IV que en encrypt
        $iv = substr($key, 0, 16);

        // 3. Decryptar
        return openssl_decrypt(
            base64_decode($encryptedData),
            'aes-256-cbc',
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );
    }
}
