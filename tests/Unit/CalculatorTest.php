<?php

namespace Gerardojbaez\SaleStatements\Tests\Unit;

use Mockery;
use Illuminate\Support\Collection;
use Gerardojbaez\SaleStatements\Calculator;
use Gerardojbaez\SaleStatements\Tests\TestCase;
use Gerardojbaez\SaleStatements\Models\SaleStatement;
use Gerardojbaez\SaleStatements\Models\SaleStatementTax;
use Gerardojbaez\SaleStatements\Models\SaleStatementItem;
use Gerardojbaez\SaleStatements\Models\SaleStatementOrder;
use Gerardojbaez\SaleStatements\Models\SaleStatementInvoice;
use Gerardojbaez\SaleStatements\Models\SaleStatementDiscount;
use Gerardojbaez\SaleStatements\Models\SaleStatementQuote;

class CalculatorTest extends TestCase
{
    public function testGetItemsCount()
    {
        // Arrange
        $collection = Mockery::mock(Collection::class);
        $collection->shouldReceive('sum')->with('quantity')->andReturn(3);

        $statement = Mockery::mock(SaleStatement::class);
        $statement->shouldReceive('getAttribute')->with('items')->andReturn($collection);

        $calculator = new Calculator();

        // Act
        $result = $calculator->getItemsCount($statement);

        // Assert
        $this->assertSame(3, $result);
    }

    public function testGetSubtotal()
    {
        // Arrange
        $items = $this->mockItems();

        $statement = Mockery::mock(SaleStatement::class);
        $statement->shouldReceive('getAttribute')->with('items')->andReturn($items);

        $calculator = new Calculator();

        // Act
        $result = $calculator->getSubtotal($statement);

        // Assert
        $this->assertSame(350, $result);
    }

    public function testGetTotalDiscount()
    {
        // Arrange
        $discountItemCollection = Mockery::mock(Collection::class);
        $discountItemCollection->shouldReceive('sum')->with('quantity')->andReturn(3);

        $discounts = collect([
            Mockery::mock(SaleStatementDiscount::class, function ($mock) use ($discountItemCollection) {
                $mock->shouldReceive('getAttribute')->with('is_percentage')->andReturn(true);
                $mock->shouldReceive('getAttribute')->with('discount')->andReturn(20);
                $mock->shouldReceive('getAttribute')->with('items')->andReturn($discountItemCollection);
            }),

            Mockery::mock(SaleStatementDiscount::class, function ($mock) use ($discountItemCollection) {
                $mock->shouldReceive('getAttribute')->with('is_percentage')->andReturn(false);
                $mock->shouldReceive('getAttribute')->with('discount')->andReturn(150);
                $mock->shouldReceive('getAttribute')->with('items')->andReturn($discountItemCollection);
            })
        ]);

        $statement = Mockery::mock(SaleStatement::class);

        $items = collect([
            Mockery::mock(SaleStatementItem::class, function ($mock) use ($discounts, $statement) {
                $mock->shouldReceive('offsetExists')->with('quantity')->andReturn(true);
                $mock->shouldReceive('offsetGet')->with('quantity')->andReturn(2);

                $mock->shouldReceive('getAttribute')->with('statement')->andReturn($statement);
                $mock->shouldReceive('getAttribute')->with('discounts')->andReturn($discounts);
                $mock->shouldReceive('getAttribute')->with('price')->andReturn(100);
                $mock->shouldReceive('getAttribute')->with('quantity')->andReturn(2);
            }),

            Mockery::mock(SaleStatementItem::class, function ($mock) use ($discounts, $statement) {
                $mock->shouldReceive('offsetExists')->with('quantity')->andReturn(true);
                $mock->shouldReceive('offsetGet')->with('quantity')->andReturn(1);

                $mock->shouldReceive('getAttribute')->with('statement')->andReturn($statement);
                $mock->shouldReceive('getAttribute')->with('discounts')->andReturn($discounts);
                $mock->shouldReceive('getAttribute')->with('price')->andReturn(150);
                $mock->shouldReceive('getAttribute')->with('quantity')->andReturn(1);
            })
        ]);

        $statement->shouldReceive('getAttribute')->with('items')->andReturn($items);
        $statement->shouldReceive('getAttribute')->with('discounts')->andReturn($discounts);

        $calculator = new Calculator();

        // Act
        $result = $calculator->getTotalDiscount($statement);

        // Assert
        $this->assertSame(170, $result);
    }

