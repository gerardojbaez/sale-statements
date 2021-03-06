<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSaleStatementItemsTable extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_statement_items', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sale_statement_id')->unsigned();
            $table->string('name');
            $table->integer('price');
            $table->smallInteger('quantity')->unsigned();

            $table->foreign('sale_statement_id')
                ->references('id')
                ->on('sale_statements')
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
        Schema::dropIfExists('sale_statement_items');
    }
}
