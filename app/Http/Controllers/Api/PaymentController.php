<?php

namespace App\Http\Controllers\Api;

use App\Services\BncApiService;
use App\Services\PaymentProcessorService;
use App\Services\PayoutService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class PaymentController extends Controller
{
    protected BncApiService $bncApiService;
    protected PaymentProcessorService $paymentProcessor;
    protected PayoutService $payoutService;

    public function __construct(
        BncApiService $bncApiService,
        PaymentProcessorService $paymentProcessor,
        PayoutService $payoutService
    ) {
        $this->bncApiService = $bncApiService;
        $this->paymentProcessor = $paymentProcessor;
        $this->payoutService = $payoutService;
    }

    /**
     * Inicia un pago C2P (Pago Móvil)
     */
    public function initiateC2PPayment(Request $request): JsonResponse
    {
        return $this->paymentProcessor->processPayment($request, 'c2p', [
            'DebtorBankCode' => 'required|integer',
            'DebtorCellPhone' => 'required|string|regex:/^[0-9]{10,15}$/',
            'DebtorID' => 'required|string|min:6|max:20',
            'Amount' => 'required|numeric|min:0.01',
            'Token' => 'required|string|regex:/^[0-9]{6,7}$/',
            'Terminal' => 'required|string|max:50',
        ], function ($validatedData) {
            return [
                'DebtorBankCode' => (int)$validatedData['DebtorBankCode'],
                'DebtorCellPhone' => $validatedData['DebtorCellPhone'],
                'DebtorID' => $validatedData['DebtorID'],
                'Amount' => (float)$validatedData['Amount'],
                'Token' => $validatedData['Token'],
                'Terminal' => $validatedData['Terminal'],
                'ChildClientID' => $validatedData['ChildClientID'] ?? '',
                'BranchID' => $validatedData['BranchID'] ?? ''
            ];
        });
    }

    /**
     * Procesa un pago con tarjeta (VPOS)
     */
    public function processCardPayment(Request $request): JsonResponse
    {
        return $this->paymentProcessor->processPayment($request, 'card', [
            'TransactionIdentifier' => 'required|string|max:50',
            'Amount' => 'required|numeric|min:0.01',
            'idCardType' => 'required|integer',
            'CardNumber' => 'required|string|max:20',
            'dtExpiration' => 'required|numeric|digits:6',
            'CardHolderName' => 'required|string|max:255',
            'AccountType' => 'required|integer',
            'CVV' => 'required|numeric|digits_between:3,4',
            'CardPIN' => 'required|numeric',
            'CardHolderID' => 'required|numeric',
            'AffiliationNumber' => 'required|numeric',
            'OperationRef' => 'required|string|max:100',
        ], function ($validatedData) {
            return [
                'TransactionIdentifier' => $validatedData['TransactionIdentifier'],
                'Amount' => (float)$validatedData['Amount'],
                'idCardType' => (int)$validatedData['idCardType'],
                'CardNumber' => $validatedData['CardNumber'],
                'dtExpiration' => (int)$validatedData['dtExpiration'],
                'CardHolderName' => $validatedData['CardHolderName'],
                'AccountType' => (int)$validatedData['AccountType'],
                'CVV' => (int)$validatedData['CVV'],
                'CardPIN' => (int)$validatedData['CardPIN'],
                'CardHolderID' => (int)$validatedData['CardHolderID'],
                'AffiliationNumber' => (int)$validatedData['AffiliationNumber'],
                'OperationRef' => $validatedData['OperationRef'],
                'ChildClientID' => $validatedData['ChildClientID'] ?? '',
                'BranchID' => $validatedData['BranchID'] ?? ''
            ];
        });
    }

    /**
     * Procesa un pago P2P (Transferencia)
     */
    public function processP2PPayment(Request $request): JsonResponse
    {
        return $this->paymentProcessor->processPayment($request, 'p2p', [
            'Amount' => 'required|numeric|min:0.01|max:999999.99',
            'BeneficiaryBankCode' => 'required|integer|min:1',
            'BeneficiaryCellPhone' => 'required|string|regex:/^[0-9]{10,15}$/',
            'BeneficiaryID' => 'required|string|min:6|max:20',
            'BeneficiaryName' => 'required|string|max:255|regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/',
            'Description' => 'required|string|max:255',
        ], function ($validatedData) {
            return [
                'Amount' => round((float)$validatedData['Amount'], 2),
                'BeneficiaryBankCode' => (int)$validatedData['BeneficiaryBankCode'],
                'BeneficiaryCellPhone' => preg_replace('/[^0-9]/', '', $validatedData['BeneficiaryCellPhone']),
                'BeneficiaryID' => $validatedData['BeneficiaryID'],
                'BeneficiaryName' => $this->sanitizeName($validatedData['BeneficiaryName']),
                'Description' => substr($validatedData['Description'], 0, 255),
                'BeneficiaryEmail' => $validatedData['BeneficiaryEmail'] ?? '',
            ];
        });
    }

    /**
     * ==================== MÉTODOS DE PAGOS Y COMISIONES ====================
     */

    /**
     * Genera archivo de pago a proveedores en formato BNC
     */
    public function generarArchivoPagosBNC(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
                'tipo_cuenta' => 'required|in:corriente,ahorro',
                'concepto' => 'nullable|string|max:100',
            ]);

            $result = $this->payoutService->generarArchivoPagosBNC(
                $validated['fecha_inicio'],
                $validated['fecha_fin'],
                $validated['tipo_cuenta'],
                $validated['concepto']
            );

            return response()->json([
                'message' => 'Archivo de pagos BNC generado exitosamente',
                'data' => $result
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error generando archivo de pagos BNC: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Confirma pagos procesados
     */
    public function confirmarPagosProcesados(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'payout_ids' => 'required|array',
                'payout_ids.*' => 'integer|exists:payouts,id',
                'fecha_pago' => 'required|date',
                'referencia_pago' => 'required|string|max:100',
                'archivo_comprobante' => 'nullable|file|mimes:pdf,jpg,png|max:5120',
            ]);

            $result = $this->payoutService->confirmarPagosProcesados(
                $validated['payout_ids'],
                $validated['fecha_pago'],
                $validated['referencia_pago'],
                $request->file('archivo_comprobante')
            );

            return response()->json([
                'message' => 'Pagos confirmados exitosamente',
                'data' => $result
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error confirmando pagos: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // En el PaymentController, actualiza el método descargarArchivoBNC:
    public function descargarArchivoBNC($archivo)
    {
        try {
            $fileContent = $this->payoutService->descargarArchivoBNC($archivo);

            return response($fileContent['content'], 200, $fileContent['headers']);
        } catch (\Exception $e) {
            Log::error('Error descargando archivo BNC: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // Y agrega este nuevo método para estadísticas:
    public function obtenerEstadisticasPayouts(): JsonResponse
    {
        try {
            $estadisticas = $this->payoutService->obtenerEstadisticasPayouts();

            return response()->json([
                'data' => $estadisticas
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error obteniendo estadísticas de payouts: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // Método para revertir pagos
    public function revertirPago(Request $request, $payoutId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'motivo' => 'required|string|max:255'
            ]);

            $this->payoutService->revertirPago($payoutId, $validated['motivo']);

            return response()->json([
                'message' => 'Pago revertido exitosamente'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error revirtiendo pago: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtiene pagos pendientes
     */
    public function obtenerPagosPendientes(): JsonResponse
    {
        try {
            $pagosPendientes = $this->payoutService->obtenerPagosPendientes();

            return response()->json([
                'data' => $pagosPendientes,
                'total' => $pagosPendientes->count(),
                'monto_total' => $pagosPendientes->sum('commission_amount')
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error obteniendo pagos pendientes: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Obtiene pagos por filtros
     */
    public function obtenerPagosPorFiltro(Request $request): JsonResponse
    {
        try {
            $payouts = $this->payoutService->obtenerPagosPorFiltro($request);

            return response()->json([
                'data' => $payouts->items(),
                'total' => $payouts->total(),
                'current_page' => $payouts->currentPage(),
                'per_page' => $payouts->perPage(),
                'last_page' => $payouts->lastPage()
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error obteniendo pagos por filtro: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * ==================== MÉTODOS AUXILIARES PRIVADOS ====================
     */

    /**
     * Sanitizar nombre para el banco
     */
    private function sanitizeName(string $name): string
    {
        $name = preg_replace('/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/', '', $name);
        $name = preg_replace('/\s+/', ' ', trim($name));
        return substr($name, 0, 255);
    }
}
