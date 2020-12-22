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

    /**
     * Determine whether the payment is applicable to the invoice.
     *
     * The most common use case would be to filter out successful/completed payments, leaving out any failed, pending,
     * or authorized payments.
     *
     * @return bool
     */
    public function isApplicable()
    {
        return true; // replace with your own logic
    }
}
