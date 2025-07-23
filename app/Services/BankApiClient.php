<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class BankApiClient
{
    protected $client;
    protected $baseUrl;
    protected $clientId;
    protected $clientSecret;
    protected $accessToken; // Para almacenar el token de acceso si usas OAuth2
    protected $tokenExpiresAt; // Para saber cuándo renovar el token

    public function __construct()
    {
        $this->baseUrl = config('services.platform_bank.api.base_url');
        $this->clientId = config('services.platform_bank.api.client_id');
        $this->clientSecret = config('services.platform_bank.api.client_secret');

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout'  => 30.0,
            // 'verify' => false, // Solo para desarrollo si tienes problemas con SSL, NO en producción
            // 'cert' => [storage_path('app/certs/your_cert.pem'), 'your_cert_password'], // Si tu banco requiere certificado
        ]);

        // Intentar obtener el token de acceso al inicializar
        $this->getAccessToken();
    }

    /**
     * Obtiene y almacena el token de acceso si es necesario (OAuth2).
     * Retorna true si se obtuvo un token válido, false en caso contrario.
     */
    protected function getAccessToken(): bool
    {
        // Si ya tenemos un token y no ha expirado, lo usamos
        if ($this->accessToken && $this->tokenExpiresAt && time() < $this->tokenExpiresAt) {
            return true;
        }

        try {
            // Este endpoint y los parámetros son EJEMPLOS para OAuth2 Client Credentials.
            // DEBES ADAPTARLOS a la documentación de autenticación de tu banco.
            $response = $this->client->post('/oauth/token', [
                'json' => [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'grant_type' => 'client_credentials',
                    // 'scope' => 'transfers payments', // Si tu banco usa scopes
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            $this->accessToken = $data['access_token'] ?? null;
            $expiresIn = $data['expires_in'] ?? 3600; // Por defecto 1 hora si no se especifica
            $this->tokenExpiresAt = time() + $expiresIn - 60; // 60 segundos antes para renovar proactivamente

            if ($this->accessToken) {
                Log::info("Token bancario obtenido con éxito.");
                return true;
            }

            Log::error("No se pudo obtener el token bancario: " . ($data['error_description'] ?? 'Error desconocido.'));
            return false;

        } catch (RequestException $e) {
            Log::error("Error al obtener token bancario: " . $e->getMessage() . " Response: " . ($e->hasResponse() ? $e->getResponse()->getBody()->getContents() : 'N/A'));
            return false;
        } catch (\Exception $e) {
            Log::error("Error inesperado al obtener token bancario: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Prepara y retorna los headers con el token de autenticación.
     */
    protected function getAuthHeaders(): array
    {
        if (!$this->accessToken && !$this->getAccessToken()) {
            throw new \Exception("No se pudo obtener un token de acceso bancario.");
        }
        return ['Authorization' => 'Bearer ' . $this->accessToken];
    }

    /**
     * NUEVO MÉTODO: Crea una solicitud de pago o genera instrucciones para el cliente.
     * Esto simula la interacción para que el cliente pague a tu cuenta principal.
     *
     * @param float $amount Monto que el cliente debe pagar.
     * @param string $paymentMethod Método de pago (pago_movil, transferencia_bancaria, qr).
     * @param string $transactionReference Código de referencia único para el pago.
     * @return array|null Detalles para que el cliente realice el pago, o null en caso de error.
     */
    public function createPaymentRequest(float $amount, string $paymentMethod, string $transactionReference): ?array
    {
        try {
            $headers = $this->getAuthHeaders();
            $platformMainAccount = config('services.platform_bank.main_account');

            // Este endpoint y la estructura del body son EJEMPLOS.
            // DEBES ADAPTARLOS a la documentación de tu banco para "solicitudes de pago" o "generación de QR/Pago Móvil".
            // Muchos bancos no tienen una API para "solicitar" un pago directamente al cliente,
            // sino para generar datos de pago (QR, Pago Móvil) o para verificar pagos recibidos.

            if ($paymentMethod === 'pago_movil' || $paymentMethod === 'transferencia_bancaria') {
                // Si el banco tiene una API para generar datos de Pago Móvil o instrucciones
                // Ejemplo: POST /payment-requests
                // $response = $this->client->post('/payment-requests', [
                //     'headers' => $headers,
                //     'json' => [
                //         'amount' => $amount,
                //         'currency' => 'VES',
                //         'concept' => 'Pago por servicio Rumbero Extremo',
                //         'reference_id' => $transactionReference,
                //         'destination_account' => $platformMainAccount['account_number'],
                //         // ... otros parámetros que pida el banco
                //     ],
                // ]);
                // $data = json_decode($response->getBody()->getContents(), true);
                // return $data; // Podría contener un QR dinámico, URL de pago, etc.

                // Por ahora, solo devolvemos las instrucciones estáticas configuradas
                return [
                    'monto_a_pagar' => $amount,
                    'metodo' => $paymentMethod,
                    'referencia_pago' => $transactionReference,
                    'datos_cuenta_plataforma' => [
                        'banco' => $platformMainAccount['bank_name'],
                        'cedula_rif' => $platformMainAccount['id_number'],
                        'telefono_pago_movil' => $platformMainAccount['phone_number'] ?? 'N/A', // Asegúrate de añadir esto en config
                        'numero_cuenta' => $platformMainAccount['account_number'],
                        'tipo_cuenta' => $platformMainAccount['account_type'],
                    ],
                ];
            } elseif ($paymentMethod === 'qr') {
                // Si el banco tiene una API para generar QR dinámicos
                // Ejemplo: POST /qr-payments
                // $response = $this->client->post('/qr-payments', [
                //     'headers' => $headers,
                //     'json' => [
                //         'amount' => $amount,
                //         'currency' => 'VES',
                //         'concept' => 'Pago por servicio Rumbero Extremo',
                //         'reference_id' => $transactionReference,
                //         'destination_account' => $platformMainAccount['account_number'],
                //         // ... otros parámetros
                //     ],
                // ]);
                // $data = json_decode($response->getBody()->getContents(), true);
                // return $data; // Podría contener 'qr_image_url' o 'qr_data'

                // Por ahora, solo devolvemos las instrucciones estáticas
                 return [
                    'monto_a_pagar' => $amount,
                    'metodo' => $paymentMethod,
                    'referencia_pago' => $transactionReference,
                    'datos_cuenta_plataforma' => [
                        'banco' => $platformMainAccount['bank_name'],
                        'cedula_rif' => $platformMainAccount['id_number'],
                        'numero_cuenta' => $platformMainAccount['account_number'],
                        'tipo_cuenta' => $platformMainAccount['account_type'],
                    ],
                    'qr_instructions' => 'Escanea este QR estático o usa los datos de cuenta.',
                    // 'qr_image_url' => 'https://tu_dominio.com/path/to/static_qr.png', // Si tienes un QR estático
                ];
            } else {
                throw new \Exception("Método de pago no soportado por la API bancaria.");
            }

        } catch (RequestException $e) {
            Log::error("Error al crear solicitud de pago bancaria: " . $e->getMessage() . " Response: " . ($e->hasResponse() ? $e->getResponse()->getBody()->getContents() : 'N/A'));
            return null;
        } catch (\Exception $e) {
            Log::error("Error inesperado en createPaymentRequest: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Inicia una transferencia bancaria saliente.
     * @param array $transferData Datos de la transferencia (origen, destino, monto, concepto)
     * @return array|null Respuesta de la API bancaria o null en caso de error
     */
    public function initiateTransfer(array $transferData): ?array
    {
        try {
            $headers = $this->getAuthHeaders();

            // Este endpoint y la estructura del body son EJEMPLOS.
            // DEBES ADAPTARLOS a la documentación de tu banco para transferencias.
            $response = $this->client->post('/transfers', [
                'headers' => $headers,
                'json' => [
                    'source_account' => $transferData['source_account_number'],
                    'destination_bank_code' => $transferData['destination_bank_code'],
                    'destination_account_number' => $transferData['destination_account_number'],
                    'destination_account_type' => $transferData['destination_account_type'],
                    'destination_id_number' => $transferData['destination_id_number'],
                    'destination_holder_name' => $transferData['destination_holder_name'],
                    'amount' => $transferData['amount'],
                    'currency' => 'VES',
                    'concept' => $transferData['concept'],
                    'reference_id' => $transferData['reference_id'],
                    // ... otros campos que pida el banco (ej. PIN transaccional, OTP)
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);

        } catch (RequestException $e) {
            Log::error("Error al iniciar transferencia bancaria: " . $e->getMessage() . " Response: " . ($e->hasResponse() ? $e->getResponse()->getBody()->getContents() : 'N/A'));
            return null;
        } catch (\Exception $e) {
            Log::error("Error inesperado en initiateTransfer: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Simula la verificación de una transferencia entrante.
     * ESTO ES ALTAMENTE ESPECÍFICO DEL BANCO Y MUY DIFÍCIL DE AUTOMATIZAR.
     * La mayoría de las veces, esto se haría con webhooks bancarios o conciliación.
     *
     * @param string $transactionReference La referencia que el usuario debe haber usado.
     * @param float $expectedAmount El monto que se espera recibir.
     * @return bool True si se verifica el pago, false en caso contrario.
     */
    public function verifyIncomingTransfer(string $transactionReference, float $expectedAmount): bool
    {
        try {
            $headers = $this->getAuthHeaders();
            $platformMainAccount = config('services.platform_bank.main_account');

            // Este es un placeholder. La lógica real sería muy compleja:
            // - Consultar un estado de cuenta a través de la API (si el banco lo permite).
            // - Buscar una transacción con la referencia y el monto.
            // - Comparar fechas, montos, etc.
            // Ejemplo: GET /account-statements?account={account}&reference={ref}&amount={amount}
            // $response = $this->client->get('/account-statements', [
            //     'headers' => $headers,
            //     'query' => [
            //         'account' => $platformMainAccount['account_number'],
            //         'reference' => $transactionReference,
            //         'amount' => $expectedAmount,
            //         'date_from' => now()->subHours(2)->format('Y-m-d H:i:s'), // Buscar en las últimas 2 horas
            //     ],
            // ]);
            // $data = json_decode($response->getBody()->getContents(), true);
            // return !empty($data['transactions']) && count($data['transactions']) > 0;

            Log::info("Simulando verificación de transferencia entrante para referencia: $transactionReference, monto: $expectedAmount");
            // Por ahora, siempre retorna true para la demostración
            return true;

        } catch (RequestException $e) {
            Log::error("Error al verificar transferencia entrante: " . $e->getMessage() . " Response: " . ($e->hasResponse() ? $e->getResponse()->getBody()->getContents() : 'N/A'));
            return false;
        } catch (\Exception $e) {
            Log::error("Error inesperado en verifyIncomingTransfer: " . $e->getMessage());
            return false;
        }
    }
}