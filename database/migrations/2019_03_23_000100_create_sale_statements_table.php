<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSaleStatementsTable extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_statements', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type', 15);
            $table->integer('discounts')->unsigned();
            $table->timestamps();

            $table->foreign('type')
                ->references('code')
                ->on('sale_statement_types')
                ->onUpdate('cascade')
                ->onDelete('no action');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sale_statements');
    }
}
