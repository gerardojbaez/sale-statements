<?php

namespace Gerardojbaez\SaleStatements;

use Gerardojbaez\SaleStatements\Models\SaleStatement;
use Gerardojbaez\SaleStatements\Models\SaleStatementItem;

interface CalculatorInterface
{
    /**
     * Get the sum of all item quantities.
     *
     * @param \Gerardojbaez\SaleStatements\Models\SaleStatement $statement
     * @return int
     */
    public function getItemsCount(SaleStatement $statement);

    /**
     * Get the sum of all item prices without any discount or tax applied.
     *
     * @param \Gerardojbaez\SaleStatements\Models\SaleStatement $statement
     * @return int
     */
    public function getSubtotal(SaleStatement $statement);

    /**
     * Get the sum of all discounts applied.
     *
     * @param \Gerardojbaez\SaleStatements\Models\SaleStatement $statement
     * @return int
     */
    public function getTotalDiscount(SaleStatement $statement);

    /**
     * Get the sum of all global discounts (i.e., not associated to any
     * line item).
     *
     * @param \Gerardojbaez\SaleStatements\Models\SaleStatement $statement
     * @return int
     */
    public function getTotalGlobalDiscount(SaleStatement $statement);

    /**
     * Get global discount per item.
     *
     * @param \Gerardojbaez\SaleStatements\Models\SaleStatement $statement
     * @return int
     */
    public function getGlobalDiscountPerItem(SaleStatement $statement);

    /**
     * Get the subtotal minus the sum of all discounts.
     *
     * @param \Gerardojbaez\SaleStatements\Models\SaleStatement $statement
     * @return int
     */
    public function getSubtotalAfterDiscount(SaleStatement $statement);

    /**
     * Get the sum of all tax amounts.
     *
     * @param \Gerardojbaez\SaleStatements\Models\SaleStatement $statement
     * @return void
     */
    public function getTotalTax(SaleStatement $statement);

    /**
     * Get the sum of all taxes not associated with any line item.
     *
     * @param \Gerardojbaez\SaleStatements\Models\SaleStatement $statement
     * @return int
     */
    public function getTotalGlobalTax(SaleStatement $statement);

    /**
     * Get global tax per item.
     *
     * @param \Gerardojbaez\SaleStatements\Models\SaleStatement $statement
     * @return int
     */
    public function getGlobalTaxPerItem(SaleStatement $statement);

    /**
     * Get the subtotal, minus discounts, plus taxes.
     *
     * @param \Gerardojbaez\SaleStatements\Models\SaleStatement $statement
     * @return int
     */
    public function getTotal(SaleStatement $statement);

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
     * @param \Gerardojbaez\SaleStatements\Models\SaleStatement $statement
     * @return int
     */
    public function getTotalPaid(SaleStatement $statement);

    /**
     * Get the remaining balance to be paid.
     *
     * @todo Add unit tests.
     * @param \Gerardojbaez\SaleStatements\Models\SaleStatement $statement
     * @return int
     */
    public function getBalance(SaleStatement $statement);

    /**
     * Determines whether the sale statement needs payment.
     *
     * @todo Add unit tests.
     * @param \Gerardojbaez\SaleStatements\Models\SaleStatement $statement
     * @return boolean
     */
    public function needsPayment(SaleStatement $statement);

    /**
     * Determines whether the sale statement has a zero balance.
     *
     * @todo Add unit tests.
     * @param \Gerardojbaez\SaleStatements\Models\SaleStatement $statement
     * @return boolean
     */
    public function isPaid(SaleStatement $statement);

    /**
     * Determines whether the sale statement has partial payments.
     *
     * @todo Add unit tests.
     * @param \Gerardojbaez\SaleStatements\Models\SaleStatement $statement
     * @return boolean
     */
    public function isPartiallyPaid(SaleStatement $statement);
}
