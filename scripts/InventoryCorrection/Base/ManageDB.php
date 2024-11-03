<?php

namespace scripts\InventoryCorrection\Base;

class ManageDB {
    function __construct()
    {
        $database = 'sample-1001';
        $this->pdo = new \PDO("sqlite:./{$database}.sqlite");
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }


    public function getShopifyLocationIdByName($locationName){
        try {
            $selectSQL = "
                SELECT * FROM map_locations
                WHERE  s_location_name = :s_location_name
                LIMIT 1;
            ";

            $stmt = $this->pdo->prepare($selectSQL);

            $stmt->bindValue(':s_location_name', $locationName);
            $stmt->execute();

            $record = $stmt->fetch(\PDO::FETCH_ASSOC);

            if(!isset($record['s_location_id'])){
                exit("EXIT: (S) location id not found name - {$locationName}");
            }

            return $record['s_location_id'];

        } catch (\PDOException $e) {
            echo "Error fetching record: " . $e->getMessage();
        }
    }
    
}