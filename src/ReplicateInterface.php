<?php

namespace Gerardojbaez\SaleStatements;

use Gerardojbaez\SaleStatements\Models\SaleStatement;

interface ReplicateInterface
{
    /**
     * Generate a new order statement from quote.
     *
     * @todo  Reduce N+1 query issue.
     * @param \Gerardojbaez\SaleStatements\Models\SaleStatement $statement
     * @return \Gerardojbaez\SaleStatements\Models\SaleStatement
     */
    public function asOrder(SaleStatement $statement);

    /**
     * Generate a new invoice statement from order.
     *
     * @todo  Reduce N+1 query issue.
     * @param \Gerardojbaez\SaleStatements\Models\SaleStatement $statement
     * @return \Gerardojbaez\SaleStatements\Models\SaleStatement
     */
    public function asInvoice(SaleStatement $statement);
}
