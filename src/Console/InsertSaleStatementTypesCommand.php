<?php

namespace Gerardojbaez\SaleStatements\Console;

use Illuminate\Console\Command;
use Gerardojbaez\SaleStatements\Models\SaleStatementType;

class InsertSaleStatementTypesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sale-statement:insert-types';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Insert the sale statement types.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $types = [
            ['code' => 'quote', 'name' => 'Quote'],
            ['code' => 'order', 'name' => 'Order'],
            ['code' => 'invoice', 'name' => 'Invoice'],
            ['code' => 'credit_memo', 'name' => 'Credit Memo'],
        ];

        foreach ($types as $type) {
            SaleStatementType::firstOrCreate([
                'code' => $type['code'],
            ], [
                'name' => $type['name'],
            ]);
        }
    }
}