    public function testGetTotalGlobalDiscount()
    {
        // Arrange
        $discountItemCollection = Mockery::mock(Collection::class);
        $discountItemCollection->shouldReceive('sum')->with('quantity')->andReturn(0);

        $discounts = [
            Mockery::mock(SaleStatementDiscount::class, function ($mock) use ($discountItemCollection) {
                $mock->shouldReceive('getAttribute')->with('is_percentage')->andReturn(true);
                $mock->shouldReceive('getAttribute')->with('discount')->andReturn(20);
                $mock->shouldReceive('getAttribute')->with('items')->andReturn($discountItemCollection);
            }),

            Mockery::mock(SaleStatementDiscount::class, function ($mock) use ($discountItemCollection) {
                $mock->shouldReceive('getAttribute')->with('is_percentage')->andReturn(false);
                $mock->shouldReceive('getAttribute')->with('discount')->andReturn(150);
                $mock->shouldReceive('getAttribute')->with('items')->andReturn($discountItemCollection);
            })
        ];

        $items = [
            Mockery::mock(SaleStatementItem::class, function ($mock) {
                $mock->shouldReceive('getAttribute')->with('price')->andReturn(100);
                $mock->shouldReceive('getAttribute')->with('quantity')->andReturn(2);
            }),

            Mockery::mock(SaleStatementItem::class, function ($mock) {
                $mock->shouldReceive('getAttribute')->with('price')->andReturn(150);
                $mock->shouldReceive('getAttribute')->with('quantity')->andReturn(1);
            })
        ];

        $statement = Mockery::mock(SaleStatement::class);
        $statement->shouldReceive('getAttribute')->with('discounts')->andReturn($discounts);
        $statement->shouldReceive('getAttribute')->with('items')->andReturn($items);

        $calculator = new Calculator();

        // Act
        $result = $calculator->getTotalGlobalDiscount($statement);

        // Assert
        $this->assertSame(220, $result);
    }

    public function testGetGlobalDiscountPerItem()
    {
        // Arrange
        $discountItemCollection = Mockery::mock(Collection::class);
        $discountItemCollection->shouldReceive('sum')->with('quantity')->andReturn(0);

        $discounts = [
            Mockery::mock(SaleStatementDiscount::class, function ($mock) use ($discountItemCollection) {
                $mock->shouldReceive('getAttribute')->with('is_percentage')->andReturn(true);
                $mock->shouldReceive('getAttribute')->with('discount')->andReturn(20);
                $mock->shouldReceive('getAttribute')->with('items')->andReturn($discountItemCollection);
            }),

            Mockery::mock(SaleStatementDiscount::class, function ($mock) use ($discountItemCollection) {
                $mock->shouldReceive('getAttribute')->with('is_percentage')->andReturn(false);
                $mock->shouldReceive('getAttribute')->with('discount')->andReturn(150);
                $mock->shouldReceive('getAttribute')->with('items')->andReturn($discountItemCollection);
            })
        ];

        $items = collect([
            Mockery::mock(SaleStatementItem::class, function ($mock) {
                $mock->shouldReceive('offsetExists')->with('quantity')->andReturn(true);
                $mock->shouldReceive('offsetGet')->with('quantity')->andReturn(2);

                $mock->shouldReceive('getAttribute')->with('price')->andReturn(100);
                $mock->shouldReceive('getAttribute')->with('quantity')->andReturn(2);
            }),

            Mockery::mock(SaleStatementItem::class, function ($mock) {
                $mock->shouldReceive('offsetExists')->with('quantity')->andReturn(true);
                $mock->shouldReceive('offsetGet')->with('quantity')->andReturn(1);

                $mock->shouldReceive('getAttribute')->with('price')->andReturn(150);
                $mock->shouldReceive('getAttribute')->with('quantity')->andReturn(1);
            })
        ]);

        $statement = Mockery::mock(SaleStatement::class);
        $statement->shouldReceive('getAttribute')->with('discounts')->andReturn($discounts);
        $statement->shouldReceive('getAttribute')->with('items')->andReturn($items);

        $calculator = new Calculator();

        // Act
        $result = $calculator->getGlobalDiscountPerItem($statement);

        // Assert
        $this->assertSame(73, $result);
    }

