<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreditNoteFieldsToInvoices extends Migration
{
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->boolean('is_credit_note')->default(false);
            $table->unsignedInteger('original_invoice_id')->nullable();
            $table->foreign('original_invoice_id')
                ->references('id')
                ->on('invoices')
                ->onDelete('restrict');
        });
    }

    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['original_invoice_id']);
            $table->dropColumn(['is_credit_note', 'original_invoice_id']);
        });
    }
}
