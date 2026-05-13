<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BncApiService
{
    private string $authApiUrl;
    private string $clientGuid;
    private string $masterKey;
    private string $merchantId;
    private string $c2pApiUrl;
    private string $vposApiUrl;
    private string $validationApiUrl;
    private string $debitTokenRequestUrl;
    private string $debitBeginnerUrl;
    private string $debitReenviarUrl;
    private string $banksApiUrl;
    private string $ratesApiUrl;
    protected DataCypher $dataCypher;

    public function __construct()
    {
        // Puerto para servicios principales (C2P, tarjeta, bancos, tasas)
        $port = app()->environment('production') ? '16000' : '16500';
        $baseUrl = "https://servicios.bncenlinea.com:{$port}";

        // Servicios principales (usan puerto según entorno)
        $this->authApiUrl = "{$baseUrl}/api/Auth/LogOn";
        $this->c2pApiUrl = "{$baseUrl}/api/MobPayment/SendC2P";
        $this->vposApiUrl = "{$baseUrl}/api/Transaction/Send";
        $this->validationApiUrl = "{$baseUrl}/api/Position/Validate";
        $this->banksApiUrl = "{$baseUrl}/api/Services/Banks";
        $this->ratesApiUrl = "{$baseUrl}/api/Services/BCVRates";

        // ✅ DÉBITO: SIEMPRE USAR QA (puerto 16500)
        // Esto aplica incluso en producción
        $debitoBaseUrl = "https://servicios.bncenlinea.com:16500";
        $this->debitTokenRequestUrl = "{$debitoBaseUrl}/api/SIMF/DebitTokenRequest";
        $this->debitBeginnerUrl = "{$debitoBaseUrl}/api/SIMF/DebitBeginner";
        $this->debitReenviarUrl = "{$debitoBaseUrl}/api/debito/reenviar-sms";

        // Credenciales desde .env
        $this->clientGuid = env('BNC_CLIENT_GUID');
        $this->masterKey = env('BNC_MASTER_KEY');
        $this->merchantId = env('BNC_MERCHANT_ID');

        $this->dataCypher = new DataCypher($this->masterKey);

        Log::info('🔧 BNC Service inicializado', [
            'environment' => app()->environment(),
            'servicios_port' => $port,
            'debito_port' => 16500,
            'debito_url' => $this->debitTokenRequestUrl
        ]);
    }

    /**
     * ==================== MÉTODOS DE AUTENTICACIÓN ====================
     */

    public function getSessionToken(): ?string
    {
        return Cache::remember('bnc_session_token', now()->addMinutes(59), function () {
            try {
                if (empty($this->authApiUrl) || empty($this->masterKey) || empty($this->clientGuid)) {
                    throw new Exception('Configuración incompleta para obtener token');
                }

                $cliente = '{"ClientGUID":"' . $this->clientGuid . '"}';
                $value = $this->dataCypher->encryptWithKey($cliente, $this->masterKey);
                $validation = $this->dataCypher->encryptSHA256($cliente);

                $solicitud = [
                    "ClientGUID" => $this->clientGuid,
                    "value" => $value,
                    "Validation" => $validation,
                    "Reference" => '',
                    "swTestOperation" => false
                ];

                Log::info('🔑 Solicitando token de sesión', [
                    'url' => $this->authApiUrl,
                    'client_guid' => $this->clientGuid
                ]);

                $response = Http::timeout(30)
                    ->retry(2, 100)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->post($this->authApiUrl, $solicitud);

                if (!$this->isSuccessfulResponse($response)) {
                    throw new Exception('Error HTTP: ' . $response->status());
                }

                $responseData = json_decode($response->body(), true);

                if (!isset($responseData['value'])) {
                    throw new Exception('Estructura de respuesta inválida');
                }

                Log::info('✅ Token de sesión obtenido exitosamente');
                return $responseData['value'];
            } catch (Exception $e) {
                Log::error('❌ Error al obtener token BNC: ' . $e->getMessage());
                return null;
            }
        });
    }

    public function processSessionToken(string $encryptedToken): ?string
    {
        try {
            $wk = $this->dataCypher->decryptWithKey($encryptedToken, $this->masterKey);
            $wkArray = json_decode($wk, true);

            $workingKey = $wkArray['WorkingKey'] ?? null;
            if (!$workingKey) {
                throw new Exception('WorkingKey no encontrado en la respuesta');
            }

            $this->setWorkingKey($workingKey);
            Log::info('✅ WorkingKey procesado exitosamente');
            return $workingKey;
        } catch (Exception $e) {
            Log::error('❌ Error en processSessionToken: ' . $e->getMessage());
            return null;
        }
    }

    private function ensureWorkingKey(): ?string
    {
        $workingKey = $this->getWorkingKey();
        if ($workingKey) {
            return $workingKey;
        }

        Log::info('🔄 No hay WorkingKey en caché, obteniendo nuevo...');
        $encryptedToken = $this->getSessionToken();
        if (!$encryptedToken) {
            Log::error('❌ No se pudo obtener session token');
            return null;
        }

        return $this->processSessionToken($encryptedToken);
    }

    /**
     * ==================== MÉTODOS PARA DÉBITO INMEDIATO ====================
     */

    /**
     * Helper para sanitizar datos sensibles
     */
    private function sanitizeSensitiveData(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (in_array($key, ['DebtorAccount', 'CardNumber', 'CVV', 'CardPIN', 'Token'])) {
                $value = (string)$value;
                $sanitized[$key] = strlen($value) > 8
                    ? substr($value, 0, 4) . '****' . substr($value, -4)
                    : '****';
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeSensitiveData($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }

    /**
     * Helper para extraer ID de transacción del mensaje
     */
    private function extractTransactionId(string $message): ?string
    {
        if (preg_match('/:\s*(\d+)/', $message, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * PASO 1: SOLICITAR DÉBITO (envía SMS)
     * Endpoint: /api/SIMF/DebitTokenRequest
     */
    public function solicitarDebito(array $data): ?array
    {
        Log::info('🔵 BNC - SOLICITAR DÉBITO - INICIO', [
            'data' => $this->sanitizeSensitiveData($data)
        ]);

        try {
            // ✅ LOG 1: URL que se va a usar
            Log::info('📍 [1] URL DE DÉBITO EN PRODUCCIÓN', [
                'debitTokenRequestUrl' => $this->debitTokenRequestUrl,
                'environment' => app()->environment()
            ]);

            // ✅ LOG 2: WorkingKey antes de obtener
            Log::info('🔑 [2] OBTENIENDO WORKING KEY', [
                'has_existing_working_key' => !empty($this->getWorkingKey())
            ]);

            $workingKey = $this->ensureWorkingKey();

            // ✅ LOG 3: WorkingKey obtenido
            Log::info('🔑 [3] WORKING KEY OBTENIDO', [
                'has_working_key' => !empty($workingKey),
                'working_key_length' => strlen($workingKey ?? ''),
                'working_key_first_10' => substr($workingKey ?? '', 0, 10)
            ]);

            if (!$workingKey) {
                throw new Exception('No se pudo obtener WorkingKey');
            }

            // ✅ LOG 4: Datos recibidos
            Log::info('📦 [4] DATOS RECIBIDOS', [
                'Amount' => $data['Amount'] ?? 'missing',
                'DebtorAccount' => isset($data['DebtorAccount']) ? substr($data['DebtorAccount'], 0, 4) . '****' : 'missing',
                'DebtorAccountType' => $data['DebtorAccountType'] ?? $data['DebtorAccType'] ?? 'missing',
                'DebtorBank' => $data['DebtorBank'] ?? 'missing',
                'DebtorID' => $data['DebtorID'] ?? 'missing'
            ]);

            $debtorAccType = $data['DebtorAccountType'] ?? $data['DebtorAccType'] ?? null;

            if (!$debtorAccType) {
                throw new Exception('No se encontró DebtorAccountType o DebtorAccType');
            }

            if ($debtorAccType === 'TLF') {
                $debtorAccType = 'CELE';
                Log::info('📱 Detectado débito a teléfono, usando DebtorAccountType: CELE');
            }

            // ✅ LOG 5: Payload antes de encriptar
            $payload = [
                "Amount" => (float)$data['Amount'],
                "DebtorAccount" => $data['DebtorAccount'],
                "DebtorAccountType" => $debtorAccType,
                "DebtorBank" => $data['DebtorBank'],
                "DebtorID" => $data['DebtorID']
            ];

            Log::info('📦 [5] PAYLOAD A ENCRIPTAR', [
                'payload' => $payload,
                'payload_json' => json_encode($payload)
            ]);

            $jsonData = json_encode($payload);
            $encryptedValue = $this->dataCypher->encryptWithKey($jsonData, $workingKey);
            $validationHash = $this->dataCypher->encryptSHA256($jsonData);

            // ✅ LOG 6: Datos encriptados
            Log::info('🔐 [6] DATOS ENCRIPTADOS', [
                'jsonData_length' => strlen($jsonData),
                'encryptedValue_length' => strlen($encryptedValue),
                'encryptedValue_preview' => substr($encryptedValue, 0, 20) . '...',
                'validationHash' => $validationHash
            ]);

            $solicitud = [
                "ClientGUID" => $this->clientGuid,
                "value" => $encryptedValue,
                "Validation" => $validationHash,
                "Reference" => $this->generateDailyReference(),
                "swTestOperation" => false
            ];

            // ✅ LOG 7: Wrapper a enviar
            Log::info('📤 [7] ENVIANDO SOLICITUD AL BNC', [
                'url' => $this->debitTokenRequestUrl,
                'method' => 'POST',
                'wrapper' => [
                    'ClientGUID' => $solicitud['ClientGUID'],
                    'value_length' => strlen($solicitud['value']),
                    'validation' => substr($solicitud['Validation'], 0, 10) . '...',
                    'Reference' => $solicitud['Reference'],
                    'swTestOperation' => $solicitud['swTestOperation']
                ]
            ]);

            $startTime = microtime(true);

            $response = Http::timeout(30)
                ->retry(2, 100)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($this->debitTokenRequestUrl, $solicitud);

            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            // ✅ LOG 8: Respuesta del BNC
            Log::info('📥 [8] RESPUESTA DEL BNC', [
                'status' => $response->status(),
                'execution_time_ms' => $executionTime,
                'body' => $response->body(),
                'successful' => $response->successful()
            ]);

            if (!$response->successful()) {
                Log::error('❌ [9] RESPUESTA NO EXITOSA', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                throw new Exception("Error HTTP: " . $response->status() . " - " . $response->body());
            }

            $responseData = $response->json();

            // ✅ LOG 9: Respuesta parseada
            Log::info('📊 [9] RESPUESTA PARSEADA', [
                'has_status' => isset($responseData['status']),
                'status_value' => $responseData['status'] ?? null,
                'has_validation' => isset($responseData['validation']),
                'has_message' => isset($responseData['message'])
            ]);

            if (isset($responseData['status']) && $responseData['status'] === 'OK') {
                Log::info('✅ [10] SOLICITUD EXITOSA', [
                    'requestId' => $responseData['validation'] ?? null,
                    'message' => $responseData['message'] ?? null
                ]);

                return [
                    'success' => true,
                    'status' => 'OK',
                    'Status' => 'OK',
                    'requestId' => $responseData['validation'] ?? null,
                    'RequestId' => $responseData['validation'] ?? null,
                    'IdSolicitud' => $responseData['validation'] ?? null,
                    'message' => $responseData['message'] ?? 'Código SMS enviado',
                    'validation' => $responseData['validation'] ?? null,
                    'value' => $responseData['value'] ?? null,
                    'data' => [
                        'requestId' => $responseData['validation'] ?? null,
                        'message' => $responseData['message'] ?? null
                    ]
                ];
            }

            Log::warning('⚠️ [11] RESPUESTA NO OK DEL BANCO', [
                'response' => $responseData
            ]);

            return [
                'success' => false,
                'status' => 'ERROR',
                'Status' => 'ERROR',
                'message' => $responseData['message'] ?? 'Error en la solicitud',
                'data' => $responseData
            ];
        } catch (Exception $e) {
            Log::error('❌ [12] ERROR EN solicitarDebito:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return [
                'success' => false,
                'status' => 'ERROR',
                'Status' => 'ERROR',
                'message' => $e->getMessage()
            ];
        }
    }
    /**
     * PASO 2: EMITIR DÉBITO (confirma con código SMS)
     */
    public function emitirDebito(array $data): ?array
    {
        Log::info('🔵 BNC - EMITIR DÉBITO', [
            'data' => $this->sanitizeSensitiveData($data)
        ]);

        try {
            $workingKey = $this->ensureWorkingKey();
            if (!$workingKey) {
                throw new Exception('No se pudo obtener WorkingKey');
            }

            // Determinar el tipo de cuenta
            $debtorAccType = $data['DebtorAccType'];
            $debtorAccount = $data['DebtorAccount'];

            if ($debtorAccType === 'TLF') {
                $debtorAccType = 'CELE';
                // Formatear número de teléfono
                $debtorAccount = preg_replace('/[^0-9]/', '', $debtorAccount);
                if (strlen($debtorAccount) === 10 && substr($debtorAccount, 0, 1) === '0') {
                    $debtorAccount = '58' . substr($debtorAccount, 1);
                }
            }

            // ✅ CORREGIDO: Payload exacto que espera el API
            $payload = [
                "DebtorBank" => $data['DebtorBank'],
                "DebtorAccount" => $debtorAccount,
                "DebtorAccType" => $debtorAccType,
                "Concept" => $data['Concept'],
                "AddtlInf" => $data['AddtlInf'], // Código SMS
                "DebtorID" => $data['DebtorID'],
                "Amount" => (float)$data['Amount'],
                "DebtorName" => $data['DebtorName'],
                "ChildClientID" => $data['ChildClientID'] ?? "",
                "BranchID" => $data['BranchID'] ?? ""
            ];

            Log::info('📦 Payload a encriptar:', [
                'payload' => [
                    'DebtorBank' => $payload['DebtorBank'],
                    'DebtorAccount' => $this->maskAccountNumber($payload['DebtorAccount']),
                    'DebtorAccType' => $payload['DebtorAccType'],
                    'Concept' => $payload['Concept'],
                    'AddtlInf' => '****' . substr($payload['AddtlInf'], -4),
                    'DebtorID' => $payload['DebtorID'],
                    'Amount' => $payload['Amount'],
                    'DebtorName' => $payload['DebtorName']
                ]
            ]);

            // Encriptar payload
            $jsonData = json_encode($payload);
            $encryptedValue = $this->dataCypher->encryptWithKey($jsonData, $workingKey);
            $validationHash = $this->dataCypher->encryptSHA256($jsonData);

            // ✅ CORREGIDO: El requestId debe ir como Reference
            $solicitud = [
                "ClientGUID" => $this->clientGuid,
                "value" => $encryptedValue,
                "Validation" => $validationHash,
                "Reference" => $data['requestId'], // ✅ Usar el requestId de solicitarDebito
                "swTestOperation" => false // Cambiar a false en producción
            ];

            Log::info('📤 Enviando solicitud al BNC:', [
                'url' => $this->debitBeginnerUrl,
                'Reference' => $solicitud['Reference']
            ]);

            $response = Http::timeout(30)
                ->retry(2, 100)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($this->debitBeginnerUrl, $solicitud);

            Log::info('📥 Respuesta del BNC:', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if (!$response->successful() && $response->status() !== 202) {
                throw new Exception("Error HTTP: " . $response->status());
            }

            $responseData = $response->json();

            // ✅ Devolver la respuesta en el formato esperado por el controlador
            return $responseData;
        } catch (\Exception $e) {
            Log::error('❌ Error en emitirDebito:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'status' => 'ERROR',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Helper para enmascarar números de cuenta
     */
    private function maskAccountNumber(string $number): string
    {
        if (strlen($number) <= 8) {
            return '****';
        }
        return substr($number, 0, 4) . '****' . substr($number, -4);
    }

    /**
     * PASO 3: REENVIAR CÓDIGO SMS
     * Endpoint: /api/debito/reenviar-sms
     */
    public function reenviarSms(array $data): ?array
    {
        Log::info('🔵 BNC - REENVIAR SMS', ['data' => $data]);

        try {
            $workingKey = $this->ensureWorkingKey();
            if (!$workingKey) {
                throw new Exception('No se pudo obtener WorkingKey');
            }

            $payload = [
                "requestId" => $data['requestId'],
                "DebtorID" => $data['DebtorID']
            ];

            $jsonData = json_encode($payload);
            $encryptedValue = $this->dataCypher->encryptWithKey($jsonData, $workingKey);
            $validationHash = $this->dataCypher->encryptSHA256($jsonData);

            $solicitud = [
                "ClientGUID" => $this->clientGuid,
                "value" => $encryptedValue,
                "Validation" => $validationHash,
                "Reference" => $this->generateDailyReference(),
                "swTestOperation" => false
            ];

            $response = Http::timeout(30)
                ->retry(2, 100)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($this->debitReenviarUrl, $solicitud);

            Log::info('📥 Respuesta reenviar SMS:', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if (!$response->successful()) {
                throw new Exception("Error HTTP: " . $response->status());
            }

            $responseData = $response->json();

            if (isset($responseData['status']) && $responseData['status'] === 'OK') {
                return [
                    'success' => true,
                    'status' => 'OK',
                    'Status' => 'OK',
                    'message' => $responseData['message'] ?? 'Código reenviado exitosamente',
                    'data' => [
                        'message' => $responseData['message'] ?? null,
                        'validation' => $responseData['validation'] ?? null
                    ]
                ];
            }

            return [
                'success' => false,
                'status' => 'ERROR',
                'Status' => 'ERROR',
                'message' => $responseData['message'] ?? 'Error al reenviar SMS'
            ];
        } catch (\Exception $e) {
            Log::error('❌ Error en reenviarSms:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'status' => 'ERROR',
                'Status' => 'ERROR',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * ==================== OTROS MÉTODOS EXISTENTES ====================
     */

    public function initiateC2PPayment(array $data): ?array
    {
        return $this->executeTransaction('C2P', $data, [
            'DebtorBankCode',
            'DebtorCellPhone',
            'DebtorID',
            'Amount',
            'Token',
            'Terminal'
        ], function ($data) {
            return [
                "DebtorBankCode" => (int)$data['DebtorBankCode'],
                "DebtorCellPhone" => $data['DebtorCellPhone'],
                "DebtorID" => $data['DebtorID'],
                "Amount" => (float)$data['Amount'],
                "Token" => $data['Token'],
                "Terminal" => $data['Terminal'],
                "ChildClientID" => $data['ChildClientID'] ?? "",
                "BranchID" => $data['BranchID'] ?? ""
            ];
        }, $this->c2pApiUrl);
    }

    public function processCardPayment(array $data): ?array
    {
        return $this->executeTransaction('VPOS', $data, [
            'TransactionIdentifier',
            'Amount',
            'idCardType',
            'CardNumber',
            'dtExpiration',
            'CardHolderName',
            'AccountType',
            'CVV',
            'CardPIN',
            'CardHolderID',
            'AffiliationNumber',
            'OperationRef'
        ], function ($data) {
            return [
                "TransactionIdentifier" => $data['TransactionIdentifier'],
                "Amount" => (float)$data['Amount'],
                "idCardType" => (int)$data['idCardType'],
                "CardNumber" => (string)$data['CardNumber'],
                "dtExpiration" => (int)$data['dtExpiration'],
                "CardHolderName" => $data['CardHolderName'],
                "AccountType" => (int)$data['AccountType'],
                "CVV" => (int)$data['CVV'],
                "CardPIN" => (int)$data['CardPIN'],
                "CardHolderID" => (int)$data['CardHolderID'],
                "AffiliationNumber" => (int)$data['AffiliationNumber'],
                "OperationRef" => $data['OperationRef'],
                "ChildClientID" => $data['ChildClientID'] ?? "",
                "BranchID" => $data['BranchID'] ?? ""
            ];
        }, $this->vposApiUrl);
    }

    public function getBanksFromBnc(): ?array
    {
        return $this->executeSimpleRequest('BANCOS', ["ClientGUID" => $this->clientGuid], $this->banksApiUrl);
    }

    public function getDailyRateFromBnc(): ?array
    {
        return $this->executeSimpleRequest('TASAS', ["ClientGUID" => $this->clientGuid], $this->ratesApiUrl);
    }

    /**
     * ==================== MÉTODOS AUXILIARES ====================
     */

    private function executeTransaction(string $operation, array $data, array $requiredFields, callable $dataMapper, string $url): ?array
    {
        try {
            $this->validateRequiredFields($data, $requiredFields, $operation);

            $workingKey = $this->ensureWorkingKey();
            if (!$workingKey) {
                throw new Exception("WorkingKey no disponible para $operation");
            }

            $transactionData = $dataMapper($data);
            $jsonData = json_encode($transactionData);

            if ($jsonData === false) {
                throw new Exception("Error al codificar JSON para $operation");
            }

            Log::info("📦 Iniciando $operation", [$operation => $transactionData]);

            return $this->sendEncryptedRequest($url, $jsonData, $workingKey, $operation);
        } catch (Exception $e) {
            Log::error("❌ Error en $operation: " . $e->getMessage());
            return [
                'success' => false,
                'Status' => 'ERROR',
                'Code' => 'EXCEPTION',
                'Message' => $e->getMessage()
            ];
        }
    }

    private function executeSimpleRequest(string $operation, array $requestData, string $url): ?array
    {
        try {
            $workingKey = $this->ensureWorkingKey();
            if (!$workingKey) {
                throw new Exception("WorkingKey no disponible para $operation");
            }

            $jsonData = json_encode($requestData);
            $response = $this->sendEncryptedRequest($url, $jsonData, $workingKey, $operation);

            if ($response && isset($response['status']) && $response['status'] === 'OK' && isset($response['value'])) {
                $decryptedData = $this->dataCypher->decryptWithKey($response['value'], $workingKey);
                if ($decryptedData) {
                    $parsedData = json_decode($decryptedData, true);
                    if (is_array($parsedData)) {
                        $response['data'] = $parsedData;
                    }
                }
            }

            return $response;
        } catch (Exception $e) {
            Log::error("❌ Error en $operation: " . $e->getMessage());
            throw new Exception("No se pudo completar la operación $operation: " . $e->getMessage());
        }
    }

    private function sendEncryptedRequest(string $url, string $jsonData, string $workingKey, string $operation): ?array
    {
        $encryptedValue = $this->dataCypher->encryptWithKey($jsonData, $workingKey);
        $validationHash = $this->dataCypher->encryptSHA256($jsonData);

        $solicitud = [
            "ClientGUID" => $this->clientGuid,
            "value" => $encryptedValue,
            "Validation" => $validationHash,
            "Reference" => $this->generateDailyReference(),
            "swTestOperation" => false
        ];

        $response = Http::timeout(30)
            ->retry(2, 100)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($url, $solicitud);

        if (!$this->isSuccessfulResponse($response)) {
            throw new Exception("Error HTTP {$response->status()} en $operation: " . $response->body());
        }

        $responseData = json_decode($response->body(), true);

        if (isset($responseData['value']) && isset($responseData['validation'])) {
            $processed = $this->processEncryptedResponse($responseData, $workingKey);
            return $processed['decrypted_response'] ?? $responseData;
        }

        return $responseData;
    }

    private function processEncryptedResponse(array $encryptedResponse, string $workingKey): ?array
    {
        try {
            $decryptedValue = $this->dataCypher->decryptWithKey($encryptedResponse['value'], $workingKey);

            if (!$decryptedValue) {
                return null;
            }

            $expectedValidation = $this->dataCypher->encryptSHA256($decryptedValue);
            if (!hash_equals($expectedValidation, $encryptedResponse['validation'])) {
                Log::warning('⚠️ Validation hash no coincide en respuesta encriptada');
            }

            $responseData = json_decode($decryptedValue, true);

            return [
                'success' => true,
                'decrypted_response' => $responseData
            ];
        } catch (Exception $e) {
            Log::error('❌ Error procesando respuesta encriptada: ' . $e->getMessage());
            return null;
        }
    }

    private function validateRequiredFields(array $data, array $requiredFields, string $operation): void
    {
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new Exception("Campo requerido faltante para $operation: $field");
            }
        }
    }

    private function isSuccessfulResponse($response): bool
    {
        return $response->successful() || $response->status() === 202;
    }

    private function generateDailyReference(): string
    {
        return 'APP_' . date('YmdHis') . '_' . substr(uniqid(), -6);
    }

    /**
     * ==================== MÉTODOS PÚBLICOS AUXILIARES ====================
     */

    public function getWorkingKey(): ?string
    {
        return Cache::get('bnc_working_key');
    }

    public function hasWorkingKey(): bool
    {
        return !empty($this->getWorkingKey());
    }

    public function setWorkingKey(string $workingKey): void
    {
        Cache::put('bnc_working_key', $workingKey, now()->addMinutes(55));
    }

    public function clearWorkingKey(): void
    {
        Cache::forget('bnc_working_key');
        Cache::forget('bnc_session_token');
    }

    public function verifyLegacyCompatibility(): bool
    {
        try {
            $testData = '{"ClientGUID":"test-guid"}';
            $testKey = 'test-master-key';

            $encrypted = $this->dataCypher->encryptWithKey($testData, $testKey);
            $decrypted = $this->dataCypher->decryptWithKey($encrypted, $testKey);

            return $testData === $decrypted;
        } catch (Exception $e) {
            return false;
        }
    }

    public function testFullAuthFlow(): array
    {
        try {
            $this->clearWorkingKey();

            $encryptedToken = $this->getSessionToken();
            if (!$encryptedToken) {
                throw new Exception('Fallo en getSessionToken()');
            }

            $workingKey = $this->processSessionToken($encryptedToken);
            if (!$workingKey) {
                throw new Exception('Fallo en processSessionToken()');
            }

            return [
                'success' => true,
                'encrypted_token_length' => strlen($encryptedToken),
                'working_key_length' => strlen($workingKey),
                'working_key_cached' => $this->hasWorkingKey()
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function checkAvailableServices(): array
    {
        $results = [];

        // 1. Probar autenticación
        $results['auth'] = $this->testFullAuthFlow();

        // 2. Probar obtener bancos (servicio básico)
        try {
            $banks = $this->getBanksFromBnc();
            $results['banks'] = isset($banks['data']) ? 'OK - ' . count($banks['data']) . ' bancos' : 'FAILED';
        } catch (Exception $e) {
            $results['banks'] = 'ERROR: ' . $e->getMessage();
        }

        // 3. Probar obtener tasa (servicio básico)
        try {
            $rate = $this->getDailyRateFromBnc();
            $results['rate'] = isset($rate['data']) ? 'OK' : 'FAILED';
        } catch (Exception $e) {
            $results['rate'] = 'ERROR: ' . $e->getMessage();
        }

        return $results;
    }
}
