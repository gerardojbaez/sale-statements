<?php

namespace Gerardojbaez\SaleStatements\Models;

use Illuminate\Database\Eloquent\Model;
use Gerardojbaez\SaleStatements\Scopes\SaleStatementItemTotals;

class SaleStatementItem extends Model
{
    /** @inheritDoc */
    protected $fillable = [
        'name', 'price', 'quantity',
    ];

    /** @inheritDoc */
    public $timestamps = false;

    /** @inheritDoc */
    protected $touches = ['statement'];

    /**
     * Get the statement of the item.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function statement()
    {
        return $this->belongsTo(
            config('sale-statements.models.sale_statement'),
            'sale_statement_id'
        );
    }

    /**
     * Get the discounts of the model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function discounts()
    {
        return $this->belongsToMany(
            config('sale-statements.models.sale_statement_discount'),
            'sale_statement_discount_item'
        );
    }

    /**
     * Get the taxes of the model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function taxes()
    {
        return $this->belongsToMany(
            config('sale-statements.models.sale_statement_tax'),
            'sale_statement_item_tax'
        );
    }
}
