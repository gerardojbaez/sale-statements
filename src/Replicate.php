<?php

namespace Gerardojbaez\SaleStatements;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Gerardojbaez\SaleStatements\Models\SaleStatement;

class Replicate implements ReplicateInterface
{
    /**
     * Generate a new order statement from quote.
     *
     * @todo  Reduce N+1 query issue.
     * @param \Gerardojbaez\SaleStatements\Models\SaleStatement $statement
     * @return \Gerardojbaez\SaleStatements\Models\SaleStatement
     */
    public function asOrder(SaleStatement $statement)
    {
        if (! $statement->isQuote()) {
            throw new Exception('Only quote type sale statement can be replicated as order.');
        }

        /** @var \Gerardojbaez\SaleStatements\Models\SaleStatementType */
        $saleStatementTypeClass = config('sale-statements.models.sale_statement_type');

        /** @var \Gerardojbaez\SaleStatements\Models\SaleStatementOrder */
        $saleStatementOrderClass = config('sale-statements.models.sale_statement_order');

        $saleStatementOrderClass::unguard();

        $statement = $this->replicateAs($saleStatementTypeClass::TYPE_ORDER, $statement);

        $saleStatementOrderClass::reguard();

        return $statement;
    }

    /**
     * Generate a new invoice statement from order.
     *
     * @todo  Reduce N+1 query issue.
     * @param \Gerardojbaez\SaleStatements\Models\SaleStatement $statement
     * @return \Gerardojbaez\SaleStatements\Models\SaleStatement
     */
    public function asInvoice(SaleStatement $statement)
    {
        if (! $statement->isOrder()) {
            throw new Exception('Only order type sale statement can be replicated as invoice.');
        }

        /** @var \Gerardojbaez\SaleStatements\Models\SaleStatementType */
        $saleStatementTypeClass = config('sale-statements.models.sale_statement_type');

        /** @var \Gerardojbaez\SaleStatements\Models\SaleStatementInvoice */
        $saleStatementInvoiceClass = config('sale-statements.models.sale_statement_invoice');

        $saleStatementInvoiceClass::unguard();

        $statement = $this->replicateAs($saleStatementTypeClass::TYPE_INVOICE, $statement);

        $saleStatementInvoiceClass::reguard();

        return $statement;
    }

    /**
     * Replicate sale statement.
     *
     * @param string $type
     * @param \Gerardojbaez\SaleStatements\Models\SaleStatement $original
     * @return \Gerardojbaez\SaleStatements\Models\SaleStatement
     */
    protected function replicateAs($type, $original)
    {
        return DB::transaction(function () use ($type, $original) {
            $statement = $original->load(
                'items.taxes',
                'items.discounts',
                'taxes',
                'discounts',
                'addresses'
            )->replicate();

            $statement->type = $type;
            $statement->save();

            $method = Str::camel($type);

            $statement->$method()->create([
                $original->subtype->getForeignKey() => $original->id
            ]);

            $statement->addresses->transform(function ($address) use ($statement) {
                $address = $address->replicate();
                $address->sale_statement_id = $statement->id;
                $address->save();

                return $address;
            });

            $itemToTaxMap = [];
            $itemToDiscountMap = [];

            $statement->items->transform(function ($item) use ($statement, &$itemToTaxMap, &$itemToDiscountMap) {
                $new = $item->replicate(['amount']);
                $new->sale_statement_id = $statement->id;
                $new->save();

                foreach ($item->taxes as $tax) {
                    $itemToTaxMap[$tax->id][] = $new->id;
                }

                foreach ($item->discounts as $discount) {
                    $itemToDiscountMap[$discount->id][] = $new->id;
                }

                return $new;
            });

            $statement->taxes->transform(function ($tax) use ($statement, $itemToTaxMap) {
                $new = $tax->replicate();
                $new->sale_statement_id = $statement->id;
                $new->save();

                if (isset($itemToTaxMap[$tax->id])) {
                    $new->items()->attach($itemToTaxMap[$tax->id]);
                }

                return $new;
            });

            $statement->discounts->transform(function ($discount) use ($statement, $itemToDiscountMap) {
                $new = $discount->replicate();
                $new->sale_statement_id = $statement->id;
                $new->save();

                if (isset($itemToDiscountMap[$discount->id])) {
                    $new->items()->attach($itemToDiscountMap[$discount->id]);
                }

                return $new;
            });

            return $statement;
        });
    }
}
