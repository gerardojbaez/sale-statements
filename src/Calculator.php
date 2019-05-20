<?php

namespace Gerardojbaez\SaleStatements;

use Exception;
use Gerardojbaez\SaleStatements\Models\SaleStatement;
use Gerardojbaez\SaleStatements\Models\SaleStatementItem;

class Calculator implements CalculatorInterface
{
    /**
     * Get the sum of all item quantities.
     *
     * @param \Gerardojbaez\SaleStatements\Models\SaleStatement $statement
     * @return int
     */
    public function getItemsCount(SaleStatement $statement)
    {
        return $statement->items->sum('quantity');
    }

    /**
     * Get the sum of all item prices without any discount or tax applied.
     *
     * @param \Gerardojbaez\SaleStatements\Models\SaleStatement $statement
     * @return int
     */
    public function getSubtotal(SaleStatement $statement)
    {
        $subtotal = 0;

        foreach ($statement->items as $item) {
            $subtotal += $item->price * $item->quantity;
        }

        return $subtotal;
    }

    /**
     * Get the sum of all discounts applied.
     *
     * @param \Gerardojbaez\SaleStatements\Models\SaleStatement $statement
     * @return int
     */
    public function getTotalDiscount(SaleStatement $statement)
    {
        $amount = 0;

        foreach ($statement->items as $item) {
            $amount += $this->getTotalDiscountsForItem($item);
        }

        return (int) round($amount);
    }

    /**
     * Get the sum of all global discounts (i.e., not associated to any
     * line item).
     *
     * @param \Gerardojbaez\SaleStatements\Models\SaleStatement $statement
     * @return int
     */
    public function getTotalGlobalDiscount(SaleStatement $statement)
    {
        $amount = 0;
        $subtotal = $this->getSubtotal($statement);

        foreach ($statement->discounts as $discount) {
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
     * @param \Gerardojbaez\SaleStatements\Models\SaleStatement $statement
     * @return int
     */
    public function getGlobalDiscountPerItem(SaleStatement $statement)
    {
        return (int) round($this->getTotalGlobalDiscount($statement) / $this->getItemsCount($statement));
    }

    /**
     * Get the subtotal minus the sum of all discounts.
     *
     * @param \Gerardojbaez\SaleStatements\Models\SaleStatement $statement
     * @return int
     */
    public function getSubtotalAfterDiscount(SaleStatement $statement)
    {
        $subtotal = $this->getSubtotal($statement) - $this->getTotalDiscount($statement);

        // Return 0 subtotal when discounts are higher than the subtotal amount.
        if ($subtotal < 0) {
            return 0;
        }

        return $subtotal;
    }

    /**
     * Get the sum of all tax amounts.
     *
     * @param \Gerardojbaez\SaleStatements\Models\SaleStatement $statement
     * @return int
     */
    public function getTotalTax(SaleStatement $statement)
    {
        $amount = 0;

        foreach ($statement->taxes as $tax) {
            $amount += $tax->amount;
        }

        return $amount;
    }

    /**
     * Get the sum of all taxes not associated with any line item.
     *
     * @param \Gerardojbaez\SaleStatements\Models\SaleStatement $statement
     * @return int
     */
    public function getTotalGlobalTax(SaleStatement $statement)
    {
        $amount = 0;

        foreach ($statement->taxes as $tax) {
            if ($tax->items->count() === 0) {
                $amount += $tax->amount;
            }
        }

        return $amount;
    }

    /**
     * Get global tax per item.
     *
     * @param \Gerardojbaez\SaleStatements\Models\SaleStatement $statement
     * @return int
     */
    public function getGlobalTaxPerItem(SaleStatement $statement)
    {
        return (int) round($this->getTotalGlobalTax($statement) / $this->getItemsCount($statement));
    }

    /**
     * Get the subtotal, minus discounts, plus taxes.
     *
     * @param \Gerardojbaez\SaleStatements\Models\SaleStatement $statement
     * @return int
     */
    public function getTotal(SaleStatement $statement)
    {
        return $this->getSubtotalAfterDiscount($statement) + $this->getTotalTax($statement);
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
        $amount = $this->getGlobalDiscountPerItem($item->statement) * $item->quantity;

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
        $amount = $this->getGlobalTaxPerItem($item->statement) * $item->quantity;

        foreach ($item->taxes as $tax) {
            $amount += $tax->amount / $tax->items->sum('quantity');
        }

        return $amount;
    }

    /**
     * Get the sum of all payments applied to the sale statement.
     *
     * @todo Add unit tests.
     * @param \Gerardojbaez\SaleStatements\Models\SaleStatement $statement
     * @return int
     */
    public function getTotalPaid(SaleStatement $statement)
    {
        if ($statement->isInvoice()) {
            $invoice = $statement;
        } elseif ($statement->isOrder() and $statement->invoice) {
            $invoice = $statement->invoice;
        } elseif ($statement->isQuote() and $statement->order and $statement->order->invoice) {
            $invoice = $statement->order->invoice;
        }

        if (! isset($invoice)) {
            return 0;
        }

        return $invoice->invoice->payments->sum('amount_applied');
    }

    /**
     * Get the remaining balance to be paid.
     *
     * @todo Add unit tests.
     * @param \Gerardojbaez\SaleStatements\Models\SaleStatement $statement
     * @return int
     */
    public function getBalance(SaleStatement $statement)
    {
        return $this->getTotal($statement) - $this->getTotalPaid($statement);
    }

    /**
     * Determines whether the sale statement needs payment.
     *
     * @todo Add unit tests.
     * @param \Gerardojbaez\SaleStatements\Models\SaleStatement $statement
     * @return boolean
     */
    public function needsPayment(SaleStatement $statement)
    {
        return $this->getBalance($statement) > 0;
    }

    /**
     * Determines whether the sale statement has a zero balance.
     *
     * @todo Add unit tests.
     * @param \Gerardojbaez\SaleStatements\Models\SaleStatement $statement
     * @return boolean
     */
    public function isPaid(SaleStatement $statement)
    {
        return $this->getBalance($statement) === 0;
    }

    /**
     * Determines whether the sale statement has partial payments.
     *
     * @todo Add unit tests.
     * @param \Gerardojbaez\SaleStatements\Models\SaleStatement $statement
     * @return boolean
     */
    public function isPartiallyPaid(SaleStatement $statement)
    {
        $totalPaid = $this->getTotalPaid($statement);

        return $totalPaid > 0 and $totalPaid < $this->getTotal($statement);
    }
}
