<?php

namespace Gerardojbaez\SaleStatements\Models;

use Illuminate\Database\Eloquent\Model;

class SaleStatementAddress extends Model
{
    /** @inheritDoc */
    protected $fillable = [
        'is_shipping',
        'is_billing',
        'line_1',
        'line_2',
        'city',
        'province',
        'country',
        'zip',
        'first_name',
        'last_name',
        'province_code',
        'country_code',
        'organization',
    ];

    /** @inheritDoc */
    public $timestamps = false;

    /** @inheritDoc */
    protected $touches = ['statement'];

    /**
     * Get the statement of the address.
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
}
