<?php

namespace Gerardojbaez\SaleStatements\Tests\Feature;

use Illuminate\Support\Facades\DB;
use Gerardojbaez\SaleStatements\Replicate;
use Gerardojbaez\SaleStatements\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Gerardojbaez\SaleStatements\Models\SaleStatement;
use Gerardojbaez\SaleStatements\Models\SaleStatementType;

class SaleStatementManagementTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create quote type sale statement.
     *
     * @return void
     */
    public function testCreateQuoteTypeSaleStatement()
    {
        // Arrange
        SaleStatementType::create([
            'code' => 'quote',
            'name' => 'Quote',
        ]);

        // Act
        list($statement, $items, $taxes) = $this->createSaleStatement('quote');

        // Assert
        $this->assertSaleStatementWasCreated($statement,  $items, $taxes, 'quote');
    }

    /**
     * Create order type sale statement.
     *
     * @return void
     */
    public function testCreateOrderTypeSaleStatement()
    {
        // Arrange
        SaleStatementType::create([
            'code' => 'order',
            'name' => 'Order',
        ]);

        // Act
        list($statement, $items, $taxes) = $this->createSaleStatement('order');

        // Assert
        $this->assertSaleStatementWasCreated($statement, $items, $taxes, 'order');
    }

    /**
     * Create invoice type sale statement.
     *
     * @return void
     */
    public function testCreateInvoiceTypeSaleStatement()
    {
        // Arrange
        SaleStatementType::create([
            'code' => 'invoice',
            'name' => 'Invoice',
        ]);

        // Act
        list($statement, $items, $taxes) = $this->createSaleStatement('invoice');

        $payment = $statement->invoice->payments()->create([
            'amount_applied' => 5000
        ]);

        $statement->invoice->payments()->create([
            'amount_applied' => 10000
        ]);

        // Assert
        $this->assertSaleStatementWasCreated($statement, $items, $taxes, 'invoice');

        $this->assertDatabaseHas('sale_statement_invoice_payments', [
            'sale_statement_invoice_id' => $statement->invoice->id,
            'amount_applied' => 5000,
        ]);

        $this->assertDatabaseHas('sale_statement_invoice_payments', [
            'sale_statement_invoice_id' => $statement->invoice->id,
            'amount_applied' => 10000,
        ]);

        $this->assertEquals($statement->invoice->id, $payment->invoice->id);
    }

    /**
     * Create credit memo type sale statement.
     *
     * @return void
     */
    public function testCreateCreditMemoTypeSaleStatement()
    {
        // Arrange
        SaleStatementType::create([
            'code' => 'credit_memo',
            'name' => 'Credit Memo',
        ]);

        // Act
        list($statement, $items, $taxes) = $this->createSaleStatement('credit_memo');

        // Assert
        $this->assertSaleStatementWasCreated($statement, $items, $taxes, 'credit_memo');
    }

    /**
     * Generate order from quote. Order must be identical.
     *
     * @return void
     */
    public function testCreateOrderFromQuote()
    {
        // Arrange
        SaleStatementType::create([
            'code' => 'quote',
            'name' => 'Quote',
        ]);

        SaleStatementType::create([
            'code' => 'order',
            'name' => 'Order',
        ]);

        list($quote, $quoteItems, $quoteTaxes) = $this->createSaleStatement('quote');

        // Act
        $order = (new Replicate($quote))->asOrder();

        // Assert
        $this->assertInstanceOf(SaleStatement::class, $order);
        $this->assertEquals('order', $order->type);

        $orderItems = $order->items()->get();
        $orderTaxes = $order->taxes()->get();

        $this->assertCount(2, $orderItems);
        $this->assertCount(2, $orderTaxes);

        $this->assertSaleStatementWasCreated($quote, $quoteItems, $quoteTaxes, 'quote');
        $this->assertSaleStatementWasCreated($order, $orderItems, $orderTaxes, 'order');

        $this->assertDatabaseHas('sale_statement_order', [
            'sale_statement_id' => $order->id,
            'sale_statement_quote_id' => $quote->id,
        ]);
    }

    /**
     * Generate invoice from order. Invoice must be identical.
     *
     * @return void
     */
    public function testCreateInvoiceFromOrder()
    {
        // Arrange
        SaleStatementType::create([
            'code' => 'order',
            'name' => 'Order',
        ]);

        SaleStatementType::create([
            'code' => 'invoice',
            'name' => 'Invoice',
        ]);

        list($order, $orderItems, $orderTaxes) = $this->createSaleStatement('order');

        // Act
        $invoice = (new Replicate($order))->asInvoice();

        // Assert
        $this->assertInstanceOf(SaleStatement::class, $invoice);
        $this->assertEquals('invoice', $invoice->type);

        $invoiceItems = $invoice->items()->get();
        $invoiceTaxes = $invoice->taxes()->get();

        $this->assertCount(2, $invoiceItems);
        $this->assertCount(2, $invoiceTaxes);

        $this->assertSaleStatementWasCreated($order, $orderItems, $orderTaxes, 'order');
        $this->assertSaleStatementWasCreated($invoice, $invoiceItems, $invoiceTaxes, 'invoice');

        $this->assertDatabaseHas('sale_statement_invoice', [
            'sale_statement_id' => $invoice->id,
            'sale_statement_order_id' => $order->id,
        ]);
    }

    /**
     * Create sale statement.
     *
     * @param string $type
     * @return \Gerardojbaez\SaleStatements\Models\SaleStatement
     */
    protected function createSaleStatement($type)
    {
        $statement = SaleStatement::create($type, [
            'discounts' => 2000,
        ]);

        $statement->addresses()->create([
            'is_shipping' => true,
            'is_billing' => true,
            'line_1' => '711-2880 Nulla St',
            'line_2' => 'second line...',
            'city' => 'Mankato',
            'province' => 'Mississipi',
            'country' => 'United States',
            'zip' => 96522,
            'first_name' => 'Cecilia',
            'last_name' => 'Chapman',
            'province_code' => 'MS',
            'country_code' => 'US',
            'organization' => 'Acme Co.',
        ]);

        $items = $statement->items()->createMany([
            [
                'name' => 'Software license',
                'price' => 14900,
                'quantity' => 2,
            ], [
                'name' => 'Professional installation',
                'price' => 4900,
                'quantity' => 1,
            ]
        ]);

        $taxes = $statement->taxes()->createMany([
            [
                'name' => 'PR State',
                'rate' => 0.105,
            ], [
                'name' => 'PR Mun (Juana Díaz)',
                'rate' => 0.01,
            ]
        ]);

        // Taxes must be associated with items, otherwise they won't be applied.
        $items->first()->taxes()->attach($taxes->first());

        return [$statement, $items, $taxes];
    }

    /**
     * Assert the sale statement was created.
     *
     * @param \Gerardojbaez\SaleStatements\Models\SaleStatement $statement
     * @param string $type
     * @return void
     */
    protected function assertSaleStatementWasCreated($statement, $items, $taxes, $type)
    {
        $this->assertDatabaseHas('sale_statements', [
            'id' => $statement->id,
            'type' => $type,
            'discounts' => 2000,
        ]);

        $this->assertDatabaseHas("sale_statement_{$type}", [
            'sale_statement_id' => $statement->id,
        ]);

        $this->assertDatabaseHas('sale_statement_addresses', [
            'sale_statement_id' => $statement->id,
            'is_shipping' => true,
            'is_billing' => true,
            'line_1' => '711-2880 Nulla St',
            'line_2' => 'second line...',
            'city' => 'Mankato',
            'province' => 'Mississipi',
            'country' => 'United States',
            'zip' => 96522,
            'first_name' => 'Cecilia',
            'last_name' => 'Chapman',
            'province_code' => 'MS',
            'country_code' => 'US',
            'organization' => 'Acme Co.',
        ]);

        $this->assertDatabaseHas('sale_statement_items', [
            'sale_statement_id' => $statement->id,
            'name' => 'Software license',
            'price' => 14900,
            'quantity' => 2,
        ]);

        $this->assertDatabaseHas('sale_statement_items', [
            'sale_statement_id' => $statement->id,
            'name' => 'Professional installation',
            'price' => 4900,
            'quantity' => 1,
        ]);

        $this->assertDatabaseHas('sale_statement_taxes', [
            'sale_statement_id' => $statement->id,
            'name' => 'PR State',
            'rate' => 0.105,
        ]);

        $this->assertDatabaseHas('sale_statement_taxes', [
            'sale_statement_id' => $statement->id,
            'name' => 'PR Mun (Juana Díaz)',
            'rate' => 0.01,
        ]);

        $this->assertDatabaseHas('sale_statement_item_tax', [
            'sale_statement_item_id' => $items->first()->id,
            'sale_statement_tax_id' => $taxes->first()->id,
        ]);

        $statement = $statement->find($statement->id);

        $this->assertEquals(34700, $statement->subtotal);
        $this->assertEquals(3434, $statement->total_tax);
        $this->assertEquals(667, $statement->discount_per_item);
        $this->assertEquals(36134, $statement->total);
        $this->assertEquals(3, $statement->items_count);

        foreach ($items as $item) {
            $item = $item->find($item->id);

            $this->assertEquals($item->price * $item->quantity, $item->amount);
        }
    }
}
