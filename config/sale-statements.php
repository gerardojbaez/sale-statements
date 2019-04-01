<?php

return [

    /**
     * The model classes used by the package. They must extend the default ones.
     */
    'models' => [
        'sale_statement' => \Gerardojbaez\SaleStatements\Models\SaleStatement::class,
        'sale_statement_address' => \Gerardojbaez\SaleStatements\Models\SaleStatementAddress::class,
        'sale_statement_creditmemo' => \Gerardojbaez\SaleStatements\Models\SaleStatementCreditMemo::class,
        'sale_statement_invoice' => \Gerardojbaez\SaleStatements\Models\SaleStatementInvoice::class,
        'sale_statement_invoice_payment' => \Gerardojbaez\SaleStatements\Models\SaleStatementInvoicePayment::class,
        'sale_statement_item' => \Gerardojbaez\SaleStatements\Models\SaleStatementItem::class,
        'sale_statement_order' => \Gerardojbaez\SaleStatements\Models\SaleStatementOrder::class,
        'sale_statement_quote' => \Gerardojbaez\SaleStatements\Models\SaleStatementQuote::class,
        'sale_statement_tax' => \Gerardojbaez\SaleStatements\Models\SaleStatementTax::class,
        'sale_statement_type' => \Gerardojbaez\SaleStatements\Models\SaleStatementType::class,
    ]
];
