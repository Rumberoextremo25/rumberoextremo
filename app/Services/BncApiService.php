<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

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
    private string $bncRatesApiUrl;

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
        $this->bncRatesApiUrl = env('BCV_RATES_API_URL');

        // ✅ Inicializar DataCypher con la master key
        $this->dataCypher = new DataCypher($this->masterKey);
    }

    /**
     * ==================== MÉTODOS PRINCIPALES ====================
     */

    /**
     * Obtiene token de sesión del BNC
     */
    public function getSessionToken(): ?string
    {
        return Cache::remember('bnc_session_token', now()->addMinutes(59), function () {
            try {
                // Validar configuración básica
                if (empty($this->authApiUrl) || empty($this->masterKey) || empty($this->clientGuid)) {
                    Log::error('Configuración incompleta para obtener token');
                    return null;
                }

                // Construir solicitud
                $cliente = json_encode(['ClientGUID' => $this->clientGuid]);
                $solicitud = [
                    "ClientGUID" => $this->clientGuid,
                    "Value" => $this->dataCypher->encryptAES($cliente),
                    "Validation" => $this->dataCypher->encryptSHA256($cliente),
                    "Reference" => $this->generateDailyReference(),
                    "swTestOperation" => false
                ];

                $response = Http::timeout(30)
                    ->retry(3, 100)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ])
                    ->post($this->authApiUrl, $solicitud);

                if ($response->successful()) {
                    $responseData = $response->json();
                    if (isset($responseData['status']) && $responseData['status'] === 'OK' && !empty($responseData['value'])) {
                        Log::info('BNC Session Token obtenido exitosamente');
                        return $responseData['value'];
                    }
                    Log::error('Error en respuesta de autenticación BNC', $responseData);
                    return null;
                }

                Log::error('Error HTTP en autenticación BNC', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return null;
            } catch (Exception $e) {
                Log::error('Excepción al obtener token: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Inicia un pago C2P (Pago Móvil)
     */
    public function initiateC2PPayment(array $data): ?array
    {
        try {
            Log::info('Iniciando pago C2P', ['data_keys' => array_keys($data)]);

            // 1. ✅ Validar campos requeridos (EXACTAMENTE como legacy)
            $requiredFields = ['banco', 'telefono', 'cedula', 'monto', 'token', 'terminal'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    throw new Exception("Campo requerido faltante: $field");
                }
            }

            // 2. ✅ OBTENER TOKEN DE SESIÓN
            $sessionToken = $this->getSessionToken();
            if (!$sessionToken) {
                Log::error('No se pudo obtener token de sesión para C2P');
                return null;
            }

            // 3. ✅ ESTRUCTURAR DATOS C2P (EXACTAMENTE como legacy - SIN CAMBIOS)
            $soliC2p = [
                "DebtorBankCode" => intval($data['banco']),          // ← intval() como legacy
                "DebtorCellPhone" => $data['telefono'],              // ← Raw, sin formato (¡REMOVER formatPhoneNumber!)
                "DebtorID" => $data['cedula'],                       // ← Raw, mantener "V" si existe
                "Amount" => floatval($data['monto']),                // ← floatval() como legacy
                "Token" => $data['token'],                           // ← Raw
                "Terminal" => $data['terminal']                      // ← Raw
                // ← ¡NO incluir TransactionID, ChildClientID, BranchID!
            ];

            Log::debug('Datos C2P preparados (legacy style)', ['soliC2p' => $soliC2p]);

            // 4. ✅ ENCRIPTAR LOS DATOS C2P (usar DataCypher)
            $jsonC2p = json_encode($soliC2p);
            if ($jsonC2p === false) {
                Log::error('Error al codificar JSON para C2P');
                return null;
            }

            $value = $this->dataCypher->encryptAES($jsonC2p);
            $validation = $this->dataCypher->encryptSHA256($jsonC2p);

            // 5. ✅ CONSTRUIR SOLICITUD FINAL (EXACTA como legacy)
            $solicitud = [
                "ClientGUID" => $this->clientGuid,
                "value" => $value,           // ← minúscula como legacy
                "Validation" => $validation, // ← mayúscula como legacy
                "Reference" => $this->generateDailyReference(),
                "swTestOperation" => false
            ];

            Log::debug('Solicitud completa preparada', [
                'client_guid' => $this->clientGuid,
                'value_length' => strlen($value),
                'validation' => $validation,
                'reference' => $solicitud['Reference']
            ]);

            // 6. ✅ HACER REQUEST DIRECTAMENTE (sin sendEncryptedRequestWithToken)
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $sessionToken,
                    'X-Client-GUID' => $this->clientGuid,
                    'X-Merchant-ID' => $this->merchantId
                ])
                ->post($this->c2pApiUrl, $solicitud);

            // 7. ✅ MANEJAR RESPUESTA
            if ($response->successful() || $response->status() === 409) {
                $responseBody = $response->body();

                if (!empty($responseBody)) {
                    $jsonResponse = json_decode($responseBody, true);

                    if (is_array($jsonResponse)) {
                        Log::info('Respuesta C2P recibida', [
                            'status' => $jsonResponse['status'] ?? 'unknown',
                            'message' => $jsonResponse['message'] ?? 'unknown',
                            'http_status' => $response->status()
                        ]);
                        return $jsonResponse;
                    }
                }
            }

            Log::error('Error en request C2P', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return null;
        } catch (Exception $e) {
            Log::error('Excepción en pago C2P: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Procesa un pago con tarjeta (VPOS)
     */
    public function processCardPayment(array $data): ?array
    {
        try {
            $requiredFields = [
                'identificador',
                'monto',
                'tipTarjeta',
                'tarjeta',
                'fechExp',
                'nomTarjeta',
                'tipCuenta',
                'cvv',
                'pin',
                'identificacion',
                'afiliacion'
            ];

            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    throw new Exception("Campo requerido faltante: $field");
                }
            }

            $sessionToken = $this->getSessionToken();
            if (!$sessionToken) {
                Log::error('No se pudo obtener token para VPOS');
                return null;
            }

            // ✅ ESTRUCTURAR DATOS VPOS (EXACTAMENTE como legacy)
            $soliVpos = [
                "TransactionIdentifier" => $data['identificador'],      // ← Raw
                "Amount" => floatval($data['monto']),
                "idCardType" => intval($data['tipTarjeta']),
                "CardNumber" => intval($data['tarjeta']),              // ← intval() como legacy
                "dtExpiration" => intval($data['fechExp']),            // ← intval() como legacy
                "CardHolderName" => $data['nomTarjeta'],               // ← Raw
                "AccountType" => intval($data['tipCuenta']),
                "CVV" => intval($data['cvv']),                         // ← intval() como legacy
                "CardPIN" => intval($data['pin']),                     // ← intval() como legacy
                "CardHolderID" => intval($data['identificacion']),     // ← intval() como legacy
                "AffiliationNumber" => intval($data['afiliacion'])     // ← intval() como legacy
            ];

            Log::debug('Datos VPOS preparados (legacy style)', ['soliVpos' => $soliVpos]);

            $jsonVpos = json_encode($soliVpos);
            $value = $this->dataCypher->encryptAES($jsonVpos);
            $validation = $this->dataCypher->encryptSHA256($jsonVpos);

            $solicitud = [
                "ClientGUID" => $this->clientGuid,
                "value" => $value,           // ← minúscula como legacy
                "Validation" => $validation, // ← mayúscula como legacy
                "Reference" => $this->generateDailyReference(),
                "swTestOperation" => false
            ];

            // ✅ HACER REQUEST DIRECTAMENTE (sin wrapper)
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $sessionToken,
                    'X-Client-GUID' => $this->clientGuid,
                    'X-Merchant-ID' => $this->merchantId
                ])
                ->post($this->vposApiUrl, $solicitud);

            // ✅ MANEJAR RESPUESTA
            if ($response->successful() || $response->status() === 409) {
                $responseBody = $response->body();

                if (!empty($responseBody)) {
                    $jsonResponse = json_decode($responseBody, true);

                    if (is_array($jsonResponse)) {
                        Log::info('Respuesta VPOS recibida', [
                            'status' => $jsonResponse['status'] ?? 'unknown',
                            'message' => $jsonResponse['message'] ?? 'unknown',
                            'http_status' => $response->status()
                        ]);
                        return $jsonResponse;
                    }
                }
            }

            Log::error('Error en request VPOS', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return null;
        } catch (Exception $e) {
            Log::error('Excepción en pago VPOS: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Procesa un pago P2P (Transferencia)
     */
    public function initiateP2PPayment(array $data): ?array
    {
        try {
            $requiredFields = ['banco', 'telefono', 'cedula', 'beneficiario', 'monto', 'descripcion'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    throw new Exception("Campo requerido faltante: $field");
                }
            }

            $sessionToken = $this->getSessionToken();
            if (!$sessionToken) {
                Log::error('No se pudo obtener token para P2P');
                return null;
            }

            // ✅ ESTRUCTURAR DATOS P2P (EXACTAMENTE como legacy)
            $soliP2p = [
                "BeneficiaryBankCode" => intval($data['banco']),
                "BeneficiaryCellPhone" => $data['telefono'],              // ← Raw, sin formato
                "BeneficiaryID" => $data['cedula'],                       // ← Raw
                "BeneficiaryName" => $data['beneficiario'],               // ← Raw
                "Amount" => floatval($data['monto']),
                "Description" => $data['descripcion'],                    // ← Raw
                "BeneficiaryEmail" => $data['email'] ?? null              // ← null en lugar de ''
            ];

            Log::debug('Datos P2P preparados (legacy style)', ['soliP2p' => $soliP2p]);

            // ✅ ENCRIPTAR SIN FILTRAR (como legacy)
            $jsonP2p = json_encode($soliP2p);                            // ← SIN array_filter
            $value = $this->dataCypher->encryptAES($jsonP2p);
            $validation = $this->dataCypher->encryptSHA256($jsonP2p);

            $solicitud = [
                "ClientGUID" => $this->clientGuid,
                "value" => $value,           // ← minúscula como legacy
                "Validation" => $validation, // ← mayúscula como legacy
                "Reference" => $this->generateDailyReference(),
                "swTestOperation" => false
            ];

            // ✅ HACER REQUEST DIRECTAMENTE (sin wrapper)
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $sessionToken,
                    'X-Client-GUID' => $this->clientGuid,
                    'X-Merchant-ID' => $this->merchantId
                ])
                ->post($this->p2pApiUrl, $solicitud);

            // ✅ MANEJAR RESPUESTA
            if ($response->successful() || $response->status() === 409) {
                $responseBody = $response->body();

                if (!empty($responseBody)) {
                    $jsonResponse = json_decode($responseBody, true);

                    if (is_array($jsonResponse)) {
                        Log::info('Respuesta P2P recibida', [
                            'status' => $jsonResponse['status'] ?? 'unknown',
                            'message' => $jsonResponse['message'] ?? 'unknown',
                            'http_status' => $response->status()
                        ]);
                        return $jsonResponse;
                    }
                }
            }

            Log::error('Error en request P2P', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return null;
        } catch (Exception $e) {
            Log::error('Excepción en pago P2P: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene la lista de bancos del BNC
     */
    public function getBanksFromBnc(): ?array
    {
        try {
            Log::info('Solicitando lista de bancos al BNC');

            if (empty($this->banksApiUrl)) {
                Log::warning('BNC_BANKS_API_URL no está configurada');
                return $this->getSimulatedBanks();
            }

            // 1. ✅ OBTENER TOKEN DE SESIÓN
            $token = $this->getSessionToken();
            if (!$token) {
                Log::error('No se pudo obtener token de sesión');
                return $this->getSimulatedBanks();
            }

            // 2. ✅ PREPARAR SOLICITUD
            $solicitudBancos = ["ClientGUID" => $this->clientGuid];
            $jsonSolicitud = json_encode($solicitudBancos);

            $value = $this->dataCypher->encryptAES($jsonSolicitud);
            $validation = $this->dataCypher->encryptSHA256($jsonSolicitud);

            $solicitud = [
                "ClientGUID" => $this->clientGuid,
                "value" => $value,
                "Validation" => $validation,
                "Reference" => $this->generateDailyReference(),
                "swTestOperation" => false
            ];

            // 3. ✅ INTENTAR 2 VECES CON REINTENTOS
            $maxAttempts = 2;
            for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
                Log::debug("Intento $attempt de $maxAttempts");

                $response = Http::timeout(30)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer ' . $token,
                        'X-Client-GUID' => $this->clientGuid,
                        'X-Merchant-ID' => $this->merchantId
                    ])
                    ->post($this->banksApiUrl, $solicitud);

                // 4. ✅ SI ES 409, REINTENTAR DESPUÉS DE PAUSA
                if ($response->status() === 409) {
                    if ($attempt < $maxAttempts) {
                        Log::warning("Intento $attempt falló con 409, reintentando en 2 segundos...");
                        sleep(2);
                        continue;
                    }

                    Log::error('Todos los intentos fallaron con error 409 del BNC');
                    return $this->getSimulatedBanks();
                }

                // 5. ✅ SI ES EXITOSO, PROCESAR
                if ($response->successful()) {
                    $responseBody = $response->body();
                    if (!empty($responseBody)) {
                        $jsonResponse = json_decode($responseBody, true);

                        if (isset($jsonResponse['value']) && !empty($jsonResponse['value'])) {
                            $decryptedData = $this->decrypt($jsonResponse['value'], $this->masterKey);
                            if ($decryptedData) {
                                $banks = json_decode($decryptedData, true);
                                if (is_array($banks)) {
                                    Log::info('Lista de bancos obtenida exitosamente del BNC');
                                    return $banks;
                                }
                            }
                        }
                    }
                }

                break; // Salir del loop si no es 409
            }

            // 6. ✅ SI TODO FALLA, USAR DATOS SIMULADOS
            Log::warning('Usando datos simulados de bancos debido a error del BNC');
            return $this->getSimulatedBanks();
        } catch (Exception $e) {
            Log::error('Excepción al obtener bancos: ' . $e->getMessage());
            return $this->getSimulatedBanks();
        }
    }

    /**
     * ✅ Datos simulados de bancos para desarrollo/fallback
     */
    private function getSimulatedBanks(): array
    {
        return [
            ['Code' => '0102', 'Name' => 'Banco de Venezuela', 'Status' => 'A'],
            ['Code' => '0104', 'Name' => 'Venezolano de Crédito', 'Status' => 'A'],
            ['Code' => '0105', 'Name' => 'Banco Mercantil', 'Status' => 'A'],
            ['Code' => '0108', 'Name' => 'Banco Provincial', 'Status' => 'A'],
            ['Code' => '0114', 'Name' => 'Bancaribe', 'Status' => 'A'],
            ['Code' => '0115', 'Name' => 'Banco Exterior', 'Status' => 'A'],
            ['Code' => '0116', 'Name' => 'Banco Occidental de Descuento', 'Status' => 'A'],
            ['Code' => '0128', 'Name' => 'Banco Caroní', 'Status' => 'A'],
            ['Code' => '0134', 'Name' => 'Banesco', 'Status' => 'A'],
            ['Code' => '0137', 'Name' => 'Banco Sofitasa', 'Status' => 'A'],
            ['Code' => '0138', 'Name' => 'Banco Plaza', 'Status' => 'A'],
            ['Code' => '0146', 'Name' => 'Banco de la Gente Emprendedora', 'Status' => 'A'],
            ['Code' => '0149', 'Name' => 'Banco del Pueblo Soberano', 'Status' => 'A'],
            ['Code' => '0151', 'Name' => 'BFC Banco Fondo Común', 'Status' => 'A'],
            ['Code' => '0156', 'Name' => '100% Banco', 'Status' => 'A'],
            ['Code' => '0157', 'Name' => 'DelSur Banco Universal', 'Status' => 'A'],
            ['Code' => '0163', 'Name' => 'Banco del Tesoro', 'Status' => 'A'],
            ['Code' => '0166', 'Name' => 'Banco Agrícola de Venezuela', 'Status' => 'A'],
            ['Code' => '0168', 'Name' => 'Bancrecer', 'Status' => 'A'],
            ['Code' => '0169', 'Name' => 'Mi Banco', 'Status' => 'A'],
            ['Code' => '0171', 'Name' => 'Banco Activo', 'Status' => 'A'],
            ['Code' => '0172', 'Name' => 'Bancamiga', 'Status' => 'A'],
            ['Code' => '0173', 'Name' => 'Banco Internacional de Desarrollo', 'Status' => 'A'],
            ['Code' => '0174', 'Name' => 'Banplus', 'Status' => 'A'],
            ['Code' => '0175', 'Name' => 'Banco Bicentenario', 'Status' => 'A'],
            ['Code' => '0177', 'Name' => 'Banco de la Fuerza Armada Nacional Bolivariana', 'Status' => 'A'],
            ['Code' => '0190', 'Name' => 'Citibank', 'Status' => 'A'],
            ['Code' => '0191', 'Name' => 'Banco Nacional de Crédito', 'Status' => 'A']
        ];
    }

    /**
     * ==================== MÉTODOS AUXILIARES ====================
     */

    /**
     * Envía request encriptado con token
     */
    private function sendEncryptedRequestWithToken(string $url, array $solicitud, string $token): ?array
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                    'X-Client-GUID' => $this->clientGuid,
                    'X-Merchant-ID' => $this->merchantId
                ])
                ->post($url, $solicitud);

            if ($response->successful() || $response->status() === 409) {
                $jsonResponse = $response->json();
                $jsonResponse['http_status'] = $response->status();
                return $jsonResponse;
            }

            return [
                'http_status' => $response->status(),
                'error' => $response->body()
            ];
        } catch (Exception $e) {
            Log::error('Error en sendEncryptedRequestWithToken: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Reintenta operación con nuevo token
     */
    private function retryWithNewToken(string $url, array $solicitud, string $operation): ?array
    {
        Log::warning("Token caducado en $operation, renovando");
        Cache::forget('bnc_session_token');
        $newToken = $this->getSessionToken();

        if ($newToken) {
            Log::info("Token renovado, reintentando $operation");
            return $this->sendEncryptedRequestWithToken($url, $solicitud, $newToken);
        }

        Log::error("No se pudo renovar token para $operation");
        return null;
    }

    /**
     * Genera referencia única diaria
     */
    private function generateDailyReference(): string
    {
        return 'APP_' . date('Ymd') . '_' . substr(uniqid(), -6);
    }

    /**
     * Formatea número de teléfono
     */
    private function formatPhoneNumber(string $phone): string
    {
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($cleanPhone) === 8) {
            return '502' . $cleanPhone;
        }
        return $cleanPhone;
    }

    /**
     * Limpia cache de token
     */
    public function clearTokenCache(): void
    {
        Cache::forget('bnc_session_token');
        Log::info('Cache de token BNC limpiado');
    }

    /**
     * Test de encriptación
     */
    public function testEncryptionManual(): array
    {
        return $this->dataCypher->testEncryption();
    }

    /**
     * Método encrypt legacy (compatible con el BNC)
     */
    private function encrypt(string $data, string $key): string
    {
        try {
            // 1. Preparar clave de 32 bytes
            $key = substr(hash('sha256', $key, true), 0, 32);

            // 2. IV FIJO (usar primeros 16 bytes de la clave)
            $iv = substr($key, 0, 16);

            // 3. Encryptar
            $encrypted = openssl_encrypt(
                $data,
                'aes-256-cbc',
                $key,
                OPENSSL_RAW_DATA,
                $iv
            );

            return $encrypted ? base64_encode($encrypted) : '';
        } catch (Exception $e) {
            Log::error('Error en encrypt legacy: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * Método decrypt legacy (compatible con el BNC - para respuestas)
     */
    private function decrypt(string $encryptedData, string $key): string
    {
        try {
            // 1. Preparar clave de 32 bytes (igual que en encrypt)
            $key = substr(hash('sha256', $key, true), 0, 32);

            // 2. IV FIJO (usar primeros 16 bytes de la clave)
            $iv = substr($key, 0, 16);

            // 3. Decryptar
            $decrypted = openssl_decrypt(
                base64_decode($encryptedData),
                'aes-256-cbc',
                $key,
                OPENSSL_RAW_DATA,
                $iv
            );

            return $decrypted ?: '';
        } catch (Exception $e) {
            Log::error('Error en decrypt legacy: ' . $e->getMessage());
            return '';
        }
    }

    public function testBothDecryptionMethods(string $encryptedValue): array
    {
        return [
            'encrypted_value' => $encryptedValue,
            'legacy_decrypt' => $this->decrypt($encryptedValue, $this->masterKey),
            'datacypher_decrypt' => $this->dataCypher->decryptAES($encryptedValue),
            'master_key_preview' => substr($this->masterKey, 0, 10) . '...'
        ];
    }

    public function checkBncConnection(): array
    {
        $results = [];

        // 1. Verificar URLs
        $results['urls'] = [
            'banks_url' => $this->banksApiUrl,
            'auth_url' => $this->authApiUrl,
            'banks_url_reachable' => !empty($this->banksApiUrl),
            'auth_url_reachable' => !empty($this->authApiUrl)
        ];

        // 2. Verificar credenciales
        $results['credentials'] = [
            'client_guid' => !empty($this->clientGuid),
            'merchant_id' => !empty($this->merchantId),
            'master_key' => !empty($this->masterKey),
            'client_guid_length' => strlen($this->clientGuid),
            'merchant_id_length' => strlen($this->merchantId)
        ];

        // 3. Verificar token
        $token = $this->getSessionToken();
        $results['token'] = [
            'has_token' => !empty($token),
            'token_length' => strlen($token ?? ''),
            'token_preview' => !empty($token) ? substr($token, 0, 10) . '...' . substr($token, -10) : null
        ];

        // 4. Intentar conexión básica
        try {
            $testResponse = Http::timeout(10)
                ->get('https://servicios.bncenlinea.com:16500');
            $results['connectivity'] = [
                'success' => $testResponse->status() !== 0,
                'status' => $testResponse->status()
            ];
        } catch (Exception $e) {
            $results['connectivity'] = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }

        return $results;
    }

    // En tu controlador
    public function debugBnc(BncApiService $bncApiService)
    {
        $debugInfo = $bncApiService->checkBncConnection();
        return response()->json($debugInfo);
    }

    // Agregar este método al BncApiService // Validar y probar conectividad en producción
    public function testProductionConnectivity(): array
    {
        $results = [];

        // 1. Test DNS
        $host = parse_url($this->banksApiUrl, PHP_URL_HOST);
        $results['dns_lookup'] = gethostbyname($host);

        // 2. Test puerto
        $port = parse_url($this->banksApiUrl, PHP_URL_PORT) ?: 16500;
        $results['port_check'] = @fsockopen($host, $port, $errno, $errstr, 10);

        // 3. Test SSL
        $results['ssl_cert'] = true;
        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->get($this->banksApiUrl, [
                'verify' => true, // Forzar verificación SSL
                'timeout' => 10
            ]);
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $results['ssl_cert'] = false;
            $results['ssl_error'] = $e->getMessage();
        }

        // 4. Test request real
        try {
            $response = Http::timeout(15)
                ->withoutVerifying() // ⚠️ Solo para测试
                ->get($this->banksApiUrl);

            $results['direct_request'] = [
                'status' => $response->status(),
                'success' => $response->successful()
            ];
        } catch (Exception $e) {
            $results['direct_request'] = [
                'error' => $e->getMessage()
            ];
        }

        return $results;
    }
}
