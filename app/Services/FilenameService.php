<?php

namespace Crater\Services;

use Carbon\Carbon;

class FilenameService
{
    public static function invoice(string $number, ?string $invoiceDate = null, ?string $prefix = null): string
    {
        $prefix = $prefix ?? env('PDF_NAME_PREFIX', 'whitemacs');
        $date   = $invoiceDate ? Carbon::parse($invoiceDate) : now();
        $stamp  = $date->format('Y_m_d');

        // Dateienamen-sicher: Schrägstriche & Backslashes ersetzen
        $safeNumber = preg_replace('/[\/\\\\]+/', '-', $number);

        return "{$stamp}_{$prefix}_{$safeNumber}.pdf";
    }
}