    public function testGetGlobalDiscountPerItemReturnsZeroWhenNoItemsWereFound()
    {
        // Arrange
        $discountItemCollection = Mockery::mock(Collection::class);
        $discountItemCollection->shouldReceive('sum')->with('quantity')->andReturn(0);

        $discounts = [
            Mockery::mock(SaleStatementDiscount::class, function ($mock) use ($discountItemCollection) {
                $mock->shouldReceive('getAttribute')->with('is_percentage')->andReturn(true);
                $mock->shouldReceive('getAttribute')->with('discount')->andReturn(20);
                $mock->shouldReceive('getAttribute')->with('items')->andReturn($discountItemCollection);
            }),

            Mockery::mock(SaleStatementDiscount::class, function ($mock) use ($discountItemCollection) {
                $mock->shouldReceive('getAttribute')->with('is_percentage')->andReturn(false);
                $mock->shouldReceive('getAttribute')->with('discount')->andReturn(150);
                $mock->shouldReceive('getAttribute')->with('items')->andReturn($discountItemCollection);
            })
        ];

        $items = collect([]);

        $statement = Mockery::mock(SaleStatement::class);
        $statement->shouldReceive('getAttribute')->with('discounts')->andReturn($discounts);
        $statement->shouldReceive('getAttribute')->with('items')->andReturn($items);

        $calculator = new Calculator();

        // Act
        $result = $calculator->getGlobalDiscountPerItem($statement);

        // Assert
        $this->assertSame(0, $result);
    }

    public function testGetSubtotalAfterDiscount()
    {
        // Arrange
        $discountItemCollection = Mockery::mock(Collection::class);
        $discountItemCollection->shouldReceive('sum')->with('quantity')->andReturn(3);

        $discounts = collect([
            Mockery::mock(SaleStatementDiscount::class, function ($mock) use ($discountItemCollection) {
                $mock->shouldReceive('getAttribute')->with('is_percentage')->andReturn(true);
                $mock->shouldReceive('getAttribute')->times(2)->with('discount')->andReturn(20);
                $mock->shouldReceive('getAttribute')->times(2)->with('discount')->andReturn(80);
                $mock->shouldReceive('getAttribute')->with('items')->andReturn($discountItemCollection);
            }),

            Mockery::mock(SaleStatementDiscount::class, function ($mock) use ($discountItemCollection) {
                $mock->shouldReceive('getAttribute')->with('is_percentage')->andReturn(false);
                $mock->shouldReceive('getAttribute')->times(2)->with('discount')->andReturn(150);
                $mock->shouldReceive('getAttribute')->times(2)->with('discount')->andReturn(1000);
                $mock->shouldReceive('getAttribute')->with('items')->andReturn($discountItemCollection);
            })
        ]);

        $statement = Mockery::mock(SaleStatement::class);

        $items = collect([
            Mockery::mock(SaleStatementItem::class, function ($mock) use ($discounts, $statement) {
                $mock->shouldReceive('offsetExists')->with('quantity')->andReturn(true);
                $mock->shouldReceive('offsetGet')->with('quantity')->andReturn(2);

                $mock->shouldReceive('getAttribute')->with('statement')->andReturn($statement);
                $mock->shouldReceive('getAttribute')->with('discounts')->andReturn($discounts);
                $mock->shouldReceive('getAttribute')->with('price')->andReturn(100);
                $mock->shouldReceive('getAttribute')->with('quantity')->andReturn(2);
            }),

            Mockery::mock(SaleStatementItem::class, function ($mock) use ($discounts, $statement) {
                $mock->shouldReceive('offsetExists')->with('quantity')->andReturn(true);
                $mock->shouldReceive('offsetGet')->with('quantity')->andReturn(1);

                $mock->shouldReceive('getAttribute')->with('statement')->andReturn($statement);
                $mock->shouldReceive('getAttribute')->with('discounts')->andReturn($discounts);
                $mock->shouldReceive('getAttribute')->with('price')->andReturn(150);
                $mock->shouldReceive('getAttribute')->with('quantity')->andReturn(1);
            })
        ]);

        $statement->shouldReceive('getAttribute')->with('items')->andReturn($items);
        $statement->shouldReceive('getAttribute')->with('discounts')->andReturn($discounts);

        $calculator = new Calculator();

        // Act
        $discountsLowerThanSubtotal = $calculator->getSubtotalAfterDiscount($statement);
        $discountsHigherThanSubtotal = $calculator->getSubtotalAfterDiscount($statement);

        // Assert
        $this->assertSame(180, $discountsLowerThanSubtotal);
        $this->assertSame(0, $discountsHigherThanSubtotal);
    }

