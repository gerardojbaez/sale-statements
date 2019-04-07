<?php

namespace Gerardojbaez\SaleStatements\Tests\Unit;

use Mockery;
use Illuminate\Support\Collection;
use Gerardojbaez\SaleStatements\Calculator;
use Gerardojbaez\SaleStatements\Tests\TestCase;
use Gerardojbaez\SaleStatements\Models\SaleStatement;
use Gerardojbaez\SaleStatements\Models\SaleStatementTax;
use Gerardojbaez\SaleStatements\Models\SaleStatementItem;
use Gerardojbaez\SaleStatements\Models\SaleStatementDiscount;

class CalculatorTest extends TestCase
{
    public function testGetItemsCount()
    {
        // Arrange
        $collection = Mockery::mock(Collection::class);
        $collection->shouldReceive('sum')->with('quantity')->andReturn(3);

        $statement = Mockery::mock(SaleStatement::class);
        $statement->shouldReceive('getAttribute')->with('items')->andReturn($collection);

        $calculator = new Calculator($statement);

        // Act
        $result = $calculator->getItemsCount();

        // Assert
        $this->assertSame(3, $result);
    }

    public function testGetSubtotal()
    {
        // Arrange
        $items = $this->mockItems();

        $statement = Mockery::mock(SaleStatement::class);
        $statement->shouldReceive('getAttribute')->with('items')->andReturn($items);

        $calculator = new Calculator($statement);

        // Act
        $result = $calculator->getSubtotal();

        // Assert
        $this->assertSame(350, $result);
    }

    public function testGetTotalDiscount()
    {
        // Arrange
        $discounts = $this->mockDiscounts();

        $statement = Mockery::mock(SaleStatement::class);
        $statement->shouldReceive('getAttribute')->with('discounts')->andReturn($discounts);

        $calculator = new Calculator($statement);

        // Act
        $result = $calculator->getTotalDiscount();

        // Assert
        $this->assertSame(250, $result);
    }

    public function testGetTotalGlobalDiscount()
    {
        // Arrange
        $discounts = $this->mockGlobalDiscounts();

        $statement = Mockery::mock(SaleStatement::class);
        $statement->shouldReceive('getAttribute')->with('discounts')->andReturn($discounts);

        $calculator = new Calculator($statement);

        // Act
        $result = $calculator->getTotalglobalDiscount();

        // Assert
        $this->assertSame(300, $result);
    }

    public function testGetGlobalDiscountPerItem()
    {
        // Arrange
        $discounts = $this->mockGlobalDiscounts();

        $items = Mockery::mock(Collection::class);
        $items->shouldReceive('sum')->with('quantity')->andReturn(7);

        $statement = Mockery::mock(SaleStatement::class);
        $statement->shouldReceive('getAttribute')->with('discounts')->andReturn($discounts);
        $statement->shouldReceive('getAttribute')->with('items')->andReturn($items);

        $calculator = new Calculator($statement);

        // Act
        $result = $calculator->getGlobalDiscountPerItem();

        // Assert
        $this->assertSame(43, $result);
    }

    public function testGetSubtotalAfterDiscount()
    {
        // Arrange
        $items = $this->mockItems();
        $discounts = [
            Mockery::mock(SaleStatementDiscount::class, function ($mock) {
                $mock->shouldReceive('getAttribute')->once()->with('discount')->andReturn(100);
                $mock->shouldReceive('getAttribute')->once()->with('discount')->andReturn(200);
            }),

            Mockery::mock(SaleStatementDiscount::class, function ($mock) {
                $mock->shouldReceive('getAttribute')->once()->with('discount')->andReturn(150);
                $mock->shouldReceive('getAttribute')->once()->with('discount')->andReturn(200);
            })
        ];

        $statement = Mockery::mock(SaleStatement::class);
        $statement->shouldReceive('getAttribute')->with('items')->andReturn($items);
        $statement->shouldReceive('getAttribute')->with('discounts')->andReturn($discounts);

        $calculator = new Calculator($statement);

        // Act
        $discountsLowerThanSubtotal = $calculator->getSubtotalAfterDiscount();
        $discountsHigherThanSubtotal = $calculator->getSubtotalAfterDiscount();

        // Assert
        $this->assertSame(100, $discountsLowerThanSubtotal);
        $this->assertSame(0, $discountsHigherThanSubtotal);
    }

