<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payout;
use App\Models\Ally;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PayoutController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Payout::with(['ally', 'sale'])
            ->orderBy('generation_date', 'desc');

        // Filtrar por estado si se especifica
        if ($request->has('status') && $request->status != 'all') {
            $query->where('status', $request->status);
        } else {
            $query->whereIn('status', ['pending', 'processing']);
        }

        // Filtrar por rango de fechas
        if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
            $query->whereBetween('generation_date', [
                $request->fecha_inicio,
                $request->fecha_fin
            ]);
        }

        // Filtrar por aliado
        if ($request->has('ally_id')) {
            $query->where('ally_id', $request->ally_id);
        }

        $payouts = $query->paginate(20);
        $allies = Ally::active()->get();

        return view('Admin.payouts.index', compact('payouts', 'allies'));
    }

    /**
     * Display only pending payouts
     */
    public function pending(Request $request)
    {
        $query = Payout::with(['ally', 'sale'])
            ->where('status', 'pending')
            ->orderBy('generation_date', 'desc');

        // Filtrar por rango de fechas
        if ($request->has('fecha_inicio') && $request->has('fecha_fin')) {
            $query->whereBetween('generation_date', [
                $request->fecha_inicio,
                $request->fecha_fin
            ]);
        }

        $payouts = $query->paginate(20);
        $allies = Ally::active()->get();

        return view('Admin.payouts.pending', compact('payouts', 'allies'));
    }

    /**
     * Generate BNC file for payouts
     */
    public function generateBncFile(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'tipo_cuenta' => 'required|in:corriente,ahorro',
            'concepto' => 'nullable|string|max:100',
        ]);

        try {
            // Obtener payouts pendientes
            $payouts = Payout::with(['ally', 'sale'])
                ->where('status', 'pending')
                ->whereBetween('generation_date', [
                    $request->fecha_inicio,
                    $request->fecha_fin
                ])
                ->get();

            if ($payouts->isEmpty()) {
                return redirect()->back()->with('error', 'No hay pagos pendientes en el rango de fechas especificado');
            }

            // Crear instancia del controlador de API
            $paymentController = new \App\Http\Controllers\Api\PaymentController(
                app(\App\Services\BncApiService::class)
            );

            // Usar el método público
            $archivoNombre = $paymentController->generarArchivoBNC($payouts, $request->tipo_cuenta, $request->concepto);

            // Actualizar estado a processing
            Payout::whereIn('id', $payouts->pluck('id'))
                ->update(['status' => 'processing']);

            // Descargar el archivo
            $filePath = storage_path('app/pagos/bnc/' . $archivoNombre);

            return response()->download($filePath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al generar archivo: ' . $e->getMessage());
        }
    }

    /**
     * Confirm payouts processing
     */
    public function confirmPayouts(Request $request)
    {
        $request->validate([
            'payout_ids' => 'required',
            'fecha_pago' => 'required|date',
            'referencia_pago' => 'required|string|max:100',
            'archivo_comprobante' => 'nullable|file|mimes:pdf,jpg,png|max:5120',
        ]);

        try {
            $payoutIds = json_decode($request->payout_ids, true);

            // Crear instancia del controlador de API
            $paymentController = new \App\Http\Controllers\Api\PaymentController(
                app(\App\Services\BncApiService::class)
            );

            // Crear un request artificial para el método del API
            $apiRequest = new \Illuminate\Http\Request([
                'payout_ids' => $payoutIds,
                'fecha_pago' => $request->fecha_pago,
                'referencia_pago' => $request->referencia_pago,
                'archivo_comprobante' => $request->file('archivo_comprobante'),
            ]);

            $response = $paymentController->confirmarPagosProcesados($apiRequest);

            if ($response->getStatusCode() === 200) {
                return redirect()->route('admin.payouts.index')->with('success', 'Pagos confirmados exitosamente');
            } else {
                $errorData = json_decode($response->getContent(), true);
                return redirect()->back()->with('error', $errorData['message'] ?? 'Error al confirmar pagos');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $allies = Ally::active()->get();
        return view('Admin.payouts.create', compact('allies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ally_id' => 'required|exists:allies,id',
            'sale_amount' => 'required|numeric|min:0.01',
            'commission_percentage' => 'required|numeric|min:0|max:100',
            'commission_amount' => 'required|numeric|min:0',
            'sale_reference' => 'nullable|string|max:100',
            'status' => 'required|in:pending,processing,paid',
            'notes' => 'nullable|string',
        ]);

        try {
            $ally = Ally::findOrFail($request->ally_id);

            $payout = Payout::create([
                'ally_id' => $request->ally_id,
                'sale_amount' => $request->sale_amount,
                'commission_percentage' => $request->commission_percentage,
                'commission_amount' => $request->commission_amount,
                'sale_reference' => $request->sale_reference,
                'status' => $request->status,
                'generation_date' => now(),
                'ally_payment_method' => $ally->default_payment_method ?? 'transfer',
                'ally_account_number' => $ally->account_number,
                'ally_bank' => $ally->bank_name,
                'notes' => $request->notes,
            ]);

            return redirect()->route('admin.payouts.show', $payout->id)
                ->with('success', 'Pago manual creado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al crear el pago: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(Payout $payout)
    {
        $payout->load(['ally', 'sale.client', 'sale.branch']);
        return view('Admin.payouts.show', compact('payout'));
    }
}
