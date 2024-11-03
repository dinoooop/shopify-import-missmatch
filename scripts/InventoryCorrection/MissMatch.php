<?php

namespace scripts\InventoryCorrection;

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


            $this->resetTable();
            
            $page = 1;
            $perPage = 1000;

            do {
                $missMatches = $this->findAllMissMatches($page, $perPage);

                echo "Missmatch page: {$page} \n";

                foreach ($missMatches as $missMatche) {
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
