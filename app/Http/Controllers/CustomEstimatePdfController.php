<?php

namespace Crater\Http\Controllers;

use Barryvdh\DomPDF\PDF as DomPdf;
use Crater\Models\Estimate;
use Crater\Services\PdfWithAgbService;
use Illuminate\Http\Request;

class CustomEstimatePdfController extends Controller
{
    public function __invoke(Request $request, Estimate $estimate)
    {
        \Log::info('CUSTOM PDF', [
            'route_param' => $request->route('estimate'),
            'model_id'    => $estimate->id ?? null,
            'model_no'    => $estimate->estimate_number ?? null,
        ]);

        // 1) PDF IMMER FRISCH rendern (kein gespeichertes PDF verwenden)
        $pdfObj = $estimate->getPDFData();   // DomPDF-Objekt

        // 2) In Binary umwandeln
        $pdfBinary = ($pdfObj instanceof DomPdf) ? $pdfObj->output() : (string) $pdfObj;

        // Optional: alte gespeicherte PDFs entfernen, damit Core künftig nicht auf alte Files zurückfällt
        // (nur ausführen, wenn du das möchtest)
        // if (method_exists($estimate, 'clearMediaCollection')) {
        //     $estimate->clearMediaCollection('estimate');
        // }

        // 3) AGB anhängen
        if (env('CRATER_APPEND_AGB', true)) {
            $pdfBinary = app(PdfWithAgbService::class)
                ->appendAgb($pdfBinary, env('CRATER_AGB_PUBLIC_PATH', 'uploads/docs/whitemacs_AGB_V1.pdf'));
        }

        // 4) Ausliefern
        $filename = sprintf('whitemacs_Auftragsangebot_%s', $estimate->estimate_number ?? now()->format('YmdHis'));
        return response($pdfBinary, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }
}
