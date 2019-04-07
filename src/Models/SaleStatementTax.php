<?php

namespace Gerardojbaez\SaleStatements\Models;

use Illuminate\Database\Eloquent\Model;

class SaleStatementTax extends Model
{
    /** @inheritDoc */
    protected $fillable = [
        'name', 'rate', 'amount',
    ];

    /** @inheritDoc */
    public $timestamps = false;

    /** @inheritDoc */
    protected $touches = ['statement'];

    /**
     * Get the statement of the tax.
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
     * Get the items of the model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->belongsToMany(
            config('sale-statements.models.sale_statement_item'),
            'sale_statement_item_tax'
        );
    }
}
