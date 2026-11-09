<?php

namespace Crater\Listeners;

use Carbon\Carbon;
use Crater\Models\Invoice;
use Crater\Services\FilenameService;
use Illuminate\Mail\Events\MessageSending;
use Swift_Message;
use Swift_Attachment;

class RenameInvoiceAttachmentOnSend
{
    public function handle(MessageSending $event): void
    {
        $raw = $event->message;

        // In deinem Setup ist $raw ein Swift_Message (siehe Log)
        if (! $raw instanceof Swift_Message) {
            return; // nichts tun, wenn anderer Mailer
        }

        $subjectSet = false;

        foreach ((array) $raw->getChildren() as $child) {
            if (!($child instanceof Swift_Attachment)) {
                continue;
            }

            // Manche Setups liefern PDFs als octet-stream
            $mime = strtolower($child->getContentType() ?? '');
            if (!in_array($mime, ['application/pdf', 'application/octet-stream'], true)) {
                continue; // nur Binär-PDFs anfassen
            }

            $orig = (string) $child->getFilename(); // z. B. "RE-25/00057.pdf"

            // Rechnungsnummer aus dem ursprünglichen Namen extrahieren (mit optionalem /… Teil)
            $invNo = null;
            if (preg_match('/RE-\d+(?:\/\d+)?/i', $orig, $m)) {
                $invNo = strtoupper($m[0]); // RE-25/00057
            }

            $invoice = null;
            if ($invNo) {
                $invoice = Invoice::where('invoice_number', $invNo)->first();
            }

            /// Zielname bestimmen
            if ($invoice) {
                $target = FilenameService::invoice($invoice->invoice_number, $invoice->invoice_date);

                // Betreff setzen (nur einmal)
                if (!$subjectSet) {
                    $raw->setSubject("whitemacs Rechnung {$invoice->invoice_number}");
                    $subjectSet = true;
                }
            } else {
                // Fallback: heute + Prefix + ursprünglicher Basename (ohne .pdf)
                $base   = preg_replace('/\.pdf$/i', '', $orig) ?: 'Invoice';
                $target = FilenameService::invoice($base, null); // Service macht Datum + Sanitizing
            }

            // Sichtbaren Anhangsnamen setzen
            $child->setFilename($target);
        }
    }
}
