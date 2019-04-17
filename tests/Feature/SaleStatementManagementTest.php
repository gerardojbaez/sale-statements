<?php

namespace Gerardojbaez\SaleStatements\Tests\Feature;

use Illuminate\Support\Facades\DB;
use Gerardojbaez\SaleStatements\Replicate;
use Gerardojbaez\SaleStatements\Calculator;
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
        list($statement, $items, $taxes, $discounts) = $this->createSaleStatement('quote');

        // Assert
        $this->assertSaleStatementWasCreated($statement,  $items, $taxes, 'quote', $discounts);
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
        list($statement, $items, $taxes, $discounts) = $this->createSaleStatement('order');

        // Assert
        $this->assertSaleStatementWasCreated($statement, $items, $taxes, 'order', $discounts);
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
        list($statement, $items, $taxes, $discounts) = $this->createSaleStatement('invoice');

        $payment = $statement->invoice->payments()->create([
            'amount_applied' => 5000
        ]);

        $statement->invoice->payments()->create([
            'amount_applied' => 10000
        ]);

        // Assert
        $this->assertSaleStatementWasCreated($statement, $items, $taxes, 'invoice', $discounts);

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
        list($statement, $items, $taxes, $discounts) = $this->createSaleStatement('credit_memo');

        // Assert
        $this->assertSaleStatementWasCreated($statement, $items, $taxes, 'credit_memo', $discounts);
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

        list($quote, $quoteItems, $quoteTaxes, $quoteDiscounts) = $this->createSaleStatement('quote');

        // Act
        $order = (new Replicate($quote))->asOrder();

        // Assert
        $this->assertInstanceOf(SaleStatement::class, $order);
        $this->assertEquals('order', $order->type);

        $orderItems = $order->items()->get();
        $orderTaxes = $order->taxes()->get();
        $orderDiscounts = $order->discounts()->get();

        $this->assertCount(2, $orderItems);
        $this->assertCount(3, $orderTaxes);

        $this->assertSaleStatementWasCreated($quote, $quoteItems, $quoteTaxes, 'quote', $quoteDiscounts);
        $this->assertSaleStatementWasCreated($order, $orderItems, $orderTaxes, 'order', $orderDiscounts);

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

        list($order, $orderItems, $orderTaxes, $orderDiscounts) = $this->createSaleStatement('order');

        // Act
        $invoice = (new Replicate($order))->asInvoice();

        // Assert
        $this->assertInstanceOf(SaleStatement::class, $invoice);
        $this->assertEquals('invoice', $invoice->type);

        $invoiceItems = $invoice->items()->get();
        $invoiceTaxes = $invoice->taxes()->get();
        $invoiceDiscounts = $invoice->discounts()->get();

        $this->assertCount(2, $invoiceItems);
        $this->assertCount(3, $invoiceTaxes);

        $this->assertSaleStatementWasCreated($order, $orderItems, $orderTaxes, 'order', $orderDiscounts);
        $this->assertSaleStatementWasCreated($invoice, $invoiceItems, $invoiceTaxes, 'invoice', $invoiceDiscounts);

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
        $statement = SaleStatement::create($type);

        $statement->addresses()->create([
            'is_shipping' => true,
            'is_billing' => true,
            'line_1' => '711-2880 Nulla St',
            'line_2' => 'second line...',
            'locality' => 'Mankato', // City, Town, Municipality, etc...
            'administrative_area' => 'Mississipi', // State, Province, Region, etc...
            'country_code' => 'US',
            'postalcode' => 96522,
            'given_name' => 'Cecilia', // i.e., first name
            'additional_name' => 'J.', // Can be used to hold a middle name, or a patronymic.
            'family_name' => 'Chapman', // i.e., last name
            'organization' => 'Acme Co.',
        ]);

        $items = $statement->items()->createMany([
            [
                'name' => 'Software license',
                'price' => 14900,
                'quantity' => 2,
            ], [
                'name' => 'Professional installation (global tax only should be applied here)',
                'price' => 4900,
                'quantity' => 1,
            ]
        ]);

        $discounts = $statement->discounts()->createMany([
            [
                'name' => 'Get 20% off of license!',
                'discount' => 20,
                'is_percentage' => true,
            ], [
                'name' => '$5 off of professional installation!',
                'discount' => 500,
                'is_percentage' => false,
            ], [
                'name' => '$5 off of everything!',
                'discount' => 500,
                'is_percentage' => false,
            ]
        ]);

        $items->first()->discounts()->attach($discounts->first());
        $items->last()->discounts()->attach($discounts->last());

        $taxes = $statement->taxes()->createMany([
            [
                'name' => 'PR State',
                'rate' => 0.105,
                'amount' => 2486
            ], [
                'name' => 'PR Mun (Juana Díaz)',
                'rate' => 0.01,
                'amount' => 237
            ], [
                'name' => 'Global tax example',
                'rate' => 0.01,
                'amount' => 275
            ]
        ]);

        $items->first()->taxes()->attach($taxes[0]);
        $items->first()->taxes()->attach($taxes[1]);

        return [$statement, $items, $taxes, $discounts];
    }

    /**
     * Assert the sale statement was created.
     *
     * @param \Gerardojbaez\SaleStatements\Models\SaleStatement $statement
     * @param string $type
     * @return void
     */
    protected function assertSaleStatementWasCreated($statement, $items, $taxes, $type, $discounts)
    {
        $this->assertDatabaseHas('sale_statements', [
            'id' => $statement->id,
            'type' => $type,
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
            'locality' => 'Mankato',
            'administrative_area' => 'Mississipi',
            'country_code' => 'US',
            'postalcode' => 96522,
            'given_name' => 'Cecilia',
            'additional_name' => 'J.',
            'family_name' => 'Chapman',
            'organization' => 'Acme Co.',
        ]);

        $this->assertDatabaseHas('sale_statement_discounts', [
            'sale_statement_id' => $statement->id,
            'name' => 'Get 20% off of license!',
            'is_percentage' => true,
            'discount' => 20
        ]);

        $this->assertDatabaseHas('sale_statement_discounts', [
            'sale_statement_id' => $statement->id,
            'name' => '$5 off of professional installation!',
            'is_percentage' => false,
            'discount' => 500
        ]);

        $this->assertDatabaseHas('sale_statement_discounts', [
            'sale_statement_id' => $statement->id,
            'name' => '$5 off of everything!',
            'is_percentage' => false,
            'discount' => 500
        ]);

        $this->assertDatabaseHas('sale_statement_discount_item', [
            'sale_statement_discount_id' => $discounts->first()->id,
            'sale_statement_item_id' => $items->first()->id,
        ]);

        $this->assertDatabaseHas('sale_statement_discount_item', [
            'sale_statement_discount_id' => $discounts->last()->id,
            'sale_statement_item_id' => $items->last()->id,
        ]);

        $this->assertDatabaseHas('sale_statement_items', [
            'sale_statement_id' => $statement->id,
            'name' => 'Software license',
            'price' => 14900,
            'quantity' => 2,
        ]);

        $this->assertDatabaseHas('sale_statement_items', [
            'sale_statement_id' => $statement->id,
            'name' => 'Professional installation (global tax only should be applied here)',
            'price' => 4900,
            'quantity' => 1,
        ]);

        $this->assertDatabaseHas('sale_statement_taxes', [
            'sale_statement_id' => $statement->id,
            'name' => 'PR State',
            'rate' => 0.105,
            'amount' => 2486
        ]);

        $this->assertDatabaseHas('sale_statement_taxes', [
            'sale_statement_id' => $statement->id,
            'name' => 'PR Mun (Juana Díaz)',
            'rate' => 0.01,
            'amount' => 237
        ]);

        $this->assertDatabaseHas('sale_statement_taxes', [
            'sale_statement_id' => $statement->id,
            'name' => 'Global tax example',
            'rate' => 0.01,
            'amount' => 275
        ]);

        $this->assertDatabaseHas('sale_statement_item_tax', [
            'sale_statement_item_id' => $items->first()->id,
            'sale_statement_tax_id' => $taxes->first()->id,
        ]);

        $calculator = new Calculator($statement);

        $this->assertEquals(34700, $calculator->getSubtotal());
        $this->assertEquals(6961, $calculator->getTotalDiscount());
        $this->assertEquals(500, $calculator->getTotalGlobalDiscount());
        $this->assertEquals(167, $calculator->getGlobalDiscountPerItem());
        $this->assertEquals(27906, $calculator->getSubtotalAfterDiscount());
        $this->assertEquals(2998, $calculator->getTotalTax());
        $this->assertEquals(275, $calculator->getTotalGlobalTax());
        $this->assertEquals(92, $calculator->getGlobalTaxPerItem());
        $this->assertEquals(30904, $calculator->getTotal());
    }
}
