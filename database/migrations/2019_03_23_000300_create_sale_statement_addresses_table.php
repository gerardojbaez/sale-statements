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
            $table->string('line_2', 75)->nullable();
            $table->string('locality', 45)->nullable(); // City, Town, Municipality, etc...
            $table->string('administrative_area', 45)->nullable(); // State, Province, Region, etc...
            $table->string('country', 45);
            $table->string('country_code', 3);
            $table->string('postalcode', 25)->nullable();
            $table->string('given_name', 45)->nullable(); // i.e., first name
            $table->string('additional_name', 45)->nullable(); // Can be used to hold a middle name, or a patronymic.
            $table->string('family_name', 45)->nullable(); // i.e., last name
            $table->string('organization', 45)->nullable();

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
