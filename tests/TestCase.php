<?php

namespace Gerardojbaez\SaleStatements\Tests;

use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as TestBench;

class TestCase extends TestBench
{
    /**
     * Get the service providers for the package.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return ['Gerardojbaez\SaleStatements\SaleStatementsServiceProvider'];
    }
}
