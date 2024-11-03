<?php

namespace scripts\InventoryCorrection;

use scripts\InventoryCorrection\Base\MissMatchBase;
use PDO;
use PDOException;

class MissMatch extends MissMatchBase
{

    function __construct()
    {
        parent::__construct();
    }

    public function init()
    {
        try {


            $this->createTable(true);
            
            $page = 1;
            $perPage = 1000;

            do {
                $missMatches = $this->findAllMissMatches($page, $perPage);

                echo "Missmatch page: {$page} \n";

                foreach ($missMatches as $key => $missMatche) {
                    $row = [];
                    $row['barcode'] = $missMatche['barcode'];
                    $row['s_location_id'] = $missMatche['s_location_id'];
                    $row['h_location_id'] = $missMatche['h_location_id'];
                    $row['s_qty'] = $missMatche['s_qty'];
                    $row['h_qty'] = $missMatche['h_qty'];
                    $this->insert($row);
                }

                $page++;
            } while (count($missMatches) == $perPage);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getMissMatchForWriteCSV($page, $limit)
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
                shopify_products ON miss_matches.barcode = shopify_products.barcode
                AND miss_matches.s_location_id = shopify_products.location_gid
            LIMIT :limit OFFSET :offset
        ");

        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $results;
    }


    public function getMissMatches($page, $limit)
    {
        $offset = ($page - 1) * $limit;

        $stmt = $this->pdo->prepare("
            SELECT * FROM {$this->table} LIMIT :limit OFFSET :offset
        ");

        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $results;
    }
}