    public function testGetTotalTax()
    {
        // Arrange
        $taxes = $this->mockTaxes();

        $statement = Mockery::mock(SaleStatement::class);
        $statement->shouldReceive('getAttribute')->with('taxes')->andReturn($taxes);

        $calculator = new Calculator($statement);

        // Act
        $result = $calculator->getTotalTax();

        // Assert
        $this->assertSame(250, $result);
    }

    public function testGetTotalGlobalTax()
    {
        // Arrange
        $taxes = $this->mockGlobalTaxes();

        $statement = Mockery::mock(SaleStatement::class);
        $statement->shouldReceive('getAttribute')->with('taxes')->andReturn($taxes);

        $calculator = new Calculator($statement);

        // Act
        $result = $calculator->getTotalGlobalTax();

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

        $calculator = new Calculator($statement);

        // Act
        $result = $calculator->getGlobalTaxPerItem();

        // Assert
        $this->assertSame(43, $result);
    }

    public function testGetTotal()
    {
        // Arrange
        $items = $this->mockItems();
        $taxes = $this->mockTaxes();
        $discounts = $this->mockDiscounts();

        $statement = Mockery::mock(SaleStatement::class);
        $statement->shouldReceive('getAttribute')->with('items')->andReturn($items);
        $statement->shouldReceive('getAttribute')->with('discounts')->andReturn($discounts);
        $statement->shouldReceive('getAttribute')->with('taxes')->andReturn($taxes);

        $calculator = new Calculator($statement);

        // Act
        $result = $calculator->getTotal();

        // Assert
        $this->assertSame(350, $result);
    }

    public function testTotalDiscountsForItem()
    {
        // Arrange
        $globalDiscounts = $this->mockGlobalDiscounts();
        $discounts = $this->mockDiscounts();

        $items = Mockery::mock(Collection::class);
        $items->shouldReceive('sum')->with('quantity')->andReturn(7);

        $item = Mockery::mock(SaleStatementItem::class);
        $item->shouldReceive('getAttribute')->with('discounts')->andReturn($discounts);

        $statement = Mockery::mock(SaleStatement::class);
        $statement->shouldReceive('getAttribute')->with('discounts')->andReturn($globalDiscounts + $discounts);
        $statement->shouldReceive('getAttribute')->with('items')->andReturn($items);

        $calculator = new Calculator($statement);

        // Act
        $result = $calculator->getTotalDiscountsForItem($item);

        // Assert
        $this->assertSame(168, $result);
    }

    public function testTotalTaxForItem()
    {
        // Arrange
        $globalTaxes = $this->mockGlobalTaxes();
        $taxes = $this->mockTaxes();

        $items = Mockery::mock(Collection::class);
        $items->shouldReceive('sum')->with('quantity')->andReturn(7);

        $item = Mockery::mock(SaleStatementItem::class);
        $item->shouldReceive('getAttribute')->with('taxes')->andReturn($taxes);

        $statement = Mockery::mock(SaleStatement::class);
        $statement->shouldReceive('getAttribute')->with('taxes')->andReturn($globalTaxes + $taxes);
        $statement->shouldReceive('getAttribute')->with('items')->andReturn($items);

        $calculator = new Calculator($statement);

        // Act
        $result = $calculator->getTotalTaxForItem($item);

        // Assert
        $this->assertSame(168, $result);
    }

    protected function mockItems()
    {
        return [
            Mockery::mock(SaleStatementItem::class, function ($mock) {
                $mock->shouldReceive('getAttribute')->with('price')->andReturn(100);
                $mock->shouldReceive('getAttribute')->with('quantity')->andReturn(2);
            }),

            Mockery::mock(SaleStatementItem::class, function ($mock) {
                $mock->shouldReceive('getAttribute')->with('price')->andReturn(150);
                $mock->shouldReceive('getAttribute')->with('quantity')->andReturn(1);
            })
        ];
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
