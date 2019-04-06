<?php

namespace Gerardojbaez\SaleStatements\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Gerardojbaez\SaleStatements\Scopes\SaleStatementTotals;

class SaleStatement extends Model
{
    /** @inheritDoc */
    protected $fillable = [
        'discounts', 'type',
    ];

    /**
     * Create a sale statement.
     *
     * @param  string  $type  The type of sales statement to create.
     * @param  array  $statement  The sale statement's fillable attributes.
     * @param  array  $subtype  The subtype's fillable attributes.
     * @return \Gerardojbaez\SaleStatements\Models\SaleStatement
     */
    public static function create(string $type, array $statement = [], array $subtype = [])
    {
        $instance = new static;
        $instance->fill(array_merge($statement, compact('type')))->save();

        $method = Str::camel($type);

        $instance->$method()->create($subtype);

        return $instance;
    }

    /**
     * Determine whether the statement is of quote type.
     *
     * @return boolean
     */
    public function isQuote()
    {
        /** @var \Gerardojbaez\SaleStatements\Models\SaleStatementType */
        $saleStatementTypeClass = config('sale-statements.models.sale_statement_type');

        return $this->type === $saleStatementTypeClass::TYPE_QUOTE;
    }

    /**
     * Determine whether the statement is of order type.
     *
     * @return boolean
     */
    public function isOrder()
    {
        /** @var \Gerardojbaez\SaleStatements\Models\SaleStatementType */
        $saleStatementTypeClass = config('sale-statements.models.sale_statement_type');

        return $this->type === $saleStatementTypeClass::TYPE_ORDER;
    }

    /**
     * Determine whether the statement is of invoice type.
     *
     * @return boolean
     */
    public function isInvoice()
    {
        /** @var \Gerardojbaez\SaleStatements\Models\SaleStatementType */
        $saleStatementTypeClass = config('sale-statements.models.sale_statement_type');

        return $this->type === $saleStatementTypeClass::TYPE_INVOICE;
    }

    /**
     * Determine whether the statement is of credit memo type.
     *
     * @return boolean
     */
    public function isCreditMemo()
    {
        /** @var \Gerardojbaez\SaleStatements\Models\SaleStatementType */
        $saleStatementTypeClass = config('sale-statements.models.sale_statement_type');

        return $this->type === $saleStatementTypeClass::TYPE_CREDIT_MEMO;
    }

    /**
     * Dynamically call the subtype relation of the sale statement. If no
     * type is defined, "quote" is used.
     *
     * @return mixed
     */
    public function subtype()
    {
        $method = Str::camel($this->type ?: 'quote');

        return $this->$method();
    }

    /**
     * Get the quote of the model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function quote()
    {
        return $this->hasOne(config('sale-statements.models.sale_statement_quote'));
    }

    /**
     * Get the order of the model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function order()
    {
        return $this->hasOne(config('sale-statements.models.sale_statement_order'));
    }

    /**
     * Get the credit memo of the model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function creditMemo()
    {
        return $this->hasOne(config('sale-statements.models.sale_statement_creditmemo'));
    }

    /**
     * Get the invoice of the model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function invoice()
    {
        return $this->hasOne(config('sale-statements.models.sale_statement_invoice'));
    }

    /**
     * Get the addresses of the model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function addresses()
    {
        return $this->hasMany(config('sale-statements.models.sale_statement_address'));
    }

    /**
     * Get the items of the model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        return $this->hasMany(config('sale-statements.models.sale_statement_item'));
    }

    /**
     * Get the taxes of the model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function taxes()
    {
        return $this->hasMany(config('sale-statements.models.sale_statement_tax'));
    }
}
