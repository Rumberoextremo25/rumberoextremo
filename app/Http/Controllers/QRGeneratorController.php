<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ally;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Log;

class QRGeneratorController extends Controller
{
    public function index()
    {
        // Obtener aliados activos con id, nombre y descuento
        $allies = Ally::where('status', 'activo')
            ->get([
                'id',
                'company_name as name',
                'discount' // Campo de descuento del aliado
            ]);
        
        return view('Admin.qr-generator', compact('allies'));
    }

    public function generate(Request $request)
    {
        try {
            $validated = $request->validate([
                'ally_id' => 'required|exists:allies,id',
                'type' => 'required|in:c2p,card,p2p',
            ]);

            // Obtener datos del aliado - CORREGIDO: usar 'discount' en lugar de 'descuento_aliado'
            $ally = Ally::where('id', $validated['ally_id'])
                ->select('id', 'company_name', 'discount')  // ← Cambiar 'descuento' por 'discount'
                ->firstOrFail();

            // Construir datos del QR
            $qrData = [
                't' => $validated['type'],        // Tipo de pago
                'mid' => $ally->id,                // ID del aliado
                'mn' => $ally->company_name,       // Nombre del aliado
                'disc' => $ally->discount,         // ← CORREGIDO: usar 'discount'
                'ts' => time(),                    // Timestamp
                'exp' => 0                          // 0 = sin expiración
            ];

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
                    'discount' => $ally->discount,  // ← CORREGIDO
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

    private function generateSignature($data)
    {
        // Firmar con ID, timestamp y descuento
        $payload = $data['mid'] . $data['ts'] . $data['disc'];
        $secretKey = env('QR_SECRET_KEY', 'tu_clave_secreta_aqui');

        return hash_hmac('sha256', $payload, $secretKey);
    }

    public function download(Request $request)
    {
        try {
            $qrString = $request->input('qr_string');
            $format = $request->input('format', 'png');

            if (!$qrString) {
                return response()->json(['error' => 'No se proporcionó el código QR'], 400);
            }

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
            Log::error('Error descargando QR: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}