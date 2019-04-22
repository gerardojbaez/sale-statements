<?php

namespace Gerardojbaez\SaleStatements\Models;

use Illuminate\Database\Eloquent\Model;

class SaleStatementInvoice extends Model
{
    /** @inheritDoc */
    protected $table = 'sale_statement_invoice';

    /** @inheritDoc */
    public $incrementing = false;

    /** @inheritDoc */
    protected $fillable = [];

    /** @inheritDoc */
    public $timestamps = false;

    /** @inheritDoc */
    protected $touches = ['statement'];

    /**
     * Get the statement of the invoice.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function statement()
    {
        return $this->belongsTo(
            config('sale-statements.models.sale_statement'),
            'id'
        );
    }

    /**
     * Get the order of the invoice.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(
            config('sale-statements.models.sale_statement_order'),
            'sale_statement_order_id'
        );
    }

    /**
     * Get the payments of the invoice.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payments()
    {
        return $this->hasMany(
            config('sale-statements.models.sale_statement_invoice_payment'),
            'sale_statement_invoice_id'
        );
    }

    /**
     * Get the credit memos of the order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creditMemos()
    {
        return $this->hasMany(
            config('sale-statements.models.sale_statement_credit_memo'),
            'sale_statement_invoice_id'
        );
    }
}
