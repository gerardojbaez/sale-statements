<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSaleStatementAddressesTable extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sale_statement_addresses', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sale_statement_id')->unsigned();
            $table->boolean('is_shipping');
            $table->boolean('is_billing');
            $table->string('line_1', 75);
            $table->string('line_2', 75);
            $table->string('city', 45);
            $table->string('province', 45);
            $table->string('country', 45);
            $table->string('zip', 25);
            $table->string('first_name', 45);
            $table->string('last_name', 45);
            $table->string('province_code', 5);
            $table->string('country_code', 3);
            $table->string('organization', 45);

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
        Schema::dropIfExists('sale_statement_addresses');
    }
}
