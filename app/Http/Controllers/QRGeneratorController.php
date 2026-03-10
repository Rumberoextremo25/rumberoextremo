<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Ally;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QRGeneratorController extends Controller
{
    public function index()
    {
        // Obtener aliados activos
        $allies = Ally::where('status', 'activo')
            ->get([
                'id',
                'company_name as name',
            ]);
        
        return view('Admin.qr-generator', compact('allies'));
    }

    public function generate(Request $request)
    {
        try {
            $validated = $request->validate([
                'ally_id' => 'required|exists:allies,id',
                'type' => 'required|in:c2p,card,p2p',
                // ⚠️ EL MONTO NO SE VALIDA NI SE USA
            ]);

            // Obtener datos del aliado
            $ally = Ally::findOrFail($validated['ally_id']);

            // Construir datos del QR - SOLO DATOS FIJOS DEL ALIADO
            $qrData = $this->buildQRData($validated['type'], $ally);

            // Generar firma de seguridad
            $qrData['sig'] = $this->generateSignature($qrData);

            // Convertir a JSON y codificar en Base64
            $jsonString = json_encode($qrData);
            $base64Data = base64_encode($jsonString);
            $qrString = 'PAYMENT:' . $base64Data;

            // Generar código QR en formato SVG
            $qrCode = QrCode::format('svg')
                ->size(300)
                ->margin(1)
                ->errorCorrection('H')
                ->generate($qrString);

            return response()->json([
                'success' => true,
                'qr_code' => (string) $qrCode,
                'qr_data' => $qrString,
                'json_data' => $jsonString,
                'base64_data' => $base64Data,
                'ally_info' => [
                    'id' => $ally->id,
                    'name' => $ally->company_name,
                    'type' => $validated['type']
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error generando QR: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el código QR: ' . $e->getMessage()
            ], 500);
        }
    }

    private function buildQRData($type, $ally)
    {
        // DATOS FIJOS desde .env (cuenta única para todos los pagos)
        $terminal = env('TERMINAL_NUMBER', '12209183');
        $affiliation = env('AFFILIATION_NUMBER', '860979861');
        $bankCode = env('BANK_CODE', '0108');
        $companyPhone = env('COMPANY_PHONE', '04141234567');
        $companyAccount = env('COMPANY_ACCOUNT', '01341234567890123456');

        // Datos base del QR - SIN MONTO
        $qrData = [
            't' => $type,                       // Tipo de pago
            'mid' => $ally->id,                  // ID del aliado
            'mn' => $ally->company_name,         // Nombre del aliado
            'ts' => time(),                      // Timestamp
            'exp' => 0,                           // 0 = sin expiración
        ];

        // Agregar datos específicos según tipo
        switch ($type) {
            case 'c2p':
                $qrData['bc'] = $bankCode;        // Banco fijo de la empresa
                $qrData['rp'] = $companyPhone;     // Teléfono fijo de la empresa
                $qrData['tm'] = $terminal;         // Terminal fijo
                break;

            case 'card':
                $qrData['af'] = $affiliation;      // N° afiliación fijo
                // La referencia de operación la genera la app
                break;

            case 'p2p':
                $qrData['acc'] = $companyAccount;  // Cuenta fija de la empresa
                // ID del receptor lo genera la app
                break;
        }

        return $qrData;
    }

    private function generateSignature($data)
    {
        // Firmar solo con ID y timestamp (el monto lo pone la app)
        $payload = $data['mid'] . $data['ts'];
        $secretKey = env('QR_SECRET_KEY', 'tu_clave_secreta_aqui');

        return hash_hmac('sha256', $payload, $secretKey);
    }

    public function download(Request $request)
    {
        try {
            $qrString = $request->input('qr_string');
            $format = $request->input('format', 'png');

            $qrCode = QrCode::format($format)
                ->size(500)
                ->margin(2)
                ->errorCorrection('H')
                ->generate($qrString);

            $filename = 'qr_' . time() . '.' . $format;

            return response($qrCode)
                ->header('Content-Type', $format === 'svg' ? 'image/svg+xml' : 'image/png')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
