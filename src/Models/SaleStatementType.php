<?php

namespace Gerardojbaez\SaleStatements\Models;

use Illuminate\Database\Eloquent\Model;

class SaleStatementType extends Model
{
    const TYPE_QUOTE = 'quote';
    const TYPE_ORDER = 'order';
    const TYPE_INVOICE = 'invoice';
    const TYPE_CREDIT_MEMO = 'credit_memo';

    /** @inheritDoc */
    protected $fillable = [
        'code', 'name'
    ];
}
