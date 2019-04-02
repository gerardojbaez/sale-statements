<?php

namespace Gerardojbaez\SaleStatements\Scopes;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;

class SaleStatementTotals implements Scope
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
            'sale_statements.*,
            SUM(items.subtotal) AS subtotal,
            ROUND((SUM(items.subtotal) - discounts) * SUM(items.total_item_tax_rate)) AS total_tax,
            ROUND(discounts / SUM(items.quantity)) AS discount_per_item,
            ROUND((SUM(items.subtotal) - discounts) + ((SUM(items.subtotal) - discounts) * SUM(items.total_item_tax_rate))) AS total,
            SUM(items.quantity) AS items_count'
        ));

        $items = DB::table('sale_statement_items')
            ->select(DB::raw(
                'sale_statement_items.sale_statement_id,
                sale_statement_items.quantity,
                sale_statement_items.price,
                (sale_statement_items.price * sale_statement_items.quantity) AS subtotal,
                COALESCE(SUM(sale_statement_taxes.rate), 0) AS total_item_tax_rate'
            ))
            ->leftJoin('sale_statement_item_tax', 'sale_statement_items.id', '=', 'sale_statement_item_tax.sale_statement_item_id')
            ->leftJoin('sale_statement_taxes', 'sale_statement_item_tax.sale_statement_tax_id', '=', 'sale_statement_taxes.id')
            ->groupBy('sale_statement_items.id');

        $builder->leftJoinSub($items, 'items', function ($join) {
            $join->on('sale_statements.id', '=', 'items.sale_statement_id');
        });

        $builder->groupBy('sale_statements.id');
    }
}
