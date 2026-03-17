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
        $this->authApiUrl = env('BNC_AUTH_API_URL');
        $this->clientGuid = env('BNC_CLIENT_GUID');
        $this->masterKey = env('BNC_MASTER_KEY');
        $this->merchantId = env('BNC_MERCHANT_ID');
        $this->c2pApiUrl = env('BNC_C2P_API_URL');
        $this->vposApiUrl = env('BNC_VPOS_API_URL');
        $this->validationApiUrl = env('BNC_P2P_API_URL');
        $this->banksApiUrl = env('BNC_BANKS_API_URL');
        $this->ratesApiUrl = env('BNC_RATES_API_URL');
        $this->debitTokenRequestUrl = env('BNC_DEBITO_SOLICITAR_URL');
        $this->debitBeginnerUrl = env('BNC_DEBITO_EMITIR_URL');
        $this->debitReenviarUrl = env('BNC_DEBITO_REENVIAR_URL');

        $this->dataCypher = new DataCypher($this->masterKey);

        Log::info('🔧 BNC Service inicializado', [
            'auth_url' => $this->authApiUrl,
            'debito_solicitar' => $this->debitTokenRequestUrl,
            'debito_emitir' => $this->debitBeginnerUrl,
            'has_client_guid' => !empty($this->clientGuid),
            'has_master_key' => !empty($this->masterKey)
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
        Log::info('🔵 BNC - SOLICITAR DÉBITO', [
            'data' => $this->sanitizeSensitiveData($data)
        ]);

        try {
            // Asegurar working key
            $workingKey = $this->ensureWorkingKey();
            if (!$workingKey) {
                throw new Exception('No se pudo obtener WorkingKey');
            }

            // Determinar el tipo de cuenta correcto
            $debtorAccountType = $data['DebtorAccountType'];
            if ($debtorAccountType === 'TLF') {
                $debtorAccountType = 'CELE';
                Log::info('📱 Detectado débito a teléfono, usando DebtorAccountType: CELE');
            }

            // Preparar payload exacto que pide el banco
            $payload = [
                "Amount" => (float)$data['Amount'],
                "DebtorAccount" => $data['DebtorAccount'],
                "DebtorAccountType" => $debtorAccountType,
                "DebtorBank" => $data['DebtorBank'],
                "DebtorID" => $data['DebtorID']
            ];

            Log::info('📦 Payload a encriptar:', ['payload' => $payload]);

            // Encriptar payload
            $jsonData = json_encode($payload);
            $encryptedValue = $this->dataCypher->encryptWithKey($jsonData, $workingKey);
            $validationHash = $this->dataCypher->encryptSHA256($jsonData);

            // Construir wrapper
            $solicitud = [
                "ClientGUID" => $this->clientGuid,
                "value" => $encryptedValue,
                "Validation" => $validationHash,
                "Reference" => $this->generateDailyReference(),
                "swTestOperation" => false
            ];

            Log::info('📤 Enviando solicitud al BNC:', [
                'url' => $this->debitTokenRequestUrl,
                'wrapper' => [
                    'ClientGUID' => $solicitud['ClientGUID'],
                    'value_length' => strlen($solicitud['value']),
                    'validation' => substr($solicitud['Validation'], 0, 10) . '...',
                    'Reference' => $solicitud['Reference']
                ]
            ]);

            // Enviar request
            $response = Http::timeout(30)
                ->retry(2, 100)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($this->debitTokenRequestUrl, $solicitud);

            Log::info('📥 Respuesta del BNC:', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if (!$response->successful()) {
                throw new Exception("Error HTTP: " . $response->status() . " - " . $response->body());
            }

            $responseData = $response->json();

            // Procesar respuesta (viene en texto plano según ejemplos)
            if (isset($responseData['status']) && $responseData['status'] === 'OK') {
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

            return [
                'success' => false,
                'status' => 'ERROR',
                'Status' => 'ERROR',
                'message' => $responseData['message'] ?? 'Error en la solicitud',
                'data' => $responseData
            ];
        } catch (\Exception $e) {
            Log::error('❌ Error en solicitarDebito:', [
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
     * PASO 2: EMITIR DÉBITO (confirma con código SMS)
     * Endpoint: /api/SIMF/DebitBeginner
     */
    /**
     * PASO 2: EMITIR DÉBITO (confirma con código SMS)
     */
    public function emitirDebito(array $data): ?array
    {
        Log::info('🔵 BNC - EMITIR DÉBITO', [
            'data' => $this->sanitizeSensitiveData($data)
        ]);

        try {
            // Asegurar working key
            $workingKey = $this->ensureWorkingKey();
            if (!$workingKey) {
                throw new Exception('No se pudo obtener WorkingKey');
            }

            // Determinar el tipo de cuenta correcto y formatear número si es teléfono
            $debtorAccType = $data['DebtorAccType'];
            $debtorAccount = $data['DebtorAccount'];

            if ($debtorAccType === 'TLF' || $debtorAccType === 'CELE') {
                Log::info('📱 Detectado débito a teléfono, usando DebtorAccType: CELE');

                // Formatear número igual que en solicitar
                $debtorAccount = preg_replace('/[^0-9]/', '', $debtorAccount);
                if (str_starts_with($debtorAccount, '0')) {
                    $debtorAccount = '58' . substr($debtorAccount, 1);
                }

                $debtorAccType = 'CELE';
            }

            // Preparar payload EXACTO que pide el banco
            $payload = [
                "DebtorBank" => $data['DebtorBank'],
                "DebtorAccount" => $debtorAccount,
                "DebtorAccType" => $debtorAccType,
                "Concept" => $data['Concept'],
                "AddtlInf" => $data['AddtlInf'], // Código SMS de 8 dígitos
                "DebtorID" => $data['DebtorID'],
                "Amount" => (float)$data['Amount'],
                "DebtorName" => $data['DebtorName'],
                "ChildClientID" => $data['ChildClientID'] ?? "",
                "BranchID" => $data['BranchID'] ?? ""
            ];

            // NOTA: El requestId NO va en el payload, va en el wrapper como Reference
            // Pero si el banco lo requiere en el payload, habría que agregarlo aquí

            Log::info('📦 Payload a encriptar:', [
                'payload' => [
                    'DebtorBank' => $payload['DebtorBank'],
                    'DebtorAccount' => $this->maskAccountNumber($payload['DebtorAccount']),
                    'DebtorAccType' => $payload['DebtorAccType'],
                    'Concept' => $payload['Concept'],
                    'AddtlInf' => '****' . substr($payload['AddtlInf'], -4),
                    'DebtorID' => $payload['DebtorID'],
                    'Amount' => $payload['Amount'],
                    'DebtorName' => $payload['DebtorName'],
                    'ChildClientID' => $payload['ChildClientID'],
                    'BranchID' => $payload['BranchID']
                ]
            ]);

            // Encriptar payload
            $jsonData = json_encode($payload);
            $encryptedValue = $this->dataCypher->encryptWithKey($jsonData, $workingKey);
            $validationHash = $this->dataCypher->encryptSHA256($jsonData);

            // Construir wrapper - ¡INCLUIR EL REQUESTID COMO REFERENCE!
            $solicitud = [
                "ClientGUID" => $this->clientGuid,
                "value" => $encryptedValue,
                "Validation" => $validationHash,
                "Reference" => $data['requestId'] ?? $this->generateDailyReference(), // Usar el requestId de solicitar
                "swTestOperation" => true // ¡CAMBIAR A TRUE PARA DESARROLLO!
            ];

            Log::info('📤 Enviando solicitud al BNC:', [
                'url' => $this->debitBeginnerUrl,
                'wrapper' => [
                    'ClientGUID' => $solicitud['ClientGUID'],
                    'value_length' => strlen($solicitud['value']),
                    'validation' => substr($solicitud['Validation'], 0, 10) . '...',
                    'Reference' => $solicitud['Reference'],
                    'swTestOperation' => $solicitud['swTestOperation']
                ]
            ]);

            // Enviar request
            $response = Http::timeout(30)
                ->retry(2, 100)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($this->debitBeginnerUrl, $solicitud);

            Log::info('📥 Respuesta del BNC:', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if (!$response->successful()) {
                $responseData = $response->json();
                throw new Exception("Error HTTP {$response->status()}: " . json_encode($responseData));
            }

            $responseData = $response->json();

            // Procesar respuesta exitosa
            if (isset($responseData['status']) && $responseData['status'] === 'OK') {
                // Extraer transactionId del mensaje
                $transactionId = null;
                if (isset($responseData['message'])) {
                    if (preg_match('/:\s*(\d+)/', $responseData['message'], $matches)) {
                        $transactionId = $matches[1];
                    }
                }

                return [
                    'success' => true,
                    'status' => 'OK',
                    'Status' => 'OK',
                    'TransactionId' => $transactionId,
                    'IdTransaccion' => $transactionId,
                    'Reference' => $responseData['validation'] ?? null,
                    'Referencia' => $responseData['validation'] ?? null,
                    'message' => $responseData['message'] ?? 'Débito procesado exitosamente',
                    'validation' => $responseData['validation'] ?? null,
                    'value' => $responseData['value'] ?? null,
                    'data' => [
                        'transactionId' => $transactionId,
                        'reference' => $responseData['validation'] ?? null,
                        'message' => $responseData['message'] ?? null
                    ]
                ];
            }

            return [
                'success' => false,
                'status' => 'ERROR',
                'Status' => 'ERROR',
                'message' => $responseData['message'] ?? 'Error al emitir débito',
                'data' => $responseData
            ];
        } catch (\Exception $e) {
            Log::error('❌ Error en emitirDebito:', [
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
}
