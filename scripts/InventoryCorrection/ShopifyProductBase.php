<?php

namespace scripts\InventoryCorrection;

use PDOException;

class ShopifyProductBase extends ManageDB
{
    private $table;

    public function __construct()
    {
        parent::__construct();
        $this->table = 'shopify_products';
    }

    public function resetTable()
    {
        $this->pdo->exec("DROP TABLE IF EXISTS {$this->table};");
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS {$this->table} (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                variant_gid VARCHAR(255),
                barcode VARCHAR(255),
                sku VARCHAR(255),
                option1_name VARCHAR(255),
                option1_value VARCHAR(255),
                option2_name VARCHAR(255),
                option2_value VARCHAR(255),
                option3_name VARCHAR(255),
                option3_value VARCHAR(255),
                product_gid VARCHAR(255),
                handle VARCHAR(255),
                s_location_id INTEGER,
                s_location_name VARCHAR(255),
                on_hand INTEGER
            );
        ");
    }

    public function insert($row)
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO {$this->table} (
                    variant_gid, 
                    barcode, 
                    sku, 
                    option1_name, 
                    option1_value, 
                    option2_name, 
                    option2_value, 
                    option3_name, 
                    option3_value, 
                    product_gid, 
                    handle, 
                    s_location_id, 
                    s_location_name, 
                    on_hand
                ) VALUES (
                    :variant_gid,
                    :barcode,
                    :sku,
                    :option1_name,
                    :option1_value,
                    :option2_name,
                    :option2_value,
                    :option3_name,
                    :option3_value,
                    :product_gid,
                    :handle,
                    :s_location_id,
                    :s_location_name,
                    :on_hand
                );
            ");

            $stmt->bindParam('variant_gid', $row['variant_gid']);
            $stmt->bindParam('barcode', $row['barcode']);
            $stmt->bindParam('sku', $row['sku']);
            $stmt->bindParam('option1_name', $row['option1_name']);
            $stmt->bindParam('option1_value', $row['option1_value']);
            $stmt->bindParam('option2_name', $row['option2_name']);
            $stmt->bindParam('option2_value', $row['option2_value']);
            $stmt->bindParam('option3_name', $row['option3_name']);
            $stmt->bindParam('option3_value', $row['option3_value']);
            $stmt->bindParam('product_gid', $row['product_gid']);
            $stmt->bindParam('handle', $row['handle']);
            $stmt->bindParam('s_location_id', $row['s_location_id']);
            $stmt->bindParam('s_location_name', $row['s_location_name']);
            $stmt->bindParam('on_hand', $row['on_hand']);

            $stmt->execute();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function getProductsGQL()
    {
        return <<<'GRAPHQL'
            query getProductVariants($after: String) {
                productVariants(first: 250, after:$after) {
                    nodes {
                        id
                        barcode
                        sku
                        selectedOptions {
                            name
                            value
                        }
                        product {
                            id
                            handle
                        }
                        inventoryItem {
                            inventoryLevels (first:20) {
                                nodes {
                                    location {
                                        id
                                        name
                                    }
                                    quantities(names: "on_hand") {
                                        quantity
                                    }
                                }
                            }
                        }
                    }
                    pageInfo {
                        hasNextPage
                        endCursor
                    }
                }
            }
            GRAPHQL;
    }

    public function getOptionName($variant, $index)
    {
        $index--;
        return isset($variant->selectedOptions[$index]) ? $variant->selectedOptions[$index]->name : '';
    }

    public function getOptionValue($variant, $index)
    {
        $index--;
        return isset($variant->selectedOptions[$index]) ? $variant->selectedOptions[$index]->value : '';
    }
}
