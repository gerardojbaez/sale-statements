<?php

namespace Gerardojbaez\SaleStatements;

use Gerardojbaez\SaleStatements\Models\SaleStatementItem;

interface CalculatorInterface
{
    /**
     * Get the sum of all item quantities.
     *
     * @return int
     */
    public function getItemsCount();

    /**
     * Get the sum of all item prices without any discount or tax applied.
     *
     * @return int
     */
    public function getSubtotal();

    /**
     * Get the sum of all discounts applied.
     *
     * @return int
     */
    public function getTotalDiscount();

    /**
     * Get the sum of all global discounts (i.e., not associated to any
     * line item).
     *
     * @return int
     */
    public function getTotalGlobalDiscount();

    /**
     * Get global discount per item.
     *
     * @return int
     */
    public function getGlobalDiscountPerItem();

    /**
     * Get the subtotal minus the sum of all discounts.
     *
     * @return int
     */
    public function getSubtotalAfterDiscount();

    /**
     * Get the sum of all tax amounts.
     *
     * @return void
     */
    public function getTotalTax();

    /**
     * Get the sum of all taxes not associated with any line item.
     *
     * @return int
     */
    public function getTotalGlobalTax();

    /**
     * Get global tax per item.
     *
     * @return int
     */
    public function getGlobalTaxPerItem();

    /**
     * Get the subtotal, minus discounts, plus taxes.
     *
     * @return int
     */
    public function getTotal();

    /**
     * Get total discounts for a particular item, which includes global discount
     * per item and any other discount directly associated with it.
     *
     * @param \Gerardojbaez\SaleStatements\Models\SaleStatementItem $item
     * @return int
     */
    public function getTotalDiscountsForItem(SaleStatementItem $item);

    /**
     * Get the sum of all taxes applied to a particular item, which includes
     * global taxes per item and any other tax directly associated with it.
     *
     * @param \Gerardojbaez\SaleStatements\Models\SaleStatementItem $item
     * @return int
     */
    public function getTotalTaxForItem(SaleStatementItem $item);

    /**
     * Get the sum of all payments applied to the sale statement.
     *
     * @todo Add unit tests.
     * @return int
     */
    public function getTotalPaid();

    /**
     * Get the remaining balance to be paid.
     *
     * @todo Add unit tests.
     * @return int
     */
    public function getBalance();

    /**
     * Determines whether the sale statement needs payment.
     *
     * @todo Add unit tests.
     * @return boolean
     */
    public function needsPayment();

    /**
     * Determines whether the sale statement has a zero balance.
     *
     * @todo Add unit tests.
     * @return boolean
     */
    public function isPaid();

    /**
     * Determines whether the sale statement has partial payments.
     *
     * @todo Add unit tests.
     * @return boolean
     */
    public function isPartiallyPaid();
}
