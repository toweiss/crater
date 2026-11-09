<?php

namespace Crater\Listeners;

use Carbon\Carbon;
use Crater\Models\Invoice;
use Crater\Services\FilenameService;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAdded;

class RenameInvoicePdfMedia
{
    public function handle(MediaHasBeenAdded $event): void
    {
        $media = $event->media;

        // Nur Hauptdateien der Rechnungs-PDF-Collection anfassen
        if ($media->collection_name !== 'invoice') {
            return;
        }
        if (! $media->model instanceof Invoice) {
            return;
        }

        $invoice = $media->model;

        // Neuen Dateinamen bestimmen (YYYY_MM_DD_whitemacs_RE-XXXX.pdf)
        $filename = \Crater\Services\FilenameService::invoice(
            $invoice->invoice_number ?? ('RE-'.$invoice->id),
            $invoice->invoice_date // wird in service zu Y_m_d formatiert
        );

        // Aktuelle & neue Pfade
        $disk = Storage::disk($media->disk);
        $oldPath = $media->getPath(); // voller aktueller Pfad inkl. altem Dateinamen

        // DB-Werte setzen (beeinflusst getPath() → erzeugt neuen Zielpfad)
        $media->file_name = $filename;                              // z. B. 2025_09_02_whitemacs_RE-0123.pdf
        $media->name      = pathinfo($filename, PATHINFO_FILENAME); // ohne .pdf

        $newPath = $media->getPath(); // Zielpfad gemäß neuem file_name

        // Physische Datei auf dem Storage umbenennen/verschieben
        if ($disk->exists($oldPath) && $oldPath !== $newPath) {
            $disk->move($oldPath, $newPath);
        }

        $media->save();
    }
}