    public function testGetTotalTax()
    {
        // Arrange
        $taxes = $this->mockTaxes();

        $statement = Mockery::mock(SaleStatement::class);
        $statement->shouldReceive('getAttribute')->with('taxes')->andReturn($taxes);

        $calculator = new Calculator();

        // Act
        $result = $calculator->getTotalTax($statement);

        // Assert
        $this->assertSame(250, $result);
    }

    public function testGetTotalGlobalTax()
    {
        // Arrange
        $taxes = $this->mockGlobalTaxes();

        $statement = Mockery::mock(SaleStatement::class);
        $statement->shouldReceive('getAttribute')->with('taxes')->andReturn($taxes);

        $calculator = new Calculator();

        // Act
        $result = $calculator->getTotalGlobalTax($statement);

        // Assert
        $this->assertSame(300, $result);
    }

    public function testGetGlobalTaxPerItem()
    {
        // Arrange
        $items = Mockery::mock(Collection::class);
        $items->shouldReceive('sum')->with('quantity')->andReturn(7);

        $taxes = $this->mockGlobalTaxes();

        $statement = Mockery::mock(SaleStatement::class);
        $statement->shouldReceive('getAttribute')->with('items')->andReturn($items);
        $statement->shouldReceive('getAttribute')->with('taxes')->andReturn($taxes);

        $calculator = new Calculator();

        // Act
        $result = $calculator->getGlobalTaxPerItem($statement);

        // Assert
        $this->assertSame(43, $result);
    }

    public function testGetTotal()
    {
        // Arrange
        $discountItemCollection = Mockery::mock(Collection::class);
        $discountItemCollection->shouldReceive('sum')->with('quantity')->andReturn(3);

        $discounts = collect([
            Mockery::mock(SaleStatementDiscount::class, function ($mock) use ($discountItemCollection) {
                $mock->shouldReceive('getAttribute')->with('is_percentage')->andReturn(true);
                $mock->shouldReceive('getAttribute')->with('discount')->andReturn(20);
                $mock->shouldReceive('getAttribute')->with('items')->andReturn($discountItemCollection);
            }),

            Mockery::mock(SaleStatementDiscount::class, function ($mock) use ($discountItemCollection) {
                $mock->shouldReceive('getAttribute')->with('is_percentage')->andReturn(false);
                $mock->shouldReceive('getAttribute')->with('discount')->andReturn(150);
                $mock->shouldReceive('getAttribute')->with('items')->andReturn($discountItemCollection);
            })
        ]);

        $statement = Mockery::mock(SaleStatement::class);

        $items = collect([
            Mockery::mock(SaleStatementItem::class, function ($mock) use ($discounts, $statement) {
                $mock->shouldReceive('offsetExists')->with('quantity')->andReturn(true);
                $mock->shouldReceive('offsetGet')->with('quantity')->andReturn(2);

                $mock->shouldReceive('getAttribute')->with('statement')->andReturn($statement);
                $mock->shouldReceive('getAttribute')->with('discounts')->andReturn($discounts);
                $mock->shouldReceive('getAttribute')->with('price')->andReturn(100);
                $mock->shouldReceive('getAttribute')->with('quantity')->andReturn(2);
            }),

            Mockery::mock(SaleStatementItem::class, function ($mock) use ($discounts, $statement) {
                $mock->shouldReceive('offsetExists')->with('quantity')->andReturn(true);
                $mock->shouldReceive('offsetGet')->with('quantity')->andReturn(1);

                $mock->shouldReceive('getAttribute')->with('statement')->andReturn($statement);
                $mock->shouldReceive('getAttribute')->with('discounts')->andReturn($discounts);
                $mock->shouldReceive('getAttribute')->with('price')->andReturn(150);
                $mock->shouldReceive('getAttribute')->with('quantity')->andReturn(1);
            })
        ]);

        $taxes = $this->mockTaxes();

        $statement->shouldReceive('getAttribute')->with('items')->andReturn($items);
        $statement->shouldReceive('getAttribute')->with('discounts')->andReturn($discounts);
        $statement->shouldReceive('getAttribute')->with('taxes')->andReturn($taxes);

        $calculator = new Calculator();

        // Act
        $result = $calculator->getTotal($statement);

        // Assert
        $this->assertSame(430, $result);
    }

