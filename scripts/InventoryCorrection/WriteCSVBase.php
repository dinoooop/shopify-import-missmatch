<?php

namespace scripts\InventoryCorrection;

use PDO;
use PDOException;

class WriteCSVBase extends ManageDB
{

    public function __construct()
    {
        parent::__construct();
    }

    public function getMissMatchesForWriteImportCSV($page, $limit)
    {

        $offset = ($page - 1) * $limit;

        $stmt = $this->pdo->prepare("
            SELECT 
                {$this->table}.barcode AS barcode,
                sku,
                option1_name,
                option1_value,
                option2_name,
                option2_value,
                option3_name,
                option3_value,
                handle,
                location,
                miss_matches.h_qty AS on_hand
            FROM miss_matches
            LEFT JOIN 
                shopify_products ON 
                    miss_matches.barcode = shopify_products.barcode AND 
                    miss_matches.s_location_id = shopify_products.s_location_id
            LIMIT :limit OFFSET :offset
        ");

        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $results;
    }

    public function getMissMatchesForWriteQuickReadCSV($page, $limit)
    {

        $offset = ($page - 1) * $limit;

        $stmt = $this->pdo->prepare("
            SELECT 
                miss_matches.barcode AS barcode,
                map_locations.h_location_name AS h_location_name,
                map_locations.s_location_name AS s_location_name,
                miss_matches.s_qty AS s_qty,
                miss_matches.h_qty AS h_qty
            FROM miss_matches
            LEFT JOIN 
                map_locations ON 
                    miss_matches.s_location_id = map_locations.s_location_id
            LIMIT :limit OFFSET :offset
        ");

        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $results;
    }
}
