<?php

namespace Crater\Http\Controllers;

use Barryvdh\DomPDF\PDF as DomPdf;
use Crater\Models\Invoice;
use Crater\Services\FilenameService;
use Illuminate\Http\Request;

class CustomInvoicePdfController extends Controller
{
    public function __invoke(Request $request, Invoice $invoice)
    {
        // Immer live rendern wie beim Angebot (kein altes Medien-PDF)
        $pdfObj = $invoice->getPDFData();                         // DomPDF-Objekt
        $pdfBinary = ($pdfObj instanceof DomPdf) ? $pdfObj->output() : (string) $pdfObj;

        $filename = FilenameService::invoice(
            $invoice->invoice_number ?? ('RE-'.$invoice->id),
            $invoice->invoice_date // erwartet ein Datum/Carbon-kompatibel
        );

        return response($pdfBinary, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
        ]);
    }
}
