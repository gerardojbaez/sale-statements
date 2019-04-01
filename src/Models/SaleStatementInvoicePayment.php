<?php

namespace Gerardojbaez\SaleStatements\Models;

use Illuminate\Database\Eloquent\Model;

class SaleStatementInvoicePayment extends Model
{
    /** @inheritDoc */
    protected $fillable = [
        'amount_applied',
    ];

    /**
     * Get the invoice of the payment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function invoice()
    {
        return $this->belongsTo(
            config('sale-statements.models.sale_statement_invoice'),
            'sale_statement_invoice_id'
        );
    }
}
