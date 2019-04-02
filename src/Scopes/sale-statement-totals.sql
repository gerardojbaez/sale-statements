-- Full query attached for debugging and development purposes.
-- Each update made should be applied here and to the SaleStatementTotals.php file. Ideally,
-- you would apply the updates here, test them on MySQL Workbench, and then apply the changes
-- to the .php file.

SELECT
    sale_statements.*,
    SUM(items.subtotal) AS subtotal,
    ROUND((SUM(items.subtotal) - discounts) * SUM(items.total_item_tax_rate)) AS total_tax,
    ROUND(discounts / SUM(items.quantity)) AS discount_per_item,
    ROUND((SUM(items.subtotal) - discounts) + ((SUM(items.subtotal) - discounts) * SUM(items.total_item_tax_rate))) AS total,
    SUM(items.quantity) AS items_count
FROM
    sale_statements
        LEFT JOIN
    (SELECT
        sale_statement_items.sale_statement_id,
            sale_statement_items.quantity,
            sale_statement_items.price,
            (sale_statement_items.price * sale_statement_items.quantity) AS subtotal,
            COALESCE(SUM(sale_statement_taxes.rate), 0) AS total_item_tax_rate
    FROM
        sale_statement_items
    LEFT JOIN sale_statement_item_tax ON sale_statement_items.id = sale_statement_item_tax.sale_statement_item_id
    LEFT JOIN sale_statement_taxes ON sale_statement_item_tax.sale_statement_tax_id = sale_statement_taxes.id
    GROUP BY sale_statement_items.id) items ON sale_statements.id = items.sale_statement_id
GROUP BY sale_statements.id