    public function testTotalDiscountsForItem()
    {
        // Arrange
        $discountItemCollection = Mockery::mock(Collection::class);
        $discountItemCollection->shouldReceive('sum')->with('quantity')->andReturn(3);

        $globalDiscounts = collect([
            Mockery::mock(SaleStatementDiscount::class, function ($mock) use ($discountItemCollection) {
                $mock->shouldReceive('getAttribute')->with('is_percentage')->andReturn(true);
                $mock->shouldReceive('getAttribute')->with('discount')->andReturn(20);
                $mock->shouldReceive('getAttribute')->with('items')->andReturn(collect([
                    ['quantity' => 0]
                ]));
            }),
        ]);

        $discounts = collect([
            Mockery::mock(SaleStatementDiscount::class, function ($mock) use ($discountItemCollection) {
                $mock->shouldReceive('getAttribute')->with('is_percentage')->andReturn(true);
                $mock->shouldReceive('getAttribute')->with('discount')->andReturn(20);
                $mock->shouldReceive('getAttribute')->with('items')->andReturn($discountItemCollection);
            }),

            Mockery::mock(SaleStatementDiscount::class, function ($mock) use ($discountItemCollection) {
                $mock->shouldReceive('getAttribute')->with('is_percentage')->andReturn(false);
                $mock->shouldReceive('getAttribute')->with('discount')->andReturn(150);
                $mock->shouldReceive('getAttribute')->with('items')->andReturn($discountItemCollection);
            })
        ]);

        $statement = Mockery::mock(SaleStatement::class);

        $items = collect([
            Mockery::mock(SaleStatementItem::class, function ($mock) use ($discounts, $statement) {
                $mock->shouldReceive('offsetExists')->with('quantity')->andReturn(true);
                $mock->shouldReceive('offsetGet')->with('quantity')->andReturn(2);

                $mock->shouldReceive('getAttribute')->with('statement')->andReturn($statement);
                $mock->shouldReceive('getAttribute')->with('discounts')->andReturn($discounts);
                $mock->shouldReceive('getAttribute')->with('price')->andReturn(100);
                $mock->shouldReceive('getAttribute')->with('quantity')->andReturn(2);
            }),

            Mockery::mock(SaleStatementItem::class, function ($mock) use ($discounts, $statement) {
                $mock->shouldReceive('offsetExists')->with('quantity')->andReturn(true);
                $mock->shouldReceive('offsetGet')->with('quantity')->andReturn(1);

                $mock->shouldReceive('getAttribute')->with('statement')->andReturn($statement);
                $mock->shouldReceive('getAttribute')->with('discounts')->andReturn($discounts);
                $mock->shouldReceive('getAttribute')->with('price')->andReturn(150);
                $mock->shouldReceive('getAttribute')->with('quantity')->andReturn(1);
            })
        ]);

        $statement->shouldReceive('getAttribute')->with('discounts')->andReturn($discounts->merge($globalDiscounts));
        $statement->shouldReceive('getAttribute')->with('items')->andReturn($items);

        $calculator = new Calculator();

        // Act
        $result = $calculator->getTotalDiscountsForItem($items[0]);

        // Assert
        $this->assertSame(136, $result);
    }

    public function testTotalTaxForItem()
    {
        // Arrange
        $globalTaxes = $this->mockGlobalTaxes();
        $taxes = $this->mockTaxes();

        $items = Mockery::mock(Collection::class);
        $items->shouldReceive('sum')->with('quantity')->andReturn(7);

        $statement = Mockery::mock(SaleStatement::class);

        $item = Mockery::mock(SaleStatementItem::class);
        $item->shouldReceive('getAttribute')->with('statement')->andReturn($statement);
        $item->shouldReceive('getAttribute')->with('taxes')->andReturn($taxes);
        $item->shouldReceive('getAttribute')->with('quantity')->andReturn(2);

        $statement->shouldReceive('getAttribute')->with('taxes')->andReturn($globalTaxes + $taxes);
        $statement->shouldReceive('getAttribute')->with('items')->andReturn($items);

        $calculator = new Calculator();

        // Act
        $result = $calculator->getTotalTaxForItem($item);

        // Assert
        $this->assertSame(211, $result);
    }

