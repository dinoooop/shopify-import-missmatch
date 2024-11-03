<?php

namespace scripts\InventoryCorrection;

use scripts\InventoryCorrection\Base\ManageDB;

class MapLocationBase extends ManageDB
{
    private $table;
    
    function __construct()
    {
        parent::__construct();
        $this->table = "map_locations";
    }

    public function resetTable()
    {
        $this->pdo->exec("DROP TABLE IF EXISTS {$this->table};");
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS {$this->table} (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                h_location_id INTEGER NOT NULL,
                h_location_name VARCHAR(255) NOT NULL,
                s_location_id INTEGER NOT NULL,
                s_location_name VARCHAR(255) NOT NULL
            )
        ");
    }

    public function insert($row)
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO {$this->table} (
                    h_location_id, 
                    h_location_name, 
                    s_location_id, 
                    s_location_name
                ) VALUES (
                    :h_location_id, 
                    :h_location_name, 
                    :s_location_id, 
                    :s_location_name
                )
            ");

            $stmt->bindParam(':h_location_id', $row['h_location_id'], \PDO::PARAM_INT);
            $stmt->bindParam(':h_location_name', $row['h_location_name'], \PDO::PARAM_STR);
            $stmt->bindParam(':s_location_id', $row['s_location_id'], \PDO::PARAM_STR);
            $stmt->bindParam(':s_location_name', $row['s_location_name'], \PDO::PARAM_STR);

            $stmt->execute();
        } catch (\PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
    
}
