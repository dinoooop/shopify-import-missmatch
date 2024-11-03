<?php

namespace scripts\InventoryCorrection;

use scripts\InventoryCorrection\Base\ManageDB;

class MapLocation extends ManageDB
{
    private $table;
    private $file;

    function __construct()
    {
        parent::__construct();
        $this->table = "map_locations";
        $this->file = "./resources/inventory-correction/map_locations.csv";
    }

    public function createTable($truncate = false)
    {
        $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS {$this->table} (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    h_location_id INTEGER NOT NULL,
                    h_location_name VARCHAR(255) NOT NULL,
                    s_location_id VARCHAR(255) NOT NULL,
                    s_location_name VARCHAR(255) NOT NULL
                )
            ");

        if ($truncate) {
            $this->pdo->exec("DELETE FROM {$this->table}");
            $this->pdo->exec("DELETE FROM sqlite_sequence WHERE name='{$this->table}'");
        }
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
    public function import()
    {

        try {
            $handle = fopen($this->file, 'r');

            $header = array_map('trim', fgetcsv($handle));

            while (($data = fgetcsv($handle)) !== false) {
                $row = [];
                $row['h_location_id'] = $data[array_search("Hearland Location Id", $header)];
                $row['h_location_name'] = $data[array_search("Heartland Location Name", $header)];
                $row['s_location_id'] = $data[array_search("Shopify location id", $header)];
                $row['s_location_name'] = $data[array_search("Shopify Location name", $header)];
                $this->insert($row);
            }

            fclose($handle);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    
}
