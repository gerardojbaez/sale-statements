<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSaleStatementInvoicePaymentsTable extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_statement_invoice_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sale_statement_invoice_id')->unsigned();
            $table->integer('amount_applied')->unsigned();
            $table->timestamps();

            $table->foreign('sale_statement_invoice_id', 'sale_statement_payments_invoice_id')
                ->references('id')
                ->on('sale_statement_invoice')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sale_statement_invoice_payments');
    }
}
