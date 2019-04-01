<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSaleStatementInvoiceTable extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_statement_invoice', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sale_statement_id')->unsigned();
            $table->integer('sale_statement_order_id')->unsigned()->nullable();

            $table->foreign('sale_statement_id')
                ->references('id')
                ->on('sale_statements')
                ->onDelete('cascade');

            $table->foreign('sale_statement_order_id')
                ->references('sale_statement_id')
                ->on('sale_statement_order')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sale_statement_invoice');
    }
}
