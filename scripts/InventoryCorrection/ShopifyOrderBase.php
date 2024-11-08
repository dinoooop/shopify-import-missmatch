<?php

namespace scripts\InventoryCorrection;


class ShopifyOrderBase extends ManageDB
{
    private $table;

    function __construct()
    {
        parent::__construct();
        $this->table = "shopify_orders";
    }

    public function resetTable()
    {

        $this->pdo->exec("DROP TABLE IF EXISTS {$this->table};");
        $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS {$this->table} (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    barcode VARCHAR(255),
                    s_location_name VARCHAR(255),
                    qty INTEGER,
                    created_at TEXT
                )
            ");
    }

    public function insert($row)
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO {$this->table} (
                    barcode, 
                    s_location_name, 
                    qty,
                    created_at
                ) VALUES (
                    :barcode, 
                    :s_location_name, 
                    :qty,
                    :created_at
                )
            ");
            $stmt->bindParam(':barcode', $row['barcode']);
            $stmt->bindParam(':s_location_name', $row['s_location_name']);
            $stmt->bindParam(':qty', $row['qty']);
            $stmt->bindParam(':created_at', $row['created_at']);
            $stmt->execute();
        } catch (\PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function getSum($missMatch)
    {
        try {
            $stmt = $this->pdo->prepare("
                SELECT SUM(qty) FROM {$this->table} 
                WHERE 
                    barcode = :barcode AND 
                    s_location_name = :s_location_name AND 
                    created_at > :created_at
            ");

            $stmt->bindParam(':barcode', $missMatch['barcode']);
            $stmt->bindParam(':s_location_name', $missMatch['s_location_name']);
            $stmt->bindParam(':created_at', $this->lastImportDate);
            $stmt->execute();
            $record = $stmt->fetch(\PDO::FETCH_ASSOC);
            return $record['SUM(qty)'] ?? 0;
        } catch (\PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public static function getFulfillmentOrdersGQL()
    {
        return <<<'GRAPHQL'
            query getOrders($after: String) {
                fulfillmentOrders(first: 250,  after:$after, reverse: true) {
                    nodes {
                        id
                        fulfillAt
                        assignedLocation {
                            location {
                                id
                                name
                            }
                        }
                        lineItems(first:20){
                            nodes {
                                id
                                totalQuantity
                                variant {
                                    barcode
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
}
