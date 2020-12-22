<?php

namespace Gerardojbaez\SaleStatements\Tests\Feature\Console;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Gerardojbaez\SaleStatements\Tests\TestCase;

class InsertSaleStatementTypesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Insert sale statement types.
     *
     * @return void
     */
    public function testInsert()
    {
        // Act
        $this->artisan('sale-statement:insert-types');

        // Assert
        $this->assertDatabaseHas('sale_statement_types', [
            'code' => 'quote',
            'name' => 'Quote',
        ]);

        $this->assertDatabaseHas('sale_statement_types', [
            'code' => 'order',
            'name' => 'Order',
        ]);

        $this->assertDatabaseHas('sale_statement_types', [
            'code' => 'invoice',
            'name' => 'Invoice',
        ]);

        $this->assertDatabaseHas('sale_statement_types', [
            'code' => 'credit_memo',
            'name' => 'Credit Memo',
        ]);
    }

    /**
     * Insert sale statement types.
     *
     * @return void
     */
    public function testIgnoresAlreadyInserted()
    {
        // Act
        $this->artisan('sale-statement:insert-types');
        $this->artisan('sale-statement:insert-types');

        // Assert
        $this->assertEquals(4, DB::table('sale_statement_types')->count());

        $this->assertDatabaseHas('sale_statement_types', [
            'code' => 'quote',
            'name' => 'Quote',
        ]);

        $this->assertDatabaseHas('sale_statement_types', [
            'code' => 'order',
            'name' => 'Order',
        ]);

        $this->assertDatabaseHas('sale_statement_types', [
            'code' => 'invoice',
            'name' => 'Invoice',
        ]);

        $this->assertDatabaseHas('sale_statement_types', [
            'code' => 'credit_memo',
            'name' => 'Credit Memo',
        ]);
    }
}
