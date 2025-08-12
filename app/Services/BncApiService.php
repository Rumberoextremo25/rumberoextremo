<?php

namespace App\Services;

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
    private string $banksApiUrl;

    // URL y configuración para la API de tasas del BCV
    private string $bcvRatesApiUrl;
    private int $bcvCacheDuration;

    public function __construct()
    {
        // Carga las configuraciones desde config/bnc.php (que a su vez las toma de .env)
        $this->authApiUrl = config('bnc.auth_api_url');
        $this->clientGuid = config('bnc.client_guid');
        $this->masterKey = config('bnc.master_key');
        $this->merchantId = config('bnc.merchant_id');
        $this->c2pApiUrl = config('bnc.c2p_api_url');
        $this->vposApiUrl = config('bnc.vpos_api_url');
        $this->banksApiUrl = config('bnc.banks_api_url');

        // Carga las configuraciones del BCV desde config/bnc.php también (o crea config/bcv.php si prefieres separar)
        $this->bcvRatesApiUrl = config('bnc.rates_api_url');
        $this->bcvCacheDuration = config('bnc.cache_duration_minutes');
    }

    /**
     * Obtiene y devuelve el token de sesión de la API del BNC.
     * El token se almacena en caché para evitar peticiones repetidas.
     *
     * @return string|null El token de sesión si se obtiene con éxito, o null en caso de error.
     */
    public function getSessionToken(): ?string
    {
        return Cache::remember('bnc_session_token', now()->addMinutes(59), function () { // Cache por 59 minutos
            try {
                $response = Http::post($this->authApiUrl, [
                    'ClientGUID' => $this->clientGuid,
                    'MasterKey' => $this->masterKey,
                ]);

                if ($response->successful()) {
                    // *** IMPORTANTE: Cambia 'data.token' a 'value' ***
                    $token = $response->json('value');

                    if ($token) {
                        Log::info('BNC Session Token obtenido exitosamente.', ['token' => substr($token, 0, 10) . '...']);
                        return $token;
                    } else {
                        Log::error('Fallo al extraer el token de la respuesta de la API BNC. El campo "value" no se encontró o estaba vacío.', ['response' => $response->json()]);
                        return null;
                    }
                } else {
                    // Aquí ya estás validando la conexión/respuesta
                    Log::error('Fallo en la conexión o la API de autenticación BNC devolvió un error.', [
                        'status' => $response->status(),
                        'response' => $response->json(),
                    ]);
                    return null;
                }
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                // Error específico de conexión (ej. host no encontrado, timeout)
                Log::error('Error de conexión a la API de autenticación BNC: ' . $e->getMessage());
                return null;
            } catch (\Exception $e) {
                // Otros errores inesperados
                Log::error('Excepción inesperada al obtener el token de sesión del BNC: ' . $e->getMessage());
                return null;
            }
        });
    }

    /**
     * Inicia un pago C2P (Pago Móvil) a través de la API del BNC.
     *
     * @param array $data Los datos necesarios para el pago C2P (DebtorBankCode, DebtorCellPhone, etc.).
     * @return array|null La respuesta del BNC si el pago se inicia con éxito, o null en caso de error.
     */
    public function initiateC2PPayment(array $data): ?array
    {
        $token = $this->getSessionToken();
        if (!$token) {
            Log::error('No se pudo obtener el token de sesión para iniciar el pago C2P.');
            return null;
        }

        try {
            // Asegúrate de enviar el MerchantID que BNC requiere
            $data['MerchantID'] = $this->merchantId;

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post($this->c2pApiUrl, $data);

            if ($response->successful()) {
                Log::info('Respuesta exitosa de C2P del BNC.', ['response' => $response->json()]);
                return $response->json();
            } else {
                Log::error('Fallo al iniciar el pago C2P con BNC.', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'request_data' => $data
                ]);
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Excepción al iniciar pago C2P con BNC: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return null;
        }
    }

    /**
     * Procesa un pago con tarjeta a través de la API del BNC (VPOS).
     *
     * @param array $data Los datos necesarios para el pago con tarjeta (card_token, amount, description, etc.).
     * @return array|null La respuesta del BNC si el pago se procesa con éxito, o null en caso de error.
     */
    public function processCardPayment(array $data): ?array
    {
        $token = $this->getSessionToken();
        if (!$token) {
            Log::error('No se pudo obtener el token de sesión para procesar el pago con tarjeta.');
            return null;
        }

        try {
            // Asegúrate de enviar el MerchantID que BNC requiere
            $data['MerchantID'] = $this->merchantId;

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post($this->vposApiUrl, $data);

            if ($response->successful()) {
                Log::info('Respuesta exitosa de VPOS del BNC.', ['response' => $response->json()]);
                return $response->json();
            } else {
                Log::error('Fallo al procesar el pago con tarjeta con BNC.', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'request_data' => $data
                ]);
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Excepción al procesar pago con tarjeta con BNC: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
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

            // Asumiendo que el endpoint de bancos del BNC no necesita el token de sesión
            // Si lo necesita, descomenta y usa: $token = $this->getSessionToken();
            // Y luego añade el header Authorization: 'Bearer ' . $token
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
        } catch (\Exception $e) {
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
            } catch (\Exception $e) {
                Log::error('Excepción al obtener las tasas de cambio del BCV: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
                return null;
            }
        });
    }

    /**
     * Simula las tasas de cambio del BCV para desarrollo/pruebas.
     * **¡ADVERTENCIA: REMUEVE O ADAPTA ESTA FUNCIÓN EN PRODUCCIÓN!**
     * En producción, deberías obtener las tasas de una fuente real.
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
}