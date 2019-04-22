<?php

namespace Gerardojbaez\SaleStatements\Models;

use Illuminate\Database\Eloquent\Model;

class SaleStatementOrder extends Model
{
    /** @inheritDoc */
    protected $table = 'sale_statement_order';

    /** @inheritDoc */
    public $incrementing = false;

    /** @inheritDoc */
    protected $fillable = [];

    /** @inheritDoc */
    public $timestamps = false;

    /** @inheritDoc */
    protected $touches = ['statement'];

    /**
     * Get the statement of the order.
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
     * Get the quote of the order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function quote()
    {
        return $this->belongsTo(
            config('sale-statements.models.sale_statement_quote'),
            'sale_statement_quote_id'
        );
    }

    /**
     * Get the invoices of the order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function invoices()
    {
        return $this->hasMany(
            config('sale-statements.models.sale_statement_invoice'),
            'sale_statement_order_id'
        );
    }
}
