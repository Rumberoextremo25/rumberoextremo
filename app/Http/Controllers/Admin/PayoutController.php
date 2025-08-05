<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PartnerPayout; // Asumo que este es el nombre de tu modelo para los pagos a aliados
use App\Models\Ally; // Asumo que 'Ally' es el nombre de tu modelo para los Aliados
use League\Csv\Writer; // Necesitarás instalar league/csv: composer require league/csv
use SplTempFileObject; // Para el archivo CSV en memoria
use Illuminate\Support\Facades\DB; // Para transacciones de base de datos
use Illuminate\Support\Facades\Log; // Para logging
use Illuminate\Support\Carbon; // Para manejo de fechas
use Symfony\Component\HttpFoundation\StreamedResponse; // Asegúrate de importar esto
use Illuminate\Http\Response; // <-- ¡Importa Illuminate\Http\Response!
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PayoutController extends Controller
{
    /**
     * Muestra una lista de pagos pendientes a aliados en el panel de administración.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $pendingPayouts = PartnerPayout::with('ally', 'order')
                                        ->where('status', 'pending')
                                        ->orderBy('created_at', 'asc')
                                        ->get();

        return view('Admin.payouts.index', compact('pendingPayouts'));
    }

    /**
     * Genera un archivo CSV con los datos de las transferencias pendientes seleccionadas.
     * Este CSV está formateado para ser compatible con la carga masiva en plataformas bancarias (ej. BNC Online Empresas).
     *
     * @param Request $request Contiene los IDs de los pagos a procesar.
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\Response
     * // <-- ¡CAMBIO AQUÍ! Añadimos |Illuminate\Http\Response
     */
    public function generateCsv(Request $request): StreamedResponse|Response
    {
        // 1. Validar que se hayan seleccionado IDs de pagos.
        // Si la validación falla, Laravel automáticamente lanza una Illuminate\Validation\ValidationException.
        // El manejador de excepciones de Laravel convierte esto en un Illuminate\Http\Response (ej. JsonResponse 422).
        $request->validate([
            'payout_ids' => 'required|array',
            'payout_ids.*' => 'exists:partner_payouts,id',
        ]);

        $selectedPayoutIds = $request->input('payout_ids');

        // 2. Obtener los pagos seleccionados que estén pendientes.
        $payoutsToProcess = PartnerPayout::with('ally', 'order')
                                        ->whereIn('id', $selectedPayoutIds)
                                        ->where('status', 'pending')
                                        ->get();

        // 3. Si no se encontraron pagos pendientes para los IDs seleccionados, lanzar una excepción.
        // Esto interrumpe el flujo y Laravel lo capturará.
        if ($payoutsToProcess->isEmpty()) {
            Log::warning("Intento de generar CSV sin pagos pendientes para IDs: " . implode(', ', $selectedPayoutIds));
            throw new NotFoundHttpException('No se encontraron pagos pendientes válidos para los IDs seleccionados.');
        }

        // --- Configuración y generación del CSV ---
        $csv = Writer::createFromFileObject(new SplTempFileObject());
        $csv->setOutputBOM(Writer::BOM_UTF8);
        $csv->setDelimiter(';'); // Define el delimitador (común: , ; |). Ajusta según el banco.

        // Encabezados del CSV. ADAPTA ESTOS NOMBRES Y ORDEN A LOS REQUERIMIENTOS ESPECÍFICOS DE TU BANCO.
        $csv->insertOne([
            'Cuenta Origen', 'Tipo Operacion', 'Cuenta Destino', 'Tipo Cuenta Destino',
            'Tipo Documento Beneficiario', 'Documento Beneficiario', 'Nombre Beneficiario',
            'Monto', 'Concepto', 'Email Beneficiario'
        ]);

        // Datos bancarios de Rumbero Extremo (cuenta de origen)
        $rumberoAccountNumber = env('RUMBERO_BANK_ACCOUNT_NUMBER');
        $rumberoAccountType = env('RUMBERO_BANK_ACCOUNT_TYPE');

        foreach ($payoutsToProcess as $payout) {
            $ally = $payout->ally;

            if (!$ally || empty($ally->account_number) || empty($ally->account_type) || empty($ally->id_document) || empty($ally->name)) {
                Log::error("Datos bancarios incompletos para el aliado ID: " . ($ally->id ?? 'N/A') . " asociado al pago #{$payout->id}. Este pago no se incluirá en el CSV.");
                throw new BadRequestHttpException("Datos bancarios incompletos para el aliado del pago ID #{$payout->id}.");
            }

            $docType = '';
            $idDocumentValue = $ally->id_document;
            if (!empty($idDocumentValue)) {
                $firstChar = strtoupper(substr($idDocumentValue, 0, 1));
                if (in_array($firstChar, ['V', 'E', 'P', 'G', 'J'])) {
                    $docType = $firstChar;
                    $idDocumentValue = substr($idDocumentValue, 1);
                } else {
                    $docType = 'V';
                }
            }

            $formattedAmount = number_format($payout->amount, 2, '.', '');

            $csv->insertOne([
                $rumberoAccountNumber,
                'TRANSFERENCIA',
                $ally->account_number,
                ($ally->account_type === 'corriente' ? 'C' : 'A'),
                $docType,
                $idDocumentValue,
                $ally->name,
                $formattedAmount,
                "Pago OE#{$payout->order->id} | Ref:RE-P". $payout->id,
                $ally->email
            ]);
        }

        $filename = 'transferencias_rumberoextremo_' . Carbon::now()->format('Ymd_His') . '.csv';

        // Retorna la respuesta HTTP para descargar el archivo CSV
        return response((string) $csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Marca los pagos seleccionados como procesados después de la carga del CSV
     * y la confirmación en la plataforma bancaria.
     *
     * @param Request $request Contiene los IDs de los pagos a marcar y una referencia de la transacción.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function markProcessed(Request $request)
    {
        $request->validate([
            'payout_ids' => 'required|array',
            'payout_ids.*' => 'exists:partner_payouts,id',
            'transaction_reference' => 'nullable|string|max:255',
        ]);

        $selectedPayoutIds = $request->input('payout_ids');
        $transactionReference = $request->input('transaction_reference');

        DB::beginTransaction();
        try {
            PartnerPayout::whereIn('id', $selectedPayoutIds)
                        ->where('status', 'pending')
                        ->update([
                            'status' => 'processed',
                            'transaction_reference' => $transactionReference,
                            'processed_at' => now(),
                            'notes' => DB::raw("CONCAT(COALESCE(notes, ''), '\nProcesado en lote: {$transactionReference}')")
                        ]);

            DB::commit();
            Log::info("Pagos a aliados marcados como procesados. IDs: " . implode(', ', $selectedPayoutIds));
            return redirect()->back()->with('success', 'Pagos marcados como procesados exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error al marcar pagos como procesados: " . $e->getMessage(), ['exception' => $e]);
            return redirect()->back()->with('error', 'Hubo un error al marcar los pagos como procesados. Inténtalo de nuevo.');
        }
    }
}