    public function testGetTotalPaidOfInvoice()
    {
        // Arrange

        $paymentsMock = Mockery::mock(Collection::class);
        $paymentsMock->shouldReceive('sum')->with('amount_applied')->andReturn(100);
        $paymentsMock->shouldReceive('filter')->andReturnSelf();
        $paymentsMock->shouldReceive('isApplicable')->andReturnSelf();

        $invoiceMock = Mockery::mock(SaleStatementInvoice::class);
        $invoiceMock->shouldReceive('getAttribute')->with('payments')->andReturn($paymentsMock);

        $statementMock = Mockery::mock(SaleStatement::class);
        $statementMock->shouldReceive('isInvoice')->andReturn(true);
        $statementMock->shouldReceive('getAttribute')->with('invoice')->andReturn($invoiceMock);

        $calculator = new Calculator;

        // Act
        $result = $calculator->getTotalPaid($statementMock);

        // Assert
        $this->assertSame(100, $result);
    }

    public function testGetTotalPaidOfOrder()
    {
        // Arrange

        $paymentsMock = Mockery::mock(Collection::class);
        $paymentsMock->shouldReceive('sum')->with('amount_applied')->andReturn(100);
        $paymentsMock->shouldReceive('filter')->andReturnSelf();
        $paymentsMock->shouldReceive('isApplicable')->andReturnSelf();

        $invoiceMock = Mockery::mock(SaleStatementInvoice::class);
        $invoiceMock->shouldReceive('getAttribute')->with('payments')->andReturn($paymentsMock);

        $orderMock = Mockery::mock(SaleStatementOrder::class);
        $orderMock->shouldReceive('getAttribute')->with('invoices')->andReturn([
            $invoiceMock, $invoiceMock
        ]);

        $statementMock = Mockery::mock(SaleStatement::class);
        $statementMock->shouldReceive('isInvoice')->andReturn(false);
        $statementMock->shouldReceive('isOrder')->andReturn(true);
        $statementMock->shouldReceive('getAttribute')->with('order')->andReturn($orderMock);

        $calculator = new Calculator;

        // Act
        $result = $calculator->getTotalPaid($statementMock);

        // Assert
        $this->assertSame(200, $result);
    }

    public function testGetTotalPaidOfQuote()
    {
        // Arrange

        $paymentsMock = Mockery::mock(Collection::class);
        $paymentsMock->shouldReceive('sum')->with('amount_applied')->andReturn(100);
        $paymentsMock->shouldReceive('filter')->andReturnSelf();
        $paymentsMock->shouldReceive('isApplicable')->andReturnSelf();

        $invoiceMock = Mockery::mock(SaleStatementInvoice::class);
        $invoiceMock->shouldReceive('getAttribute')->with('payments')->andReturn($paymentsMock);

        $orderMock = Mockery::mock(SaleStatementOrder::class);
        $orderMock->shouldReceive('getAttribute')->with('invoices')->andReturn([
            $invoiceMock, $invoiceMock
        ]);

        $quoteMock = Mockery::mock(SaleStatementQuote::class);
        $quoteMock->shouldReceive('getAttribute')->with('orders')->andReturn([
            $orderMock, $orderMock
        ]);

        $statementMock = Mockery::mock(SaleStatement::class);
        $statementMock->shouldReceive('isInvoice')->andReturn(false);
        $statementMock->shouldReceive('isOrder')->andReturn(false);
        $statementMock->shouldReceive('isQuote')->andReturn(true);
        $statementMock->shouldReceive('getAttribute')->with('quote')->andReturn($quoteMock);

        $calculator = new Calculator;

        // Act
        $result = $calculator->getTotalPaid($statementMock);

        // Assert
        $this->assertSame(400, $result);
    }

