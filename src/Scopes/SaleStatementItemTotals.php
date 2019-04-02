<?php

namespace Gerardojbaez\SaleStatements\Scopes;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;

class SaleStatementItemTotals implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $builder->select(DB::raw(
            'sale_statement_items.*,
            sale_statement_items.price * sale_statement_items.quantity as amount'
        ));
    }
}
