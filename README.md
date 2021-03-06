# Sales Statements :moneybag: :page_with_curl: :grin:

A payment-agnostic sales records system for your Laravel application. 

Keep track of quotes, orders, invoices and credit notes easily. The goal of this package is to provide an easy and quick starting point, without compromising flexibility.

Please feel free to join and contribute with pull requests, recommendations, advice and anything helpful!

*Payment gateways are out of scope for this package.*

## Features

- Record quotes, orders, invoices and credit notes
- Generate order from a quote
- Apply invoices to multiple orders
- Apply partial or full payments records to invoices
- Generate multiple credit notes per invoice

## A note on PDFs, address and money formatting

Address and money formatting is something that varies a lot between applications, this is why PDF generation is not included in this package and may not be added in the future.

You may consider using [Dompdf](https://github.com/dompdf/dompdf) and [Sparksuite's invoice template](https://github.com/sparksuite/simple-html-invoice-template) as a starting point. If your application already has addresses and money formatting, adding PDF with the above tools may be a trivial task.

## A note on the database design

You will notice that there are no quote, order, invoice or credit memo database tables. Since these documents share the same information and structure, the database was designed in such a way that all sale statements share the same tables. 

We are using the supertype/subtype approach. All the shared information between all sale statements are stored in `sale_statements` table. Sale statement specific information, like associating an invoice to an order statement is made on the `sale_statement_{subtype}` table; where "subtype" is the type of document (e.g., `sale_statement_invoice` for invoice type statements). 

Items, addresses, and taxes are associated with the supertype (i.e., `sale_statements` table). Invoices payments are associated with the subtype `sale_statement_invoice`. 

## A note on money values

All amounts are represented in the smallest unit (e.g., cents), so USD 5.00 is written as `500`. For this reason, all database columns that store monetary values are using the INT data type.

## I don't see the relation to my users or products table

There's no assumption of your billable and product models. If you need to add a relation or extend the base functionality, you are free to do so by altering the tables as needed and extending the default models.

## Security

If you discover any security-related issue, please email g@gerardobaez.com instead of using the issue tracker.

## License

Released under the MIT License. Please see the License file included for more information.
