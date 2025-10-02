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
    private string $p2pApiUrl;
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
        $this->p2pApiUrl = env('BNC_P2P_API_URL');
        $this->banksApiUrl = env('BNC_BANKS_API_URL');
        $this->ratesApiUrl = env('BNC_RATES_API_URL');

        $this->dataCypher = new DataCypher($this->masterKey);
    }

    /**
     * ==================== MÉTODOS PRINCIPALES OPTIMIZADOS ====================
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

                Log::info('BNC Session Token obtenido exitosamente');
                return $responseData['value'];

            } catch (Exception $e) {
                Log::error('Error al obtener token BNC: ' . $e->getMessage());
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
            Log::info('WorkingKey procesado exitosamente');
            return $workingKey;

        } catch (Exception $e) {
            Log::error('Error en processSessionToken: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * ==================== MÉTODOS DE TRANSACCIÓN OPTIMIZADOS ====================
     */

    public function initiateC2PPayment(array $data): ?array
    {
        return $this->executeTransaction('C2P', $data, [
            'DebtorBankCode', 'DebtorCellPhone', 'DebtorID', 'Amount', 'Token', 'Terminal'
        ], function($data) {
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
            'TransactionIdentifier', 'Amount', 'idCardType', 'CardNumber', 'dtExpiration',
            'CardHolderName', 'AccountType', 'CVV', 'CardPIN', 'CardHolderID', 'AffiliationNumber', 'OperationRef'
        ], function($data) {
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

    public function initiateP2PPayment(array $data): ?array
    {
        return $this->executeTransaction('P2P', $data, [
            'Amount', 'BeneficiaryBankCode', 'BeneficiaryCellPhone', 'BeneficiaryID',
            'BeneficiaryName', 'Description'
        ], function($data) {
            return [
                "Amount" => (float)$data['Amount'],
                "BeneficiaryBankCode" => (int)$data['BeneficiaryBankCode'],
                "BeneficiaryCellPhone" => $data['BeneficiaryCellPhone'],
                "BeneficiaryEmail" => $data['BeneficiaryEmail'] ?? "",
                "BeneficiaryID" => $data['BeneficiaryID'],
                "BeneficiaryName" => $data['BeneficiaryName'],
                "Description" => $data['Description'],
                "ChildClientID" => $data['ChildClientID'] ?? "",
                "BranchID" => $data['BranchID'] ?? ""
            ];
        }, $this->p2pApiUrl);
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
     * ==================== MÉTODOS AUXILIARES OPTIMIZADOS ====================
     */

    private function ensureWorkingKey(): ?string
    {
        $workingKey = $this->getWorkingKey();
        if ($workingKey) {
            return $workingKey;
        }

        $encryptedToken = $this->getSessionToken();
        return $encryptedToken ? $this->processSessionToken($encryptedToken) : null;
    }

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

            Log::info("Iniciando $operation", [$operation => $transactionData]);

            return $this->sendEncryptedRequest($url, $jsonData, $workingKey, $operation);

        } catch (Exception $e) {
            Log::error("Error en $operation: " . $e->getMessage());
            return [
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
            Log::error("Error en $operation: " . $e->getMessage());
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
            throw new Exception("Error HTTP {$response->status()} en $operation");
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

            // Verificar integridad
            $expectedValidation = $this->dataCypher->encryptSHA256($decryptedValue);
            if (!hash_equals($expectedValidation, $encryptedResponse['validation'])) {
                Log::warning('Validation hash no coincide en respuesta encriptada');
            }

            $responseData = json_decode($decryptedValue, true);
            
            return [
                'success' => true,
                'decrypted_response' => $responseData
            ];

        } catch (Exception $e) {
            Log::error('Error procesando respuesta encriptada: ' . $e->getMessage());
            return null;
        }
    }

    private function validateRequiredFields(array $data, array $requiredFields, string $operation): void
    {
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
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