    protected function mockItems()
    {
        return collect([
            Mockery::mock(SaleStatementItem::class, function ($mock) {
                $mock->shouldReceive('getAttribute')->with('price')->andReturn(100);
                $mock->shouldReceive('getAttribute')->with('quantity')->andReturn(2);

                $mock->shouldReceive('offsetExists')->with('quantity')->andReturn(true);
                $mock->shouldReceive('offsetGet')->with('quantity')->andReturn(2);
            }),

            Mockery::mock(SaleStatementItem::class, function ($mock) {
                $mock->shouldReceive('getAttribute')->with('price')->andReturn(150);
                $mock->shouldReceive('getAttribute')->with('quantity')->andReturn(1);

                $mock->shouldReceive('offsetExists')->with('quantity')->andReturn(true);
                $mock->shouldReceive('offsetGet')->with('quantity')->andReturn(2);
            })
        ]);
    }

    protected function mockDiscounts()
    {
        $collection = Mockery::mock(Collection::class);
        $collection->shouldReceive('sum')->with('quantity')->andReturn(2);

        return [
            Mockery::mock(SaleStatementDiscount::class, function ($mock) use ($collection) {
                $mock->shouldReceive('getAttribute')->with('discount')->andReturn(100);
                $mock->shouldReceive('getAttribute')->with('items')->andReturn($collection);
            }),

            Mockery::mock(SaleStatementDiscount::class, function ($mock) use ($collection) {
                $mock->shouldReceive('getAttribute')->with('discount')->andReturn(150);
                $mock->shouldReceive('getAttribute')->with('items')->andReturn($collection);
            })
        ];
    }

    protected function mockGlobalDiscounts()
    {
        $collection = Mockery::mock(Collection::class);
        $collection->shouldReceive('count')->twice()->andReturn(0);
        $collection->shouldReceive('count')->once()->andReturn(1);

        $discounts = [
            Mockery::mock(SaleStatementDiscount::class, function ($mock) use ($collection) {
                $mock->shouldReceive('getAttribute')->with('items')->andReturn($collection);
                $mock->shouldReceive('getAttribute')->once()->with('discount')->andReturn(100);
            }),

            Mockery::mock(SaleStatementDiscount::class, function ($mock) use ($collection) {
                $mock->shouldReceive('getAttribute')->with('items')->andReturn($collection);
                $mock->shouldReceive('getAttribute')->once()->with('discount')->andReturn(200);
            }),

            Mockery::mock(SaleStatementDiscount::class, function ($mock) use ($collection) {
                $mock->shouldReceive('getAttribute')->with('items')->andReturn($collection);
                $mock->shouldReceive('getAttribute')->never()->with('discount');
            })
        ];

        return $discounts;
    }

    protected function mockTaxes()
    {
        $collection = Mockery::mock(Collection::class);
        $collection->shouldReceive('sum')->with('quantity')->andReturn(2);

        return [
            Mockery::mock(SaleStatementTax::class, function ($mock) use ($collection) {
                $mock->shouldReceive('getAttribute')->with('amount')->andReturn(100);
                $mock->shouldReceive('getAttribute')->with('items')->andReturn($collection);
            }),

            Mockery::mock(SaleStatementTax::class, function ($mock) use ($collection) {
                $mock->shouldReceive('getAttribute')->with('amount')->andReturn(150);
                $mock->shouldReceive('getAttribute')->with('items')->andReturn($collection);
            })
        ];
    }

    protected function mockGlobalTaxes()
    {
        $collection = Mockery::mock(Collection::class);
        $collection->shouldReceive('count')->twice()->andReturn(0);
        $collection->shouldReceive('count')->once()->andReturn(1);

        $taxes = [
            Mockery::mock(SaleStatementTax::class, function ($mock) use ($collection) {
                $mock->shouldReceive('getAttribute')->with('items')->andReturn($collection);
                $mock->shouldReceive('getAttribute')->once()->with('amount')->andReturn(100);
            }),

            Mockery::mock(SaleStatementTax::class, function ($mock) use ($collection) {
                $mock->shouldReceive('getAttribute')->with('items')->andReturn($collection);
                $mock->shouldReceive('getAttribute')->once()->with('amount')->andReturn(200);
            }),

            Mockery::mock(SaleStatementTax::class, function ($mock) use ($collection) {
                $mock->shouldReceive('getAttribute')->with('items')->andReturn($collection);
                $mock->shouldReceive('getAttribute')->never()->with('amount');
            })
        ];

        return $taxes;
    }
}
