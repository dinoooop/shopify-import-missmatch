<?php

namespace scripts\InventoryCorrection\Base;

use PDO;
use PDOException;

class MissMatchBase extends ManageDB
{

    protected $table;

    function __construct()
    {
        parent::__construct();
        $this->table = "miss_matches";
    }

    public function createTable($truncate = false)
    {
        try {
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS {$this->table} (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    barcode VARCHAR(255) NOT NULL,
                    s_location_id VARCHAR(255) NOT NULL,
                    h_location_id INTEGER NOT NULL,
                    s_qty INTEGER DEFAULT 0,
                    h_qty INTEGER DEFAULT 0,
                    order INTEGER,
                    purchase_order INTEGER,
                    transfer INTEGER,
                    adjustment INTEGER,
                    other INTEGER
                )
            ");

            if ($truncate) {
                $this->pdo->exec("DELETE FROM {$this->table}");
                $this->pdo->exec("DELETE FROM sqlite_sequence WHERE name='{$this->table}'");
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function findAllMissMatches($page, $limit)
    {

        $offset = ($page - 1) * $limit;

        $stmt = $this->pdo->prepare("
            SELECT
                shopify_products.barcode AS barcode,
                map_locations.s_location_id,
                map_locations.h_location_id,
                shopify_products.on_hand AS s_qty,
                heartland_inventories.on_hand AS h_qty
            FROM shopify_products
            LEFT JOIN
                map_locations
                    ON shopify_products.location_gid = map_locations.s_location_id
            LEFT JOIN
                heartland_products  -- Ensure this table name is correct
                    ON shopify_products.barcode = heartland_products.barcode
            LEFT JOIN
                heartland_inventories
                    ON heartland_products.item_id = heartland_inventories.item_id AND
                    map_locations.h_location_id = heartland_inventories.location_id
            WHERE 
                shopify_products.barcode IS NOT NULL AND shopify_products.barcode != '' AND
                COALESCE(shopify_products.on_hand, 0) != COALESCE(heartland_inventories.on_hand, 0)
            LIMIT :limit OFFSET :offset;
        ");

        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $results;
    }

    

    public function insert($row)
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO {$this->table} (
                    barcode, 
                    s_location_id, 
                    h_location_id, 
                    s_qty,
                    h_qty
                ) VALUES (
                    :barcode, 
                    :s_location_id, 
                    :h_location_id, 
                    :s_qty, 
                    :h_qty
                )
            ");
            $stmt->bindParam(':barcode', $row['barcode']);
            $stmt->bindParam(':s_location_id', $row['s_location_id']);
            $stmt->bindParam(':h_location_id', $row['h_location_id']);
            $stmt->bindParam(':s_qty', $row['s_qty']);
            $stmt->bindParam(':h_qty', $row['h_qty']);
            $stmt->execute();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    
}
