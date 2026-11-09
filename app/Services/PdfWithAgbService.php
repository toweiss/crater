<?php

namespace Crater\Services;

use iio\libmergepdf\Driver\Fpdi2Driver;
use iio\libmergepdf\Merger;

class PdfWithAgbService
{
    /**
     * Hängt eine AGB-PDF an ein bestehendes PDF-Binary an.
     * @param string $mainPdfBinary  Bereits gerendertes Angebots-PDF (Binary/String)
     * @param string $agbRelativePublicPath  Relativer Pfad ab /public (z. B. 'docs/AGB.pdf')
     * @return string  Gemergtes PDF als Binary
     */
    public function appendAgb(string $mainPdfBinary, string $agbRelativePublicPath = 'docs/whitemacs_AGB_V1.pdf'): string
    {
        $agbPath = public_path($agbRelativePublicPath);

        if (!is_readable($agbPath)) {
            // Kein Blocker: Original zurückgeben
            return $mainPdfBinary;
        }

        try {
            $merger = new Merger(new Fpdi2Driver());
            $merger->addRaw($mainPdfBinary); // 1) Angebot
            $merger->addFile($agbPath);      // 2) AGB
            return $merger->merge();
        } catch (\Throwable $e) {
            // Safety first: Bei Fehlern nicht hart abbrechen
            return $mainPdfBinary;
        }
    }
}
