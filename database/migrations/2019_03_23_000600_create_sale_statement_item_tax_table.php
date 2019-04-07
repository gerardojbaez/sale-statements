<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSaleStatementItemTaxTable extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_statement_item_tax', function (Blueprint $table) {
            $table->integer('sale_statement_item_id')->unsigned();
            $table->integer('sale_statement_tax_id')->unsigned();

            $table->primary(['sale_statement_item_id', 'sale_statement_tax_id'], 'sale_statement_item_id_tax_id');

            $table->foreign('sale_statement_item_id')
                ->references('id')
                ->on('sale_statement_items')
                ->onDelete('cascade');

            $table->foreign('sale_statement_tax_id')
                ->references('id')
                ->on('sale_statement_taxes')
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
        Schema::dropIfExists('sale_statement_item_tax');
    }
}
