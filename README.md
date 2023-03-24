# Sales Statements :moneybag: :page_with_curl: :grin:

A payment-agnostic sales records system for your Laravel application. 

Easily store and manage your application's quotes, orders, invoices, and credit memos sales documents.

Used in production by [Rentado.net](https://rentado.net/?source=gerardojbaez/sale-statements) and [Full Help](https://www.fullhelp.com/?source=gerardojbaez/sale-statements) 🎉

## Features

- Support for quotes, orders, invoices and credit notes
- Ability to generate orders from a quote, invoices from an order, credit memos from an invoice.
- May be extended to support other sales documents.
- Store discounts on statement and optionally associate it with select items
- Per-item tax rates

## TL;DR

```php
use \Gerardojbaez\SaleStatements\Models\SaleStatement;
use \Gerardojbaez\SaleStatements\Models\SaleStatementType;

// Create Invoice sale statement
$invoice = SaleStatement::create(SaleStatementType::TYPE_ORDER);

// Add Billing and Shipping Address
$invoice->addresses()->create([
    'is_shipping' => true,
    'is_billing' => true,
    'line_1' => '711-2880 Nulla St',
    'locality' => 'Mankato', // City, Town, Municipality, etc...
    'administrative_area' => 'Mississipi', // State, Province, Region, etc...
    'country_code' => 'US',
    'postalcode' => 96522,
    'given_name' => 'Cecilia', // i.e., first name
    'additional_name' => 'J.', // Can be used to hold a middle name, or a patronymic.
    'family_name' => 'Chapman', // i.e., last name
    'organization' => 'Acme Co.',
]);

// Store a discount at the statement-level
$discount = $invoice->discounts()->create([
    'name' => 'Get 20% off of license!',
    'discount' => 20,
    'is_percentage' => true,
]);

// Store tax rate at the statement-level
$tax = $statement->taxes()->create([
    'name' => 'PR 10.5 %',
    'rate' => 0.105,
    'amount' => 2486
]);

// Add line item to statement
$item = $invoice->items()->create([
    'name' => 'Software license',
    'price' => 14900, // $149.00
    'quantity' => 1,
]);

// Optionally associate discount with item
$item->discounts()->attach($discount);

// Associate tax rate with item
$item->taxes()->attach($tax);
```

## A note on PDFs, address and money formatting

Address and money formatting is something that varies a lot between applications, this is why PDF generation is not included in this package and may not be added in the future.

You may consider using [Dompdf](https://github.com/dompdf/dompdf) and [Sparksuite's invoice template](https://github.com/sparksuite/simple-html-invoice-template) as a starting point. If your application already has addresses and money formatting, adding PDF with the above tools may be a trivial task.

## A note on money values

All amounts are represented in the smallest unit (e.g., cents), so USD 5.00 is written as `500`. For this reason, all database columns that store monetary values are using the INT data type.

## I don't see the relation to my users or products table

There's no assumption of your billable and product models. If you need to add a relation or extend the base functionality, you are free to do so by altering the tables as needed and extending the default models.

## Security

If you discover any security-related issue, please email g@gerardobaez.com instead of using the issue tracker.

## License

Released under the MIT License. Please see the License file included for more information.
