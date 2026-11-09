<?php

use Crater\Models\Invoice;
use Crater\Http\Controllers\CustomInvoicePdfController;

// Robust binden: Hash ODER ID
Route::bind('invoice', function ($value) {
    $q = Invoice::query()->where('unique_hash', $value);
    if (is_numeric($value)) $q->orWhere('id', (int) $value);
    return $q->firstOrFail();
});

// Gleiche URI wie der Core (meist: invoices/pdf/{invoice})
Route::middleware('web')
    ->get('invoices/pdf/{invoice}', CustomInvoicePdfController::class)
    ->name('invoices.pdf');

use Crater\Models\Estimate;
use Illuminate\Support\Facades\Route;

// Robust binden: Hash ODER ID akzeptieren
Route::bind('estimate', function ($value) {
    // 1) Erst Hash versuchen (Standard in Crater)
    $q = Estimate::query()->where('unique_hash', $value);

    // 2) Fallback: numerische ID
    if (is_numeric($value)) {
        $q->orWhere('id', (int) $value);
    }

    // 3) (Optional) weitere mögliche Schlüssel – nur lassen, wenn Spalten existieren:
    // $q->orWhere('hash', $value)->orWhere('xid', $value);

    return $q->firstOrFail();
});

Route::middleware('web')
    ->get('estimates/pdf/{estimate}', \Crater\Http\Controllers\CustomEstimatePdfController::class)
    ->name('estimates.pdf');

