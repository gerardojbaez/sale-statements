<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSaleStatementDiscountItemTable extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_statement_discount_item', function (Blueprint $table) {
            $table->integer('sale_statement_discount_id')->unsigned();
            $table->integer('sale_statement_item_id')->unsigned();

            $table->primary(['sale_statement_item_id', 'sale_statement_discount_id'], 'sale_statement_discount_id_item_id');

            $table->foreign('sale_statement_discount_id')
                ->references('id')
                ->on('sale_statement_discounts')
                ->onDelete('cascade');

            $table->foreign('sale_statement_item_id')
                ->references('id')
                ->on('sale_statement_items')
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
        Schema::dropIfExists('sale_statement_discount_item');
    }
}
