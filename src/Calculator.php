<?php

namespace Gerardojbaez\SaleStatements;

use Exception;
use Gerardojbaez\SaleStatements\Models\SaleStatement;
use Gerardojbaez\SaleStatements\Models\SaleStatementItem;

class Calculator implements CalculatorInterface
{
    /**
     * The sale statement being calculated.
     *
     * @var \Gerardojbaez\SaleStatements\Models\SaleStatement
     */
    protected $statement;

    /**
     * Create a new calculator instance.
     *
     * @param \Gerardojbaez\SaleStatements\Models\SaleStatement $statement
     */
    public function __construct(SaleStatement $statement)
    {
        $this->statement = $statement;
    }

    /**
     * Get the sum of all item quantities.
     *
     * @return int
     */
    public function getItemsCount()
    {
        return $this->statement->items->sum('quantity');
    }

    /**
     * Get the sum of all item prices without any discount or tax applied.
     *
     * @return int
     */
    public function getSubtotal()
    {
        $subtotal = 0;

        foreach ($this->statement->items as $item) {
            $subtotal += $item->price * $item->quantity;
        }

        return $subtotal;
    }

    /**
     * Get the sum of all discounts applied.
     *
     * @return int
     */
    public function getTotalDiscount()
    {
        $amount = 0;

        foreach ($this->statement->items as $item) {
            $amount += $this->getTotalDiscountsForItem($item);
        }

        return (int) round($amount);
    }

    /**
     * Get the sum of all global discounts (i.e., not associated to any
     * line item).
     *
     * @return int
     */
    public function getTotalGlobalDiscount()
    {
        $amount = 0;
        $subtotal = $this->getSubtotal();

        foreach ($this->statement->discounts as $discount) {
            if ($discount->items->sum('quantity') > 0) {
                continue;
            }

            if ($discount->is_percentage) {
                $amount += $subtotal * $discount->discount / 100;
            } else {
                $amount += $discount->discount;
            }
        }

        return $amount;
    }

    /**
     * Get global discount per item.
     *
     * @return int
     */
    public function getGlobalDiscountPerItem()
    {
        return (int) round($this->getTotalGlobalDiscount() / $this->getItemsCount());
    }

    /**
     * Get the subtotal minus the sum of all discounts.
     *
     * @return int
     */
    public function getSubtotalAfterDiscount()
    {
        $subtotal = $this->getSubtotal() - $this->getTotalDiscount();

        // Return 0 subtotal when discounts are higher than the subtotal amount.
        if ($subtotal < 0) {
            return 0;
        }

        return $subtotal;
    }

    /**
     * Get the sum of all tax amounts.
     *
     * @return void
     */
    public function getTotalTax()
    {
        $amount = 0;

        foreach ($this->statement->taxes as $tax) {
            $amount += $tax->amount;
        }

        return $amount;
    }

    /**
     * Get the sum of all taxes not associated with any line item.
     *
     * @return int
     */
    public function getTotalGlobalTax()
    {
        $amount = 0;

        foreach ($this->statement->taxes as $tax) {
            if ($tax->items->count() === 0) {
                $amount += $tax->amount;
            }
        }

        return $amount;
    }

    /**
     * Get global tax per item.
     *
     * @return int
     */
    public function getGlobalTaxPerItem()
    {
        return (int) round($this->getTotalGlobalTax() / $this->getItemsCount());
    }

    /**
     * Get the subtotal, minus discounts, plus taxes.
     *
     * @return int
     */
    public function getTotal()
    {
        return $this->getSubtotalAfterDiscount() + $this->getTotalTax();
    }

    /**
     * Get total discounts for a particular item, which includes global discount
     * per item and any other discount directly associated with it.
     *
     * @param \Gerardojbaez\SaleStatements\Models\SaleStatementItem $item
     * @return int
     */
    public function getTotalDiscountsForItem(SaleStatementItem $item)
    {
        $amount = $this->getGlobalDiscountPerItem() * $item->quantity;

        foreach ($item->discounts as $discount) {
            if ($discount->is_percentage) {
                $amount += $item->price * $item->quantity * $discount->discount / 100;
            } else {
                $amount += $discount->discount / $discount->items->sum('quantity');
            }
        }

        return $amount;
    }

    /**
     * Get the sum of all taxes applied to a particular item, which includes
     * global taxes per item and any other tax directly associated with it.
     *
     * @param \Gerardojbaez\SaleStatements\Models\SaleStatementItem $item
     * @return int
     */
    public function getTotalTaxForItem(SaleStatementItem $item)
    {
        $amount = $this->getGlobalTaxPerItem() * $item->quantity;

        foreach ($item->taxes as $tax) {
            $amount += $tax->amount / $tax->items->sum('quantity');
        }

        return $amount;
    }

    /**
     * Get the sum of all payments applied to the sale statement.
     *
     * @todo Add unit tests.
     * @return int
     */
    public function getTotalPaid()
    {
        if ($this->statement->isInvoice()) {
            $invoice = $this->statement;
        } elseif ($this->statement->isOrder()) {
            $invoice = $this->statement->invoice;
        } elseif ($this->statement->isQuote()) {
            $invoice = $this->statement->order->invoice;
        }

        if (! $invoice) {
            return 0;
        }

        return $invoice->invoice->payments->sum('amount_applied');
    }

    /**
     * Get the remaining balance to be paid.
     *
     * @todo Add unit tests.
     * @return int
     */
    public function getBalance()
    {
        return $this->getTotal() - $this->getTotalPaid();
    }

    /**
     * Determines whether the sale statement needs payment.
     *
     * @todo Add unit tests.
     * @return boolean
     */
    public function needsPayment()
    {
        return $this->getBalance() > 0;
    }

    /**
     * Determines whether the sale statement has a zero balance.
     *
     * @todo Add unit tests.
     * @return boolean
     */
    public function isPaid()
    {
        return $this->getBalance() === 0;
    }

    /**
     * Determines whether the sale statement has partial payments.
     *
     * @todo Add unit tests.
     * @return boolean
     */
    public function isPartiallyPaid()
    {
        $totalPaid = $this->getTotalPaid();

        return $totalPaid > 0 and $totalPaid < $this->getTotal();
    }
}
