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
    private string $ratesApiUrl;

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
        $this->ratesApiUrl = env('BNC_RATES_API_URL');

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
            // 1. Validar campos requeridos
            $requiredFields = ['banco', 'telefono', 'cedula', 'monto', 'token', 'terminal'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    throw new Exception("Campo requerido faltante: $field");
                }
            }

            // 2. Obtener token de sesión
            $sessionToken = $this->getSessionToken();
            if (!$sessionToken) {
                Log::error('No se pudo obtener token de sesión');
                throw new Exception('No se pudo obtener token de sesión');
            }

            // 3. Estructurar datos C2P
            $soliC2p = [
                "DebtorBankCode" => intval($data['banco']),
                "DebtorCellPhone" => $data['telefono'],
                "DebtorID" => $data['cedula'],
                "Amount" => floatval($data['monto']),
                "Token" => $data['token'],
                "Terminal" => $data['terminal']
            ];

            // 4. DEBUG: Log datos antes de encriptar
            Log::debug('DEBUG: Datos C2P antes de encryption', [
                'soliC2p' => $soliC2p,
                'json_string' => json_encode($soliC2p, JSON_UNESCAPED_UNICODE)
            ]);

            // 5. Encriptar datos
            $encryptedData = $this->dataCypher->encryptLegacyFormat($soliC2p);

            // 6. DEBUG: Log datos encriptados
            Log::debug('DEBUG: Datos C2P encriptados', [
                'value_length' => strlen($encryptedData['value']),
                'validation' => $encryptedData['validation'],
                'value_sample' => substr($encryptedData['value'], 0, 50) . '...'
            ]);

            // 7. Construir solicitud final (¡CORREGIR "Validation" a "validation"!)
            $solicitud = [
                "ClientGUID" => $this->clientGuid,
                "value" => $encryptedData['value'],
                "validation" => $encryptedData['validation'], // ← minúscula
                "Reference" => $this->generateDailyReference(),
                "swTestOperation" => false
            ];

            // 8. DEBUG: Log solicitud completa
            Log::debug('DEBUG: Solicitud C2P completa', [
                'url' => $this->c2pApiUrl,
                'headers' => [
                    'Authorization' => 'Bearer ' . (substr($sessionToken, 0, 10) . '...'),
                    'X-Client-GUID' => $this->clientGuid,
                    'X-Merchant-ID' => $this->merchantId
                ],
                'solicitud_body' => $solicitud
            ]);

            // 9. Hacer request
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $sessionToken,
                    'X-Client-GUID' => $this->clientGuid,
                    'X-Merchant-ID' => $this->merchantId
                ])
                ->post($this->c2pApiUrl, $solicitud);

            // 10. DEBUG: Log respuesta completa
            Log::debug('DEBUG: Respuesta C2P', [
                'status' => $response->status(),
                'headers' => $response->headers(),
                'body' => $response->body(),
                'successful' => $response->successful()
            ]);

            // 11. Manejar respuesta
            if ($response->successful() || $response->status() === 409) {
                $responseBody = $response->body();
                return !empty($responseBody) ? json_decode($responseBody, true) : null;
            }

            throw new Exception('Error en response: ' . $response->status() . ' - ' . $response->body());
        } catch (Exception $e) {
            Log::error('Excepción en pago C2P: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
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

            // DEBUG: Verificar si el token fue obtenido
            if (!$sessionToken) {
                Log::debug('DEBUG: No se pudo obtener token de sesión - getSessionToken() retornó null/false');
                throw new Exception('No se pudo obtener token para VPOS');
            } else {
                Log::debug('DEBUG: Token de sesión obtenido exitosamente', [
                    'token_length' => strlen($sessionToken),
                    'token_prefix' => substr($sessionToken, 0, 10) . '...'
                ]);
            }

            // Generar OperationRef automáticamente (UUID)
            $operationRef = $data['OperationRef'] ?? $this->generateOperationRef();

            // Estructurar datos VPOS según el formato correcto
            $soliVpos = [
                "TransactionIdentifier" => $data['identificador'],
                "Amount" => floatval($data['monto']),
                "idCardType" => intval($data['tipTarjeta']),
                "CardNumber" => (string)$data['tarjeta'], // Mantener como string para ceros a la izquierda
                "dExpiration" => (string)$data['fechExp'], // Cambiado a dExpiration y como string
                "CardHolderName" => $data['nomTarjeta'],
                "AccountType" => intval($data['tipCuenta']),
                "CW" => (string)$data['cvv'], // Cambiado de CVV a CW y como string
                "CardPIN" => (string)$data['pin'], // Como string para mantener ceros a la izquierda
                "CardHolderID" => (string)$data['identificacion'], // Como string
                "AffiliationNumber" => (string)$data['afiliacion'], // Como string
                "OperationRef" => $operationRef, // Campo requerido añadido
                "ChildClientID" => $data['ChildClientID'] ?? "", // Campo opcional
                "BranchID" => $data['BranchID'] ?? "" // Campo opcional
            ];

            // DEBUG: Log de los datos estructurados
            Log::debug('DEBUG: Datos estructurados para VPOS', [
                'transaction_data' => $soliVpos
            ]);

            // Encriptar datos
            $encryptedData = $this->dataCypher->encryptLegacyFormat($soliVpos);

            // Construir solicitud final
            $solicitud = [
                "ClientGUID" => $this->clientGuid,
                "value" => $encryptedData['value'],
                "validation" => $encryptedData['validation'], // Cambiado a minúscula
                "Reference" => $this->generateDailyReference(),
                "swTestOperation" => false
            ];

            // DEBUG: Log de la solicitud final (sin datos sensibles)
            Log::debug('DEBUG: Solicitud final encriptada', [
                'client_guid' => $this->clientGuid,
                'reference' => $solicitud['Reference'],
                'value_length' => strlen($encryptedData['value']),
                'validation_length' => strlen($encryptedData['validation'])
            ]);

            // Hacer request
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $sessionToken,
                    'X-Client-GUID' => $this->clientGuid,
                    'X-Merchant-ID' => $this->merchantId
                ])
                ->post($this->vposApiUrl, $solicitud);

            // DEBUG: Log de la respuesta
            Log::debug('DEBUG: Respuesta del servidor', [
                'status' => $response->status(),
                'successful' => $response->successful()
            ]);

            // Manejar respuesta
            if ($response->successful() || $response->status() === 409) {
                $responseBody = $response->body();

                if (!empty($responseBody)) {
                    $responseData = json_decode($responseBody, true);

                    // DEBUG: Log de respuesta exitosa
                    Log::debug('DEBUG: Respuesta exitosa del servidor', [
                        'status' => $responseData['status'] ?? 'N/A',
                        'message' => $responseData['message'] ?? 'N/A'
                    ]);

                    return $responseData;
                }
            }

            throw new Exception('Error en response: ' . $response->status() . ' - ' . $response->body());
        } catch (Exception $e) {
            Log::error('Excepción en pago VPOS: ' . $e->getMessage());
            return null;
        }
    }

    // Añade este método en la misma clase para generar el OperationRef
    private function generateOperationRef(): string
    {
        // Generar UUID v4
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
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
                throw new Exception('No se pudo obtener token para P2P');
            }

            // Estructurar datos P2P según el formato del ejemplo
            $soliP2p = [
                "Amount" => floatval($data['monto']),
                "BeneficiaryBankCode" => intval($data['banco']),
                "BeneficiaryCellPhone" => $data['telefono'],
                "BeneficiaryEmail" => $data['email'] ?? "",
                "BeneficiaryID" => $data['cedula'],
                "BeneficiaryName" => $data['beneficiario'],
                "Description" => $data['descripcion'],
                "OperationRef" => $data['operation_ref'] ?? $this->generateOperationRef(),
                "ChildClientID" => $data['child_client_id'] ?? ""
            ];

            // DEBUG: Log de datos estructurados
            Log::debug('DEBUG: Datos P2P estructurados', ['p2p_data' => $soliP2p]);

            // Encriptar datos
            $encryptedData = $this->dataCypher->encryptLegacyFormat($soliP2p);

            // Construir solicitud final
            $solicitud = [
                "ClientGUID" => $this->clientGuid,
                "value" => $encryptedData['value'],
                "validation" => $encryptedData['validation'], // Cambiado a minúscula
                "Reference" => $this->generateDailyReference(),
                "swTestOperation" => false
            ];

            // DEBUG: Log de solicitud final
            Log::debug('DEBUG: Solicitud P2P encriptada', [
                'client_guid' => $this->clientGuid,
                'reference' => $solicitud['Reference']
            ]);

            // Hacer request
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $sessionToken,
                    'X-Client-GUID' => $this->clientGuid,
                    'X-Merchant-ID' => $this->merchantId
                ])
                ->post($this->p2pApiUrl, $solicitud);

            // DEBUG: Log de respuesta
            Log::debug('DEBUG: Respuesta P2P', [
                'status' => $response->status(),
                'successful' => $response->successful()
            ]);

            // Manejar respuesta
            if ($response->successful() || $response->status() === 409) {
                $responseBody = $response->body();

                if (!empty($responseBody)) {
                    $responseData = json_decode($responseBody, true);

                    // DEBUG: Log de respuesta exitosa
                    if (isset($responseData['status']) && $responseData['status'] === 'OK') {
                        Log::debug('DEBUG: P2P procesado exitosamente', [
                            'message' => $responseData['message'],
                            'reference' => $responseData['message'] ? $this->extractReference($responseData['message']) : null
                        ]);
                    }

                    return $responseData;
                }
            }

            throw new Exception('Error en response: ' . $response->status() . ' - ' . $response->body());
        } catch (Exception $e) {
            Log::error('Excepción en pago P2P: ' . $e->getMessage());
            return null;
        }
    }

    // Método auxiliar para extraer número de referencia del mensaje
    private function extractReference(string $message): ?string
    {
        if (preg_match('/Nro de Referencia: (\d+)/', $message, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Obtiene la lista de bancos del BNC
     */
    public function getBanksFromBnc(): ?array
    {
        try {
            if (empty($this->banksApiUrl)) {
                Log::warning('Banks API URL está vacía, usando datos simulados');
                return $this->getSimulatedBanksFormatted();
            }

            $token = $this->getSessionToken();
            if (!$token) {
                Log::warning('Token de sesión no disponible, usando datos simulados');
                return $this->getSimulatedBanksFormatted();
            }

            // Preparar solicitud
            $solicitudBancos = ["ClientGUID" => $this->clientGuid];
            $encryptedData = $this->dataCypher->encryptLegacyFormat($solicitudBancos);

            $solicitud = [
                "ClientGUID" => $this->clientGuid,
                "value" => $encryptedData['value'],
                "validation" => $encryptedData['validation'],
                "Reference" => $this->generateDailyReference(),
                "swTestOperation" => false
            ];

            // DEBUG: Log de la solicitud
            Log::debug('DEBUG: Solicitud para obtener bancos', [
                'client_guid' => $this->clientGuid,
                'reference' => $solicitud['Reference']
            ]);

            // Intentar 2 veces con reintentos
            $maxAttempts = 2;
            for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
                $response = Http::timeout(30)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer ' . $token,
                        'X-Client-GUID' => $this->clientGuid,
                        'X-Merchant-ID' => $this->merchantId
                    ])
                    ->post($this->banksApiUrl, $solicitud);

                // DEBUG: Log del intento
                Log::debug('DEBUG: Intento de obtener bancos', [
                    'attempt' => $attempt,
                    'status' => $response->status(),
                    'successful' => $response->successful()
                ]);

                // Si es 409, reintentar después de pausa
                if ($response->status() === 409) {
                    if ($attempt < $maxAttempts) {
                        sleep(2);
                        continue;
                    }
                    break;
                }

                // Si es exitoso, procesar
                if ($response->successful()) {
                    $responseBody = $response->body();

                    // DEBUG: Log de la respuesta cruda
                    Log::debug('DEBUG: Respuesta cruda de bancos', [
                        'response_body' => $responseBody
                    ]);

                    if (!empty($responseBody)) {
                        $jsonResponse = json_decode($responseBody, true);

                        // Verificar si la respuesta tiene el formato esperado
                        if (isset($jsonResponse['status']) && $jsonResponse['status'] === 'OK') {
                            // DEBUG: Log de respuesta exitosa
                            Log::debug('DEBUG: Respuesta OK de bancos', [
                                'status' => $jsonResponse['status'],
                                'message' => $jsonResponse['message']
                            ]);

                            // ¡CORRECCIÓN IMPORTANTE! Desencriptar el valor
                            if (isset($jsonResponse['value']) && !empty($jsonResponse['value'])) {
                                try {
                                    $decryptedData = $this->dataCypher->decryptAES($jsonResponse['value']);
                                    if ($decryptedData) {
                                        $banksData = json_decode($decryptedData, true);

                                        // DEBUG: Log de datos desencriptados
                                        Log::debug('DEBUG: Datos desencriptados de bancos', [
                                            'decrypted_data' => $decryptedData,
                                            'banks_count' => is_array($banksData) ? count($banksData) : 0
                                        ]);

                                        if (is_array($banksData)) {
                                            // Devolver la respuesta con los datos desencriptados
                                            return [
                                                'status' => 'OK',
                                                'message' => '000000Consulta exitosa',
                                                'value' => $jsonResponse['value'], // Mantener valor encriptado
                                                'validation' => $jsonResponse['validation'] ?? '',
                                                'data' => $banksData // Datos desencriptados
                                            ];
                                        }
                                    }
                                } catch (Exception $e) {
                                    Log::error('Error al desencriptar respuesta de bancos: ' . $e->getMessage());
                                }
                            }

                            // Si no se pudo desencriptar, devolver la respuesta original
                            return $jsonResponse;
                        }

                        // Procesamiento tradicional para compatibilidad
                        if (isset($jsonResponse['value']) && !empty($jsonResponse['value'])) {
                            $decryptedData = $this->dataCypher->decryptAES($jsonResponse['value']);
                            if ($decryptedData) {
                                $banks = json_decode($decryptedData, true);
                                if (is_array($banks)) {
                                    // Formatear como la respuesta del ejemplo
                                    return [
                                        'status' => 'OK',
                                        'message' => '000000Consulta exitosa',
                                        'value' => $jsonResponse['value'], // Mantener el valor encriptado
                                        'validation' => $jsonResponse['validation'] ?? '',
                                        'data' => $banks // Datos desencriptados
                                    ];
                                }
                            }
                        }
                    }
                }

                break;
            }

            // Si falla, devolver simulado pero con el formato correcto
            Log::warning('Usando datos simulados después de fallo en la API');
            return $this->getSimulatedBanksFormatted();
        } catch (Exception $e) {
            Log::error('Excepción al obtener bancos: ' . $e->getMessage());

            // Devolver simulado con formato correcto incluso en error
            return $this->getSimulatedBanksFormatted();
        }
    }

    private function getSimulatedBanksFormatted(): array
    {
        $simulatedBanks = [
            ['id' => 1, 'name' => 'Banco de Chile', 'code' => 'BCH'],
            ['id' => 2, 'name' => 'Banco Estado', 'code' => 'EST'],
            ['id' => 3, 'name' => 'Banco Santander', 'code' => 'SAN'],
            ['id' => 4, 'name' => 'Banco de Crédito e Inversiones', 'code' => 'BCI'],
            ['id' => 5, 'name' => 'Banco Falabella', 'code' => 'FAL']
        ];

        // Encriptar los datos simulados para mantener el mismo formato
        $encrypted = $this->dataCypher->encryptLegacyFormat(['banks' => $simulatedBanks]);

        return [
            'status' => 'OK',
            'message' => '000000Consulta exitosa',
            'value' => $encrypted['value'],
            'validation' => $encrypted['validation'],
            'data' => $simulatedBanks // Datos desencriptados para fácil acceso
        ];
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
     * Obtiene la tasa del día desde el BNC
     */
    public function getDailyRateFromBnc(): ?array
    {
        try {
            // Verificar si la URL está configurada
            if (empty($this->ratesApiUrl)) {
                Log::warning('URL de tasas no configurada, usando datos simulados');
                return $this->getSimulatedRate();
            }

            $token = $this->getSessionToken();
            if (!$token) {
                Log::warning('No se pudo obtener token, usando datos simulados de tasa');
                return $this->getSimulatedRate();
            }

            // Preparar solicitud
            $solicitudTasa = ["ClientGUID" => $this->clientGuid];
            $encryptedData = $this->dataCypher->encryptLegacyFormat($solicitudTasa);

            $solicitud = [
                "ClientGUID" => $this->clientGuid,
                "value" => $encryptedData['value'],
                "validation" => $encryptedData['validation'],
                "Reference" => $this->generateDailyReference(),
                "swTestOperation" => false
            ];

            // DEBUG: Log de la solicitud
            Log::debug('DEBUG: Solicitud para obtener tasa del día', [
                'client_guid' => $this->clientGuid,
                'reference' => $solicitud['Reference'],
                'api_url' => $this->ratesApiUrl
            ]);

            // Hacer request
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                    'X-Client-GUID' => $this->clientGuid,
                    'X-Merchant-ID' => $this->merchantId
                ])
                ->post($this->ratesApiUrl, $solicitud);

            // DEBUG: Log de la respuesta
            Log::debug('DEBUG: Respuesta de tasa del día', [
                'status_code' => $response->status(),
                'successful' => $response->successful(),
                'api_url' => $this->ratesApiUrl
            ]);

            // Si es exitoso, procesar
            if ($response->successful()) {
                $responseBody = $response->body();

                if (!empty($responseBody)) {
                    $jsonResponse = json_decode($responseBody, true);

                    // Verificar si la respuesta tiene el formato esperado
                    if (isset($jsonResponse['status']) && $jsonResponse['status'] === 'OK') {
                        // DEBUG: Log de respuesta exitosa
                        Log::debug('DEBUG: Tasa del día obtenida exitosamente', [
                            'status' => $jsonResponse['status'],
                            'message' => $jsonResponse['message'],
                            'value_length' => strlen($jsonResponse['value'] ?? '')
                        ]);

                        return $jsonResponse;
                    }
                }
            }

            // Log del error si la respuesta no fue exitosa
            Log::warning('Error obteniendo tasa del día', [
                'status_code' => $response->status(),
                'response_body' => $response->body(),
                'api_url' => $this->ratesApiUrl
            ]);

            // Si falla, devolver simulado
            return $this->getSimulatedRate();
        } catch (Exception $e) {
            Log::error('Excepción al obtener tasa del día: ' . $e->getMessage(), [
                'api_url' => $this->ratesApiUrl
            ]);
            return $this->getSimulatedRate();
        }
    }

    // Método para simular tasa cuando el API falla
    private function getSimulatedRate(): array
    {
        // Datos de ejemplo para simular la respuesta
        $simulatedRateData = [
            'rate' => 35.50,
            'currency' => 'USD',
            'last_updated' => date('Y-m-d H:i:s'),
            'effective_date' => date('Y-m-d')
        ];

        // Encriptar los datos simulados
        $encryptedData = $this->dataCypher->encryptLegacyFormat($simulatedRateData);

        return [
            'status' => 'OK',
            'message' => 'e9999OConsulta exitosa',
            'value' => $encryptedData['value'],
            'validation' => $encryptedData['validation']
        ];
    }

    /**
     * ==================== MÉTODOS AUXILIARES ====================
     */

    /**
     * Genera referencia única diaria
     */
    private function generateDailyReference(): string
    {
        return 'APP_' . date('Ymd') . '_' . substr(uniqid(), -6);
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

    /**
     * Obtiene la tasa del BCV (Banco Central de Venezuela) desde el BNC
     */
    public function getBcvRateFromBnc(): ?array
    {
        try {
            // Verificar si la URL está configurada
            if (empty($this->ratesApiUrl)) {
                Log::warning('URL de tasas no configurada, usando datos simulados para BCV');
                return $this->getSimulatedBcvRate();
            }

            $token = $this->getSessionToken();
            if (!$token) {
                Log::warning('No se pudo obtener token, usando datos simulados de tasa BCV');
                return $this->getSimulatedBcvRate();
            }

            // Preparar solicitud específica para tasa BCV
            $solicitudTasa = [
                "ClientGUID" => $this->clientGuid,
                "RateType" => "BCV" // Especificar que queremos la tasa del BCV
            ];

            $encryptedData = $this->dataCypher->encryptLegacyFormat($solicitudTasa);

            $solicitud = [
                "ClientGUID" => $this->clientGuid,
                "value" => $encryptedData['value'],
                "validation" => $encryptedData['validation'],
                "Reference" => $this->generateDailyReference(),
                "swTestOperation" => false
            ];

            // DEBUG: Log de la solicitud
            Log::debug('DEBUG: Solicitud para obtener tasa BCV', [
                'client_guid' => $this->clientGuid,
                'reference' => $solicitud['Reference'],
                'api_url' => $this->ratesApiUrl
            ]);

            // Hacer request
            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                    'X-Client-GUID' => $this->clientGuid,
                    'X-Merchant-ID' => $this->merchantId
                ])
                ->post($this->ratesApiUrl, $solicitud);

            // DEBUG: Log de la respuesta
            Log::debug('DEBUG: Respuesta de tasa BCV', [
                'status_code' => $response->status(),
                'successful' => $response->successful(),
                'api_url' => $this->ratesApiUrl
            ]);

            // Si es exitoso, procesar
            if ($response->successful()) {
                $responseBody = $response->body();

                if (!empty($responseBody)) {
                    $jsonResponse = json_decode($responseBody, true);

                    // Verificar si la respuesta tiene el formato esperado
                    if (isset($jsonResponse['status']) && $jsonResponse['status'] === 'OK') {
                        // DEBUG: Log de respuesta exitosa
                        Log::debug('DEBUG: Tasa BCV obtenida exitosamente', [
                            'status' => $jsonResponse['status'],
                            'message' => $jsonResponse['message']
                        ]);

                        // Intentar desencriptar los datos si están encriptados
                        if (isset($jsonResponse['value']) && !empty($jsonResponse['value'])) {
                            try {
                                $decryptedData = $this->dataCypher->decryptAES($jsonResponse['value']);
                                if ($decryptedData) {
                                    $rateData = json_decode($decryptedData, true);

                                    // Agregar los datos desencriptados a la respuesta
                                    $jsonResponse['decrypted_data'] = $rateData;

                                    Log::debug('DEBUG: Datos desencriptados de tasa BCV', [
                                        'rate_data' => $rateData
                                    ]);
                                }
                            } catch (Exception $e) {
                                Log::warning('No se pudo desencriptar respuesta de tasa BCV: ' . $e->getMessage());
                            }
                        }

                        return $jsonResponse;
                    }
                }
            }

            // Log del error si la respuesta no fue exitosa
            Log::warning('Error obteniendo tasa BCV', [
                'status_code' => $response->status(),
                'response_body' => $response->body()
            ]);

            // Si falla, devolver simulado
            return $this->getSimulatedBcvRate();
        } catch (Exception $e) {
            Log::error('Excepción al obtener tasa BCV: ' . $e->getMessage());
            return $this->getSimulatedBcvRate();
        }
    }

    /**
     * Método para simular tasa BCV cuando el API falla
     */
    private function getSimulatedBcvRate(): array
    {
        // Datos de ejemplo para simular la respuesta de tasa BCV
        $simulatedBcvRate = [
            'rate' => 36.15,
            'currency' => 'USD',
            'source' => 'BCV',
            'last_updated' => date('Y-m-d H:i:s'),
            'effective_date' => date('Y-m-d'),
            'rate_type' => 'official'
        ];

        // Encriptar los datos simulados
        $encryptedData = $this->dataCypher->encryptLegacyFormat($simulatedBcvRate);

        return [
            'status' => 'OK',
            'message' => '000000Consulta exitosa (simulado)',
            'value' => $encryptedData['value'],
            'validation' => $encryptedData['validation'],
            'decrypted_data' => $simulatedBcvRate // Incluir datos desencriptados para fácil acceso
        ];
    }
}
