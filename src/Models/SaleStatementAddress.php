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
        'administrative_area',
        'locality',
        'dependent_locality',
        'country',
        'country_code',
        'postalcode',
        'given_name',
        'additional_name',
        'family_name',
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
