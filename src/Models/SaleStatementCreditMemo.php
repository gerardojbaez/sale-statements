<?php

namespace Gerardojbaez\SaleStatements\Models;

use Illuminate\Database\Eloquent\Model;

class SaleStatementCreditMemo extends Model
{
    /** @inheritDoc */
    protected $table = 'sale_statement_credit_memo';

    /** @inheritDoc */
    public $incrementing = false;

    /** @inheritDoc */
    protected $fillable = [];

    /** @inheritDoc */
    public $timestamps = false;

    /** @inheritDoc */
    protected $touches = ['statement'];

    /**
     * Get the statement of the credit memo.
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
     * Get the invoice of the credit memo.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function invoice()
    {
        return $this->belongsTo(
            config('sale-statements.models.sale_statement_invoice'),
            'sale_statement_credit_memo_id'
        );
    }
}
