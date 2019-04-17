<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSaleStatementOrderTable extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_statement_order', function (Blueprint $table) {
            $table->integer('id')->unsigned();
            $table->integer('sale_statement_quote_id')->unsigned()->nullable();

            $table->foreign('id')
                ->references('id')
                ->on('sale_statements')
                ->onDelete('cascade');

            $table->foreign('sale_statement_quote_id')
                ->references('id')
                ->on('sale_statement_quote')
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
        Schema::dropIfExists('sale_statement_order');
    }
}
