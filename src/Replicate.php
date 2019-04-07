<?php

namespace Gerardojbaez\SaleStatements;

use Exception;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Gerardojbaez\SaleStatements\Models\SaleStatement;

class Replicate
{
    /**
     * The sale statement to be replicated.
     *
     * @var \Gerardojbaez\SaleStatements\Models\SaleStatement
     */
    protected $statement;

    /**
     * Create a new sale statement replication instance.
     *
     * @param \Gerardojbaez\SaleStatements\Models\SaleStatement $statement
     */
    public function __construct(SaleStatement $statement)
    {
        $this->statement = $statement;
    }

    /**
     * Generate a new order statement from quote.
     *
     * @todo  Reduce N+1 query issue.
     * @return \Gerardojbaez\SaleStatements\Models\SaleStatement
     */
    public function asOrder()
    {
        if (! $this->statement->isQuote()) {
            throw new Exception('Only quote type sale statement can be replicated as order.');
        }

        /** @var \Gerardojbaez\SaleStatements\Models\SaleStatementType */
        $saleStatementTypeClass = config('sale-statements.models.sale_statement_type');

        /** @var \Gerardojbaez\SaleStatements\Models\SaleStatementOrder */
        $saleStatementOrderClass = config('sale-statements.models.sale_statement_order');

        $saleStatementOrderClass::unguard();

        $statement = $this->replicateAs($saleStatementTypeClass::TYPE_ORDER);

        $saleStatementOrderClass::reguard();

        return $statement;
    }

    /**
     * Generate a new invoice statement from order.
     *
     * @todo  Reduce N+1 query issue.
     * @return \Gerardojbaez\SaleStatements\Models\SaleStatement
     */
    public function asInvoice()
    {
        if (! $this->statement->isOrder()) {
            throw new Exception('Only order type sale statement can be replicated as invoice.');
        }

        /** @var \Gerardojbaez\SaleStatements\Models\SaleStatementType */
        $saleStatementTypeClass = config('sale-statements.models.sale_statement_type');

        /** @var \Gerardojbaez\SaleStatements\Models\SaleStatementInvoice */
        $saleStatementInvoiceClass = config('sale-statements.models.sale_statement_invoice');

        $saleStatementInvoiceClass::unguard();

        $statement = $this->replicateAs($saleStatementTypeClass::TYPE_INVOICE);

        $saleStatementInvoiceClass::reguard();

        return $statement;
    }

    /**
     * Replicate sale statement.
     *
     * @param string $type
     * @return \Gerardojbaez\SaleStatements\Models\SaleStatement
     */
    protected function replicateAs($type)
    {
        return DB::transaction(function () use ($type) {
            $statement = $this->statement->load(
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
                $this->statement->subtype->getForeignKey() => $this->statement->id
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
